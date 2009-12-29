<?php

function output($text)
{
    global $wgOut;
    $wgOut->addHTML($text);
}

function run()
{
  output("foo");
}


function wfSpecialFlowDreamfish( $params )
{
    $proc = new FlowDreamfish( $params );
    
    $proc->execute();
}

class dfUser
{
    public static function getCurrentName()
    {
        global $wgUser;
        if ( $wgUser->isLoggedIn() )
        {
            return $wgUser->getName();
        }
        else 
        {
            return "";
        }
    }
    
    public static function getAll()
    {
        $arr = array();
        $dbr  = &wfGetDB(DB_MASTER);
				$tbl   = $dbr->tableName('user');
    
        $res  = $dbr->select($tbl, 'user_name', "NULL IS NULL", __METHOD__);
        while ($row = $dbr->fetchRow($res)) {
            array_push($arr, $row);
        }
        $dbr->freeResult($res);        
        
        return $arr;
    }
}


class dfPage
{
    public $content = '';
    public $title = '';
    public $isSaved = false;
    
    function __construct($title)
    {
        $this->title = $title;
    }
    
    public function save()
    {
        $titleObj = Title::newFromText($this->title);
        $articleObj = new Article($titleObj);
        $articleObj->doEdit($this->content, "test");
    }
    
    public static function get($title)
    {
        $titleObj = Title::newFromText($title);

        $articleObj = new Article($titleObj);        
        $page = new dfPage($title);
        $page->content = $articleObj->getContent();
        if ($articleObj->getID() != 0)
            $page->isSaved = true;
            
        return $page;
    }
    
    public static function find($title)
    {
        $db = wfGetDB( DB_MASTER );
        //$s = $db->selectRow( 'page', array( 'page_title' ), array( 'page_title' => $fname ), __METHOD__ );
        /*
        		$row = $dbr->selectRow( 'job', '*', "job_id >= ${offset}", __METHOD__,
			array( 'ORDER BY' => 'job_id', 'LIMIT' => 1 ));
*/

/*

$sql = "SELECT img_timestamp from $image";
	if ($hidebotsql) {
		$sql .= "$hidebotsql WHERE ug_group IS NULL";
	}
	$sql .= ' ORDER BY img_timestamp DESC LIMIT 1';
	$res = $dbr->query( $sql, __FUNCTION__ );
	$row = $dbr->fetchRow( $res );
	if( $row !== false ) {
		$ts = $row[0];
	} else {
		$ts = false;
	}
	$dbr->freeResult( $res );
	$sql = '';


*/
        $s = $db->selectRow( 'page', array( 'page_title' ), array( 'page_title' => $fname ), __METHOD__ );
        print $s->user_name;
    }
}

class FlowDreamfish extends SpecialPage
{
    var $params = null;
    function __construct( &$params )
    {
        $this->params = $params;
    }
    function execute( )
    {
        $this->setHeaders();
        run();
    }
    
    function getDescription()
    {
        return "";
    }
}

