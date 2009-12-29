<?php
/******
extensions/mailman.php
*******/ 
$wgExtensionFunctions[] = "wfMailmanExtension"; 
 
function wfMailmanExtension() {
    global $wgParser;
    $wgParser->setHook( "mailman", "printMailmanForm" );
}
 
function printMailmanForm( $input, $argv ) {

    global $wgParser;
    $wgParser->disableCache(); 

    $output = "<form action=\"$input\" method=\"post\">".
      "<input name=\"email\" type=\"text\" />".
      "<input type=\"submit\" value=\"Subscribe\">".
      "</form>";
    return $output;
}
 
?>
