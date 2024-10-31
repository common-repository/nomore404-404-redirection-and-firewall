<?php
/* dashboard widget functions */

// If this file is called directly, stop execution.
defined('ABSPATH') or exit;

// adding admin widget
add_action('wp_dashboard_setup', 'nomore404_dashboard_widget'); 
add_action('wp_dashboard_setup', 'nomore404_dashboard_widget_control');

function nomore404_dashboard_widget_control(){
    //NoMore404_Model_Static_Class::var_error_log('Request:', $_REQUEST);
    if (isset($_REQUEST['btnSubmit'])){ // submit clicked
        // erasing counters
        $objCounters = new NoMore404_counters();
        $objCounters->ResetAllCounters();
    }
}

function nomore404_dashboard_widget() {
    wp_add_dashboard_widget('nomore404_help', 'Nomore404 404 redirection and firewall', 'nomore404_dashboard_function');

    // Globalize the metaboxes array, this holds all the widgets for wp-admin.
    global $wp_meta_boxes;
     
    // Get the regular dashboard widgets array 
    // (which already has our new widget but appended at the end).
    $default_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
     
    // Backup and delete our new dashboard widget from the end of the array.
    $widget_backup = $wp_meta_boxes['dashboard']['normal']['core']['nomore404_help'];
    unset( $default_dashboard['nomore404_help'] );
  
    // Now we just add your widget back in. on the right side
    $wp_meta_boxes['dashboard']['side']['core']['nomore404_help'] = $widget_backup;
}

function nomore404_dashboard_function() {
    $objCounters = new NoMore404_counters();
    
    $output  = '<form method = "post">' . PHP_EOL;
    
    $output .= '<h2> How Nomore404 served you:</h2>' . PHP_EOL;
    
    $output .= '<table>' . PHP_EOL; 
    
    $output .= '<tr>' . PHP_EOL;
    $output .= '<td style="width: 300px">Date count started</td>' . PHP_EOL;
    $output .= '<td>' . esc_attr($objCounters->dateCountStarted) . '</td>' . PHP_EOL;
    $output .= '</tr>' . PHP_EOL;

    $output .= '<tr>' . PHP_EOL;
    $output .= '<td style="width: 300px">Number of blocked bad URLs</td>' . PHP_EOL;
    $output .= '<td>' . esc_attr($objCounters->countURIblocked) . '</td>' . PHP_EOL;
    $output .= '</tr>' . PHP_EOL;

    $output .= '<tr>' . PHP_EOL;
    $output .= '<td style="width: 300px">Number of blocked bad Callers/Hosts</td>' . PHP_EOL;
    $output .= '<td>' . esc_attr($objCounters->countCallersBlocked) . '</td>' . PHP_EOL;
    $output .= '</tr>' . PHP_EOL;    
    
    $output .= '<tr>' . PHP_EOL;
    $output .= '<td style="width: 300px">Number of Callers the system self-learned as malicious and marked them</td>' . PHP_EOL;
    $output .= '<td>' . esc_attr($objCounters->countCallersMarkedMalicious) . '</td>' . PHP_EOL;
    $output .= '</tr>' . PHP_EOL; 
    
    $output .= '<tr>' . PHP_EOL;
    $output .= '<td style="width: 300px">Number of URLs/URIs redirected to default, and saved you from 404 error</td>' . PHP_EOL;
    $output .= '<td>' . esc_attr($objCounters->countURIredirected) . '</td>' . PHP_EOL;
    $output .= '</tr>' . PHP_EOL; 
    
    $output .= '<tr>' . PHP_EOL;
    $output .= '<td style="width: 300px">Number of URLs/URIs redirected to custom URL</td>' . PHP_EOL;
    $output .= '<td>' . esc_attr($objCounters->countURIredirected2Custom) . '</td>' . PHP_EOL;
    $output .= '</tr>' . PHP_EOL; 
    
    $output .= '<tr>' . PHP_EOL;
    $output .= '<td style="width: 300px"><h2>Quick access links: </h2></td>' . PHP_EOL;
    $output .= '</tr>' . PHP_EOL; 
    
    $output .= '<tr>' . PHP_EOL;
    $output .=  '<td style="width: 300px">'
            .   '<a href="'
            .   esc_url( add_query_arg( array('page' => 'nomore404', 'tab' => '1'), admin_url('options-general.php')))
            .   '">Redirection/URIs</a>'. PHP_EOL;
    $output .= '</tr>' . PHP_EOL; 
    
    $output .= '<tr>' . PHP_EOL;
    $output .=  '<td style="width: 300px">'
            .   '<a href="'
            .   esc_url( add_query_arg( array('page' => 'nomore404', 'tab' => '2'), admin_url('options-general.php')))
            .   '">Callers/hosts</a>'. PHP_EOL;
    $output .= '</tr>' . PHP_EOL; 
    
    $output .= '<tr>' . PHP_EOL;
    $output .=  '<td style="width: 300px">'
            .   '<a href="'
            .   esc_url( add_query_arg( array('page' => 'nomore404', 'tab' => '3'), admin_url('options-general.php')))
            .   '">Settings</a>'. PHP_EOL;
    $output .= '</tr>' . PHP_EOL; 

    $output .= '<tr>' . PHP_EOL;
    $output .=  '<td style="width: 300px">'
            .   '<a href="'
            .   esc_url('https://devoutpro.com/forums/forum/nomore404-forum/')
            .   '">Support forum</a>'. PHP_EOL;
    $output .= '</tr>' . PHP_EOL; 

    
    $output .= '</table>' . PHP_EOL;
    
    //$output .= submit_button( 'Reset all counters', '', 'btnSubmit', False);
    //  <input type="submit" name="btnSubmit" id="btnSubmit" class="button" value="Reset all counters">      
    $output .= "<input type='submit' name='btnSubmit' id='btnSubmit' value='Reset all counters' />" . PHP_EOL;
    $output .= '</form>';
            
    echo $output;
}
?>