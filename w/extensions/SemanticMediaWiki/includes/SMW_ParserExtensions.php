<?php
/**
 * This file contains essentially all SMW code that affects parsing by reading some
 * special SMW syntax.
 * @file
 * @ingroup SMW
 * @author Markus Krötzsch
 * @author Denny Vrandecic
 */

/**
 * Static class to collect all functions related to parsing wiki text in SMW.
 * It includes all parser function declarations and hooks.
 * @ingroup SMW
 */
class SMWParserExtensions {

	/// Temporarily store parser as it cannot be passed to call-back functions otherwise.
	protected static $mTempParser;
	/// Internal state for switching off/on SMW link annotations during parsing
	protected static $mTempStoreAnnotations;

	/**
	 *  This method will be called before an article is displayed or previewed.
	 *  For display and preview we strip out the semantic properties and append them
	 *  at the end of the article.
	 */
	static public function onInternalParseBeforeLinks(&$parser, &$text) {
		global $smwgStoreAnnotations, $smwgLinksInValues;
		SMWParseData::stripMagicWords($text, $parser);
		// store the results if enabled (we have to parse them in any case, in order to
		// clean the wiki source for further processing)
		$smwgStoreAnnotations = smwfIsSemanticsProcessed($parser->getTitle()->getNamespace());
		SMWParserExtensions::$mTempStoreAnnotations = true; // used for [[SMW::on]] and [[SMW:off]]

		// process redirects, if any
		// (it seems that there is indeed no more direct way of getting this info from MW)
		$rt = Title::newFromRedirect($text);
		if ($rt !== NULL) {
			$p = SMWPropertyValue::makeProperty('_REDI');
			$dv = SMWDataValueFactory::newPropertyObjectValue($p,$rt->getPrefixedText());
			if ($smwgStoreAnnotations) {
				SMWParseData::getSMWData($parser)->addPropertyObjectValue($p,$dv);
			}
		}

		SMWParserExtensions::$mTempParser = $parser; // only used in subsequent callbacks, forgotten afterwards
		// In the regexp matches below, leading ':' escapes the markup, as
		// known for Categories.
		// Parse links to extract semantic properties
		if ($smwgLinksInValues) { // more complex regexp -- lib PCRE may cause segfaults if text is long :-(
			$semanticLinkPattern = '/\[\[                 # Beginning of the link
			                        (?:([^:][^]]*):[=:])+ # Property name (or a list of those)
			                        (                     # After that:
			                          (?:[^|\[\]]         #   either normal text (without |, [ or ])
			                          |\[\[[^]]*\]\]      #   or a [[link]]
			                          |\[[^]]*\]          #   or an [external link]
			                        )*)                   # all this zero or more times
			                        (?:\|([^]]*))?        # Display text (like "text" in [[link|text]]), optional
			                        \]\]                  # End of link
			                        /xu';
			$text = preg_replace_callback($semanticLinkPattern, array('SMWParserExtensions','parsePropertiesCallback'), $text);
		} else { // simpler regexps -- no segfaults found for those, but no links in values
			$semanticLinkPattern = '/\[\[                 # Beginning of the link
			                        (?:([^:][^]]*):[=:])+ # Property name (or a list of those)
			                        ([^\[\]]*)            # content: anything but [, |, ]
			                        \]\]                  # End of link
			                        /xu';
			$text = preg_replace_callback($semanticLinkPattern, array('SMWParserExtensions','simpleParsePropertiesCallback'), $text);
		}

		// add link to RDF to HTML header
		SMWOutputs::requireHeadItem('smw_rdf', '<link rel="alternate" type="application/rdf+xml" title="' .
		                    htmlspecialchars( $parser->getTitle()->getPrefixedText() ) . '" href="' .
		                    htmlspecialchars(
		                    	SpecialPage::getTitleFor( 'ExportRDF', $parser->getTitle()->getPrefixedText() )->getLocalUrl( 'xmlmime=rdf' ) ) . "\" />");

		SMWOutputs::commitToParser($parser);
		return true; // always return true, in order not to stop MW's hook processing!
	}

	/**
	 * This callback function strips out the semantic attributes from a wiki
	 * link. Expected parameter: array(linktext, properties, value|caption)
	 * This function is a preprocessing for smwfParsePropertiesCallback, and
	 * takes care of separating value and caption (instead of leaving this to
	 * a more complex regexp).
	 */
	static public function simpleParsePropertiesCallback($semanticLink) {
		$value = '';
		$caption = false;
		if (array_key_exists(2,$semanticLink)) {
			$parts = explode('|',$semanticLink[2]);
			if (array_key_exists(0,$parts)) {
				$value = $parts[0];
			}
			if (array_key_exists(1,$parts)) {
				$caption = $parts[1];
			}
		}
		if ($caption !== false) {
			return SMWParserExtensions::parsePropertiesCallback(array($semanticLink[0],$semanticLink[1],$value,$caption));
		} else {
			return SMWParserExtensions::parsePropertiesCallback(array($semanticLink[0],$semanticLink[1],$value));
		}
	}

	/**
	 * This callback function strips out the semantic attributes from a wiki
	 * link. Expected parameter: array(linktext, properties, value, caption)
	 */
	static public function parsePropertiesCallback($semanticLink) {
		global $smwgInlineErrors, $smwgStoreAnnotations;
		wfProfileIn("smwfParsePropertiesCallback (SMW)");
		if (array_key_exists(1,$semanticLink)) {
			$property = $semanticLink[1];
		} else { $property = ''; }
		if (array_key_exists(2,$semanticLink)) {
			$value = $semanticLink[2];
		} else { $value = ''; }
		if ($value == '') { // silently ignore empty values
			wfProfileOut("smwfParsePropertiesCallback (SMW)");
			return '';
		}

		if ($property == 'SMW') {
			switch ($value) {
				case 'on':  SMWParserExtensions::$mTempStoreAnnotations = true;  break;
				case 'off': SMWParserExtensions::$mTempStoreAnnotations = false; break;
			}
			wfProfileOut("smwfParsePropertiesCallback (SMW)");
			return '';
		}

		if (array_key_exists(3,$semanticLink)) {
			$valueCaption = $semanticLink[3];
		} else { $valueCaption = false; }

		//extract annotations and create tooltip
		$properties = preg_split('/:[=:]/u', $property);
		foreach($properties as $singleprop) {
			$dv = SMWParseData::addProperty($singleprop,$value,$valueCaption, SMWParserExtensions::$mTempParser, $smwgStoreAnnotations && SMWParserExtensions::$mTempStoreAnnotations);
		}
		$result = $dv->getShortWikitext(true);
		if ( ($smwgInlineErrors && $smwgStoreAnnotations && SMWParserExtensions::$mTempStoreAnnotations) && (!$dv->isValid()) ) {
			$result .= $dv->getErrorText();
		}
		wfProfileOut("smwfParsePropertiesCallback (SMW)");
		return $result;
	}

	/**
	 * This hook registers parser functions and hooks to the given parser. It is
	 * called during SMW initialisation. Note that parser hooks are something different
	 * than MW hooks in general, which explains the two-level registration.
	 */
	public static function registerParserFunctions(&$parser) {
		$parser->setHook( 'ask', array('SMWParserExtensions','doAskHook') );
		$parser->setFunctionHook( 'ask', array('SMWParserExtensions','doAsk') );
		$parser->setFunctionHook( 'show', array('SMWParserExtensions','doShow') );
		$parser->setFunctionHook( 'info', array('SMWParserExtensions','doInfo') );
		$parser->setFunctionHook( 'concept', array('SMWParserExtensions','doConcept') );
		$parser->setFunctionHook( 'set', array('SMWParserExtensions','doSet') );
		$parser->setFunctionHook( 'set_recurring_event', array('SMWParserExtensions','doSetRecurringEvent') );
		if (defined('SFH_OBJECT_ARGS')) { // only available since MediaWiki 1.13
			$parser->setFunctionHook( 'declare', array('SMWParserExtensions','doDeclare'), SFH_OBJECT_ARGS );
		}
		return true; // always return true, in order not to stop MW's hook processing!
	}

	/**
	 * Function for handling the {{\#ask }} parser function. It triggers the execution of inline
	 * query processing and checks whether (further) inline queries are allowed.
	 */
	static public function doAsk(&$parser) {
		global $smwgQEnabled, $smwgIQRunningNumber;
		if ($smwgQEnabled) {
			$smwgIQRunningNumber++;
			$params = func_get_args();
			array_shift( $params ); // we already know the $parser ...
			$result = SMWQueryProcessor::getResultFromFunctionParams($params,SMW_OUTPUT_WIKI);
		} else {
			wfLoadExtensionMessages('SemanticMediaWiki');
			$result = smwfEncodeMessages(array(wfMsgForContent('smw_iq_disabled')));
		}
		SMWOutputs::commitToParser($parser);
		return $result;
	}

	/**
	 * The \<ask\> parser hook processing part. This has been replaced by the
	 * parser function \#ask and is no longer supported.
	 * @todo Remove this function entirely, one could have an extension for those who
	 * wish to have some intelligent behaviour here.
	 */
	static public function doAskHook($querytext, $params, &$parser) {
		return '&lt;ask&gt; no longer supported. See SMW documentation on how to do inline queries now.';
	}

	/**
	 * Function for handling the {{\#show }} parser function. The \#show function is
	 * similar to \#ask but merely prints some property value for a specified page.
	 */
	static public function doShow(&$parser) {
		global $smwgQEnabled, $smwgIQRunningNumber;
		if ($smwgQEnabled) {
			$smwgIQRunningNumber++;
			$params = func_get_args();
			array_shift( $params ); // we already know the $parser ...
			$result = SMWQueryProcessor::getResultFromFunctionParams($params,SMW_OUTPUT_WIKI,SMWQueryProcessor::INLINE_QUERY,true);
		} else {
			wfLoadExtensionMessages('SemanticMediaWiki');
			$result = smwfEncodeMessages(array(wfMsgForContent('smw_iq_disabled')));
		}
		SMWOutputs::commitToParser($parser);
		return $result;
	}

	/**
	* Function for handling the {{\#concept }} parser function. This parser function provides a special input
	* facility for defining concepts, and it displays the resulting concept description.
	*/
	static public function doConcept(&$parser) {
		global $smwgQDefaultNamespaces, $smwgQMaxSize, $smwgQMaxDepth, $wgContLang;
		wfLoadExtensionMessages('SemanticMediaWiki');
		$title = $parser->getTitle();
		$pconc = SMWPropertyValue::makeProperty('_CONC');
		if ($title->getNamespace() != SMW_NS_CONCEPT) {
			$result = smwfEncodeMessages(array(wfMsgForContent('smw_no_concept_namespace')));
			SMWOutputs::commitToParser($parser);
			return $result;
		} elseif (count(SMWParseData::getSMWdata($parser)->getPropertyValues($pconc)) > 0 ) {
			$result = smwfEncodeMessages(array(wfMsgForContent('smw_multiple_concepts')));
			SMWOutputs::commitToParser($parser);
			return $result;
		}

		// process input:
		$params = func_get_args();
		array_shift( $params ); // we already know the $parser ...
		$concept_input = str_replace(array('&gt;','&lt;'),array('>','<'),array_shift( $params )); // use first parameter as concept (query) string
		/// NOTE: the str_replace above is required in MediaWiki 1.11, but not in MediaWiki 1.14
		$query = SMWQueryProcessor::createQuery($concept_input, array('limit' => 20, 'format' => 'list'), SMWQueryProcessor::CONCEPT_DESC);
		$concept_text = $query->getDescription()->getQueryString();
		$concept_docu = array_shift( $params ); // second parameter, if any, might be a description

		$dv = SMWDataValueFactory::newPropertyObjectValue($pconc);
		$dv->setValues($concept_text, $concept_docu, $query->getDescription()->getQueryFeatures(), $query->getDescription()->getSize(), $query->getDescription()->getDepth());
		if (SMWParseData::getSMWData($parser) !== NULL) {
			SMWParseData::getSMWData($parser)->addPropertyObjectValue($pconc,$dv);
		}

		// display concept box:
		$rdflink = SMWInfolink::newInternalLink(wfMsgForContent('smw_viewasrdf'), $wgContLang->getNsText(NS_SPECIAL) . ':ExportRDF/' . $title->getPrefixedText(), 'rdflink');
		SMWOutputs::requireHeadItem(SMW_HEADER_STYLE);

		$result = '<div class="smwfact"><span class="smwfactboxhead">' . wfMsgForContent('smw_concept_description',$title->getText()) .
				(count($query->getErrors())>0?' ' . smwfEncodeMessages($query->getErrors()):'') .
				'</span>' . '<span class="smwrdflink">' . $rdflink->getWikiText() . '</span>' . '<br />' .
				($concept_docu?"<p>$concept_docu</p>":'') .
				'<pre>' . str_replace('[', '&#x005B;', $concept_text) . "</pre>\n</div>";
		SMWOutputs::commitToParser($parser);
		return $result;
	}

	/**
	 * Function for handling the {{\#info }} parser function. This function creates a tooltip like
	 * the one used by SMW for giving hints.
	 * @note This feature is at risk and may vanish or change in future versions.
	 */
	static public function doInfo(&$parser) {
		$params = func_get_args();
		array_shift( $params ); // we already know the $parser ...
		$content = array_shift( $params ); // use only first parameter, ignore rest (may get meaning later)
		$result = smwfEncodeMessages(array($content), 'info');
		SMWOutputs::commitToParser($parser);
		return $result;
	}

	/**
	 * Function for handling the {{\#set }} parser function. This is used for adding annotations
	 * silently.
	 *
	 * Usage:
	 * {{\#set:
	 *   population = 13000
	 * | area = 396 km²
	 * | sea = Adria
	 * }}
	 * This creates annotations with the properties as stated on the left side, and the
	 * values on the right side.
	 *
	 * @param[in] &$parser Parser  The current parser
	 * @return nothing
	 */
	static public function doSet( &$parser ) {
		$params = func_get_args();
		array_shift( $params ); // we already know the $parser ...
		foreach ($params as $p)
			if (trim($p) != "") {
				$parts = explode("=", trim($p));
				if (count($parts)==2) {
					$property = $parts[0];
					$object = $parts[1];
					SMWParseData::addProperty( $property, $object, false, $parser, true );
				}
			}
		SMWOutputs::commitToParser($parser); // not obviously required, but let us be sure
		return '';
	}

	/**
	 * Function for handling the {{\#set_recurring_event }} parser function.
	 * This is used for defining a set of date values for a page that
	 * represents a recurring event.
	 * Like with the #set function, all annotations happen silently.
	 *
	 * Usage:
	 * {{\#set_recurring_event:
	 *   property = Has date
	 * | start = January 4, 2010
	 * | end = June 7, 2010
	 * | unit = week
	 * | period = 1
	 * | include = March 16, 2010;March 23, 2010
	 * | exclude = March 15, 2010;March 22, 2010
	 * }}
	 * This sets a "Has date" value for every Monday within the specified
	 * six-month period, except for two Mondays which are excluded and
	 * two Tuesdays that are saved in their place.
	 *
	 * @param[in] &$parser Parser  The current parser
	 * @return nothing
	 */
	static public function doSetRecurringEvent( &$parser ) {
		$params = func_get_args();
		array_shift( $params ); // we already know the $parser ...
		// initialize variables
		$property_name = $start_date = $end_date = $unit = $period = null;
		$included_dates = array();
		$excluded_dates_jd = array();
		// set values from the parameters
		foreach ($params as $p) {
			if (trim($p) != "") {
				$parts = explode("=", trim($p));
				if (count($parts)==2) {
					list($arg, $value) = $parts;
					if ($arg === 'property') {
						$property_name = $value;
					} elseif ($arg === 'start') {
						$start_date = SMWDataValueFactory::newTypeIDValue('_dat', $value);
					} elseif ($arg === 'end') {
						$end_date = SMWDataValueFactory::newTypeIDValue('_dat', $value);
					} elseif ($arg === 'unit') {
						$unit = $value;
					} elseif ($arg === 'period') {
						$period = (int)$value;
					} elseif ($arg === 'include') {
						$included_dates = explode(';', $value);
					} elseif ($arg === 'exclude') {
						$excluded_dates = explode(';', $value);
						foreach ($excluded_dates as $date_str) {
							$date = SMWDataValueFactory::newTypeIDValue('_dat', $date_str);
							$excluded_dates_jd[] = $date->getNumericValue();
						}
					}
				}
			}
		}
		// we need at least a property and start date - if either one
		// is null, exit here
		if (is_null($property_name) || is_null($start_date))
			return;

		// if the period is null, or outside of normal bounds, set it to 1
		if (is_null($period) || $period < 1 || $period > 500)
			$period = 1;
		// get the Julian day value for both the start and end date
		$start_date_jd = $start_date->getNumericValue();
		if (! is_null($end_date))
			$end_date_jd = $end_date->getNumericValue();
		$cur_date = $start_date;
		$cur_date_jd = $start_date->getNumericValue();
		$i = 0;
		$reached_end_date = false;
		do {
			$i++;
			$exclude_date = (in_array($cur_date_jd, $excluded_dates_jd));
			if (! $exclude_date) {
				$cur_date_str = $cur_date->getLongWikiText();
				SMWParseData::addProperty( $property_name, $cur_date_str, false, $parser, true );
			}
			// now get the next date
			// handling is different depending on whether it's
			// month/year or week/day, since the latter is a
			// set number of days while the former isn't
			if ($unit === 'year' || $unit == 'month') {
				$cur_year = $cur_date->getYear();
				$cur_month = $cur_date->getMonth();
				$cur_day = $cur_date->getDay();
				$cur_time = $cur_date->getTimeString();
				if ($unit == 'year') {
					$cur_year += $period;
				} else { // $unit === 'month'
					$cur_month += $period;
					$cur_year += (int)($cur_month / 12);
					$cur_month %= 12;
				}
				$date_str = "$cur_year-$cur_month-$cur_day $cur_time";
				$cur_date = SMWDataValueFactory::newTypeIDValue('_dat', $date_str);
				$cur_date_jd = $cur_date->getNumericValue();
			} else { // $unit == 'day' or 'week'
				// assume 'day' if it's none of the above
				$cur_date_jd += ($unit === 'week') ? 7 * $period : $period;
				$cur_date = SMWDataValueFactory::newTypeIDValue('_dat', $cur_date_jd);
			}

			// should we stop?
			if (is_null($end_date)) {
				global $smwgDefaultNumRecurringEvents;
				$reached_end_date = $i > $smwgDefaultNumRecurringEvents;
			} else {
				global $smwgMaxNumRecurringEvents;
				$reached_end_date = ($cur_date_jd > $end_date_jd) || ($i > $smwgMaxNumRecurringEvents);
			}
		} while (! $reached_end_date);

		// handle the 'include' dates as well
		foreach ($included_dates as $date_str)
			SMWParseData::addProperty( $property_name, $date_str, false, $parser, true );
		SMWOutputs::commitToParser($parser); // not obviously required, but let us be sure
	}

	/**
	 * Function for handling the {{\#declare }} parser function. It is used for declaring template parameters
	 * that should automagically be annotated when the template is used.
	 *
	 * Usage:
	 * {{\#declare:Author=Author\#list|Publisher=editor}}
	 */
	static public function doDeclare( &$parser, PPFrame $frame, $args ) {
		if ($frame->isTemplate()) {
			foreach ($args as $arg)
				if (trim($arg) != "") {
					$expanded = trim( $frame->expand( $arg ));
					$parts = explode("=", $expanded, 2);
					if (count($parts)==1) {
						$propertystring = $expanded;
						$argumentname = $expanded;
					} else {
						$propertystring = $parts[0];
						$argumentname = $parts[1];
					}
					$property = SMWPropertyValue::makeUserProperty($propertystring);
					$argument = $frame->getArgument($argumentname);
					$valuestring = $frame->expand($argument);
					if ($property->isValid()) {
						$type = $property->getPropertyTypeID();
						if ($type == "_wpg") {
							$matches = array();
							preg_match_all("/\[\[([^\[\]]*)\]\]/", $valuestring, $matches);
							$objects = $matches[1];
							if (count($objects) == 0) {
								if (trim($valuestring) != '') {
									SMWParseData::addProperty( $propertystring, $valuestring, false, $parser, true );
								}
							} else {
								foreach ($objects as $object) {
									SMWParseData::addProperty( $propertystring, $object, false, $parser, true );
								}
							}
						} else {
							if (trim($valuestring) != '') {
								SMWParseData::addProperty( $propertystring, $valuestring, false, $parser, true );
							}
						}
						$value = SMWDataValueFactory::newPropertyObjectValue($property, $valuestring);
						//if (!$value->isValid()) continue;
					}
				}
		} else {
			// @todo Save as metadata
		}
		SMWOutputs::commitToParser($parser); // not obviously required, but let us be sure
		return '';
	}

}
