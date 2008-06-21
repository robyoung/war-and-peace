<?php
require 'inc/config.php';
require 'inc/db.php';

require_once 'lib/MultiMap.php';

$map = MultiMap::create();
print_r($map->getEdgesForBox(array(0, 0), array(90, 90), 20));
