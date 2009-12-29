<?php
/**
 * New SQL implementation of SMW's storage abstraction layer.
 *
 * @author Markus Krötzsch
 * @file
 * @ingroup SMWStore
 */

// The use of the following constants is explained in SMWSQLStore2::setup():
define('SMW_SQL2_SMWIW',':smw'); // virtual "interwiki prefix" for special SMW objects
define('SMW_SQL2_SMWREDIIW',':smw-redi'); // virtual "interwiki prefix" for SMW objects that are redirected
define('SMW_SQL2_SMWBORDERIW',':smw-border'); // virtual "interwiki prefix" separating very important pre-defined properties from the rest
define('SMW_SQL2_SMWPREDEFIW',':smw-preprop'); // virtual "interwiki prefix" marking predefined objects (non-movable)
define('SMW_SQL2_SMWINTDEFIW',':smw-intprop'); // virtual "interwiki prefix" marking internal (invisible) predefined properties

// Constant flags for identifying tables/retrieval types:
define('SMW_SQL2_NONE',0);
define('SMW_SQL2_RELS2',1);
define('SMW_SQL2_ATTS2',2);
define('SMW_SQL2_TEXT2',4);
define('SMW_SQL2_SPEC2',8);
define('SMW_SQL2_REDI2',16);
define('SMW_SQL2_NARY2',32); // not really a table, but a retrieval type
define('SMW_SQL2_SUBS2',64);
define('SMW_SQL2_INST2',128);
define('SMW_SQL2_CONC2',256);


/**
 * Storage access class for using the standard MediaWiki SQL database
 * for keeping semantic data.
 *
 * @note Regarding the use of interwiki links in the store, there is currently
 * no support for storing semantic data about interwiki objects, and hence queries
 * that involve interwiki objects really make sense only for them occurring in
 * object positions. Most methods still use the given input interwiki text as a simple
 * way to filter out results that may be found if an interwiki object is given but a
 * local object of the same name exists. It is currently not planned to support things
 * like interwiki reuse of properties.
 * @ingroup SMWStore
 */
class SMWSQLStore2 extends SMWStore {

	/// Cache for SMW IDs, indexed by string keys
	protected $m_ids = array();

	/// Cache for SMWSemanticData objects, indexed by SMW ID
	protected $m_semdata = array();
	/// Like SMWSQLStore2::m_semdata, but containing flags indicating completeness of the SMWSemanticData objs
	protected $m_sdstate = array();
	/// >0 while getSemanticData runs, used to prevent nested calls from clearing the cache while another call runs and is about to fill it with data
	protected static $in_getSemanticData = 0;

	/// Use pre-defined ids for Very Important Properties, avoiding frequent ID lookups for those
	private static $special_ids = array(
		'_TYPE' => 1,
		'_URI'  => 2,
		'_INST' => 4,
		'_UNIT' => 7,
		'_IMPO' => 8,
		'_CONV' => 12,
		'_SERV' => 13,
		'_PVAL' => 14,
		'_REDI' => 15,
		'_SUBP' => 17,
		'_SUBC' => 18,
		'_CONC' => 19,
		'_SF_DF' => 20, // Semantic Form's default form property
		'_SF_AF' => 21,  // Semantic Form's alternate form property
		'_ERRP' => 22
	);

	/// This array defines how various datatypes should be handled internally. This
	/// list usually agrees with the datatype constants given in SMWDataValueFactory,
	/// but need not be complete: the default storage method is SMW_SQL2_ATTS2.
	/// Note that some storage methods require datavalue objects ot support specific
	/// APIs, so arbitrary changes in this table may not work unless the according
	/// datavalue class in SMWDataValueFactory supports those.
	private static $storage_mode = array(
		'_txt'  => SMW_SQL2_TEXT2, // Text type
		'_cod'  => SMW_SQL2_TEXT2, // Code type
		'_str'  => SMW_SQL2_ATTS2, // String type
		'_ema'  => SMW_SQL2_ATTS2, // Email type
		'_uri'  => SMW_SQL2_ATTS2, // URL/URI type
		'_anu'  => SMW_SQL2_ATTS2, // Annotation URI type
		'_wpg'  => SMW_SQL2_RELS2, // Page type
		'_wpp'  => SMW_SQL2_RELS2, // Property page type
		'_wpc'  => SMW_SQL2_RELS2, // Category page type
		'_wpf'  => SMW_SQL2_RELS2, // Form page type (for Semantic Forms)
		'_num'  => SMW_SQL2_ATTS2, // Number type
		'_tem'  => SMW_SQL2_ATTS2, // Temperature type
		'_dat'  => SMW_SQL2_ATTS2, // Time type
		'_geo'  => SMW_SQL2_ATTS2, // Geographic coordinates type
		'_boo'  => SMW_SQL2_ATTS2, // Boolean type
		// Special types are not avaialble directly for users (and have no local language name):
		'__typ' => SMW_SQL2_SPEC2, // Special type page type
		'__con' => SMW_SQL2_CONC2, // Special concept page type
		'__sps' => SMW_SQL2_SPEC2, // Special string type
		'__spu' => SMW_SQL2_SPEC2, // Special uri type
		'__sup' => SMW_SQL2_SUBS2, // Special subproperty type
		'__suc' => SMW_SQL2_SUBS2, // Special subcategory type
		'__spf' => SMW_SQL2_SPEC2, // Special form type (for Semantic Forms)
		'__sin' => SMW_SQL2_INST2, // Special instance of type
		'__red' => SMW_SQL2_REDI2, // Special redirect type
		'__lin' => SMW_SQL2_SPEC2, // Special linear unit conversion type
		'__nry' => SMW_SQL2_NARY2, // Special multi-valued type
		'__err' => SMW_SQL2_NONE, // Special error type, actually this is not stored right now
		'__imp' => SMW_SQL2_SPEC2, // Special import vocabulary type
		'__pro'  => SMW_SQL2_NONE, // Property page type; actually this should never be stored as a value (_wpp is used there)
	);

///// Reading methods /////

	function getSemanticData($subject, $filter = false) {
		wfProfileIn("SMWSQLStore2::getSemanticData (SMW)");
		SMWSQLStore2::$in_getSemanticData++;

		if ( $subject instanceof Title ) { ///TODO: can this still occur?
			$sid = $this->getSMWPageID($subject->getDBkey(),$subject->getNamespace(),$subject->getInterwiki());
			$svalue = SMWWikiPageValue::makePageFromTitle($subject);
		} elseif ($subject instanceof SMWWikiPageValue) {
			$sid =  $subject->isValid()?
			        $this->getSMWPageID($subject->getDBkey(),$subject->getNamespace(),$subject->getInterwiki()):
					0;
			$svalue = $subject;
		} else {
			$sid = 0;
		}
		if ($sid == 0) { // no data, safe our time
			/// NOTE: we consider redirects for getting $sid, so $sid == 0 also means "no redirects"
			SMWSQLStore2::$in_getSemanticData--;
			wfProfileOut("SMWSQLStore2::getSemanticData (SMW)");
			return isset($svalue)?(new SMWSemanticData($svalue)):NULL;
		}

		if ($filter !== false) { //array as described in docu for SMWStore
			$tasks = 0;
			foreach ($filter as $value) {
				$tasks = $tasks | SMWSQLStore2::getStorageMode($value);
			}
		} else {
			$tasks = SMW_SQL2_RELS2 | SMW_SQL2_ATTS2 | SMW_SQL2_TEXT2| SMW_SQL2_SPEC2 | SMW_SQL2_NARY2 | SMW_SQL2_SUBS2 | SMW_SQL2_INST2 | SMW_SQL2_REDI2 | SMW_SQL2_CONC2;
		}
		if ( ($subject->getNamespace() != SMW_NS_PROPERTY) && ($subject->getNamespace() != NS_CATEGORY) ) {
			$tasks = $tasks & ~SMW_SQL2_SUBS2;
		}
		if ($subject->getNamespace() != SMW_NS_CONCEPT) {
			$tasks = $tasks & ~SMW_SQL2_CONC2;
		}

		if (!array_key_exists($sid, $this->m_semdata)) { // new cache entry
			$this->m_semdata[$sid] = new SMWSemanticData($svalue, false);
			$this->m_sdstate[$sid] = $tasks;
		} else { // do only remaining tasks
			$newtasks = $tasks & ~$this->m_sdstate[$sid];
			$this->m_sdstate[$sid] = $this->m_sdstate[$sid] | $tasks;
			$tasks = $newtasks;
		}

		if ($tasks != 0) { // fetch DB handler only when really needed!
			$db =& wfGetDB( DB_SLAVE );
		}
		if ( (count($this->m_semdata) > 20) && (SMWSQLStore2::$in_getSemanticData == 1) ) {
			// prevent memory leak;
			// It is not so easy to find the sweet spot between cache size and performance gains (both memory and time),
			// The value of 20 was chosen by profiling runtimes for large inline queries and heavily annotated pages.
			$this->m_semdata = array($sid => $this->m_semdata[$sid]);
			$this->m_sdstate = array($sid => $this->m_sdstate[$sid]);
		}

		// most types of data suggest rather similar code
		foreach (array(SMW_SQL2_RELS2, SMW_SQL2_ATTS2, SMW_SQL2_TEXT2, SMW_SQL2_INST2, SMW_SQL2_SUBS2, SMW_SQL2_SPEC2, SMW_SQL2_REDI2, SMW_SQL2_CONC2) as $task) {
			if ( !($tasks & $task) ) continue;
			wfProfileIn("SMWSQLStore2::getSemanticData-task$task (SMW)");
			$where = 'p_id=smw_id AND s_id=' . $db->addQuotes($sid);
			switch ($task) {
				case SMW_SQL2_RELS2:
					$from = $db->tableName('smw_rels2') . ' INNER JOIN ' . $db->tableName('smw_ids') . ' AS p ON p_id=p.smw_id INNER JOIN ' . $db->tableName('smw_ids') . ' AS o ON o_id=o.smw_id';
					$select = 'p.smw_title as prop, o.smw_title as title, o.smw_namespace as namespace, o.smw_iw as iw';
					$where = 's_id=' . $db->addQuotes($sid);
				break;
				case SMW_SQL2_ATTS2:
					$from = array('smw_atts2','smw_ids');
					$select = 'smw_title as prop, value_unit as unit, value_xsd as value';
				break;
				case SMW_SQL2_TEXT2:
					$from = array('smw_text2','smw_ids');
					$select = 'smw_title as prop, value_blob as value';
				break;
				case SMW_SQL2_SPEC2:
					$from = 'smw_spec2';
					$select = 'p_id, value_string as value';
					$where = 's_id=' . $db->addQuotes($sid);
				break;
				case SMW_SQL2_SUBS2:
					$from = array('smw_subs2','smw_ids');
					$select = 'smw_title as value';
					$where = 'o_id=smw_id AND s_id=' . $db->addQuotes($sid);
					$namespace = $subject->getNamespace();
					$specprop = ($namespace==NS_CATEGORY)?'_SUBC':'_SUBP';
				break;
				case SMW_SQL2_REDI2:
					$from = array('smw_redi2','smw_ids');
					$select = 'smw_title as title, smw_namespace as namespace';
					$where = 'o_id=smw_id AND s_title=' . $db->addQuotes($subject->getDBkey()) .
					         ' AND s_namespace=' . $db->addQuotes($subject->getNamespace());
				break;
				case SMW_SQL2_INST2:
					$from = array('smw_inst2','smw_ids');
					$select = 'smw_title as value';
					$where = 'o_id=smw_id AND s_id=' . $db->addQuotes($sid);
					$namespace = NS_CATEGORY;
					$specprop = '_INST';
				break;
				case SMW_SQL2_CONC2:
					$from = 'smw_conc2';
					$select = 'concept_txt as concept, concept_docu as docu, concept_features as features, concept_size as size, concept_depth as depth';
					$where = 's_id=' . $db->addQuotes($sid);
				break;
			}
			$res = $db->select( $from, $select, $where, 'SMW::getSemanticData' );
			while($row = $db->fetchObject($res)) {
				$valuekeys = false;
				if ($task & (SMW_SQL2_RELS2 | SMW_SQL2_ATTS2 | SMW_SQL2_TEXT2) ) {
					$propertyname = $row->prop;
				}
				// The following cases are very similar, yet different in certain details:
				if ($task == SMW_SQL2_RELS2) {
					if ( ($row->iw === '') || ($row->iw{0} != ':') ) { // filter "special" iws that mark internal objects
						$valuekeys = array($row->title, $row->namespace,$row->iw,'');
					}
				} elseif ($task == SMW_SQL2_ATTS2) {
					$valuekeys = array($row->value, $row->unit);
				} elseif ($task == SMW_SQL2_TEXT2) {
					$valuekeys = array($row->value);
				} elseif ($task == SMW_SQL2_SPEC2) {
					$pid = array_search($row->p_id, SMWSQLStore2::$special_ids);
					if ($pid != false) {
						$propertyname = $pid;
					} else { // this should be rare (only if some extension uses properties of "special" types)
						$proprow = $db->selectRow('smw_ids', array('smw_title'), array('smw_id' => $row->p_id), 'SMW::getSemanticData');
						/// TODO: $proprow may be false (inconsistent DB but anyway); maybe check and be gentle in some way
						$propertyname = $proprow->smw_title;
					}
					$valuekeys = array($row->value);
				} elseif ( ($task == SMW_SQL2_SUBS2) || ($task == SMW_SQL2_INST2) ) {
					$propertyname = $specprop;
					$valuekeys = array($row->value,$namespace,'','');
				} elseif ($task == SMW_SQL2_REDI2) {
					$propertyname = '_REDI';
					$valuekeys = array($row->title, $row->namespace,'','');
				} elseif ($task == SMW_SQL2_CONC2) {
					$propertyname = '_CONC';
					$valuekeys = array($row->concept, $row->docu, $row->features, $row->size, $row->depth);
				}
				if ($valuekeys !== false) {
					$this->m_semdata[$sid]->addPropertyStubValue($propertyname, $valuekeys);
				}
			}
			$db->freeResult($res);
			wfProfileOut("SMWSQLStore2::getSemanticData-task$task (SMW)");
		}

		// nary values
		if ($tasks & SMW_SQL2_NARY2) {
			// here we fetch all relevant data at once, with one call per table
			// requires filling out data for all properties in parallel
			$properties = array(); // property title objects indexed by DBkey
			$ptypes = array(); // arrays of subtypes per property, indexed by DBkey
			$dvs = array(); // datavalue objects, nested array: property DBkey x bnode x Pos

			foreach (array('smw_rels2','smw_atts2','smw_text2') as $table) {
				switch ($table) {
					case 'smw_rels2':
						$sql='SELECT r.o_id AS bnode, prop.smw_title AS prop, pos.smw_title AS pos, o.smw_title AS title, o.smw_namespace AS namespace, o.smw_iw AS iw FROM ' . $db->tableName('smw_rels2') .  ' AS r INNER JOIN ' . $db->tableName('smw_rels2') . ' AS r2 ON r.o_id=r2.s_id INNER JOIN ' . $db->tableName('smw_ids') . ' AS pos ON pos.smw_id=r2.p_id INNER JOIN ' . $db->tableName('smw_ids') . ' AS prop ON prop.smw_id=r.p_id INNER JOIN ' . $db->tableName('smw_ids') . ' AS o ON o.smw_id=r2.o_id WHERE pos.smw_iw=' . $db->addQuotes(SMW_SQL2_SMWIW) . ' AND r.s_id=' . $db->addQuotes($sid);
					break;
					case 'smw_atts2':
						$sql='SELECT r.o_id AS bnode, prop.smw_title AS prop, pos.smw_title AS pos, att.value_unit AS unit, att.value_xsd AS xsd FROM ' . $db->tableName('smw_rels2') . ' AS r INNER JOIN ' . $db->tableName('smw_atts2') . ' AS att ON r.o_id=att.s_id INNER JOIN ' . $db->tableName('smw_ids') . ' AS pos ON pos.smw_id=att.p_id INNER JOIN ' . $db->tableName('smw_ids') . ' AS prop ON prop.smw_id=r.p_id WHERE pos.smw_iw=' . $db->addQuotes(SMW_SQL2_SMWIW) . ' AND r.s_id=' . $db->addQuotes($sid);
					break;
					case 'smw_text2':
						$sql='SELECT r.o_id AS bnode, prop.smw_title AS prop, pos.smw_title AS pos, text.value_blob AS xsd FROM ' . $db->tableName('smw_rels2') . ' AS r INNER JOIN ' . $db->tableName('smw_text2') . ' AS text ON r.o_id=text.s_id INNER JOIN ' . $db->tableName('smw_ids') . ' AS pos ON pos.smw_id=text.p_id INNER JOIN ' . $db->tableName('smw_ids') . ' AS prop ON prop.smw_id=r.p_id WHERE pos.smw_iw=' . $db->addQuotes(SMW_SQL2_SMWIW) . ' AND r.s_id=' . $db->addQuotes($sid);
					break;
				}
				$res = $db->query($sql, 'SMWSQLStore2::getSemanticData-nary');
				while($row = $db->fetchObject($res)) {
					if ( !array_key_exists($row->prop,$properties) ) {
						$properties[$row->prop] = SMWPropertyValue::makeUserProperty($row->prop);
						$type = $properties[$row->prop]->getTypesValue();
						$ptypes[$row->prop] = $type->getTypeValues();
						$dvs[$row->prop] = array();
					}
					$pos = intval($row->pos);
					if ($pos >= count($ptypes[$row->prop])) continue; // out of range, maybe some old data that still waits for update
					if (!array_key_exists($row->bnode,$dvs[$row->prop])) {
						$dvs[$row->prop][$row->bnode] = array();
						for ($i=0; $i < count($ptypes[$row->prop]); $i++) { // init array
							$dvs[$row->prop][$row->bnode][$i] = NULL;
						}
					}
					$dv = SMWDataValueFactory::newTypeObjectValue($ptypes[$row->prop][$pos]);
					switch ($table) {
						case 'smw_rels2':
							$dv->setDBkeys(array($row->title, $row->namespace));
						break;
						case 'smw_atts2':
							$dv->setDBkeys(array($row->xsd, $row->unit));
						break;
						case 'smw_text2':
							$dv->setDBkeys(array($row->xsd));
						break;
					}
					$dvs[$row->prop][$row->bnode][$pos] = $dv;
				}
				$db->freeResult($res);
			}

			foreach ($properties as $name => $property) {
				$pdvs = $dvs[$name];
				foreach ($pdvs as $bnode => $values) {
					$dv = SMWDataValueFactory::newPropertyObjectValue($property);
					if ($dv instanceof SMWNAryValue) {
						$dv->setDVs($values);
						$this->m_semdata[$sid]->addPropertyObjectValue($property, $dv);
					}
				}
			}
		}

		SMWSQLStore2::$in_getSemanticData--;
		wfProfileOut("SMWSQLStore2::getSemanticData (SMW)");
		return $this->m_semdata[$sid];
	}

	/**
	 * @todo While the function can retrieve all values of a given property (i.e. values occurring for any subject),
	 * it currently will only do this for user-defined properties that are not multi-valued.
	 */
	function getPropertyValues($subject, SMWPropertyValue $property, $requestoptions = NULL, $outputformat = '') {
		wfProfileIn("SMWSQLStore2::getPropertyValues (SMW)");
		if ($property->isInverse()) { // inverses are working differently
			$noninverse = clone $property;
			$noninverse->setInverse(false);
			$result = $this->getPropertySubjects($noninverse,$subject,$requestoptions);
			wfProfileOut("SMWSQLStore2::getPropertyValues (SMW)");
			return $result;
		} elseif ($subject !== NULL) { // subject given, use semantic data cache:
			$sd = $this->getSemanticData($subject,array($property->getPropertyTypeID()));
			$result = $this->applyRequestOptions($sd->getPropertyValues($property),$requestoptions);
			if ($outputformat != '') { // reformat cached values
				$newres = array();
				foreach ($result as $dv) {
					$ndv = clone $dv;
					$ndv->setOutputFormat($outputformat);
					$newres[] = $ndv;
				}
				$result = $newres;
			}
		} else { // no subject given, get all values for the given property
			$pid = $this->getSMWPropertyID($property);
			if ( $pid == 0 ) {
				wfProfileOut("SMWSQLStore2::getPropertyValues (SMW)");
				return array();
			}
			$db =& wfGetDB( DB_SLAVE );
			$result = array();
			$mode = SMWSQLStore2::getStorageMode($property->getPropertyTypeID());
			switch ($mode) {
				case SMW_SQL2_TEXT2:
					$res = $db->select( 'smw_text2', 'value_blob',
										'p_id=' . $db->addQuotes($pid),
										'SMW::getPropertyValues', $this->getSQLOptions($requestoptions) ); ///NOTE: Do not add DISTINCT here for performance reasons
					while($row = $db->fetchObject($res)) {
						$dv = SMWDataValueFactory::newPropertyObjectValue($property);
						$dv->setOutputFormat($outputformat);
						$dv->setDBkeys(array($row->value_blob));
						$result[] = $dv;
					}
					$db->freeResult($res);
				break;
				case SMW_SQL2_RELS2:
					$res = $db->select( array('smw_rels2', 'smw_ids'),
										'smw_namespace, smw_title, smw_iw',
										'p_id=' . $db->addQuotes($pid) . ' AND o_id=smw_id' .
										$this->getSQLConditions($requestoptions,'smw_sortkey','smw_sortkey'),
										'SMW::getPropertyValues', $this->getSQLOptions($requestoptions,'smw_sortkey') + array('DISTINCT') );
					while($row = $db->fetchObject($res)) {
						$dv = SMWDataValueFactory::newPropertyObjectValue($property);
						$dv->setOutputFormat($outputformat);
						$dv->setDBkeys(array($row->smw_title, $row->smw_namespace, $row->smw_iw));
						$result[] = $dv;
					}
					$db->freeResult($res);
				break;
				case SMW_SQL2_ATTS2:
					if ( ($requestoptions !== NULL) && ($requestoptions->boundary !== NULL) ) { // the quick way to find out if this is a numeric type
						$value_column = $requestoptions->boundary->isNumeric()?'value_num':'value_xsd';
					} else { // need to do more work to find out if this is a numeric type
						$testval = SMWDatavalueFactory::newTypeIDValue($property->getPropertyTypeID());
						$value_column = $testval->isNumeric()?'value_num':'value_xsd';
					}
					$sql = 'p_id=' . $db->addQuotes($pid) .
						$this->getSQLConditions($requestoptions,$value_column,'value_xsd');
					$res = $db->select( 'smw_atts2', 'value_unit, value_xsd',
										'p_id=' . $db->addQuotes($pid) .
										$this->getSQLConditions($requestoptions,$value_column,'value_xsd'),
										'SMW::getPropertyValues', $this->getSQLOptions($requestoptions,$value_column) + array('DISTINCT') );
					while($row = $db->fetchObject($res)) {
						$dv = SMWDataValueFactory::newPropertyObjectValue($property);
						$dv->setOutputFormat($outputformat);
						$dv->setDBkeys(array($row->value_xsd, $row->value_unit));
						$result[] = $dv;
					}
					$db->freeResult($res);
				break;
				case SMW_SQL2_NARY2: ///TODO: currently disabled
// 					$type = $property->getTypesValue();
// 					$subtypes = $type->getTypeValues();
// 					$res = $db->select( $db->tableName('smw_nary'),
// 										'nary_key',
// 										$subjectcond .
// 										'attribute_title=' . $db->addQuotes($property->getDBkey()),
// 										'SMW::getPropertyValues', $this->getSQLOptions($requestoptions) );
// 					///TODO: presumably slow. Try to do less SQL queries by making a join with smw_nary
// 					while($row = $db->fetchObject($res)) {
// 						$values = array();
// 						for ($i=0; $i < count($subtypes); $i++) { // init array
// 							$values[$i] = NULL;
// 						}
// 						$res2 = $db->select( $db->tableName('smw_nary_attributes'),
// 										'nary_pos, value_unit, value_xsd',
// 										$subjectcond .
// 										'nary_key=' . $db->addQuotes($row->nary_key),
// 										'SMW::getPropertyValues');
// 						while($row2 = $db->fetchObject($res2)) {
// 							if ($row2->nary_pos < count($subtypes)) {
// 								$dv = SMWDataValueFactory::newTypeObjectValue($subtypes[$row2->nary_pos]);
// 								$dv->setDBkeys(array($row2->value_xsd, $row2->value_unit));
// 								$values[$row2->nary_pos] = $dv;
// 							}
// 						}
// 						$db->freeResult($res2);
// 						$res2 = $db->select( $db->tableName('smw_nary_longstrings'),
// 										'nary_pos, value_blob',
// 										$subjectcond .
// 										'nary_key=' . $db->addQuotes($row->nary_key),
// 										'SMW::getPropertyValues');
// 						while($row2 = $db->fetchObject($res2)) {
// 							if ( $row2->nary_pos < count($subtypes) ) {
// 								$dv = SMWDataValueFactory::newTypeObjectValue($subtypes[$row2->nary_pos]);
// 								$dv->setDBkeys(array($row2->value_blob));
// 								$values[$row2->nary_pos] = $dv;
// 							}
// 						}
// 						$db->freeResult($res2);
// 						$res2 = $db->select( $db->tableName('smw_nary_relations'),
// 										'nary_pos, object_title, object_namespace, object_id',
// 										$subjectcond .
// 										'nary_key=' . $db->addQuotes($row->nary_key),
// 										'SMW::getPropertyValues');
// 						while($row2 = $db->fetchObject($res2)) {
// 							if ( ($row2->nary_pos < count($subtypes)) &&
// 								($subtypes[$row2->nary_pos]->getDBkey() == '_wpg') ) {
// 								$dv = SMWDataValueFactory::newTypeIDValue('_wpg');
// 								$dv->setValues($row2->object_title, $row2->object_namespace, $row2->object_id);
// 								$values[$row2->nary_pos] = $dv;
// 							}
// 						}
// 						$db->freeResult($res2);
// 						$dv = SMWDataValueFactory::newPropertyObjectValue($property);
// 						$dv->setOutputFormat($outputformat);
// 						$dv->setDVs($values);
// 						$result[] = $dv;
// 					}
// 					$db->freeResult($res);
				break;
			}
		}
		wfProfileOut("SMWSQLStore2::getPropertyValues (SMW)");
		return $result;
	}

	function getPropertySubjects(SMWPropertyValue $property, $value, $requestoptions = NULL) {
		/// TODO: should we share code with #ask query computation here? Just use queries?
		wfProfileIn("SMWSQLStore2::getPropertySubjects (SMW)");
		if ($property->isInverse()) { // inverses are working differently
			$noninverse = clone $property;
			$noninverse->setInverse(false);
			$result = $this->getPropertyValues($value,$noninverse,$requestoptions);
			wfProfileOut("SMWSQLStore2::getPropertySubjects (SMW)");
			return $result;
		}
		$result = array();
		$pid = $this->getSMWPropertyID($property);
		if ( ($pid == 0) || ( ($value !== NULL) && (!$value->isValid()) ) ) {
			wfProfileOut("SMWSQLStore2::getPropertySubjects (SMW)");
			return $result;
		}
		$db =& wfGetDB( DB_SLAVE );
		// The following DB calls are all very similar, so we try to share a much code as possible.
		// If the $table parameter is set, a standard query is used in the end. Only n-aries and
		// redirects work differently.
		$table = '';
		$sql = 'p_id=' . $db->addQuotes($pid);
		$typeid = $property->getPropertyTypeID();
		$mode = SMWSQLStore2::getStorageMode($typeid);

		switch ($mode) {
		case SMW_SQL2_TEXT2:
			$table = 'smw_text2'; // ignore value condition in this case
		break;
		case SMW_SQL2_CONC2:
			$table = 'smw_conc2'; // ignore value condition in this case
		break;
		case SMW_SQL2_RELS2: case SMW_SQL2_INST2: case SMW_SQL2_SUBS2:
			if ($mode!=SMW_SQL2_RELS2) $sql = ''; // no property column here
			if ($mode==SMW_SQL2_SUBS2) { // this table is shared, filter the relevant case
				$sql = 'smw_namespace=' . (($typeid == '__sup')?$db->addQuotes(SMW_NS_PROPERTY):$db->addQuotes(NS_CATEGORY));
			}
			if ($value !== NULL) {
				$oid = $this->getSMWPageID($value->getDBkey(),$value->getNamespace(),$value->getInterwiki());
				$sql .= ($sql?" AND ":'') . 'o_id=' . $db->addQuotes($oid);
			}
			if ( ($value === NULL) || ($oid != 0) ) {
				switch ($mode) {
					case SMW_SQL2_RELS2: $table = 'smw_rels2'; break;
					case SMW_SQL2_INST2: $table = 'smw_inst2'; break;
					case SMW_SQL2_SUBS2: $table = 'smw_subs2'; break;
				}
			}
		break;
		case SMW_SQL2_ATTS2:
			$table = 'smw_atts2';
			if ($value !== NULL) {
				$keys = $value->getDBkeys();
				$sql .= ' AND value_xsd=' . $db->addQuotes($keys[0]) .
				        ' AND value_unit=' . $db->addQuotes($value->getUnit());
			}
		break;
		case SMW_SQL2_SPEC2:
			$table = 'smw_spec2';
			if ($value !== NULL) {
				$keys = $value->getDBkeys();
				$sql .= ' AND value_string=' . $db->addQuotes($keys[0]);
			}
		break;
		case SMW_SQL2_REDI2:
			$oid = $this->getSMWPageID($value->getDBkey(),$value->getNamespace(),$value->getInterwiki(),false);
			/// NOTE: we do not use the canonical (redirect-aware) id here!
			/// NOTE: we ignore sortkeys here -- this should be ok
			if ($oid != 0) {
				$res = $db->select( array('smw_redi2'), 's_title,s_namespace',
				                    'o_id=' . $db->addQuotes($oid),
				                    'SMW::getSpecialSubjects', $this->getSQLOptions($requestoptions) );
				while($row = $db->fetchObject($res)) {
					$dv = SMWWikiPageValue::makePage($row->s_title, $row->s_namespace);
					$result[] = $dv;
				}
				$db->freeResult($res);
			}
		break;
		case SMW_SQL2_NARY2:
			if ($value === NULL) { // no value -- handled just like for wikipage
				$table = 'smw_rels2';
				break;
			}
			$values = $value->getDVs();
			$smw_rels2 = $db->tableName('smw_rels2');
			$smw_ids = $db->tableName('smw_ids');
			// build a single SQL query for that
			$where = "t.p_id=" . $db->addQuotes($pid);
			$from = "$smw_rels2 AS t INNER JOIN $smw_ids AS i ON t.s_id=i.smw_id";
			$count = 0;
			foreach ($values as $dv) {
				if ( ($dv === NULL) || (!$dv->isValid()) ) {
					$count++;
					continue;
				}
				$npid = $this->makeSMWPageID(strval($count),SMW_NS_PROPERTY,SMW_SQL2_SMWIW); // might be cached; FIXME: make some of those predefined + important!
				switch (SMWSQLStore2::getStorageMode($dv->getTypeID())) {
				case SMW_SQL2_RELS2:
					$from .= " INNER JOIN $smw_rels2 AS t$count ON t.o_id=t$count.s_id INNER JOIN $smw_ids AS i$count ON t$count.o_id=i$count.smw_id";
					$where .= " AND t$count.p_id=" . $db->addQuotes($npid) .
					          " AND i$count.smw_title=" . $db->addQuotes($dv->getDBkey()) .
					          " AND i$count.smw_namespace=" . $db->addQuotes($dv->getNamespace()) .
					          " AND i$count.smw_iw=" . $db->addQuotes('');
				break;
				case SMW_SQL2_ATTS2:
					$keys = $dv->getDBkeys();
					$from .= ' INNER JOIN ' . $db->tableName('smw_atts2') . " AS t$count ON t.o_id=t$count.s_id";
					$where .= " AND t$count.p_id=" . $db->addQuotes($npid) .
					          " AND t$count.value_xsd=" . $db->addQuotes($keys[0]) .
					          " AND t$count.value_unit=" . $db->addQuotes($dv->getUnit());
				}
				$count++;
				// default: not supported (including text and code)
			}
			$res = $db->query("SELECT DISTINCT i.smw_title AS title,i.smw_namespace AS namespace,i.smw_sortkey AS sortkey FROM $from WHERE $where", 'SMW::getPropertySubjects', $this->getSQLOptions($requestoptions,'smw_sortkey'));
			while($row = $db->fetchObject($res)) {
				$result[] = SMWWikiPageValue::makePage($row->title, $row->namespace, $row->sortkey);
			}
			$db->freeResult($res);
		break;
		}

		if ($table != '') {
			$res = $db->select( array($table,'smw_ids'),
			                    'DISTINCT smw_title,smw_namespace,smw_sortkey',
			                    's_id=smw_id' . ($sql?' AND ':'') . $sql . $this->getSQLConditions($requestoptions,'smw_sortkey','smw_sortkey'),
								'SMW::getPropertySubjects',
			                    $this->getSQLOptions($requestoptions,'smw_sortkey') );
			while ($row = $db->fetchObject($res)) {
				$dv = SMWWikiPageValue::makePage($row->smw_title, $row->smw_namespace, $row->smw_sortkey);
				$result[] = $dv;
			}
			$db->freeResult($res);
		}
		wfProfileOut("SMWSQLStore2::getPropertySubjects (SMW)");
		return $result;
	}

	function getAllPropertySubjects(SMWPropertyValue $property, $requestoptions = NULL) {
		wfProfileIn("SMWSQLStore2::getAllPropertySubjects (SMW)");
		$result = $this->getPropertySubjects($property, NULL, $requestoptions);
		wfProfileOut("SMWSQLStore2::getAllPropertySubjects (SMW)");
		return $result;
	}

	function getProperties($subject, $requestoptions = NULL) {
		wfProfileIn("SMWSQLStore2::getProperties (SMW)");
		$sid = $this->getSMWPageID($subject->getDBkey(), $subject->getNamespace(),$subject->getInterwiki());
		if ($sid == 0) {
			wfProfileOut("SMWSQLStore2::getProperties (SMW)");
			return array();
		}

		$db =& wfGetDB( DB_SLAVE );
		$sql = 's_id=' . $db->addQuotes($sid) . ' AND p_id=smw_id' . $this->getSQLConditions($requestoptions,'smw_sortkey','smw_sortkey');

		$result = array();
		/// NOTE: the following also includes naries, which are now kept in smw_rels2
		foreach (array('smw_atts2','smw_text2','smw_rels2','smw_spec2') as $table) {
			$res = $db->select( array($table,'smw_ids'), 'DISTINCT smw_title',
			                    $sql, 'SMW::getProperties', $this->getSQLOptions($requestoptions,'smw_sortkey') );
			while($row = $db->fetchObject($res)) {
				$result[] = SMWPropertyValue::makeProperty($row->smw_title);
			}
			$db->freeResult($res);
		}
		wfProfileOut("SMWSQLStore2::getProperties (SMW)");
		return $result;
	}

	/**
	 * @todo This function is currently implemented only for values of type Page ('_wpg').
	 */
	function getInProperties(SMWDataValue $value, $requestoptions = NULL) {
		wfProfileIn("SMWSQLStore2::getInProperties (SMW)");
		$db =& wfGetDB( DB_SLAVE );
		$result = array();
		if (SMWSQLStore2::getStorageMode($value->getTypeID()) == SMW_SQL2_RELS2) {
			$oid = $this->getSMWPageID($value->getDBkey(),$value->getNamespace(),$value->getInterwiki());
			$sql = 'p_id=smw_id AND o_id=' . $db->addQuotes($oid) .
			       ' AND smw_iw=' . $db->addQuotes('') . // only local, non-internal properties
			       $this->getSQLConditions($requestoptions,'smw_sortkey','smw_sortkey');
			$res = $db->select( array('smw_rels2','smw_ids'), 'DISTINCT smw_title, smw_sortkey',
			                    $sql, 'SMW::getInProperties', $this->getSQLOptions($requestoptions,'smw_sortkey') );
			while($row = $db->fetchObject($res)) {
				$result[] = SMWPropertyValue::makeProperty($row->smw_title);
			}
			$db->freeResult($res);
		}
		wfProfileOut("SMWSQLStore2::getInProperties (SMW)");
		return $result;
	}

///// Writing methods /////

	function deleteSubject(Title $subject) {
		wfProfileIn('SMWSQLStore2::deleteSubject (SMW)');
		$this->deleteSemanticData(SMWWikiPageValue::makePageFromTitle($subject));
		$this->updateRedirects($subject->getDBkey(), $subject->getNamespace()); // also delete redirects, may trigger update jobs!
		if ($subject->getNamespace() == SMW_NS_CONCEPT) { // make sure to clear caches
			$db =& wfGetDB( DB_MASTER );
			$id = $this->getSMWPageID($subject->getDBkey(), $subject->getNamespace(),$subject->getInterwiki(),false);
			$db->delete('smw_conc2', array('s_id' => $id), 'SMW::deleteSubject::Conc2');
			$db->delete('smw_conccache', array('o_id' => $id), 'SMW::deleteSubject::Conccache');
		}
		///FIXME: if a property page is deleted, more pages may need to be updated by jobs!
		///TODO: who is responsible for these updates? Some update jobs are currently created in SMW_Hooks, some internally in the store
		///TODO: Possibly delete ID here (at least for non-properties/categories, if not used in any place in rels2)
		///FIXME: clean internal caches here
		wfProfileOut('SMWSQLStore2::deleteSubject (SMW)');
	}

	function updateData(SMWSemanticData $data) {
		wfProfileIn("SMWSQLStore2::updateData (SMW)");
		$subject = $data->getSubject();
		$this->deleteSemanticData($subject);
		$redirects = $data->getPropertyValues(SMWPropertyValue::makeProperty('_REDI'));
		if (count($redirects) > 0) {
			$redirect = end($redirects); // at most one redirect per page
			$this->updateRedirects($subject->getDBkey(), $subject->getNamespace(), $redirect->getDBkey(), $redirect->getNameSpace());
			wfProfileOut("SMWSQLStore2::updateData (SMW)");
			return; // stop here -- no support for annotations on redirect pages!
		} else {
			$this->updateRedirects($subject->getDBkey(),$subject->getNamespace());
		}
		// always make an ID (pages without ID cannot be in query results, not even in fixed value queries!):
		$sid = $this->makeSMWPageID($subject->getDBkey(),$subject->getNamespace(),'',true,$subject->getSortkey());
		$db =& wfGetDB( DB_MASTER );

		// do bulk updates:
		$up_rels2 = array();  $up_atts2 = array();
		$up_text2 = array();  $up_spec2 = array();
		$up_subs2 = array();  $up_inst2 = array();
		$concept_desc = NULL; // this gets a special treatment

		foreach($data->getProperties() as $property) {
			$propertyValueArray = $data->getPropertyValues($property);
			$mode = SMWSQLStore2::getStorageMode($property->getPropertyTypeID());
			foreach($propertyValueArray as $value) {
				if (!$value->isValid()) continue; // errors are already recorded in valid values, no need to store them here
				switch ($mode) {
					case SMW_SQL2_REDI2: break; // handled above
					case SMW_SQL2_INST2:
						$up_inst2[] = array(
						  's_id' => $sid,
						  'o_id' => $this->makeSMWPageID($value->getDBkey(),$value->getNamespace(),''));
					break;
					case SMW_SQL2_SUBS2:
						$up_subs2[] = array(
						  's_id' => $sid,
						  'o_id' => $this->makeSMWPageID($value->getDBkey(),$value->getNamespace(),''));
					break;
					case SMW_SQL2_CONC2:
						$concept_desc = end($propertyValueArray); // only one value per page!
					break;
					case SMW_SQL2_SPEC2:
						$keys = $value->getDBkeys();
						$up_spec2[] = array(
						  's_id' => $sid,
						  'p_id' => $this->makeSMWPropertyID($property),
						  'value_string' => $keys[0]);
					break;
					case SMW_SQL2_TEXT2:
						$keys = $value->getDBkeys();
						$up_text2[] = array(
						  's_id' => $sid,
						  'p_id' => $this->makeSMWPropertyID($property),
						  'value_blob' => $keys[0] );
					break;
					case SMW_SQL2_RELS2:
						$up_rels2[] = array(
						  's_id' => $sid,
						  'p_id' => $this->makeSMWPropertyID($property),
						  'o_id' => $this->makeSMWPageID($value->getDBkey(),$value->getNamespace(),$value->getInterwiki()) );
					break;
					case SMW_SQL2_ATTS2:
						$keys = $value->getDBkeys();
						$up_atts2[] = array(
						  's_id' => $sid,
						  'p_id' => $this->makeSMWPropertyID($property),
						  'value_unit' => $value->getUnit(),
						  'value_xsd' => $keys[0],
						  'value_num' => $value->getNumericValue() );
					break;
					case SMW_SQL2_NARY2:
						$bnode = $this->makeSMWBnodeID($sid);
						$up_rels2[] = array(
						  's_id' => $sid,
						  'p_id' => $this->makeSMWPropertyID($property),
						  'o_id' => $bnode );
						$npos = 0;
						foreach ($value->getDVs() as $dv) {
							if ( ($dv !== NULL) && ($dv->isValid()) ) {
								$pid = $this->makeSMWPageID(strval($npos),SMW_NS_PROPERTY,SMW_SQL2_SMWIW); // TODO: predefine some of those (
								switch (SMWSQLStore2::getStorageMode($dv->getTypeID())) {
								case SMW_SQL2_RELS2:
									$up_rels2[] = array(
									  's_id' => $bnode,
									  'p_id' => $pid,
									  'o_id' => $this->makeSMWPageID($dv->getDBkey(),$dv->getNamespace(),$dv->getInterwiki()) );
								break;
								case SMW_SQL2_TEXT2:
									$keys = $dv->getDBkeys();
									$up_text2[] = array(
									  's_id' => $bnode,
									  'p_id' => $pid,
									  'value_blob' => $keys[0] );
								break;
								case SMW_SQL2_ATTS2:
									$keys = $dv->getDBkeys();
									$up_atts2[] = array(
									  's_id' => $bnode,
									  'p_id' => $pid,
									  'value_unit' => $dv->getUnit(),
									  'value_xsd' => $keys[0],
									  'value_num' => $dv->getNumericValue() );
								break;
								}
								//default: no other cases expected/supported
							}
							$npos++;
						}
					break;
				}
			}
		}

		// write to DB:
		if (count($up_rels2) > 0) {
			$db->insert( 'smw_rels2', $up_rels2, 'SMW::updateRel2Data');
		}
		if (count($up_atts2) > 0) {
			$db->insert( 'smw_atts2', $up_atts2, 'SMW::updateAtt2Data');
		}
		if (count($up_text2) > 0) {
			$db->insert( 'smw_text2', $up_text2, 'SMW::updateText2Data');
		}
		if (count($up_spec2) > 0) {
			$db->insert( 'smw_spec2', $up_spec2, 'SMW::updateSpec2Data');
		}
		if (count($up_subs2) > 0) {
			$db->insert( 'smw_subs2', $up_subs2, 'SMW::updateSubs2Data');
		}
		if (count($up_inst2) > 0) {
			$db->insert( 'smw_inst2', $up_inst2, 'SMW::updateInst2Data');
		}
		// Concepts are not just written but carefully updated,
		// preserving existing metadata (cache ...) for a concept:
		if ( $subject->getNamespace() == SMW_NS_CONCEPT ) {
			if ( ($concept_desc !== NULL) && ($concept_desc->isValid()) )  {
				$up_conc2 = array(
				     'concept_txt'   => $concept_desc->getConceptText(),
				     'concept_docu'  => $concept_desc->getDocu(),
				     'concept_features' => $concept_desc->getQueryFeatures(),
				     'concept_size'  => $concept_desc->getSize(),
				     'concept_depth' => $concept_desc->getDepth()
				);
			} else {
				$up_conc2 = array(
				     'concept_txt'   => '',
				     'concept_docu'  => '',
				     'concept_features' => 0,
				     'concept_size'  => -1,
				     'concept_depth' => -1
				);
			}
			$row = $db->selectRow(
				'smw_conc2',
				array( 'cache_date', 'cache_count' ),
				array( 's_id' => $sid ),
				'SMWSQLStore2Queries::updateConst2Data'
			);
			if ( ($row === false) && ($up_conc2['concept_txt'] != '') ) { // insert newly given data
				$up_conc2['s_id'] = $sid;
				$db->insert( 'smw_conc2', $up_conc2, 'SMW::updateConc2Data');
			} elseif ($row !== false) { // update data, preserve existing entries
				$db->update('smw_conc2',$up_conc2, array('s_id'=>$sid), 'SMW::updateConc2Data');
			}
		}

		$this->m_semdata[$sid] = clone $data; // update cache, important if jobs are directly following this call
		$this->m_sdstate[$sid] = 0xFFFFFFFF; // everything that one can know
		wfProfileOut("SMWSQLStore2::updateData (SMW)");
	}

	function changeTitle(Title $oldtitle, Title $newtitle, $pageid, $redirid=0) {
		wfProfileIn("SMWSQLStore2::changeTitle (SMW)");
		///NOTE: this function ignores the given MediaWiki IDs (this store has its own IDs)
		///NOTE: this function assumes input titles to be local (no interwiki). Anything else would be too gross.
		$sid_c = $this->getSMWPageID($oldtitle->getDBkey(),$oldtitle->getNamespace(),'');
		$sid = $this->getSMWPageID($oldtitle->getDBkey(),$oldtitle->getNamespace(),'',false);
		$tid_c = $this->getSMWPageID($newtitle->getDBkey(),$newtitle->getNamespace(),'');
		$tid = $this->getSMWPageID($newtitle->getDBkey(),$newtitle->getNamespace(),'',false);

		$db =& wfGetDB( DB_MASTER );

		if ($tid_c == 0) { // target not used anywhere yet, just hijack its title for our current id
			/// NOTE: given our lazy id management, this condition may not hold, even if $newtitle is an unused new page
			if ($sid != 0) { // move only if id exists at all
				$cond_array = array( 'smw_id' => $sid );
				$val_array  = array( 'smw_title' => $newtitle->getDBkey(),
				                     'smw_namespace' => $newtitle->getNamespace());
				$db->update('smw_ids', $val_array, $cond_array, 'SMWSQLStore2::changeTitle');
			} else { // make new (target) id for use in redirect table
				$sid = $this->makeSMWPageID($newtitle->getDBkey(),$newtitle->getNamespace(),''); // make target id
			} // at this point, $sid is the id of the target page (according to smw_ids)
			$this->makeSMWPageID($oldtitle->getDBkey(),$oldtitle->getNamespace(),SMW_SQL2_SMWREDIIW); // make redirect id for oldtitle
			// update redirects
			/// NOTE: there is the (bad) case that the moved page is a redirect. As chains of
			/// redirects are not supported by MW or SMW, the below is maximally correct there too.
			$db->insert(
				'smw_redi2',
				array( 's_title' => $oldtitle->getDBkey(), 's_namespace' => $oldtitle->getNamespace(), 'o_id'=>$sid ),
				'SMWSQLStore2::changeTitle'
			);
			/// NOTE: this temporarily leaves existing redirects to oldtitle point to newtitle as well, which
			/// will be lost after the next update. Since double redirects are an error anyway, this is not
			/// a bad behaviour: everything will continue to work until the old redirect is updated, which
			/// will hopefully be to fix the double redirect.
		} else {
			$this->deleteSemanticData(SMWWikiPageValue::makePageFromTitle($newtitle)); // should not have much effect, but let's be sure
			$this->updateRedirects($newtitle->getDBkey(), $newtitle->getNamespace()); // delete these redirects, may trigger update jobs!
			$this->updateRedirects($oldtitle->getDBkey(), $oldtitle->getNamespace(), $newtitle->getDBkey(), $newtitle->getNamespace());
			// also move subject data along (updateRedirects only cares about changes in objects/properties)
			if ($sid != 0) {
				$cond_array = array( 's_id' => $sid );
				$val_array  = array( 's_id' => $tid );
				$db->update('smw_rels2', $val_array, $cond_array, 'SMWSQLStore2::changeTitle');
				$db->update('smw_atts2', $val_array, $cond_array, 'SMWSQLStore2::changeTitle');
				$db->update('smw_text2', $val_array, $cond_array, 'SMWSQLStore2::changeTitle');
				$db->update('smw_inst2', $val_array, $cond_array, 'SMWSQLStore2::changeTitle');
				if ( ( $oldtitle->getNamespace() == SMW_NS_PROPERTY ) &&
				     ( $newtitle->getNamespace() == SMW_NS_PROPERTY ) ) {
					$db->update('smw_subs2', $val_array, $cond_array, 'SMWSQLStore2::changeTitle');
				} elseif ($oldtitle->getNamespace() == SMW_NS_PROPERTY) {
					$db->delete('smw_subs2', $cond_array, 'SMWSQLStore2::changeTitle');
				} elseif ( ( $oldtitle->getNamespace() == NS_CATEGORY ) &&
				           ( $newtitle->getNamespace() == NS_CATEGORY ) ) {
					$db->update('smw_subs2', $val_array, $cond_array, 'SMWSQLStore2::changeTitle');
				} elseif ($oldtitle->getNamespace() == NS_CATEGORY) {
					$db->delete('smw_subs2', $cond_array, 'SMWSQLStore2::changeTitle');
				} elseif ( ( $oldtitle->getNamespace() == SMW_NS_CONCEPT ) &&
				           ( $newtitle->getNamespace() == SMW_NS_CONCEPT ) ) {
					$db->update('smw_conc2', $val_array, $cond_array, 'SMWSQLStore2::changeTitle');
					$db->update('smw_conccache', array('o_id' => $tid), array('o_id' => $sid), 'SMWSQLStore2::changeTitle');
				} elseif ($oldtitle->getNamespace() == SMW_NS_CONCEPT) {
					$db->delete('smw_conc2', $cond_array, 'SMWSQLStore2::changeTitle');
					$db->delete('smw_conccache', array('o_id' => $sid), 'SMWSQLStore2::changeTitle');
				}
			}
			/// TODO: may not be optimal for the standard case that newtitle existed and redirected to oldtitle (PERFORMANCE)
		}

		wfProfileOut("SMWSQLStore2::changeTitle (SMW)");
	}

///// Query answering /////

	function getQueryResult(SMWQuery $query) {
		wfProfileIn('SMWSQLStore2::getQueryResult (SMW)');
		global $smwgIP;
		include_once("$smwgIP/includes/storage/SMW_SQLStore2_Queries.php");
		$qe = new SMWSQLStore2QueryEngine($this,wfGetDB( DB_SLAVE ));
		$result = $qe->getQueryResult($query);
		wfProfileOut('SMWSQLStore2::getQueryResult (SMW)');
		return $result;
	}

///// Special page functions /////

	function getPropertiesSpecial($requestoptions = NULL) {
		wfProfileIn("SMWSQLStore2::getPropertiesSpecial (SMW)");
		$db =& wfGetDB( DB_SLAVE );
		$options = ' ORDER BY smw_sortkey';
		if ($requestoptions->limit > 0) {
			$options .= ' LIMIT ' . $requestoptions->limit;
		}
		if ($requestoptions->offset > 0) {
			$options .= ' OFFSET ' . $requestoptions->offset;
		}
		// NOTE: the query needs to do the fitlering of internal properties, else LIMIT is wrong
		$res = $db->query('(SELECT smw_id, smw_title, COUNT(*) as count, smw_sortkey FROM ' .
		                  $db->tableName('smw_rels2') . ' INNER JOIN ' . $db->tableName('smw_ids') . ' ON p_id=smw_id WHERE smw_iw=' .
		                  $db->addQuotes('') . ' OR smw_iw=' . $db->addQuotes(SMW_SQL2_SMWPREDEFIW) . ' GROUP BY smw_id,smw_title,smw_sortkey) UNION ' .
		                  '(SELECT smw_id, smw_title, COUNT(*) as count, smw_sortkey FROM ' .
		                  $db->tableName('smw_spec2') . ' INNER JOIN ' . $db->tableName('smw_ids') . ' ON p_id=smw_id WHERE smw_iw=' .
		                  $db->addQuotes('') . ' OR smw_iw=' . $db->addQuotes(SMW_SQL2_SMWPREDEFIW) . ' GROUP BY smw_id,smw_title,smw_sortkey) UNION ' .
		                  '(SELECT smw_id, smw_title, COUNT(*) as count, smw_sortkey FROM ' .
		                  $db->tableName('smw_atts2') . ' INNER JOIN ' . $db->tableName('smw_ids') . ' ON p_id=smw_id WHERE smw_iw=' .
		                  $db->addQuotes('') . ' OR smw_iw=' . $db->addQuotes(SMW_SQL2_SMWPREDEFIW) . ' GROUP BY smw_id,smw_title,smw_sortkey) UNION ' .
		                  '(SELECT smw_id, smw_title, COUNT(*) as count, smw_sortkey FROM ' .
		                  $db->tableName('smw_text2') . ' INNER JOIN ' . $db->tableName('smw_ids') . ' ON p_id=smw_id WHERE smw_iw=' .
		                  $db->addQuotes('') . ' OR smw_iw=' . $db->addQuotes(SMW_SQL2_SMWPREDEFIW) . ' GROUP BY smw_id,smw_title,smw_sortkey) ' . $options,
		                  'SMW::getPropertySubjects');
		$result = array();
		while($row = $db->fetchObject($res)) {
			$result[] = array(SMWPropertyValue::makeProperty($row->smw_title), $row->count);
		}
		$db->freeResult($res);
		wfProfileOut("SMWSQLStore2::getPropertiesSpecial (SMW)");
		return $result;
	}

	function getUnusedPropertiesSpecial($requestoptions = NULL) {
		global $wgDBtype;
		wfProfileIn("SMWSQLStore2::getUnusedPropertiesSpecial (SMW)");
		$db =& wfGetDB( DB_SLAVE );
		/// TODO: some db-calls in here can use better wrapper functions,
		/// make an options array for those and use them
		$options = ' ORDER BY title';
		if ($requestoptions->limit > 0) {
			$options .= ' LIMIT ' . $requestoptions->limit;
		}
		if ($requestoptions->offset > 0) {
			$options .= ' OFFSET ' . $requestoptions->offset;
		}
		extract( $db->tableNames('page', 'smw_rels2', 'smw_atts2', 'smw_text2', 'smw_subs2', 'smw_ids', 'smw_tmp_unusedprops', 'smw_redi2') );

		if ($wgDBtype=='postgres') { // PostgresQL: no in-memory tables available
			$sql = "CREATE OR REPLACE FUNCTION create_" . $smw_tmp_unusedprops . "() RETURNS void AS "
				   ."$$ "
				   ."BEGIN "
				   ." IF EXISTS(SELECT NULL FROM pg_tables WHERE tablename='" . $smw_tmp_unusedprops . "' AND schemaname = ANY (current_schemas(true))) "
				   ." THEN DELETE FROM " . $smw_tmp_unusedprops ."; "
				   ." ELSE "
				   ."  CREATE TEMPORARY TABLE " . $smw_tmp_unusedprops . " ( title text ); "
				   ." END IF; "
				   ."END; "
				   ."$$ "
				   ."LANGUAGE 'plpgsql'; "
				   ."SELECT create_" . $smw_tmp_unusedprops . "(); ";
		} else { // MySQL: use temporary in-memory table
			$sql = "CREATE TEMPORARY TABLE " . $smw_tmp_unusedprops . "( title VARCHAR(255) ) TYPE=MEMORY";
		}
		$db->query($sql, 'SMW::getUnusedPropertiesSpecial');
		$db->query( "INSERT INTO $smw_tmp_unusedprops SELECT page_title FROM $page" .
		            " WHERE page_namespace=" . SMW_NS_PROPERTY , 'SMW::getUnusedPropertySubjects');
		foreach (array($smw_rels2,$smw_atts2,$smw_text2) as $table) {
			$db->query( "DELETE $smw_tmp_unusedprops.* FROM $smw_tmp_unusedprops, $table INNER JOIN $smw_ids ON p_id=smw_id WHERE title=smw_title AND smw_iw=" . $db->addQuotes(''), 'SMW::getUnusedPropertySubjects');
		}
		$db->query( "DELETE $smw_tmp_unusedprops.* FROM $smw_tmp_unusedprops, $smw_subs2 INNER JOIN $smw_ids ON o_id=smw_id WHERE title=smw_title", 'SMW::getUnusedPropertySubjects');
		$db->query( "DELETE $smw_tmp_unusedprops.* FROM $smw_tmp_unusedprops, $smw_ids WHERE title=smw_title AND smw_namespace=" . $db->addQuotes(SMW_NS_PROPERTY) . ' AND smw_iw=' . $db->addQuotes(SMW_SQL2_SMWPREDEFIW), 'SMW::getUnusedPropertySubjects');
		// assume any property redirecting to some property to be used here:
		// (a stricter and more costy approach would be to delete only redirects to active properties;
		//  this would need to be done with an addtional query in the above loop)
		$db->query( "DELETE $smw_tmp_unusedprops.* FROM $smw_tmp_unusedprops, $smw_redi2 INNER JOIN $smw_ids ON (s_title=smw_title AND s_namespace=" . $db->addQuotes(SMW_NS_PROPERTY) . ") WHERE title=smw_title", 'SMW::getUnusedPropertySubjects');
		$res = $db->query("SELECT title FROM $smw_tmp_unusedprops " . $options, 'SMW::getUnusedPropertySubjects');

		$result = array();
		while($row = $db->fetchObject($res)) {
			$result[] = SMWPropertyValue::makeProperty($row->title);
		}
		$db->freeResult($res);
		$db->query("DROP TEMPORARY table $smw_tmp_unusedprops", 'SMW::getUnusedPropertySubjects');
		wfProfileOut("SMWSQLStore2::getUnusedPropertiesSpecial (SMW)");
		return $result;
	}

	function getWantedPropertiesSpecial($requestoptions = NULL) {
		global $smwgPDefaultType;
		wfProfileIn("SMWSQLStore2::getWantedPropertiesSpecial (SMW)");
		switch (SMWSQLStore2::getStorageMode($smwgPDefaultType)) {
			case SMW_SQL2_RELS2: $table = 'smw_rels2'; break;
			case SMW_SQL2_ATTS2: $table = 'smw_atts2'; break;
			case SMW_SQL2_TEXT2: $table = 'smw_text2'; break;
			default: // nothing else is plausible enough to justify more lines
				wfProfileOut("SMWSQLStore2::getWantedPropertiesSpecial (SMW)");
				return array();
		}
		$db =& wfGetDB( DB_SLAVE );
		$options = ' ORDER BY count DESC';
		if ($requestoptions->limit > 0) {
			$options .= ' LIMIT ' . $requestoptions->limit;
		}
		if ($requestoptions->offset > 0) {
			$options .= ' OFFSET ' . $requestoptions->offset;
		}
		$res = $db->query('SELECT smw_title, COUNT(*) as count FROM ' .
		                  $db->tableName($table) . ' INNER JOIN ' . $db->tableName('smw_ids') .
		                  ' ON p_id=smw_id LEFT JOIN ' . $db->tableName('page') .
		                  ' ON (page_namespace=' . SMW_NS_PROPERTY .
		                  ' AND page_title=smw_title) WHERE smw_id > 50 AND page_id IS NULL GROUP BY smw_title' . $options,
		                  'SMW::getWantedPropertiesSpecial');
		$result = array();
		while($row = $db->fetchObject($res)) {
			$result[] = array(SMWPropertyValue::makeProperty($row->smw_title), $row->count);
		}
		wfProfileOut("SMWSQLStore2::getWantedPropertiesSpecial (SMW)");
		return $result;
	}

	function getStatistics() {
		wfProfileIn('SMWSQLStore2::getStatistics (SMW)');
		$db =& wfGetDB( DB_SLAVE );
		$result = array();
		extract( $db->tableNames('smw_rels2', 'smw_atts2', 'smw_text2', 'smw_spec2') );
		$propuses = 0;
		$usedprops = 0;
		foreach (array($smw_rels2, $smw_atts2, $smw_text2) as $table) {
			/// TODO: this currently counts parts of nary properties as singular property uses
			/// Is this minor issue worth the extra join of filtering those?
			$res = $db->query("SELECT COUNT(s_id) AS count FROM $table", 'SMW::getStatistics');
			$row = $db->fetchObject( $res );
			$propuses += $row->count;
			$db->freeResult( $res );
			$res = $db->query("SELECT COUNT(DISTINCT(p_id)) AS count FROM $table", 'SMW::getStatistics');
			$row = $db->fetchObject( $res );
			$usedprops += $row->count;
			$db->freeResult( $res );
		}
		$result['PROPUSES'] = $propuses;
		$result['USEDPROPS'] = $usedprops;

		$res = $db->query("SELECT COUNT(s_id) AS count FROM $smw_spec2 WHERE p_id=" . $db->addQuotes(SMWSQLStore2::$special_ids['_TYPE']), 'SMW::getStatistics');
		$row = $db->fetchObject( $res );
		$result['DECLPROPS'] = $row->count;
		$db->freeResult( $res );

		wfProfileOut('SMWSQLStore2::getStatistics (SMW)');
		return $result;
	}

///// Setup store /////

	function setup($verbose = true) {
		global $wgDBtype;
		$this->reportProgress("Setting up standard database configuration for SMW ...\n\n",$verbose);
		$this->reportProgress("Selected storage engine is \"SMWSQLStore2\" (or an extension thereof)\n\n",$verbose);
		$db =& wfGetDB( DB_MASTER );
		extract( $db->tableNames('smw_ids','smw_rels2','smw_atts2','smw_text2',
		                         'smw_spec2','smw_subs2','smw_redi2','smw_inst2',
		                         'smw_conc2','smw_conccache') );
		$reportTo = $verbose?$this:NULL; // use $this to report back from static SMWSQLHelpers
		// repeatedly used DB field types defined here for convenience
		$dbt_id        = SMWSQLHelpers::getStandardDBType('id');
		$dbt_namespace = SMWSQLHelpers::getStandardDBType('namespace');
		$dbt_title     = SMWSQLHelpers::getStandardDBType('title');
		$dbt_iw        = SMWSQLHelpers::getStandardDBType('iw');
		$dbt_blob      = SMWSQLHelpers::getStandardDBType('blob');

		SMWSQLHelpers::setupTable($smw_ids, // internal IDs used in this store
		              array('smw_id'        => $dbt_id . ' NOT NULL' . ($wgDBtype=='postgres'?' PRIMARY KEY':' KEY AUTO_INCREMENT'),
		                    'smw_namespace' => $dbt_namespace . ' NOT NULL',
		                    'smw_title'     => $dbt_title . ' NOT NULL',
		                    'smw_iw'        => $dbt_iw,
		                    'smw_sortkey'   => $dbt_title  . ' NOT NULL'), $db, $reportTo);
		SMWSQLHelpers::setupIndex($smw_ids, array('smw_id','smw_title,smw_namespace,smw_iw', 'smw_sortkey'), $db);
		// NOTE: smw_ids is normally used to store references to wiki pages (possibly with some external
		// interwiki prefix). There are, however, some special objects that are also stored therein. These
		// are marked by special interwiki prefixes (iw) that cannot occcur in real life:
		// * Rows with iw SMW_SQL2_SMWIW describe "virtual" objects that have no page or other reference in the wiki.
		//   These are specifically the auxilliary objects ("bnodes") required to encode multi-valued properties,
		//   which are recognised by their empty title field. As a namespace, they use the id of the object that
		//   "owns" them, so that the can be reused/maintained more easily.
		//   A second object type that can occur in SMW_SQL2_SMWIW rows are the internal properties used to
		//   refer to some position in a multivalued property value. They have titles like "1", "2", "3", ...
		//   and occur only once (i.e. there is just one such property for the whoel wiki, and it has no type).
		//   The namespace of those entries is the usual property namespace.
		// * Rows with iw SMW_SQL2_SMWREDIIW are similar to normal entries for (internal) wiki pages, but the iw
		//   indicates that the page is a redirect, the target of whihc should be sought using the smw_redi2 table.
		// * The (unique) row with iw SMW_SQL2_SMWBORDERIW just marks the border between predefined ids (rows that
		//   are reserved for hardcoded ids built into SMW) and normal entries. It is no object, but makes sure that
		//   SQL's auto increment counter is high enough to not add any objects before that marked "border".

		SMWSQLHelpers::setupTable($smw_redi2, // fast redirect resolution
		              array('s_title'     => $dbt_title . ' NOT NULL',
		                    's_namespace' => $dbt_namespace . ' NOT NULL',
		                    'o_id'        => $dbt_id . ' NOT NULL'), $db, $reportTo);
		SMWSQLHelpers::setupIndex($smw_redi2, array('s_title,s_namespace','o_id'), $db);

		SMWSQLHelpers::setupTable($smw_rels2, // properties with other pages as values ("relations")
		              array('s_id' => $dbt_id . ' NOT NULL',
		                    'p_id' => $dbt_id . ' NOT NULL',
		                    'o_id' => $dbt_id . ' NOT NULL'), $db, $reportTo);
		SMWSQLHelpers::setupIndex($smw_rels2, array('s_id','p_id','o_id'), $db);

		SMWSQLHelpers::setupTable($smw_atts2, // most standard properties ("attributes")
		              array('s_id'        => $dbt_id . ' NOT NULL',
		                    'p_id'        => $dbt_id . ' NOT NULL',
		                    'value_unit'  => ($wgDBtype=='postgres'?'TEXT':'VARCHAR(63) binary'),
		                    'value_xsd'   => $dbt_title . ' NOT NULL',
		                    'value_num'   => ($wgDBtype=='postgres'?'DOUBLE PRECISION':'DOUBLE')), $db, $reportTo);
		SMWSQLHelpers::setupIndex($smw_atts2, array('s_id','p_id','value_num','value_xsd'), $db);

		SMWSQLHelpers::setupTable($smw_text2, // properties with long strings as values
		              array('s_id'        => $dbt_id . ' NOT NULL',
		                    'p_id'        => $dbt_id . ' NOT NULL',
		                    'value_blob'  => $dbt_blob), $db, $reportTo);
		SMWSQLHelpers::setupIndex($smw_text2, array('s_id','p_id'), $db);

		// field renaming between SMW 1.3 and SMW 1.4:
		if ( ($db->tableExists($smw_spec2)) && ($db->fieldExists($smw_spec2, 'sp_id', 'SMWSQLStore2::setup')) ) {
			if ($wgDBtype=='postgres') {
				$db->query("ALTER TABLE $smw_spec2 ALTER COLUMN sp_id RENAME TO p_id", 'SMWSQLStore2::setup');
			} else {
				$db->query("ALTER TABLE $smw_spec2 CHANGE `sp_id` `p_id` $dbt_id NOT NULL", 'SMWSQLStore2::setup');
			}
		}
		SMWSQLHelpers::setupTable($smw_spec2, // very important special properties, for faster access
		              array('s_id'         => $dbt_id . ' NOT NULL',
		                    'p_id'         => $dbt_id . ' NOT NULL',
		                    'value_string' => $dbt_title . ' NOT NULL'), $db, $reportTo);
		SMWSQLHelpers::setupIndex($smw_spec2, array('s_id', 'p_id', 's_id,p_id'), $db);

		SMWSQLHelpers::setupTable($smw_subs2, // subproperty/subclass relationships
		              array('s_id'        => $dbt_id . ' NOT NULL',
		                    'o_id'        => $dbt_id . ' NOT NULL'), $db, $reportTo);
		SMWSQLHelpers::setupIndex($smw_subs2, array('s_id', 'o_id'), $db);

		SMWSQLHelpers::setupTable($smw_inst2, // class instances (s_id the element, o_id the class)
		              array('s_id'        => $dbt_id . ' NOT NULL',
		                    'o_id'        => $dbt_id . ' NOT NULL',), $db, $reportTo);
		SMWSQLHelpers::setupIndex($smw_inst2, array('s_id', 'o_id'), $db);

		SMWSQLHelpers::setupTable($smw_conc2, // concept descriptions
		              array('s_id'             => $dbt_id . ' NOT NULL' . ($wgDBtype=='postgres'?' PRIMARY KEY':' KEY'),
		                    'concept_txt'      => $dbt_blob,
		                    'concept_docu'     => $dbt_blob,
		                    'concept_features' => ($wgDBtype=='postgres'?'INTEGER':'INT(8)'),
		                    'concept_size'     => ($wgDBtype=='postgres'?'INTEGER':'INT(8)'),
		                    'concept_depth'    => ($wgDBtype=='postgres'?'INTEGER':'INT(8)'),
		                    'cache_date'       => ($wgDBtype=='postgres'?'INTEGER':'INT(8) UNSIGNED'),
		                    'cache_count'      => ($wgDBtype=='postgres'?'INTEGER':'INT(8) UNSIGNED'), ), $db, $reportTo);
		SMWSQLHelpers::setupIndex($smw_conc2, array('s_id'), $db);

		SMWSQLHelpers::setupTable($smw_conccache, // concept cache: member elements (s)->concepts (o)
		              array('s_id'        => $dbt_id . ' NOT NULL',
		                    'o_id'        => $dbt_id . ' NOT NULL'), $db, $reportTo);
		SMWSQLHelpers::setupIndex($smw_conccache, array('o_id'), $db);

		$this->reportProgress("Database initialised successfully.\n\n",$verbose);
		$this->reportProgress("Setting up internal property indices ...\n",$verbose);
		// Check if we already have this structure
		$borderiw = $db->selectField($smw_ids, 'smw_iw', 'smw_id=' . $db->addQuotes(50));
		if ($borderiw != SMW_SQL2_SMWBORDERIW) {
			$this->reportProgress("   ... allocate space for internal properties\n",$verbose);
			$this->moveID(50); // make sure position 50 is empty
			$db->insert(
				'smw_ids',
				array(
					'smw_id' => 50,
					'smw_title' => '',
					'smw_namespace' => 0,
					'smw_iw' => SMW_SQL2_SMWBORDERIW,
					'smw_sortkey' => ''
				), 'SMW::setup'
			); //put dummy "border element" on index 50

			$this->reportProgress("   ",$verbose);
			for ( $i=0; $i<50; $i++ ) { // make way for built-in ids
				$this->moveID( $i );
				$this->reportProgress( ".", $verbose );
			}
			$this->reportProgress("done\n",$verbose);
		} else {
			$this->reportProgress("   ... space for internal properties already allocated.\n",$verbose);
		}
		// now write actual properties; do that each time, it is cheap enough and we can update sortkeys by current language
		$this->reportProgress("   ... writing entries for internal properties.\n",$verbose);
		foreach ( SMWSQLStore2::$special_ids as $prop => $id ) {
			$p = SMWPropertyValue::makeProperty($prop);
			$db->replace(
				'smw_ids',
				array( 'smw_id' ),
				array(
					'smw_id' => $id,
					'smw_title' => $p->getDBkey(),
					'smw_namespace' => SMW_NS_PROPERTY,
					'smw_iw' => $this->getPropertyInterwiki( $p ),
					'smw_sortkey' => $p->getDBkey()
				),
				'SMW::setup'
			);
		}
		if( $wgDBtype == 'postgres' ) {
			$this->reportProgress("   ... updating smw_ids_smw_id_seq sequence accordingly.\n",$verbose);
			$max = $db->selectField( 'smw_ids', 'max(smw_id)', array(), __METHOD__ );
			$max += 1;
			$db->query( "ALTER SEQUENCE smw_ids_smw_id_seq RESTART WITH {$max}", __METHOD__ );
		}
		$this->reportProgress("Internal properties initialised successfully.\n",$verbose);
		return true;
	}

	function drop($verbose = true) {
		global $wgDBtype;
		$this->reportProgress("Deleting all database content and tables generated by SMW ...\n\n",$verbose);
		$db =& wfGetDB( DB_MASTER );
		$tables = array('smw_rels2', 'smw_atts2', 'smw_text2', 'smw_spec2',
		                'smw_subs2', 'smw_redi2', 'smw_ids', 'smw_inst2',
		                'smw_conc2', 'smw_conccache');
		foreach ($tables as $table) {
			$name = $db->tableName($table);
			$db->query('DROP TABLE' . ($wgDBtype=='postgres'?'':' IF EXISTS'). $name, 'SMWSQLStore2::drop');
			$this->reportProgress(" ... dropped table $name.\n", $verbose);
		}
		$this->reportProgress("All data removed successfully.\n",$verbose);
		return true;
	}

	public function refreshData(&$index, $count, $namespaces = false, $usejobs = true) {
		$updatejobs = array();
		$emptyrange = true; // was nothing found in this run?

		// update by MediaWiki page id --> make sure we get all pages
		$tids = array();
		for ($i = $index; $i < $index + $count; $i++) { // array of ids
			$tids[] = $i;
		}
		$titles = Title::newFromIDs($tids);
		foreach ($titles as $title) {
			// set $wgTitle, in case semantic data is set based
			// on values not originating from the page (such as
			// via the External Data extension)
			global $wgTitle;
			$wgTitle = $title;
			if ( ($namespaces == false) || (in_array($title->getNamespace(),$namespaces)) ) {
				$updatejobs[] = new SMWUpdateJob($title);
				$emptyrange = false;
			}
		}

		// update by internal SMW id --> make sure we get all objects in SMW
		$db =& wfGetDB( DB_SLAVE );
		$res = $db->select('smw_ids', array('smw_id', 'smw_title','smw_namespace','smw_iw'),
		                   "smw_id >= $index AND smw_id < " . $db->addQuotes($index+$count), __METHOD__);
		foreach ($res as $row) {
			$emptyrange = false; // note this even if no jobs were created
			if ( ($namespaces != false) && (!in_array($row->smw_namespace,$namespaces)) ) continue;
			if ( ($row->smw_iw == '') || ($row->smw_iw == SMW_SQL2_SMWREDIIW) ) { // objects representing pages in the wiki, even special pages
				// TODO: special treament of redirects needed, since the store will not act on redirects that did not change according to its records
				$title = Title::makeTitle($row->smw_namespace, $row->smw_title);
				if ( !$title->exists() ) {
					$updatejobs[] = new SMWUpdateJob($title);
				}
			} elseif ($row->smw_iw{0} != ':') { // refresh all "normal" interwiki pages by just clearing their content
				$this->deleteSemanticData(SMWWikiPageValue::makePage($row->smw_namespace, $row->smw_title, '', $row->smw_iw));
			}
		}
		$db->freeResult($res);

		if ($usejobs) {
			Job::batchInsert($updatejobs);
		} else {
			foreach ($updatejobs as $job) {
				$job->run();
			}
		}
		$nextpos = $index + $count;
		if ($emptyrange) { // nothing found, check if there will be more pages later on
			$next1 = $db->selectField('page', 'page_id', "page_id >= $nextpos", __METHOD__, array('ORDER BY' => "page_id ASC"));
			$next2 = $db->selectField('smw_ids', 'smw_id', "smw_id >= $nextpos", __METHOD__, array('ORDER BY' => "smw_id ASC"));
			$nextpos = ( ($next2 != 0) && ($next2<$next1) )?$next2:$next1;
		}
		$max1 = $db->selectField('page', 'MAX(page_id)', '', __METHOD__);
		$max2 = $db->selectField('smw_ids', 'MAX(smw_id)', '', __METHOD__);
		$index = $nextpos?$nextpos:-1;
		return ($index>0) ? $index/max($max1,$max2) : 1;
	}


///// Concept caching /////

	/**
	 * Refresh the concept cache for the given concept.
	 *
	 * @param $concept Title
	 */
	public function refreshConceptCache($concept) {
		wfProfileIn('SMWSQLStore2::refreshConceptCache (SMW)');
		global $smwgIP;
		include_once("$smwgIP/includes/storage/SMW_SQLStore2_Queries.php");
		$qe = new SMWSQLStore2QueryEngine($this,wfGetDB( DB_MASTER ));
		$result = $qe->refreshConceptCache($concept);
		wfProfileOut('SMWSQLStore2::refreshConceptCache (SMW)');
		return $result;
	}

	/**
	 * Delete the concept cache for the given concept.
	 *
	 * @param $concept Title
	 */
	public function deleteConceptCache($concept) {
		wfProfileIn('SMWSQLStore2::deleteConceptCache (SMW)');
		global $smwgIP;
		include_once("$smwgIP/includes/storage/SMW_SQLStore2_Queries.php");
		$qe = new SMWSQLStore2QueryEngine($this,wfGetDB( DB_MASTER ));
		$result = $qe->deleteConceptCache($concept);
		wfProfileOut('SMWSQLStore2::deleteConceptCache (SMW)');
		return $result;
	}

	/**
	 * Return status of the concept cache for the given concept as an array
	 * with key 'status' ('empty': not cached, 'full': cached, 'no': not
	 * cachable). If status is not 'no', the array also contains keys 'size'
	 * (query size), 'depth' (query depth), 'features' (query features). If
	 * status is 'full', the array also contains keys 'date' (timestamp of
	 * cache), 'count' (number of results in cache).
	 *
	 * @param $concept Title or SMWWikiPageValue
	 */
	public function getConceptCacheStatus($concept) {
		wfProfileIn('SMWSQLStore2::getConceptCacheStatus (SMW)');
		$db =& wfGetDB( DB_SLAVE );
		$cid = $this->getSMWPageID($concept->getDBkey(), $concept->getNamespace(), '', false);
		$row = $db->selectRow('smw_conc2',
		         array('concept_txt','concept_features','concept_size','concept_depth','cache_date','cache_count'),
		         array('s_id'=>$cid), 'SMWSQLStore2::getConceptCacheStatus (SMW)');
		if ($row !== false) {
			$result = array('size' => $row->concept_size, 'depth' => $row->concept_depth, 'features' => $row->concept_features);
			if ($row->cache_date) {
				$result['status'] = 'full';
				$result['date'] = $row->cache_date;
				$result['count'] = $row->cache_count;
			} else {
				$result['status'] = 'empty';
			}
		} else {
			$result = array('status' => 'no');
		}
		wfProfileOut('SMWSQLStore2::getConceptCacheStatus (SMW)');
		return $result;
	}


///// Helper methods, mostly protected /////

	/**
	 * Transform input parameters into a suitable array of SQL options.
	 * The parameter $valuecol defines the string name of the column to which
	 * sorting requests etc. are to be applied.
	 */
	protected function getSQLOptions($requestoptions, $valuecol = NULL) {
		$sql_options = array();
		if ($requestoptions !== NULL) {
			if ($requestoptions->limit > 0) {
				$sql_options['LIMIT'] = $requestoptions->limit;
			}
			if ($requestoptions->offset > 0) {
				$sql_options['OFFSET'] = $requestoptions->offset;
			}
			if ( ($valuecol !== NULL) && ($requestoptions->sort) ) {
				$sql_options['ORDER BY'] = $requestoptions->ascending ? $valuecol : $valuecol . ' DESC';
			}
		}
		return $sql_options;
	}

	/**
	 * Transform input parameters into a suitable string of additional SQL conditions.
	 * The parameter $valuecol defines the string name of the column to which
	 * value restrictions etc. are to be applied.
	 * @param $requestoptions object with options
	 * @param $valuecol name of SQL column to which conditions apply
	 * @param $labelcol name of SQL column to which string conditions apply, if any
	 */
	protected function getSQLConditions($requestoptions, $valuecol, $labelcol = NULL) {
		$sql_conds = '';
		if ($requestoptions !== NULL) {
			$db =& wfGetDB( DB_SLAVE );
			if ($requestoptions->boundary !== NULL) { // apply value boundary
				if ($requestoptions->ascending) {
					$op = $requestoptions->include_boundary?' >= ':' > ';
				} else {
					$op = $requestoptions->include_boundary?' <= ':' < ';
				}
				$sql_conds .= ' AND ' . $valuecol . $op . $db->addQuotes($requestoptions->boundary);
			}
			if ($labelcol !== NULL) { // apply string conditions
				foreach ($requestoptions->getStringConditions() as $strcond) {
					$string = str_replace('_', '\_', $strcond->string);
					switch ($strcond->condition) {
						case SMWStringCondition::STRCOND_PRE:  $string .= '%'; break;
						case SMWStringCondition::STRCOND_POST: $string = '%' . $string; break;
						case SMWStringCondition::STRCOND_MID:  $string = '%' . $string . '%'; break;
					}
					$sql_conds .= ' AND ' . $labelcol . ' LIKE ' . $db->addQuotes($string);
				}
			}
		}
		return $sql_conds;
	}

	/**
	 * Not in all cases can requestoptions be forwarded to the DB using getSQLConditions()
	 * and getSQLOptions(): some data comes from caches that do not respect the options yet.
	 * This method takes an array of results (SMWDataValue or Title objects) and applies
	 * the given requestoptions as appropriate.
	 */
	protected function applyRequestOptions($data, $requestoptions) {
		wfProfileIn("SMWSQLStore2::applyRequestOptions (SMW)");
		$result = array();
		$sortres = array();
		$key = 0;
		if ( (count($data) == 0) || ($requestoptions === NULL) ) {
			wfProfileOut("SMWSQLStore2::applyRequestOptions (SMW)");
			return $data;
		}
		foreach ($data as $item) {
			$numeric = false;
			$ok = true;
			if ($item instanceof SMWWikiPageValue) {
				$label = $item->getSortkey();
				$value = $label;
			} elseif ($item instanceof SMWDataValue) {
				$keys = $item->getDBkeys(); // use DB keys since we need to mimic the behaviour of direct SQL conditions (which also use the DB key)
				$label = $keys[0];
				if ($item->isNumeric()) {
					$value = $item->getNumericValue();
					$numeric = true;
				} else {
					$value = $label;
				}
			} else { // instance of Title
				$label = $item->getText(); /// NOTE: no prefixed text, since only Text is used in SQL operations
				$value = $label;
			}
			if ($requestoptions->boundary !== NULL) { // apply value boundary
				$strc = $numeric?0:strcmp($value,$requestoptions->boundary);
				if ($requestoptions->ascending) {
					if ($requestoptions->include_boundary) {
						$ok = $numeric? ($value >= $requestoptions->boundary) : ($strc >= 0);
					} else {
						$ok = $numeric? ($value > $requestoptions->boundary) : ($strc > 0);
					}
				} else {
					if ($requestoptions->include_boundary) {
						$ok = $numeric? ($value <= $requestoptions->boundary) : ($strc <= 0);
					} else {
						$ok = $numeric? ($value < $requestoptions->boundary) : ($strc < 0);
					}
				}
			}
			foreach ($requestoptions->getStringConditions() as $strcond) { // apply string conditions
				switch ($strcond->condition) {
					case SMWStringCondition::STRCOND_PRE:
						$ok = $ok && (strpos($label,$strcond->string)===0);
						break;
					case SMWStringCondition::STRCOND_POST:
						$ok = $ok && (strpos(strrev($label),strrev($strcond->string))===0);
						break;
					case SMWStringCondition::STRCOND_MID:
						$ok = $ok && (strpos($label,$strcond->string)!==false);
						break;
				}
			}
			if ($ok) {
				$result[$key] = $item;
				$sortres[$key] = $value; // we cannot use $value as key: it is not unique if there are units!
				$key++;
			}
		}
		if ($requestoptions->sort) {
			// use last value of $numeric to indicate overall type
			$flag = $numeric?SORT_NUMERIC:SORT_LOCALE_STRING;
			if ($requestoptions->ascending) {
				asort($sortres,$flag);
			} else {
				arsort($sortres,$flag);
			}
			$newres = array();
			foreach ($sortres as $key => $value) {
				$newres[] = $result[$key];
			}
			$result = $newres;
		}
		if ($requestoptions->limit > 0) {
			$result = array_slice($result,$requestoptions->offset,$requestoptions->limit);
		} else {
			$result = array_slice($result,$requestoptions->offset);
		}
		wfProfileOut("SMWSQLStore2::applyRequestOptions (SMW)");
		return $result;
	}

	/**
	 * Print some output to indicate progress. The output message is given by
	 * $msg, while $verbose indicates whether or not output is desired at all.
	 */
	public function reportProgress($msg, $verbose = true) {
		if (!$verbose) {
			return;
		}
		if (ob_get_level() == 0) { // be sure to have some buffer, otherwise some PHPs complain
			ob_start();
		}
		print $msg;
		ob_flush();
		flush();
	}

	/**
	 * Retrieve a constant that defines how values of the given type should be stored. The constant refers
	 * to the internal storage details of this class (which table, which mapping from datavalue features to
	 * table cells, ...).
	 */
	public static function getStorageMode($typeid) {
		if (array_key_exists($typeid, SMWSQLStore2::$storage_mode)) {
			return SMWSQLStore2::$storage_mode[$typeid];
		} else {
			return SMW_SQL2_ATTS2;
		}
	}

	/**
	 * Find the numeric ID used for the page of the given title and namespace.
	 * If $canonical is set to true, redirects are taken into account to find the
	 * canonical alias ID for the given page.
	 * If no such ID exists, 0 is returned.
	 */
	public function getSMWPageID($title, $namespace, $iw, $canonical=true) {
		$sort = '';
		return $this->getSMWPageIDandSort($title, $namespace, $iw, $sort, $canonical);
	}

	/**
	 * Like getSMWPageID, but also sets the Call-By-Ref parameter $sort to the current
	 * sortkey.
	 */
	public function getSMWPageIDandSort($title, $namespace, $iw, &$sort, $canonical) {
		global $smwgQEqualitySupport;
		wfProfileIn('SMWSQLStore2::getSMWPageID (SMW)');
		$ckey = "$iw $namespace $title C";
		$nkey = "$iw $namespace $title -";
		$key = ($canonical?$ckey:$nkey);
		if (array_key_exists($key,$this->m_ids)) {
			wfProfileOut('SMWSQLStore2::getSMWPageID (SMW)');
			return $this->m_ids[$key];
		}
		if (count($this->m_ids)>1500) { // prevent memory leak in very long PHP runs
			$this->m_ids = array();
		}
		$db =& wfGetDB( DB_SLAVE );
		$id = 0;
		$redirect = false;
		if ($iw != '') {
			$res = $db->select('smw_ids', array('smw_id','smw_sortkey'), 'smw_title=' . $db->addQuotes($title) . ' AND ' . 'smw_namespace=' . $db->addQuotes($namespace) . ' AND smw_iw=' . $db->addQuotes($iw), 'SMW::getSMWPageID', array('LIMIT'=>1));
			if ($row = $db->fetchObject($res)) {
				$id = $row->smw_id;
				$sort = $row->smw_sortkey;
			}
		} else { // check for potential redirects also
			$res = $db->select('smw_ids', array('smw_id', 'smw_iw', 'smw_sortkey'), 'smw_title=' . $db->addQuotes($title) . ' AND ' . 'smw_namespace=' . $db->addQuotes($namespace) . ' AND (smw_iw=' . $db->addQuotes('') . ' OR smw_iw=' . $db->addQuotes(SMW_SQL2_SMWREDIIW) . ')', 'SMW::getSMWPageID', array('LIMIT'=>1));
			if ($row = $db->fetchObject($res)) {
				$sort = $row->smw_sortkey;
				$id = $row->smw_id; // set id in any case, the below check for properties will use even the redirect id in emergency
				if ( ($row->smw_iw == '') || (!$canonical) || ($smwgQEqualitySupport == SMW_EQ_NONE) ) {
					if ($row->smw_iw == '') {
						$this->m_ids[$ckey] = $id; // what we found is also the canonical key, cache it
					}
				} else {
					$redirect = true;
					$this->m_ids[$nkey] = $id; // what we found is the non-canonical key, cache it
				}
			}
		}
		$db->freeResult($res);

		if ($redirect) { // get redirect alias
			if ($namespace == SMW_NS_PROPERTY) { // redirect properties only to properties
				/// FIXME: Shouldn't this condition be ensured during writing?
				$res = $db->select(
					array( 'smw_redi2', 'smw_ids' ),
					'o_id',
					'o_id=smw_id AND smw_namespace=s_namespace AND s_title=' . $db->addQuotes($title) . ' AND s_namespace=' . $db->addQuotes($namespace),
					'SMW::getSMWPageID',
					array( 'LIMIT'=>1 )
				);
			} else {
				$res = $db->select(
					'smw_redi2',
					'o_id',
					's_title=' . $db->addQuotes($title) . ' AND s_namespace=' . $db->addQuotes($namespace),
					'SMW::getSMWPageID',
					array( 'LIMIT' => 1 )
				);
			}
			if ($row = $db->fetchObject($res)) {
				$id = $row->o_id;
			}
			$db->freeResult($res);
		}
		$this->m_ids[$key] = $id;
		wfProfileOut('SMWSQLStore2::getSMWPageID (SMW)');
		return $id;
	}

	/**
	 * Find the numeric ID used for the page of the given title and namespace.
	 * If $canonical is set to true, redirects are taken into account to find the
	 * canonical alias ID for the given page.
	 * If no such ID exists, a new ID is created and returned.
	 * In any case, the current sortkey is set to the given one unless $sortkey
	 * is empty.
	 * @note Using this with $canonical==false may make sense, especially when
	 * the title is a redirect target (we do not want chains of redirects).
	 */
	protected function makeSMWPageID($title, $namespace, $iw, $canonical=true, $sortkey = '') {
		wfProfileIn('SMWSQLStore2::makeSMWPageID (SMW)');
		$oldsort = '';
		$id = $this->getSMWPageIDandSort($title, $namespace, $iw, $oldsort, $canonical);
		if ($id == 0) {
			$db =& wfGetDB( DB_MASTER );
			$sortkey = $sortkey?$sortkey:(str_replace('_',' ',$title));
			$db->insert('smw_ids',
				array(
					'smw_id' => $db->nextSequenceValue('smw_ids_smw_id_seq'),
					'smw_title' => $title,
					'smw_namespace' => $namespace,
					'smw_iw' => $iw,
					'smw_sortkey' => $sortkey
				),
				'SMW::makeSMWPageID');
			$id = $db->insertId();
			$this->m_ids["$iw $namespace $title -"] = $id; // fill that cache, even if canonical was given
			// This ID is also authorative for the canonical version.
			// This is always the case: if $canonical===false and $id===0, then there is no redi-entry in
			// smw_ids either, hence the object just did not exist at all.
			$this->m_ids["$iw $namespace $title C"] = $id;
		} elseif ( ($sortkey != '') && ($sortkey != $oldsort) ) {
			$db =& wfGetDB( DB_MASTER );
			$db->update('smw_ids', array('smw_sortkey' => $sortkey), array('smw_id' => $id), 'SMW::makeSMWPageID');
		}
		wfProfileOut('SMWSQLStore2::makeSMWPageID (SMW)');
		return $id;
	}

	/**
	 * Properties have a mechanisms for being predefined (i.e. in PHP instead of in wiki). Special
	 * "interwiki" prefixes are separate the ids of such predefined properties from the ids for the
	 * current pages (which may, e.g. be moved, while the predefined object is not movable!).
	 */
	private function getPropertyInterwiki(SMWPropertyValue $property) {
		if ($property->isUserDefined()) {
			return '';
		} elseif ($property->isVisible()) {
			return SMW_SQL2_SMWPREDEFIW;
		} else {
			return SMW_SQL2_SMWINTDEFIW;
		}
	}

	/**
	 * Like getSMWPageID but taking into account that properties might be predefined.
	 */
	public function getSMWPropertyID(SMWPropertyValue $property) {
		if ( (!$property->isUserDefined()) && (array_key_exists($property->getPropertyID(), SMWSQLStore2::$special_ids))) { // very important property?
			return SMWSQLStore2::$special_ids[$property->getPropertyID()];
		} else {
			return $this->getSMWPageID($property->getDBkey(),SMW_NS_PROPERTY,$this->getPropertyInterwiki($property),true);
		}
	}

	/**
	 * Like makeSMWPageID but taking into account that properties might be predefined.
	 */
	protected function makeSMWPropertyID(SMWPropertyValue $property) {
		if ( (!$property->isUserDefined()) && (array_key_exists($property->getPropertyID(), SMWSQLStore2::$special_ids))) { // very important property?
			return SMWSQLStore2::$special_ids[$property->getPropertyID()];
		} else {
			return $this->makeSMWPageID($property->getDBkey(),SMW_NS_PROPERTY,$this->getPropertyInterwiki($property),true);
		}
	}

	/**
	 * Extend the ID cache as specified. This is called in places where IDs are retrieved
	 * by SQL queries and it would be a pity to throw them away. This function expects to
	 * get the contents of a line in smw_ids, i.e. possibly with iw being SMW_SQL2_SMWREDIIW.
	 * This information is used to determine whether the given ID is canonical or not.
	 */
	public function cacheSMWPageID($id, $title, $namespace, $iw) {
		$real_iw = ($iw == SMW_SQL2_SMWREDIIW)?'':$iw;
		$ckey = "$iw $namespace $title C";
		$nkey = "$iw $namespace $title -";
		if (count($this->m_ids)>1500) { // prevent memory leak in very long PHP runs
			$this->m_ids = array();
		}
		$this->m_ids[$nkey] = $id;
		if ($real_iw === $iw) {
			$this->m_ids[$ckey] = $id;
		}
	}

	/**
	 * Get a numeric ID for some Bnode that is to be used to encode an arbitrary
	 * n-ary property. Bnodes are managed through the smw_ids table but will always
	 * have an empty smw_title, and smw_namespace being set to the parent object
	 * (the id of the page that uses the Bnode). Unused Bnodes are not deleted but
	 * marked as available by setting smw_namespace to 0. This method then tries to
	 * reuse an unused bnode before making a new one.
	 * @note Every call to this function, even if the same parameter id is used, returns
	 * a new bnode id!
	 */
	protected function makeSMWBnodeID($sid) {
		$db =& wfGetDB( DB_MASTER );
		$id = 0;
		// check if there is an unused bnode to take:
		$res = $db->select(
			'smw_ids',
			'smw_id',
			'smw_title=' . $db->addQuotes('') . ' AND ' . 'smw_namespace=' . $db->addQuotes(0) . ' AND smw_iw=' . $db->addQuotes(SMW_SQL2_SMWIW),
			'SMW::makeSMWBnodeID',
			array( 'LIMIT' => 1 )
		);
		if ($row = $db->fetchObject($res)) {
			$id = $row->smw_id;
		}
		// claim that bnode:
		if ($id != 0) {
			$db->update(
				'smw_ids',
				array( 'smw_namespace' => $sid ),
				array(
					'smw_id'=>$id,
					'smw_title' => '',
					'smw_namespace' => 0,
					'smw_iw' => SMW_SQL2_SMWIW
				),
				'SMW::makeSMWBnodeID',
				array( 'LIMIT'=>1 )
			);
			if ($db->affectedRows() == 0) { // Oops, someone was faster (collisions are possible here, no locks)
				$id = 0; // fallback: make a new node (TODO: we could also repeat to try another ID)
			}
		}
		// if no node was found yet, make a new one:
		if ($id == 0) {
			$db->insert('smw_ids', array(
				'smw_id' => $db->nextSequenceValue('smw_ids_smw_id_seq'),
				'smw_title' => '',
				'smw_namespace' => $sid,
				'smw_iw' => SMW_SQL2_SMWIW), 'SMW::makeSMWBnodeID'
			);
			$id = $db->insertId();
		}
		return $id;
	}

	/**
	 * Change an internal id to another value. If no target value is given, the value is changed
	 * to become the last id entry (based on the automatic id increment of the database). Whatever
	 * currently occupies this id will be moved consistently in all relevant tables. Whatever
	 * currently occupies the target id will be ignored (it should be ensured that nothing is moved
	 * to an id that is still in use somewhere).
	 */
	protected function moveID($curid, $targetid = 0) {
		$db =& wfGetDB( DB_MASTER );
		$row = $db->selectRow(
			'smw_ids',
			array( 'smw_id', 'smw_namespace', 'smw_title', 'smw_iw', 'smw_sortkey' ),
			array( 'smw_id' => $curid ),
			'SMWSQLStore2::moveID'
		);
		if ($row === false) return; // no id at current position, ignore
		if ($targetid == 0) {
			$db->insert('smw_ids',
				array(
					'smw_id' => $db->nextSequenceValue('smw_ids_smw_id_seq'),
					'smw_title' => $row->smw_title,
					'smw_namespace' => $row->smw_namespace,
					'smw_iw' => $row->smw_iw,
					'smw_sortkey' => $row->smw_sortkey
				),
				'SMW::moveID'
			);
			$targetid = $db->insertId();
		} else {
			$db->insert('smw_ids',
				array(
					'smw_id' => $targetid,
					'smw_title' => $row->smw_title,
					'smw_namespace' => $row->smw_namespace,
					'smw_iw' => $row->smw_iw,
					'smw_sortkey' => $row->smw_sortkey
				),
				'SMW::moveID'
			);
		}
		$db->delete('smw_ids', array('smw_id'=>$curid), 'SMWSQLStore2::moveID');
		// Bnode references use namespace field to store ids:
		$db->update('smw_ids',
			array('smw_namespace' => $targetid),
			array('smw_title' => '', 'smw_namespace' => $curid, 'smw_iw' => SMW_SQL2_SMWIW),
			'SMW::moveID'
		);

		// now change all id entries in all other tables:
		$cond_array = array( 's_id' => $curid );
		$val_array  = array( 's_id' => $targetid );
		$db->update('smw_rels2', $val_array, $cond_array, 'SMW::moveID');
		$db->update('smw_atts2', $val_array, $cond_array, 'SMW::moveID');
		$db->update('smw_text2', $val_array, $cond_array, 'SMW::moveID');
		$db->update('smw_spec2', $val_array, $cond_array, 'SMW::moveID');
		$db->update('smw_subs2', $val_array, $cond_array, 'SMW::moveID');
		$db->update('smw_inst2', $val_array, $cond_array, 'SMW::moveID');
		if ($row->smw_namespace == SMW_NS_CONCEPT) {
			$db->update('smw_conc2', $val_array, $cond_array, 'SMW::moveID');
		}
		$db->update('smw_conccache', $val_array, $cond_array, 'SMW::moveID');
		if ($row->smw_namespace == SMW_NS_PROPERTY) {
			$cond_array = array( 'p_id' => $curid );
			$val_array  = array( 'p_id' => $targetid );
			$db->update('smw_rels2', $val_array, $cond_array, 'SMW::moveID');
			$db->update('smw_atts2', $val_array, $cond_array, 'SMW::moveID');
			$db->update('smw_text2', $val_array, $cond_array, 'SMW::moveID');
			$db->update('smw_spec2', $val_array, $cond_array, 'SMW::moveID');
		}
		$cond_array = array( 'o_id' => $curid );
		$val_array  = array( 'o_id' => $targetid );
		$db->update('smw_redi2', $val_array, $cond_array, 'SMW::moveID');
		$db->update('smw_rels2', $val_array, $cond_array, 'SMW::moveID');
		$db->update('smw_subs2', $val_array, $cond_array, 'SMW::moveID');
		$db->update('smw_inst2', $val_array, $cond_array, 'SMW::moveID');
		$db->update('smw_conccache', $val_array, $cond_array, 'SMW::moveID');
	}

	/**
	 * Delete all semantic data stored for the given subject.
	 * Used for update purposes.
	 */
	public function deleteSemanticData(SMWWikiPageValue $subject) {
		$db =& wfGetDB( DB_MASTER );
		/// NOTE: redirects are handled by updateRedirects(), not here!
			//$db->delete('smw_redi2', array('s_title' => $subject->getDBkey(),'s_namespace' => $subject->getNamespace()), 'SMW::deleteSubject::Redi2');
		$id = $this->getSMWPageID($subject->getDBkey(),$subject->getNamespace(),$subject->getInterwiki(),false);
		if ($id == 0) return; // not (directly) used anywhere yet, maybe a redirect but we do not care here
		$db->delete('smw_rels2', array('s_id' => $id), 'SMW::deleteSubject::Rels2');
		$db->delete('smw_atts2', array('s_id' => $id), 'SMW::deleteSubject::Atts2');
		$db->delete('smw_text2', array('s_id' => $id), 'SMW::deleteSubject::Text2');
		$db->delete('smw_spec2', array('s_id' => $id), 'SMW::deleteSubject::Spec2');
		$db->delete('smw_inst2', array('s_id' => $id), 'SMW::deleteSubject::Inst2');
		if ( ($subject->getNamespace() == SMW_NS_PROPERTY) || ($subject->getNamespace() == NS_CATEGORY) ) {
			$db->delete('smw_subs2', array('s_id' => $id), 'SMW::deleteSubject::Subs2');
		}

		// find bnodes used by this ID ...
		$res = $db->select(
			'smw_ids',
			'smw_id','smw_title=' . $db->addQuotes('') . ' AND smw_namespace=' . $db->addQuotes($id) . ' AND smw_iw=' . $db->addQuotes(SMW_SQL2_SMWIW),
			'SMW::deleteSubject::Nary'
		);
		// ... and delete them as well
		while ($row = $db->fetchObject($res)) {
			$db->delete('smw_rels2', array('s_id' => $row->smw_id), 'SMW::deleteSubject::NaryRels2');
			$db->delete('smw_atts2', array('s_id' => $row->smw_id), 'SMW::deleteSubject::NaryAtts2');
			$db->delete('smw_text2', array('s_id' => $row->smw_id), 'SMW::deleteSubject::NaryText2');
		}
		$db->freeResult($res);
		// free all affected bnodes in one call:
		$db->update(
			'smw_ids',
			array( 'smw_namespace' => 0 ),
			array( 'smw_title' => '', 'smw_namespace' => $id, 'smw_iw' => SMW_SQL2_SMWIW ),
			'SMW::deleteSubject::NaryIds'
		);
		wfRunHooks('smwDeleteSemanticData', array($subject));
	}

	/**
	 * Trigger all necessary updates for redirect structure on creation, change, and deletion
	 * of redirects. The title+namespace of the affected page and of its updated redirect
	 * target are given. The target can be empty ('') if none is specified.
	 * Returns the canonical ID that is now to be used for the subject, or 0 if the subject did
	 * not occur anywhere yet.
	 * @note This method must do a lot of updates right, and some care is needed to not confuse
	 * ids or forget relevant tables. Please make sure you understand the relevant cases before
	 * making changes, especially since errors may go unnoticed for some time.
	 */
	protected function updateRedirects($subject_t, $subject_ns, $curtarget_t='', $curtarget_ns=-1) {
		global $smwgQEqualitySupport, $smwgEnableUpdateJobs;
		$sid = $this->getSMWPageID($subject_t, $subject_ns, '', false); // find real id of subject, if any
		/// NOTE: $sid can be 0 here; this is useful to know since it means that fewer table updates are needed
		$db =& wfGetDB( DB_SLAVE );
		$res = $db->select( array('smw_redi2'),'o_id','s_title=' . $db->addQuotes($subject_t) .
		                    ' AND s_namespace=' . $db->addQuotes($subject_ns),
		                    'SMW::updateRedirects', array('LIMIT' => 1) );
		$old_tid = ($row = $db->fetchObject($res))?$row->o_id:0; // real id of old target, if any
		$db->freeResult($res);
		$new_tid = $curtarget_t?($this->makeSMWPageID($curtarget_t, $curtarget_ns, '', false)):0; // real id of new target
		/// NOTE: $old_tid and $new_tid both ignore further redirects, (intentionally) no redirect chains!
		if ($old_tid == $new_tid) { // no change, all happy
			return ($new_tid==0)?$sid:$new_tid;
		}
		$db =& wfGetDB( DB_MASTER ); // now we need to write something
		if ( ($old_tid == 0) && ($sid != 0) && ($smwgQEqualitySupport != SMW_EQ_NONE) ) {
			// new redirect, directly change object entries of $sid to $new_tid
			/// NOTE: if $sid == 0, then nothing needs to be done here
			$db->update('smw_rels2', array( 'o_id' => $new_tid ), array( 'o_id' => $sid ), 'SMW::updateRedirects');
			if ( ( $subject_ns == SMW_NS_PROPERTY ) && ( $curtarget_ns == SMW_NS_PROPERTY ) ) {
				$cond_array = array( 'p_id' => $sid );
				$val_array  = array( 'p_id' => $new_tid );
				$db->update('smw_rels2', $val_array, $cond_array, 'SMW::updateRedirects');
				$db->update('smw_atts2', $val_array, $cond_array, 'SMW::updateRedirects');
				$db->update('smw_text2', $val_array, $cond_array, 'SMW::updateRedirects');
				$db->update('smw_subs2', array( 'o_id' => $new_tid ), array( 'o_id' => $sid ), 'SMW::updateRedirects');
			} elseif ($subject_ns == SMW_NS_PROPERTY) { // delete triples that are only allowed for properties
				$db->delete('smw_rels2', array( 'p_id' => $sid ), 'SMW::updateRedirects');
				$db->delete('smw_atts2', array( 'p_id' => $sid ), 'SMW::updateRedirects');
				$db->delete('smw_text2', array( 'p_id' => $sid ), 'SMW::updateRedirects');
				$db->delete('smw_subs2', array( 'o_id' => $sid ), 'SMW::updateRedirects');
			} elseif ( ( $subject_ns == NS_CATEGORY ) && ( $curtarget_ns == NS_CATEGORY ) ) {
				$db->update('smw_subs2', array( 'o_id' => $new_tid ), array( 'o_id' => $sid ), 'SMW::updateRedirects');
				$db->update('smw_inst2', array( 'o_id' => $new_tid ), array( 'o_id' => $sid ), 'SMW::updateRedirects');
			} elseif ($subject_ns == NS_CATEGORY) { // delete triples that are only allowed for categories
				$db->delete('smw_subs2', array( 'o_id' => $sid ), 'SMW::updateRedirects');
				$db->delete('smw_inst2', array( 'o_id' => $sid ), 'SMW::updateRedirects');
			}
		} elseif ($old_tid != 0) { // existing redirect is overwritten
			// we do not know which entries of $old_tid are now $new_tid/$sid
			// -> ask SMW to update all affected pages as soon as possible (using jobs)
			//first delete the existing redirect:
			$db->delete('smw_redi2', array('s_title' => $subject_t,'s_namespace' => $subject_ns), 'SMW::updateRedirects');
			if ( $smwgEnableUpdateJobs && ($smwgQEqualitySupport != SMW_EQ_NONE) ) { // further updates if equality reasoning is enabled
				$jobs = array();
				$res = $db->select( array('smw_rels2','smw_ids'),'DISTINCT smw_title,smw_namespace',
				                    's_id=smw_id AND o_id=' . $db->addQuotes($old_tid),
				                    'SMW::updateRedirects');
				while ($row = $db->fetchObject($res)) {
					$t = Title::makeTitle($row->smw_namespace,$row->smw_title);
					$jobs[] = new SMWUpdateJob($t);
				}
				$db->freeResult($res);
				if ( $subject_ns == SMW_NS_PROPERTY ) {
					/// TODO: this would be more efficient if we would know the type of the
					/// property, but the current architecture deletes this first (PERFORMANCE)
					foreach (array('smw_rels2','smw_atts2','smw_text2') as $table) {
						$res = $db->select( array($table,'smw_ids'),'DISTINCT smw_title,smw_namespace',
						                    's_id=smw_id AND p_id=' . $db->addQuotes($old_tid),
						                    'SMW::updateRedirects');
						while ($row = $db->fetchObject($res)) {
							$t = Title::makeTitle($row->smw_namespace,$row->smw_title);
							$jobs[] = new SMWUpdateJob($t);
						}
					}
					$res = $db->select( array('smw_subs2','smw_ids'),'DISTINCT smw_title,smw_namespace',
					                    's_id=smw_id AND o_id=' . $db->addQuotes($old_tid),
					                    'SMW::updateRedirects');
					while ($row = $db->fetchObject($res)) {
						$t = Title::makeTitle($row->smw_namespace,$row->smw_title);
						$jobs[] = new SMWUpdateJob($t);
					}
				} elseif ( $subject_ns == NS_CATEGORY ) {
					foreach (array('smw_subs2','smw_inst2') as $table) {
						$res = $db->select( array($table,'smw_ids'),'DISTINCT smw_title,smw_namespace',
						                    's_id=smw_id AND o_id=' . $db->addQuotes($old_tid),
						                    'SMW::updateRedirects');
						while ($row = $db->fetchObject($res)) {
							$t = Title::makeTitle($row->smw_namespace,$row->smw_title);
							$jobs[] = new SMWUpdateJob($t);
						}
					}
				}
				Job::batchInsert($jobs); ///NOTE: this only happens if $smwgEnableUpdateJobs was true above
			}
		}
		// finally, write the new redirect AND refresh your internal canonical id cache!
		if ($sid == 0) {
			$sid = $this->makeSMWPageID($subject_t, $subject_ns, '', false);
		}
		if ($new_tid != 0) {
			$db->insert( 'smw_redi2', array('s_title'=>$subject_t, 's_namespace'=>$subject_ns, 'o_id'=>$new_tid), 'SMW::updateRedirects');
			if ($smwgQEqualitySupport != SMW_EQ_NONE) {
				$db->update('smw_ids', array('smw_iw'=>SMW_SQL2_SMWREDIIW), array('smw_id'=>$sid), 'SMW::updateRedirects');
			}
			$this->m_ids[" $subject_ns $subject_t C"] = $new_tid; // "iw" is empty here
		} else {
			$this->m_ids[" $subject_ns $subject_t C"] = $sid; // "iw" is empty here
			if ($smwgQEqualitySupport != SMW_EQ_NONE) {
				$db->update('smw_ids', array('smw_iw'=>''), array('smw_id'=>$sid), 'SMW::updateRedirects');
			}
		}
		// just flush those caches to be safe, they are not essential in program runs with redirect updates
		unset($this->m_semdata[$sid]); unset($this->m_semdata[$new_tid]); unset($this->m_semdata[$old_tid]);
		unset($this->m_sdstate[$sid]); unset($this->m_sdstate[$new_tid]); unset($this->m_sdstate[$old_tid]);
		return ($new_tid==0)?$sid:$new_tid;
	}

}
