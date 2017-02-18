=== dbview ===
Contributors: john ackers
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=john%2eackers%40ymail%2ecom&lc=GB&item_name=John%20Ackers&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: shortcode, table, database, query, ajax, admin, SQL, mysql
Requires at least: 3.0.1
Tested up to: 3.9.1
Stable tag: trunk

== Description ==

Presents the results a database SQL query in a table. The query can be saved as a 
named view which can then be embedded as a table in any post using the shortcode 
[dbview name=name-of-view]. Views can be created and edited in the dashboard. 

= Shortcode examples = 

Show the 'world cities' view with a page size of 50 rows.
`[dbview name='world cities' pagesize=50]`

Show the 'world cities' view initially sorted by column 'city' in ascending order.
`[dbview name='world cities' sort=city order=asc]`

Show the 'world cities' view initially sorted by column 'population' in descending order.
`[dbview name='world cities' sort=population order=desc]` 

Show the 'world cities' view without any pagination.
`[dbview name='world cities']` 

Show the 'world cities' in the US with a population of greater than 5 million. See FAQ on passing arguments.
`[dbview name='world cities in country' sort=city order=asc pagesize=10 
 arg1='United States' arg2=5000000]`
   

= End User Features =

* Column sorting on table header. Columns are enabled on each column.
* Page navigation is on the table footer (from 0.5.3).  
* Tables are loaded using AJAX.

= Management Features =

* Each view can be created/edited/deleted and tested under dbview/Settings in the dashboard.
* Easy view consists of one SQL statement plus an optional PHP snippet associated wthe each column.
* Sorting on each column can be enabled and disabled.
* Each column can be manipulated using a PHP snippet. This functionality allows the introduction of permalinks, images and other customisations. 
* Each view is stored in a single serialized object in the wp_options table.

= Limitations =

* Tables are not styled; this is left to the theme. 
* The data in the results table(s) cannot be edited.

= Security =

When the plugin is activated, administrators are given the capability to 'manage DB views'.
Any other wp user with a different role that needs to create/edit views [must be granted that capability](http://codex.wordpress.org/Roles_and_Capabilities).
Only a view that is explicitly checked as public will be visible to non administrators and the public.

== Installation ==

1. Follow the [standard installation procedure for WordPress plugins](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins).
2. Refresh (F5) any existing pages in browser so latest javascript loaded.
3. Manually decativate and reactivate if any predefined views are missing.

There are no configurable options.

Ten or more predefined views that navigate wp_posts, wp_postmeta, wp_options, 
wp_users and wp_usermeta are loaded and reloaded each time the plugin is activated. 
These views can be modified and deleted.

== Frequently Asked Questions ==

= Is it possible to pass arguments from a dbview embedded on a public facing page to an SQL statement? =

A Yes. Use arg1 and arg2 to pass the arguments. 

For example, to embed the results of this query: 

`select * from cities where country=%s and population > %d`

use:
 
`[dbview name='world cities in country' sort=city 
 order=asc pagesize=10 arg1='United States' arg2=5000000]`

= How do I load a second table from a link in the first table? =

By using a link 
`<a href="?action=dbview&verb=autoLoad&name=NameOfView">my link</a>`
An easier way is to insert a PHP snippet in a column in the first table:
`return DBView::link($value, "name-of-dbview", optional-arg1, optional-arg2);`

The loaded table replaces the existing table.

= How do I pass the ID of the current user to a query relating to that user? =

By invoking some PHP using another plugin such as 'Post Snippets' to load dbview and pass the appropriate argument. 

For example to get the current user and display a table that shows that users information.
`$u=wp_get_current_user(); 
echo do_shortcode("[dbview  name='show user' arg1=$u->ID]");` 
The corresponding SQL stored in 'show user' is:
`select ID, user_login, user_email, user_registered, user_status 
 from wp_users where ID=%d`

= How can i show images in a table column and make them clickable links? =

Assuming the image URL and the link URL are in separate columns in the table, it's necessary to concatenate the two URLs so as to not create an extra column in the view that is not wanted. 

For example this SQL concatenates two URLs:

`select id, concat('https://www.google.co.uk/images/srpr/logo11w.png',
',','http://www.google.com') as link from wp_posts limit 2`

And this PHP snippet extracts the two URLs from the single column 'link':  

`$a = explode(",",$value);
return "<a href='".$a[1]."'><img src='".$a[0]."' /></a>";`

[Original discussion](http://wordpress.org/support/topic/db-images?replies=33#post-5570117)


= Why are there no visible ascending or descending tabs on the column headers on the public facing pages? =

Include the file (or the contents of) dbview.css into your theme.


= I need to use a column from my SQL query in my PHP snippet, how can i prevent the column from appearing in the table? =

At present, there is no way to hide columns. 


= Why are changes to the 'public' setting of a view not immediately effective? =

Because the properties of each dbview are stored in the wp_options table which is cached for each session.

== Screenshots ==

1. screenshot-1.png - the admin screen showing initial 'views' which can be modified or deleted. Reactivate the plugin if these aren't visible. 
2. screenshot-2.png - the admin screen showing an arbitrary view 'signatures so far'.
3. screenshot-3.png - the admin screen showing one view containing links to other views.

== Changelog ==

= 0.5.5 =

* fixed cell function editing broken in 0.5.3
* add helper DBView::link() method to create clickable links to load tables (see FAQ)

= 0.5.4 =

* pass arguments (arg1, arg2) from a dbview on a public facing page to the query (see FAQ)

= 0.5.3 =

* incorporates slevit's column sorting enabling/disabling [see post](http://wordpress.org/support/topic/dbview-contributor-request)
* bug fix, handle empty results table  [see post](http://wordpress.org/support/topic/db-images#post-5573339)
* bug fix "..non-static method DBViewPublic::shortcode().."
* management page renamed to 'Settings' page for consistency with other WP plugins

= 0.5.2 =

* sorting by column
* orphaned PHP snippets displayed in extra columns in table

= 0.5.1 =

* bug fix, last page of results wasn't shown 

= 0.5.0 =

* table scrolling supported.

= 0.4.5 =

* list tables when using table prefix other than 'wp_'  [see post](http://wordpress.org/support/topic/plugin-dbview-another-table-doesnt-exist).

= 0.4.4 =

* remove superflous character encoding/decoding so umlauts etc handled properly [see post](http://wordpress.org/support/topic/plugin-dbview-charset-encoding-encodehtmlentities-is-broken-by-using-utf-8).

= 0.4.3 =

* even when magic quotes is off, stripslashes from textarea input (because wp always adds them).
* warn administrators when they are looking at a page with a dbview that is not public.

= 0.4.2 =

* Rows founds, rows affected shown.
* Index related warnings fixed. 

= 0.4.1 =

* Preconfigured views extended and linked together to allow wpdb tables to be navigated.
* Handle links with containing SQL query

= 0.4.0 =

* Public flag added to each view.
* 'List views' now show PHP snippets count and SQL statements containing are encoded.
* Change button legends
* Text moved into PHP class to support translation
* Bug fix, make ?page=dbview&name=myview works so allow sharing of tables
* Bug fix, correct loading.gif URL when table loading on public pages

= 0.3.1 =

* Preload 'list views' and 'show table status' as views.
* Allow unsaved queries to be executed
* Put back top line of file containing Plugin Name !!!

= 0.3.0 =

* Unserialize objects and display using print_r()
* Bug fix: Accidental double serialization of DBView objects stopped. Old objects still loadable.

= 0.2.3 =

* Correct the saved successfully message.

= 0.2.2 =

* Header cell editing improved.

= 0.2 =

* Fix bugs to correct views on public pages.

= 0.1 =

* First version.
