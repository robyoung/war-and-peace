<?php
require 'inc/config.php';
require 'inc/db.php';

require 'lib/FeedParser.class.php';
require 'lib/Tokenizer.class.php';

echo "Loading feeds...";
$parser = new FeedParser();
$opml = simplexml_load_file('http://news.bbc.co.uk/rss/feeds.opml');
$i = 0;
foreach ($opml->xpath('//outline') as $item) {
	if ((string)$item['language'] == 'en-gb') {
		$parser->add((string)$item['xmlUrl']);
	}
}
$parser->add('http://www.guardian.co.uk/rss');
$parser->add('http://www.guardian.co.uk/rssfeed/0,,11,00.xml');
$parser->add('http://www.guardian.co.uk/rssfeed/0,,12,00.xml');
$parser->add('http://www.guardian.co.uk/rssfeed/0,,5,00.xml');
$parser->add('http://www.guardian.co.uk/rssfeed/0,,24,00.xml');
$parser->add('http://www.guardian.co.uk/rssfeed/0,15065,19,00.xml');
$parser->add('http://www.guardian.co.uk/rssfeed/0,,18,00.xml');
$parser->add('http://www.guardian.co.uk/rssfeed/0,,7,00.xml');
echo "DONE\n";

$tk_factory = TokenizerFactory::create();

foreach ($parser as $item) {
  $tokenizer = $tk_factory->createTokenizer($parser, $item);
  $locations = $tokenizer->getLocations();
  if ($locations) {
    if (count($locations)>1) {
      $classifier = $tokenizer->getEdgeType();
      if ($classifier) {
        if (!dbSelect('SELECT * FROM edge WHERE edge_type="'. $classifier['edge_type_id'] . '" and guid="' . (string)$item->guid() . '"')) {
          dbInsert('edge', array(
            'edge_type' => $classifier['edge_type_id'],
            'country_one' => $locations[0]['country_id'],
            'country_two' => $locations[1]['country_id'],
            'url'         => (string)$item->link(),
            'guid'        => (string)$item->guid(),
            'title'       => (string)$item->title()
          ));
        }
      } else {
        echo $tokenizer . "\n";
        echo $item->content() . "\n";
        echo $item->description() . "\n";
        print_r($locations);
      }
    }
  }
}
