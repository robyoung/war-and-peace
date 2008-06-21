<?php

class TokenizerFactory
{
	private $country_tags;
	private $tags;

	public function __construct($country_tags)
	{
		$this->country_tags = array();
		$this->tags = array();
		foreach ($country_tags as $country_tag) {
			$this->country_tags[$country_tag['tag']] = $country_tag;
			$this->tags[] = $country_tag['tag'];
		}
	}

	public function create(FeedParser $parser, Zend_Feed_Entry_Rss $item)
	{
		$tokenizer = new Tokenizer($this->country_tags, $this->tags);
		$tokenizer->addText((string)$item->title());
		$tokenizer->addText((string)$item->description());
		$tokenizer->addText((string)$item->content());
		return $tokenizer;
	}
}

class Tokenizer
{
	private $text;
	private $country_tags;
	private $tags;

	public function __construct($country_tags, $tags)
	{
		$this->text = '';
		$this->country_tags = $country_tags;
		$this->tags = $tags;
	}
	
	public function __get($name)
	{
		switch ($name) {
		case 'text':
			return $this->text;
		}
	}
	
	public function addText($text)
	{
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
		$matches = array();
		foreach ($this->getCapsNGrams() as $ngram) {
			$i = array_search($ngram, $this->tags);
			if ($i!==false && !in_array($ngram, $matches)) {
				$matches[] = $ngram;
			}
		}
		return $matches;
	}

	public function getClassifiers()
	{
		
	}
}
