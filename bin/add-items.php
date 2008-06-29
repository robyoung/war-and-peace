<?php
require 'inc/config.php';
require 'inc/db.php';

require 'lib/FeedParser.class.php';
require 'lib/Parser.class.php';

echo "Loading feeds...";
$feed_parser = new FeedParser();
$i = 0;
$opml = simplexml_load_file('http://news.bbc.co.uk/rss/feeds.opml');
foreach ($opml->xpath('//outline') as $item) {
	if ((string)$item['language'] == 'en-gb') {
		$feed_parser->add((string)$item['xmlUrl']);
  }
}
$feed_parser->add('http://www.guardian.co.uk/rss');
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

function prompt($parser, $item, $category, $locations)
{
  echo "\n" . $item->title() . "\n";
  echo $locations[0]['name'] . ' <-> ' . $locations[1]['name'] . "\n";
  if ($category) {
    echo $category['name'] . " ok?\n";
  }
  while (true) {
    $in = readline('> ');
    if ($in == 'q') {
      return;
    } elseif ($in == 'm') {
      echo $item->title() . "\n";
      echo $item->guid() . "\n";
      echo $item->description() . "\n";
      echo $item->content() . "\n";
    } elseif ($in == 'no') {
      return;
    } elseif ($in == 'h') {
      foreach ($parser->getClassifier()->getAllCategories() as $category) {
        echo "  " . $category['id'] . "> " . $category['name'] . "\n";
      }
      echo "  q> skip\n";
      echo "  m> more\n";
    } else {
      if (!$category) {
        $category = $parser->getClassifier()->getCategory($in);
      }
      if ($category) {
        return $category;
      }
    }
  }
}

foreach ($feed_parser as $item) {
  $parser = $parser_factory->createParser($feed_parser, $item);
  if ($parser->haveEdge($item)) continue;
  $locations = $parser->getLocations();
  if ($locations) {
    if (count($locations)>1) {
      $category = $parser->classify();
      $category = prompt($parser, $item, $category, $locations);
      if ($category) {
        $parser->train($category['id']);
        $parser->saveEdge($item, $category, $locations);
      }
    }
  }
}
