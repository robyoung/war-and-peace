<?php

require('inc/config.php');
include('lib/Smarty/Smarty.class.php');

$smarty = new Smarty();
$smarty->caching = false;
$smarty->template_dir = $config['path'].'smarty/templates';
$smarty->compile_dir = $config['path'].'smarty/templates_c';
$smarty->cache_dir = $config['path'].'smarty/cache';
$smarty->config_dir = $config['path'].'smarty/configs';

$relationships = array(
	array('start' => array('lat' => 0, 'long' => 0), 'finish' => array('lat' => 42.3508, 'long' => 0))
);

$smarty->assign('relationships', $relationships);
$smarty->assign('config', $config);

$smarty->display('index.tpl');

?>