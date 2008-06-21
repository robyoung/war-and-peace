<?php
require 'inc/config.php';
require 'inc/db.php';

require 'lib/FeedParser.class.php';
require 'lib/Tokenizer.class.php';

echo "Loading country tags...";
$country_tags = dbSelect("SELECT * FROM countries JOIN country_tags ON (countries.id=country_tags.country_id)");
echo "DONE\n";

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

$tk_factory = new TokenizerFactory($country_tags);

$yes = 0;
$no  = 0;
foreach ($parser as $item) {
  $tokenizer = $tk_factory->create($parser, $item);
  $locations = $tokenizer->getLocations();
  if ($locations) {
    $yes++;
    echo "[" . $parser->current_feed->title() . "] " . $item->title() . "\n";
    print_r($locations);
  } else {
    $no++;
  }
}
echo "yes: " . $yes . "\n";
echo "no:  " . $no  . "\n";
