<?php

class SimpleClassifier
{
  private $edge_type_tags;
  public static function create()
  {
    $classifier = new self();
    $classifier->setEdgeTypeTags(dbSelect("SELECT * FROM edge_type_tags ORDER BY sort_order ASC"));
    return $classifier;
  }

  public function setEdgeTypeTags($edge_type_tags)
  {
    $this->edge_type_tags = array();
    $this->tags           = array();
    foreach ($edge_type_tags as $edge_type_tag) {
      $this->edge_type_tags[$edge_type_tag['tag']] = $edge_type_tag;
    }
  }

  public function getEdgeType($text)
  {
    foreach ($this->edge_type_tags as $tag => $info) {
      if (preg_match('/' . $tag . '/i', $text)) {
        return $info;
      }
    }
  }
}

class Classifier
{
	private $categories;
	private $feature_counts;

	private $feature_category_counts;
	private $category_counts;
	/**
	 *
	 * @var Tokenizer
	 */
	private $tokenizer;

	public function __construct(Tokenizer $tokenizer)
	{
		$this->feature_category_counts = array();
		$this->category_counts         = array();
		$this->tokenizer               = $tokenizer;
	}

	public function __get($name)
	{
		switch ($name) {
		case 'categories':
			$this->_loadCategories();
			return array_keys($this->categories);
		}
	}
	
	public function getAllCategories()
	{
		return $this->categories;
	}
	
	public function getCategory($category)
	{
		return $this->categories[$category];
	}
	
	protected function getFeatures($document)
	{
		return $this->tokenizer->getTerms($document);
	}

	protected function _loadFeatures()
	{
		if (!$this->feature_counts) {
			$this->feature_counts = array();
			foreach (dbSelect('SELECT * FROM feature_counts') as $feature) {
				$this->feature_counts[$feature['name']][$feature['category']] = $feature['count'];
			}
		}
	}
	
	public function featureCount($feature, $category)
	{
		$this->_loadFeatures();
		if (!isset($this->feature_counts[$feature][$category])) return 0;
		return $this->feature_counts[$feature][$category];
	}
	
	protected function incFeature($feature, $category)
	{
		$count = $this->featureCount($feature, $category);
		// update the db
		if ($count) {
			dbUpdate('feature_counts', array('count'=>$count+1), "feature='$feature' and category=$category");
			$this->feature_counts[$feature][$category] = $count+1;
		} else {
			dbInsert('feature_counts', array('feature'=>$feature, 'category'=>$category, 'count'=>1));
			$this->feature_counts[$feature][$category] = 1;
		}		
	}

	protected function _loadCategories()
	{
		if (!$this->categories) {
			$this->categories = array();
			foreach (dbSelect("SELECT * FROM category") as $category) {
				$this->categories[$category['id']] = $category;
			}
		}
	}

	public function categoryCount($category)
	{
		$this->_loadCategories();
		if (!isset($this->categories[$category])) {
			throw new Exception("Unhandled category id " . $category);
		}
		return $this->categories[$category]['count'];
	}
	
	protected function incCategory($category)
	{
		$count = $this->categoryCount($category);
		dbUpdate('category', array('count'=>$count+1), 'id=' . $category);
		$this->categories[$category]['count'] = $count + 1;
	}
	
	public function featureProbability($feature, $category)
	{
		if ($this->categoryCount($category) == 0) return 0;
		return $this->featureCount($feature, $category) / $this->categoryCount($category);
	}
	
	public function weightedProbability($feature, $category, $method, $weight=1, $assumed_probability=0.3)
	{
		$probability = call_user_func(array($this, $method), $feature, $category);
		$total       = 0;
		foreach ($this->categories as $other_category) {
			$total += $this->featureCount($feature, $other_category);
		}
		return (($weight*$assumed_probability) + ($probability*$total)) / ($weight + $total);
	}
	
	public function totalCount()
	{
		return array_sum($this->category_counts);
	}
	
	public function probability($document, $category)
	{
		$catprob = $this->categoryCount($category)/$this->totalCount();
		$docprob = $this->documentProbability($document, $category);
		return $docprob * $catprob;
	}
	
	public function train($item, $category)
	{
		$features = $this->getFeatures($item);
		foreach ($features as $feature) {
			$this->incFeature($feature, $category);
		}
		$this->incCategory($category);
	}
}

class NaiveBayesClassifier extends Classifier 
{
	public function __construct(Tokenizer $tokenizer)
	{
		parent::__construct($tokenizer);
	}

	public function documentProbability($document, $category)
	{
		$features    = $this->getFeatures($document);
		$probability = 1;
		foreach ($features as $feature) {
			$probability *= $this->weightedProbability($feature, $category, 'featureProbability');
		}
		return $probability;
	}
	
	public function classify($document)
	{
		$max = 0;
		$probabilities = array();
		foreach ($this->categories as $category) {
			$probabilities[$category] = $this->documentProbability($document, $category);
			if ($probabilities[$category] > $max) {
				$max  = $probabilities[$category];
				$best = $category;
			}
		}
		foreach ($probabilities as $category => $probability) {
			if ($category == $best) continue;
			if ($probability * 2 > $max) return null;
		}
		return $best;
	}
}

class FisherClassifier extends Classifier
{
	public function categoryProbability($feature, $category)
	{
		$clf = $this->featureProbability($feature, $category);
		if ($clf == 0) return 0;
		$freqsum = 0;
		foreach ($this->categories as $other_category) {
			$freqsum += $this->featureProbability($feature, $other_category);
		}
		
		return $clf / $freqsum;
	}

	public function documentProbability($document, $category)
	{
		$prob = 1;
		$features = $this->getFeatures($document);
		foreach ($features as $feature) {
			$category_probability = $this->weightedProbability($feature, $category, 'categoryProbability');
			$prob *= $category_probability;
		}
		$score = log($prob) * -2;
		
		$invchi = $this->invchi2($score, count($features)*2);
		return $invchi;
	}
	
	public function classify($document)
	{
		$threshold = 0.7;
		$max  = 0;
		$best = null;
		foreach ($this->categories as $category) {
			$probability = $this->documentProbability($document, $category);
			if ($probability > $threshold && $probability > $max) {
				$max  = $probability;
				$best = $category;
			}
		}
		return $best ? $this->getCategory($best) : null;
	}
	
	private function invchi2($chi, $df)
	{
		$m   = $chi / 2;
		$sum = $term = exp(-$m);
		for ($i=1; $i<floor($df/2); $i++) {
			$term *= $m/$i;
			$sum  += $term;
		}
		return min($sum, 1);
	}
}