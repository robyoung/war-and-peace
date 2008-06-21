<?php

require_once 'lib/CountryFinder.class.php';
require_once 'lib/Classifier.class.php';

class TokenizerFactory
{
  private $country_finder;
  private $classifier;
  
  public static function create()
  {
    $factory = new self();
    $factory->setCountryFinder(CountryFinder::create());
    $factory->setClassifier(Classifier::create());
    return $factory;
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

	public function createTokenizer(FeedParser $parser, Zend_Feed_Entry_Rss $item)
	{
    $tokenizer = new Tokenizer($this->country_finder, $this->classifier);
    $tokenizer->setSource($parser, $item);
		$tokenizer->addText((string)$item->title());
		$tokenizer->addText((string)$item->description());
		$tokenizer->addText((string)$item->content());
		return $tokenizer;
	}
}

class Tokenizer
{

  private $text;
  private $country_finder;
  private $classifier;

  private $parser;
  private $item;

	public function __construct($country_finder, $classifier)
	{
    $this->text = '';
    $this->country_finder = $country_finder;
    $this->classifier     = $classifier;
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

	public function getLocations()
  {
    return $this->country_finder->getLocations($this->getCapsNGrams());
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
