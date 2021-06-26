<?php
namespace TP\Tools;

use stdClass;

#
# @Class SonarQube
# 
# This Class can be used for the data extraction from the sonar qube server
#

class SonarQube
{
    /**
     * This method is used to fetch sonar qube vulnerabilities
     * @param {String} URL - contains the TestLink API URL
     * @param {String} options - contains the request options
     * @return {Object} vulnearabilities
     */
    function getVulnerabilities($URL, $authentication_token){
        $response = $this->callAPI($URL, $authentication_token);
        
        return $response;
    }


    /**
     * This method is used to make a http/https call
     * @param {String} URL - contains the URL
     * @param {Object} options - contains the request options
     * @return {Object} API response 
     */
    private function callAPI($URL, $authentication_token){
        try {
            // $baseEncodedToken = base64_encode("$authentication_token:");
            $vulnerabilities = [];
            // $ch = curl_init();  
            // $curl_options = array(
            //     CURLOPT_URL => $URL,
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => '',
            //     CURLOPT_FOLLOWLOCATION => true,
            //     CURLOPT_TIMEOUT => 5,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => 'GET',
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_HTTPHEADER => array(
            //         "Authorization: Basic $baseEncodedToken"
            //     )
            // );
    
            // if($authentication_token != ""){
            //     $baseEncodedToken = base64_encode("$authentication_token:");
            //     $curl_options[CURLOPT_MAXREDIRS] = 10;
            //     $curl_options[CURLOPT_HTTPHEADER] = array(
            //         "Authorization: Basic $baseEncodedToken"
            //     );
            // }
    
            // curl_setopt_array($ch, $curl_options);
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            // curl_setopt($ch, CURLOPT_URL, $URL); 
            // curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Basic $baseEncodedToken"));
            // $result = curl_exec($ch); 
            // curl_close($ch);
            helper('Helpers\utils');
            $data = curlGETRequest($URL,  $authentication_token, false);
            // print_r($data[]);
            // exit(0);
            if( $data->total ){
                $count = $data->total;  
                $pageCount = floor($count/100);
                if( $pageCount == 0 ){
                    $pageCount = 1;
                }        
                if( $count != 0 && $pageCount > 0 ){
                    for( $i = 1; $i <= $pageCount; $i++ ){
                        $pageIndex = $i;
                        // $curlReq = curl_init();  
                        // curl_setopt($curlReq, CURLOPT_RETURNTRANSFER, 1); 
                        // curl_setopt($curlReq, CURLOPT_URL, "$URL&pageIndex=$pageIndex"); 
                        // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        //     "Authorization: Basic $baseEncodedToken"
                        // ));
                        // $res = curl_exec($curlReq); 
                        $tmp = curlGETRequest("$URL&pageIndex=$pageIndex",  $authentication_token, false);
                        $vulnerabilities = array_merge( $vulnerabilities, $tmp->issues);
                    }
                }
            }
            return $vulnerabilities;
        } catch(Exception $e){
            error_log($e);
            return false;
        }
    }
}