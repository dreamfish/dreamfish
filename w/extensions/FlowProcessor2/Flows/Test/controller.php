<?php

require_once 'lib/limonade.php';

function configure()
{
  global $IP;
  global $env;
  
  option('env', ENV_DEVELOPMENT);
  #option('root_dir', $IP."extensions\FlowProcessor\Flows\Test\lib");
  $root_dir  =  $IP."\extensions\FlowProcessor\Flows\Test";
  
  $base_path = dirname(file_path($env['SERVER']['SCRIPT_NAME']));
  $base_file = basename($env['SERVER']['SCRIPT_NAME']);
  $base_uri  = file_path($base_path, (($base_file == 'index.php') ? '?' : $base_file.'?'));
  $lim_dir   = dirname(__FILE__);
  option('root_dir',           $root_dir);
  option('base_path',          $base_path);
  option('base_uri',           $base_uri); // set it manually if you use url_rewriting
  option('limonade_dir',       file_path($lim_dir));
  option('limonade_views_dir', file_path($lim_dir, 'limonade', 'views'));
  option('limonade_public_dir',file_path($lim_dir, 'limonade', 'public'));
  option('public_dir',         file_path($root_dir, 'public'));
  option('views_dir',          file_path($root_dir, 'views'));
  option('controllers_dir',    file_path($root_dir, 'controllers'));
  option('lib_dir',            file_path($root_dir, 'lib'));
  option('error_views_dir',    option('limonade_views_dir'));
}

function before()
{

}

dispatch('/Special:FlowTest/asdf', 'index');
function index()
{
  global $wgOut;
  $wgOut->addHTML(html('foo'));
  set('name', 'Joe!');
  //$wgOut->addHTML(render('/test.html.php'));
}


dispatch('/Special:FlowTest/template', 'template');
function template()
{
  global $wgOut;
  set('name', 'Joe!');
  $wgOut->addHTML(render('/test.html.php'));
}

/**
	 * Standard MediaWiki entry point for
	 * ''Special Page'' FlowTest
	 */
	 function wfSpecialFlowTest( $params )
	 {
		$proc = new MW_Flow_Test( $params );
 
		$proc->execute();
	 }
 
	class MW_Flow_Test extends SpecialPage
	{
	 var $params = null;
	 function __construct( &$params )
	 {
	  $this->params = $params;
	 }
	 function execute( )
	 {
	 
    global $wgOut;
    global $wgRequest;
	  $this->setHeaders();
	  /*
    $wgOut->addHTML("<form method=\"post\"><input type=\"text\" name=\"name\"><input type=\"submit\" name=\"foo\"></form>");
	  #var_dump( $this->params );
	  #var_dump( $wgRequest);
	  var_dump($_POST);
	  if ($wgRequest->wasPosted())
      $wgOut->addHTML("POSTED!!!!!!!!");
      */      
      #var_dump($wgOut);
      #print "\n\n<br><br>";
      #$wgOut->addHTML('test');
      run();
	 }
 
	 function getDescription()
	 {
	  return "Test1 !";
	 }
	}
