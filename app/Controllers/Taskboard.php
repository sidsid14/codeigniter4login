<?php namespace App\Controllers;

use App\Models\TaskboardModel;
use App\Models\ProjectModel;
use App\Models\TeamModel;

class Taskboard extends BaseController
{
	public function index()
    {
        $data = [];
        $project_id = $this->request->getVar('project-id');
        if($project_id != ""){
            $projectModel = new ProjectModel();
            $project = $projectModel->where('project-id', $project_id)->first();
            $activeProjects = $projectModel->getProjects();
            $data['activeProjects'] =  $activeProjects;
            $title = $project['name'];
            $data['title'] = $title;	
            $data['project_id'] =  $project_id;

            $teamModel = new TeamModel();
            $data['teamMembers'] = $teamModel->getMembers();

            $taskModel = new TaskboardModel();
            $data['tasksArr'] = $taskModel->getTasks("WHERE project_id = ".$project_id);
            $data['chartData'] = $taskModel->getTasksCount($project_id);
        }
		echo view('templates/header');
		echo view('taskboard',$data);
		echo view('templates/footer');

    }

    public function getTasks(){
        $project_id = $this->request->getVar('project_id');
        $user_id = $this->request->getVar('user_id');

        $userCondition = "";
        if($user_id != "ALL"){
            $userCondition = " AND (tasks.assignee = ".$user_id." OR tasks.verifier = ".$user_id.") ";
        }
        $taskModel = new TaskboardModel();
        $tasksArr = $taskModel->getTasks("WHERE project_id = ".$project_id.$userCondition );
        $chartData= $taskModel->getTasksCount($project_id, $user_id);

        $response["success"] = "True";
        $response["tasksArr"] = $tasksArr;
        $response["chartData"] = $chartData;

        echo json_encode($response);
    }

    private function isAuthorized($id){
        if(session()->get('is-manager')){
            return true;
        }else{
            $taskModel = new TaskboardModel();
            $task = $taskModel->find($id);
            if($task["creator"] == session()->get('id')){
                return true;
            }else{
                $response = array();
                $response["success"] = "False";
                $response["errorMsg"] = "You are not the owner of this task. Your changes will not be saved.";
                echo json_encode($response);
                exit(0);
            }
        }
    }

    public function addTask(){
        
        $id = $this->request->getVar('id');
        $project_id = $this->request->getVar('project_id');
        if($id != ""){
            $this->isAuthorized($id);
        }
        if($project_id == ""){
            $response = array();
            $response["success"] = "False";
            $response["errorMsg"] = "Project cannot be empty!";
            echo json_encode($response);
            exit(0);
        }
        $project_id = $this->request->getVar('project_id');
        $task = [
            "assignee" => $this->request->getVar('newTask_assignee'),
            "description" => $this->request->getVar('newTask_description'),
            "project_id" => $project_id,
            "verifier" => $this->request->getVar('newTask_verifier') ,
            "task_category" => $this->request->getVar('newTask_category'),
            "task_column" => $this->request->getVar('newTask_column'),
            "title" => $this->request->getVar('newTask_title'),
        ];

        $attachmentsDir = "uploads/taskboard/".$project_id;
        $fileLinks = $this->uploadFiles($attachmentsDir);

        if(count($fileLinks)){
            
            $attachments = $this->returnJsonField($id, 'attachments');
            $attachmentsCount = count($attachments);
            $attachmentsCount += 1;
            
            foreach($fileLinks as $key=>$object){
                $attachment["id"] = $attachmentsCount;
                $attachment["link"] = $object['link'];
                $attachment["type"] = $object['type'];
                array_push($attachments, $attachment);
                $attachmentsCount++;
            }
            $task["attachments"] = json_encode($attachments);
            
        }
        
        $model = new TaskboardModel();
        if($id != ""){
            $model->update($id, $task);
            $task = $model->find($id);
        }else{
            $task['creator'] = session()->get('id');
            $id = $model->insert($task);
            $task["id"] = $id;
        }

        
        $response["success"] = "True";
        $response["id"] = $id;
        
        $response["task"] = $task;

        echo json_encode($response);
    }

    private function uploadFiles($attachmentsDir){
        $fileLinks = array();
        if($files = $this->request->getFiles())
        {
            if (!file_exists($attachmentsDir)) {
                mkdir($attachmentsDir, 0777, true);
            }

            foreach($files['attachments'] as $attachment)
            {
                if ($attachment->isValid() && ! $attachment->hasMoved())
                {                   
                    $newName = $attachment->getRandomName();
                    $attachment->move($attachmentsDir, $newName);
                    $type = $attachment->getClientMimeType();
                    $link = "/".$attachmentsDir."/".$newName;

                    $object['link'] = $link;
                    $object['type'] = $type;
                    array_push($fileLinks,  $object);
                }
            }
        }
        return $fileLinks;
    }

    public function updateTaskColumn(){
        
        $id = $this->request->getVar('id');
        $this->isAuthorized($id);

        $task = [
            "task_column" => $this->request->getVar('task_column')
        ];

        $model = new TaskboardModel();
        $model->update($id, $task);
        
        $response = array();
        $response["success"] = "True";
        
        echo json_encode($response);
    }

    private function returnJsonField($id, $fieldName){
        if($id == ""){
            return array();
        }else{
            $model = new TaskboardModel();
            $task = $model->find($id);
            $existingValue = $task[$fieldName];
    
            if($existingValue == null){
                $existingValue = array();
            }else{
                $existingValue = json_decode($existingValue, true);
            }
    
            return $existingValue;
        }
        
    }

    public function addComment(){
        
        $jsonComment["comment"] = $this->request->getVar('comment');
        $jsonComment["timestamp"] = gmdate("Y-m-d H:i:s");
        $jsonComment["by"] = session()->get('name');

        $id = $this->request->getVar('id');
        $model = new TaskboardModel();
        $task = $model->find($id);
        $existingComments = $task['comments'];

        if($existingComments == null){
            $existingComments = array();
        }else{
            $existingComments = json_decode($existingComments, true);
        }
        array_push($existingComments, $jsonComment);

        $updatedTask = [
            "comments" => json_encode($existingComments)
        ];

        $model->update($id, $updatedTask);

        $response = array();
        $response["success"] = "True";
        $response['jsonComment'] = json_encode($jsonComment);

        echo json_encode($response);
    }

    public function deleteTask(){
        
        $id = $this->request->getVar('id');
        $this->isAuthorized($id);
        $model = new TaskboardModel();
        $task = $model->find($id);

        if($task["attachments"] != null){
            $attachments = json_decode($task["attachments"], true);
            foreach($attachments as $attachment){
                $this->deleteAttachments($attachment["link"]);
            }

        }

        $model->delete($id);

        $response = array('success' => "True");
        
        echo json_encode($response);       
    }

    private function deleteAttachments($imagePath){
        $existingFile = ltrim($imagePath, '/'); 
                
        if(file_exists($existingFile)){
            unlink($existingFile);
        }
    }



}