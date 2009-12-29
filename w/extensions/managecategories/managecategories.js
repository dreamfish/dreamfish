/**
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
*/

/**
	evaluates select box
	create markup and put it into article if a value from selecbox is chosen
*/
function insertCategoryTags1() {
	
	for (var i=0; i<document.editform.categories.length; i++) {
		if (document.editform.categories.options[i].selected)
			document.editform.wpTextbox1.value += "[[Category:"+document.editform.categories.options[i].text+"]]";
	}

	document.editform.wpTextbox1.focus();

}

/**
	evaluates check boxes 
	remove markup is a current article category is beeing deselected
	create markup and put it into article if a current article category is beeing selected
*/
function insertCategoryTags2(obj) {

  if (obj.checked) // radio box checked 
		document.editform.wpTextbox1.value += "[[Category:"+obj.value+"]]";
	else { // radio box not checked
		// search and remove markups
		var pattern = "\[\[Category:" + obj.value + "\]\]";
		var myregex = new RegExp(obj.value+"\]\]","g");
		var res = document.editform.wpTextbox1.value.match(myregex);

		for (var i=0;i<res.length;i++) {
			document.editform.wpTextbox1.value = document.editform.wpTextbox1.value.replace(pattern,"");
		}
	}			
	document.editform.wpTextbox1.focus();	
}

/**
	evaluates textfield
	create markup for a new category put it into article
*/
function insertCategoryTags3() {

	if(document.editform.newCategory.value != "enter new Category name")
		document.editform.wpTextbox1.value += "[[Category:"+document.editform.newCategory.value+"]]";

	document.editform.wpTextbox1.focus();

}
