<?php
require 'inc/config.php';
require 'inc/db.php';

require 'lib/FeedParser.class.php';
require 'lib/Tokenizer.class.php';

echo "Loading country tags...";
$country_tags = dbSelect("SELECT * FROM country_tags");
echo "DONE\n";

echo "Loading feeds...";
$parser = new FeedParser($country_tags);
$opml = simplexml_load_file('http://news.bbc.co.uk/rss/feeds.opml');
$i = 0;
foreach ($opml->xpath('//outline') as $item) {
	if ($i++>5) {
		break;
	}
	if ((string)$item['language'] == 'en-gb') {
		$parser->add((string)$item['xmlUrl']);
	}
}
echo "DONE\n";

$tk_factory = new TokenizerFactory();

foreach ($parser as $item) {
    echo "[" . $parser->current_feed->title() . "] " . $item->title() . "\n";
    $tokenizer = $tk_factory->create($parser, $item);
    echo strlen($tokenizer->text) . "\n";
    print_r($tokenizer->getLocations());
    //print_r($tokenizer->getCapsNGrams());
    echo "\n";
    /*$tokenizer->getLocations();
    $tokenizer->getClasifiers();*/
}
