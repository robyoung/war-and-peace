<?php
require 'lib/FeedParser.class.php';
require 'lib/Tokenizer.class.php';

$parser = new FeedParser();
$parser->add('http://newsrss.bbc.co.uk/rss/newsonline_uk_edition/front_page/rss.xml');
$parser->add('http://newsrss.bbc.co.uk/rss/newsonline_uk_edition/business/rss.xml');

$tk_factory = new TokenizerFactory();

foreach ($parser as $item) {
    echo "[" . $parser->current_feed->title() . "] " . $item->title() . "\n";
    $tokenizer = $tk_factory->create($parser, $item);
    echo strlen($tokenizer->text) . "\n";
    print_r($tokenizer->getCapsNGrams());
    echo "\n";
    /*$tokenizer->getLocations();
    $tokenizer->getClasifiers();*/
}
