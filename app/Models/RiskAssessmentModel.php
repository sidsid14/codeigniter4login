<?php  namespace App\Models;

use CodeIgniter\Model;
use TP\Tools\SonarQube;

class RiskAssessmentModel extends Model{
    protected $table = 'docsgo-risks';
    protected $allowedFields = ['project_id', 'risk_type', 'risk', 'description', 'component', 'failure_mode', 'harm', 'cascade_effect', 'hazard-analysis', 'baseScore_severity','status', 'assessment', 'update_date'];


    function getRisks($status = '', $type = '') {
        $db      = \Config\Database::connect();
        $whereCondition = ""; $riskType = 'Vulnerability';
        $riskType = $type;
        if($status == "All"){
            $whereCondition = ($riskType == 'Automation') ? " WHERE component = 'vms_automation' AND risk_type = 'Vulnerability' " : " WHERE risk_type = '".$riskType."' ";
        }else{
            $whereCondition = ($riskType == 'Automation') ? " WHERE component = 'vms_automation' AND risk_type = 'Vulnerability' AND status = '".$status."' " : " WHERE risk_type = '".$riskType."' AND status = '".$status."' ";
        }
        $sql = "SELECT * from `docsgo-risks` ". $whereCondition . "ORDER BY update_date desc;";
        $query = $db->query($sql);
        $data = $query->getResult('array');
        return $data;
    }

    function getVulnerabilitiesList(){
        $db = \Config\Database::connect();

        $whereCondition = " WHERE risk_type = 'Vulnerability' "; 
        $sql = "SELECT * FROM `docsgo-risks` ". $whereCondition . "ORDER BY update_date desc;";
        $query = $db->query($sql);
        $data = $query->getResult('array');
        return $data;
    }

    function updateVulnerabilityDescription( $riskId, $description){
        $db = \Config\Database::connect();

        $whereCondition = " WHERE id = ".$riskId." "; 
        $sql = "UPDATE `docsgo-risks` SET `description` = '".$description."'
        ".$whereCondition ."";

        $query = $db->query($sql);
        $data = $query->getResult('array');

        return $data; 
    }

    function getRisksForDocuments($condition = ""){
        $db      = \Config\Database::connect();
        $sql = "SELECT * from `docsgo-risks` ".$condition." ORDER BY update_date desc;";
        $query = $db->query($sql);
        $result = $query->getResult('array');
        $data = array();
        
    // $data['assessment'] = $row['assessment'];
       


        foreach($result as $row){
            $temp = array();
            $temp['baseScore_severity'] = $row['baseScore_severity'];
            $temp['component'] = $row['component'];
            $temp['description'] = $row['description'];
            $temp['id'] = $row['id'];
            $temp['hazard-analysis'] = $row['hazard-analysis'];
            $temp['project_id'] = $row['project_id'];
            $temp['risk_type'] = $row['risk_type'];
            $temp['failure_mode'] = $row['failure_mode'];
            $temp['harm'] = $row['harm'];
            $temp['cascade_effect'] = $row['cascade_effect'];
            switch($temp['risk_type']){
                case 'Vulnerability':
                    if($temp['component'] != 'vms_automation')
                        $temp['risk'] = 'V- '.$row['risk'];
                    else
                        $temp['risk'] = 'AT- '.$row['risk'];
                    break;
                case 'SOUP':
                    $temp['risk'] = 'S- '.$row['risk'];
                    break;
                case 'Open-Issue':
                    $temp['risk'] = 'OI- '.$row['risk'];
                    break;
                case 'Scope-Items':
                    $temp['risk'] = 'SI- '.$row['risk'];
                    break;
            }
            $temp['status'] = $row['status'];
            
            if($row['assessment'] == "" ){
                $temp['assessment'] = "";
            }else{
                $assessment = json_decode( $row['assessment'], true );
                if($assessment["risk-assessment"]["cvss"][0]["Score"][0]["value"] == ""){
                    //FMEA
                    $fmea = $assessment["risk-assessment"]["fmea"];
                    $content = "<ol>";
                    foreach($fmea as $section){
                        $content .= "<li>".($section["category"])." => ";
                        if($section["value"] == ""){
                            $section["value"] = "--";
                        }
                        $content .= " ".$section["value"]."</li>";
                    }
                    $content .= "</ol>";
                    $temp['assessment'] = $content;
                }else{
                    //CVSS
                    $cvss = $assessment["risk-assessment"]["cvss"][0];
                    $temp['assessment'] = "";
                    foreach($cvss as $key=>$metric){
                        if($key == "Score"){
                            $key = "CVSS 3.1 ". $key;
                        }
                        $content = "**".strtoupper($key)."** <br/>";
                        $content .= "<ol>";
                        foreach($metric as $section){
                            $content .= "<li>".$section["category"] . " => ";
                            if($section["value"] == ""){
                                $section["value"] = "--";
                            }
                            $content .= " ".$section["value"]."</li>";
                        }
                        $content .= "</ol>";
                        $temp['assessment'] .= $content .  "<br/>" ;
                    }
                    
                }
            }
           
            array_push($data, $temp);
        }

        return $data;
    }

    function formatAssesment($data){

    }


    function getSonarRecords(){
        $settingsModel = new SettingsModel();
        $sonarQubeObj = new SonarQube();

        $serverConfig = $settingsModel->getSettings("third-party");
        
        $serverDetails = json_decode($serverConfig[0]['options']);
        $BaseURL = "";
        foreach( $serverDetails as $server ){
            if($server->key == "sonar"){
                $BaseURL= $server->url;
            }
        }
        
        $vulnerabilities = [];
        if( $BaseURL){
            try {
                $vulnerabilitiesAPIURL = "$BaseURL/api/issues/search?types=VULNERABILITY&statuses=OPEN";
                
                $vulnerabilities = $sonarQubeObj->getVulnerabilities($vulnerabilitiesAPIURL);
                return $vulnerabilities;
            } catch(Exception $e){
                error_log($e);
                return false;
            }
        } else {
            return false;
        }        
    }
}