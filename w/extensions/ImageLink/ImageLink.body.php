<?php
/**
 * @author Jean-Lou Dupont
 * @package ImageLink
 * @version 1.7.1
 * @Id $Id: ImageLink.body.php 1198 2008-07-14 10:34:54Z jeanlou.dupont $
 */
//<source lang=php>*/
class ImageLink
{
	// constants.
	const thisName = 'ImageLink';
	const thisType = 'other';
	
	var $links;

	// For Messages
	static $msg = array();

	// Error Codes
	const codeInvalidTitleImage = 0;
	const codeInvalidTitleLink  = 1;
	const codeArticleNotExist   = 2;
	const codeLinkLess          = 3;
	const codeImageNotExist		= 4;
	const codeDefaultNotProvided= 5;
	const codeMissingParameter  = 6;
	const codeEmptyList  		= 7;
	const codeRestrictedParam   = 8;
	const codeListEmpty         = 9;
	
	/* Parameters for {{#img}} magic word
	 * 
	 * m: mandatory parameter
	 * s: sanitization required
	 * l: which parameters to pick from list
	 * d: default value
	 */
	static $parameters = array(
		'image'		=> array( 'm' => true,  's' => false, 'l' => false, 'd' => null ),
		'default'	=> array( 'm' => false, 's' => false, 'l' => false, 'd' => null ),		
		'page'		=> array( 'm' => false, 's' => false, 'l' => false, 'd' => '' ),
		'content'	=> array( 'm' => false, 's' => false, 'l' => false, 'd' => null, 'dq' => true, 'sq' => true ),
		'target'	=> array( 'm' => false, 's' => false, 'l' => false, 'd' => null, 'dq' => true, 'sq' => true ),
		'alt'		=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true ),
		'height'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true  ),
		'width' 	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true  ),
		'title' 	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true  ),
		'border'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true  ),
		'class'		=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true  ),
		
		// Events
		// Restricted parameters
		'onchange'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'onsubmit'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'onreset'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'onselect'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),

		'onblur'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'onfocus'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		
		'onkeydown'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'onkeyup'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'onkeypress'=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),

		'onclick'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'ondblclick'=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),

		'onmousedown'=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'onmousemove'=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'onmouseout' => array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'onmouseover'=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'onmouseup'	 => array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
	);
	/* Parameters for {{#iconlink}} magic word
	 * 
	 * m: mandatory parameter
	 * s: sanitization required
	 * l: which parameters to pick from list
	 * d: default value
	 */
	static $parametersIconLink = array(
		'site'		 => array( 'm' => true,  's' => false, 'l' => false, 'd' => '' ),
		'domaincheck'=> array( 'm' => false,'s' => false, 'l' => false,  'd' => 'n' ),		
		
		// same as for #img
		'default'	=> array( 'm' => false, 's' => false, 'l' => false, 'd' => null ),		
		'target'	=> array( 'm' => false, 's' => false, 'l' => false, 'd' => null, 'dq' => true, 'sq' => true ),		
		'content'	=> array( 'm' => false, 's' => false, 'l' => false, 'd' => null, 'dq' => true, 'sq' => true ),		
		'alt'		=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true ),
		'height'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true  ),
		'width' 	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true  ),
		'alt'		=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true  ),
		'title' 	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true  ),
		'border'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true  ),
		'class'		=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true  ),
		
		// Events
		// Restricted parameters
		'onchange'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'onsubmit'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'onreset'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'onselect'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),

		'onblur'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'onfocus'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		
		'onkeydown'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'onkeyup'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'onkeypress'=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),

		'onclick'	=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'ondblclick'=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),

		'onmousedown'=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'onmousemove'=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'onmouseout' => array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'onmouseover'=> array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
		'onmouseup'	 => array( 'm' => false, 's' => true,  'l' => true,  'd' => null, 'dq' => true, 'sq' => true, 'r' => true  ),
	);

	/**
	 * Initialize the messages
	 */
	public function __construct()
	{
		global $wgMessageCache;

		foreach( self::$msg as $key => $value )
			$wgMessageCache->addMessages( self::$msg[$key], $key );		
	}	 
	/**
	 * legacy parser function... please use #img instead
	 * @deprecated 
	 */
	public function mg_imagelink( &$parser, $img, $page='',  							// mandatory parameters  
								$alt=null, $width=null, $height=null, $border=null, $title = null )// optional parameters
	/**
	 *  $img  = image reference i.e. a valid image name e.g. "New Clock.gif" 
	 *  $page = page reference i.e. a valid page name e.g. "Admin:Show Time"
	 *
	 * {{#imagelink:New Clock.gif|Admin:Show Time|alternate text}}
	 */
	{
		$html = $this->buildHTML( $img, $page, $alt, $width, $height, $border, $title );
		if ($this->isError( $html ))
			return $this->getErrorMsg( $html );
		return array( $html, 'noparse' => true, 'isHTML' => true );		
	}
	/**
	 * Can be used with [[Extension:ParserPhase2]]
	 */
	public function mg_imagelink_raw( &$parser, $img, $page='',  							// mandatory parameters  
								$alt=null, $width=null, $height=null, $border=null, $title = null )// optional parameters
	{
		$html = $this->buildHTML( $img, $page, $alt, $width, $height, $border, $title );
		if ($this->isError( $html ))
			return $this->getErrorMsg( $html );
		return $html;
	}
	/**
	 * This method builds the HTML code relative to the required imagelink
	 */
	protected function buildHTML( $img, $page,  							// mandatory parameters  
								$alt=null, $width=null, $height=null, $border=null, $title = null )
	{
		$iURL = $this->getImageURL( $img );
		if ($this->isError( $iURL ))
			return $iURL;
		
		// prepare for 'link-less' case ... if required.
		$anchor_open = '';
		$anchor_close = '';
		
		$ret = $this->getLinkToPageAnchor( $page, $anchor_open, $anchor_close );
		if ($this->isError( $ret ) && ( $ret !== self::codeLinkLess))
			return $ret;
		
		// sanitize the input
		$alt    = htmlspecialchars( $alt );
		$width  = htmlspecialchars( $width );
		$height = htmlspecialchars( $height );
		$border = htmlspecialchars( $border );
		$title = htmlspecialchars( $title );		
								
		// Optional parameters
		if ($alt    !== null)	$alt    = "alt='${alt}'"; 		else $alt='';
		if ($width  !== null)	$width  = "width='${width}'"; 	else $width='';
		if ($height !== null)	$height = "height='${height}'";	else $height='';
		if ($border !== null)	$border = "border='${border}'";	else $border='';
		if ($title  !== null)	$title =  "title='${title}'";	else $title='';		

		// let's put an easy marker that we can 'safely' find once we need to render the HTML
		return $anchor_open."<img src='${iURL}' $alt $width $height $border $title />".$anchor_close;
	}
	/**
	 * Returns the URL of the specified Image page
	 * Reverts to default Image page IFF the title isn't an interwiki one
	 */
	protected function getImageURL( &$img, &$default = null )
	{
		$iURL = $this->getImageURLreal( $img );
		
		// try out the specified image page name and
		// revert to default if it does not exists
		if ( ($iURL===self::codeInvalidTitleImage) || ($iURL===self::codeImageNotExist) )
		{
			if ( $default === null )
				return self::codeDefaultNotProvided;
				
			// if this one fails, not much we can do...
			$iURL = $this->getImageURLreal( $default );
		}
		
		return $iURL;	
	}
	/**
	 * Really returns an URL for a given image page.
	 */
	protected function getImageURLreal( &$img )
	{
		$ititle = Title::newFromText( $img );

		// this really shouldn't happen... not much we can do here.		
		if (!is_object($ititle)) 
			return self::codeInvalidTitleImage;

		// check if we are dealing with an InterWiki link
		if ( $ititle->isLocal() )
		{
			$image = Image::newFromName( $img );
			if ( !$image->exists() ) 
				return self::codeImageNotExist;
	
			$iURL = $image->getURL();
		}
		else
			$iURL = $ititle->getFullURL();

		return $iURL;		
	} 
	/**
	 * getLinkToPage
	 */
	protected function getLinkToPageAnchor( &$page, &$anchor_open, &$anchor_close, $target = null, $content = null ) {
	
		// check if we are asked to render a 'link-less' element
		if (empty( $page ))
			return self::codeLinkLess;
			
		$ptitle = Title::newFromText( $page );

		// Extract fragment i.e. #section-on-page
		$fragment = null;
		$fragmentStart = stripos( $page, '#' );
		if ( $fragmentStart !== false ) {
			$fragment = substr( htmlspecialchars( $page ), $fragmentStart );
		}
		
		// this might happen in templates...
		if (!is_object( $ptitle ))
			return self::codeInvalidTitleLink;
				
		if ( $ptitle->isLocal() ) {
			// check if the local article exists
			if ( !$ptitle->exists() )
				return self::codeArticleNotExist;
				
			$tURL = $ptitle->getLocalUrl();
			$aClass='';
			 			
		} else {
			// we can't know easily what is at the end of this URL...
			$tURL = $ptitle->getFullURL();
			$aClass = 'class="extiw"';
		}
		
		//  Is there a # already? Strip it.
		$tFragmentStart = stripos( $tURL, '#' );
		if ( $tFragmentStart !== false ) {
			$tURL = substr( $tURL, 0, $tFragmentStart );
		}
		// Add fragment back to url
		//  This is required to support things like "#tab=section"		
		$tURL .= $fragment;
		
		$this->formatLinkAnchor( $tURL, $aClass, $anchor_open, $anchor_close, $target, $content );

		return true;
	}
	/**
	 * Formats an HTML anchor
	 * 
	 * @return $this
	 * @param $url string
	 * @param $anchor_open string
	 * @param $anchor_close string
	 */	
	protected function formatLinkAnchor( &$url, &$classe, &$anchor_open, &$anchor_close, 
										$target = null, $content = null )
	{
		$_target = !is_null( $target ) ? 'target="'.$target.'"':null;
			
		$anchor_open = "<a ".$classe." $_target href='${url}'>";
		$anchor_close = " $content</a>";
		return $this;
	}	
	
	/**
	 * {{#img:  image=image-page 
	 *			[|page=page-to-link-to] 
	 *			[|alt=alternate-text]
	 *			[|target=target-text] 
 	 *			[|content=anchor-text] 
	 *			[|height=height-parameter]
	 *			[|width=width-parameter]	 
	 *			[|border=border-parameter]
	 *			[|class=class-parameter]
	 *			[|title=title-parameter]
	 *			[|default=image-page-used-for-default]
	 *			[|onchange=onchange-handler]
	 *			[|onsubmit=onsubmit-handler]	 
	 *			[|onreset=onreset-handler]	 
	 *			[|onselect=onselect-handler]	 
	 *			[|onblur=onblur-handler]	 
	 *			[|onfocus=onfocus-handler]	 
	 *			[|onkeydown=onkeydown-handler]	 
	 *			[|onkeyup=onkeyup-handler]	 
	 *			[|onkeypress=onkeypress-handler]	 
	 *			[|onclick=onclick-handler]	 
	 *			[|ondblclick=ondblclick-handler]
	 *			[|onmousedown=onmousedown-handler]
	 *			[|onmousemove=onmousemove-handler]	 
	 *			[|onmouseout=onmouseout-handler]	 	 
	 *			[|onmouseover=onmouseover-handler]	 	 	 
	 *			[|onmouseup=onmouseup-handler]	 	 	 
	 * }} 
	 */
	public function mg_img( &$parser )
	{
		$params = func_get_args();
		
		$liste = StubManager::processArgList( $params, true );
		
		$sliste= ExtHelper::doListSanitization( $liste, self::$parameters );
		if (empty( $sliste ))
			return $this->getErrorMsg( self::codeListEmpty );
		
		if (!is_array( $sliste ))
			return $this->getErrorMsg( self::codeMissingParameter, $sliste);
		
		ExtHelper::doSanitization( $sliste, self::$parameters );
		
		$result = ExtHelper::checkListForRestrictions( $sliste, self::$parameters );
		$title  = $parser->mTitle;
		
		// first check for restricted parameter usage
		$check = $this->checkRestrictionStatus( $title, $result );
		if ($this->isError( $check ))
			return $this->getErrorMsg( $check, $result );
		
		$html = $this->buildHTMLfromList( $sliste, self::$parameters );		
		if ($this->isError( $html ))
			return $this->getErrorMsg( $html );
					
		return array( $html, 'noparse' => true, 'isHTML' => true );			
	}
	/**
	 * Creates an 'icon link':
	 *  Fetches a site's ''favicon.ico''
	 * 
	 * {{#iconlink:  
	 *			[|site=url-of-page] 
	 *			[|domaincheck=y|n] 
	 *			[|target=target-text] 
 	 *			[|content=anchor-text] 
	 *			[|alt=alternate-text]
	 *			[|height=height-parameter]
	 *			[|width=width-parameter]	 
	 *			[|border=border-parameter]	 
	 *			[|class=class-parameter]	 
	 *			[|title=title-parameter]
	 *			[|default=image-page-used-for-default]
	 *			[|onchange=onchange-handler]
	 *			[|onsubmit=onsubmit-handler]	 
	 *			[|onreset=onreset-handler]	 
	 *			[|onselect=onselect-handler]	 
	 *			[|onblur=onblur-handler]	 
	 *			[|onfocus=onfocus-handler]	 
	 *			[|onkeydown=onkeydown-handler]	 
	 *			[|onkeyup=onkeyup-handler]	 
	 *			[|onkeypress=onkeypress-handler]	 
	 *			[|onclick=onclick-handler]	 
	 *			[|ondblclick=ondblclick-handler]
	 *			[|onmousedown=onmousedown-handler]
	 *			[|onmousemove=onmousemove-handler]	 
	 *			[|onmouseout=onmouseout-handler]	 	 
	 *			[|onmouseover=onmouseover-handler]	 	 	 
	 *			[|onmouseup=onmouseup-handler]	 	 	 
	 * }} 
	 */
	public function mg_iconlink( &$parser )
	{
		$params = func_get_args();
		
		$liste = StubManager::processArgList( $params, true );
		
		$sliste= ExtHelper::doListSanitization( $liste, self::$parametersIconLink );
		if (empty( $sliste ))
			return $this->getErrorMsg( self::codeListEmpty );
		
		if (!is_array( $sliste ))
			return $this->getErrorMsg( self::codeMissingParameter, $sliste);
		
		ExtHelper::doSanitization( $sliste, self::$parameters );
		
		$result = ExtHelper::checkListForRestrictions( $sliste, self::$parametersIconLink );
		$title  = $parser->mTitle;
		
		// first check for restricted parameter usage
		$check = $this->checkRestrictionStatus( $title, $result );
		if ($this->isError( $check ))
			return $this->getErrorMsg( $check, $result );
		
		// Normalize domainCheck parameter
		$site = $liste[ 'site' ];
		$domainCheckParam = @$liste['domaincheck'];
		$domainCheck = $this->extractBoolean( $domainCheckParam );
		
		$iconURL = $this->getFavicon( $site, $domainCheck );
		
		// Build the HTML element
		$html = $this->buildHTMLfromList( $sliste, self::$parametersIconLink, $iconURL );	
		if ($this->isError( $html ))
			return $this->getErrorMsg( $html );

		return array( $html, 'noparse' => true, 'isHTML' => true );			
	}
	
	// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	// IconLink functionality helpers	
	// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%	
	/**
	 * Retrieves the 'favicon' from the site pointed to by $url.
	 * Currently, only the /favicon.ico icon is looked for.
	 * 
	 * @TODO: parse the root web page for the <link 'rel' ...>
	 * 
	 * @return string URL to favicon
	 * @return null if none exists or isn't available
	 * @param $url string
	 */
	protected function getFavicon( &$url, $domainCheck = true )
	{
		$icoURL = $url.'/favicon.ico';
		
		// if we were to get NULL as return code, then we couldn't
		// provide much feedback in terms of error checking...
		if ( $this->validateURI( $icoURL, $domainCheck ) === false )	
			return null;

		// check if the icon uri exists
		$responseHeader = @get_headers( $icoURL );
		$exists = !( strpos( $responseHeader[0], '200 OK' ) === FALSE );
		
		if ( !$exists )
			return null;
		
		return $icoURL;
	}
	/**
	 * Validates a given URI using PEAR::Validate package
	 * 
	 * @param string $uri
	 * @return boolean (if the function was able to perform the validation)
	 * @return null if PEAR::Validate package isn't available
	 */	
	protected function validateURI( &$uri, $domainCheck = true )
	{
		@include_once 'Validate.php';
		if (!class_exists( 'Validate' ))
			return null;
			
		$validate = new Validate;
		
		return $validate->uri( $uri, array( 'domain_check' => $domainCheck ) );
	}
	/**
	 * Extracts a 'boolean'
	 * 
	 * @return boolean
	 * @param $param string
	 */
	protected function extractBoolean( $param )
	{
		$lc = strtolower( $param );
		$result = false;
		switch( $lc )
		{
			case 'y':
			case '1':
			case 'yes':
				$result = true;
				break;
		}
		return $result;
	}
	// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	// 	
	// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%	
	
	/**
	 * @return false invalid image page title
	 * @return null  invalid target title
	 * @return -1    local article does not exist
	 */
	protected function buildHTMLfromList( &$liste, &$ref_liste, $img_url = null )
	{
		if ( $img_url === null )
		{
			$img_url = $this->getImageURL( $liste['image'], $liste['default'] );
			if ($this->isError( $img_url ))
				return $img_url;
		}
		
		// <a> anchor 'target' attribute
		$target  = @!is_null( $liste['target'] )  ? $liste['target'] :null;
		
		// text between the <a> $content </a>
		$content = @!is_null( $liste['content'] ) ? $liste['content']:null;
		
		$page = null;
		$link = null;

		// prepare for 'link-less' case ... if required.
		$anchor_open = '';
		$anchor_close = '';
		
		// #img case
		if ( isset( $liste['page'] ))
		{
			$page = $liste['page'];
			// returns a formatted <a> anchor with href etc.
			$r = $this->getLinkToPageAnchor( $page, $anchor_open, $anchor_close, $target, $content );
			if ( $this->isError( $r ) && ( $r !== self::codeLinkLess) )
				return $r;
		}
		
		// #iconlink case
		if ( isset( $liste['site'] ))
		{
			$link = $liste['site'];
			$classe = 'class="extiw"';
			$this->formatLinkAnchor( $link, $classe, $anchor_open, $anchor_close, $target, $content );
		}

		$params = ExtHelper::buildList( $liste, $ref_liste );
		
		return $anchor_open."<img src='${img_url}' $params />".$anchor_close;
	}
	/**
	 * Returns 'true' if the code provided constitute an error code
	 */
	protected function isError( $code )
	{
		return is_numeric( $code );
	}
	/**
	 * Returns the corresponding error message
	 */
	protected function getErrorMsg( $code, $param = null )
	{
		return wfMsgForContent( 'imagelink'.$code, $param );	
	}
	/**
	 * Verifies a page's edit protection status 
	 * 
	 * @return bool false means ''no-restriction''
	 */
	protected function checkRestrictionStatus( &$title, $result )
	{
		$protected = $title->isProtected('edit');

		// if the page is protected, then anything goes!
		if ( $protected === true )
			return false;
		
		// check if we are in the MediaWiki namespace
		// i.e. edit protected by default.
		if ( $title->getNamespace() === NS_MEDIAWIKI )
			return false;
		
		// empty list.
		if ( $result === null )
			return false;
		
		// page is not protected... are there any restricted parameters then?
		return ( $result !== false ) ? self::codeRestrictedParam:false;
	}
} // end class definition.

// ugly but effective.
require 'ImageLink.i18n.php';
//</source>