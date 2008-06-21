<?php /* Smarty version 2.6.19, created on 2008-06-22 00:22:08
         compiled from index.tpl */ ?>
<?php echo '<?xml'; ?>
 version="1.0"<?php echo '?>'; ?>

<!DOCTYPE html PUBLIC "-//IDEAL//DTD XHTML-with Target//EN" "<?php echo $this->_tpl_vars['config']['sitedomain']; ?>
DTD/xhtml1-target.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/xml; charset=UTF-8" />
	<title>War and Peace</title>
	<link rel="stylesheet" type="text/css" href="<?php echo $this->_tpl_vars['config']['sitedomain']; ?>
styles/setup.css" media="screen,projector,handheld" />
	<link rel="stylesheet" type="text/css" href="<?php echo $this->_tpl_vars['config']['sitedomain']; ?>
styles/screen.css" media="screen,projector,handheld" />
	<link rel="shortcut icon" href="<?php echo $this->_tpl_vars['config']['sitedomain']; ?>
favicon.ico" type="image/icon" />
	<script type="text/javascript">
	var sitedomain = '<?php echo $this->_tpl_vars['config']['sitedomain']; ?>
';
	var siteprefix = '<?php echo $this->_tpl_vars['config']['siteprefix']; ?>
';
	</script>
	<script src="<?php echo $this->_tpl_vars['config']['domain']; ?>
lib/prototype.js" type="text/javascript"></script>
	<script src="<?php echo $this->_tpl_vars['config']['domain']; ?>
lib/scriptaculous.js" type="text/javascript"></script>
	<script src="<?php echo $this->_tpl_vars['config']['domain']; ?>
lib/height.js" type="text/javascript"></script>
	<script type="text/javascript" src="http://developer.multimap.com/API/maps/1.2/OA08062116357113812"></script>
	<?php echo '
	<script type="text/javascript">
	var mapviewer;
	</script>
	<script type="text/javascript">		var edgeCount = 300;
		var mapviewer = false;
		
		Event.observe(window, \'load\', function() {		 getSize();
		
		 mapviewer = new MMFactory.createViewer( document.getElementById( \'map\' ) );
		 mapviewer.goToPosition( new MMLatLon( 42.3508, 0 ) );
		 mapviewer.zoom(-10, \'Start\');
		 mapviewer.addEventHandler(\'changeZoom\', loadEdges);
		 
		 loadEdges();
		 		 		 		});
		
		function loadEdges(){
		 	
		 	mapviewer.removeAllOverlays();
		 
		 	mapBounds = mapviewer.getMapBounds();
		 	southEast = mapBounds.getSouthEast();
		 	northWest = mapBounds.getNorthWest();
		 	
		 	url = \''; ?>
<?php echo $this->_tpl_vars['config']['domain']; ?>
<?php echo '?module=edges&southEast=\' + southEast + \'&northWest=\' + northWest + \'&count=\' + edgeCount;
		 	
		 	new Ajax.Request(url, {			  method: \'get\',			  onSuccess: function(http) {			    // cycle through and set the overlays
			    $(\'test\').innerHTML = http.responseText;
			    eval(http.responseText);			  }			});
		 	
		 	
		 }
	</script>
	'; ?>

</head>

<body>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'navigation.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'map.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'details.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	
	<div id="test"></div>
	
</body>
</html>