<?php  namespace App\Models;

use CodeIgniter\Model;

class ProjectModel extends Model{
    protected $table = 'docsgo-projects';
    protected $primaryKey = 'project-id'; 
    protected $allowedFields = ['project-id','name', 'version', 'description', 'start-date', 'end-date', 'status', 'manager-id'];
    
    public function getProjects(){
        $db = \Config\Database::connect();
        $sql = "Select `project-id`, `name` from `docsgo-projects` where status = 'Active' ORDER BY `start-date` DESC ";
        $query = $db->query($sql);

        $result = $query->getResult('array');
        $data = [];
        foreach($result as $row){
            $data[$row['project-id']] = $row['name'];
        }
        
        return $data;
    }
    
    public function getDownloadedProjectStatus($projectId, $updateDate) {
        $db = \Config\Database::connect();
        $sql = "SELECT count(*) as count from `docsgo-documents` where `project-id` = ".$projectId." AND `update-date` > '".$updateDate."'";
        $query = $db->query($sql);

        $result = $query->getResult('array');
        return $result;
    }

    public function updateGenerateDocumentPath($projectId, $link) {
        $db = \Config\Database::connect();
        $whereCondition = " WHERE `project-id` = ".$projectId." "; 
        $sql = "UPDATE `docsgo-projects` SET `download-path` = '".$link."' WHERE `project-id` = '".$projectId."'";

        $query = $db->query($sql);
        $data = $query->getResult('array');
        return $data;
    }    

}