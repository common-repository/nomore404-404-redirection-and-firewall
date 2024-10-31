<?php
/* this file is looking after all activation/deactivation/uninstall
 */
// If this file is called directly, stop execution.
defined('ABSPATH') or exit;

// register scheduled event to run exports
// create a scheduled event (if it does not exist already)
function nomore404_cronstarter_activation() {
    // run periodic tasks here	
    $settings_obj = new NoMore404_Settings();
    $opt = $settings_obj->GetOptions();
    
    if($opt['share_baddies']){    // only schedule if user chose to
	if( !wp_next_scheduled( 'nomore404cronjob' ) ) {  
	   //wp_schedule_event( time(), 'everyminute', 'nomore404cronjob' );  // this must be twice a day in production, no more than that
           wp_schedule_event( time(), 'twicedaily', 'nomore404cronjob' ); // prod schedule for twice a day
	}
    }else{ //Removing cron
        nomore404_cronstarter_deactivate();
    }
}

// and make sure it's called whenever WordPress loads
add_action('wp', 'nomore404_cronstarter_activation');

// unschedule event upon plugin deactivation
function nomore404_cronstarter_deactivate() {	
	// find out when the last event was scheduled
	$timestamp = wp_next_scheduled ('nomore404cronjob');
	// unschedule previous event if any
	wp_unschedule_event ($timestamp, 'nomore404cronjob');
} 

// here's the function we'd like to call with our cron job
function nomore404_cronjob_function() {
        $uploaded = NoMore404_UD_Static_Class::UploadAllNewData();
}

// hook that function onto our scheduled event:
add_action ('nomore404cronjob', 'nomore404_cronjob_function'); 


/*
// add custom interval of once per minute for debug purposes
function nomore404_cron_add_minute( $schedules ) {
	// Adds once every minute to the existing schedules.
    $schedules['everyminute'] = array(
	    'interval' => 60,
	    'display' => __( 'Once Every Minute' )
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'nomore404_cron_add_minute' );
*/

// run on plugin activation
function nomore404_activation() {
// Get access to global database access class
    global $wpdb;
    
    // Check to see if WordPress installation is a network
    if (is_multisite()) {
// If it is, cycle through all blogs, switch to them
// and call function to create plugin table
        if (!empty($_GET['networkwide'])) {
            $start_blog = $wpdb->blogid;
            $blog_list = $wpdb->get_col('SELECT blog_id FROM ' . $wpdb->blogs);
            foreach ($blog_list as $blog) {
                switch_to_blog($blog);
// Send blog table prefix to creation function
                nomore404_create_tables($wpdb->get_blog_prefix());
            }
            switch_to_blog($start_blog);
            return;
        }
    }
    // Create table on main blog in network mode or single blog
    nomore404_create_tables($wpdb->get_blog_prefix());
    $obj_settings = new NoMore404_Settings(); // this creates default options via constructor or creates missing options
}

function nomore404_update_db_check() {
    $obj_settings = new NoMore404_Settings();
    if(NOMORE404DBVERSION != $obj_settings->options['dbversion']) nomore404_activation();
}

add_action( 'plugins_loaded', 'nomore404_update_db_check' );

function nomore404_create_tables($prefix) {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    
    $obj_settings = new NoMore404_Settings();
    
    $table_name1 = $prefix . "nomore404_uri";
    $table1updated = False;
    if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name1'") != $table_name1 ) { // Table does not exist
        $creation_query1 = "CREATE TABLE $table_name1 (
                            uri_id smallint(6) NOT NULL AUTO_INCREMENT,
                            uri_text varchar(750) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                            comment VARCHAR(50) NULL,
                            uri_redirect_to varchar(750) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
                            use_redirection tinyint(1) DEFAULT '1',
                            counter int(10) UNSIGNED NOT NULL DEFAULT '1',
                            date_created datetime DEFAULT CURRENT_TIMESTAMP,
                            date_last_used datetime DEFAULT CURRENT_TIMESTAMP,
                            suspicious tinyint(1) DEFAULT '0',
                            malicious tinyint(1) DEFAULT '0',
                            date_uploaded datetime,
                            date_downloaded datetime,
                            whitelist tinyint(1) DEFAULT '0',
                            uploaded TINYINT(1) NOT NULL DEFAULT '0',
                            PRIMARY KEY  (uri_id),
                            UNIQUE KEY uri_text_unique (uri_text)
                            ) $charset_collate;";
        $ttt = dbDelta($creation_query1);
        $table1updated = True;
    }else{ // table exists, upgrade the table if needed
        if($obj_settings->options['dbversion'] < '1.06'){ // alter table here if before v 1.06
            $update_query1 = "ALTER TABLE $table_name1 
                                ADD date_uploaded DATETIME NULL, 
                                ADD date_downloaded DATETIME NULL, 
                                ADD whitelist TINYINT(1) NOT NULL DEFAULT '0'";
            $result = $wpdb->query($update_query1); 
        }
        if($obj_settings->options['dbversion'] < '1.08'){ // alter table here if before v 1.08
            $update_query1 = "ALTER TABLE $table_name1 
                                ADD comment VARCHAR(50) NULL";
            $result = $wpdb->query($update_query1); 
        }
        if(False == NoMore404_Model_Static_Class::check_table_column_exists($table_name1, 'uploaded')){
            $update_query1 = "ALTER TABLE $table_name1 
                                ADD uploaded TINYINT(1) NOT NULL DEFAULT '0'";
            $result = $wpdb->query($update_query1); 
        }
        
        $table1updated = True; 
    }

    $table_name2 = $prefix . "nomore404_caller";
    $table2updated = False;
    if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name2'") != $table_name2 ) { // Table does not exist
        $creation_query2 = "CREATE TABLE $table_name2 (
                            caller_id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                            caller_ip varbinary(16) NOT NULL,
                            comment varchar(50) DEFAULT NULL,
                            hostname VARCHAR(50) NULL,
                            referrer varchar(512) DEFAULT NULL,
                            date_created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            date_last_used datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            counter int(10) UNSIGNED NOT NULL DEFAULT '1',
                            suspicious tinyint(1) DEFAULT '0',
                            malicious tinyint(1) DEFAULT '0',
                            date_uploaded datetime,
                            date_downloaded datetime,
                            whitelist tinyint(1) DEFAULT '0',
                            caller_ip_str varchar(40) NOT NULL,
                            uploaded TINYINT(1) NOT NULL DEFAULT '0',
                            PRIMARY KEY  (caller_id),
                            UNIQUE KEY cip (caller_ip)
                            ) $charset_collate;";
        $ttt = dbDelta($creation_query2);
        $table2updated = True;
    }else{ // table exists, upgrade the table if needed
         if($obj_settings->options['dbversion'] < '1.06'){ // alter table here if before v 1.06

            $update_query2 = "ALTER TABLE $table_name2 
                                ADD date_uploaded DATETIME NULL, 
                                ADD date_downloaded DATETIME NULL, 
                                ADD whitelist TINYINT(1) NOT NULL DEFAULT '0'";
            $result = $wpdb->query($update_query2); 
        }
        if($obj_settings->options['dbversion'] < '1.08'){ // alter table here if before v 1.08
            $update_query2 = "ALTER TABLE $table_name2 
                                ADD hostname VARCHAR(50) NULL";
            $result = $wpdb->query($update_query2); 
        }
        if(False == NoMore404_Model_Static_Class::check_table_column_exists($table_name2, 'uploaded')){
            $update_query2 = "ALTER TABLE $table_name2 
                                ADD uploaded TINYINT(1) NOT NULL DEFAULT '0'";
            $result = $wpdb->query($update_query2); 
        }
        $table2updated = True;
    }
    
    $table_name3 = $prefix . "nomore404_uri_caller";
    $table3updated = False;
    if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name3'") != $table_name3 ) { // Table does not exist   
        $creation_query3 = "CREATE TABLE $table_name3 (
                            uri_id smallint(6) NOT NULL,
                            caller_id int(10) UNSIGNED NOT NULL,
                            counter int(10) UNSIGNED NOT NULL DEFAULT '1',
                            date_created datetime DEFAULT CURRENT_TIMESTAMP,
                            date_last_used datetime DEFAULT CURRENT_TIMESTAMP,
                            date_uploaded datetime,
                            date_downloaded datetime,
                            uploaded TINYINT(1) NOT NULL DEFAULT '0',
                            PRIMARY KEY  (uri_id, caller_id),
                            KEY caller (caller_id),
                            FOREIGN KEY(uri_id) 
                                REFERENCES $table_name1(uri_id),
                            FOREIGN KEY(caller_id) 
                                REFERENCES $table_name2(caller_id)                            
                            ) $charset_collate;";
        $ttt = dbDelta($creation_query3);
        $table3updated = True;
    }else{ // table exists, upgrade the table if needed
        if($obj_settings->options['dbversion'] < '1.06'){ // alter table here if before v 1.06
            $update_query3 = "ALTER TABLE $table_name3 
                                ADD date_uploaded DATETIME NULL, 
                                ADD date_downloaded DATETIME NULL";
            $result = $wpdb->query($update_query3); 
        }
        if(False == NoMore404_Model_Static_Class::check_table_column_exists($table_name3, 'uploaded')){
            $update_query3 = "ALTER TABLE $table_name3 
                                ADD uploaded TINYINT(1) NOT NULL DEFAULT '0'";
            $result = $wpdb->query($update_query3); 
        }
        $table3updated = True;
    }
    
    if($table1updated && $table2updated && $table3updated){
        $obj_settings->SetLatestVersions();
    }
}    

// Register function to be called when new blogs are added
// to a network site
add_action('wpmu_new_blog', 'nomore404_new_network_site');

function nomore404_new_network_site($blog_id) {
    global $wpdb;
// Check if this plugin is active when new blog is created
// Include plugin functions if it is
    if (!function_exists('is_plugin_active_for_network'))
        { require_once( ABSPATH . '/wp-admin/includes/plugin.php' );}
// Select current blog, create new table and switch back
    if (is_plugin_active_for_network(plugin_basename(__FILE__))) {
        $start_blog = $wpdb->blogid;
        switch_to_blog($blog_id);
// Send blog table prefix to table creation function
        nomore404_create_table($wpdb->get_blog_prefix());
        switch_to_blog($start_blog);
    }
}

function nomore404_uninstall() {
    global $wpdb;
    $obj_settings = new NoMore404_Settings(); // this creates default options via constructor or creates missing options
    
    if ($obj_settings->options['remove_options_on_uninstall']) { // only remove options and data if remove options is set
        if (is_multisite()) {
            // If it is, cycle through all blogs, switch to them
            // and call function to create plugin table
            if (!empty($_GET['networkwide'])) {
                $start_blog = $wpdb->blogid;
                $blog_list = $wpdb->get_col('SELECT blog_id FROM ' . $wpdb->blogs);
                foreach ($blog_list as $blog) {
                    switch_to_blog($blog);
                    // Send blog table prefix to creation function
                    // Delete tables on a blog in network mode 
                    NoMore404_Model_Static_Class::DeleteTables($wpdb->get_blog_prefix());
                    // delete options
                    delete_option('nomore404_options');
                }
                switch_to_blog($start_blog);
                return;
            }
        }

        // Delete tables on main blog in network mode or single blog
        NoMore404_Model_Static_Class::DeleteTables($wpdb->get_blog_prefix());
        // delete options
        delete_option('nomore404_options');
    }
}
?>