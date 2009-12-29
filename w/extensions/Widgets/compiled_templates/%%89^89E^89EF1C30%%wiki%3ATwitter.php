<?php /* Smarty version 2.6.18-dev, created on 2009-11-02 02:36:53
         compiled from wiki:Twitter */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'counter', 'wiki:Twitter', 1, false),array('modifier', 'escape', 'wiki:Twitter', 1, false),array('modifier', 'default', 'wiki:Twitter', 1, false),)), $this); ?>
<?php echo smarty_function_counter(array('name' => 'twittercounter','assign' => 'twitblogincluded'), $this);?>
<?php if ($this->_tpl_vars['twitblogincluded'] == 1): ?><script type="text/javascript">function twitterCallback(id,C){var A=[];for(var D=0;D<C.length;D++){var E=C[D].user.screen_name;var B=C[D].text.replace(/((https?|s?ftp|ssh)\:\/\/[^"\s\<\>]*[^.,;'">\:\s\<\>\)\]\!])/g,function(F){return'<a href="'+F+'">'+F+"</a>"}).replace(/\B@([_a-z0-9]+)/ig,function(F){return F.charAt(0)+'<a href="http://www.twitter.com/'+F.substring(1)+'">'+F.substring(1)+"</a>"});A.push("<li><span>"+B+'</span> <a style="font-size:85%" href="http://twitter.com/'+E+"/statuses/"+C[D].id+'">'+relative_time(C[D].created_at)+"</a></li>")}document.getElementById(id).innerHTML=A.join("")}function relative_time(C){var B=C.split(" ");C=B[1]+" "+B[2]+", "+B[5]+" "+B[3];var A=Date.parse(C);var D=(arguments.length>1)?arguments[1]:new Date();var E=parseInt((D.getTime()-A)/1000);E=E+(D.getTimezoneOffset()*60);if(E<60){return"less than a minute ago"}else{if(E<120){return"about a minute ago"}else{if(E<(60*60)){return(parseInt(E/60)).toString()+" minutes ago"}else{if(E<(120*60)){return"about an hour ago"}else{if(E<(24*60*60)){return"about "+(parseInt(E/3600)).toString()+" hours ago"}else{if(E<(48*60*60)){return"1 day ago"}else{return(parseInt(E/86400)).toString()+" days ago"}}}}}}};twitter=[];</script><?php endif; ?><ul id="tf<?php echo $this->_tpl_vars['twitblogincluded']; ?>
"></ul><script type="text/javascript">twitter[<?php echo $this->_tpl_vars['twitblogincluded']; ?>
]=function(data){twitterCallback('tf<?php echo $this->_tpl_vars['twitblogincluded']; ?>
', data);};s=document.createElement('script');s.type='text/javascript';s.id='twcall<?php echo $this->_tpl_vars['twitblogincluded']; ?>
';s.src='http://twitter.com/statuses/user_timeline/<?php echo ((is_array($_tmp=$this->_tpl_vars['user'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')); ?>
.json?callback=twitter[<?php echo $this->_tpl_vars['twitblogincluded']; ?>
]'+String.fromCharCode(38)+'count=<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['count'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')))) ? $this->_run_mod_handler('default', true, $_tmp, 5) : smarty_modifier_default($_tmp, 5)); ?>
';document.getElementsByTagName('head')[0].appendChild(s);</script>

=== Attribution ===

Created by [http://www.mediawikiwidgets.org/User:Sergey_Chernyshev Sergey Chernyshev]

[[Category:Widgets]]