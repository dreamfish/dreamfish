<?php
/**
 * @author Jean-Lou Dupont
 * @package FlowProcessor
 * @category Flow
 * @version 1.3.0
 * @Id $Id: FlowProcessor.php 968 2008-04-04 18:37:38Z jeanlou.dupont $
 */
//<source lang=php>
if (!class_exists('StubManager') || (version_compare( StubManager::version(), '1.3.0', '<' ) ))
	echo '[[Extension:FlowProcessor]] requires [[Extension:StubManager]] version >= 1.3.0';							
else
{
	$wgExtensionCredits['other'][] = array( 
		'name'        => 'FlowProcessor', 
		'version'     => '1.3.0',
		'author'      => 'Jean-Lou Dupont', 
		'description' => 'Provides an MVC-like flow processing framework. ',
		'url' 		=> 'http://mediawiki.org/wiki/Extension:FlowProcessor',			
	);

	StubManager::createStub2(	array(	'class' 		=> 'FlowProcessor', 
										'classfilename'	=> dirname(__FILE__).'/FlowProcessor.body.php',
										'hooks'			=> array(	'SpecialPage_initList' , 
																	'SpecialVersionExtensionTypes' )
									)
							);
}
//</source>