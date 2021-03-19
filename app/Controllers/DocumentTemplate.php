<?php namespace App\Controllers;

use App\Models\DocumentTemplateModel;
use App\Models\SettingsModel;

class DocumentTemplate extends BaseController
{
	
	public function index()
    {
        $data = [];
		$data['pageTitle'] = 'Templates';
		$data['addBtn'] = True;
		$data['addUrl'] = "/documents-templates/add";
		

		$model = new DocumentTemplateModel();
		$data['data'] = $model->orderBy('name')->findAll();	

		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('DocumentTemplates/list',$data);
		
		echo view('templates/footer');
	}
	
	private function returnParams(){
		$uri = $this->request->uri;
		$id = $uri->getSegment(3);
		if($id != ""){
			$id = intval($id);
		}
		return $id;
	}

	public function addTemplate(){
		if ($this->request->getMethod() == 'post') {
			$id = $this->request->getVar('id');
			$name = $this->request->getVar('name');
			$type = $this->request->getVar('type');
			$json = $this->request->getVar('template-json-object');
			
			$newData = [
				'name' => $name ,
				'type' => $type,
                'template-json-object' => $json,
			];
			
			if($id != ""){
				$newData["id"] = $id;
			}

			$model = new DocumentTemplateModel();
			$model->save($newData);

			$newRecord = $model->where('type',$type)->first();

			$response = array('success' => "True");
			$response['id'] = $newRecord['id'];
			
			echo json_encode( $response );
		}
	}

	public function add(){
		$id = $this->returnParams();
		helper(['form']);
		$model = new DocumentTemplateModel();
		$data = [];
		$data['pageTitle'] = 'Templates';
		$data['addBtn'] = False;
		$data['backUrl'] = "/documents-templates";
		// $data['existingTypes'] =  join(",",$model->getTypes());
		$existingTypes = $model->getTypes();
		$data['existingTypes'] = implode(",", array_keys($existingTypes));

		$settingsModel = new SettingsModel();
		 $templateCategory = $settingsModel->where("identifier","templateCategory")->first();
		
		 if($templateCategory["options"] != null){
			$data["templateCategory"] = json_decode( $templateCategory["options"], true );
		 }else{
			$data["templateCategory"] = [];
		 }
		 
		if($id == ""){
			$data['action'] = "add";
			$data['formTitle'] = "Add Template";
		}else{
			$data['action'] = "add/".$id;

			$documentTemplate = $model->where('id',$id)->first();	
			$data['documentTemplate'] = $documentTemplate;
			$data['formTitle'] = $documentTemplate['name'];
			$template = json_decode($data['documentTemplate']["template-json-object"], true);		
			$data['template'] = $template[$documentTemplate['type']];
		}

		$data['tablesLayout'] = json_encode($this->returnTablesLayout());
		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('DocumentTemplates/form', $data);
		echo view('templates/footer');
	}

	private function returnTablesLayout(){
		$tables = array();
		// There should be no spaces between column value names
		$tables['Acronyms']['name'] = "acronyms";
		$tables['Acronyms']['columns'] = "acronym,description";
		$tables['Documents']['name'] = "documents";
		$tables['Documents']['columns'] = "file-name,author";
		$tables['References']['name'] = "documentMaster";
		$tables['References']['columns'] = "reference,name,category,description,location,status,version";
		$tables['Requirements']['name'] = "requirements";
		$tables['Requirements']['columns'] = "description,requirement,type,update_date";
		$tables['Reviews']['name'] = "reviews";
		$tables['Reviews']['columns'] = "review-name,context,description,review-ref,status,project-name,reviewer,author";
		$tables['RiskAssessment']['name'] = "riskAssessment";
		$tables['RiskAssessment']['columns'] = "risk_type,risk,description,component,failure_mode,harm,cascade_effect,hazard-analysis,assessment,baseScore_severity,status";
		$tables['Teams']['name'] = "teams";
		$tables['Teams']['columns'] = "name,email,responsibility,role";
		$tables['TraceabilityMatrix']['name'] = "traceabilityMatrix";
		$tables['TraceabilityMatrix']['columns'] = "cncr,system,subsysreq,design,code,testcase";
		

		return $tables;
	}
	
	
	public function delete(){
		if (session()->get('is-admin')){
			$uri = $this->request->uri;
			$id = $uri->getSegment(3);

			$model = new DocumentTemplateModel();
			$model->delete($id);
			$response = array('success' => "True");
			echo json_encode( $response );
		}
		else{
			$response = array('success' => "False");
			echo json_encode( $response );
		}
	}


}