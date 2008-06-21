<?php
require 'lib/FeedParser.class.php';

$parser = new FeedParser();
$parser->add('http://rss.slashdot.org/Slashdot/slashdot');
echo "loaded\n";

foreach ($parser as $item) {
	echo "foo\n";
}