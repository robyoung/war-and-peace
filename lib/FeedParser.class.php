<?php
require_once 'Zend/Feed.php';

class FeedParser implements Iterator 
{
	private $urls   = array();
	private $urls_i = 0;
	private $current_feed;

	public function add($url)
	{
		$this->urls[] = $url;
  }

  public function __get($name)
  {
    switch ($name) {
    case 'current_feed':
      return $this->current_feed;
    }
  }

	/* Iterator Methods */
	public function current()
	{
		return $this->current_feed->current();
	}
	
	public function key()
	{
		return null;
	}
	
	public function next()
  {
    if ($this->current_feed && $this->current_feed->valid()) {
      $this->current_feed->next();
    } else {
      if (!isset($this->urls[$this->urls_i])) {
        $this->current_feed = null;
      } else {
        $this->current_feed = Zend_Feed::import($this->urls[$this->urls_i++]);
        $this->current_feed->rewind();
      }
    }
	}

	public function rewind()
	{
		$this->current_feed = null;
		$this->urls_i       = 0;
	}
	
	public function valid()
  {
    if (!$this->current_feed || !$this->current_feed->valid()) {
      $this->next();
      if (!$this->current_feed) {
          return false;
      }
      $return =  $this->valid();
      return $return;
    }
    return true;
	}
}
