<?php /* Smarty version 2.6.18-dev, created on 2009-10-24 11:02:15
         compiled from wiki:Feed */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'wiki:Feed', 1, false),)), $this); ?>
<script language="JavaScript" src="http://feed2js.org//feed2js.php?src=<?php echo ((is_array($_tmp=$this->_tpl_vars['feedurl'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')); ?>
&chan=<?php echo ((is_array($_tmp=$this->_tpl_vars['chan'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')); ?>
&num=<?php echo ((is_array($_tmp=$this->_tpl_vars['num'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')); ?>
&desc=<?php echo ((is_array($_tmp=$this->_tpl_vars['desc'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')); ?>
&date=<?php echo ((is_array($_tmp=$this->_tpl_vars['date'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')); ?>
&targ=<?php echo ((is_array($_tmp=$this->_tpl_vars['targ'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')); ?>
&utf=y" type="text/javascript"></script> 

== Attribution  ==

It was originally created by [[mediawikiwiki:User:Sergey%20Chernyshev|Sergey Chernyshev]] for [http://www.ardorado.com Ardorado.com] and sponsored by [http://www.semanticcommunities.com/ Semantic Communities, LLC.]. 

This widget uses code and service from [http://www.feed2js.org Feed2JS].

[[Category:Widgets]]