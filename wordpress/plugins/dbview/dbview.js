/*
 * <!-- http://wordpress.org/extend/plugins/dbview -->
 */


jQuery(document).ready(function() {
  
  var messageHolder = jQuery('.messageHolder');  messageHolder.empty(); 

  var form = jQuery(".formwrap"); 
  for (var selector in dbview.tooltips) // hang tooltips off buttons
  { 
    dbview.addTooltip(form.find(selector), dbview.tooltips[selector]); 
  }
  
  jQuery('.autoload').each(function()  {
    var target = jQuery(this); 
    var query = { action: 'dbview', verb: 'autoLoad'} ;
    var attrNames = ['name','pageSize', 'sort', 'order', 'arg1', 'arg2'];
    for (var i = 0 ; i < attrNames.length; i++)
    {
      var attr = target.attr('data-' + attrNames[i].toLowerCase());
      if (attr) 
        query[attrNames[i]] = attr ; 
    }    
    dbview.busy(target);
    jQuery.ajax({
      url:   dbview.ajaxurl, 
      data:  query,
      success: function(response, textStatus, jqXHR, dataView) { dbview.handleDOMUpdates(response, textStatus, jqXHR, target );  },
      error: function(jqXHR, textStatus, errorThrown) { dbview.handleAjaxError(jqXHR, textStatus, errorThrown, target); } ,
      dataType: 'json'
    });
  });
  
  form.find('input[type=button]').click(function() {
    if (dbview.lockoutButtons != undefined)
      if (dbview.lockoutButtons)
        return ;
    var button = jQuery(this); 
    var fields = button.parents('.formwrap').find("input, textarea");
    var ar = fields.serializeArray();
    var verb = button.attr('name')
    if (verb == 'delete' && !confirm("Do you want to delete this view?"))
      return ; 
//    button.attr('disabled','disabled') ;

    dbview.busy(null);
    jQuery.ajax({
      url:   dbview.ajaxurl, 
      data : 'action=dbview&verb=' + verb + '&' + fields.serialize(),
      success: function(response, textStatus, jqXHR)  { dbview.handleDOMUpdates(response, textStatus, jqXHR, null ); },
      error: function(jqXHR, textStatus, errorThrown) { dbview.handleAjaxError(jqXHR, textStatus, errorThrown, null); } ,      
      dataType: 'json'
    });
  });
});



dbview.addTooltip = function(elements, text)
{
  elements.mouseover(function(event) {
    dbview.tooltip1(event, text, true);               
  }).mouseout(function(event){   
    dbview.tooltip1(event, text, false);    
  });
}


dbview.tooltip1 = function(event, text, display)
{ 
  var tooltip = jQuery("body .tooltip7");
  
  if (display == false)
  {
    if (text ==  tooltip.html());
      tooltip.remove();
    return ;
  }
  if (tooltip.length == 0) 
    tooltip = jQuery('<div class="tooltip7"></div>').appendTo('body');

  tooltip.html(text).css(
      { 'background' : 'lightyellow', 
        'border-style' : 'solid',
        'border-color' : 'yellow',
        'border-width' : 3,
        'padding' : 10,
        'position': 'absolute', 
        'top': event.pageY + 20, 'left': event.pageX}
      );
};  


dbview.enableColumnHeaderEditing = function (target)
{
  for (var selector in dbview.tooltips) // hang tooltips off buttons
  { 
    dbview.addTooltip(target.find(selector), dbview.tooltips[selector]); 
  }    
  
  var td = target.find('thead tr.columnName th, thead tr.cellFunction th');
  td.click(function() {
    var cell = jQuery(this) ;
    var input = cell.find('textarea');
    if (input.length == 0)
    {
      var cellText = cell.text(); 
      cell.html("<textarea rows='3' style='width:100%; height:100%;'></textarea>");
      input = cell.children();
      input.val(cellText).focus();
      
      input.focusout(function() {
        cell.empty().text(cellText);
      });
      
      input.change(function () {
        var td = input.parent(); // need to use parents() here
        var tr = td.parent();
        if (tr.hasClass('columnName'))
        {
          dbview.requestCellUpdate(input, td, 'updateColumnName', input.parent().attr('id'), input.val()); return ;
        }
        if (tr.hasClass('cellFunction'))
        {
          dbview.requestCellUpdate(input, td, 'updateCellFunction', input.parent().attr('id'), input.val()); return ;
        }  
        alert("No handler for cell of class : " +  tr.attr('class'));
        return true;
      });
    }
  });
  // handle sortable checkbox
  var td = target.find('thead tr.columnSortable th');
  td.click(function() {
  	event.stopPropagation(); // tryingto only have remote updates to chechbox
    var input = jQuery(this).find('input');
    var isChecked = input.is(":checked") ? true : false ;
    dbview.requestCellUpdate(input, input, 'updateColumnSortable', input.attr('id'), isChecked);
    return true;
  });
  
};


dbview.requestCellUpdate = function (input, target, verb, Id, Text)
{  
  var requestArgs = {
      action : 'dbview',
      nameHidden :  jQuery('.formwrap').find('[name=nameHidden]').val(),
      verb : verb,
      id : Id,
      text : Text,
      _ajax_nonce : jQuery('input[name=_ajax_nonce]').val()
  };  
  dbview.busy();
  dbview.lockoutButtons = true ;
  jQuery.ajax({
  //  type:     "POST",
    url:   dbview.ajaxurl, 
    data : requestArgs,
    success: function(response, textStatus, jqXHR) { dbview.handleDOMUpdates(response, textStatus, jqXHR, target ); },
    error: function(jqXHR, textStatus, errorThrown) { dbview.handleAjaxError(jqXHR, textStatus, errorThrown, target); } ,  
    dataType: 'json'
  });   
};


dbview.busy = function(target)
{
  var messageHolder = dbview.findClosestRelative(target,'.messageHolder');
  messageHolder.html("<img src='"+dbview.loadingImage+"' />");     
};


dbview.handleAjaxError = function(jqXHR, textStatus, thrownError, target) 
{
  var errorText ;
  if (jqXHR.status != undefined)  
    if (jqXHR.status != 200)   
  {
    errorText = jqXHR.status + " " + jqXHR.statusText;
  }
  // Server may be returning xml/html but not in JSON, e.g. stack dump
  // ignore JSON parse errors expected in textStatus and thrownError 
  else 
    errorText = jqXHR.responseText;
  var response = {};
  response.messages = new Array(errorText);
  dbview.handleDOMUpdates(response, "", jqXHR);
};  
  


dbview.handleDOMUpdates = function (response, textStatus, jqXHR, target)
{
  dbview.lockoutButtons = false ;
  var messageHolder = dbview.findClosestRelative(target,'.messageHolder');
  messageHolder.empty();
//  if (target != null) 
  //  target.empty();  
  
  if (response.messages != undefined 
      && response.messages instanceof Object
      && response.messages.length > 0)
  {
    var text = ""
    for (x in response.messages)
    {
      var text = text + response.messages[x] + "<br>";
    }
    if (messageHolder.length > 0) 
      messageHolder.html(text);
    else 
      alert(text);
  }

  if (response.updates != undefined && response.updates instanceof Object)
  {
    for (x in response.updates)
    {
      var update = response.updates[x];
      if (update.selector != undefined)  // optional selector
      {
        target = jQuery(update.selector);
        if (target.length == 0)
        {
          alert("Cannot find element '" + update.selector + "' anywhere");
          return ; 
        }
      }
      else 
      {
        if (!(target instanceof Object))
        {
          alert("Target not an JQuery array"); return ;
        }
        if (target.length == 0)
        {
          alert("Target not specified"); return ; 
        }
      }
      if (update.val != undefined) 
        target.val(update.val);    // input fields
      if (update.text != undefined) 
        target.text(update.text);  // (encoded) div and textarea 
      if (update.html != undefined) 
        target.html(update.html);
      if (update.checked != undefined) 
      {
        if (target.attr('type') != 'checkbox')
          alert('Cannot (un)set checkbox');
         target.prop('checked', update.checked);
      }
      dbview.enableColumnHeaderEditing(target); 
      dbview.enableLocalAjaxLinks(target);
    }
    return ;
  }
};


dbview.enableLocalAjaxLinks = function (target)
{
  var alink = target.find('a');
  alink.click(function ()
  {
    var query = null ;
    var href = jQuery(this).attr("href");   // href only expected to contain query
    if (href.match('page=dbview') != null)  // hijack links to tools?dbview page and convert them to ajax calls
    {
      query = href.replace('page=dbview', 'action=dbview&verb=handleLink&_ajax_nonce=' +jQuery('input[name=_ajax_nonce]').val());
    }
    if (href.match('action=dbview') != null) // e.g. table navigation request
    {
      query = href ;
    }      
    if (query == null) 
      return true ;   // browser processes link as normal
  
    dbview.busy(target);
    jQuery.ajax({
      url:   dbview.ajaxurl + query,
      success: function(response, textStatus, jqXHR) 
      { 
        dbview.handleDOMUpdates(response, textStatus, jqXHR, target);        
      },
      dataType: 'json'
    });
    return false ;    // browser ignores this link
  });
};



dbview.findClosestRelative = function (elements, select)
{  
  var parents = (elements == null) ?  jQuery(document) : elements.parents();
  for (var i=0 ; i < parents.length ; i++)
  {
    var cousins = jQuery(parents[i]).find(select);
    if (cousins.length > 0) 
      return cousins.first();
  }
  return jQuery();  // returns an empty set
};

