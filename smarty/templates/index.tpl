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
	<script type="text/javascript">		var edgeCount = 300;
		var mapviewer = false;
		
		Event.observe(window, 'load', function() {		 getSize();
		
		 mapviewer = new MMFactory.createViewer( document.getElementById( 'map' ) );
		 mapviewer.goToPosition( new MMLatLon( 42.3508, 0 ) );
		 mapviewer.zoom(-10, 'Start');
		 mapviewer.addEventHandler('changeZoom', loadEdges);
		 
		 loadEdges();
		 		 		 		});
		
		function loadEdges(){
		 	
		 	mapviewer.removeAllOverlays();
		 
		 	mapBounds = mapviewer.getMapBounds();
		 	southEast = mapBounds.getSouthEast();
		 	northWest = mapBounds.getNorthWest();
		 	center = mapBounds.getCenter();
		 	
		 	projection = new MMProjection(mapviewer.getAvailableZoomFactors());
		 	alert(projection.getWrapWidth(mapviewer.getZoomFactor()));
		 	
		 	alert(mapviewer.getZoomFactor() + ' - ' + mapviewer.getAvailableZoomFactors());
		 	
		 	url = '{/literal}{$config.domain}{literal}?module=edges&center=' + center + '&southEast=' + southEast + '&northWest=' + northWest + '&count=' + edgeCount;
		 	
		 	new Ajax.Request(url, {			  method: 'get',			  onSuccess: function(http) {			    // cycle through and set the overlays
			    $('test').innerHTML = http.responseText;
			    eval(http.responseText);			  }			});
		 	
		 	
		 }
	</script>
	{/literal}
</head>

<body>
	{include file='navigation.tpl'}
	{include file='map.tpl'}
	{include file='details.tpl'}
	
	<div id="test"></div>
	
</body>
</html>