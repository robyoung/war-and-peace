<?php
require_once 'Zend/Feed.php';

class FeedParser implements Iterator 
{
	private $urls   = array();
	private $urls_i = 0;
	private $current_feed;
	private $current_item;

	public function add($url)
	{
		$this->urls[] = $url;
		$this->next();
	}
	
	/* Iterator Methods */
	public function current()
	{
		return $this->current_item;
	}
	
	public function key()
	{
		return null;
	}
	
	public function next()
	{
		$this->current_item = null;
		if (!$this->current_feed) {
			if (!isset($this->urls[$this->urls_i])) return;
			$this->current_feed = Zend_Feed::import($this->urls[$this->urls_i++]);
		}
		$this->current_feed->next();
		if (!$this->current_feed->valid()) {
			return $this->next();
		} else {
			$this->current_item = $this->current_feed->current();
		}
	}

	public function rewind()
	{
		$this->current_feed = null;
		$this->current_item = null;
		$this->urls_i       = 0;
	}
	
	public function valid()
	{
		return (bool)$this->current_item;
	}
}