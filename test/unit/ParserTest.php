<?php

require_once 'PHPUnit/Framework/TestCase.php';
//require_once dirname(__file__) . '/../../lib/Classifier.class.php';
require_once dirname(__file__) . '/../../lib/Parser.class.php';

class ParserTest extends PHPUnit_Framework_TestCase 
{
	public function testWordToknizer()
	{
		$tokenizer = new WordTokenizer(new NullFilter());
		$words     = $tokenizer->getTerms('foo bar monkey');
		$this->assertEquals(array('foo', 'bar', 'monkey'), $words);
	}
	
	public function testStopWordFilter()
	{
		$tokenizer = new WordTokenizer(new StopWordFilter(array('this', 'the', 'at', 'is')));
		$words     = $tokenizer->getTerms('this is the cat at the dog');
		$this->assertEquals(array('cat', 'dog'), array_values($words));
	}
}