<?php  namespace App\Models;

use CodeIgniter\Model;

class ProjectModel extends Model{
    protected $table = 'docsgo-projects';
    protected $primaryKey = 'project-id'; 
    protected $allowedFields = ['project-id','name', 'version', 'description', 'start-date', 'end-date', 'status', 'manager-id'];
    
    public function getProjects(){
        $db = \Config\Database::connect();
        $sql = "Select `project-id`, `name` from `docsgo-projects`";
        $query = $db->query($sql);

        $result = $query->getResult('array');
        $data = [];
        foreach($result as $row){
            $data[$row['project-id']] = $row['name'];
        }
        
        return $data;
    }
    
}