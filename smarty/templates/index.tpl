<?xml version="1.0"?>
<!DOCTYPE html PUBLIC "-//IDEAL//DTD XHTML-with Target//EN" "{$config.sitedomain}DTD/xhtml1-target.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/xml; charset=UTF-8" />
	<title>War and Peace</title>
	<link rel="stylesheet" type="text/css" href="{$config.sitedomain}styles/setup.css" media="screen,projector,handheld" />
	<link rel="stylesheet" type="text/css" href="{$config.sitedomain}styles/screen.css" media="screen,projector,handheld" />
	<link rel="shortcut icon" href="{$config.sitedomain}favicon.ico" type="image/icon" />
	<script type="text/javascript">
	var sitedomain = '{$config.sitedomain}';
	var siteprefix = '{$config.siteprefix}';
	</script>
	<script src="{$config.domain}lib/prototype.js" type="text/javascript"></script>
	<script src="{$config.domain}lib/scriptaculous.js" type="text/javascript"></script>
	<script src="{$config.domain}lib/height.js" type="text/javascript"></script>
	<script type="text/javascript" src="http://developer.multimap.com/API/maps/1.2/OA08062116357113812"></script>
	{literal}
	<script type="text/javascript">
	var mapviewer;
	</script>
	<script type="text/javascript">		Event.observe(window, 'load', function() {		 getSize();
		 mapviewer = new MMFactory.createViewer( document.getElementById( 'map' ) );
		 mapviewer.goToPosition( new MMLatLon( 42.3508, 0 ) );
		 mapviewer.zoom(-10, 'Start');
		 
		 {/literal}{foreach from=$relationships item=rel}
		 var points = [];
		 point1 = new MMLatLon( {$rel.start.lat}, {$rel.start.long} );
		 point2 = new MMLatLon( {$rel.finish.lat}, {$rel.finish.long} );
		 points.push(point1);
		 points.push(point2);
		 polyline = new MMPolyLineOverlay( points, undefined, undefined, undefined,  undefined, undefined );
		 mapviewer.addOverlay(polyline);
		 
		 //alert(mapviewer.getMapBounds());
		 
		 {/foreach}{literal}
		 		});	</script>
	{/literal}
</head>

<body>
	{include file='navigation.tpl'}
	{include file='map.tpl'}
	{include file='details.tpl'}
</body>
</html>