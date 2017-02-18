<?php

/*
 Plugin Name: dbview
 Plugin URI: http://wordpress.org/extend/plugins/dbview
 Description: Presents the results of a database query in a table. The query can be saved as a view which can then be embedded in any post using a shortcode. Views can be created and edited in the admin pages. Cell contents can be manipulated with PHP snippets.
 Author: John Ackers
 Version: 0.5.5
 Author URI: john . ackers ATT ymail . com
 */


class DBView
{
  // Note the attributes below are stored with the view in wp_options
  public $version = 1 ;
  public $name = "" ;
  public $query =  "" ;
  public $columnName = array();
  public $columnSortable = array();
  public $cellFunction = array();

  /**
   * create a permalink to another dbview
   * useful when creating PHP snippets.
   *
   * Clicking on the link, replaces the existing table with another table
   *
   * @param string $text
   * @param string $dbviewName
   */

  static function link($text, $dbviewName, $arg1=null, $arg2=null)
  {
  	$args = "" ;
  	if (!empty($arg1)) $args .= "&arg1=".$arg1 ;
  	if (!empty($arg2)) $args .= "&arg2=".$arg2 ;
  	return "<a href=\"?action=dbview&verb=autoLoad&name=$dbviewName$args\" title=\"DBView '$dbviewName'\">$text</a>";
  }

/**
 * create a permalink to another dbview
 * useful when creating PHP snippets.
 * works only in dashboard.
 *
 * @param string$text
 * @param string $sqlQuery which cannot contain "
 */
  static function linkToView($text, $dbviewName, $arg1=null, $arg2=null)
  {
    $args = "" ;
    if (!empty($arg1)) $args .= "&arg1=".$arg1 ;
    if (!empty($arg2)) $args .= "&arg2=".$arg2 ;
    return "<a href=\"?page=dbview&name=$dbviewName$args\" title=\"DBView '$dbviewName'\">$text</a>";
  }

/**
 * create a permalink to another database view with a query
 * works only in dashboard.
 *
 * @param string$text
 * @param string $sqlQuery which cannot contain "
 */
  static function linkToQuery($text, $sqlQuery)
  {
    return "<a href=\"?page=dbview&query=$sqlQuery\" title=\"$sqlQuery\">$text</a>";
  }
}

DBViewPublic::initHooks();

class DBViewPublic
{
  static protected $singleton = null ;
  const pluginName = 'dbview' ;
  const optionPrefix = 'dbview_' ;
  const requiredCapability = 'manage DB views' ;

  public $showScripts = false ;
  public $runScripts = true ;
  public $queryArgs = array();
  public $pageSize = 20 ;     // default number of rows displayed on a single page in dashboard
  public $rowsFound ;
  public $rowsAffected ;
  public $errors = array();

  function __construct()
  {
    $this->dview = new DBView;
  }

  static function initHooks() {

    self::$singleton = new DBViewPublic();

    if (is_admin())
    {
      add_action('wp_ajax_dbview',          array( self::$singleton, 'handleAjaxRequest' ) );
      add_action('wp_ajax_nopriv_dbview',   array( self::$singleton, 'handleNoprivAjaxRequest' ) );
      add_action('admin_menu',              array( __CLASS__, 'addMenuPages'));
      add_action('admin_enqueue_scripts',   array( __CLASS__, 'enqueueScripts'));
      add_filter('plugin_action_links',     array( __CLASS__, 'addManagementLink' ), 10, 2 );

      register_activation_hook(plugin_basename(__FILE__), array( __CLASS__, 'activate' ));
      register_uninstall_hook(__FILE__, array( __CLASS__, 'uninstall' ));
    }
    else
    {
      add_action('wp_enqueue_scripts',      array( __CLASS__, 'enqueueScripts'), false);
//      add_action('init', array( self::$singleton, 'init' ), 1);
      add_shortcode('dbview', array( __CLASS__, 'shortcode' ));
    }

  }

  static function activate()
  {
    foreach(self::getDefaultViews() as $dview)
    {
      $existView = get_option($option = self::optionPrefix . @$dview->name);
      if (true || empty($existView)) // override stored views
      {
        update_option($option, $dview);
      }
    }

    $role = get_role('administrator');
    $role->add_cap(self::requiredCapability);
  }


  static function uninstall()
  {
    delete_option(self::optionPrefix . 'index');
  }


  function init()
  {
  }

  static function enqueueScripts()
  {
    wp_enqueue_script( 'dbview-js', plugin_dir_url( __FILE__ ) . 'dbview.js', array('jquery'));
    global $wpdb;
    $tooltips = array(
    'input[name=listViews]'=> "List all serialised views stored in the '.$wpdb->prefix.'options table.",
    'input[name=name]'=> "Name of the view to load/save/execute. <br/>It can contain blanks.",
    'input[name=save]'=> "Save this view in the '.$wpdb->prefix.'options table. <br/>Any existing view is overwritten.<br/>Once saved, the view can be included in a page using a shortcode.",
    'input[name=executeQuery]'=> "Reveals the PHP snippets and shows query results in a table.",
    'label[for=id-public],input[name=public]'=> "Check this box if you want this view to be visible to anonymous website visitors.",
    'label[for=id-runscripts],input[name=runScripts]'=> "Disable if the table does not display after adding a PHP snippet.<br/>
     This flag is not saved with the view;  scripts run normally.",

    'thead tr.columnName th'=> 'Click to edit the column name. Note : you can also change the column name name
    using AS in the SQL query.',
    'thead tr.columnSortable th'=> 'Check/uncheck to toggle the sortable attribute.',
    'thead tr.cellFunction th'=> 'Click to create/edit a PHP snippet. These PHP variables might be useful.
    <ul><li>$value is the database field value</li>
    <li>$name is the database field name</li>
    <li>$row is an array containing $name=>$value pairs</li>
    <ul>As usual every PHP statement must be terminated with a ; and the snippet should a include a return statement.'
    ) ;

    wp_localize_script('dbview-js', self::pluginName, array
    (
      'ajaxurl'      => admin_url('admin-ajax.php'),
      'loadingImage' => admin_url('images/loading.gif'),
      'tooltips'     => is_admin() ? $tooltips : ""  // only pass tooltips to dashboard viewers
    ));
  }


  static function addManagementLink($links, $file)
  {
    if ($file == plugin_basename(__FILE__))
    {
      $link = '<a href="tools.php?page=dbview">'.__("Settings").'</a>';
      array_unshift($links, $link);
    }
    return $links;
  }


  static function addMenuPages()
  {
    add_management_page('DBView management', 'DB View', self::requiredCapability, 'dbview',  array(self::$singleton, 'managementPage'));
  }


  function managementPage()
  {
    $this->response = new DBViewResponse();
    try {
      $this->handleLink(false);
    }
    catch (Exception $e) { }

    ?>

<h2><a title='Reload the page' href="" style='text-decoration:none'>DB View</a></h2>

<div class='formwrap'>
  <input type='button' name='listViews'    value='<?php _e("List views")       ?>'></input>&nbsp;&nbsp;
  <input type='button' name='load'         value='<?php _e("Load")             ?>'></input>
  <input type='text'   name='name'         value='<?php echo $this->dview->name?>'></input>
  <input type='button' name='save'         value='<?php _e("Save")             ?>'></input>
  <input type='button' name='delete'       value='<?php _e("Delete")           ?>'></input>
  <input type='button' name='executeQuery' value='<?php _e("Execute query")    ?>' ></input>

  <input type='checkbox' name='public'   id='id-public' <?php checked(isset($this->dview->public) && $this->dview->public, 1); ?>></input><label for='id-public'>Public</label>
  <input type='checkbox' name='runScripts' id='id-runscripts' checked='checked'></input><label for='id-runscripts'>Run column scripts</label>

  <input type='hidden' name='nameHidden'   value='<?php echo $this->dview->name ?>'  ></input>
  <input type='hidden' name='_ajax_nonce'  value='<?php echo wp_create_nonce(self::pluginName)   ?>'  ></input>
  </p>
  <p>SQL Query: <br/>
  <textarea name="query" rows='4' cols='30' style='width:98%'><?php echo esc_textarea($this->dview->query)?></textarea></p>

</div>
<div class='messageHolder' style='min-height: 30px'>Javascript has not started.</div>
<div class='dbview'><?php echo implode($this->response->r['messages']);
                          foreach($this->response->r['updates'] as $update)
                            if ($update['selector'] == '.dbview')
                              echo $update['html'] ; ?></div>
    <?php
  }

  /**
   * This handler processes all ajax requests for this plugin
   */

  function handleAjaxRequest()
  {
    return $this->handleNoprivAjaxRequest(true);
  }

  function handleNoprivAjaxRequest($priv=false)
  {
    $this->response = new DBViewResponse();
    $method = null ;

    try
    {
      $method = $_REQUEST['verb'];
      if (empty($method))
        throw new Exception(__("verb not specified: $method"));

      $reflectedMethod = new ReflectionMethod(__CLASS__, $method);
      if (!$reflectedMethod->isPublic())
        throw new Exception(__("verb/method not public: $method"));

      if ($method != 'autoLoad')  // the only method available from front page
      {
        if (!check_ajax_referer(self::pluginName, false, false))
        {
          if (!isset($_REQUEST['_ajax_nonce']))
            throw new Exception(__("request rejected, missing security token (nonce value)."));
          else
            throw new Exception(__("request rejected, mismatching security token (nonce value),
            try clicking again. If that fails try refreshing page (F5) and reentering data."));
        }
        if (!current_user_can(self::requiredCapability))
          throw new Exception(__("This database view is not public."));
      }
      self::$method();
    }
    catch (Exception $e)
    {
      $this->response->addErrorMessage($e->getMessage());
    }
    $this->response->writeJSON();
    exit ;
  }

  // entry point for load button in admin/dashboard

  public function load()
  {
    $name = $_REQUEST['name'];
    $this->dview = self::load1($name);
    $this->updateAllFormFields();
  }


  static function load1($name)
  {
    if (empty($name))
    {
      throw new Exception("Name of view not specified");
    }
    $dview = get_option($option = self::optionPrefix . $name);
    if (empty($dview))
      throw new Exception("$name: view not found");

    $dview = gettype($dview) == 'object' ? $dview :  unserialize($dview); // back compat

    if (gettype($dview)  != "object")
      throw new Exception('Unable to deserialize this view');

    if (!isset($dview->query))
    {
      $dview->query = $dview->sql ; unset($dview->sql);  // back compat
    }
    return $dview ;
  }


  function save()
  {
    $nameHidden = $_REQUEST['nameHidden'];
    try {
      // load what user is currently look at
      $this->dview = self::load1($nameHidden);
    }
    catch (Exception $e)
    {
      $this->dview = new DBView();
    } ;
    // now override name and query
    $this->dview->name = $_REQUEST['name'];
    $q = $_REQUEST['query'];
    $q = stripslashes($q);
    unset($this->dview->public);
    if (isset($_REQUEST['public']))
    {
      $this->dview->public = true ;
    }
    $this->dview->query = $q ;
    $this->save1();
    $this->response->addUpdate(array("selector"=>"input[name=nameHidden]", "val"=>$this->dview->name));
    $this->response->addMessage("{$this->dview->name}: View saved!");

    $views = self::getDefaultViews();
    if (isset($views[$this->dview->name]))
      $this->response->addMessage(_("Warning: that is a preconfigured view which will be overwritten if you reactivate this plugin."));

    if (isset($this->dview->public))
      $this->response->addMessage("<span style='color:grey'>To embed in a post use shortcode: [dbview name='{$this->dview->name}']</span>");
      $this->response->addMessage("<span style='color:grey'>Options: [dbview name='{$this->dview->name}' pagesize=10 sort='some column name' order='asc or desc']</span>");
  }


  protected function save1()
  {
    if (empty($this->dview->name))
      throw new Exception(__("Name of view cannot be blank"));

    if (preg_match('/order by/i',$this->dview->query))  // order by clause will override column sorting criteria
      $this->response->addMessage(_("Warning: specifying 'ORDER BY' will override column sorting criteria. Use a MySql View or shortcode arguments instead."));

    $result = update_option(self::optionPrefix . $this->dview->name, $this->dview);
  }

  function delete()
  {
    $nameHidden = $_REQUEST['nameHidden'];
    $name = $_REQUEST['name'];
    if ($name != $nameHidden)
      throw new Exception(__("The name of the view has been modified. Please reload the query first."));

    $query = $_REQUEST['query'];
    $query = stripslashes($query);

    $this->dview = self::load1($name);
    if ($query != $this->dview->query)
      throw new Exception(__("The query has been modified. Please reload the view you want to delete."));
    $optionName = self::optionPrefix . $name;
    if (delete_option($optionName))
      $this->response->addMessage("$name: View deleted");
    else
      $this->response->addMessage("$name: View NOT deleted");
  }


  function executeQuery()
  {
    $nameHidden = $_REQUEST['nameHidden'];
    $name = $_REQUEST['name'];
    if ($name != $nameHidden)
      throw new Exception(__("The view name has been modified. Please reload the query first."));

    $this->showScripts = true ;
    $this->runScripts = isset($_REQUEST['runScripts']);

    $this->dview = empty($name) ? new DBView() : self::load1($name);

    $query = $_REQUEST['query'];
    $query = stripslashes($query);

    $this->queryArgs = array();
    $this->executeQuery1($query);
  }


  public function handleLink()
  {
    if (isset($_REQUEST['name']))
      $name = $_REQUEST['name'];

    $this->dview = empty($name) ? new DBView() : self::load1($name);

    if (isset($_REQUEST['query']))
    {
      $this->dview->query = $_REQUEST['query'];
      $this->dview->query = stripslashes($this->dview->query);
    }
    if (empty($this->dview->name) && empty($this->dview->query))
      throw new Exception(__("Link does not include 'name' or 'query' parameter."));

    $this->queryArgs = array();
    if (isset($_REQUEST['arg1'])) $this->queryArgs[] = $_REQUEST['arg1'];
    if (isset($_REQUEST['arg2'])) $this->queryArgs[] = $_REQUEST['arg2'];
    $this->updateAllFormFields();
    $this->executeQuery1($this->dview->query);
  }

  function executeQuery1($query)
  {
    if (empty($query))
      throw new Exception(__("Empty SQL query."));

    if ($query != $this->dview->query)
    {
      $this->dview->query = $query ;
      $this->response->addMessage(__("Note: this query has not been saved."));
    }
    $content = "<h2>{$this->dview->name}</h2>\r\n".$this->view();
    if ($this->rowsAffected != -1)
      $this->response->addMessage($this->rowsAffected . " " . __("rows affected."));
    if ($this->rowsFound!=-1)
      $this->response->addMessage($this->rowsFound . " " . __("rows found."));
    foreach($this->errors as $errorText)
      $this->response->addErrorMessage($errorText);
    $this->response->addUpdate(array("selector"=>".dbview", "html"=>$content));
  }

  private function updateAllFormFields()
  {
    $this->response->addUpdate(array("selector"=>"input[name=name]", "val"=>$this->dview->name)); // not required
    $this->response->addUpdate(array("selector"=>"input[name=nameHidden]", "val"=>$this->dview->name));
    $this->response->addUpdate(array("selector"=>"textarea[name=query]", "val"=>$this->dview->query));
    $this->response->addUpdate(array("selector"=>"input[name=public]", "checked"=>isset($this->dview->public)));
  }

  /**
   * replace the short code with an element that will autoload the requested view
   * @param $attributes
   * @param $body
   */

  static function shortcode($attributes, $body)
  {
    $attString = ""; foreach ($attributes as $key =>$value)
      $attString .= "data-$key='$value' ";   // the 'data-' is stripped by the js

    return "<div><!-- http://wordpress.org/extend/plugins/dbview -->\r\n"
          ."     <div class='messageHolder'></div>\r\n"
          ."     <div class='dbview autoload' $attString></div></div>\r\n";
  }

  function autoLoad()
  {
    $name = $_REQUEST['name'];
    $this->dview = self::load1($name);
    $this->pageSize = isset($_REQUEST['pageSize']) ? $_REQUEST['pageSize'] : 0 ; // no scroll by default
    $this->queryArgs = array();
    if (isset($_REQUEST['arg1'])) $this->queryArgs[] = $_REQUEST['arg1'];
    if (isset($_REQUEST['arg2'])) $this->queryArgs[] = $_REQUEST['arg2'];
    $this->response->addUpdate(array("html"=>$this->view(true)));
  }

  function listViews()
  {
    try {
      $this->dview = self::load1('List views');
    }
    catch(Exception $e)
    {
      $views = self::getDefaultViews();
      $this->dview = $views['List views'];
      $this->save1();
      $this->response->addMessage("'List views' restored. Deactivate and reactivate plugin to load other missing preconfigured views.");
    }
    $query = $this->dview->query ;
    $this->executeQuery1($query, false, true);
  }

  function getDefaultViews()
  {
    global $wpdb;
    $views = array();
    $views['List views'] = $view = new DBView();
    $view->query = 'select option_name as name, option_value as v1, option_value as v2, option_value as v3 from '
    .$wpdb->prefix.'options where option_name like "dbview%"' ;
    $view->cellFunction['name'] = 'return "<a href=\'?page=dbview&name=" .substr($value,7)."\'>" .substr($value,7). "</a>";' ;
    $view->columnName['name'] = 'Name of view';

    $view->columnName['v1'] = 'Public';
    $view->cellFunction['v1'] =
    ' $obj = unserialize($value);
      if (gettype($obj) != \'object\')
        return "";
      return isset($obj->public) ? "on" : "-"; ' ;

    $view->columnName['v2'] = 'PHP snippets';
    $view->cellFunction['v2'] =
    ' $obj = unserialize($value);
      if (gettype($obj) != \'object\')
        return "";
      return isset($obj->cellFunction) ? count($obj->cellFunction) : "-"; ' ;

    $view->columnName['v3'] = 'SQL query';
    $view->cellFunction['v3'] =
'$obj = unserialize($value);
if (gettype($obj) != \'object\')
  return("<span style=\'color:lightgrey\'>Serialized object from DBView ver < 0.3, please save first.</span>");
return esc_textarea(@$obj->query) ; ';

    $views['show table status'] = $view = new DBView();
    $view->query = 'show table status' ;  // to get a fixed column name
    $view->cellFunction['Name'] =
'$q="select * from $value ";
$qe=urlencode($q);
$href="?page=dbview&query=$qe";
return "<a href=\'".$href."\'>$value</a>" ;';

//- posts
    $views['show postmeta'] = $view = new DBView();
    $view->query = 'select * from '.$wpdb->prefix.'postmeta where post_id=%d';
    $view->cellFunction['post_id'] =
'return $value . " " . DBView::linkToView("[show post $value]","show post", $value);';
    $view->cellFunction['meta_key'] =
'return $value . " " . DBView::linkToView("[rows with same key]","show postmeta by key", $value);';

    $views['show postmeta by key'] = $view = new DBView();
    $view->query = 'select * from '.$wpdb->prefix.'postmeta where meta_key=%s';
    $view->cellFunction['post_id'] = $views['show postmeta']->cellFunction['post_id'];

    $views['show post'] = $view = new DBView();
    $view->query = 'select ID, post_author, post_date, post_title, post_status from '.$wpdb->prefix.'posts where ID=%d';
    $view->cellFunction['ID'] =
'return $value . " " . DBView::linkToView("[show postmeta for post $value]","show postmeta", $value);';

    $views['show posts by user'] = $view = new DBView();
    $view->query = 'select ID, post_author, post_date, post_title, post_status from '.$wpdb->prefix.'posts where post_author=%d';
    $view->cellFunction['ID'] = $views['show post']->cellFunction['ID'] ;

    $views['show all posts'] = $view = new DBView();
    $view->query = 'select ID, post_author, post_date, post_title, post_status from '.$wpdb->prefix.'posts ';
    $view->cellFunction['ID'] = $views['show post']->cellFunction['ID'] ;

//- users
    $views['show usermeta'] = $view = new DBView();
    $view->query = 'select * from '.$wpdb->prefix.'usermeta where user_id=%d';
    $view->cellFunction['user_id'] =
'return $value . " " . DBView::linkToView("[show user $value]","show user", $value);';
    $view->cellFunction['meta_key'] =
'return $value . " " . DBView::linkToView("[rows with same key]","show usermeta by key", $value);';

    $views['show usermeta by key'] = $view = new DBView();
    $view->query = 'select * from '.$wpdb->prefix.'usermeta where meta_key=%s';

    $views['show user'] = $view = new DBView();
    $view->query = 'select ID, user_login, user_email, user_registered, user_status from '.$wpdb->prefix.'users where ID=%d';
    $view->cellFunction['ID'] =
'return $value . " " . DBView::linkToView("[show usermeta for user $value]","show usermeta", $value). '
.               '" " . DBView::linkToView("[show posts by user $value]","show posts by user", $value);' ;

    $views['show all users'] = $view = new DBView();
    $view->query = 'select ID, ID as UID, user_login, user_email, user_registered, user_status from '.$wpdb->prefix.'users ';
    $view->cellFunction['ID'] = $views['show user']->cellFunction['ID'] ;

    $views['show all options'] = $view = new DBView();
    $view->query = 'select option_name, option_value from wp_options' ;

    foreach ($views as $name => &$v)
    {
      $v->name = $name;
    }

    $views['first ten posts']->cellFunction['post_author'] =
      $views['show posts by user']->cellFunction['post_author'] =
        $views['show usermeta by key']->cellFunction['user_id'] =
         $views['show post']->cellFunction['post_author'] = $views['show usermeta']->cellFunction['user_id'] ;

    return $views;
  }


  function view($giveNotPublicWarning = false)
  {
    include_once dirname(__FILE__) . '/DBViewTable.class.php';

    if (!isset($this->dview->public) || !$this->dview->public)
    {
      if (!current_user_can(self::requiredCapability))
        throw new Exception($this->dview->name. ": ".__("this database view is not public."));

      if ($giveNotPublicWarning)
        $this->response->addMessage($this->dview->name. __(": Warning: ").__("this database view is not public."));
    }
    $tableView = new DBViewTable($this->dview, $this);
    $tableView->addNavigation();
    return $tableView->view();
  }


  function updateColumnName()
  {
    $this->updateCell('columnName', 'column name');
  }

  function updateColumnSortable()
  {
    $this->updateCell('columnSortable', 'column sortable');
  }

  function updateCellFunction()
  {
    $this->updateCell('cellFunction', 'cell function');
  }

  private function updateCell($cellIdent, $cellLabel)
  {
    $nameHidden = ($_REQUEST['nameHidden']);
    $this->dview = self::load1($nameHidden) ;

    $id = $_REQUEST['id'];
    $updatedValue = $_REQUEST['text'];
    $updatedValue = stripslashes($updatedValue);
    if (!isset($this->dview->{$cellIdent}[$id]))
      $this->dview->{$cellIdent}[$id] = '';

    if (in_array($cellIdent,array('columnSortable')))
    	$updatedValue = filter_var($updatedValue, FILTER_VALIDATE_BOOLEAN);

    if ($this->dview->{$cellIdent}[$id] == $updatedValue)
    {
    }
    else
    {
      if (empty($updatedValue))
      {
        unset($this->dview->{$cellIdent}[$id]) ;
        $this->response->addMessage("$nameHidden : $id : $cellLabel cleared.");
      }
      else
      {
        $this->dview->{$cellIdent}[$id] = $updatedValue ;
        $this->response->addMessage("$nameHidden : $id : $cellLabel set.");
      }
      $this->save1();
      if (in_array($cellIdent,array('columnName', 'cellFunction')))
				$this->response->addUpdate(array("text"=>$updatedValue));

      if (in_array($cellIdent,array('columnSortable')))
      	$this->response->addUpdate(array("checked"=>$updatedValue));
    }
  }
}


/**
 * An ajax response structure that can carry
 * multiple page updates and multiple messages
 *
 * response handled by dbview.js
 *
 * @author johna
 *
 */

class DBViewResponse
{
  public $r ;
  function __construct()
  {
    $this->r = array("success" => true, "messages" => array(), "updates" =>array() );
    $this->r["_ajax_nonce"] = wp_create_nonce(DBViewPublic::pluginName);
  }

  function addUpdate($update)  // $update expected to be an array
  {
    $this->r["updates"][] = $update ;
  }

  function addMessage($text)
  {
    $this->r["messages"][] = "<span style='color:blue'>".$text."</span>" ;
  }

  function addErrorMessage($text)
  {
    $this->r["success"] = false ;
    $this->r["messages"][] = "<span style='color:red'>".$text."</span>" ;
  }

  function writeJSON()
  {
    header('Content-Type: application/json; charset="'. get_option('blog_charset').'"');
    echo json_encode($this->r);
  }
}

