<?php

require_once dirname(__file__) . '/CountryFinder.class.php';
require_once dirname(__file__) . '/Classifier.class.php';

class ParserFactory
{
  private $country_finder;
  private $classifier;
  private $tokenizer;
  
  public static function create()
  {
    $factory = new self();
    $tokenizer = self::createTokenizer();
    $factory->setCountryFinder(CountryFinder::create());
    $factory->setClassifier(new FisherClassifier($tokenizer));
    $factory->setTokenizer($tokenizer);
    return $factory;
  }
  
  public static function createTokenizer()
  {
    $filter     = new LowercaseFilter();
    $filter->addFilter(new StopWordFilter(array('the', 'a', 'and', 'is', 'it', 'of', 'to', 'be', 'in')));
    $filter->addFilter(new ShortWordFilter(2));
  	$tokenizer = new WordTokenizer($filter);
    return $tokenizer;
  }

  public function setCountryTags($country_tags)
  {
		$this->country_tags = array();
		$this->tags = array();
		foreach ($country_tags as $country_tag) {
			$this->country_tags[$country_tag['tag']] = $country_tag;
			$this->tags[] = $country_tag['tag'];
		}
  }

  public function setCountryFinder($finder)
  {
    $this->country_finder = $finder;
  }

  public function setClassifier($classifier)
  {
    $this->classifier = $classifier;
  }
  
	public function setTokenizer(Tokenizer $tokenizer)
	{
		$this->tokenizer = $tokenizer;
	}

	public function createParser(FeedParser $feed_parser, Zend_Feed_Entry_Rss $item)
	{
    	$parser = new Parser($this->country_finder, $this->classifier, $this->tokenizer);
    	$parser->setSource($feed_parser, $item);
		$parser->addText((string)$item->title());
		$parser->addText((string)$item->description());
		$parser->addText((string)$item->content());
		return $parser;
	}
}

class Parser
{
	private $text;
	private $country_finder;
	private $classifier;
	private $tokenizer;

	private $parser;
	private $item;

	public function __construct($country_finder, $classifier, $tokenizer)
	{
	    $this->text = '';
	    $this->country_finder = $country_finder;
	    $this->classifier     = $classifier;
	    $this->tokenizer      = $tokenizer;
	}

	public function __get($name)
	{
		switch ($name) {
		case 'text':
			return $this->text;
		}
	}

	public function setSource($parser, $item)
	{
    	$this->parser = $parser;
    	$this->item   = $item;
	}

	public function addText($text)
	{
    	$text = str_replace(array('US', 'UK'), array('United States', 'United Kingdom'), $text);
		$this->text .= str_repeat(' ', 10) . $text;
		$this->text = trim($this->text);
	}

	public function getCapsNGrams()
	{
		preg_match_all('#(?:^|\W)([A-Z]\w+(?:\W{1,3}[A-Z]\w+)*)(?:\W|$)#', $this->text, $matches);
		return $matches[1];
	}

	public function getTerms()
	{
		return $this->tokenizer->getTerms($this->text);
	}

	public function getLocations()
  	{
    	return $this->country_finder->getLocations($this->getCapsNGrams());
    }

  public function getClassifier()
  {
    return $this->classifier;
  }

  public function train($category)
  {
    $this->classifier->train((string)$this->text, $category);
  }

	public function classify()
	{
		$category = $this->classifier->classify((string)$this->text);
		return $category;
  }

  public function haveEdge($item)
  {
    $data = dbSelect('SELECT * FROM edge WHERE guid="' . (string)$item->guid() . '"');
    return (bool)$data;
  }

  public function saveEdge($item, $category, $locations)
  {
    if (!$this->haveEdge($item)) {
        echo "Added > " . (string)$item->title() . ' > ' . $category['name'] . "\n";
        dbInsert('edge', array(
            'category'    => $category['id'],
            'country_one' => min($locations[0]['country_id'], $locations[1]['country_id']),
            'country_two' => max($locations[0]['country_id'], $locations[1]['country_id']),
            'url'         => (string)$item->link(),
            'guid'        => (string)$item->guid(),
            'title'       => (string)$item->title()
        ));
    }
  }

	public function getEdgeType()
  	{
    	$edge_type = $this->classifier->getEdgeType((string)$this->parser->current_feed->title());
    	if ($edge_type) return $edge_type;
    	return $this->classifier->getEdgeType($this->text);
	}

  	public function __toString()
	{
    	return '[' . $this->parser->current_feed->title() . '] ' . $this->item->title();
	}
}

interface Tokenizer
{
	public function getTerms($text);
}

interface TokenFilter
{
	public function filter($terms);
	public function addFilter(TokenFilter $filter);
}

abstract class BaseTokenizer implements Tokenizer
{
	protected $filter_chain;

	public function __construct(TokenFilter $filter)
	{
		$this->filter_chain = $filter;
	}
}

class WordTokenizer extends BaseTokenizer 
{
	public function getTerms($text)
	{
		$terms = preg_split('/\W+/', $text);
		return $this->filter_chain->filter($terms);
	}
}

abstract class BaseFilter implements TokenFilter
{
	protected $filter_chain;

	public function __construct()
	{
		$this->filter_chain = new NullFilter();
	}

	public function addFilter(TokenFilter $filter)
	{
		if (!$this->filter_chain || $this->filter_chain instanceof NullFilter) {
			$this->filter_chain = $filter;
		} else {
			$this->filter_chain->addFilter($filter);
		}
	}
}

class NullFilter implements TokenFilter
{
	public function filter($terms)
	{
		return $terms;
	}

	public function addFilter(TokenFilter $filter)
	{
		throw new Exception("Cannot add to a NullFilter");
	}
}

class StopWordFilter extends BaseFilter 
{
	private $stopwords;

	public function __construct($stopwords=null)
	{
		parent::__construct();
		if (!$stopwords) {
			$this->stopwords = array();
		} else {
			$this->stopwords = $stopwords;
		}
	}

	public function filter($terms)
	{
		return $this->filter_chain->filter(array_diff($terms, $this->stopwords));
	}
}

class ShortWordFilter extends BaseFilter
{
  private $min_length;

  public function __construct($length)
  {
    parent::__construct();
    $this->min_length = $length;
  }

  public function _filter($item)
  {
    return strlen($item) >= $this->min_length;
  }

  public function filter($terms)
  {
    return $this->filter_chain->filter(array_filter($terms, array($this, '_filter')));
  }
}

class LowercaseFilter extends BaseFilter 
{
	public function filter($terms)
	{
		return $this->filter_chain->filter(array_map('strtolower', $terms));
	}
}
