<?php
# Copyright (C) 2007 Florian Mayrhuber <f_mayrhuber@gmx.at>
# 
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or 
# (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along
# with this program; if not, write to the Free Software Foundation, Inc.,
# 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
# http://www.gnu.org/copyleft/gpl.html


/**
	
	 managecategories is an extension for mediawiki that allows user-friendly 
	 categorization of new articles as well as modifying existing article-categorizations
	  
	 mangecategories is located beneath the main edit textarea and consists of:
	 - a select box with all categories of the wiki
	 - a text field to create a new category
	 - currently active categorizations represented with name and check boxes
	 
	 all user inputs and changes to these fields are transformed to valid
	 wiki markup and inserted into the edit textarea above..

*/

$wgExtensionCredits['other'][] = array(
	'name'		=> 'manageCategories',
	'author'	=> 'Florian Mayrhuber',
	'url'		=> 'http://www.mediawiki.org/wiki/Extension:manageCategories',
	'description'	=> 'Allows to categorize articles in a user-friendly way.'
);

if ( !defined('MEDIAWIKI') )
{
    die();
}

/**
	 * hook function
	 * asserts the main function to the hook
*/
$wgExtensionFunctions[] = 'wfEditCategory';

function wfEditCategory()
{

    // set up hooks
    global $wgHooks;
    $wgHooks['EditPage::showEditForm:initial'][] = 'fnShowCategoryBox';

}

/**
	* main function integrates html for imageplus into output
	* param: editpage
*/
function fnShowCategoryBox( $editPage )
{
	
	// output
	global $wgOut;
	
	// get articleid
    global $wgTitle;
    $id = $wgTitle->getArticleID();
	$wgOut->addScript("<script type=\"text/javascript\" src=\"extensions/managecategories/managecategories.js\"></script>\n");
        
	// get article data
    $dbr = &wfGetDB( DB_SLAVE );
    $categoryList = fnGetAllCategories( $dbr );
    $articleCategories = fnGetArticleCategories( $dbr, $id );
	
	// determine if articles is already categorized
	$reg = '&\[\[Category\:.*\]\]&is';
	if ( preg_match($reg,$editPage->textbox1) ) $categorized = true;
	
    // open tags and description
    $editPage->editFormTextAfterWarn .= "<table width=\"750\" border=\"0\">";
    //$editPage->editFormTextAfterWarn .= "<caption>Categories</caption>";	
    $editPage->editFormTextAfterWarn .= "<tr><th height=\"30\" width=\"350\" align=\"left\" colspan=\"2\" scope=\"col\">Add Categories</th>";   
    
    // article is categorized show caption
    if ($categorized) {
 		$editPage->editFormTextAfterWarn .= "<th height=\"30\" align=\"left\" scope=\"col\">
	 	<blockquote>Update Categories</blockquote></th></tr>";
	 }     
        
     // article is NOT categorized show nothing
    if (!$categorized) {
 		$editPage->editFormTextAfterWarn .= "<th height=\"20\" align=\"left\" scope=\"col\"> </th></tr>";
	 }     
	 
    $editPage->editFormTextAfterWarn .= "<tr>";
	
	// show all existing categories as select box
    $editPage->editFormTextAfterWarn .= "<td width=\"350\" height=\"20\">
	<select name=\"categories\" style=\"width:300px;border: 1px solid\">";
    
    while ( $row = $dbr->fetchObject($categoryList) )
    {
		$editPage->editFormTextAfterWarn .= "<option style=\"color:#000000;\">$row->cl_to</option>";
    }

    $editPage->editFormTextAfterWarn .= "</select></td>";    
	$editPage->editFormTextAfterWarn .= "<td width=\"100\"><input type=\"button\" style=\"width:75px\" value=\"add\" 
	onClick=\"insertCategoryTags1()\"/></td>";

	// show current categorys as check boxes 
	$editPage->editFormTextAfterWarn .= "<td width=\"350\" valign=\"top\" rowspan=\"3\" height=\"20\"><blockquote>";
    
    $categoryList = fnGetAllCategories( $dbr );
    
	while ( $current = $dbr->fetchObject($categoryList) )
    {
		if ( in_array($current->cl_to, $articleCategories) ) {
			$editPage->editFormTextAfterWarn .= "<p><input type=\"checkbox\" checked=\"checked\" value=$current->cl_to 
		    name=\"currentCategories[]\" onClick=\"insertCategoryTags2(this)\"/> $current->cl_to </p>";
		}	
	}
	
	$editPage->editFormTextAfterWarn .= "</blockquote></td></tr>";
	
	// input field for new category
	$editPage->editFormTextAfterWarn .= "<tr><td width=\"100\">
	<input name=\"newCategory\" type=\"text\" style=\"width:296px\" maxlength=\"30\" value=\"Enter new category name\"/></td>";	
	$editPage->editFormTextAfterWarn .= "<td width=\"100\"><input type=\"button\" style=\"width:75px\" value=\"create\" 
	onClick=\"insertCategoryTags3()\"/></td></tr>";
	$editPage->editFormTextAfterWarn .= "<tr><td>&nbsp;</td><td>&nbsp;</td>";	

	//closing tags
    $editPage->editFormTextAfterWarn .= "</tr></table><br>";
    
    return true;
}

/**
	* retrieve all Categories from the db using the mediawiki select wrapper
	* param: wiki db object
	* return: resultset with all categories 
*/ 
function fnGetAllCategories( $dbr )
{

     $res = $dbr->select('categorylinks', // FROM
        array('cl_to', 'cl_from'), // SELECT
        array(), $fname, array('GROUP BY' => 'cl_to'));// GROUP BY

    return $res;

}

/**
	* determine to which categories the current article
	* is asigned and return array with results  
	* param: wiki db object, article id
	* return: resultset with categories 
*/
function fnGetArticleCategories( $dbr, $id )
{

    $rs = array();

    $res = $dbr->select( 'categorylinks', // FROM
        array('cl_to', 'cl_from'), // SELECT
        array('cl_from' => $id) );// WHERE

    while ( $row = $dbr->fetchObject($res) )
    {
        $rs[] = $row->cl_to;
    }

    return $rs;

}

?>
