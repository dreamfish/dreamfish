<?php
/**
 * @file
 * @ingroup SMWDataValues
 */

/**
 * SMWDataValue implements the handling of n-ary relations.
 * @todo: support outputformat
 * @todo: support "allows value" and "display units"
 *
 * @author Jörg Heizmann
 * @author Markus Krötzsch
 * @ingroup SMWDataValues
 */
class SMWNAryValue extends SMWDataValue {

	private $m_count = 0;

	///The array of the data values within this container value
	private $m_values = array();
	/// TypeObject as we received them when datafactory called us
	private $m_type;
	/// Should this DV operate on query syntax (special mode for parsing queries in a compatible fashion)
	private $m_querysyntax = false;
	/// Array of comparators as might be found in query strings (based on inputs like >, <, etc.)
	private $m_comparators;

	protected function parseUserValue($value) {
		$this->m_values = array();
		$this->m_comparators = array(); // only for query mode
		if ($value == '') {
			$this->addError('No values specified.');
			return;
		}

		$types = $this->m_type->getTypeValues();
		$values = preg_split('/[\s]*;[\s]*/u', trim($value), $this->m_count);
		$vi = 0; // index in value array
		$empty = true;
		for ($i = 0; $i < $this->m_count; $i++) { // iterate over slots
			// special handling for supporting query parsing
			if ($this->m_querysyntax) {
				$comparator = SMW_CMP_EQ;
				SMWQueryParser::prepareValue($values[$vi], $comparator);
			}
			// generating the DVs:
			if ( (count($values) > $vi) &&
			     ( ($values[$vi] == '') || ($values[$vi] == '?') ) ) { // explicit omission
				$this->m_values[$i] = NULL;
				$vi++;
			} elseif (count($values) > $vi) { // some values left, try next slot
				$dv = SMWDataValueFactory::newTypeObjectValue($types[$i], $values[$vi]);
				if ($dv->isValid()) { // valid DV: keep
					$this->m_values[$i] = $dv;
					$vi++;
					$empty = false;
					if ($this->m_querysyntax) { // keep comparator for later querying
						$this->m_comparators[$i] = $comparator;
					}
				} elseif ( (count($values)-$vi) == (count($types)-$i) ) {
					// too many errors: keep this one to have enough slots left
					$this->m_values[$i] = $dv;
					$vi++;
				} else { // assume implicit omission, reset to NULL
					$this->m_values[$i] = NULL;
				}
			} else { // fill rest with NULLs
				$this->m_values[$i] = NULL;
			}
		}
		if ($empty) {
			$this->addError('No values specified.');
		}
	}

	public function setDBkeys($args) {
		wfLoadExtensionMessages('SemanticMediaWiki');
		$this->addError(wfMsgForContent('smw_parseerror'));
// 		trigger_error("setDBkeys() cannot be used for initializing n-ary datavalues (SMWNAryValue). Use SMWNAryValue->setDVs() instead.", E_USER_WARNING);
//  		debug_print_backtrace();
// 		die;
	}

	/// Parsing from a value array is not supported for this datatype. Use setDVs() to initialize this datatype.
	protected function parseDBkeys($args) {}

	/// No unstubbing required for this datatype. Contained data will be unstubbed if needed.
	protected function unstub() {}

	public function getShortWikiText($linked = NULL) {
		if ($this->m_caption !== false) {
			return $this->m_caption;
		}
		return $this->makeOutputText(0, $linked);
	}

	public function getShortHTMLText($linker = NULL) {
		if ($this->m_caption !== false) {
			return $this->m_caption;
		}
		return $this->makeOutputText(1, $linker);
	}

	public function getLongWikiText($linked = NULL) {
		return $this->makeOutputText(2, $linked);
	}

	public function getLongHTMLText($linker = NULL) {
		return $this->makeOutputText(3, $linker);
	}

	private function makeOutputText($type = 0, $linker = NULL) {
		if (!$this->isValid()) {
			return ( ($type == 0)||($type == 1) )? '' : $this->getErrorText();
		}
		$result = '';
		for ($i = 0; $i < $this->m_count; $i++) {
			if ($i == 1) {
				$result .= ' (';
			} elseif ($i > 1) {
				$result .= ", ";
			}
			if ($this->m_values[$i] !== NULL) {
				$result .= $this->makeValueOutputText($type, $i, $linker);
			} else {
				$result .= '?';
			}
			if ($i == sizeof($this->m_values) - 1) {
				$result .= ')';
			}
		}
		return $result;
	}

	private function makeValueOutputText($type, $index, $linker) {
		switch ($type) {
			case 0: return $this->m_values[$index]->getShortWikiText($linker);
			case 1: return $this->m_values[$index]->getShortHTMLText($linker);
			case 2: return $this->m_values[$index]->getShortWikiText($linker);
			case 3: return $this->m_values[$index]->getShortHTMLText($linker);
		}
	}

	/// @note This function does not return a useful result for n-ary values. Use getDVs() to access the individual values of this n-ary.
	public function getDBkeys() {
		return array('');
	}

	public function getWikiValue() {
		$result = '';
		$first = true;
		foreach ($this->m_values as $value) {
			if ($first) {
				$first = false;
			} else {
				$result .= "; ";
			}
			if ($value !== NULL) {
				$result .= $value->getWikiValue();
			} else {
				$result .= "?";
			}
		}
		return $result;
	}

	public function getHash() {
		$first = true;
		$result = '';
		foreach ($this->m_values as $value) {
			if ($first) {
				$first = false;
			} else {
				$result .= ' - ';
			}
			if ($value !== NULL) {
				$result .= str_replace('-', '--', $value->getHash());
			}
		}
		return $result;
	}

////// Custom functions for n-ary attributes

	public function getDVTypeIDs() {
		return implode(';', $this->m_type->getTypeLabels());
	}

	public function getType() {
		return $this->m_type;
	}

	/**
	 * Set type array. Must be done before setting any values.
	 */
	public function setType($type) {
		$this->m_type = $type;
		$this->m_count = count($this->m_type->getTypeLabels());
		$this->m_values = array(); // careful: do not iterate to m_count if DV is not valid!
	}

	/**
	 * Change to query syntax mode.
	 */
	public function acceptQuerySyntax() {
		$this->m_querysyntax = true;
	}

	public function getDVs() {
		return $this->isValid() ? $this->m_values : NULL;
	}

	/**
	 * Directly set the values to the given array of values. The given values
	 * should correspond to the types and arity of the nary container, with
	 * NULL as an indication for omitted values.
	 */
	public function setDVs($datavalues) {
		$this->clearErrors(); // clear errors
		$this->m_infolinks = array(); // clear links
		$this->m_hasssearchlink = false;
		$this->m_caption = false;
		$typelabels = $this->m_type->getTypeLabels();
		for ($i = 0; $i < $this->m_count; $i++) {
			if ( ($i < count($datavalues) ) && ($datavalues[$i] !== NULL) ) {
			    //&& ($datavalues[$i]->getTypeID() == SMWDataValueFactory::findTypeID($typelabels[$i])) ) {
			    ///TODO: is the above typcheck required, or can we assume responsible callers?
				$this->m_values[$i] = $datavalues[$i];
			} else {
				$this->m_values[$i] = NULL;
			}
		}
		$this->m_isset = true;
	}

	/**
	 * If valid and in querymode, build a suitable SMWValueList description from the
	 * given input or return NULL if no such description was given. This requires the
	 * input to be given to setUserValue(). Otherwise bad things will happen.
	 */
	public function getValueList() {
		$vl = new SMWValueList();
		if (!$this->isValid() || !$this->m_querysyntax) {
			return NULL;
		}
		for ($i=0; $i < $this->m_count; $i++) {
			if ($this->m_values[$i] !== NULL) {
				$vl->setDescription($i,new SMWValueDescription($this->m_values[$i], $this->m_comparators[$i]));
			}
		}
		return $vl;
	}

	public function getExportData() {
		if (!$this->isValid()) return NULL;

		$result = new SMWExpData(new SMWExpElement('', $this)); // bnode
		$ed = new SMWExpData(SMWExporter::getSpecialElement('swivt','Container'));
		$result->addPropertyObjectValue(SMWExporter::getSpecialElement('rdf','type'), $ed);
		$count = 0;
		foreach ($this->m_values as $value) {
			$count++;
			if ( ($value === NULL) || (!$value->isValid()) ) {
				continue;
			}
			if (($value->getTypeID() == '_wpg') || ($value->getTypeID() == '_uri') || ($value->getTypeID() == '_ema')) {
				$result->addPropertyObjectValue(
				      SMWExporter::getSpecialElement('swivt','object' . $count),
				      $value->getExportData());
			} else {
				$result->addPropertyObjectValue(
				      SMWExporter::getSpecialElement('swivt','value' . $count),
				      $value->getExportData());
			}
		}
		return $result;
	}

	/// @todo Allowed values for multi-valued properties are not supported yet.
	protected function checkAllowedValues() {}

}

