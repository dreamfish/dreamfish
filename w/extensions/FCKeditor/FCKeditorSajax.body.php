<?php

function wfSajaxGetMathUrl( $term ) {
	$originalLink = MathRenderer::renderMath( $term );

	if (false == strpos($originalLink, "src=\"")) {
		return "";
	}

	$srcPart = substr($originalLink, strpos($originalLink, "src=")+ 5);
	$url = strtok($srcPart, '"');

	return $url;
}

function wfSajaxGetImageUrl( $term ) {
	global $wgExtensionFunctions, $wgTitle;

	$options = new FCKeditorParserOptions();
	$options->setTidy(true);
	$parser = new FCKeditorParser();

	if (in_array("wfCite", $wgExtensionFunctions)) {
		$parser->setHook('ref', array($parser, 'ref'));
		$parser->setHook('references', array($parser, 'references'));
	}
	$parser->setOutputType(OT_HTML);
	$originalLink = $parser->parse("[[Image:".$term."]]", $wgTitle, $options)->getText();
	if (false == strpos($originalLink, "src=\"")) {
		return "";
	}

	$srcPart = substr($originalLink, strpos($originalLink, "src=")+ 5);
	$url = strtok($srcPart, '"');

	return $url;
}

function wfSajaxSearchSpecialTagFCKeditor($empty) {
	global $wgParser, $wgRawHtml;

	$ret = "nowiki\nincludeonly\nonlyinclude\nnoinclude\ngallery\n";
	if( $wgRawHtml )
	{
		$ret.="html\n";
	}
	$wgParser->firstCallInit();
	foreach ($wgParser->getTags() as $h) {
		if (!in_array($h, array("pre", "math", "ref", "references"))) {
			$ret .= $h ."\n";
		}
	}
	$arr = explode("\n", $ret);
	sort($arr);
	$ret = implode("\n", $arr);

	return $ret;
}

function wfSajaxSearchImageFCKeditor( $term ) {
	global $wgContLang, $wgOut;
	$limit = 10;

	$term = $wgContLang->checkTitleEncoding( $wgContLang->recodeInput( js_unescape( $term ) ) );
	$term1 = str_replace( ' ', '_', $wgContLang->ucfirst( $term ) );
	$term2 = str_replace( ' ', '_', $wgContLang->lc( $term ) );
	$term3 = str_replace( ' ', '_', $wgContLang->uc( $term ) );
	$term4 = str_replace( ' ', '_', $wgContLang->ucfirst( $term2 ) );
	$term = $term1;

	if ( strlen( str_replace( '_', '', $term ) )<3 )
	return "";

	$db = wfGetDB( DB_SLAVE );
	$res = $db->select( 'page', 'page_title',
	array(  'page_namespace' => NS_IMAGE,
	"page_title LIKE '%". $db->strencode( $term1 ) ."%'".
	"OR (page_title LIKE '%". $db->strencode( $term2 ) ."%') ".
	"OR (page_title LIKE '%". $db->strencode( $term3 ) ."%') ".
	"OR (page_title LIKE '%". $db->strencode( $term4 ) ."%') " ),
	"wfSajaxSearch",
	array( 'LIMIT' => $limit+1 )
	);

	$ret = "";
	$i = 0;
	while ( ( $row = $db->fetchObject( $res ) ) && ( ++$i <= $limit ) ) {
		$ret .= $row->page_title ."\n";
	}

	$term = htmlspecialchars( $term );

	return $ret;
}

function wfSajaxSearchArticleFCKeditor( $term ) {
	global $wgContLang, $wgOut, $wgExtraNamespaces;
	$limit = 10;
	$ns = NS_MAIN;

	$term = $wgContLang->checkTitleEncoding( $wgContLang->recodeInput( js_unescape( $term ) ) );

	if (strpos(strtolower($term), "category:") === 0) {
		$ns = NS_CATEGORY;
		$term = substr($term, 9);
		$prefix = "Category:";
	}
	else if (strpos(strtolower($term), ":category:") === 0) {
		$ns = NS_CATEGORY;
		$term = substr($term, 10);
		$prefix = ":Category:";
	}
	else if (strpos(strtolower($term), "media:") === 0) {
		$ns = NS_IMAGE;
		$term = substr($term, 6);
		$prefix = "Media:";
	}
	else if (strpos(strtolower($term), ":image:") === 0) {
		$ns = NS_IMAGE;
		$term = substr(strtolower($term), 7);
		$prefix = ":Image:";
	}
	else if ( strpos($term,":") && is_array($wgExtraNamespaces )) {
		$pos = strpos($term,":");
		$find_ns = array_search(substr($term,0,$pos),$wgExtraNamespaces);
		if ($find_ns) {
			$ns = $find_ns;
			$prefix = substr($term,0,$pos+1);
			$term = substr($term,$pos+1);
		}
	}

	$term1 = str_replace( ' ', '_', $wgContLang->ucfirst( $term ) );
	$term2 = str_replace( ' ', '_', $wgContLang->lc( $term ) );
	$term3 = str_replace( ' ', '_', $wgContLang->uc( $term ) );
	$term4 = str_replace( ' ', '_', $wgContLang->ucfirst( $term2 ) );
	$term = $term1;

	if ( strlen( str_replace( '_', '', $term ) )<3 ) {
		return "";
	}

	$db = wfGetDB( DB_SLAVE );
	$res = $db->select( 'page', 'page_title',
	array(  'page_namespace' => $ns,
	"page_title LIKE '%". $db->strencode( $term1 ) ."%' ".
	"OR (page_title LIKE '%". $db->strencode( $term2 ) ."%') ".
	"OR (page_title LIKE '%". $db->strencode( $term3 ) ."%') ".
	"OR (page_title LIKE '%". $db->strencode( $term4 ) ."%') " ),
	"wfSajaxSearch",
	array( 'LIMIT' => $limit+1 )
	);

	$ret = "";
	$i = 0;
	while ( ( $row = $db->fetchObject( $res ) ) && ( ++$i <= $limit ) ) {
		if (isset($prefix) && !is_null($prefix)) {
			$ret .= $prefix;
		}
		$ret .= $row->page_title ."\n";
	}

	$term = htmlspecialchars( $term );

	return $ret;
}

function wfSajaxSearchCategoryFCKeditor()
{
	global $wgContLang, $wgOut;
	$ns = NS_CATEGORY;
	$db =& wfGetDB( DB_SLAVE );
	$m_sql="SELECT tmpSelectCat1.cl_to AS title FROM ".$db->tableName('categorylinks')." AS tmpSelectCat1 ".
		"LEFT JOIN ".$db->tableName('page')." AS tmpSelectCatPage ON ( tmpSelectCat1.cl_to = tmpSelectCatPage.page_title ".
		"AND tmpSelectCatPage.page_namespace =$ns ) ".
		"LEFT JOIN ".$db->tableName('categorylinks')." AS tmpSelectCat2 ON tmpSelectCatPage.page_id = tmpSelectCat2.cl_from ".
		"WHERE tmpSelectCat2.cl_from IS NULL GROUP BY tmpSelectCat1.cl_to";

	$res = $db->query($m_sql,__METHOD__ );

	$ret = "";
	$i=0;
	while ( ( $row = $db->fetchObject( $res ) ) ) {
		$ret .= $row->title ."\n";
		$sub = explode("\n",wfSajaxSearchCategoryChildrenFCKeditor($row->title));
		foreach($sub as $subrow)if(strlen($subrow)>0)$ret.=" ".$subrow."\n";
	}

	return $ret;
}

function wfSajaxSearchCategoryChildrenFCKeditor($m_root)
{
	global $wgContLang, $wgOut;
	$limit = 50;
	$ns = NS_CATEGORY;
	$m_root = str_replace("'","\'",$m_root);
	$db =& wfGetDB( DB_SLAVE );
	$m_sql ="SELECT tmpSelectCatPage.page_title AS title FROM ".$db->tableName('categorylinks')." AS tmpSelectCat ".
			"LEFT JOIN ".$db->tableName('page')." AS tmpSelectCatPage ON tmpSelectCat.cl_from = tmpSelectCatPage.page_id ".
			"WHERE tmpSelectCat.cl_to LIKE '$m_root' AND tmpSelectCatPage.page_namespace = $ns";


	$res = $db->query($m_sql,__METHOD__ );

	$ret = "";
	$i=0;
	while ( ( $row = $db->fetchObject( $res ) ) ) {
		$ret .= $row->title ."\n";
		$sub = explode("\n",wfSajaxSearchCategoryChildrenFCKeditor($row->title));
		foreach($sub as $subrow)if(strlen($subrow)>0)$ret.=" ".$subrow."\n";

	}

	return $ret;
}

function wfSajaxSearchTemplateFCKeditor($empty) {
	global $wgContLang, $wgOut;
	$ns = NS_TEMPLATE;

	$db = wfGetDB( DB_SLAVE );
	$options['ORDER BY'] = 'page_title';
	$res = $db->select( 'page', 'page_title',
	array( 'page_namespace' => $ns),
	"wfSajaxSearch",
	$options
	);

	$ret = "";
	while ( $row = $db->fetchObject( $res ) ) {
		$ret .= $row->page_title ."\n";
	}

	return $ret;
}

function wfSajaxWikiToHTML( $wiki ) {
	global $wgTitle;

	$options = new FCKeditorParserOptions();
	$options->setTidy(true);
	$parser = new FCKeditorParser();
	$parser->setOutputType(OT_HTML);

	wfSajaxToggleFCKeditor('show');		//FCKeditor was switched to visible
	return str_replace("<!-- Tidy found serious XHTML errors -->", "", $parser->parse($wiki, $wgTitle, $options)->getText());
}

function wfSajaxToggleFCKeditor($data) {
	global $wgFCKEditorSaveUserSetting;

	if($data == 'show'){
		$_SESSION['showMyFCKeditor'] = RTE_VISIBLE;	//visible last time
	}
	else {
		$_SESSION['showMyFCKeditor'] = 0;	//invisible
	}
	return "SUCCESS";
}
