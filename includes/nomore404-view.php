<?php
// If this file is called directly, stop execution.
defined('ABSPATH') or exit;

class NoMore404_View {
    // uploads/downloads extracts
    public function ShowToolsForm(){
            ?>
            <!--Form to load uris from 404 to 301 plugin -->
            <form method = "post" action = "<?php echo admin_url('admin-post.php'); ?>"
                  accept-charset=""enctype = "multipart/form-data">
                <input type = "hidden" name = "action" value = "nomore404_upload" />
                <!--Adding security through hidden referrer field -->
            <?php wp_nonce_field('nomore404_upload_nonce'); ?>
                    <h3>Tools page, uploads, downloads</h3>
            <?php 
                submit_button( 'Upload new info', 'primary', 'btnUpload', False);
            ?>
            </form>       
            <?php        
    }
    
    // import form
    public function ShowImportForm(){
            ?>
            <!--Form to load uris from 404 to 301 plugin -->
            <form method = "post" action = "<?php echo admin_url('admin-post.php'); ?>"
                  accept-charset=""enctype = "multipart/form-data">
                <input type = "hidden" name = "action" value = "import_nomore404_from_404_to_301" />
                <!--Adding security through hidden referrer field -->
            <?php wp_nonce_field('nomore404_import_from_404_to_301'); ?>
                    <h3>Import Redirects from "404 to 301 - Redirect, Log and Notify 404 Errors By Joel James" plugin</h3>
            <?php 
                submit_button( 'Import', 'primary', 'btnImport', False);
            ?>
            </form>       
            <?php
    }
    
    // shows tabs in config page with current tab showing
   public function ShowTabs($curtab = 1){
        $output = '<h2 class="nav-tab-wrapper">' . PHP_EOL 
                 .'<a href="'
                 . esc_url( add_query_arg( array('page' => 'nomore404', 'tab' => '1'), admin_url('options-general.php')))
                 .'" class="nav-tab'. ($curtab === 1 ? ' nav-tab-active' : '') 
                 .'">Redirection</a>'. PHP_EOL
                
                 .'<a href="'
                 . esc_url( add_query_arg( array('page' => 'nomore404', 'tab' => '2'), admin_url('options-general.php')))
                 .'" class="nav-tab'. ($curtab === 2 ? ' nav-tab-active' : '') 
                 .'">Callers</a>'. PHP_EOL
                 
                 .'<a href="'
                 . esc_url( add_query_arg( array('page' => 'nomore404', 'tab' => '3'), admin_url('options-general.php')))
                 .'" class="nav-tab'. ($curtab === 3 ? ' nav-tab-active' : '') 
                 .'">Settings</a>'. PHP_EOL
                
                 .'<a href="'
                 . esc_url( add_query_arg( array('page' => 'nomore404', 'tab' => '4'), admin_url('options-general.php')))
                 .'" class="nav-tab'. ($curtab === 4 ? ' nav-tab-active' : '') 
                 .'">Pro version</a>'. PHP_EOL
                 
                 .'<a href="'
                 . esc_url( add_query_arg( array('page' => 'nomore404', 'tab' => '5'), admin_url('options-general.php')))
                 .'" class="nav-tab'. ($curtab === 5 ? ' nav-tab-active' : '') 
                 .'">Import</a>'. PHP_EOL
                 
                 .'<a href="'
                 . esc_url( add_query_arg( array('page' => 'nomore404', 'tab' => '6'), admin_url('options-general.php')))
                 .'" class="nav-tab'. ($curtab === 6 ? ' nav-tab-active' : '') 
                 .'">Help</a>'. PHP_EOL

                 .'<a href="'
                 . esc_url( add_query_arg( array('page' => 'nomore404', 'tab' => '7'), admin_url('options-general.php')))
                 .'" class="nav-tab'. ($curtab === 7 ? ' nav-tab-active' : '') 
                 .'">Tools</a>'. PHP_EOL
                
                .'</h2>'. PHP_EOL;       
        echo $output;
   }
   
    public function ShowHelp(){
        $output = '';
        $output .= '
                <body>
                    <div>
                    <h2>Welcome to the help page of NoMore404!</h2>';
        $output .= 'Plugin version ' . NOMORE404VERSION . ', DB version ' . NOMORE404DBVERSION; 

        $output .= '<article class="single-page-article clr">
                    <div class="entry clr" itemprop="text">
                    <p>NoMore404 is a free WordPress plugin for redirection of 404 pages and simple firewall to block malicious hosts and URLs.</p>
                    <p>This plug-in is written by ILYA POLYAKOV.  One of our clients needed redirection plugin with easy to use and good user interface, so he decided to write a free plug-in and share it with all WordPress community.</p>
                    <p>The plugin has 2 functions:</p>
                    <p>1) Redirects 404 requests using 301 type of redirection to either default page (which you can setup in '; 
        $output .= '<a href="' . esc_url( add_query_arg( array('page' => 'nomore404', 'tab' => '3'), 
                                                admin_url('options-general.php')));
        $output .= '">Settings</a>) or you can specify exact target page to redirect to in '; 
        $output .= '<a href="' . esc_url( add_query_arg( array('page' => 'nomore404', 'tab' => '1'), 
                                                admin_url('options-general.php')));
        $output .= '">Redirection tab</a>.
                    </p>
                    <p>2) Blocks malicious callers (hosts) based on IP address, for that you need to mark as malicious in ';
        $output .= '<a href="' . esc_url( add_query_arg( array('page' => 'nomore404', 'tab' => '2'), 
                                                admin_url('options-general.php')));
        $output .= '">Callers tab</a>.'; 
        $output .= '</p>
                    <p>It is up to you to mark hosts as suspicious and/or malicious. To edit caller, you need to click on IP address of that caller. To edit redirection, you need to click on URI of that redirection.
                    </p>
                    <p>You can use bulk actions to mark malicious or suspicious a number of rows at the same time. Note, if you mark URL as malicious, all its callers are marked malicious as well. 
                    </p>
                    <p>If you want to help others – you can share information on suspicious/malicious hosts with our black list, you can enable this in ';
        $output .= '<a href="' . esc_url( add_query_arg( array('page' => 'nomore404', 'tab' => '3'), 
                                                admin_url('options-general.php')));
        $output .= '">Settings</a></p>';

        $output .= '<p>';
        $output .= '<a href="' . esc_url( add_query_arg( array('page' => 'nomore404', 'tab' => '5'), 
                                                admin_url('options-general.php')));
        $output .= '">Import tab</a>';

        $output .= ' can import data from 404 to 301 plug-in, if you have been using it on your site, just import its data, and you can disable 404 to 301 after that. Our plugin will take over all redirects based on that plug-in settings. </p>
                    <p>If you would like to discuss anything related to the plugin use or have some questions, please come to the <a href="https://devoutpro.com/forums/topic/faq-forum-any-use-questions-should-be-posted-here/">forum</a>.</p>
                    <p>If you would like to support free plugin development, please donate below.</p>

                        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                        <input type="hidden" name="cmd" value="_s-xclick">
                        <input type="hidden" name="hosted_button_id" value="25KGNF3ZDUN9E">
                        <input type="image" src="https://www.paypalobjects.com/en_AU/i/btn/btn_donateCC_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button">
                        <img alt="" border="0" src="https://www.paypal.com/en_AU/i/scr/pixel.gif" width="1" height="1">
                        </form>
                    </div> 

                </body>';
       echo $output;
   }

    public function ShowPRO(){
       $output = '';
       $output .= '
           <h1 class="page-header-title clr" itemprop="headline">NoMore404 PRO 404 Redirection and Firewall</h1>
              <h2>Current status of development:</h2>
            <body>
                <div>
                    <p>Plugin backend is in design phase at the moment, we are focusing on releasing free version of the plugin first. Tentative release date for Pro version is 4 May 2020.</p>
                    <p>You can always check the latest status of Nomore404 PRO development at <a href="https://devoutpro.com/nomore404-pro/">this </a>page</p>
                    <p>NoMore404 PRO version is under development and will offer extra security features (main one &#8211; is updates from the block list of malicious hosts and URLs, that will be maintained by our security experts) . </p>
                    <p>If you would like to ask for any additional features or ask us a question about PRO version, please post in our <a href="https://devoutpro.com/forums/topic/pro-version/">forum</a></p>
                </div>
            </body>';
       echo $output;
   }
   
   
    public function showURIEditForm($uri_data){        
        $output = '<table>' . PHP_EOL;   
        $output .= '<tr>' . PHP_EOL;
        $output .= '<td style="width: 300px">Redirect URI</td>' . PHP_EOL;
        $output .= '<td><input type="text" name="uri_text" size="260"';
        $output .= ' value="' . esc_attr($uri_data['uri_text']) . '"/>' . PHP_EOL;
        $output .= '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
        
        $output .= '<tr>' . PHP_EOL;
        $output .= '<td style="width: 300px">Redirect to</td>' . PHP_EOL;
        $output .= '<td><input type="text" name="uri_redirect_to" size="260"';
        $output .= ' value="' . esc_attr($uri_data['uri_redirect_to']) . '"/>' . PHP_EOL;
        $output .= '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
        $output .= '<tr>' . PHP_EOL;
        
        $output .= '<tr>' . PHP_EOL;
        $output .= '<td style="width: 300px">Comment</td>' . PHP_EOL;
        $output .= '<td><input type="text" name="comment" size="100"';
        $output .= ' value="' . esc_attr($uri_data['comment']) . '"/>' . PHP_EOL;
        $output .= '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
        $output .= '<tr>' . PHP_EOL;        
        
        $output .= '<td>Enabled</td>' . PHP_EOL;
        $output .= '<td><input type="checkbox" name="use_redirection" value="Enabled" ' 
                . checked($uri_data['use_redirection'], true, false) . '>' . PHP_EOL;
        $output .= '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
        $output .= '<tr>' . PHP_EOL;
        $output .= '<td>Counter</td>' . PHP_EOL;
        $output .= '<td>' . esc_attr($uri_data['counter']) . '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
        $output .= '<tr>' . PHP_EOL;
        $output .= '<td>Date created</td>' . PHP_EOL;
        $output .= '<td>' . esc_attr($uri_data['date_created']) . '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
        $output .= '<tr>' . PHP_EOL;
        $output .= '<td>Date Last Used</td>' . PHP_EOL;
        $output .= '<td>' . esc_attr($uri_data['date_last_used']) . '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
        $output .= '<tr>' . PHP_EOL;
        $output .= '<td>Suspicious</td>' . PHP_EOL;
        $output .= '<td><input type="checkbox" name="suspicious" value="Enabled" '
                . checked($uri_data['suspicious'], true, false) . '>' . PHP_EOL;
        $output .= '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
        
        $output .= '<tr>' . PHP_EOL;
        $output .= '<td>Malicious</td>' . PHP_EOL;
        $output .= '<td><input type="checkbox" name="malicious" value="Enabled" '
                . checked($uri_data['malicious'], true, false) . '>' . PHP_EOL;
        $output .= '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;        
        
        $output .= '<tr>' . PHP_EOL;
        $output .= '<td>Whitelist</td>' . PHP_EOL;
        $output .= '<td><input type="checkbox" name="whitelist" value="Enabled" '
                . checked($uri_data['whitelist'], true, false) . '>' . PHP_EOL;
        $output .= '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
        
        $output .= '<td>Date Uploaded</td>' . PHP_EOL;
        $output .= '<td>' . esc_attr($uri_data['date_uploaded']) . '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
        $output .= '<tr>' . PHP_EOL;

        $output .= '<td>Date Downloaded</td>' . PHP_EOL;
        $output .= '<td>' . esc_attr($uri_data['date_downloaded']) . '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
        $output .= '<tr>' . PHP_EOL;     
        
        $output .= '<tr>' . PHP_EOL;
        $output .= '<td>Uploaded</td>' . PHP_EOL;
        $output .= '<td><input type="checkbox" name="uploaded" value="Enabled" '
                . checked($uri_data['uploaded'], true, false) . '>' . PHP_EOL;
        $output .= '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL; 
        
        $output .= '</table>' . PHP_EOL;
        echo $output;
    }

    public function showCallerEditForm($caller_data){ 
        $output = '<table>' . PHP_EOL;   
        $output .= '<tr>' . PHP_EOL;
        $output .= '<td style="width: 300px">Caller IP</td>' . PHP_EOL;
        $output .= '<td><input type="text" name="caller_ip_str" size="39"';
        $output .= ' value="' . esc_attr($caller_data['caller_ip_str']) . '"/>' . PHP_EOL;
        $output .= '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;

        $output .= '<tr>' . PHP_EOL;
        $output .= '<td style="width: 300px">Comment</td>' . PHP_EOL;
        $output .= '<td><input type="text" name="comment" size="50"';
        $output .= ' value="' . esc_attr($caller_data['comment']) . '"/>' . PHP_EOL;
        $output .= '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;

        $output .= '<tr>' . PHP_EOL;
        $output .= '<td style="width: 300px">Caller/host name</td>' . PHP_EOL;
        $output .= '<td><input type="text" name="hostname" size="50"';
        $output .= ' value="' . esc_attr($caller_data['hostname']) . '"/>' . PHP_EOL;
        $output .= '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
        
        $output .= '<tr>' . PHP_EOL;
        $output .= '<td style="width: 300px">Referrer</td>' . PHP_EOL;
        $output .= '<td><input type="text" name="referrer" size="260"';
        $output .= ' value="' . esc_attr($caller_data['referrer']) . '"/>' . PHP_EOL;
        $output .= '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
        
        $output .= '<tr>' . PHP_EOL;
        $output .= '<td>Date created</td>' . PHP_EOL;
        $output .= '<td>' . esc_attr($caller_data['date_created']) . '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
        $output .= '<tr>' . PHP_EOL;
        
        $output .= '<td>Date Last Used</td>' . PHP_EOL;
        $output .= '<td>' . esc_attr($caller_data['date_last_used']) . '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
        $output .= '<tr>' . PHP_EOL;
        
        $output .= '<td>Counter</td>' . PHP_EOL;
        $output .= '<td>' . esc_attr($caller_data['counter']) . '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;        
        $output .= '<tr>' . PHP_EOL;
        
        $output .= '<td>Suspicious</td>' . PHP_EOL;
        $output .= '<td><input type="checkbox" name="suspicious" value="Enabled" '
                . checked($caller_data['suspicious'], true, false) . '>' . PHP_EOL;
        $output .= '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
        
        $output .= '<tr>' . PHP_EOL;
        $output .= '<td>Malicious</td>' . PHP_EOL;
        $output .= '<td><input type="checkbox" name="malicious" value="Enabled" '
                . checked($caller_data['malicious'], true, false) . '>' . PHP_EOL;
        $output .= '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;

        $output .= '<tr>' . PHP_EOL;
        $output .= '<td>Whitelist</td>' . PHP_EOL;
        $output .= '<td><input type="checkbox" name="whitelist" value="Enabled" '
                . checked($caller_data['whitelist'], true, false) . '>' . PHP_EOL;
        $output .= '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
        
        $output .= '<td>Date Uploaded</td>' . PHP_EOL;
        $output .= '<td>' . esc_attr($caller_data['date_uploaded']) . '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
        $output .= '<tr>' . PHP_EOL;

        $output .= '<td>Date Downloaded</td>' . PHP_EOL;
        $output .= '<td>' . esc_attr($caller_data['date_downloaded']) . '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
        $output .= '<tr>' . PHP_EOL;     
        
        $output .= '<tr>' . PHP_EOL;
        $output .= '<td>Uploaded</td>' . PHP_EOL;
        $output .= '<td><input type="checkbox" name="uploaded" value="Enabled" '
                . checked($caller_data['uploaded'], true, false) . '>' . PHP_EOL;
        $output .= '</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;        
        
        $output .= '</table>' . PHP_EOL;
        echo $output;
    }
   
}

class NoMore404_counters{
    public $dateCountStarted; // very beginning of the counters' start
    public $countURIblocked; // malicious 404 URIs blocked from being accessed
    public $countURIredirected; // default redirections
    public $countURIredirected2Custom; // customised redirections
    public $countCallersBlocked; // malicious hosts blocked from accessing your site
    public $countCallersMarkedMalicious; // system self learned that a caller/host is malicious and marked them thus
    
    public function __construct( $args = array() ) {
        // $dateCountStarted
        $opt = get_option('nomore404_dateCountStarted');
        if(false === $opt) add_option('nomore404_dateCountStarted', current_time('mysql', TRUE));
        else $this->dateCountStarted = $opt;
        // $countURIblocked
        $opt = get_option('nomore404_countURIblocked');
        if(false === $opt) add_option('nomore404_countURIblocked', 0);
        else $this->countURIblocked = $opt;  
        // $countURIredirected
        $opt = get_option('nomore404_countURIredirected');
        if(false === $opt) add_option('nomore404_countURIredirected', 0);
        else $this->countURIredirected = $opt;  
        // $countURIredirected2Custom
        $opt = get_option('nomore404_countURIredirected2Custom');
        if(false === $opt) add_option('nomore404_countURIredirected2Custom', 0);
        else $this->countURIredirected2Custom = $opt;  
        // $countCallersBlocked
        $opt = get_option('nomore404_countCallersBlocked');
        if(false === $opt) add_option('nomore404_countCallersBlocked', 0);
        else $this->countCallersBlocked = $opt; 
        // $countCallersMarkedMalicious
        $opt = get_option('nomore404_countCallersMarkedMalicious');
        if(false === $opt) add_option('nomore404_countCallersMarkedMalicious', 0);
        else $this->countCallersMarkedMalicious = $opt; 
    }

    public function ResetAllCounters() {
        update_option('nomore404_dateCountStarted', current_time('mysql', TRUE));
        update_option('nomore404_countURIblocked', 0);
        update_option('nomore404_countURIredirected', 0);
        update_option('nomore404_countURIredirected2Custom', 0);
        update_option('nomore404_countCallersBlocked', 0);
        update_option('nomore404_countCallersMarkedMalicious', 0);
        $this->dateCountStarted = current_time('mysql', TRUE);
        $this->countURIblocked = 0;
        $this->countURIredirected = 0;
        $this->countURIredirected2Custom = 0;
        $this->countCallersBlocked = 0;
        $this->countCallersMarkedMalicious = 0;
    }
    
    public function IncreaseCounter($counter = 'countURIblocked'){
        $opt = get_option("nomore404_$counter");
        if(false === $opt){ 
            add_option("nomore404_$counter", 1);
        }
        else{
            $this->{$counter} = $opt + 1;
            update_option("nomore404_$counter", $this->{$counter});
        }
    }
}

class NoMore404_Settings{
    public $options;
    
    public function __construct( $args = array() ) {
        $opt = get_option('nomore404_options');
        if(false === $opt){ // default setup
            $opt = array();
            $opt['enable_redirection'] = True; // plugin works
            $opt['default_redirect_to'] = ''; // default redirection is the home page of the site
            $opt['callers_per_page'] = 6; // number of callers in the table
            $opt['uris_per_page'] = 8; // number of uris in the table
            $opt['block_malicious'] = True; // by default blocks malicious hosts
            $opt['share_baddies'] = False; // enables the plugin to send info about malicious and suspects to our central db
            $opt['remove_options_on_uninstall'] = False; // removes all the data, when plugin is removed
            $opt['version'] = NOMORE404VERSION;
            $opt['dbversion'] = NOMORE404DBVERSION;
            add_option('nomore404_options', $opt);
        } else { // check that all keys are in the options and none are lost
            if(!array_key_exists('enable_redirection', $opt)) $opt['enable_redirection'] = True; // plugin works
            if(!array_key_exists('default_redirect_to', $opt)) $opt['default_redirect_to'] = ''; // default redirection is the home page of the site
            if(!array_key_exists('callers_per_page', $opt)) $opt['callers_per_page'] = 6; // number of callers in the table
            if(!array_key_exists('uris_per_page', $opt)) $opt['uris_per_page'] = 8; // number of uris in the table
            if(!array_key_exists('block_malicious', $opt)) $opt['block_malicious'] = True; // by default blocks malicious hosts
            if(!array_key_exists('share_baddies', $opt)) $opt['share_baddies'] = False; // enables the plugin to send info about malicious and suspects to our central db
            if(!array_key_exists('remove_options_on_uninstall', $opt)) $opt['remove_options_on_uninstall'] = False; // removes all the data, when plugin is removed
            if(!array_key_exists('version', $opt)) $opt['version'] = NOMORE404VERSION; // updating versions
            if(!array_key_exists('dbversion', $opt)) $opt['dbversion'] = NOMORE404DBVERSION;
            
            update_option('nomore404_options', $opt);
        }
        $this->options = $opt;
    }
    
    public function SetLatestVersions(){
        $this->options['dbversion'] = NOMORE404DBVERSION;
        $this->options['version'] = NOMORE404VERSION;
        update_option('nomore404_options', $this->options); 
    }
    
    public function UpdateOptions($updated_opt) {
        $opt = get_option('nomore404_options');
        $new_opt = array_merge($opt, $updated_opt); 
        $status = update_option('nomore404_options', $new_opt);
        if($status){ $this->options = $new_opt; }
        return $status;
    }
     public function GetOptions(){
        return $this->options; 
     }
     
    public function ShowEditForm(){
        ?>
        <!--Form to edit settings -->
        <form method = "post" action = "<?php echo admin_url('admin-post.php'); ?>">
            <input type = "hidden" name = "action" value = "nomore404_edit_settings" />
            <!--Adding security through hidden referrer field -->
        <?php wp_nonce_field('nomore404_edit_settings_nonce'); ?>
            <h3>Update global settings below</h3>
        <?php
        $this->EditTable();

        submit_button( 'Submit', 'primary', 'btnSubmit', False);
        submit_button( 'Cancel', 'primary', 'btnCancel', False);
        ?>
        </form> 
        <?php
    }
     
    public function EditTable(){
        $output = '<table class="wp-list-table widefat fixed striped settings">' . PHP_EOL;
        $output .= '<thead><tr>';
        $output .= '<th style="width: 10px">Setting</th>';
        $output .= '<th style="width: 100px">Value</th>';
        $output .= '<th style="width: 100px">Description</th>';
        $output .= '</tr></thead>' . PHP_EOL;
                
        $output .= '<tr>' . PHP_EOL;
        $output .= '<td>Redirection enabled</td>' . PHP_EOL;
        $output .= '<td><input type="checkbox" name="enable_redirection" value="Enabled" ' 
                . checked($this->options['enable_redirection'], true, false) . '>' . PHP_EOL;
        $output .= '</td>' . PHP_EOL;
        $output .= '<td>If you disable this, the plugin will stop redirecting traffic.</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
       
        $output .= '<tr>' . PHP_EOL;
        $output .= '<td style="width: 300px">Default redirect to URI/URL</td>' . PHP_EOL;
        $output .= '<td><textarea rows="5" cols="90" name="default_redirect_to">';
        $output .= esc_attr($this->options['default_redirect_to']) . '</textarea>' . PHP_EOL;
        $output .= '</td>' . PHP_EOL;
        $output .= '<td>Leave blank if you want to redirect by default to your home page, otherwise put link where default redirects will go.</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
     
        $output .= '<tr>' . PHP_EOL;
        $output .= '<td>Block malicious hosts/callers</td>' . PHP_EOL;
        $output .= '<td><input type="checkbox" name="block_malicious" value="Enabled" ' 
                . checked($this->options['block_malicious'], true, false) . '>' . PHP_EOL;
        $output .= '</td>' . PHP_EOL;
        $output .= '<td>The plugin will make your site to look broken to malicious hosts/callers. Recommended to enable this.</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
        
        $output .= '<tr>' . PHP_EOL;
        $output .= '<td>Share suspicious and malicious</td>' . PHP_EOL;
        $output .= '<td><input type="checkbox" name="share_baddies" value="Enabled" ' 
                . checked($this->options['share_baddies'], true, false) . '>' . PHP_EOL;
        $output .= '</td>' . PHP_EOL;
        $output .= '<td>Recomended to set to true, shares info about baddies with our backend.</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;

        $output .= '<tr>' . PHP_EOL;
        $output .= '<td>Remove all data on uninstall.</td>' . PHP_EOL;
        $output .= '<td><input type="checkbox" name="remove_options_on_uninstall" value="Enabled" ' 
                . checked($this->options['remove_options_on_uninstall'], true, false) . '>' . PHP_EOL;
        $output .= '</td>' . PHP_EOL;
        $output .= '<td>If set, all settings and data (including redirection and host) will be removed, if you uninstall this plugin. Default is keep all data.</td>' . PHP_EOL;
        $output .= '</tr>' . PHP_EOL;
        
        $output .= '</table>' . PHP_EOL;
        echo $output;        
    }
}
?>