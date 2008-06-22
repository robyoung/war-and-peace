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

	private function _buildBounder($for, $bottom_left, $top_right, $middle)
  {
    if ($middle[1] > $top_right[0] or $middle[0] < $bottom_left[0]) {
      $sql = '';
    } elseif ($top_right[0] < $bottom_left[0]) {
      $sql = '(' . $for . '.lat BETWEEN -90 and ' . $top_right[0] . ' or ' . $for . '.lat and ' . $bottom_left[0] . ' and 90) ';
    } else {
		  $sql = $for . '.lat BETWEEN  ' . $bottom_left[0] . ' and  ' . $top_right[0] . ' ';
    }

    if ($middle[1] > $top_right[1] or $middle[1] < $bottom_left[1]) {
      return $sql;
    } elseif ($top_right[1] < $bottom_left[1]) {
			$sql .= 'and (' . $for . '.long BETWEEN -180 and ' . $top_right[1] . ' or ' . $for . '.long BETWEEN ' . $bottom_left[1] . ' and 180) ';
		} else {
			$sql .= 'and ' . $for . '.long BETWEEN ' . $bottom_left[1] . ' and ' . $top_right[1];
		}
		return $sql;
	}

  public function getEdgesForBox($bottom_left, $top_right, $middle, $count, $country=null, $edge_types=null)
  {
		$country_one = $this->_buildBounder('country_one', $bottom_left, $top_right, $middle);
		$country_two = $this->_buildBounder('country_two', $bottom_left, $top_right, $middle);
    $sql = "SELECT country_one.id as country_one_id, country_one.name as country_one_name, country_one.lat as country_one_lat, country_one.long as country_one_long, ".
           "country_two.id as country_two_id, country_two.name as country_two_name, country_two.lat as country_two_lat, country_two.long as country_two_long, " .
      "SQRT(POW(ABS(country_one.lat-country_two.lat), 2)+POW(ABS(country_one.long-country_two.long), 2)) as distance ".
      "FROM edge " .
      "JOIN countries AS country_one ON (edge.country_one=country_one.id) ".
      "JOIN countries AS country_two ON (edge.country_two=country_two.id) " .
      "WHERE " . $country_one . ' and ' . $country_two . ' ' . ($country?'and (edge.coutnry_one=' . $country . ' or edge.country_two=' . $country . ') ':'') .
      ($edge_types?'and edge.edge_type IN (' . implode(',', $edge_types) . ') ':'').
      "group by country_one_id, country_two_id, country_one_name, country_one_lat, country_one_long, country_two_name, country_two_lat, country_two_long, distance ".
      "order by distance desc limit $count";

    $items  = dbSelect($sql);
    echo count($items) . "\n";

    $return = array();
    foreach ($items as $item) {
      $sql = 'SELECT edge.url, edge.title, edge_type.name as type '.
             'FROM edge JOIN edge_type ON (edge.edge_type=edge_type.id) '.
             'WHERE edge.country_one='.$item['country_one_id'].' '.
             'and edge.country_two='.$item['country_two_id'];
      $edges = dbSelect($sql);
      $types = array();
      $colours = array(
        'angry'    => '#c8050c',
        'happy'    => '#00b495',
        'sport'    => '#00a235',
        'business' => '#538aad',
        'pirates'  => '#1a5690'
        );
      foreach ($edges as $edge) {
        if (!isset($types[$edge['type']])) {
          $types[$edge['type']] = 1;
        } else {
          $types[$edge['type']]++;
        }
      }
      list($type, $count) = each($types);
      
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
          'colour' => $colours[$type],
          'edges' => $edges
        );
      $return[] = $return_item;
    }
    return '';
    return $return;
  }
}
