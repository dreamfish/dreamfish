<?php
/**
 * @file
 * @ingroup SMWLanguage
 */

/**
 * This group contains all parts of SMW that relate to localisation and
 * translation.
 * @defgroup SMWLanguage SMWLanguage
 * @ingroup SMW
 */

/**
 * Base class for all SMW language classes.
 * @author Markus Krötzsch
 * @ingroup SMWLanguage
 * @ingroup Language
 */
abstract class SMWLanguage {

	// the special message arrays ...
	protected $m_DatatypeLabels;
	protected $m_DatatypeAliases = array();
	protected $m_SpecialProperties;
	protected $m_SpecialPropertyAliases = array();
	protected $m_Namespaces;
	protected $m_NamespaceAliases = array();
	/// Twelve strings naming the months. English is always supported in Type:Date, but
	/// we still need the English defaults to ensure that labels are returned by getMonthLabel()
	protected $m_months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	/// Twelve strings briefly naming the months. English is always supported in Type:Date, so
	/// the default is simply empty (no labels in addition to English)
	protected $m_monthsshort = array();
	/// Preferred interpretations for dates with 1, 2, and 3 components. There is an array for
	/// each case, and the constants define the obvious order (e.g. SMW_YDM means "first Year,
	/// then Day, then Month). Unlisted combinations will not be accepted at all.
	protected $m_dateformats = array(array(SMW_Y), array(SMW_MY,SMW_YM), array(SMW_DMY,SMW_MDY,SMW_YMD,SMW_YDM));
	/// Should English default aliases be used in this language?
	protected $m_useEnDefaultAliases = true;
	/// Default English aliases for namespaces (typically used in all languages)
	static protected $enNamespaceAliases = array(
		'Property'      => SMW_NS_PROPERTY,
		'Property_talk' => SMW_NS_PROPERTY_TALK,
		'Type'          => SMW_NS_TYPE,
		'Type_talk'     => SMW_NS_TYPE_TALK,
		'Concept'       => SMW_NS_CONCEPT,
		'Concept_talk'  => SMW_NS_CONCEPT_TALK
	);
	/// Default English aliases for namespaces (typically used in all languages)
	static protected $enDatatypeAliases = array(
		'URL'                   => '_uri',
		'Page'                  => '_wpg',
		'String'                => '_str',
		'Text'                  => '_txt',
		'Code'                  => '_cod',
		'Boolean'               => '_boo',
		'Number'                => '_num',
		'Geographic coordinate' => '_geo',
		'Temperature'           => '_tem',
		'Date'                  => '_dat',
		'Email'                 => '_ema',
		'Annotation URI'        => '_anu'
	);
	/// Default English aliases for special property names (typically used in all languages)
	static protected $enPropertyAliases = array(
		'Has type'          => '_TYPE',
		'Equivalent URI'    => '_URI',
		'Subproperty of'    => '_SUBP',
		'Display units'     => '_UNIT',
		'Imported from'     => '_IMPO',
		'Corresponds to'    => '_CONV',
		'Provides service'  => '_SERV',
		'Allows value'      => '_PVAL',
		'Modification date' => '_MDAT',
		'Has improper value for' => '_ERRP'
	);


	/**
	 * Function that returns an array of namespace identifiers.
	 */
	function getNamespaces() {
		return $this->m_Namespaces;
	}

	/**
	 * Function that returns an array of namespace aliases, if any.
	 */
	function getNamespaceAliases() {
		return $this->m_useEnDefaultAliases?
		       $this->m_NamespaceAliases + SMWLanguage::$enNamespaceAliases:
			   $this->m_NamespaceAliases;
	}

	/**
	 * Return all labels that are available as names for built-in datatypes. Those
	 * are the types that users can access via [[has type::...]] (more built-in
	 * types may exist for internal purposes but the user won't need to
	 * know this). The returned array is indexed by (internal) type ids.
	 */
	function getDatatypeLabels() {
		return $this->m_DatatypeLabels;
	}

	/**
	 * Return an array that maps aliases to internal type ids. All ids used here
	 * should also have a primary label defined in m_DatatypeLabels.
	 */
	function getDatatypeAliases() {
		return $this->m_useEnDefaultAliases?
		       $this->m_DatatypeAliases + SMWLanguage::$enDatatypeAliases:
			   $this->m_DatatypeAliases;
	}

	/**
	 * Function that returns the labels for predefined properties.
	 */
	function getPropertyLabels() {
		return $this->m_SpecialProperties;
	}

	/**
	 * Aliases for predefined properties, if any.
	 */
	function getPropertyAliases() {
		return $this->m_useEnDefaultAliases?
		       $this->m_SpecialPropertyAliases + SMWLanguage::$enPropertyAliases:
			   $this->m_SpecialPropertyAliases;
	}

	/**
	 * Function that returns the preferred date formats
	 */
	function getDateFormats() {
		return $this->m_dateformats;
	}

	/**
	 * Function looks up a month and returns the corresponding number.
	 * @todo Should we add functionality to ignore case here?
	 * @todo Should there be prefix string matching instead of two arrays for full and short names?
	 */
	function findMonth($label) {
		$id = array_search($label, $this->m_months);
		if ($id !== false) {
			return $id+1;
		}
		$id = array_search($label, $this->m_monthsshort);
		if ($id !== false) {
			return $id+1;
		}
		return false;
	}

	/**
	 * Return the name of the month with the given number.
	 */
	function getMonthLabel($number) {
	  return (($number>=1)&&($number<=12))?$this->m_months[(int)($number-1)]:'';
	}

}


