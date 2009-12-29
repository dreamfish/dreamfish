<?php

require_once 'lib/limonade.php';

function configure()
{
  option('env', ENV_DEVELOPMENT);
}

 
dispatch('/', 'index');
function index()
{
  //var_dump($wgOut);
  //$wgOut->addHTML('blah');
  return html('foo');
}

run();