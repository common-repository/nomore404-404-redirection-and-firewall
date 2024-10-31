<?php
// This is module to upload URI data and download URI data as well
// it will upload your 404 callers and redirects to Devoutpro backed 
// for analysis by security experts and will download updates for 
// malicious/suspicious/whitelisted database, analysed and checked
// by our security team.
// All updates are incremental, and you will not be getting any 
// unnessesary trafic

// If this file is called directly, stop execution.
defined('ABSPATH') or exit;

// AWS initialising
require_once plugin_dir_path(__FILE__) . '/aws/aws-autoloader.php'; // aws sdk 
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\Exception\AwsException;

// this class does all upload and download of data with backend
Class NoMore404_UD_Static_Class {
    const s3uploadBucket = '';     
   
    private function __construct() {
    }

    // all data will be uploaded and marked as uploaded if in the transaction
    // if not uploaded then all changes to uploaded flag will be rolled back
    public static function UploadAllNewData($dofullupload = false) {
        global $wpdb;
        
        // Start Transaction
        $wpdb->query( "START TRANSACTION" );    
        
        // first get data in JSON format 
        $strJSON = Self::CreateJSON4Upload(FALSE); // what was not uploaded yet
        
        //NoMore404_Model_Static_Class::good_error_log('Uploading info:', $strJSON);
        
        if(FALSE !== $strJSON){ // there is something to upload
            $uploaded = self::UploadStringAsFile2S3($strJSON);
        
            if($uploaded) $wpdb->query( "COMMIT" );
            else $wpdb->query( "ROLLBACK" );
        }else{
            $uploaded = FALSE;
        }
        
        return $uploaded;
    }
    
    public static function UploadStringAsFile2S3($file2upload){
        //Create a S3Client
        $s3 = new Aws\S3\S3Client([
            'version' => 'latest',
            'region' => 'ap-southeast-2'
        ]);
        
        $sharedConfig = [
            'region' => 'ap-southeast-2',
            'version' => '2006-03-01', // good version for S3, do not use 'latest' in production
            'signature_version' => 'v4',
            'credentials' => false,
  //          'debug'   => true
        ];

        // Create an SDK class used to share configuration across clients.
        $sdk = new Aws\Sdk($sharedConfig);

        // Use an Aws\Sdk class to create the S3Client object.
        $s3Client = $sdk->createS3();        
        
        try {
            // Send a PutObject request and get the result object.
            $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $key =  'nomore404-' . substr(str_shuffle($permitted_chars), 0, 24) . '.json';
            
            //$key = basename($file2upload);
            $result = $s3Client->putObject([
                'Bucket' => 'nomore404-in',
                'Key' => $key,
                'Body' => $file2upload, 
                'ACL' => 'bucket-owner-full-control',
            ]);
            //NoMore404_Model_Static_Class::good_error_log('S3UploadResult:', $result);
            //NoMore404_Model_Static_Class::good_error_log('File uploaded:', $file2upload);
            
            // returns true if uploaded
            if(isset($result['@metadata']['statusCode']) && ('200' == $result['@metadata']['statusCode'])) return true;
        } catch (S3Exception $e) {
            // Catch an S3 specific exception.
  //          NoMore404_Model_Static_Class::good_error_log('S3Exception:', $e->getMessage());
            return false; 
        } catch (AwsException $e) {
            // This catches the more generic AwsException. You can grab information
            // from the exception using methods of the exception object.
//            NoMore404_Model_Static_Class::good_error_log('AWSExceptionAwsRequestId:', $e->getAwsRequestId());    
 //           NoMore404_Model_Static_Class::good_error_log('AWSExceptionAwsErrorType:', $e->getAwsErrorType());    
 //           NoMore404_Model_Static_Class::good_error_log('AWSExceptionAwsErrorCode:', $e->getAwsErrorCode());    
            // This dumps any modeled response data, if supported by the service
            // Specific members can be accessed directly (e.g. $e['MemberName'])
 //           NoMore404_Model_Static_Class::var_error_log('AWSExceptionModeledResponse:', $e->toArray());    
            return false; 
        }
        
        return false; // any other problems, upload did not work, return false
    }
    
    // returns JSON string for upload or False if nothing to upload
    public static function CreateJSON4Upload($dofullupload = false){
        $strJSONText = '';
        $thereIsSomethingToUpload = FALSE;
        
        // get web site data and admin email into JSON for identification
        $strJSONText .= '{"nomore404_upload": "nomore404_upload",' . PHP_EOL;
        $strJSONText .= '"date_time": "' . current_time('mysql', TRUE) . '",' . PHP_EOL; // add time in gmt timezone
        $strJSONText .= '"admin_email": "' . get_bloginfo('admin_email') . '",' . PHP_EOL; // geting admin email
        $strJSONText .= '"url": "' . get_bloginfo('url') . '",' . PHP_EOL; // address of the client
        $strJSONText .= '"wp_version": "' . get_bloginfo('version') . '",' . PHP_EOL; // wordpress verson
        $strJSONText .= '"version": "' . NOMORE404VERSION . '",' . PHP_EOL; // plug in versions
        $strJSONText .= '"dbversion": "' . NOMORE404DBVERSION . '"'; //
        
    
        // lets get callers data
        // first get data what was not uploaded yet and mark them uploaded as well in transaction
        $callers2upload = NoMore404_Model_Static_Class::getCallersForUpload(TRUE);
        //NoMore404_Model_Static_Class::good_error_log('Callers:', $callers2upload);
        //NoMore404_Model_Static_Class::var_error_log('Callers to upload:',$callers2upload);
        if((NULL !== $callers2upload) && (is_array($callers2upload) && sizeof($callers2upload) > 0)){ 
        // there is something to take in, it is array with at least one element
            //NoMore404_Model_Static_Class::good_error_log('Callers:', $callers2upload);
            //NoMore404_Model_Static_Class::good_error_log('CallersJSON:', json_encode($callers2upload, JSON_FORCE_OBJECT));
            $strJSONText .= ',' . PHP_EOL . '",Callers": '; // start of caller records
            $strJSONText .= json_encode($callers2upload, JSON_PRETTY_PRINT); 
            $thereIsSomethingToUpload = TRUE;
            //NoMore404_Model_Static_Class::good_error_log('JSON error:',json_last_error_msg()); 
        }
        
        

        $URIs2upload = NoMore404_Model_Static_Class::getURIsForUpload(TRUE); // get data what was not uploaded yet
        if(NULL !== $URIs2upload && (is_array($URIs2upload) && sizeof($URIs2upload) > 0)){ 
            $strJSONText .=  ',' . PHP_EOL . '"URIs": '; // start of URIs records
            // there is something to take in, it is array with at least one element
            $strJSONText .= json_encode($URIs2upload, JSON_PRETTY_PRINT); 
            $thereIsSomethingToUpload = TRUE;
        } 

        
        $URICaller2upload = NoMore404_Model_Static_Class::getURICallerForUpload(TRUE); // get data what was not uploaded yet
        //NoMore404_Model_Static_Class::good_error_log('$URICaller2upload:', $URICaller2upload);
        if(NULL !== $URICaller2upload && (is_array($URICaller2upload) && sizeof($URICaller2upload) > 0)){ 
            $strJSONText .= ',' . PHP_EOL . '"URICaller": '; // start of URIs records
            // there is something to take in, it is array with at least one element
            $strJSONText .= json_encode($URICaller2upload, JSON_PRETTY_PRINT) . PHP_EOL; 
            $thereIsSomethingToUpload = TRUE;
        } 
        
        $strJSONText .= '}'; // last closing bracket
        
        if($thereIsSomethingToUpload) return $strJSONText; else return FALSE;
    }
}
?>