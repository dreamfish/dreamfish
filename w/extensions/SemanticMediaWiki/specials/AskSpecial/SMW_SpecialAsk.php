<?php
/**
 * @file
 * @ingroup SMWSpecialPage
 * @ingroup SpecialPage
 */

/**
 * @author Markus Krötzsch
 *
 * This special page for MediaWiki implements a customisable form for
 * executing queries outside of articles.
 *
 * @ingroup SMWSpecialPage
 * @ingroup SpecialPage
 */
class SMWAskPage extends SpecialPage {

	protected $m_querystring = '';
	protected $m_params = array();
	protected $m_printouts = array();
	protected $m_editquery = false;

	// MW 1.13 compatibilty
	private static $pipeseparator = '|';

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct('Ask');
		wfLoadExtensionMessages('SemanticMediaWiki');
	}

	function execute( $p ) {
		global $wgOut, $wgRequest, $smwgQEnabled, $smwgRSSEnabled, $smwgMW_1_14;
		wfProfileIn('doSpecialAsk (SMW)');
		if ($smwgMW_1_14) { // since MW 1.14.0 this is governed by a message
			SMWAskPage::$pipeseparator = wfMsgExt( 'pipe-separator' , 'escapenoentities' );
		}
		if (!$smwgQEnabled) {
			$wgOut->addHTML('<br />' . wfMsg('smw_iq_disabled'));
		} else {
			$this->extractQueryParameters($p);
			$this->makeHTMLResult();
		}
		SMWOutputs::commitToOutputPage($wgOut); // make sure locally collected output data is pushed to the output!
		wfProfileOut('doSpecialAsk (SMW)');
	}

	protected function extractQueryParameters($p) {
		// This code rather hacky since there are many ways to call that special page, the most involved of
		// which is the way that this page calls itself when data is submitted via the form (since the shape
		// of the parameters then is governed by the UI structure, as opposed to being governed by reason).
		global $wgRequest, $smwgQMaxInlineLimit;

		// First make all inputs into a simple parameter list that can again be parsed into components later.

		if ($wgRequest->getCheck('q')) { // called by own Special, ignore full param string in that case
			$rawparams = SMWInfolink::decodeParameters($wgRequest->getVal( 'p' ), false); // p is used for any additional parameters in certain links
		} else { // called from wiki, get all parameters
			$rawparams = SMWInfolink::decodeParameters($p, true);
		}
		// Check for q= query string, used whenever this special page calls itself (via submit or plain link):
		$this->m_querystring = $wgRequest->getText( 'q' );
		if ($this->m_querystring != '') {
			$rawparams[] = $this->m_querystring;
		}
		// Check for param strings in po (printouts), appears in some links and in submits:
		$paramstring = $wgRequest->getText( 'po' );
		if ($paramstring != '') { // parameters from HTML input fields
			$ps = explode("\n", $paramstring); // params separated by newlines here (compatible with text-input for printouts)
			foreach ($ps as $param) { // add initial ? if omitted (all params considered as printouts)
				$param = trim($param);
				if ( ($param != '') && ($param{0} != '?') ) {
					$param = '?' . $param;
				}
				$rawparams[] = $param;
			}
		}

		// Now parse parameters and rebuilt the param strings for URLs
		SMWQueryProcessor::processFunctionParams($rawparams,$this->m_querystring,$this->m_params,$this->m_printouts);
		// Try to complete undefined parameter values from dedicated URL params
		if ( !array_key_exists('format',$this->m_params) ) {
			if (array_key_exists('rss', $this->m_params)) { // backwards compatibility (SMW<=1.1 used this)
				$this->m_params['format'] = 'rss';
			} else { // default
				$this->m_params['format'] = 'broadtable';
			}
		}
		$sortcount = $wgRequest->getVal( 'sc' );
		if (!is_numeric($sortcount)) {
			$sortcount = 0;
		}
		if ( !array_key_exists('order',$this->m_params) ) {
			$this->m_params['order'] = $wgRequest->getVal( 'order' ); // basic ordering parameter (, separated)
			for ($i=0; $i<$sortcount; $i++) {
				if ($this->m_params['order'] != '') {
					$this->m_params['order'] .= ',';
				}
				$value = $wgRequest->getVal( 'order' . $i );
				$value = ($value == '')?'ASC':$value;
				$this->m_params['order'] .= $value;
			}
		}
		if ( !array_key_exists('sort',$this->m_params) ) {
			$this->m_params['sort'] = $wgRequest->getText( 'sort' ); // basic sorting parameter (, separated)
			for ($i=0; $i<$sortcount; $i++) {
				if ( ($this->m_params['sort'] != '') || ($i>0) ) { // admit empty sort strings here
					$this->m_params['sort'] .= ',';
				}
				$this->m_params['sort'] .= $wgRequest->getText( 'sort' . $i );
			}
		}
		// Find implicit ordering for RSS -- needed for downwards compatibility with SMW <=1.1
		if ( ($this->m_params['format'] == 'rss') && ($this->m_params['sort'] == '') && ($sortcount==0)) {
			foreach ($this->m_printouts as $printout) {
				if ((strtolower($printout->getLabel()) == "date") && ($printout->getTypeID() == "_dat")) {
					$this->m_params['sort'] = $printout->getTitle()->getText();
					$this->m_params['order'] = 'DESC';
				}
			}
		}
		if ( !array_key_exists('offset',$this->m_params) ) {
			$this->m_params['offset'] = $wgRequest->getVal( 'offset' );
			if ($this->m_params['offset'] == '')  $this->m_params['offset'] = 0;
		}
		if ( !array_key_exists('limit',$this->m_params) ) {
			$this->m_params['limit'] = $wgRequest->getVal( 'limit' );
			if ($this->m_params['limit'] == '') {
				 $this->m_params['limit'] = ($this->m_params['format'] == 'rss')?10:20; // standard limit for RSS
			}
		}
		$this->m_params['limit'] = min($this->m_params['limit'], $smwgQMaxInlineLimit);

		$this->m_editquery = ( $wgRequest->getVal( 'eq' ) != '' ) || ('' == $this->m_querystring );
	}

	protected function makeHTMLResult() {
		global $wgOut;
		$result = '';
		$result_mime = false; // output in MW Special page as usual

		// build parameter strings for URLs, based on current settings
		$urltail = '&q=' . urlencode($this->m_querystring);

		$tmp_parray = array();
		foreach ($this->m_params as $key => $value) {
			if ( !in_array($key,array('sort', 'order', 'limit', 'offset', 'title')) ) {
				$tmp_parray[$key] = $value;
			}
		}
		$urltail .= '&p=' . urlencode(SMWInfolink::encodeParameters($tmp_parray));
		$printoutstring = '';
		foreach ($this->m_printouts as $printout) {
			$printoutstring .= $printout->getSerialisation() . "\n";
		}
		if ('' != $printoutstring)          $urltail .= '&po=' . urlencode($printoutstring);
		if ('' != $this->m_params['sort'])  $urltail .= '&sort=' . $this->m_params['sort'];
		if ('' != $this->m_params['order']) $urltail .= '&order=' . $this->m_params['order'];

		if ($this->m_querystring != '') {
			$queryobj = SMWQueryProcessor::createQuery($this->m_querystring, $this->m_params, SMWQueryProcessor::SPECIAL_PAGE , $this->m_params['format'], $this->m_printouts);
			$res = smwfGetStore()->getQueryResult($queryobj);
			// try to be smart for rss/ical if no description/title is given and we have a concept query:
			if ($this->m_params['format'] == 'rss') {
				$desckey = 'rssdescription';
				$titlekey = 'rsstitle';
			} elseif ($this->m_params['format'] == 'icalendar') {
				$desckey = 'icalendardescription';
				$titlekey = 'icalendartitle';
			} else { $desckey = false; }
			if ( ($desckey) && ($queryobj->getDescription() instanceof SMWConceptDescription) &&
			     (!isset($this->m_params[$desckey]) || !isset($this->m_params[$titlekey])) ) {
				$concept = $queryobj->getDescription()->getConcept();
				if ( !isset($this->m_params[$titlekey]) ) {
					$this->m_params[$titlekey] = $concept->getText();
				}
				if ( !isset($this->m_params[$desckey]) ) {
					$dv = end(smwfGetStore()->getPropertyValues(SMWWikiPageValue::makePageFromTitle($concept), SMWPropertyValue::makeProperty('_CONC')));
					if ($dv instanceof SMWConceptValue) {
						$this->m_params[$desckey] = $dv->getDocu();
					}
				}
			}
			$printer = SMWQueryProcessor::getResultPrinter($this->m_params['format'], SMWQueryProcessor::SPECIAL_PAGE);
			$result_mime = $printer->getMimeType($res);
			if ($result_mime == false) {
				if ( $res->getCount() > 0 ) {
					$navigation = $this->getNavigationBar($res, $urltail);
					$result = '<div style="text-align: center;">' . $navigation;
					$result .= '</div>' . $printer->getResult($res, $this->m_params,SMW_OUTPUT_HTML);
					$result .= '<div style="text-align: center;">' . $navigation . '</div>';
				} else {
					$result = '<div style="text-align: center;">' . wfMsg('smw_result_noresults') . '</div>';
				}
			} else { // make a stand-alone file
				$result = $printer->getResult($res, $this->m_params,SMW_OUTPUT_FILE);
				$result_name = $printer->getFileName($res); // only fetch that after initialising the parameters
			}
		}

		if ($result_mime == false) {
			if ($this->m_querystring) {
				$wgOut->setHTMLtitle($this->m_querystring);
			} else {
				$wgOut->setHTMLtitle(wfMsg('ask'));
			}
			$result = $this->getInputForm($printoutstring, 'offset=' . $this->m_params['offset'] . '&limit=' . $this->m_params['limit'] . $urltail) . $result;
			$wgOut->addHTML($result);
		} else {
			$wgOut->disable();
			header( "Content-type: $result_mime; charset=UTF-8" );
			if ($result_name !== false) {
				header( "content-disposition: attachment; filename=$result_name");
			}
			print $result;
		}
	}


	protected function getInputForm($printoutstring, $urltail) {
		global $wgUser, $smwgQSortingSupport, $wgLang, $smwgResultFormats;
		$skin = $wgUser->getSkin();
		$result = '';

		if ($this->m_editquery) {
			$spectitle = Title::makeTitle( NS_SPECIAL, 'Ask' );
			$result .= '<form name="ask" action="' . $spectitle->escapeLocalURL() . '" method="get">' . "\n" .
			           '<input type="hidden" name="title" value="' . $spectitle->getPrefixedText() . '"/>';
			$result .= '<table style="width: 100%; "><tr><th>' . wfMsg('smw_ask_queryhead') . '</th><th>' . wfMsg('smw_ask_printhead') . '</th></tr>' .
			         '<tr><td><textarea name="q" cols="20" rows="6">' . htmlspecialchars($this->m_querystring) . '</textarea></td>' .
			         '<td><textarea name="po" cols="20" rows="6">' . htmlspecialchars($printoutstring) . '</textarea></td></tr></table>' . "\n";
			if ($smwgQSortingSupport) {
				if ( $this->m_params['sort'] . $this->m_params['order'] == '') {
					$orders = array(); // do not even show one sort input here
				} else {
					$sorts = explode(',', $this->m_params['sort']);
					$orders = explode(',', $this->m_params['order']);
					reset($sorts);
				}
				$i = 0;
				foreach ($orders as $order) {
					if ($i>0) {
						$result .= '<br />';
					}
					$result .=  wfMsg('smw_ask_sortby') . ' <input type="text" name="sort' . $i . '" value="' .
					            htmlspecialchars(current($sorts)) . '"/> <select name="order' . $i . '"><option ';
					if ($order == 'ASC') $result .= 'selected="selected" ';
					$result .=  'value="ASC">' . wfMsg('smw_ask_ascorder') . '</option><option ';
					if ($order == 'DESC') $result .= 'selected="selected" ';
					$result .=  'value="DESC">' . wfMsg('smw_ask_descorder') . '</option></select> ';
					next($sorts);
					$i++;
				}
				$result .= '<input type="hidden" name="sc" value="' . $i . '"/>';
				$result .= '<a href="' . htmlspecialchars($skin->makeSpecialUrl('Ask',$urltail . '&eq=yes&sc=1')) . '" rel="nofollow">' . wfMsg('smw_add_sortcondition') . '</a>'; // note that $urltail uses a , separated list for sorting, so setting sc to 1 always adds one new condition
			}

			$printer = SMWQueryProcessor::getResultPrinter('broadtable',SMWQueryProcessor::SPECIAL_PAGE);
			$result .= '<br /><br />' . wfMsg('smw_ask_format_as').' <input type="hidden" name="eq" value="yes"/>' .
				'<select name="p">' .
				'<option value="format=broadtable"'.($this->m_params['format'] == 'broadtable' ? ' selected' : ''). '>' .
				$printer->getName() . ' ('.wfMsg('smw_ask_defaultformat').')</option>'."\n";

			$formats = array();
			foreach (array_keys($smwgResultFormats) as $format) {
				if ( ($format != 'broadtable') && ($format != 'count') && ($format != 'debug') ) { // special formats "count" and "debug" currently not supported
					$printer = SMWQueryProcessor::getResultPrinter($format,SMWQueryProcessor::SPECIAL_PAGE);
					$formats[$format] = $printer->getName();
				}
			}
			natcasesort($formats);
			foreach ($formats as $format => $name) {
				$result .= '<option value="format='.$format.'"'.($this->m_params['format'] == $format ? ' selected' : '').'>'.$name."</option>\n";
			}

			$result .= '</select><br />';

			$result .= '<br /><input type="submit" value="' . wfMsg('smw_ask_submit') . '"/>' .
				'<input type="hidden" name="eq" value="yes"/>' .
					' <a href="' . htmlspecialchars($skin->makeSpecialUrl('Ask',$urltail)) . '" rel="nofollow">' . wfMsg('smw_ask_hidequery') . '</a> ' .
					SMWAskPage::$pipeseparator .' '.SMWAskPage::getEmbedToggle() .
					SMWAskPage::$pipeseparator .
					' <a href="' . htmlspecialchars(wfMsg('smw_ask_doculink')) . '">' . wfMsg('smw_ask_help') . '</a>' .
				"\n</form>";
		} else {
			$result .= '<p><a href="' . htmlspecialchars($skin->makeSpecialUrl('Ask',$urltail . '&eq=yes')) . '" rel="nofollow">' . wfMsg('smw_ask_editquery') . '</a> '.
				SMWAskPage::$pipeseparator.' '.SMWAskPage::getEmbedToggle().'</p>';
		}

		$result .= '<div id="inlinequeryembed" style="display: none"><div id="inlinequeryembedinstruct">'.wfMsg('smw_ask_embed_instr').'</div><textarea id="inlinequeryembedarea" readonly="yes" cols="20" rows="6" onclick="this.select()">'.
			'{{#ask:'.htmlspecialchars($this->m_querystring)."\n";

		foreach ($this->m_printouts as $printout) {
			$result .= '|'.$printout->getSerialisation() . "\n";
		}

		foreach ($this->m_params as $param_name => $param_value) {
			$result .= '|'.htmlspecialchars($param_name).'='.htmlspecialchars($param_value)."\n";
		}

		$result .= '}}</textarea></div><br/>';

		return $result;
	}

	private static function getEmbedToggle()
	{
		return '<span id="embed_show"><a href="#" rel="nofollow" onclick="' .
			"document.getElementById('inlinequeryembed').style.display='block';" .
			"document.getElementById('embed_hide').style.display='inline';" .
			"document.getElementById('embed_show').style.display='none';" .
			"document.getElementById('inlinequeryembedarea').select();".
			'">' . wfMsg('smw_ask_show_embed') . '</a></span>' .
			'<span id="embed_hide" style="display: none"><a href="#" rel="nofollow" onclick="' .
			"document.getElementById('inlinequeryembed').style.display='none';" .
			"document.getElementById('embed_show').style.display='inline';" .
			"document.getElementById('embed_hide').style.display='none';".
			'">' . wfMsg('smw_ask_hide_embed') . '</a></span>';
	}

	/**
	 * Build the navigation for some given query result, reuse url-tail parameters
	 */
	protected function getNavigationBar($res, $urltail) {
		global $wgUser, $smwgQMaxInlineLimit;
		$skin = $wgUser->getSkin();
		$offset = $this->m_params['offset'];
		$limit  = $this->m_params['limit'];
		// prepare navigation bar
		if ($offset > 0) {
			$navigation = '<a href="' . htmlspecialchars($skin->makeSpecialUrl('Ask','offset=' . max(0,$offset-$limit) . '&limit=' . $limit . $urltail)) . '" rel="nofollow">' . wfMsg('smw_result_prev') . '</a>';
		} else {
			$navigation = wfMsg('smw_result_prev');
		}

		$navigation .= '&nbsp;&nbsp;&nbsp;&nbsp; <b>' . wfMsg('smw_result_results') . ' ' . ($offset+1) . '&ndash; ' . ($offset + $res->getCount()) . '</b>&nbsp;&nbsp;&nbsp;&nbsp;';

		if ($res->hasFurtherResults())
			$navigation .= ' <a href="' . htmlspecialchars($skin->makeSpecialUrl('Ask','offset=' . ($offset+$limit) . '&limit=' . $limit . $urltail)) . '" rel="nofollow">' . wfMsg('smw_result_next') . '</a>';
		else $navigation .= wfMsg('smw_result_next');

		$first=true;
		foreach (array(20,50,100,250,500) as $l) {
			if ($l > $smwgQMaxInlineLimit) break;
			if ($first) {
				$navigation .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(';
				$first = false;
			} else $navigation .= ' ' . SMWAskPage::$pipeseparator . ' ';
			if ( $limit != $l ) {
				$navigation .= '<a href="' . htmlspecialchars($skin->makeSpecialUrl('Ask','offset=' . $offset . '&limit=' . $l . $urltail)) . '" rel="nofollow">' . $l . '</a>';
			} else {
				$navigation .= '<b>' . $l . '</b>';
			}
		}
		$navigation .= ')';
		return $navigation;
	}

}

