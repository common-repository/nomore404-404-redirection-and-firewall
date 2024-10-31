<?php
/* model php, has all db works in it
 */
// If this file is called directly, stop execution.
defined('ABSPATH') or exit;

//initialise static model class for use everywhere;
if(!isset(NoMore404_Model_Static_Class::$uri_table_name)){
    global $wpdb;
    NoMore404_Model_Static_Class::$uri_table_name = $wpdb->get_blog_prefix() . 'nomore404_uri';
    NoMore404_Model_Static_Class::$caller_table_name = $wpdb->get_blog_prefix() . 'nomore404_caller';
    NoMore404_Model_Static_Class::$uri_caller_table_name = $wpdb->get_blog_prefix() . 'nomore404_uri_caller';    
}

// this class does all data and database manipulations
Class NoMore404_Model_Static_Class {

    public static $uri_table_name;    
    public static $caller_table_name;
    public static $uri_caller_table_name;
    
    private function __construct() {
    }
    
    public static function DeleteTables($prefix){
        global $wpdb;
		//$wpdb->query("SET FOREIGN_KEY_CHECKS = FALSE");
        $wpdb->query("DROP TABLE IF EXISTS " . $prefix . 'nomore404_uri_caller;');
        $wpdb->query("DROP TABLE IF EXISTS " . $prefix . 'nomore404_uri');
        $wpdb->query("DROP TABLE IF EXISTS " . $prefix . 'nomore404_caller');
		//$wpdb->query("SET FOREIGN_KEY_CHECKS = TRUE");
    }
    
    /**
     * Returns true if a database table column exists. Otherwise returns false.
     *
     * @param string $table_name Name of table we will check for column existence.
     * @param string $column_name Name of column we are checking for.
     *
     * @return boolean True if column exists. Else returns false.
     */
    public static function check_table_column_exists($table_name, $column_name) {

        global $wpdb;

        $column = $wpdb->get_results($wpdb->prepare(
                        "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ", DB_NAME, $table_name, $column_name
                ));

        if (!empty($column)) {
            return true;
        }

        return false;
    }

    public static function delete_col(&$array, $key)
    {
        // Check that the column ($key) to be deleted exists in all rows before attempting delete
        foreach ($array as &$row)   { if (!array_key_exists($key, $row)) { return false; } }
        foreach ($array as &$row)   { unset($row[$key]); }

        unset($row);

        return true;
    }
    
    // get Caller-URI table for upload
    public static  function getURICallerForUpload($markuploaded = false)  { 
        global $wpdb;
        
        $query = 'SELECT * FROM ' . self::$uri_caller_table_name;
        $query .= ' WHERE uploaded <> 1 AND (';
        $query .= 'uri_id IN ';
        $query .= '(SELECT uri_id FROM ' . self::$uri_table_name;
        $query .= ' WHERE whitelist = 1 OR malicious = 1 OR suspicious = 1) OR ';
        $query .= 'caller_id IN ';
        $query .= '(SELECT caller_id FROM ' . self::$caller_table_name;
        $query .= ' WHERE whitelist = 1 OR malicious = 1 OR suspicious = 1)';
        $query .= ' ) '; // closing bracked after first AND
        $query .= ' ORDER BY uri_id';

        $output = $wpdb->get_results($query, ARRAY_A);
        
        if($markuploaded){
            $query = 'UPDATE ' . self::$uri_caller_table_name . ' SET uploaded = 1, date_uploaded = UTC_TIMESTAMP()';
            $query .= ' WHERE uploaded <> 1 AND (';
            $query .= 'uri_id IN ';
            $query .= '(SELECT uri_id FROM ' . self::$uri_table_name;
            $query .= ' WHERE whitelist = 1 OR malicious = 1 OR suspicious = 1) OR ';
            $query .= 'caller_id IN ';
            $query .= '(SELECT caller_id FROM ' . self::$caller_table_name;
            $query .= ' WHERE whitelist = 1 OR malicious = 1 OR suspicious = 1)';
            $query .= ' ) '; // closing bracked after first AND
            $wpdb->query($query);
        }         
        
        return $output;
    }
    
    // get URI table for upload
    public static  function getURIsForUpload($markuploaded = false) { 
        global $wpdb;
        
        
        $query = 'SELECT * FROM ' . self::$uri_table_name;
        $query .= ' WHERE uploaded <> 1 AND (whitelist = 1 OR malicious = 1 OR suspicious = 1)';
        $query .= ' ORDER BY uri_id';

        $output = $wpdb->get_results($query, ARRAY_A);
        
        if($markuploaded){
            $query = 'UPDATE ' . self::$uri_table_name . ' SET uploaded = 1, date_uploaded = UTC_TIMESTAMP()';
            $query .= ' WHERE uploaded <> 1 AND (whitelist = 1 OR malicious = 1 OR suspicious = 1)';
            $wpdb->query($query);
        }
        
        return $output;
    }
    
    // get Callers table for upload
    public static  function getCallersForUpload($markuploaded = false) { 
        global $wpdb;
        
        $query = 'SELECT * FROM ' . self::$caller_table_name;
        $query .= ' WHERE uploaded <> 1 AND (whitelist = 1 OR malicious = 1 OR suspicious = 1)';
        $query .= ' ORDER BY caller_id';


        $output = $wpdb->get_results($query, ARRAY_A);
        self::delete_col($output, 'caller_ip'); // remove encoded ips
        
        if($markuploaded){
            $query = 'UPDATE ' . self::$caller_table_name . ' SET uploaded = 1, date_uploaded = UTC_TIMESTAMP()';
            $query .= ' WHERE uploaded <> 1 AND (whitelist = 1 OR malicious = 1 OR suspicious = 1)';
            $wpdb->query($query);
        }        
        
        return $output;
    }
    
    public static function emptyTables(){
        global $wpdb;
        $wpdb->query("DELETE FROM " . NoMore404_Model_Static_Class::$uri_caller_table_name);
        $wpdb->query("DELETE FROM " . NoMore404_Model_Static_Class::$uri_table_name);
        $wpdb->query("DELETE FROM " . NoMore404_Model_Static_Class::$caller_table_name);
    }
    public static  function MarkCallersSuspicious($uri_id = ''){
        global $wpdb;
        if('' != $uri_id && NULL != $uri_id){ // uri id is present
            $query  = ''; 
            $query .= 'UPDATE ' . self::$caller_table_name . ' c1 SET c1.suspicious = TRUE, c1.uploaded = FALSE ';
            $query .= 'WHERE caller_id IN ';         
            $query .= '(SELECT uc.caller_id FROM ' . self::$uri_caller_table_name . ' uc ';
            $query .= 'WHERE uc.uri_id = %d)';
            $cleanedsql = $wpdb->prepare($query , $uri_id);
            //NoMore404_Model_Static_Class::good_error_log("SQL mark suspicious:", $cleanedsql);
            $maliciouscallers = $wpdb->query($cleanedsql);            
        }
    }
    public static  function MarkCallersMalicious($uri_id = ''){
        global $wpdb;
        if('' != $uri_id && NULL != $uri_id){ // uri id is present
            $query  = ''; 
            $query .= 'UPDATE ' . self::$caller_table_name . ' c1 SET c1.malicious = TRUE, c1.suspicious = TRUE, c1.uploaded = FALSE ';
            $query .= 'WHERE caller_id IN ';         
            $query .= '(SELECT uc.caller_id FROM ' . self::$uri_caller_table_name . ' uc ';
            $query .= 'WHERE uc.uri_id = %d)';
            $cleanedsql = $wpdb->prepare($query , $uri_id);
            //NoMore404_Model_Static_Class::good_error_log("SQL mark malicious:", $cleanedsql);
            $maliciouscallers = $wpdb->query($cleanedsql);            
        }
    }
    public static  function getURICaller($uriid, $callerid) { 
        global $wpdb;
        try {
            if ($uriid != '' && $callerid != ''){ // use this function to search for caller_uri , if it is present
                $uri_caller_query = 'select * from ' . self::$uri_caller_table_name;
                $uri_caller_query .= ' where uri_id = %d and caller_id = %d';
                $uri_caller_data = $wpdb->get_row($wpdb->prepare($uri_caller_query, $uriid, $callerid), ARRAY_A);            
            } else { $uri_caller_data = FALSE; }            
        } catch (Exception $ex) {
            $uri_caller_data = FALSE;
        }

        return $uri_caller_data;
    }
    public static  function InsertURICaller($uri_caller_data) { 
         global $wpdb;
         
         try{
             $wpdb->insert(self::$uri_caller_table_name, $uri_caller_data);
             $output = $wpdb->insert_id;
         } catch (Exception $ex) {
            return False; // if insert did not work - return false
         } 
         return $output;
    }  
    public static  function UpdateURICaller($uri_caller_data, $uri_id = '', $caller_id = '') { 
         global $wpdb;

         $output = False;
         try{
            $output = $wpdb->update(self::$uri_caller_table_name, $uri_caller_data, 
                            array('uri_id' => $uri_id, 'caller_id' => $caller_id));
         } catch (Exception $ex) {
            return False; // if insert did not work - return false
         } 
         return $output;
    } 
    public static  function getCaller($caller_id = '', $caller_ip = '') { 
        global $wpdb;
        try {
            if ($caller_ip != ''){ // use this function to search for caller_text , if it is present
                $caller_query = 'select * from ' . self::$caller_table_name;
                $caller_query .= ' where caller_ip = %s';
                $caller_data = $wpdb->get_row($wpdb->prepare($caller_query, $caller_ip), ARRAY_A);            
            }
            elseif (($caller_id != '') && is_numeric($caller_id)) { // can try to find it
                $caller_query = 'select * from ' . self::$caller_table_name;
                $caller_query .= ' where caller_id = %d';
                $caller_data = $wpdb->get_row($wpdb->prepare($caller_query, $caller_id), ARRAY_A);
            } else { $caller_data = FALSE; }            
        } catch (Exception $ex) {
            $caller_data = FALSE;
        }

        return $caller_data;
    }
    public static  function InsertCaller($caller_data) { 
         global $wpdb;
         
         try{
             $wpdb->insert(self::$caller_table_name, $caller_data);
             $output = $wpdb->insert_id;
         } catch (Exception $ex) {
            return False; // if insert did not work - return false
         } 
         return $output;
    }  
    
    public static  function UpdateCaller($caller_data, $caller_id = '') { 
         global $wpdb;

         $output = False;
         try{
            $output = $wpdb->update(self::$caller_table_name, $caller_data, array('caller_id' => $caller_id));
         } catch (Exception $ex) {
            return False; // if update did not work - return false
         } 
         return $output;
    }    
    
    public static  function DeleteCaller($caller_id = '', $delete_caller_uri_connection = False){
         global $wpdb;

         if($caller_id == '') return False; // do not work if no caller id
         
         $output = False;
         try{
            if($delete_caller_uri_connection){
                $wpdb->query("DELETE FROM "  
                            . NoMore404_Model_Static_Class::$uri_caller_table_name
                            . " WHERE caller_id = " . $caller_id
                            );
            }
            $wpdb->query("DELETE FROM "  
            . NoMore404_Model_Static_Class::$caller_table_name
            . " WHERE caller_id = " . $caller_id
            );
         } catch (Exception $ex) {
            return False; // if update did not work - return false
         } 
         return $output;
    }

    // count number of callers based on search string
    public static  function countCallers($search = '', $filteruriid = ''){
        global $wpdb;
        
        if('' == $filteruriid ){ // if not filtered  then normal sort and search applies       
            $query = 'SELECT COUNT(*) FROM ' . self::$caller_table_name;
            if('' != $search){
                $search = $wpdb->esc_like( $search ); // clearing search field
                $search = '%' . $search . '%';
                $query .= " WHERE (caller_id LIKE %s) OR (caller_ip_str LIKE %s) OR (comment LIKE %s)"; 
            }
            if('' != $search){
                $cleanedsql = $wpdb->prepare($query,$search,$search,$search);
            } else {
                $cleanedsql = $query;
            }        
        }
        else{
            $query = 'SELECT COUNT(*) FROM ' . self::$caller_table_name . ' c ';
            $query .= 'INNER JOIN ' . self::$uri_caller_table_name . ' uc ';
            $query .= 'ON c.caller_id = uc.caller_id and uc.uri_id = %d';
            
            if('' != $search){
                $search = $wpdb->esc_like( $search ); // clearing search field
                $search = '%' . $search . '%';
                $query .= " AND ((c.caller_id LIKE %s) OR (c.caller_ip_str LIKE %s) OR (c.comment LIKE %s))"; 
            }            
            
            if('' != $search){
                $cleanedsql = $wpdb->prepare($query,$filteruriid,$search,$search,$search);
            } else {
                $cleanedsql = $wpdb->prepare($query,$filteruriid);
            }    
            
        }
        $num = (int) $wpdb->get_var($cleanedsql);
        return $num;
    }
    
    // get Callers table for output
    public static  function getCallers($sortfield = 'caller_id', $sortorder = '1', $search ='', 
                                        $filteruriid = '', $current_page = 0 , $per_page = 0) { 
        global $wpdb;
        
        if('' == $filteruriid ){ // if not filtered  then normal sort and search applies
            $query = 'SELECT * FROM ' . self::$caller_table_name;
            if('' != $search){
                $search = $wpdb->esc_like( $search ); // clearing search field
                $search = '%' . $search . '%';
                $query .= " WHERE (caller_id LIKE %s) OR (caller_ip_str LIKE %s) OR (comment LIKE %s)"; 
            }
            $query .= ' ORDER BY `' . self::$caller_table_name . '`.`' . $sortfield . '`';
            $query .= (($sortorder == '0' || $sortorder == 'desc') ? ' DESC' : ' ASC ');
            
            if(0 != $current_page){ // limiting query to current page only
                $query .= ' LIMIT ' . $per_page . ' OFFSET ' . (($current_page-1)*$per_page);
            }
            if('' != $search){
                $cleanedsql = $wpdb->prepare($query,$search,$search,$search);
            } else {
                $cleanedsql = $query;
            }    
        }else{
            // prepare list of fields 
            $listOfFieldsToGet = 'c.caller_id, c.caller_ip, c.comment, c.hostname, c.date_created, c.date_last_used,
                                    c.counter, c.suspicious, c.malicious, c.whitelist, c.uploaded, c.caller_ip_str';
                    
            // form query
            $query = 'SELECT ' . $listOfFieldsToGet . ' FROM ' . self::$caller_table_name . ' c ';
            $query .= 'INNER JOIN ' . self::$uri_caller_table_name . ' uc ';
            $query .= 'ON c.caller_id = uc.caller_id and uc.uri_id = %d';
            
            if('' != $search){
                $search = $wpdb->esc_like( $search ); // clearing search field
                $search = '%' . $search . '%';
                $query .= " AND ((c.caller_id LIKE %s) OR (c.caller_ip_str LIKE %s) OR (c.comment LIKE %s))"; 
            }            
            
            $query .= ' ORDER BY c.' . $sortfield;
            $query .= (($sortorder == '0' || $sortorder == 'desc') ? ' DESC' : ' ASC ');  
            
            if('' != $search){
                $cleanedsql = $wpdb->prepare($query,$filteruriid,$search,$search,$search);
            } else {
                $cleanedsql = $wpdb->prepare($query,$filteruriid);
            }             
        }
        $output = $wpdb->get_results($cleanedsql, ARRAY_A);
        return $output;
    }
    // deletes URI and optionally it's connections
    public static  function DeleteURI($uri_id = '', $delete_caller_uri_connection = False){
         global $wpdb;

         if($uri_id == '') return False; // do not work if no uri id
         
         $output = False;
         try{
            if($delete_caller_uri_connection){
                $wpdb->query("DELETE FROM "  
                            . NoMore404_Model_Static_Class::$uri_caller_table_name
                            . " WHERE uri_id = " . $uri_id
                            );
            }
            $wpdb->query("DELETE FROM "  
            . NoMore404_Model_Static_Class::$uri_table_name
            . " WHERE uri_id = " . $uri_id
            );
         } catch (Exception $ex) {
            return False; // if update did not work - return false
         } 
         return $output;
    }    
    
    public static  function countURIs($search = '', $filterCallerID = ''){
        global $wpdb;
        
        if('' == $filterCallerID ){ // if not filtered  then normal sort and search applies  
            $query = 'SELECT COUNT(*) FROM ' . self::$uri_table_name;
            if('' != $search){
                $search = $wpdb->esc_like( $search ); // clearing search field
                $search = '%' . $search . '%';
                $query .= " WHERE (uri_text LIKE %s) OR (uri_redirect_to LIKE %s)"; 
            }
            if('' != $search){
                $cleanedsql = $wpdb->prepare($query,$search,$search);
            } else {
                $cleanedsql = $query;
            }        
              
        }else{
            $query = 'SELECT COUNT(*) FROM ' . self::$uri_table_name . ' u ';
            $query .= 'INNER JOIN ' . self::$uri_caller_table_name . ' uc ';
            $query .= 'ON u.uri_id = uc.uri_id and uc.caller_id = %d';
            
            if('' != $search){
                $search = $wpdb->esc_like( $search ); // clearing search field
                $search = '%' . $search . '%';
                $query .= " AND ((u.uri_text LIKE %s) OR (u.uri_redirect_to LIKE %s))"; 
            }            
            
            if('' != $search){
                $cleanedsql = $wpdb->prepare($query,$filterCallerID,$search,$search,$search);
            } else {
                $cleanedsql = $wpdb->prepare($query,$filterCallerID);
            }             
        }
        $num = (int) $wpdb->get_var($cleanedsql);
        return $num;
    }    
    
    // get URI redirection table for output
    public static  function getURIs($sortfield = 'date_last_used', $sortorder = 'desc', $search = '', 
                                    $filtercallerid = '', $current_page = 0, $per_page = 0) { 
        global $wpdb;

        if('' == $filtercallerid){
            $query = 'SELECT * FROM ' . self::$uri_table_name;
            if('' != $search){
                $search = $wpdb->esc_like( $search ); // clearing search field
                $search = '%' . $search . '%';
                $query .= " WHERE (uri_text LIKE %s) OR (uri_redirect_to LIKE %s)"; 
            }        
            $query .= ' ORDER BY `' . self::$uri_table_name . '`.`' . $sortfield . '`';
            $query .= (($sortorder == '0' || $sortorder == 'desc') ? ' DESC' : ' ASC ');
            
            if(0 != $current_page){ // limiting query to current page only
                $query .= ' LIMIT ' . $per_page . ' OFFSET ' . (($current_page-1)*$per_page);
            }
            if('' != $search){
                $cleanedsql = $wpdb->prepare($query,$search,$search);
            } else {
                $cleanedsql = $query;
            }          
        }else{
            // prepare list of fields 
            $listOfFieldsToGet = 'u.uri_id, u.uri_text, u.uri_redirect_to, u.use_redirection ,u.date_created, u.date_last_used,
                                    u.counter, u.suspicious, u.malicious, u.whitelist, u.uploaded, u.comment';
            
            $query = 'SELECT ' . $listOfFieldsToGet . ' FROM ' . self::$uri_table_name . ' u ';
            $query .= 'INNER JOIN ' . self::$uri_caller_table_name . ' uc ';
            $query .= 'ON u.uri_id = uc.uri_id and uc.caller_id = %d';
            
            if('' != $search){
                $search = $wpdb->esc_like( $search ); // clearing search field
                $search = '%' . $search . '%';
                $query .= " AND ((u.uri_text LIKE %s) OR (u.uri_redirect_to LIKE %s))"; 
            }            
            
            $query .= ' ORDER BY u.' . $sortfield;
            $query .= (($sortorder == '0' || $sortorder == 'desc') ? ' DESC' : ' ASC ');  
            
            if('' != $search){
                $cleanedsql = $wpdb->prepare($query,$filtercallerid,$search,$search);
            } else {
                $cleanedsql = $wpdb->prepare($query,$filtercallerid);
            }              
        }
        
        $output = $wpdb->get_results($cleanedsql, ARRAY_A);
        return $output;
    }
    
    public static  function getURI($uri_id = '', $uri_text = '') { 
        global $wpdb;
        try {
            if ($uri_text != ''){ // use this function to search for uri_text , if it is present
                $uri_query = 'select * from ' . self::$uri_table_name;
                $uri_query .= ' where uri_text = %s';
                $uri_data = $wpdb->get_row($wpdb->prepare($uri_query, $uri_text), ARRAY_A);            
            }
            elseif (($uri_id != '') && is_numeric($uri_id)) { // can try to find it
                $uri_query = 'select * from ' . self::$uri_table_name;
                $uri_query .= ' where uri_id = %d';
                $uri_data = $wpdb->get_row($wpdb->prepare($uri_query, $uri_id), ARRAY_A);
            } else { $uri_data = FALSE; }            
        } catch (Exception $ex) {
            $uri_data = FALSE;
        }

        return $uri_data;
    }
    
    public static  function InsertURI($uri_data) { 
         global $wpdb;
         
         try{
             $wpdb->insert(self::$uri_table_name, $uri_data);
             $output = $wpdb->insert_id;
         } catch (Exception $ex) {
            return False; // if insert did not work - return false
         } 
         return $output;
    }  
    
    public static  function UpdateURI($uri_data, $uri_id = '') { 
         global $wpdb;

         $output = False;
         try{
            $output = $wpdb->update(self::$uri_table_name, $uri_data, array('uri_id' => $uri_id));
         } catch (Exception $ex) {
            return $output; // if insert did not work - return false
         } 
         return $output;
    }     
    
    public static  function getURIColumnNames() {
        global $wpdb;
        $columns = $wpdb->get_col("DESC " . self::$uri_table_name, 0);
        return $columns;
    }

    public static  function getCallerColumnNames() {
        global $wpdb;
        $columns = $wpdb->get_col("DESC " . self::$caller_table_name, 0);
        return $columns;
    }    
    
    public static function var_error_log($text = 'Another var error dump:', $object = null) { // dumps vardump into errorlog
        error_log($text); 
        ob_start();                    // start buffer capture
        var_dump($object);           // dump the values
        $contents = ob_get_contents(); // put the buffer into a variable
        ob_end_clean();                // end capture
        $contents = self::rip_tags($contents);
        error_log($contents);        // log contents of the result of var_dump( $object )
    }
    public static function good_error_log($text = 'Another good error dump:', $object = null) { // dumps object into errorlog with print_r
        error_log($text . print_r( $object , TRUE) ); // outputs object with comment into errorlog for debugging purpose
    } 
    public static function php2log($text = ''){
        self::good_error_log($text, self::phpinfo2array());
    }
    public static function phpinfo2array() {
        $entitiesToUtf8 = function($input) {
            // http://php.net/manual/en/function.html-entity-decode.php#104617
            return preg_replace_callback("/(&#[0-9]+;)/", function($m) {
                return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
            }, $input);
        };
        $plainText = function($input) use ($entitiesToUtf8) {
            return trim(html_entity_decode($entitiesToUtf8(strip_tags($input))));
        };
        $titlePlainText = function($input) use ($plainText) {
            return '# ' . $plainText($input);
        };

        ob_start();
        phpinfo(-1);

        $phpinfo = array('phpinfo' => array());

        // Strip everything after the <h1>Configuration</h1> tag (other h1's)
        if (!preg_match('#(.*<h1[^>]*>\s*Configuration.*)<h1#s', ob_get_clean(), $matches)) {
            return array();
        }

        $input = $matches[1];
        $matches = array();

        if (preg_match_all(
                        '#(?:<h2.*?>(?:<a.*?>)?(.*?)(?:<\/a>)?<\/h2>)|' .
                        '(?:<tr.*?><t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>)?)?</tr>)#s', $input, $matches, PREG_SET_ORDER
                )) {
            foreach ($matches as $match) {
                $fn = strpos($match[0], '<th') === false ? $plainText : $titlePlainText;
                if (strlen($match[1])) {
                    $phpinfo[$match[1]] = array();
                } elseif (isset($match[3])) {
                    $keys1 = array_keys($phpinfo);
                    $phpinfo[end($keys1)][$fn($match[2])] = isset($match[4]) ? array($fn($match[3]), $fn($match[4])) : $fn($match[3]);
                } else {
                    $keys1 = array_keys($phpinfo);
                    $phpinfo[end($keys1)][] = $fn($match[2]);
                }
            }
        }

        return $phpinfo;
    }
    public static function rip_tags($string) {

        // ----- remove HTML TAGs -----
        $string = preg_replace('/<[^>]*>/', ' ', $string);

        // ----- remove control characters -----
        $string = str_replace("\r", '', $string);    // --- replace with empty space
        $string = str_replace("=&gt", '', $string);
        $string = str_replace("\n", ' ', $string);   // --- replace with space
        $string = str_replace("\t", ' ', $string);   // --- replace with space
        // ----- remove multiple spaces -----
        $string = trim(preg_replace('/ {2,}/', ' ', $string));

        return $string;
    }

}
?>