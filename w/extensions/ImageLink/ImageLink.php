<?php
/**
 * @author Jean-Lou Dupont
 * @package ImageLink
 * @version 1.7.1
 * @Id $Id: ImageLink.php 961 2008-04-03 14:43:28Z jeanlou.dupont $
 */
//<source lang=php>
$wgExtensionCredits['other'][] = array( 
	'name'        	=> 'ImageLink', 
	'version'     	=> '1.7.1',
	'author'      	=> 'Jean-Lou Dupont', 
	'description' 	=> 'Provides a clickable image link',
	'url' 			=> 'http://mediawiki.org/wiki/Extension:ImageLink',			
);
if (!class_exists('StubManager') || (version_compare( StubManager::version(), '1.2.0', '<' ) ))
	echo '[[Extension:ImageLink]] requires [[Extension:StubManager]] version >= 1.2.0';							
else
	StubManager::createStub(	'ImageLink', 
								dirname(__FILE__).'/ImageLink.body.php',
								null,						// i18n file			
								null,						// hooks
								false, 						// no need for logging support
								null,						// tags
								array('imagelink', 'imagelink_raw', 'img', 'iconlink' ),	// parser Functions
								null
							 );
//</source>