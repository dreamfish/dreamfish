<?php /* Smarty version 2.6.18-dev, created on 2009-10-25 14:41:41
         compiled from wiki:Google+Maps */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'counter', 'wiki:Google Maps', 1, false),array('modifier', 'escape', 'wiki:Google Maps', 1, false),array('modifier', 'default', 'wiki:Google Maps', 12, false),)), $this); ?>
<?php if (! isset ( $this->_tpl_vars['static'] )): ?><?php echo smarty_function_counter(array('name' => 'mapDivID','assign' => 'mapDivID'), $this);?>
<script type="text/javascript" src="http://www.google.com/jsapi?key=<?php echo ((is_array($_tmp=$this->_tpl_vars['key'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')); ?>
"></script>
<script type="text/javascript">
google.load("maps", "2.x");
// Call this function when the page has been loaded
google.setOnLoadCallback(function()
{
	if (google.maps.BrowserIsCompatible())
	{
		var center = new GLatLng('<?php echo ((is_array($_tmp=$this->_tpl_vars['lat'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
', '<?php echo ((is_array($_tmp=$this->_tpl_vars['lng'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
');
		// Create and Center a Map
		var map = new google.maps.Map2(document.getElementById("map<?php echo ((is_array($_tmp=$this->_tpl_vars['mapDivID'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
"),
			{size: new google.maps.Size('<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['width'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')))) ? $this->_run_mod_handler('default', true, $_tmp, '420') : smarty_modifier_default($_tmp, '420')); ?>
', '<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['height'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')))) ? $this->_run_mod_handler('default', true, $_tmp, 350) : smarty_modifier_default($_tmp, 350)); ?>
')}
		);
		map.setCenter(center, 13);
		map.setZoom(Number('<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['zoom'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')))) ? $this->_run_mod_handler('default', true, $_tmp, 16) : smarty_modifier_default($_tmp, 16)); ?>
'));
		<?php $_from = $this->_tpl_vars['marker']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['m']):
?>map.addOverlay(new google.maps.Marker(new GLatLng('<?php echo ((is_array($_tmp=$this->_tpl_vars['m']['lat'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
', '<?php echo ((is_array($_tmp=$this->_tpl_vars['m']['lng'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
')));<?php endforeach; endif; unset($_from); ?>
		<?php if (isset ( $this->_tpl_vars['xml'] )): ?>map.addOverlay(new GGeoXml('<?php echo ((is_array($_tmp=$this->_tpl_vars['xml'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
'));<?php endif; ?>
		<?php if (isset ( $this->_tpl_vars['centermarker'] )): ?>map.addOverlay(new google.maps.Marker(center));<?php endif; ?>
		<?php if (isset ( $this->_tpl_vars['maptypecontrol'] )): ?>map.addControl(new GMapTypeControl());<?php endif; ?>
		<?php if (isset ( $this->_tpl_vars['largemapcontrol'] )): ?>map.addControl(new GLargeMapControl());<?php endif; ?>
		<?php if (isset ( $this->_tpl_vars['smallmapcontrol'] )): ?>map.addControl(new GSmallMapControl());<?php endif; ?>
		<?php if (isset ( $this->_tpl_vars['smallzoomcontrol'] )): ?>map.addControl(new GSmallZoomControl());<?php endif; ?>
		<?php if (isset ( $this->_tpl_vars['scalecontrol'] )): ?>map.addControl(new GScaleControl());<?php endif; ?>
		<?php if (isset ( $this->_tpl_vars['overviewmapcontrol'] )): ?>map.addControl(new GOverviewMapControl());<?php endif; ?>
		<?php if (isset ( $this->_tpl_vars['hierarchicalmaptypecontrol'] )): ?>map.addControl(new GHierarchicalMapTypeControl());<?php endif; ?>
	}
});
</script>
<div id="map<?php echo ((is_array($_tmp=$this->_tpl_vars['mapDivID'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" style="width: <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['width'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')))) ? $this->_run_mod_handler('default', true, $_tmp, '420') : smarty_modifier_default($_tmp, '420')); ?>
px; height: <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['height'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')))) ? $this->_run_mod_handler('default', true, $_tmp, 350) : smarty_modifier_default($_tmp, 350)); ?>
px"><?php endif; ?><img src="http://maps.google.com/staticmap?center=<?php echo ((is_array($_tmp=$this->_tpl_vars['lat'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')); ?>
,<?php echo ((is_array($_tmp=$this->_tpl_vars['lng'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')); ?>
&zoom=<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['zoom'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')))) ? $this->_run_mod_handler('default', true, $_tmp, 16) : smarty_modifier_default($_tmp, 16)); ?>
&size=<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['width'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')))) ? $this->_run_mod_handler('default', true, $_tmp, '420') : smarty_modifier_default($_tmp, '420')); ?>
x<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['height'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')))) ? $this->_run_mod_handler('default', true, $_tmp, 350) : smarty_modifier_default($_tmp, 350)); ?>
&markers=<?php if (isset ( $this->_tpl_vars['centermarker'] )): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['lat'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')); ?>
,<?php echo ((is_array($_tmp=$this->_tpl_vars['lng'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')); ?>
%7C<?php endif; ?><?php $_from = $this->_tpl_vars['marker']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['m']):
?><?php echo ((is_array($_tmp=$this->_tpl_vars['m']['lat'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')); ?>
,<?php echo ((is_array($_tmp=$this->_tpl_vars['m']['lng'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')); ?>
%7C<?php endforeach; endif; unset($_from); ?>&maptype=roadmap&key=<?php echo ((is_array($_tmp=$this->_tpl_vars['key'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')); ?>
" width="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['width'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')))) ? $this->_run_mod_handler('default', true, $_tmp, '420') : smarty_modifier_default($_tmp, '420')); ?>
" height="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['height'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')))) ? $this->_run_mod_handler('default', true, $_tmp, 350) : smarty_modifier_default($_tmp, 350)); ?>
"><?php if (! isset ( $this->_tpl_vars['static'] )): ?></div><?php endif; ?> 

[[Category:Widgets]]