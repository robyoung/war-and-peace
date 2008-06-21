<?php

class TokenizerFactory
{
	public function create(FeedParser $parser, Zend_Feed_Entry_Rss $item)
	{
		$tokenizer = new Tokenizer();
		$tokenizer->addText((string)$item->title());
		$tokenizer->addText((string)$item->description());
		$tokenizer->addText((string)$item->content());
		return $tokenizer;
	}
}

class Tokenizer
{
	private $text;

	public function __construct()
	{
		$this->text = '';
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
		preg_match_all('#(?:^|\W)([A-Z]\w+(?:\W+[A-Z]\w+)*)(?:\W|$)#', $this->text, $matches);
		return $matches[1];
	}
	
	public function getLocations()
	{
		
	}
	
	public function getClassifiers()
	{
		
	}
}