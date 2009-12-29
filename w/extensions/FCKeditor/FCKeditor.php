<?php

# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
	echo <<<HEREDOC
To install FCKeditor extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/FCKeditor/FCKeditor.php" );
HEREDOC;
	exit( 1 );
}

/*
This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

require_once $IP . "/includes/GlobalFunctions.php";
require_once $IP . "/includes/EditPage.php";

if (version_compare("1.14alpha", $wgVersion, "<=")) {
    require_once $IP . "/includes/parser/ParserOptions.php";
    require_once $IP . "/includes/parser/Parser.php";
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "mw14/Parser_OldPP.body.php";
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "mw12/FCKeditorParserWrapper.body.php";
}
else if (version_compare("1.13alpha", $wgVersion, "<=")) {
    require_once $IP . "/includes/parser/ParserOptions.php";
    require_once $IP . "/includes/parser/Parser_OldPP.php";
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "mw12/FCKeditorParserWrapper.body.php";
}
else if (version_compare("1.12", $wgVersion, "<")) {
    require_once $IP . "/includes/ParserOptions.php";
    require_once $IP . "/includes/Parser_OldPP.php";
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "mw12/FCKeditorParserWrapper.body.php";
}
else {
    require_once $IP . "/includes/ParserOptions.php";
    require_once $IP . "/includes/Parser.php";
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "mw11/FCKeditorParserWrapper.body.php";
}

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "FCKeditorParser.body.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "FCKeditorSajax.body.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "FCKeditorParserOptions.body.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "FCKeditorSkin.body.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "FCKeditorEditPage.body.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "FCKeditor.body.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "fckeditor" . DIRECTORY_SEPARATOR . "fckeditor.php";

if (empty ($wgFCKEditorExtDir)) {
    $wgFCKEditorExtDir = "extensions/FCKeditor";
}
if (empty ($wgFCKEditorDir)) {
    $wgFCKEditorDir = "extensions/FCKeditor/fckeditor";
}
if (empty ($wgFCKEditorToolbarSet)) {
    $wgFCKEditorToolbarSet = "Wiki";
}
if (empty ($wgFCKEditorHeight)) {
    $wgFCKEditorHeight = "0"; // "0" for automatic ("300" minimum).
}
/**
 * Enable use of AJAX features.
 */
$wgUseAjax = true;
$wgAjaxExportList[] = 'wfSajaxSearchImageFCKeditor';
$wgAjaxExportList[] = 'wfSajaxSearchArticleFCKeditor';
$wgAjaxExportList[] = 'wfSajaxSearchCategoryFCKeditor';
$wgAjaxExportList[] = 'wfSajaxWikiToHTML';
$wgAjaxExportList[] = 'wfSajaxGetImageUrl';
$wgAjaxExportList[] = 'wfSajaxGetMathUrl';
$wgAjaxExportList[] = 'wfSajaxSearchTemplateFCKeditor';
$wgAjaxExportList[] = 'wfSajaxSearchSpecialTagFCKeditor';
$wgAjaxExportList[] = 'wfSajaxToggleFCKeditor';

$wgExtensionCredits['other'][] = array(
    'name' => 'FCKeditor',
    'author' => array('FCKeditor.net', 'Wikia'),
    'version' => 'fckeditor/mw-extension $Rev$ 2008',
    'url' => 'http://www.mediawiki.org/wiki/Extension:FCKeditor_%28by_FCKeditor_and_Wikia%29',
    'description' => 'FCKeditor extension for editing wiki pages (WYSIWYG editor)'
);

$fckeditor = new FCKeditor("fake");
$wgFCKEditorIsCompatible = $fckeditor->IsCompatible();

$oFCKeditorExtension = new FCKeditor_MediaWiki();

$oFCKeditorExtension->registerHooks();

$wgDefaultUserOptions['riched_use_toggle'] = 1;
$wgDefaultUserOptions['riched_start_disabled'] = 0;
$wgDefaultUserOptions['riched_use_popup'] = 1;
$wgDefaultUserOptions['riched_toggle_remember_state'] = 1;
