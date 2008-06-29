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
			return array_keys($this->category_counts);
		}
	}
	
	protected function getFeatures($document)
	{
		return $this->tokenizer->getTerms($document);
	}
	
	protected function incFeature($feature, $category)
	{
		if (!isset($this->feature_category_counts[$feature][$category])) {
			$this->feature_category_counts[$feature][$category] = 0;
		}
		$this->feature_category_counts[$feature][$category]++;
	}
	
	protected function incCategory($category)
	{
		if (!isset($this->category_counts[$category])) {
			$this->category_counts[$category] = 0;
		}
		$this->category_counts[$category]++;
	}
	
	public function featureProbability($feature, $category)
	{
		if ($this->categoryCount($category) == 0) return 0;
		return $this->featureCount($feature, $category) / $this->categoryCount($category);
	}
	
	public function weightedProbability($feature, $category, $method, $weight=1, $assumed_probability=0.5)
	{
		$probability = call_user_func(array($this, $method), $feature, $category);
		$total       = 0;
		foreach ($this->categories as $other_category) {
			$total += $this->featureCount($feature, $other_category);
		}
		return (($weight*$assumed_probability) + ($probability*$total)) / ($weight + $total);
	}
	
	public function featureCount($feature, $category)
	{
		if (!isset($this->feature_category_counts[$feature][$category])) return 0;
		return $this->feature_category_counts[$feature][$category];
	}
	
	public function categoryCount($category)
	{
		if (isset($this->category_counts[$category])) {
			return $this->category_counts[$category];
		}
		return 0;	
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
			$prob *= $this->weightedProbability($feature, $category, 'categoryProbability');
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
		return $best ? $best : null;
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