<?php namespace App\Controllers;

use App\Models\ProjectModel;
use App\Models\TeamModel;

class Projects extends BaseController
{
	public function index()
    {
        $data = [];
		$data['pageTitle'] = 'Projects';
		$data['addBtn'] = True;
		$data['addUrl'] = "/projects/add";

		$model = new ProjectModel();
		$view = $this->request->getVar('view');

		if($view == ''){
			$view = 'Active';			
		}

		$data['data'] = $model->where('status', $view)->orderBy('start-date', 'desc')->findAll();	
		$data['view'] = $view;
		
		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('Projects/list',$data);
		echo view('templates/footer');
	}


	public function add($id = 0){

		helper(['form']);
		$model = new ProjectModel();
		$teamModel = new TeamModel();
		$data = [];
		$data['pageTitle'] = 'Projects';
		$data['addBtn'] = False;
		$data['backUrl'] = "/projects";
		$data['statusList'] = ['Active', 'Completed'];
		
		$data['teamMembers'] = $teamModel->getManagers();	

		if($id == 0){
			$data['action'] = "add";
			$data['formTitle'] = "Add Project";
		}else{
			$data['action'] = "add/".$id;
			

			$data['project'] = $model->where('project-id',$id)->first();		
			$data['formTitle'] = $data['project']["name"];	
		}


		if ($this->request->getMethod() == 'post') {
			
			$rules = [
				'name' => 'required|min_length[3]|max_length[50]',
				'description' => 'max_length[500]',
				'version' => 'required|min_length[3]|max_length[10]',
				'start-date' => 'required',
				'status' => 'required',
			];	

			$newData = [
				'name' => $this->request->getVar('name'),
				'version' => $this->request->getVar('version'),
				'category' => $this->request->getVar('category'),
				'start-date' => $this->request->getVar('start-date'),
				'description' => trim($this->request->getVar('description')),
				'end-date' => $this->request->getVar('end-date'),
				'status' => $this->request->getVar('status'),
				'manager-id' => $this->request->getVar('manager-id'),
			];

			$data['project'] = $newData;

			if (! $this->validate($rules)) {
				$data['validation'] = $this->validator;
			}else{
				if($id > 0){
					$newData['project-id'] = $id;
					$message = 'Project successfully updated.';
				}else{
					$message = 'Project successfully added.';
				}
				$model->save($newData);
				$session = session();
				$session->setFlashdata('success', 'Project successfully added.');
				
			}
		}
		
		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('Projects/form', $data);
		echo view('templates/footer');
	}

	
	public function delete($id){
		if (session()->get('is-admin')){
			$model = new ProjectModel();
			$model->delete($id);
			$response = array('success' => "True");
			echo json_encode( $response );
		}else{
			$response = array('success' => "False");
			echo json_encode( $response );
		}
	}

	public function checkEmail(){

		$emailTitle = "Review Request";
		$emailBody = "Sudhanshu Tiwari has requested review of D-316.";
		$referenceLink = "https://info.viosrdtest.in/documents/add?id=316" ;
		$referenceLinkText = "D-316";
		$subject = 'Docsgo '.$emailTitle.'  '.$referenceLinkText;

		helper('Helpers\utils');
		$html = getEmailHtml($emailTitle, $emailBody, $referenceLink, $referenceLinkText,2);
		
		$to = "sidsid14@gmail.com";
		$cc = "sidsid14@gmail.com";
		// echo "THis is working";
		// echo getenv('email.protocol');
		// helper('Helpers\utils');
		sendEmail($cc,$cc,$subject, $html);
	}


}