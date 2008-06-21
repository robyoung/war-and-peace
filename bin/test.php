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
echo "DONE\n";

$tk_factory = TokenizerFactory::create();

foreach ($parser as $item) {
  $tokenizer = $tk_factory->createTokenizer($parser, $item);
  $locations = $tokenizer->getLocations();
  if ($locations) {
    if (count($locations)>1) {
      $classifier = $tokenizer->getEdgeType();
      if ($classifier) {
        if (!dbSelect('SELECT * FROM edge WHERE edge_type="'. $classifier['edge_type_id'] . '" and url="' . (string)$item->link() . '"')) {
          dbInsert('edge', array(
            'edge_type' => $classifier['edge_type_id'],
            'country_one' => $locations[0]['id'],
            'country_two' => $locations[1]['id'],
            'url'         => (string)$item->link(),
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
