<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once dirname(__file__) . '/../../lib/Classifier.class.php';
require_once dirname(__file__) . '/../../lib/Parser.class.php';

class ClassifierTest extends PHPUnit_Framework_TestCase
{
	private $tokenizer;
	/**
	 * The Classifier instance under test
	 *
	 * @var Classifier
	 */
	private $classifier;

	public function setUp()
	{
		$filter    = new LowercaseFilter();
		$filter->addFilter(new StopWordFilter(array('the', 'a', 'and', 'is', 'it')));
		$this->tokenizer = new WordTokenizer($filter);
		$this->classifier = new Classifier($this->tokenizer);
		$this->train();
	}
	
	private function train($classifier=null)
	{
		if (!$classifier) {
			$classifier = $this->classifier;
		}
		$classifier->train('Figure out why the binary data is not being deployed correctly', 'IPC');
		$classifier->train('Gallery Component', 'IPC');
		$classifier->train('Figure out why the binary data is not being deployed correctly', 'IPC');
		$classifier->train('Write Classifier test cases', 'war');
	}
	
	public function tearDown()
	{
		unset($this->classifier);
	}
	
	public function testFeatureCounts()
	{
		$this->assertEquals(2, $this->classifier->featureCount('out', 'IPC'));
		$this->assertEquals(0, $this->classifier->featureCount('the', 'IPC'));
	}
	
	public function testCategoryCounts()
	{
		$this->assertEquals(3, $this->classifier->categoryCount('IPC'));
		$this->assertEquals(1, $this->classifier->categoryCount('war'));
	}
	
	public function testTotalCount()
	{
		$this->assertEquals(4, $this->classifier->totalCount());
	}
	
	public function testCategories()
	{
		$this->assertEquals(array('IPC', 'war'), $this->classifier->categories);
	}
	
	public function testFeatureProbability()
	{
		$this->assertEquals(0, $this->classifier->featureProbability('the', 'IPC'));
		$this->assertEquals(2/3, $this->classifier->featureProbability('out', 'IPC'));
		$this->assertEquals(1/3, $this->classifier->featureProbability('component', 'IPC'));
	}
	
	public function testWeightedProbability()
	{
		$this->assertLessThan(0.7, $this->classifier->weightedProbability('out', 'IPC', 'featureProbability'));
		$this->assertGreaterThan(0.6, $this->classifier->weightedProbability('out', 'IPC', 'featureProbability'));
		// use within margin
		$this->assertLessThan(0.5, $this->classifier->weightedProbability('component', 'IPC', 'featureProbability'));
		$this->assertGreaterThan(0.4, $this->classifier->weightedProbability('component', 'IPC', 'featureProbability'));
	}
	
	public function testNaieveBayes()
	{
		$classifier = new NaiveBayesClassifier($this->tokenizer);
		$this->train($classifier);
		$this->assertLessThan(0.04, $classifier->documentProbability('The classifier should be working', 'IPC'));
		$this->assertGreaterThan(0.03, $classifier->documentProbability('The classifier should be working', 'IPC'));
	}
	
	public function testNaiveBayesClassify()
	{
		$classifier = new NaiveBayesClassifier($this->tokenizer);
		$this->train($classifier);
		$this->assertEquals('war', $classifier->classify('The classifier should be working'));
		$this->assertEquals('IPC', $classifier->classify('could binary data have been an issue today?'));
	}
	
	public function testFisher()
	{
		$classifier = new FisherClassifier($this->tokenizer);
		$this->train($classifier);
		$this->assertLessThan(0.6, $classifier->documentProbability('The classifier should be working', 'IPC'));
		$this->assertGreaterThan(0.5, $classifier->documentProbability('The classifier should be working', 'IPC'));
	}
	
	public function testFisherClassify()
	{
		$classifier = new FisherClassifier($this->tokenizer);
		$this->train($classifier);
		$this->assertEquals('war', $classifier->classify('The classifier should be working'));
		$this->assertEquals('IPC', $classifier->classify('could binary data have been an issue today?'));
	}
}