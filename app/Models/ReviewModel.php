<?php  namespace App\Models;

use CodeIgniter\Model;

class ReviewModel extends Model{
    protected $table = 'docsgo-reviews';
    protected $allowedFields = ["assigned-to","context","description", "code-diff", "id","project-id",
                                "review-by","review-name","review-ref","status","updated-at", "category"];


    public function getMappedRecords($whereCondition = ""){
        $db      = \Config\Database::connect();
        $sql = "SELECT CONCAT('R','-',rev.`id`) as reviewId, rev.`id`, rev.`review-by`, team.`name` as `reviewer`, rev.`assigned-to`, team2.name as `author`,
        rev.`context`,rev.`description`,proj.`name` as `project-name`,rev.`review-name`,rev.`review-ref`,rev.`status`, rev.`updated-at`
        FROM `docsgo-reviews` AS rev
        INNER JOIN `docsgo-projects` AS proj ON proj.`project-id` = rev.`project-id`
        INNER JOIN `docsgo-team-master` AS team ON rev.`review-by` = team.`id` 
        INNER JOIN `docsgo-team-master` AS team2 ON rev.`assigned-to` = team2.`id` 
        ".$whereCondition."
         ORDER BY rev.`updated-at` desc;";

        $query = $db->query($sql);
        $data = $query->getResult('array');

        for($i=0; $i<count($data );$i++){

            $description = $data[$i]['description'];
            
            if($description != null && $description != ""){
                $description = json_decode($description, true);
                $comments = "";
                foreach($description as $comment){
                    $comments .= "[".$comment["by"]."]";
                    $comments .= $comment["message"];
                    $comments .= "<br/>";
                }
                $data[$i]['description'] = $comments;
            }
            
        }
        return $data;
    }

    public function getReviewsCount($project_id){
        $db      = \Config\Database::connect();
        $sql = "select count(*) as count ,status from `docsgo-reviews` where `project-id` = ".$project_id." group by status";

        $query = $db->query($sql);
        $result = $query->getResult('array');

        if(count($result)){
            for($i=0; $i<count($result);$i++){
                $data[$result[$i]['status']] = $result[$i]['count'];
            }
            return $data;
        }else{
            return null;
        }
        
        
        
    }

    public function getPrevReviewId($updateDate, $project_id, $status){
        $db      = \Config\Database::connect();
        $sql = "SELECT id from `docsgo-reviews` where `updated-at` < '".$updateDate."' and `project-id` = ".$project_id." and status = '".$status."'  ORDER BY `updated-at` desc LIMIT 1;";
       
        $query = $db->query($sql);

        $data = $query->getResult('array');
        if(count($data)){
            $data = $data[0]['id'];
        }else{
            $data = null;
        }
        return $data;
    }
     
    public function getNextReviewId($updateDate, $project_id, $status){
        $db      = \Config\Database::connect();
        $sql = "SELECT id from `docsgo-reviews` where `updated-at` > '".$updateDate."' and `project-id` = ".$project_id." and status = '".$status."'  ORDER BY `updated-at`,id desc LIMIT 1;";
       
        $query = $db->query($sql);

        $data = $query->getResult('array');
        if(count($data)){
            $data = $data[0]['id'];
        }else{
            $data = null;
        }
        return $data;
    }
}