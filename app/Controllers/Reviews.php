<?php namespace App\Controllers;

use App\Models\DocumentModel;
use App\Models\ProjectModel;
use App\Models\ReviewModel;
use App\Models\SettingsModel;
use App\Models\TeamModel;

class Reviews extends BaseController
{
    public function index()
    {
        $data = [];
        $data['pageTitle'] = 'Review Register';
        $data['addBtn'] = true;
        $data['addUrl'] = "/reviews/add";

        $selectedStatus = null;
        $selectedProject = null;
        $selectedUser = null;

        if (isset($_SESSION['PREV_URL'])) {
            $prev_url = $_SESSION['PREV_URL'];
            if ($prev_url["name"] == "reviewsList") {
                $vars = $prev_url["vars"];
                $selectedStatus = $vars['view'];
                $selectedProject = $vars['project_id'];
                $selectedUser = isset($vars['user_id']) ? $vars['user_id'] : null;
            }
        }

        if($selectedUser == null ){
            $selectedUser = session()->get('id');
        }

        $teamModel = new TeamModel();
        $data['teamMembers'] = $teamModel->getMembers();

        $settingsModel = new SettingsModel();
        $documentStatusOptions = $settingsModel->getConfig("documentStatus");
        $data["reviewStatus"] = $documentStatusOptions; //Status Radio Buttons

        if ($selectedStatus == null) {
            if ($documentStatusOptions != null) {
                $selectedStatus = $documentStatusOptions[0]; //Default status
            } else {
                $selectedStatus = null;
            }
        }

        $projectModel = new ProjectModel();
        $data['projects'] = $projectModel->getProjects(); //Projects Dropdown

        if ($selectedProject == null) {
            $selectedProject = $this->getActiveProjectId(); //Default project
        }

        $reviewModel = new ReviewModel();
        $data['reviewsCount'] = $reviewModel->getReviewsCount($selectedProject, $selectedUser);

        $data['selectedProject'] = $selectedProject;
        $data['selectedStatus'] = $selectedStatus;
        $data['selectedUser'] = $selectedUser;
        // $this->updateReviewDescription();

        echo view('templates/header');
        echo view('templates/pageTitle', $data);
        echo view('Reviews/list', $data);
        echo view('templates/footer');
    }

    public function getReviewStats()
    {
        $project_id = $this->request->getVar('project_id');
        $selectedUser =  $this->request->getVar('user_id');

        $reviewModel = new ReviewModel();
        $reviewStats = $reviewModel->getReviewsCount($project_id, $selectedUser);
        $response["success"] = "True";
        $response["reviewStats"] = $reviewStats;

        echo json_encode($response);
    }

    private function getActiveProjectId()
    {

        $projectModel = new ProjectModel();
        $activeProject = $projectModel->where("status", "Active")->first();

        if ($activeProject != "") {
            return $activeProject['project-id'];
        } else {
            $activeProject = $projectModel->first();
            if ($activeProject != "") {
                return $activeProject['project-id'];
            } else {
                return null;
            }
        }
    }

    public function getReviews()
    {
        $view = $this->request->getVar('view');
        $project_id = $this->request->getVar('project_id');
        $user_id = $this->request->getVar('user_id');

        $vars['view'] = $view;
        $vars['project_id'] = $project_id;
        $vars['user_id'] = $user_id;

        helper('Helpers\utils');
        setPrevUrl('reviewsList', $vars);

        $userCondition = "";
        if($user_id != "ALL"){
            $userCondition = " AND ( `review-by` = ".$user_id." OR `assigned-to` = ".$user_id." ) ";
        }

        $model = new ReviewModel();
        $whereCondition = ' WHERE rev.`status` = "' . $view . '" AND proj.`project-id` = ' . $project_id.$userCondition;

        $data = $model->getMappedRecords($whereCondition);

        $response["success"] = "True";
        $response["reviews"] = $data;

        echo json_encode($response);

    }

    private function getProjects()
    {
        $projectModel = new ProjectModel();
        $data = $projectModel->findAll();
        $projects = [];
        foreach ($data as $project) {
            $projects[$project['project-id']] = $project['name'];
        }
        return $projects;
    }

    private function returnParams()
    {
        $uri = $this->request->uri;
        $id = $uri->getSegment(3);
        if ($id != "") {
            $id = intval($id);
        }
        return $id;
    }

    public function addDocReview()
    {
        if ($this->request->getMethod() == 'post') {
            $response = array();
            $response["success"] = "True";
            $currentTime = gmdate("Y-m-d H:i:s");

            $reviewId = $this->request->getVar('id');
            $status = $this->request->getVar('status');
            $commentId = $this->request->getVar('commentId');
            $message = $this->request->getVar('description');

            if($commentId == ""){
                $commentId = uniqid();
                $response["message"] = "Success! Comment added.";
            }else{
                $response["message"] = "Success! Comment updated.";
            }
            
            $reviewModel = new ReviewModel();

            if ($reviewId == "") {
                $data = [
                    "project-id" => $this->request->getVar('projectId'),
                    "review-name" => $this->request->getVar('reviewName'),
                    "category" => $this->request->getVar('category'),
                    "context" => $this->request->getVar('context'),
                    "description" => '',
                    "review-by" => $this->request->getVar('reviewBy'),
                    "assigned-to" => $this->request->getVar('assignedTo'),
                    "review-ref" => $this->request->getVar('reviewRef'),
                    'updated-at' => $currentTime,
                    "status" => $status,
                ];
                $reviewId = $reviewModel->insert($data);
            }

            list($comment, $comments) = $this->addToComments($reviewId, $commentId, $message);
                
            $response["comment"] = $comment;

            $commentsData = [
                "status" => $status,
                "description" => json_encode($comments),
            ];

            $reviewModel->update($reviewId, $commentsData);
           
            //Updating Document Revision

            $documentModel = new DocumentModel();

            $docId = $this->request->getVar('docId');
            $doc = $documentModel->where('id', $docId)->first();

            $revision["dateTime"] = $currentTime;
            $revision["who"] = session()->get("name");
            $revision["type"] = "Reviewed";
            $revision["log"] = "Review comments added.";

            if ($doc['status'] != $status) {
                $revision["log"] .= " Document status updated to " . $status;
            }
            $revisionHistory = $doc["revision-history"];
            if ($revisionHistory != null) {
                $revisionHistory = json_decode($revisionHistory, true);
            } else {
                $revisionHistory["revision-history"] = array();
            }
            array_push($revisionHistory["revision-history"], $revision);

            //Adding review id to the document
            $docData = [
                "review-id" => $reviewId,
                "status" => $status,
                'update-date' => $currentTime,
                'revision-history' => json_encode($revisionHistory),
            ];

            $documentModel->update($docId, $docData);

            
            $response['reviewId'] = $reviewId;
            $response["revisionHistory"] = json_encode($revisionHistory);
            $response['status'] = $status;

            echo json_encode($response);
        }
    }


    public function add()
    {
        $id = $this->returnParams();

        helper(['form']);
        $model = new ReviewModel();
        $data = [];
        $data['pageTitle'] = 'Review Register';
        $data['addBtn'] = false;
        $data['backUrl'] = "/reviews";

        $projectModel = new ProjectModel();
        $data['projects'] = $projectModel->getProjects();

        $teamModel = new TeamModel();
        $data['teamMembers'] = $teamModel->getMembers();
        // $data['reviewStatus'] = ['Request Change', 'Ready For Review', 'Accepted'];
        $settingsModel = new SettingsModel();
        $reviewStatus = $settingsModel->where("identifier", "documentStatus")->first();
        if ($reviewStatus["options"] != null) {
            $data["reviewStatus"] = json_decode($reviewStatus["options"], true);
        } else {
            $data["reviewStatus"] = [];
        }

        $reviewCategory = $settingsModel->where("identifier", "reviewCategory")->first();
        if ($reviewCategory["options"] != null) {
            $data["reviewCategory"] = json_decode($reviewCategory["options"], true);
        } else {
            $data["reviewCategory"] = [];
        }

        // $data['categoryList'] = ["User Needs", "Plan", "Requirements", "Design",
        //  "Code", "Verification", "Validation", "Release", "Risk Management", "Traceability"];

        if ($id == "") {
            $data['formTitle'] = "Add Review";
            //Add new form, auto fill the project,Name,Author fields
            $project_id = $this->request->getVar('project_id');
            if ($project_id == "null") {
                $session = session();
                $session->setFlashdata('alert', "danger");
                $session->setFlashdata('message', "Create a project first!");
                return redirect()->to('/reviews');
            }
            $data['project_id'] = $project_id;
            $data['project_name'] = $data['projects'][$project_id];
            $data['action'] = "add?project_id=${project_id}";
            $TeamModel = new TeamModel();
            $data['user'] = $TeamModel->where('id', session()->get('id'))->first();
            $data['review']['assigned-to'] = $data['user']['id'];
        } else {

            $data['action'] = "add/" . $id;

            $data['review'] = $model->where('id', $id)->first();
            $data['formTitle'] = 'R-' . $data['review']['id'] . " " . $data['review']['context'];
            //Update form, auto fill the project field
            $project_id = $data['review']['project-id'];
            $data['project_id'] = $project_id;
            $data['project_name'] = $data['projects'][$project_id];

            $data['nearByReviews'] = $this->getNearByReviews($data['review']['updated-at']);

        }
        $currentTime = gmdate("Y-m-d H:i:s");
        if ($this->request->getMethod() == 'post') {
            $rules = [
                "project-id" => 'required',
                "review-name" => 'required|max_length[64]',
                "assigned-to" => 'required',
                "context" => 'required|max_length[60]',
                "review-by" => 'required',
                "status" => 'required',
                "category" => 'required',
            ];

            $errors = [
                'review-by' => [
                    'required' => 'Select a reviewer',
                ],
            ];

            $newData = [
                "assigned-to" => $this->request->getVar('assigned-to'),
                "context" => $this->request->getVar('context'),
                "code-diff" => $this->request->getVar('code-diff'),
                "project-id" => $this->request->getVar('project-id'),
                "review-by" => $this->request->getVar('review-by'),
                "review-name" => $this->request->getVar('review-name'),
                "review-ref" => $this->request->getVar('review-ref'),
                "status" => $this->request->getVar('status'),
                'updated-at' => $currentTime,
                "category" => $this->request->getVar('category'),
            ];

            if (!$this->validate($rules, $errors)) {
                $data['validation'] = $this->validator;
            } else {
                $session = session();
                if ($id > 0) {
                    $newData['id'] = $id;
                    $model->save($newData);
                    $data['review'] = $model->where('id', $id)->first();
                    $message = 'Review successfully updated.';
                    $session->setFlashdata('success', $message);
                } else {
                    $reviewId = $model->insert($newData);
                    $message = 'Review successfully added.';
                    $session->setFlashdata('success', $message);
                    return redirect()->to('/reviews/add/' . $reviewId);
                }

            }
        }

        echo view('templates/header');
        echo view('templates/pageTitle', $data);
        echo view('Reviews/form', $data);
        echo view('templates/footer');
    }

    public function saveComment()
    {
		$response = array('success' => "True");

        $reviewId = $this->request->getVar('reviewId');
        $commentId = $this->request->getVar('commentId');
		$message = $this->request->getVar('message');
        $reviewStatus = $this->request->getVar('reviewStatus');
        
        if ($commentId != "") {
            $responseMessage = "Success! Comment updated.";

        } else {
            $commentId = uniqid();
            $responseMessage = "Success! Comment added.";
        }
        list($comment, $comments) = $this->addToComments($reviewId, $commentId, $message);

        $data = [
            "description" => json_encode($comments),
		];

		if($reviewStatus != ""){
			$data["status"] = $reviewStatus;
			$response["updateStatus"] = "True";
		}else{
			$response["updateStatus"] = "False";
		}
        $reviewModel = new ReviewModel();
        $reviewModel->update($reviewId, $data);
        
        $response["comment"] = $comment;
        $response["message"] = $responseMessage;

        echo json_encode($response);

    }

    private function addToComments($reviewId, $commentId, $message){

        $comment = array();
        $comment["id"] = $commentId;
        $comment["by"] = session()->get('name');
        $comment["message"] = $message;
        $comment["timestamp"] = gmdate("Y-m-d H:i:s");

        $reviewModel = new ReviewModel();
        $record = $reviewModel->where('id', $reviewId)->first();

        $comments = $record["description"];

        if ($comments == null) {
            $comments = array();
        } else {
            $comments = json_decode($comments, true);
        }

        $comments[$commentId] = $comment;

        uasort($comments, function ($a, $b) {
            $ad = new \DateTime($a['timestamp']);
            $bd = new \DateTime($b['timestamp']);

            if ($ad == $bd) {
                return 0;
            }

            return $ad < $bd ? -1 : 1;
        });

        return array($comment, $comments);
    }

    public function deleteComment()
    {
        $reviewId = $this->request->getVar('reviewId');
        $commentId = $this->request->getVar('commentId');

        $reviewModel = new ReviewModel();
        $record = $reviewModel->where('id', $reviewId)->first();

        $comments = $record["description"];
        $comments = json_decode($comments, true);
        unset($comments[$commentId]);

        if (count($comments)) {
            $comments = json_encode($comments);
        } else {
            $comments = null;
        }

        $data = [
            "description" => $comments,
        ];

        $reviewModel->update($reviewId, $data);

        $response = array('success' => "True");
        $response["commentId"] = $commentId;
        $response["message"] = "Success! Comment deleted.";

        echo json_encode($response);
    }

//One time use to convert text fields to json for review comments
    public function updateReviewDescription()
    {
        $reviewModel = new ReviewModel();
        $reviews = $reviewModel->getMappedRecords();
        foreach ($reviews as $index => $review) {
            $id = $review["id"];
            $message = $review["description"];
            $reviewer = $review["reviewer"];
            $timestamp = $review["updated-at"];
            if ($message != null) {
                $comment["id"] = uniqid();
                $comment["by"] = $reviewer;
                $comment["message"] = $message;
                $comment["timestamp"] = $timestamp;

                $comments = array();
                $comments[$comment["id"]] = $comment;
                $reviews[$index]["description"] = json_encode($comments);
                $reviewModel->save($reviews[$index]);
            }
        }
        return $reviews;
    }

    private function getNearByReviews($id)
    {
        $prevId = null;
        $nextId = null;

        if (isset($_SESSION['PREV_URL'])) {
            $prev_url = $_SESSION['PREV_URL'];
            if ($prev_url["name"] == "reviewsList") {
                $vars = $prev_url["vars"];
                $status = $vars['view'];
                $project_id = $vars['project_id'];
                $user_id = isset($vars['user_id']) ? ( ($vars['user_id'] != 'ALL') ? $vars['user_id'] : '' ): '';
                $model = new ReviewModel();
                $prevId = $model->getPrevReviewId($id, $project_id, $status, $user_id);
                $nextId = $model->getNextReviewId($id, $project_id, $status, $user_id);
            }
        }

        $rev["prevId"] = $prevId;
        $rev["nextId"] = $nextId;

        return $rev;
    }

    public function delete()
    {
        $id = $this->returnParams();

        $model = new ReviewModel();
        $model->delete($id);
        $response = array('success' => "True");
        echo json_encode($response);
    }

}
