<?php
/* table classes for the plugin
*/
// If this file is called directly, stop execution.
defined('ABSPATH') or exit;

// copy of wp-list-table file to save it in case it is discontinued
require_once(plugin_dir_path(__FILE__) . 'class-wp-list-table_saved.php');

// must include db functions of the plugin
require_once(plugin_dir_path(__FILE__) . 'nomore404-model.php'); // all data operations

// table for URLs/URIs
class Nomore404_List_Table_URIs_Class extends WP_List_Table_Saved {
    
    /** ************************************************************************
     * this class prepare_items() method will get callers from DB
     **************************************************************************/
    // need to have different names for different tabs, this name will be used to save option as well
    public $items_per_page_name = 'uris_per_page'; 

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'URI',     //singular name of the listed records
            'plural'    => 'URIs',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
    }

    // this function outputs string if no records found
    function no_items() {
        _e( 'No redirections found.' );
    }
    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    
    function column_default($item, $column_name){
        switch($column_name){
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    /** ************************************************************************
    These are custom column methods and is responsible for handling respective columns
     **************************************************************************/
    function column_uri_id($item){
        //Return the uri_id contents
        return sprintf('<a href="%s"> %8d </a>'
                        ,esc_url(
                            add_query_arg(
                                array('page' => 'nomore404', 'tab' => '2', 'mode' => 'browse',
                                        'uriid' => $item['uri_id']), admin_url('options-general.php')))
                
                        ,$item['uri_id']);
    }
    function column_uri_text($item){
        return sprintf('<a href="%s"> %s </a>' 
                        ,esc_url(
                            add_query_arg(
                                array('page' => 'nomore404', 'tab' => '1', 'mode' => 'edit',
                                        'id' => $item['uri_id']), admin_url('options-general.php')))
                        ,$item['uri_text'] 
        );
    }
    function column_uri_redirect_to($item){
        return sprintf('<a href="%s"> %s </a>' 
                        ,esc_url(home_url($item['uri_redirect_to']))
                        ,$item['uri_redirect_to'] 
        );
    }    
    function column_use_redirection($item){
        return sprintf(
            '<input type="checkbox" disabled="disabled" name="%1$s[]" value="Enabled" '
                . checked($item['use_redirection'], true, false)
                . '/>',
            'suspicious'                       
        );
    }   
    function column_counter($item){
        //Return the uri_id contents
        return sprintf('%8d', $item['counter']);
    }
    function column_date_created($item){
        $d1 = new DateTime($item['date_created']);
        return sprintf('%20s ', $d1->format('Y-m-d H:i:s'));
    }
     function column_date_last_used($item){
        $d1 = new DateTime($item['date_last_used']);
        return sprintf('%20s ', $d1->format('Y-m-d H:i:s'));
    }  
    /** ************************************************************************
    check box columns
     **************************************************************************/
    function column_cb($item) {
        return sprintf('<input type = "checkbox" name = "uri[]" value = "%d" />', $item['uri_id'] );
    }
    function column_suspicious($item){
        return sprintf(
            '<input type="checkbox" disabled="disabled" name="%1$s[]" value="Enabled" '
                . checked($item['suspicious'], true, false)
                . '/>',
            'suspicious'                       
        );
    }
    function column_malicious($item){
        return sprintf(
            '<input type="checkbox" disabled="disabled" name="%1$s[]" value="Enabled" '
                . checked($item['malicious'], true, false)
                . '/>',
            'malicious'                       
        );
    }
    function column_whitelist($item){
        return sprintf(
            '<input type="checkbox" disabled="disabled" name="%1$s[]" value="Enabled" '
                . checked($item['whitelist'], true, false)
                . '/>',
            'whitelist'                       
        );
    }
    
    function column_uploaded($item){
        return sprintf(
            '<input type="checkbox" disabled="disabled" name="%1$s[]" value="Enabled" '
                . checked($item['uploaded'], true, false)
                . '/>',
            'uploaded'                       
        );
    }   
    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'                => '<input type="checkbox" />',
            'uri_id'            => 'ID',
            'uri_text'          => 'URI to redirect',
            'uri_redirect_to'   => 'URI redirect to',
            'use_redirection'   => 'Enabled',
            'counter'           => 'Counter',
            'date_created'      => 'Date created',
            'date_last_used'    => 'Date last used',
            'suspicious'        => 'Suspicious', 
            'malicious'         => 'Malicious', 
            'whitelist'         => 'Whitelist',
            'uploaded'          => 'Uploaded'
        );
        return $columns;
    }

    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'uri_id'            => array('uri_id',false),     
            'uri_text'          => array('uri_text',false),
            'uri_redirect_to'   => array('uri_redirect_to',false),
            'use_redirection'   => array('use_redirection',false),
            'counter'           => array('counter',false),
            'date_created'      => array('date_created',false), 
            'date_last_used'    => array('date_last_used',true), //true means it's already sorted
            'suspicious'        => array('suspicious',false),
            'malicious'         => array('malicious',false),
            'whitelist'         => array('whitelist',false),
            'uploaded'          => array('uploaded',false)
        );
        return $sortable_columns;
    }


    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete',
            'toggle_suspicious' => 'Toggle suspcicious',
            'toggle_malicious' => 'Toggle malicious',
            'toggle_enabled' => 'Toggle enabled',
            'toggle_whitelist' => 'Toggle whitelist'
        );
        return $actions;
    }


    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
        if(isset($_REQUEST['uri'])){
            $uris = array_map('intval',$_REQUEST['uri']);
            if( 'delete'===$this->current_action() ) {
                foreach ($uris as $uri) {
                    $uri_data = NoMore404_Model_Static_Class::GetURI($uri);
                    if(False !== $uri_data && !$uri_data['whitelist']){  // cant delete whitelisted items
                        NoMore404_Model_Static_Class::DeleteURI($uri, True);
                    }    
                }
            }
            elseif ('toggle_whitelist'===$this->current_action()) {
                foreach ($uris as $uri) {
                    $uri_data = NoMore404_Model_Static_Class::GetURI($uri);
                    if(False !== $uri_data){ 
                        if(!$uri_data['malicious']){ // cant do it for malicious
                            if($uri_data['whitelist'] ) $uri_data['whitelist'] = False; else $uri_data['whitelist'] = True;
                            $ttt = NoMore404_Model_Static_Class::UpdateURI($uri_data,$uri); 
                        }
                    }
                }
            }
            elseif ('toggle_suspicious'===$this->current_action()) {
                foreach ($uris as $uri) {
                    $uri_data = NoMore404_Model_Static_Class::GetURI($uri);
                    if(False !== $uri_data){ 
                        if($uri_data['suspicious'] ) $uri_data['suspicious'] = False; else $uri_data['suspicious'] = True;
                        if($uri_data['malicious']) // malicious always suspicious
                            $uri_data['suspicious'] = True;
                        $ttt = NoMore404_Model_Static_Class::UpdateURI($uri_data,$uri); 
                        if(False !== $ttt && $uri_data['suspicious']) NoMore404_Model_Static_Class::MarkCallersSuspicious($uri);
                    }
                }
            }
            elseif ('toggle_malicious'===$this->current_action()) {
                foreach ($uris as $uri) {
                    $uri_data = NoMore404_Model_Static_Class::GetURI($uri);
                    if(False !== $uri_data){
                        if($uri_data['malicious']) $uri_data['malicious'] = False;
                        else $uri_data['malicious'] = True;
                        if($uri_data['whitelist']) $uri_data['malicious'] = False; // whitelist cant be malicious, can only be suspicious
                        if($uri_data['malicious'] ) $uri_data['suspicious'] = True;
                        $ttt = NoMore404_Model_Static_Class::UpdateURI($uri_data,$uri); 
                        if(False !== $ttt && $uri_data['malicious']) NoMore404_Model_Static_Class::MarkCallersMalicious($uri);
                    }
                }
            } elseif ('toggle_enabled'===$this->current_action()) {
                foreach ($uris as $uri) {
                    $uri_data = NoMore404_Model_Static_Class::GetURI($uri);
                    if(False !== $uri_data){
                        if(True == $uri_data['use_redirection'] ) $uri_data['use_redirection'] = False;
                        else $uri_data['use_redirection'] = True;
                        NoMore404_Model_Static_Class::UpdateURI($uri_data,$uri); 
                    }
                }
            }
        }    
    }
    
    function Items_per_page(){
        $options = get_option('nomore404_options');
        
        // get $this->items_per_page_name from options or set options for the first time
        if ($options === false) {
            $options["$this->items_per_page_name"] = 8;
            add_option('nomore404_options', $options);
            $items_per_page = 8;
        }else{
            if(0 == $options["$this->items_per_page_name"]){
                $options["$this->items_per_page_name"] = 8;
                $items_per_page = 8;
                update_option('nomore404_options', $options);
            }else{
                $items_per_page = $options["$this->items_per_page_name"];
            }            
        }
        
        // if the field is set already, save it in options
        if(isset($_REQUEST["$this->items_per_page_name"])){
            $items_per_page = (int)$_REQUEST["$this->items_per_page_name"]; 
        }
        
        echo '<div class="alignleft actions bulkactions">';
        echo '<label for="items-per-page" class="screen-reader-text">' . __( 'Per page' ) . '</label>';
	echo '<input type="text" name="' . $this->items_per_page_name 
                . '" id="items-per-page" size="3" value="' 
                . $items_per_page . '">';

        submit_button( __( 'Set' ), 'setitemsperpage', '', false, array( 'id' => "setitemsperpage" ) );
        echo '</div>';
    }

    // outputs header and link for add new button
    public function TitleAndAddNewButton($title = '', $addnewbuttonlink = '') {
        $output = '';
        $output .= '<h2>' . $title;
        if('' !== $addnewbuttonlink){
            $output .= '<a class="add-new-h2" href="';
            $output .= $addnewbuttonlink;
            $output .= '">Add New</a>';
        }
        $output .= '</h2>';
        echo $output;
    }
    
    public function extra_tablenav( $which ){ // overriding the class method to put pages in tablenav
        if('top' === $which){
            $this->Items_per_page();
                    
            echo '<div class="alignleft actions bulkactions">';
            $this->search_box('Search Table', 'search_uri_id');
            echo '</div>';
        }
    }
    
    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items($search ='', $per_page = 0, $callerid = '') {
        global $wpdb; 
        
        // working out items per page, saving and or retieving it from options
        $options = get_option('nomore404_options');
        if(0 !== $per_page){ // this means per page is set from the form and button pressed, need to save option
            $options["$this->items_per_page_name"] = $per_page;
            update_option('nomore404_options', $options); // saving value from the field            
        }    
        else{ // take per page from options   
            $per_page = 8; // default per page to be overwritten
            $options = get_option('nomore404_options');
            if ($options !== false) {
                if(isset($options["$this->items_per_page_name"])){
                    $per_page = ( (int) $options["$this->items_per_page_name"] == 0 ? 8 : (int) $options["$this->items_per_page_name"]);
                }
            }
        }
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $orderby = (!empty($_REQUEST['orderby'])) ? sanitize_text_field($_REQUEST['orderby'])  : 'date_last_used'; //If no sort, default to date_last_used
        $order = (!empty($_REQUEST['order'])) ? sanitize_text_field($_REQUEST['order']) : 'desc'; //If no order, default to desc
        
        $this->process_bulk_action(); // process any  actions, the latest place where we can do it, before data retrieval from the db
        
                
        $current_page = $this->get_pagenum();
        $data = NoMore404_Model_Static_Class::getURIs($orderby, $order, $search, $callerid, $current_page, $per_page);
        
        $total_items = NoMore404_Model_Static_Class::countURIs($search, $callerid);        
        
        $this->items = $data;
        
        $this->set_pagination_args( array(
                'total_items' => $total_items,                  //WE have to calculate the total number of items
                'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
                'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
            ) );
    }


}

// table for Callers
class Nomore404_List_Table_Callers_Class extends WP_List_Table_Saved {
    
    /** ************************************************************************
     * this class prepare_items() method will get callers from DB
     **************************************************************************/
    // need to have different names for different tabs, this name will be used to save option as well
    public $items_per_page_name = 'callers_per_page'; 

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'Caller',     //singular name of the listed records
            'plural'    => 'Callers',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
    }

    // this function outputs string if no records found
    function no_items() {
        _e( 'No callers found.' );
    }
    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    
    function column_default($item, $column_name){
        switch($column_name){
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    /** ************************************************************************
    These are custom column methods and is responsible for handling respective columns
     **************************************************************************/
    function column_caller_id($item){
        //Return the caller_id contents
        return sprintf('<a href="%s"> %8d </a>'
                        ,esc_url(
                            add_query_arg(
                                array('page' => 'nomore404', 'tab' => '1', 'mode' => 'browse',
                                        'callerid' => $item['caller_id']), admin_url('options-general.php')))
                
                        ,$item['caller_id']);
    }
    function column_caller_ip($item){
        return sprintf('<a href="%s"> %13s </a>' 
                        ,esc_url(
                            add_query_arg(
                                array('page' => 'nomore404', 'tab' => '2', 'mode' => 'edit',
                                        'id' => $item['caller_id']), admin_url('options-general.php')))
                        ,inet_ntop($item['caller_ip']) // converting binary to human readable
        );
    }
    function column_comment($item){
        return sprintf('%50s', $item['comment']); 
    }    
    function column_hostname($item){
        return sprintf('%50s', $item['hostname']); 
    } 
    function column_date_created($item){
        $d1 = new DateTime($item['date_created']);
        return sprintf('%20s ', $d1->format('Y-m-d H:i:s'));
    }
     function column_date_last_used($item){
        $d1 = new DateTime($item['date_last_used']);
        return sprintf('%20s ', $d1->format('Y-m-d H:i:s'));
    }  
    function column_counter($item){
        return sprintf('%8d', $item['counter']);
    }
    /** ************************************************************************
    check box columns
     **************************************************************************/
    function column_cb($item) {
        return sprintf('<input type = "checkbox" name = "caller[]" value = "%d" />', $item['caller_id'] );
    }
    
    function column_suspicious($item){
        return sprintf(
            '<input type="checkbox" disabled="disabled" name="%1$s[]" value="Enabled" '
                . checked($item['suspicious'], true, false)
                . '/>',
            'suspicious'                       
        );
    }
    function column_malicious($item){
        $urlabusedb = esc_url('https://www.abuseipdb.com/check/' . $item['caller_ip_str'] );
        return sprintf(
            '<input type="checkbox" disabled="disabled" name="%1$s[]" value="Enabled" '
                . checked($item['malicious'], true, false)
                . '/> <a href="%2$s">?</a>',
            'malicious',
            $urlabusedb
        );
    }
    
    function column_whitelist($item){
        return sprintf(
            '<input type="checkbox" disabled="disabled" name="%1$s[]" value="Enabled" '
                . checked($item['whitelist'], true, false)
                . '/>',
            'whitelist'                       
        );
    }    
    
    function column_uploaded($item){
        return sprintf(
            '<input type="checkbox" disabled="disabled" name="%1$s[]" value="Enabled" '
                . checked($item['uploaded'], true, false)
                . '/>',
            'uploaded'                       
        );
    }     

    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'caller_id'     => 'ID',
            'caller_ip'     => 'IP Address',
            'comment'       => 'Comment',
            'hostname'      => 'Hostname',
            'date_created'    => 'Date created',
            'date_last_used'    => 'Date last used',
            'counter'           => 'Counter',
            'suspicious'        => 'Suspicious', //Render a checkbox instead of text
            'malicious'        => 'Malicious', //Render a checkbox instead of text
            'whitelist'         => 'Whitelist',
            'uploaded'      => 'Uploaded'
        );
        return $columns;
    }

    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'caller_id'     => array('caller_id',false),     
            'caller_ip'     => array('caller_ip',false),
            'hostname'      => array('hostname',false),
            'comment'       => array('comment',false),
            'date_created'  => array('date_created',false), 
            'date_last_used'  => array('date_last_used',true), //true means it's already sorted
            'counter'       => array('counter',false),
            'suspicious'    => array('suspicious',false),
            'malicious'     => array('malicious',false),
            'whitelist'     => array('whitelist',false),
            'uploaded'      => array('uploaded',false)
        );
        return $sortable_columns;
    }


    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'delete'            => 'Delete',
            'toggle_suspicious' => 'Toggle suspcicious',
            'toggle_malicious'  => 'Toggle malicious',
            'toggle_whitelist'  => 'Toggle whitelist',
            'find_hostnames'    => 'Find hostnames'
        );
        return $actions;
    }

    function os_gethostbyaddr($ip = ''){
        //NoMore404_Model_Static_Class::good_error_log('PHPUNAME:',php_uname('s'));
        if(strtoupper(substr(php_uname('s'), 0, 3)) === 'WIN' ){ // windows
            //return gethostbyaddr($ip
            
            // execute nslookup command
            exec('nslookup '.$ip, $op);
            //NoMore404_Model_Static_Class::var_error_log('IP:', $ip);
            //NoMore404_Model_Static_Class::var_error_log('output:', $op);
            if(isset($op[3]))
                return substr($op[3], 6);
            else
                return $ip;
        } else { // linux
            //return gethostbyaddr($ip);
            //
            // execute nslookup command
            exec('nslookup '.$ip, $op);
            
            // on linux nslookup returns 2 diffrent line depending on
            // ip or hostname given for nslookup
            if(isset($op[4])){
                if (strpos($op[4], 'name = ') > 0)
                        return substr($op[4], strpos($op[4], 'name =') + 7, -1);
                else
                        return substr($op[4], strpos($op[4], 'Name:') + 6); 
                
            }
            else{
                return $ip;
            }
                
        }
    }

    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
        if (isset($_REQUEST['caller'])) {
            $callers = array_map('intval', $_REQUEST['caller']);
            if ('delete' === $this->current_action()) {
                foreach ($callers as $caller) {
                    $delete_status = NoMore404_Model_Static_Class::DeleteCaller($caller, True);
                }
            }  elseif ('toggle_whitelist' === $this->current_action()) {
                foreach ($callers as $caller) {
                    $caller_data = NoMore404_Model_Static_Class::GetCaller($caller);
                    if(False !== $caller_data){ 
                        if(!$caller_data['malicious']){ // cant do it for malicious
                            if($caller_data['whitelist'] ) $caller_data['whitelist'] = False; else $caller_data['whitelist'] = True;
                            NoMore404_Model_Static_Class::UpdateCaller($caller_data, $caller);
                        }
                    }
                }
            }  elseif ('find_hostnames' === $this->current_action()) {
                foreach ($callers as $caller) {
                    $caller_data = NoMore404_Model_Static_Class::GetCaller($caller);
                    if(False !== $caller_data){
                        $hostname = $this->os_gethostbyaddr($caller_data['caller_ip_str']);
                        
                        if(False !==  $hostname){ // cant do it for malicious
                            if(strlen($caller_data['hostname']) > 0){ // save previous hostname to comment
                                $newcomment = $caller_data['comment'] . ':previous hostname:' . $caller_data['hostname'];
                                $caller_data['comment'] = $newcomment;
                            }
                            $caller_data['hostname'] = $hostname;
                            NoMore404_Model_Static_Class::UpdateCaller($caller_data, $caller);
                        }
                    }
                }
            } elseif ('toggle_suspicious' === $this->current_action()) {
                foreach ($callers as $caller) {
                    $caller_data = NoMore404_Model_Static_Class::GetCaller($caller);

                    if (False !== $caller_data) {
                        if (True == $caller_data['suspicious'])
                            $caller_data['suspicious'] = False;
                        else
                            $caller_data['suspicious'] = True;
                        if ($caller_data['malicious']) { // malicious always suspicious
                            $caller_data['suspicious'] = True;
                        }
                        NoMore404_Model_Static_Class::UpdateCaller($caller_data, $caller);
                    }
                }
            } elseif ('toggle_malicious' === $this->current_action()) {
                foreach ($callers as $caller) {
                    $caller_data = NoMore404_Model_Static_Class::GetCaller($caller);
                    if (False !== $caller_data) {
                        if ($caller_data['malicious']) $caller_data['malicious'] = False;
                        else $caller_data['malicious'] = True;
                        if ($caller_data['whitelist']) $caller_data['malicious'] = False; // if whitelist, not malicious then
                        if ($caller_data['malicious']) $caller_data['suspicious'] = True;
                        NoMore404_Model_Static_Class::UpdateCaller($caller_data, $caller);
                    }
                }
            }
        }
    }

    function Items_per_page(){
        $options = get_option('nomore404_options');
        
        // get $this->items_per_page_name from options or set options for the first time
        if ($options === false) {
            $options["$this->items_per_page_name"] = 8;
            add_option('nomore404_options', $options);
            $items_per_page = 8;
        }else{
            if(0 == $options["$this->items_per_page_name"]){
                $options["$this->items_per_page_name"] = 8;
                $items_per_page = 8;
                update_option('nomore404_options', $options);
            }else{
                $items_per_page = $options["$this->items_per_page_name"];
            }            
        }
        
        // if the field is set already, save it in options
        if(isset($_REQUEST["$this->items_per_page_name"])){
            $items_per_page = (int)$_REQUEST["$this->items_per_page_name"]; 
        }
        
        echo '<div class="alignleft actions bulkactions">';
        echo '<label for="items-per-page" class="screen-reader-text">' . __( 'Per page' ) . '</label>';
	echo '<input type="text" name="' . $this->items_per_page_name 
                . '" id="items-per-page" size="3" value="' 
                . $items_per_page . '">';

        submit_button( __( 'Set' ), 'setitemsperpage', '', false, array( 'id' => "setitemsperpage" ) );
        echo '</div>';
    }

    // outputs header and link for add new button
    public function TitleAndAddNewButton($title = '', $addnewbuttonlink = '') {
        $output = '';
        $output .= '<h2>' . $title;
        if('' !== $addnewbuttonlink){
            $output .= '<a class="add-new-h2" href="';
            $output .= $addnewbuttonlink;
            $output .= '">Add New</a>';
        }
        $output .= '</h2>';
        echo $output;
    }
    
    public function extra_tablenav( $which ){ // overriding the class method to put pages in tablenav
        if('top' === $which){
            $this->Items_per_page();
                    
            echo '<div class="alignleft actions bulkactions">';
            $this->search_box('Search Table', 'search_caller_id');
            echo '</div>';
        }
    }
    
    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items($search ='', $per_page = 0, $uriid = '') {
        global $wpdb; 
        
        // sorting out per page number of elements
        $options = get_option('nomore404_options');
        if(0 !== $per_page){ // this means per page is set from the form and button pressed, need to save option
            $options["$this->items_per_page_name"] = $per_page;
            update_option('nomore404_options', $options); // saving value from the field            
        }    
        else{ // take per page from options   
            $per_page = 8; // default per page to be overwritten
            $options = get_option('nomore404_options');
            if ($options !== false) {
                if(isset($options["$this->items_per_page_name"])){
                    $per_page = ( (int) $options["$this->items_per_page_name"] == 0 ? 8 : (int) $options["$this->items_per_page_name"]);
                }
            }
        }
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $orderby = (!empty($_REQUEST['orderby'])) ? sanitize_text_field($_REQUEST['orderby']) : 'date_last_used'; //If no sort, default to date_last_used
        $order = (!empty($_REQUEST['order'])) ? sanitize_text_field($_REQUEST['order']) : 'desc'; //If no order, default to descending
        
        $this->process_bulk_action(); // process any  actions, the latest place where we can do it, before data retrieval from the db
        
                
        $current_page = $this->get_pagenum();

        $data = NoMore404_Model_Static_Class::getCallers($orderby, $order, $search, $uriid, $current_page, $per_page);
        
        //NoMore404_Model_Static_Class::good_error_log('Callers in list class:', $data);
        
        $total_items = NoMore404_Model_Static_Class::countCallers($search, $uriid);
        
        //$data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        $this->items = $data;
        
        $this->set_pagination_args( array(
                'total_items' => $total_items,                  //WE have to calculate the total number of items
                'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
                'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
            ) );
    }
}
?>