<?php
/*
 * Plugin Name:         NoMore404 404 Redirection and Firewall
 * Plugin URI:          https://devoutpro.com/nomore404
 * Description:         NoMore404 is a free WordPress plugin for redirection of 404 pages (301 type of redirection) and simple firewall to block malicious hosts and URLs. 
 * Version:             2.1
 * Requires at least:   4.0
 * Requires PHP:        5.3
 * Author:              Devoutpro.com (ILYA POLYAKOV)
 * Author URI:          https://devoutpro.com/ip
 * License:             GPL v2 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:         nomore404
 * Domain Path:         /languages
 * 
  "No more 404" is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 2 of the License, or
  any later version.

  "No more 404" is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with "No more 404". If not, see <http://www.gnu.org/licenses/>.
 * 
 */

// If this file is called directly, stop execution.
defined('ABSPATH') or exit;

define("NOMORE404VERSION", "2.1");
define("NOMORE404DBVERSION", "2.1");

// plugin activation and uninstallation
include_once plugin_dir_path(__FILE__) . 'includes/nomore404-activation.php'; // all activation and deactivation code
register_activation_hook(__FILE__, 'nomore404_activation');
register_uninstall_hook( __FILE__, 'nomore404_uninstall' );
register_deactivation_hook (__FILE__, 'nomore404_cronstarter_deactivate'); // removing cron on deactivation

add_action('admin_menu', 'nomore404_settings_menu');

function nomore404_settings_menu() {
    add_options_page('NoMore404 Data Management', 'NoMore404', 'manage_options', 'nomore404', 'nomore404_config_page');
}

// must include output class of the plugin
require_once plugin_dir_path(__FILE__) . 'includes/nomore404-view.php'; // all html outputs are here
// must include db functions of the plugin
require_once plugin_dir_path(__FILE__) . 'includes/nomore404-model.php'; // all data operations 
// must include table classes for UI
require_once plugin_dir_path(__FILE__) . 'includes/nomore404-list-table-class.php'; // table classes
// must include uploads and downloads
require_once plugin_dir_path(__FILE__) . 'includes/nomore404-upload-download.php'; // class for upload/download

// actual processing of redirection or blocking malicious URIs
add_action( 'template_redirect', 'process_404' );
function process_404(){
    
    // redirects do not work for admin, and not processing if page found
    if ( !is_404() || is_admin() ) {
            return;
    }
    
    // here we know it is not admin and it is 404 page
    // we know it is not malicious host as well, as malicious would be blocked by now
    
    $uriid = '';
    $callerid = '';
    
    //  process URI
    if ( isset( $_SERVER['REQUEST_URI'] ) ) {
        $request_uri = trim(esc_url($_SERVER['REQUEST_URI']), '/ '); // trim slashes and spaces
    
        $uri_current_record = NoMore404_Model_Static_Class::getURI('', $request_uri);
        if (NULL !== $uri_current_record && False !== $uri_current_record ) { // URI record exists, 
                // need to update counter and date last used for URI
                $uri_current_record['counter'] = (int) ($uri_current_record['counter']) + 1;
                $uri_current_record['date_last_used'] = current_time('mysql', TRUE);
                $uri_current_record['uploaded'] = FALSE;
                $uriid = $uri_current_record['uri_id'];
                $uriupdatestatus = NoMore404_Model_Static_Class::UpdateURI($uri_current_record, $uriid);
        } else { // URI record does not exist, can insert now
                $uri_data = array();
                $uri_data['uri_text'] = $request_uri;
                $uri_data['use_redirection'] = True; 
                $uri_data['counter'] = 1; 
                $uri_data['uploaded'] = False;
                $uriid = NoMore404_Model_Static_Class::InsertURI($uri_data);
                if($uriid !== False) $uri_current_record = NoMore404_Model_Static_Class::getURI($uriid); // get the full record
        };
    }
    
    if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
            $referer = esc_url( $_SERVER['HTTP_REFERER'] );
    } else $referer = '' ;

    if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
        $caller_ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
    
        //first lets check if this caller IP is in the db - this is independend of URI         
        $callerip = inet_pton(trim($caller_ip));
        if ($callerip !== False) {

            // check if this caller already exist
            $caller_current_record = NoMore404_Model_Static_Class::getCaller('', $callerip);
            
            if (NULL !== $caller_current_record && False !== $caller_current_record ) { // Caller record exists,
                $caller_current_record['counter'] = (int) ($caller_current_record['counter'] ) + 1;
                $caller_current_record['uploaded'] = FALSE;
                $callerid = $caller_current_record['caller_id'];              
                NoMore404_Model_Static_Class::UpdateCaller($caller_current_record, $callerid);
            } else { // create the caller record
                // add caller first, as it is needed for call/uri table
               $caller_data = array();
               $caller_data['caller_ip'] = $callerip;
               $caller_data['caller_ip_str'] = $caller_ip; 
               $caller_data['uploaded'] = False;
               $caller_data['referrer'] = $referer;

               $callerid = NoMore404_Model_Static_Class::InsertCaller($caller_data);
               if($callerid !== False) $caller_current_record = NoMore404_Model_Static_Class::getCaller($callerid); 
            }
        }else{ // bad ip    
            $callerid = False;
        }
        
    }
    // at this point callerid is populated and $caller_current_record has current record in it
        
    // update caller/uri table join
    // *** processing uri - caller connection ***
     if(is_numeric($callerid) && is_numeric($uriid)){ // process only if both caller and uri are present
         // search for uriid, callerid (primary key) in uri_caller table
         $uri_caller_record = NoMore404_Model_Static_Class::getURICaller($uriid, $callerid);

         if (False !== $uri_caller_record && NULL !== $uri_caller_record){  // found uri-caller
              // increase counter
             $uri_caller_record['counter'] = (int) ($uri_caller_record['counter'] ) + 1;
             $uri_caller_record['date_last_used'] = current_time('mysql', TRUE);
             $uri_caller_record['uploaded'] = FALSE;
             $uri_caller = NoMore404_Model_Static_Class::UpdateURICaller($uri_caller_record, $uriid, $callerid);
         } else { // uri_caller record not found, creating new one
             $uri_caller_record = array();
             $uri_caller_record['uri_id'] = $uriid;
             $uri_caller_record['caller_id'] = $callerid;
             $uri_caller_record['counter'] = 1;
             $uri_caller_record['uploaded'] = False;
             $uri_caller = NoMore404_Model_Static_Class::InsertURICaller($uri_caller_record);
         };            
     };   
     
    $settings_obj = new NoMore404_Settings();
    $opt = $settings_obj->GetOptions();     
     
     if(True == $uri_current_record['malicious'] && True == $opt['block_malicious']){
         $counters_obj = new NoMore404_counters();
         
         // update caller as malicious if he is not and not whitelisted
         if(False !== $callerid && !$caller_current_record['malicious'] && !$caller_current_record['whitelist']){ 
            $caller_current_record['malicious'] = True;
            $caller_current_record['suspicious'] = True;
            $caller_current_record['uploaded'] = False;
            NoMore404_Model_Static_Class::UpdateCaller($caller_current_record, $callerid);
            
            // it is malicious caller, should we mark all his URIs as malicious ??? !!!
            
            // increase counter of hosts marked malicious 
            $counters_obj->IncreaseCounter('countCallersMarkedMalicious');
         }
         // increase counter of blocked URIs by 1 
        $counters_obj->IncreaseCounter('countURIblocked');
         
         // stop executing for malicious uri
         wp_die('Sorry, our site is undergoing maintenance, please try again later','Scheduled maintenance'); // malicious uri, stop working
     }
     
     // all db updates are done, now redirect or not?
     if(True == $uri_current_record['use_redirection'] && True == $opt['enable_redirection']){ // redirection is enabled for that uri
         $counters_obj = new NoMore404_counters();
         
         if('' == trim($uri_current_record['uri_redirect_to'])){ // redirect target is empty, use default from options
             $uri_redirect_to = $opt['default_redirect_to'];
             
            // increase counter of sucessfully redirected URIs by 1 
            $counters_obj->IncreaseCounter('countURIredirected');             
         }else{
             $uri_redirect_to = trim($uri_current_record['uri_redirect_to']);
             
            // increase counter of sucessfully redirected URIs by 1 
            $counters_obj->IncreaseCounter('countURIredirected2Custom');              
         }
         
         $redirection_url = home_url($uri_redirect_to);
         
         wp_redirect($redirection_url, 301);
         exit;
     } else {
         return;
     }
}

// blocking of malicious hosts
add_action('plugins_loaded', 'nomore404_check_caller');
function nomore404_check_caller(){
    if ( is_admin() ) { // not blocking admins , so we dont lock out by accident
            return;
    }
    $caller_ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
    
    $calleripVAR = inet_pton($caller_ip); // internal representation for IP
    
    $caller_data = NoMore404_Model_Static_Class::getCaller('', $calleripVAR);
    
    if (NULL === $caller_data) return; // error searching caller, no need to block    
    if (False === $caller_data) return; // error searching caller, no need to block
    // now we have record in $caller_data
    if(!$caller_data['malicious']) return; // safe caller, no need to block
    
    $settings_obj = new NoMore404_Settings();
    if($settings_obj->options['block_malicious']){
        $counters_obj = new NoMore404_counters();
        // increase counter of blocked Callers by 1
        $counters_obj->IncreaseCounter('countCallersBlocked');
        
        // need to update date_last_used and counter of caller here
        $caller_data['date_last_used'] = current_time('mysql', TRUE);
        $caller_data['counter']++;
        $caller_data['uploaded'] = False;
        NoMore404_Model_Static_Class::UpdateCaller($caller_data, $caller_data['caller_id']);
        
        wp_die('Sorry, our site is undergoing maintenance, please try again later','Scheduled maintenance'); // malicious caller, stop working
    }
}

//main interface function for backend, note there is no front end interface for the plugin
function nomore404_config_page() {
    global $wpdb;

    $view404 = new NoMore404_View();
 
    ?>
<!-- Top-level menu -->
        <div id="nomore404-general" class="wrap">
        <h2>NoMore404 (404 redirection and simple firewall)</h2>
    <?php
    
        //session_start(); // start session to save UI objects and for error handling   
        //
        //
        // check current tab and set it to 1 if not set
        if (isset($_GET['tab']))
            $curtab = (int) $_GET['tab'];
        else
            $curtab = 1;

        $view404->ShowTabs($curtab); // output tabs in the top of the page
        

        
        if(7 === $curtab){ // uploads and downloads page
            $view404->ShowToolsForm();
        }if (5 === $curtab) {  // imports tab
            $view404->ShowImportForm(); 
        } elseif (6 === $curtab) {    // help tab
            $view404->ShowHelp();
        } elseif (4 === $curtab) {    // Pro tab
            $view404->ShowPro();
        } elseif (3 === $curtab) {    // settings form   
            $settings_Obj = new NoMore404_Settings();
            $settings_Obj->ShowEditForm();
        } elseif (2 === $curtab) {    // Callers form
                if(isset($_GET['mode']) && 'browse' !== $_GET['mode']){ // callers edit, or new form
                    if('new' === $_GET['mode']){ // add new caller
                        $caller_data = array();
                        foreach (NoMore404_Model_Static_Class::getCallerColumnNames() as $value) {
                            $caller_data[$value] = '';
                        }
                        $caller_id = 'new';
                        echo '<h3>Add New Caller</h3>';
                        // recover session data to make life easier for user
                        //if (isset($_SESSION['caller_data'])) 
                        //    $caller_data = array_merge($caller_data,$_SESSION['caller_data']);
                        if (isset($_COOKIE['caller_ip_str'])){
                            $caller_data['caller_ip_str'] = sanitize_text_field($_COOKIE['caller_ip_str']);
                        }
                    }elseif('edit' === $_GET['mode']) { // edit existing caller
                        $caller_id = (int) $_GET['id'];
                        $caller_data = NoMore404_Model_Static_Class::getCaller($caller_id);
                        if(False === $caller_data) wp_die('No such caller');
                        
                        echo '<h3>Edit Caller #' . $caller_data['caller_id'] . ' - ';
                        echo $caller_data['caller_ip_str'] . '</h3>';
                        // recover session data to make life easier for user in case edit save did not work
                        if(isset($_GET['err'])){
                            if (isset($_COOKIE['caller_ip_str'])){
                                $caller_data['caller_ip_str'] = sanitize_text_field($_COOKIE['caller_ip_str']);
                            }
                        }    
                        //    $caller_data = array_merge($caller_data,$_SESSION['caller_data']);
                    }      
                    //session_write_close(); //now close the session to avoid hold up
                    ?>
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                        <input type="hidden" name="action" value="save_nomore404_caller" />
                        <input type="hidden" name="caller_id" value="<?php echo esc_attr($caller_id); ?>"/>
                        <!-- Adding security through hidden referrer field -->
                        <?php wp_nonce_field('nomore404_add_edit_caller'); ?>
                    <?php
                        //Display caller editing form
                        $view404->showCallerEditForm($caller_data);
                        submit_button( 'Submit', 'primary', 'btnSubmit', False);
                        submit_button( 'Cancel', 'primary', 'btnCancel', False);
                    ?>
                    </form>
                    <?php                    
                }else{  // callers table with its all navigation and links
                        $callersTable = new Nomore404_List_Table_Callers_Class();
                        
                        // setting uri id for filtering of callers that called uriid
                        if(isset($_GET['mode']) && 'browse' == $_GET['mode'])
                            $uriid = ( isset($_GET['uriid']) ? (int) $_GET['uriid'] : '');
                        else $uriid = '';
                        
                        if(isset($_GET['s'])){
                            $search = sanitize_text_field($_GET['s']);
                        }else{
                            $search = ( isset($_POST['s']) ? sanitize_text_field($_POST['s']) : ''); 
                        }
                        
                        $per_page = ( isset($_POST['callers_per_page']) ? (int) $_POST['callers_per_page'] : 0);
                    ?>
                        <form id="callers" method="post">
                            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                            <!-- Now we can render the completed list table -->
                            <div class="wrap">
                            <?php
                        $callersTable->prepare_items($search, $per_page, $uriid);                    
                        $callersTable->TitleAndAddNewButton('Callers ',
                                            esc_url(add_query_arg(
                                                        array('page' => 'nomore404', 
                                                                'tab' => '2' 
                                                                ,'mode' => 'new'), 
                                                    admin_url('options-general.php')))
                                            );
                        $callersTable->display();
                            ?>
                            </div>
                        </form>
                     <?php
                }    
        } 
        elseif ($curtab === 1) { // URIs
                if(isset($_GET['mode']) && 'browse' !== $_GET['mode']){ // uris edit, or new form
                    if('new' === $_GET['mode']){ // add new uri
                        $uri_data = array();
                        foreach (NoMore404_Model_Static_Class::getURIColumnNames() as $value) {
                            $uri_data[$value] = '';
                        }
                        $uri_id = 'new';
                        echo '<h3>Add New Redirection</h3>';
                        // recover uri to make life easier for user
                        if (isset($_COOKIE['uri_text'])) {
                            $uri_data['uri_text'] = sanitize_text_field($_COOKIE['uri_text']);
                        }
                        //if (isset($_SESSION['uri_data'])) 
                        //    $uri_data = array_merge($uri_data,$_SESSION['uri_data']); 
                    }elseif ('edit' === $_GET['mode']) { // edit existing uri
                        $uri_id = (int) $_GET['id'];
                        $uri_data = NoMore404_Model_Static_Class::getURI($uri_id);
                        if(False === $uri_data) wpdie('No such redirection');
                        
                        echo '<h3>Edit Redirection #' . $uri_data['uri_id'] . ' - ';
                        echo $uri_data['uri_text'] . '</h3>';
                        // recover session data to make life easier for user in case edit save did not work
                        if(isset($_GET['err']) && isset($_COOKIE['uri_text'])){
                            $uri_data['uri_text'] = sanitize_text_field($_COOKIE['uri_text']); 
                        }
                        //    $uri_data = array_merge($uri_data,$_SESSION['uri_data']);
                    }    
                    //session_write_close(); //now close the session to avoid hold up
                    ?>
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                        <input type="hidden" name="action" value="save_nomore404_uri" />
                        <input type="hidden" name="uri_id" value="<?php echo esc_attr($uri_id); ?>"/>
                        <!-- Adding security through hidden referrer field -->
                        <?php wp_nonce_field('nomore404_add_edit_uri'); ?>
                    <?php
                        //Display uri editing form
                        $view404->showURIEditForm($uri_data);
                        submit_button( 'Submit', 'primary', 'btnSubmit', False);
                        submit_button( 'Cancel', 'primary', 'btnCancel', False);
                    ?>
                    </form>
                    <?php                    
                }else{  // uris table with its all navigation and links
                        $urisTable = new Nomore404_List_Table_URIs_Class();
                        
                        // setting caller id for filtering of uris that called that uri
                        if(isset($_GET['mode']) && 'browse' == $_GET['mode'])
                            $callerid = ( isset($_GET['callerid']) ? (int) $_GET['callerid'] : '');
                        else $callerid = '';
                        
                        if(isset($_GET['s'])){
                            $search = sanitize_text_field($_GET['s']);
                        }else{
                            $search = ( isset($_POST['s']) ? sanitize_text_field($_POST['s']) : ''); 
                        }
                        
                        $per_page = ( isset($_POST['uris_per_page']) ? (int) $_POST['uris_per_page'] : 0);
                        $urisTable->prepare_items($search, $per_page, $callerid);                    
                    ?>
                        <form id="uris" method="post">
                            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                            <!-- Now we can render the completed list table -->
                            <div class="wrap">
                            <?php
                            $urisTable->TitleAndAddNewButton('Manage redirections ',
                                                esc_url(add_query_arg(
                                                            array('page' => 'nomore404', 
                                                                    'tab' => '1' 
                                                                    ,'mode' => 'new'), 
                                                            admin_url('options-general.php')))
                                                );
                            $urisTable->display();
                            ?>
                            </div>
                        </form>
                     <?php
                        if(isset($_GET['mode']) && 'browse' === $_GET['mode']){ // browsing connected records
                            // will add it later, when URI is reworked
                        }
                }    
        }
    ?></div><?php
}

// adding form actions to process input
add_action('admin_init', 'nomore404_admin_init');
function nomore404_admin_init() {
    add_action('admin_post_nomore404_edit_settings', 'process_nomore404_edit_settings');
    add_action('admin_post_save_nomore404_uri', 'process_nomore404_uri');
    add_action('admin_post_save_nomore404_caller', 'process_nomore404_caller');
    add_action('admin_post_nomore404_upload', 'process_nomore404_upload');
    add_action('admin_post_import_nomore404_from_404_to_301', 'import_nomore404_from_404_to_301');
}

// prepare upload json and upload to S3 bucket
function process_nomore404_upload(){
    // Check if user has proper security level
    if (!current_user_can('manage_options')) wp_die('Not allowed');
    // Check if nonce field is present for security
    check_admin_referer('nomore404_upload_nonce');   
    
    if (isset($_POST['btnUpload'])) { // button upload clicked
        $uploaded = NoMore404_UD_Static_Class::UploadAllNewData();
        if($uploaded)
            wp_safe_redirect(add_query_arg(array('page' => 'nomore404', 'tab' => '7', 'err' => '102'), 
                                            admin_url('options-general.php')));
        else
            wp_safe_redirect(add_query_arg(array('page' => 'nomore404', 'tab' => '7', 'err' => '6'), 
                                            admin_url('options-general.php')));    }    
}

// saving options
function process_nomore404_edit_settings() {
    // Check if user has proper security level
    if (!current_user_can('manage_options')) wp_die('Not allowed');
    // Check if nonce field is present for security
    check_admin_referer('nomore404_edit_settings_nonce');
    
    if (isset($_POST['btnCancel'])) {
            wp_safe_redirect(add_query_arg(array('page' => 'nomore404', 'tab' => '1'), admin_url('options-general.php')));
            exit;    
    }

    // Place all user submitted values in an array (or empty strings if no value was sent)
    $opt = array();
    $opt['enable_redirection'] = ( isset($_POST['enable_redirection']) ? True : False );
    $opt['default_redirect_to'] = ( isset($_POST['default_redirect_to']) ? 
                sanitize_text_field(trim($_POST['default_redirect_to'], '/ ')) : '' );
    $opt['block_malicious'] = ( isset($_POST['block_malicious']) ? True : False );
    $opt['share_baddies'] = ( isset($_POST['share_baddies']) ? True : False );
    $opt['remove_options_on_uninstall'] = ( isset($_POST['remove_options_on_uninstall']) ? True : False );
    
    $settings_Obj = new NoMore404_Settings();
    
    $ttt = $settings_Obj->UpdateOptions($opt);
    if( TRUE == $ttt ){
        // Redirect the page to the options form to saved
        wp_safe_redirect(add_query_arg(array('page' => 'nomore404', 'tab' => '3', 'err' => '100'), admin_url('options-general.php')));
    }else{
        wp_safe_redirect(add_query_arg(array('page' => 'nomore404', 'tab' => '3', 'err' => '2'), admin_url('options-general.php')));
    }
    exit;
}

// processing caller editing form
function process_nomore404_caller() {
    // Check if user has proper security level
    if (!current_user_can('manage_options')) wp_die('Not allowed');

    // Check if nonce field is present for security
    check_admin_referer('nomore404_add_edit_caller');
    
    if (isset($_POST['btnCancel'])) {
            wp_safe_redirect(add_query_arg(array('page' => 'nomore404', 'tab' => '2'), admin_url('options-general.php')));
            exit;    
    }
    
    $caller_id = ( isset($_POST['caller_id']) ? (int) $_POST['caller_id'] : '' );
    
    // Place all user submitted values in an array (or empty strings if no value was sent)
    $caller_data = array();
    $caller_data['caller_ip_str'] = ( isset($_POST['caller_ip_str']) ? 
            filter_var(trim($_POST['caller_ip_str']), FILTER_VALIDATE_IP) : '' );
    
    $caller_data['comment'] = ( isset($_POST['comment']) ? 
            sanitize_text_field(trim($_POST['comment']))  : '' );
    $caller_data['referrer'] = ( isset($_POST['referrer']) ? 
            sanitize_text_field(trim($_POST['referrer'], '/ '))  : '' );
    $caller_data['hostname'] = ( isset($_POST['hostname']) ? 
            sanitize_text_field(trim($_POST['hostname'], '/ '))  : '' );

    $caller_data['suspicious'] = ( isset($_POST['suspicious']) ? True : False );
    $caller_data['malicious'] = ( isset($_POST['malicious']) ? True : False );
    $caller_data['whitelist'] = ( isset($_POST['whitelist']) ? True : False );
    $caller_data['uploaded'] = ( isset($_POST['uploaded']) ? True : False );
    

    
    if($caller_data['malicious']) $caller_data['suspicious'] = True; // malicious is stronger

    if($caller_data['whitelist'] && $caller_data['malicious'])   // whitelist cant be malicious, take off the flag
    {
        // redirect to the same form with error message
        wp_safe_redirect(add_query_arg( array(  'page' => 'nomore404', 
                                                'tab' => '2',
                                                'mode' => (is_numeric($_POST['caller_id']) ? 'edit' : 'new' ), 
                                                'id' => $caller_id,
                                                'err' => '5'), 
                                        admin_url('options-general.php')));
        exit;        
    }    
    
    //session_start(); // start session to save UI objects and for error handling
    //$_SESSION['caller_data'] = $caller_data; // save caller data in session
    setcookie( 'caller_ip_str', $caller_data['caller_ip_str'], time() + 600); // save IP for error handling
    //NoMore404_Model_Static_Class::good_error_log('Saving ip to cookie:', $caller_data['caller_ip_str']);
    
    if(False === $caller_data['caller_ip_str']){ // ip is wrong
            // redirect to the same form with error message
            $caller_data['caller_ip_str'] = sanitize_text_field($_POST['caller_ip_str']); // recover text of ip that was wrong to recover in form, so user can edit
            //$_SESSION['caller_data'] = $caller_data; // save caller data in session
            setcookie( 'caller_ip_str', $caller_data['caller_ip_str'], time()+600); // save IP for error handling
            
            wp_safe_redirect(add_query_arg(array('page' => 'nomore404', 'tab' => '2', 'mode' => 'new', 'err' => '4'), admin_url('options-general.php')));
            exit;        
    }
    // ip is good here, can convert now
    $caller_data['caller_ip'] = ( isset($_POST['caller_ip_str']) ? inet_pton($caller_data['caller_ip_str']) : '' );
    
    // Call insert or update method based on value of hidden caller_id field
    if (isset($_POST['caller_id']) && 'new' == $_POST['caller_id']) {        
        $ttt = NoMore404_Model_Static_Class::InsertCaller($caller_data);

        if (False == $ttt || 0 == $ttt) {
            //session_write_close(); //now close the session to avoid hold up
            // redirect to the same form with error message
            wp_safe_redirect(add_query_arg(array('page' => 'nomore404', 'tab' => '2', 'mode' => 'new', 'err' => '3'), admin_url('options-general.php')));
            exit;
        }
    } elseif (isset($_POST['caller_id']) && is_numeric($_POST['caller_id'])) {  // update is required
        $callerid = (int) $_POST['caller_id'];
        
        // fix up uploaded to be always false on update if one of flags are of interest SMW
        if($caller_data['suspicious'] || $caller_data['malicious'] || $caller_data['whitelist'])
            $caller_data['uploaded'] = False;
        
        $ttt = NoMore404_Model_Static_Class::UpdateCaller($caller_data, $callerid);
        

        if ($ttt === False) {
            //session_write_close(); //now close the session to avoid hold up            
            // redirect to the same form with error message
            wp_safe_redirect(add_query_arg(array('page' => 'nomore404', 'mode' => 'edit',
                                                'id' => $callerid, 'tab' => '2',  'err' => '3'), 
                                            admin_url('options-general.php')));
            exit;
        }
    }
    //session_write_close(); //now close the session to avoid hold up
    // Redirect the page to the Caller list form
    wp_safe_redirect(add_query_arg(array('page' => 'nomore404', 'tab' => '2', 'err' => '100'), 
                                    admin_url('options-general.php')));
    exit;
}

// processing uri editing form
function process_nomore404_uri() {
    // Check if user has proper security level
    if (!current_user_can('manage_options')) wp_die('Not allowed');
    // Check if nonce field is present for security
    check_admin_referer('nomore404_add_edit_uri');
    
    if (isset($_POST['btnCancel'])) {
            wp_safe_redirect(add_query_arg(array('page' => 'nomore404', 'tab' => '1'), admin_url('options-general.php')));
            exit;    
    }
    
    $uri_id = ( isset($_POST['uri_id']) ? (int) $_POST['uri_id'] : '' );

    // Place all user submitted values in an array (or empty strings if no value was sent)
    $uri_data = array();
    $uri_data['uri_text'] = ( isset($_POST['uri_text']) ? 
            sanitize_text_field(trim($_POST['uri_text'], '/ '))  : '' );
    $uri_data['uri_redirect_to'] = ( isset($_POST['uri_redirect_to']) ? 
            sanitize_text_field(trim($_POST['uri_redirect_to'], '/ ')) : '' );
    $uri_data['comment'] = ( isset($_POST['comment']) ? 
            sanitize_text_field(trim($_POST['comment'])) : '' );    
    $uri_data['use_redirection'] = ( isset($_POST['use_redirection']) ? True : False );
    $uri_data['suspicious'] = ( isset($_POST['suspicious']) ? True : False );
    $uri_data['malicious'] = ( isset($_POST['malicious']) ? True : False );
    $uri_data['whitelist'] = ( isset($_POST['whitelist']) ? True : False );
    $uri_data['uploaded'] = ( isset($_POST['uploaded']) ? True : False );
    
    //session_start(); // start session to save UI objects and for error handling
    //$_SESSION['uri_data'] = $uri_data; // save uri data in session
    //session_write_close(); //now close the session to avoid hold up
    setcookie( 'uri_text', $uri_data['uri_text'], time()+600); // save uri for error handling
    //NoMore404_Model_Static_Class::good_error_log('Saving uri to cookie:', $uri_data['uri_text']);
    
    if($uri_data['whitelist'] && $uri_data['malicious'])   // whitelist cant be malicious, take off the flag
    {
        // redirect to the same form with error message
        wp_safe_redirect(add_query_arg( array(   'page' => 'nomore404', 
                                                'tab' => '1',
                                                'mode' => (is_numeric($_POST['uri_id']) ? 'edit' : 'new' ), 
                                                'id' => $uri_id,
                                                'err' => '5'), 
                                        admin_url('options-general.php')));
        exit;        
    }
    
    if($uri_data['malicious']) $uri_data['suspicious'] = True;
    
    // Call insert or update method based on value of hidden uri_id field
    if (isset($_POST['uri_id']) && 'new' == $_POST['uri_id'] ) {
        $ttt = NoMore404_Model_Static_Class::InsertURI($uri_data);
        if (False === $ttt || 0 === $ttt) { // insert did not work
            // redirect to the same form with error message
            wp_safe_redirect(add_query_arg(array('page' => 'nomore404', 'tab' => '1','mode' => 'new', 'err' => '1'), admin_url('options-general.php')));
            exit;
        }
    } elseif (isset($_POST['uri_id']) && is_numeric($_POST['uri_id'])) { // update is required
        // fix up uploaded to be always false on update if one of flags are of interest SMW
        if($uri_data['suspicious'] || $uri_data['malicious'] || $uri_data['whitelist'])
            $uri_data['uploaded'] = False;
        
        $ttt = NoMore404_Model_Static_Class::UpdateURI($uri_data, $uri_id);
        if ($ttt === False) { // update did not work well
            // redirect to the same form with error message
            wp_safe_redirect(add_query_arg(array('page' => 'nomore404', 'mode' => 'edit','tab' => '1',
                                                 'id' => $uri_id, 'err' => '1'), 
                                            admin_url('options-general.php')));
            exit;
        }
        // update worked
        if($uri_data['malicious']){ // it was malicious, need to mark all callers as malicious
            NoMore404_Model_Static_Class::MarkCallersMalicious($uri_id);
        }
    }
// Redirect the page to the UEI list form
    wp_safe_redirect(add_query_arg(array('page' => 'nomore404', 'tab' => '1', 'err' => '100'), admin_url('options-general.php')));
    exit;
}

// this function imports all data from another plugin "404 to 301 - Redirect, Log and Notify 404 Errors" by  Joel James v 3.0.5
function import_nomore404_from_404_to_301() {
// Check that user has proper security level
    if (!current_user_can('manage_options')) wp_die('Not allowed');
    // Check if nonce field is present
    check_admin_referer('nomore404_import_from_404_to_301');
    
    include_once plugin_dir_path(__FILE__) . 'includes/nomore404-import.php'; // import class;
            
    //NoMore404_Model_Static_Class::emptyTables(); // DANGER DANGER DANGER - delete all my data for debug    

    $ImportFrom404301 = new NoMore404_Import(); // new import object
    
    foreach ($ImportFrom404301->GetAllTheRecords() as $row) { // going through all the records in 404_to_301 table
        $uri_data = array();

        set_time_limit(29); // resetting execution time
        
        // clearing leading and trailing / as they are not important for storing in db
        $row['url'] = trim( trim($row['url']), '//' );
        $row['redirect'] = trim( trim($row['redirect']), '//' );

        $uri_data['uri_text'] = $row['url'];
        $uri_data['uri_redirect_to'] = $row['redirect'];
        $uri_data['counter'] = 1;
        $uri_data['use_redirection'] = $row['status'];
        $uri_data['date_created'] = $row['date'];
        $uri_data['date_last_used'] = $row['date'];

        if ($uri_data['uri_text'] == ''){
            continue; // skipping empty uris
        }
            
        // *** processing URI first ***
        //first lets check if this uri_text is already in the db        
        $uri_current_record = NoMore404_Model_Static_Class::getURI('', $uri_data['uri_text']);

        if($uri_current_record === False) continue; // error searching, no point to keep going for this record
        
        if ($uri_current_record !== NULL) { // URI record exists, 
            // need to update counter and date last used for URI
            $uri_current_record['counter'] = (int) ($uri_current_record['counter']) + 1;
            $d1 = new DateTime($uri_current_record['date_last_used']);
            $d2 = new DateTime($row['date']);
            if ($d1 < $d2){$uri_current_record['date_last_used'] = $d2->format('Y-m-d H:i:s');}
            $uriid = $uri_current_record['uri_id'];
            $uriupdatestatus = NoMore404_Model_Static_Class::UpdateURI($uri_current_record, $uriid);
        } else { // URI record does not exist, can insert now
            $uriid = NoMore404_Model_Static_Class::InsertURI($uri_data);
            if($uriid === False)                continue; // insert did not work
        };
        // at the end of it we should have uriid in the variable

        // *** processing Caller second ***
        //first lets check if this caller IP is in the db - this is independend of URI         
        $callerip = inet_pton(trim($row['ip']));
        if ($callerip === False) {
            continue; // the address is wrong, no point to store caller or uri-caller relationship
        }

        // check if this caller already exist
        $caller_current_record = NoMore404_Model_Static_Class::getCaller('', $callerip);
        
        if ($caller_current_record === False) continue; // error searching caller, no point to go on

        if ($caller_current_record === NULL) { // caller record does not exist, can insert now
            // add caller first, as it is needed for call/uri table
            $caller_data = array();
            $caller_data['caller_ip'] = $callerip;
            $caller_data['caller_ip_str'] = trim($row['ip']); // debug field - remove for prod
            $caller_data['referrer'] = trim($row['ref']);
            $caller_data['date_created'] = $row['date'];
            
            $callerid = NoMore404_Model_Static_Class::InsertCaller($caller_data);
            if($callerid === False)                continue; // insert did not work
            
        } else { // we have caller id in current record
            $callerid = $caller_current_record['caller_id'];
        };
        // at this point callerid is populated
        
        // *** processing uri - caller connection ***
        if(is_numeric($callerid) && is_numeric($uriid)){ // process only if both caller and uri are present
            // search for uriid, callerid (primary key) in uri_caller table
            $uri_caller_record = NoMore404_Model_Static_Class::getURICaller($uriid, $callerid);
            
            if ($uri_caller_record === False) continue; // error searching uri-caller, no point to go on
            
            if ($uri_caller_record !== NULL) { // found a record in uri_caller
                // increase counter
                $uri_caller_record['counter'] = (int) ($uri_caller_record['counter'] ) + 1;

                // update date to later date if it is older
                $d1 = new DateTime($uri_caller_record['date_last_used']);
                $d1 = new DateTime($row['date']);
                if ($d1 < $d2) {
                    $uri_caller_record['date_last_used'] = $d2->format('Y-m-d H:i:s');
                }
                $uri_caller = NoMore404_Model_Static_Class::UpdateURICaller($uri_caller_record, $uriid, $callerid);
            } else { // uri_caller record not found, creating new one
                $uri_caller_record = array();
                $uri_caller_record['uri_id'] = $uriid;
                $uri_caller_record['caller_id'] = $callerid;
                $uri_caller_record['counter'] = 1;
                $uri_caller_record['date_created'] = $row['date'];
                $uri_caller_record['date_last_used'] = $row['date'];
                $uri_caller = NoMore404_Model_Static_Class::InsertURICaller($uri_caller_record);
            };            
        };
       
    };
    
    // import successful 
    wp_safe_redirect(add_query_arg(array('page' => 'nomore404', 'err' => '101'), admin_url('options-general.php')));
    exit;
}


// display custom admin notice based on err
// use on of those notice-error, notice-warning, notice-success, or notice-info
function nomore404_add_settings_errors() {
//    session_start(); // start session to save UI objects and for error handling
    //NoMore404_Model_Static_Class::good_error_log('Cookies:', $_COOKIE);

    if (isset($_GET['err']) && ('1' == $_GET['err']) ) {
//        if (isset($_SESSION['uri_data']['uri_text'])) $uri_text = $_SESSION['uri_data']['uri_text'];
        if (isset($_COOKIE['uri_text']))
            $uri_text = sanitize_text_field($_COOKIE['uri_text']);
        else
            $uri_text = 'that URI';
        ?>       
        <div class="notice notice-error is-dismissible">
            <p><?php _e('Error in saving the URI, most likely "' . $uri_text . '" already exists.'); ?></p>
        </div>   
        <?php
    };
    if (isset($_GET['err']) && ('3' == $_GET['err']) ) {
     //   if (isset($_SESSION['caller_data']['caller_ip_str'])) $caller_ip_str = $_SESSION['caller_data']['caller_ip_str'];
        if (isset($_COOKIE['caller_ip_str']))
            $caller_ip_str = sanitize_text_field($_COOKIE['caller_ip_str']);
        else
            $caller_ip_str = 'that IP';
        ?>       
        <div class="notice notice-error is-dismissible">
            <p><?php _e('Error in saving the Caller, most likely "' . $caller_ip_str . '" already exists.'); ?></p>
        </div>   
        <?php
    };    
    if (isset($_GET['err']) && ('4' == $_GET['err']) ) {
        if (isset($_COOKIE['caller_ip_str']))
            $caller_ip_str = sanitize_text_field($_COOKIE['caller_ip_str']);
        else
            $caller_ip_str = 'that IP';
        ?>       
        <div class="notice notice-error is-dismissible">
            <p><?php _e('Error in saving the Caller, IP address "' . $caller_ip_str . '" is wrong.'); ?></p>
        </div>   
        <?php
    };
    if (isset($_GET['err']) && ('5' == $_GET['err']) ) {
        ?> 
        <div class="notice notice-error is-dismissible">
            <p><?php _e('Error in saving the form, whitelisted can\'t be malicious! Uncheck either malicious or whitelist checkbox.'); ?></p>
        </div>   
        <?php
    };    
    if (isset($_GET['err']) && ('2' == $_GET['err'] )) :
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('Options did not save, contact support'); ?></p>
        </div>
        <?php
    endif;
    if (isset($_GET['err']) && ('6' == $_GET['err']) ) { // upload did not work
        ?> 
        <div class="notice notice-error is-dismissible">
            <p><?php _e('Upload did not work, possibly nothing to upload.'); ?></p>
        </div>   
        <?php
    };      
    if (isset($_GET['err']) && '100' == $_GET['err']) :
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Saved'); ?></p>
        </div>
        <?php
    endif;
    if (isset($_GET['err']) && '101' == $_GET['err']) :
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Import from the plugin "404 to 301" is finished'); ?></p>
        </div>
        <?php
    endif;
    if (isset($_GET['err']) && '102' == $_GET['err']) :
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Upload of your data is complete'); ?></p>
        </div>
        <?php
    endif;
    //session_write_close(); //now close the session to avoid hold up
}
add_action('admin_notices', 'nomore404_add_settings_errors');

// additional links in plugins list
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'nomore404_add_plugin_page_settings_link');
function nomore404_add_plugin_page_settings_link( array $actions ) {
	return array_merge( array(
                'Settings' => '<a href="' . 
                admin_url( 'options-general.php?page=nomore404&tab=3' ) .
		'">' . __('Settings') . '</a>'
                ), $actions);
}

// all dashboard widget setup
include_once plugin_dir_path(__FILE__) . 'includes/nomore404-dashboard-widget.php';
?>