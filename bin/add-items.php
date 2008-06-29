<?php
require 'inc/config.php';
require 'inc/db.php';

require 'lib/FeedParser.class.php';
require 'lib/Parser.class.php';

echo "Loading feeds...";
$feed_parser = new FeedParser();
$opml = simplexml_load_file('http://news.bbc.co.uk/rss/feeds.opml');
$i = 0;
foreach ($opml->xpath('//outline') as $item) {
	if ((string)$item['language'] == 'en-gb') {
		$feed_parser->add((string)$item['xmlUrl']);
	}
}
$feed_parser->add('http://www.guardian.co.uk/rss');
$feed_parser->add('http://www.guardian.co.uk/world/rss');
$feed_parser->add('http://www.guardian.co.uk/rssfeed/0,,11,00.xml');
$feed_parser->add('http://www.guardian.co.uk/rssfeed/0,,12,00.xml');
$feed_parser->add('http://www.guardian.co.uk/rssfeed/0,,5,00.xml');
$feed_parser->add('http://www.guardian.co.uk/rssfeed/0,,24,00.xml');
$feed_parser->add('http://www.guardian.co.uk/rssfeed/0,15065,19,00.xml');
$feed_parser->add('http://www.guardian.co.uk/rssfeed/0,,18,00.xml');
$feed_parser->add('http://www.guardian.co.uk/rssfeed/0,,7,00.xml');
$feed_parser->add('http://english.aljazeera.net/Services/Rss/?PostingId=2007731105943979989');
echo "DONE\n";

$parser_factory = ParserFactory::create();

foreach ($feed_parser as $item) {
  $parser = $parser_factory->createParser($feed_parser, $item);
  $locations = $parser->getLocations();
  if ($locations) {
    if (count($locations)>1) {
      $classifier = $parser->getEdgeType();
      if ($classifier) {
        if (!dbSelect('SELECT * FROM edge WHERE edge_type="'. $classifier['edge_type_id'] . '" and guid="' . (string)$item->guid() . '"')) {
          dbInsert('edge', array(
            'edge_type' => $classifier['edge_type_id'],
            'country_one' => min($locations[0]['country_id'], $locations[1]['country_id']),
            'country_two' => max($locations[0]['country_id'], $locations[1]['country_id']),
            'url'         => (string)$item->link(),
            'guid'        => (string)$item->guid(),
            'title'       => (string)$item->title()
          ));
        }
      } else {
        echo $parser . "\n";
        echo $item->content() . "\n";
        echo $item->description() . "\n";
        print_r($locations);
      }
    }
  }
}
