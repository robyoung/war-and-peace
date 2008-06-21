<?php
require 'lib/FeedParser.class.php';

$parser = new FeedParser();
$parser->add('http://newsrss.bbc.co.uk/rss/newsonline_uk_edition/front_page/rss.xml');
$parser->add('http://newsrss.bbc.co.uk/rss/newsonline_uk_edition/business/rss.xml');
echo "loaded\n";

foreach ($parser as $item) {
    echo "[" . $parser->current_feed->title() . "] " . $item->title() . "\n";
}
