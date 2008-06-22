<?php

require('inc/config.php');
include('lib/Smarty/Smarty.class.php');

$smarty = new Smarty();
$smarty->caching = false;
$smarty->template_dir = $config['path'].'smarty/templates';
$smarty->compile_dir = $config['path'].'smarty/templates_c';
$smarty->cache_dir = $config['path'].'smarty/cache';
$smarty->config_dir = $config['path'].'smarty/configs';

function GetRequest($var, $default = false){
	return (isset($_REQUEST[$var]))? $_REQUEST[$var] : $default;
}

$module = GetRequest('module');
if(file_exists('modules/' . $module . '.php')) include('modules/' . $module . '.php');

$smarty->assign('config', $config);

if(!isset($tpl)) $tpl = 'index';
$smarty->display($tpl . '.tpl');

?>