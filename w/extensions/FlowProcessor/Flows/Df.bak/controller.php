<?php

require_once 'lib/limonade.php';

/*
Some issues: Error handling isn't working very well. If there's an error you'll just get a white screen. This needs to be fixed

*/


dispatch('/Special:FlowDreamfish/', 'MainController_index');
dispatch('/Special:FlowDreamfish/project', 'ProjectController::index');
dispatch('/Special:FlowDreamfish/project/new', 'ProjectController::create_or_edit');
dispatch('/Special:FlowDreamfish/project/edit/:id', 'ProjectController::create_or_edit');
dispatch_post('/Special:FlowDreamfish/project/save', 'ProjectController::save');



function MainController_index()
{
    return output("check out <a href=\"" . url_for("project") . "\">project</a>");
}



class ProjectController
{  
  public static function index()
  {    
      $projects = Project::getAll();      
      set('user', dfUser::getCurrentName());      
      set('projects', $projects);
      $myProjects = ProjectController::getMyProjects($projects);
      set('myProjects', $myProjects);
      
      output('/projects/index.html.php');            
  }     
  
  public static function create_or_edit()
  {
      $id = params('id');
      if (empty($id))
          $project = new Project();
      else
          $project = Project::getById($id);
      
      $users = dfUser::getAll();

      set('project', $project);
      set('users', $users);
      
      output('/projects/edit.html.php');      
  }
  
  
  public static function save()
  {
      $input = array(
          "name" => $_POST["name"],
          "id" => $_POST["id"],
          "members" => $_POST["members"],
          "description" => $_POST["description"]
      );
      
      if ($_POST["action"] == "Save") {
          $project = new Project($input);      
          $project->save();
      }
      else if ($_POST["action"] == "Delete") {
          $project = Project::getById($input["id"]);
          $project->delete();      
      }
      //redirect so that we don't repost on refresh
      redirect_to("project");
  }       
  
  private static function getMyProjects($projects) {
  
      $me = dfUser::getCurrentName();
      
      $my_projects = array();  
      
      foreach($projects as $project) {
          if (!empty($project->members) and is_array($project->members)) {
              foreach($project->members as $member) {
                  if ($member == $me) {
                      array_push($my_projects, $project);
                  }
              }
          }
      }

      return $my_projects;  
  }
}

class HtmlHelper
{
    public static function multiple_select_list($arr, $key, $selected)
    {
        $html = array();
        foreach($arr as $item) {
            $selectedtag = "";
            if (!empty($selected) and is_array($selected)) {
                foreach($selected as $sel) {
                    if ($sel == $item[$key]) {
                        $selectedtag = "selected = \"selected\"";
                    }
                }
            }
            array_push($html, "<option $selectedtag>$item[$key]</option");
        }    
        return implode("", $html);	

    }
}

class Project extends PageObject
{
    const PAGE_NAME = "dTest";
    public static function getAll()
    {
        return PageObject::getAll(Project::PAGE_NAME);
    }
    
    public static function getById($id)
    {
        $items = PageObject::getAll(Project::PAGE_NAME);
        
        $filter = filter_by_value($items, 'id', $id);        
        
        return casttoclass('Project', $filter[0]);
    }
    
    public function getPageName() { return Project::PAGE_NAME; }
}

/*------------------------------ START GENERIC CODE -------------------------- */
/************************************************************************************ /
/* THIS WILL EVENTUALLY BE MOVED INTO SEPARATE FILES */
/**************************************************************************************/



function casttoclass($class, $object)
{
  return unserialize(preg_replace('/^O:\d+:"[^"]++"/', 'O:' . strlen($class) . ':"' . $class . '"', serialize($object)));
}
 
 
function configure()
{
    global $IP;
    global $env;
    
    option('env', ENV_DEVELOPMENT);
    $root_dir  =  $IP."\extensions\FlowProcessor\Flows\Dreamfish";
    
    $base_path = dirname(file_path($env['SERVER']['SCRIPT_NAME']));
    $base_file = basename($env['SERVER']['SCRIPT_NAME']);
    $base_uri  = file_path($base_path, (($base_file == 'index.php') ? '?' : $base_file.'?'));
    
    //TODO need to make dynamic
    //$base_uri = '/w/index.php/Special:FlowDreamfish/';
    $base_uri = '/devwiki/Special:FlowDreamfish/';
    $lim_dir   = dirname(__FILE__) . '\\lib';
    option('root_dir',           $root_dir);
    option('base_path',          $base_path);
    option('base_uri',           $base_uri); // set it manually if you use url_rewriting
    option('limonade_dir',       file_path($lim_dir));
    option('limonade_views_dir', file_path($lim_dir, 'limonade', 'views'));
    option('limonade_public_dir',file_path($lim_dir, 'limonade', 'public'));
    option('public_dir',         file_path($root_dir, 'public'));
    option('views_dir',          file_path($root_dir, 'views'));
    option('controllers_dir',    file_path($root_dir, 'controllers'));
    option('lib_dir',            file_path($root_dir, 'lib'));
    option('error_views_dir',    option('limonade_views_dir'));
}

//optional hook    
function before()
{
  
}

//must be defined
function template() { }


function filter_by_value ($array, $index, $value, $opposite = false){ 

    $newarray = array();
    if(is_array($array) && count($array)>0)  
    { 
        foreach(array_keys($array) as $key){ 
            $tmpArr = (array)$array[$key];
            $temp[$key] = $tmpArr[$index]; 
             
            if ($opposite == false) {
              if ($temp[$key] == $value){ 
                  array_push($newarray, $array[$key]);
              } 
            }
            else {
              if ($temp[$key] != $value) { 
                  array_push($newarray, $array[$key]); 
              } 
            }
        } 
      }       
    return $newarray; 
} 

class PageObject extends ArrayObject 
{
    
    public function getPageName() { return 'dData'; }
       
    public function __set($name, $val) {
        $this[$name] = $val;
    }

    public function __get($name) {
        if (!array_key_exists($name, $this))
            return "";
        return $this[$name];
    }
    
    public static function getAll($type)
    {
        $page = dfPage::get($type);
        if ($page->isSaved)
        {
            return json_decode($page->content);
        }
        else 
        {
            return array();
        }      
    }
      
    public function save()
    {
        $page = dfPage::get($this->getPageName());
        $projects = json_decode($page->content);
        
        if (empty($projects))
          $projects = array();
          
        if (empty($this['id'])) {
          $this['id'] = uniqid();
        }
        else {
          //remove the old one
          $projects = filter_by_value($projects, 'id', $this['id'], true);          
        }
      
        array_push($projects, $this);
        $page->content = json_encode($projects);
        $page->save();
    }
    
    
    public function delete()
    {
        $page = dfPage::get($this->getPageName());
        $projects = json_decode($page->content);        
        $projects = filter_by_value($projects, 'id', $this['id'], true);          
        $page->content = json_encode($projects);
        $page->save();
    }
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

if( !defined( 'MW_INSTALL_PATH' ) ) {

function output($text)
{
    global $wgOut;
    $wgOut->addHTML(html($text));
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
        /*
        $wgOut->addHTML("&<form method=\"post\">&<input type=\"text\" name=\"name\">&<input type=\"submit\" name=\"foo\">&</form>");
        #var_dump( $this->params );
        #var_dump( $wgRequest);
        var_dump($_POST);
        if ($wgRequest->wasPosted())
        $wgOut->addHTML("POSTED!!!!!!!!");
        */
        #var_dump($wgOut);
        #print "\n\n<br><br>";
        #$wgOut->addHTML('test');
        run();
    }
    
    function getDescription()
    {
        return "";
    }
}

}
else
{

  function output($text)
  {
    return render($text);
  }
  
  run();
  
  

}