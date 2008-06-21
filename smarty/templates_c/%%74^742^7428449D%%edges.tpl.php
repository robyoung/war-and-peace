<?php /* Smarty version 2.6.19, created on 2008-06-21 23:17:50
         compiled from edges.tpl */ ?>
<?php $_from = $this->_tpl_vars['relationships']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['rel']):
?>
	var points = [];
	point1 = new MMLatLon( <?php echo $this->_tpl_vars['rel']['countries']['0']['lat']; ?>
, <?php echo $this->_tpl_vars['rel']['countries']['0']['long']; ?>
 );
	point2 = new MMLatLon( <?php echo $this->_tpl_vars['rel']['countries']['1']['lat']; ?>
, <?php echo $this->_tpl_vars['rel']['countries']['1']['long']; ?>
 );
	points.push(point1);
	points.push(point2);
	polyline = new MMPolyLineOverlay( points, undefined, undefined, undefined,  undefined, undefined );
	mapviewer.addOverlay(polyline);
<?php endforeach; endif; unset($_from); ?>