<?php
/**
 * Display database query resiults in a table
 *
 * @author johna
 *
 */

class DBViewTable
{
  private $dview, $dbview  ;
  public $nl2br = true ;  public $encodeHtmlEntities = true ;
  private $offset = 0 , $limit = 0, $drows, $controls ;

  function __construct($dview, $dbview)
  {
    $this->dview = $dview ;     // primary params stored in view
    $this->dbview = $dbview ;   // return runtime params
    $this->controls = new DBViewString();
    $this->sort = array();
    global $wpdb ;
    $drows = $wpdb->get_var("SET @rownum = 0; ");
    $wpdb->show_errors(false);
    $query = $this->dview->query ;

    // implement column sorting by adding SORT ORDER commands to SQL but only if those
    // keywords not already present in the user's query
    if (0 == preg_match("!\b(sort|order)\b!i", $query))
    {
      if (isset($_GET['sort']) && isset($_GET['order']))
      {
        $query .= " order by " . $_GET['sort'] . " " . $_GET['order'];
        $this->sort[$_GET['sort']] = $_GET['order'] ;
      }
    }

    // implement page scrolling by adding LIMIT and/or OFFSET commands to SQL but only if those keywords not already in use
    if (0 == preg_match("!\b(offset|limit)\b!i", $query))
    {
      $qmod = preg_replace("!^select\b!i", "select SQL_CALC_FOUND_ROWS ", $query, 1, $count);
      if ($count == 1)
      {
        $this->offset = !empty($_GET['offset']) ? $_GET['offset'] : '0' ;
        $this->limit = !empty($_GET['pageSize']) ? $_GET['pageSize'] : $this->dbview->pageSize ;

        if ($this->limit > 0)
          $query = $qmod . " LIMIT $this->limit OFFSET $this->offset " ;
      }
    }
    // $this->dbview->errors[] = $query ;  // for debug

    if (count($this->dbview->queryArgs) > 0)
      $query = $wpdb->prepare($query, $this->dbview->queryArgs);

    $this->drows = $wpdb->get_results($query, ARRAY_A);

    $error = $wpdb->last_error; // not formally documented
    if (!empty($error))
      throw new Exception($error);

    $this->dbview->rowsFound = $wpdb->get_var("select found_rows() as rowsFound, row_count() as rowsAffected");
    $this->dbview->rowsAffected = $wpdb->get_var(null, 1);
  }


  function addNavigation()
  {
    if ($this->limit == 0) return ;     // no scrolling wanted
    $totalRows = $this->dbview->rowsFound ;

    $lastPage = intval(($totalRows-1)/$this->limit) + 1 ; // numbering from 1
    if ($this->offset > $totalRows)              // happens after filtering
      $this->offset = max(0, $totalRows-$this->limit); // show last page
    $currentPage = intval($this->offset/$this->limit) + 1 ;

    $pageNumbers = new DBViewString();

    $first = $onlyOnePage = true ;
    for ($p = 1 ; $p <= $lastPage ; $first=false)
    {
      if ($first)
        $pageNumbers->add("&nbsp;&nbsp;" . __('Page:'));

      $o = ($p-1) * $this->limit ;

      if ($p > 1+1 && $p < $currentPage-3)  // skip link for early pages
      {
        $pageNumbers->add("<span>...</span>");
        $p = $currentPage-3 ;
        continue ;
      }
      if ($p > $currentPage+3 && $p < $lastPage-1)
      {
        $pageNumbers->add("<span>...</span>");  // skip link for later pages
        $p = $lastPage-1 ;
        continue ;
      }

      if ($p != $currentPage)
      {
        $q = $this->createQuery(array("offset" => $o));
        $pageNumbers->add("<a href='?$q'>$p</a>&nbsp;");
        $onlyOnePage = false ;
      }
      else
        $pageNumbers->add("<span>&nbsp;<b>$p</b>&nbsp;</span>");

      $p++;
    }

    $itemCount = new DBViewString();
    $itemCount->add($totalRows)->add(" "._n("item","items", $totalRows));

    $pageInput = new DBViewString();
    $pageInput->add("&nbsp;<label for='offset'>Offset</label>")
              ->add("<input id='offset' type='text' name='offset' size=4' value='$this->offset' />")
              ->add("&nbsp;<label for='pageSize'>Page Size</label>")
              ->add("<input id='pageSize' type='text' name='pageSize' size='2'  value='$this->limit' />");

    $this->controls->add($itemCount)->wrap("span","class='item-count'");
    if (!$onlyOnePage)
      $this->controls->add($pageNumbers->wrap("span","class='page-list'"));
  }



  function view()
  {
    return $this->addTableContent($this->drows)->toString();
  }


  function addTableContent($drows)
  {
    $tbody = new DBViewString();
    $dcolsExtended = array(); // cell names (and 1st row data)
    $firstRow = true ;

    if (is_array($drows))
    {
      $tcols = new DBViewString();
      $dcolsAltered = array();  // cell data modified by PHP snippets
      $fn = array();            // PNP snippets
      foreach ($drows as $dcols)
      {
        foreach($dcols as $dkey=>$dval)
        {
          // 1st pass of first row
          if ($firstRow)
          {
            if ($this->dbview->runScripts && isset($this->dview->cellFunction[$dkey]))
            {
              $fn[$dkey] = create_function('$value, $name, $row', $this->dview->cellFunction[$dkey]);
            }
            $dcolsExtended = $dcols ; // save the column names for later
          }
          // forget the header and build the body
          if (isset($fn[$dkey]))
          {
            $dcolsAltered[$dkey] = $fn[$dkey]($dval, $dkey, $dcols) ;
          }
          else if (self::isSerializedObject($dval))
          {
  //          $dcolsAltered[$dkey] = str_replace(" ", "&nbsp;", nl2br(print_r(unserialize($dval), true)));
            $dcolsAltered[$dkey] =  "<pre>".print_r(unserialize($dval), true)."</pre>";
          }
          else
          {
            if ($this->encodeHtmlEntities)
            {
              $dval = esc_textarea($dval);
            }
            if ($this->nl2br)
            {
              $dval = nl2br($dval);
            }
            $dcolsAltered[$dkey] = $dval;
          }
        }
        $tcols->set($dcolsAltered)->wrapEach("td")->wrap("tr")->moveTo($tbody);
      }
    }

    // add extra columns for orphan functions (PHP snippets) to the table

    if (isset($dcols))  // query may return empty result
      foreach($this->dview->cellFunction as $name=>$unused) // fyi - ignore orphaned cell names
    {
      if (!array_key_exists($name, $dcols))
      {
        $this->dbview->errors[] = "'$name'" . __(' is an orphaned PHP snippet. There is no matching column name.');
        $dcolsExtended[$name] = "" ;
      }
    }
    $thead = new DBViewString();
    $tfoot = new DBViewString();
    $tnames = new DBViewString();
    $tfunc = new DBViewString();
    $tsortable = new DBViewString();

    $colNames = array();
    // now build the multi row header (paging, names, functions);
    $reverseOrder = array('asc'=>'desc', 'desc'=>'asc');
    foreach($dcolsExtended as $dkey=>$dval)
    {
      $cell = new DBViewString();
      $name = isset($this->dview->columnName[$dkey]) ? $this->dview->columnName[$dkey] : $dkey;
      $colSortable = isset($this->dview->columnSortable[$dkey]) ? 'sortable' : '';

      $s = new DBViewString($name);
      if ($this->dbview->showScripts) //column names are sortable or editable but not both
      {
        $s->moveTo($cell);
        $cell->wrap('th', "class='editable' id='$dkey'");
      }
      else
      {
        // use the css in wp-admin/load-styles.php
        $s->wrap('span')->moveTo($cell);
        $sortable = 0 < preg_match("!^[\d\w]*$!", $dkey); // column name must be single word
        if ($sortable)
        {
          $order  = $reverseOrder[isset($this->sort[$dkey]) ? $this->sort[$dkey] : 'desc'];
          $sorted = isset($this->sort[$dkey]) ? 'sorted' : '' ;
          if ($colSortable == 'sortable')
          {
            $s->wrap('span', "class='sorting-indicator'")->moveTo($cell); // no content!
            $sortQuery = $this->createQuery(array("sort" => $dkey, "order"=>$order, "offset"=>"0"));
            $cell->wrap("a", "href='?$sortQuery'")->wrap('th', "class='$colSortable $sorted $order' id='$dkey'");
          }
          else
          {
            $cell->wrap('th', "id='$dkey'");
          }
        }
        else
          $cell->wrap('th', "id='$dkey'");
      }
      $cell->moveTo($tnames);

      if ($this->dbview->showScripts)
      {
        $s = new DBViewString('sortable');
        $celltemp = new DBViewString();
        $s->moveTo($celltemp);
        if ($colSortable == 'sortable')
        {
          $celltemp->wrap('input', "type='checkbox' id='$dkey' checked='checked'")->wrap('label');
        }
        else
        {
          $celltemp->wrap('input', "type='checkbox' id='$dkey'")->wrap('label');
        }
        $celltemp->moveto($cell);
        $cell->wrap('th', "id='$dkey'");
        $cell->moveTo($tsortable);
      }
      if (isset($this->dview->cellFunction[$dkey]))
         $cell->add(nl2br(esc_textarea($this->dview->cellFunction[$dkey])));
      $cell->wrap('th', "id='$dkey'")->moveTo($tfunc);
    }

    // now construct the table
    // add paging
    $numCols = count($dcolsExtended);
    if (count($this->controls->sb) > 0)
      $this->controls->wrap('th', "class='table-navigation' colspan=$numCols")->wrap('tr')->moveTo($tfoot);

    // add names (and function strings)
    if (!$this->dbview->showScripts)
    {
      $tnames->wrap('tr')->moveTo($thead);  // public view
    }
    else
    {
      $tnames->wrap('tr', "class='columnName'")->moveTo($thead);
      $tsortable->wrap('tr',"class='columnSortable'")->moveTo($thead);
      $tfunc->wrap('tr',"class='cellFunction'")->moveTo($thead);
    }

    $ttable = new DBViewString();
    $thead->wrap('thead')->moveTo($ttable);
    $tbody->wrap('tbody')->moveTo($ttable);
    $tfoot->wrap('tfoot')->moveTo($ttable);
    return $ttable->wrap("table style='width:98%'", " class='wp-list-table widefat' ");
  }

  /**
   * write raw downloadable formats CSV and tab separate
   *
   * @param $drow results from db
   */

  function writeFile($drows)
  {
    $thead = new DBViewString(array_values($this->dview->columnName));
    $trows = new DBViewString();
    $tcols = new DBViewString();

    // handle raw downloadable formats CSV and tab separate
    switch (strtolower($_REQUEST['format']))
    {
      case 'tab' : $glue = "\t" ; break ;
      case 'csv' : $glue = "," ; break ;
    }
    $filename = 'ecampaign-' . date('ymd') . '.txt' ;
    if (isset($_REQUEST['noheader']))
    {
      header( "Content-Type: text/plain" );
      header( "Content-Disposition: attachment; filename=$filename" );
    }

    $thead->implode($glue)->moveTo($trows);

    foreach ($drows as $dcols)
    {
      foreach($dcols as $dkey=>$dval)
      {
        switch($dkey)
        {
          case 'address':
          case 'info':
            $dcols[$dkey] = '"' . $dval . '"';
          break;
        }
      }
      $tcols->set($dcols)->implode($glue)->moveTo($trows);
    }
    echo $trows->toString();
    exit(0);
  }

  /**
   * update the query string with the new key pairs supplied in $params
   * (there must be a better way of doing this)
   * key pairs will be removed from existing string if params value is set to null.
   */
  static function createQuery($params)
  {
    $q = $_SERVER['QUERY_STRING'];    // does this work on all servers ?
    // if the 'filter' appears in GET string, we have to count number of
    // records which is only necesseary on the first filter. Subsequent
    // the recordcount is kept carried forward

    foreach($params as $key => $value)
    {
      $count = 0 ;
      $keyPair = empty($value) ? "" : "&$key=". urlencode($value) ;
      $q = preg_replace("/&$key=[^&]*/", $keyPair, $q, -1, $count);
      if ($count == 0 && !empty($keyPair))
       $q .= "$keyPair" ;
    }
    return self::trimQuery($q);
  }

  /**
   * remove all the arguments that empty values (to reduce url lengths)
   * @param unknown_type $q
   */
  static function trimQuery($q)
  {
    $q1 = preg_replace("/[^&]+=(?:&|$)/", "", $q);
    return $q1 ;
  }

  static function isSerializedObject($obj)
  {
    $count = preg_match_all('$[asi]:[0-9]+[:;{]$', $obj, $matches);
    return $count >= 4 ;
  }
}



/**
 * String utilities
 * Intended to make the code slightly more readable.
 * @author johna
 *
 */

class DBViewString
{
  public $sb ;
  function __construct($s = null)
  {
    if (isset($s))
      $this->set($s);
    else
      $this->sb = array();
  }

  function set($item)
  {
    if (is_string($item))
      $this->sb = array($item) ;
    else
      if (is_array($item))
        $this->sb = $item;
      else throw new Exception("Unable to set item");
    return $this ;
  }

  function add($item)
  {
    if (is_string($item))
      $this->sb[] = $item ;
    else
      if (is_array($item))
        $this->sb = array_merge($this->sb, $item);
      else
        if (is_a($item, get_class()))
          $this->sb = array_merge($this->sb, $item->sb);
        else
        {
          throw new Exception("Unable to add item to " . $this->toString());
        }
    return $this ;
  }

  function moveTo($another)
  {
    $another->sb[] = implode($this->sb);
    $this->sb = array();
    return $this ;
  }

  /**
   * WRAP the whole string buffer
   * @param unknown_type $tag
   * @param unknown_type $attributes
   */

  function wrap($tag, $attributes = null)
  {
    array_unshift($this->sb, isset($attributes) ? "<$tag $attributes>" : "<$tag>" );
    array_push($this->sb, "</$tag>");
    return $this ;
  }

  function wrapEach($tag, $attributes = null)
  {
    $another = array();
    foreach($this->sb as $s)
    {
      array_push($another, isset($attributes) ? "<$tag $attributes>$s</$tag>" :  "<$tag>$s</$tag>" );
    }
    $this->sb = $another ;
    return $this ;
  }

  function implode($glue)
  {
    $this->sb = array(implode($glue, $this->sb));
    return $this ;
  }

  function removeEmptyFields()
  {
    $another = array();
    foreach($this->sb as $s)
    {
      if (!empty($s))
        $another[] = $s ;
    }
    $this->sb = $another;
    return $this ;
  }

  function toString()
  {
    return implode("\r\n", $this->sb);
  }

  function asBlock()
  {
    return implode("\r\n", $this->sb);
  }

  function toCSV()
  {
    return implode(", ", $this->sb);
  }
}

