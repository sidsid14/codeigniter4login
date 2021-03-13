<?php namespace App\Controllers;
use App\Models\SettingsModel;
class Dashboard extends BaseController
{
    public function index()
    {
        $data = [];

        echo view('templates/header', $data);
        echo view('dashboard', $data);
        echo view('templates/footer');
    }

    public function getStats()
    {
        $response = array("success" => "true");
        
        $settingsModel = new SettingsModel();
		$thirdParty = $settingsModel->getThirdPartyConfig();

        $response["sonar_stats"] = $this->getSonarStats($thirdParty["sonar"]["url"]);
        $response["jenkins_stats"] = $this->getJenkinsStats($thirdParty["jenkins"]["url"], $thirdParty["jenkins"]["key"]);
   
        return json_encode($response);
    }

    private function getSonarStats($sonar_ip)
    {
        $sonar_projects_url = $sonar_ip . "/api/components/search?qualifiers=TRK";
        $sonar_stats = $this->getReq($sonar_projects_url);
        $sonar_projects = [];

        if ($sonar_stats != null) {
            if (count($sonar_stats)) {
                if (count($sonar_stats["components"])) {
                    $components = $sonar_stats["components"];
                    foreach ($components as $comp) {
                        array_push($sonar_projects, $comp["key"]);
                    }
                    $sonar_metric_keys = "&metricKeys=alert_status,bugs,vulnerabilities,code_smells";
                    $sonar_metrics_url = $sonar_ip . "/api/measures/search?projectKeys=" . join(",", $sonar_projects) . $sonar_metric_keys;
                    $sonar_metrics = $this->getReq($sonar_metrics_url);
                    if (count($sonar_metrics["measures"])) {
                        $measures = $sonar_metrics["measures"];
                        $stats = array();
                        $components = array();
                        foreach ($measures as $measure) {
                            $component_name = $measure["component"];
                            $metric = $measure["metric"];
                            $value = $measure["value"];

                            if (!in_array($component_name, $components)) {
                                $stats[$component_name] = array($metric => $value);
                                array_push($components, $component_name);
                            }

                            $stats[$component_name] += [$metric => $value];

                        }

                        return $stats;

                    } else {
                        return null;
                    }
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } else {
            return null;
        }

    }

    private function getJenkinsStats($jenkins_ip, $jenkins_job_name)
    {
        $jenkins_api = $jenkins_ip."/job/".$jenkins_job_name."/lastBuild/api/json";
        $jenkins_stats = $this->getReq($jenkins_api);

        if ($jenkins_stats != null) {
            $buildInfo = array(
                "id" => $jenkins_stats["id"],
                "result" => $jenkins_stats["result"],
                "timestamp" => $jenkins_stats["timestamp"],
                "duration" => $jenkins_stats["duration"],
                "url" => $jenkins_stats["url"],
                "changeLog" => array(),
            );

            $changeSets = count($jenkins_stats["changeSets"]) > 0 ? $jenkins_stats["changeSets"][0]["items"] : [];
            foreach ($changeSets as $changes) {
                $changeLog = array(
                    "authorEmail" => $changes["authorEmail"],
                    "msg" => $changes["msg"],
                    "timestamp" => $changes["timestamp"],
                );
                array_push($buildInfo["changeLog"], $changeLog);
            }

            return $buildInfo;
        } else {
            return null;
        }
    }

    private function getReq($url)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_TIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }


}
