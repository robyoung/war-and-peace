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

  public function getEdgesForBox($bottom_left, $top_right, $count)
  {
    $sql = "SELECT country_one.id as country_one_id, country_one.name as country_one_name, country_one.lat as country_one_lat, country_one.long as country_one_long, ".
           "country_two.id as country_two_id, country_two.name as country_two_name, country_two.lat as country_two_lat, country_two.long as country_two_long, " .
      "SQRT(POW(ABS(country_one.lat-country_two.lat), 2)+POW(ABS(country_one.long-country_two.long), 2)) as distance ".
      "FROM edge " .
      "JOIN countries AS country_one ON (edge.country_one=country_one.id) ".
      "JOIN countries AS country_two ON (edge.country_two=country_two.id) " .
      "WHERE country_one.lat > {$bottom_left[0]} and country_one.long > {$bottom_left[1]} and country_one.lat < {$top_right[0]} and country_one.long < {$top_right[1]} ".
      "and country_two.lat > {$bottom_left[0]} and country_two.long > {$bottom_left[1]} and country_two.lat < {$top_right[0]} and country_two.long < {$top_right[1]} ".
      "group by country_one_id, country_two_id, country_one_name, country_one_lat, country_one_long, country_two_name, country_two_lat, country_two_long, distance ".
      "order by distance desc limit $count";

    $items  = dbSelect($sql);
    echo count($items) . "\n";

    $return = array();
    foreach ($items as $item) {
      $sql = 'SELECT edge.url, edge.title, edge_type.name '.
             'FROM edge JOIN edge_type ON (edge.edge_type=edge_type.id) '.
             'WHERE edge.country_one='.$item['country_one_id'].' '.
             'and edge.country_two='.$item['country_two_id'];
      $edges = dbSelect($sql);
      $return_item = array(
        'countries' =>
          array(
            array(
              'id'   => $item['country_one_id'],
              'name' => $item['country_one_name'],
              'lat'  => $item['country_one_lat'],
              'long' => $item['country_one_long']
            ),
            array(
              'id'   => $item['country_two_id'],
              'name' => $item['country_two_name'],
              'lat'  => $item['country_two_lat'],
              'long' => $item['country_two_long']
            )
          ),
          'edges' => $edges
        );
      $return[] = $return_item;
    }
    return $return;
  }
}
