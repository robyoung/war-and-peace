<?php

class MultiMap
{
	const DEFAULT_API_KEY = 'OA08062116357113812';
	const BASE_URL        = 'http://developer.multimap.com/API/';
	const VERSION         = '1.2';

	private $api_key;

	public static function create()
	{
		return new self(self::DEFAULT_API_KEY);
	}

	public function __construct($api_key)
	{
		$this->api_key = $api_key;
	}
	
	public function getPoint($query)
	{
		$url   = self::BASE_URL . 'geocode/' . self::VERSION . '/' . $this->api_key . '?qs=' . $query;
		$xml   = simplexml_load_file($url);
		$point = $xml->Location[0]->Point;
		return array((string)$point->Lat, (string)$point->Lon);
	}
}