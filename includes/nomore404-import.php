<?php
// If this file is called directly, stop execution.
defined('ABSPATH') or exit;
// this class does all data and database manipulations for third party data input from other plugins
// currently only one plugin for import data from 404_to_301
Class NoMore404_Import {
    private $table4import;
    
    public function __construct() {
        global $wpdb;
        
        $this->table4import = $wpdb->get_blog_prefix() . '404_to_301';
    }
    
    public function GetAllTheRecords(){
        global $wpdb;
        $query = 'SELECT * FROM ' . $this->table4import;
        $query .= ' ORDER BY `' . $this->table4import . '`.`id`';
    //    $query .= ' LIMIT 200'; // debug mode
        return $wpdb->get_results( $query, ARRAY_A );
  /*
  `id`  just index
  `date` date created
  `url` uri to redirect
  `ref` request came from this referrer
  `ip` ip of the request
  `ua` caller browser
  `redirect` where to redirect to
  `options` options for redirection
  `status` status, enabled or not
   * 
   `id` bigint(20) NOT NULL,
  `date` datetime NOT NULL,
  `url` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `ref` varchar(512) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ip` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ua` varchar(512) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `redirect` varchar(512) COLLATE utf8_unicode_ci DEFAULT '',
  `options` longtext COLLATE utf8_unicode_ci,
  `status` bigint(20) NOT NULL DEFAULT '1'
   */
    }
}
?>