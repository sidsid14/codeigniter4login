<?php namespace App\Controllers;

use App\Models\InventoryMasterModel;
use CodeIgniter\I18n\Time;
use App\Models\TeamModel;
use App\Models\SettingsModel;

class InventoryMaster extends BaseController
{
	public function index()
    {
		$data = [];

		$data['pageTitle'] = 'Assets';
		$data['addBtn'] = true;
		$data['addUrl'] = "/inventory-master/add";
		$data['backUrl'] = '/inventory-master';

		$model = new InventoryMasterModel();
		$view = $this->request->getVar('view');
		if($view == '')
			$view = 'active';

		$data['data'] = $model->where('status', $view)->orderBy('id', 'desc')->findAll();	
		$data['today_date'] = gmdate("Y-m-d H:i:s");
		$data['statusList'] = ['active', 'in-active', 'not-found', 'cal-overdue'];
		$teamModel = new TeamModel();
		$data['teamMembers'] = $teamModel->getMembers();
		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('InventoryMaster/list',$data);
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

	private function getAssetsCategoryEnums() {
		$settingsModel = new SettingsModel;
		$assetsCategory = $settingsModel->where("identifier","assetsCategory")->first();
		if($assetsCategory["options"] != null){
			$dataList = json_decode( $assetsCategory["options"], true );
			$assetsCategory = [];
			foreach($dataList as $key=>$list){
				$assetsCategory[] = $dataList[$key];
			}
		}else{
			$assetsCategory = [];
		}
		return $assetsCategory;
	}

	public function add(){
		$backUrl = '/inventory-master';
		if(isset($_SERVER['HTTP_REFERER'])){
			$urlStr = $_SERVER['HTTP_REFERER'];
			if (strpos($urlStr, 'view')) {
				$urlAr = explode("=", $urlStr);
				$backUrl = '/inventory-master?view='.$urlAr[count($urlAr)-1];
			}
		}
		$id = $this->returnParams();
		helper(['form']);
		$model = new InventoryMasterModel();
		$data = [];
		$data['pageTitle'] = 'Assets';
		$data['addBtn'] = False;
		$data['backUrl'] = $backUrl;
		$dataList = [];
		$data['statusList'] = ['active', 'in-active', 'not-found', 'cal-overdue'];
		$date['today_date'] = gmdate("Y-m-d H:i:s");
		$teamModel = new TeamModel();
		$data['teamMembers'] = $teamModel->getMembers();
		$data['assetsCategory'] = $this->getAssetsCategoryEnums();

		$rules = [
			'type' => 'required|max_length[25]',
			'description' => 'required|max_length[500]',
			'make' => 'required|max_length[25]',
			'model' => 'required|max_length[25]',
			'serial' => 'required|max_length[25]',
		];	

		if($id == ""){
			$data['action'] = "add";
			$data['formTitle'] = "Add Assets";
		}else{
			$data['action'] = "add/".$id;
			$data['formTitle'] = "Update Assets";
			$data['member'] = $model->where('id',$id)->first();	
		}

		if ($this->request->getMethod() == 'post') {
			/* Checking the 'cal_date', if its exceeds the current date then status should be 'cal-overdue' */
			$status = $this->request->getVar('status');
			if((int)$this->request->getVar('cal_date')){
				$cal_date = strtotime($this->request->getVar('cal_date')) + (330*60);
				$today = strtotime(gmdate("Y-m-d")) + (330*60);
				if($cal_date < $today){
					$status = $data['statusList'][3];
				}
			}
			if($status == ''){
				$status = 'active';
			}
			$newData = [
				'item' => $this->request->getVar('item'),
				'model' => $this->request->getVar('model'),
				'type' => $this->request->getVar('type'),
				'description' => $this->request->getVar('description'),
				'make' => $this->request->getVar('make'),
				'model' => $this->request->getVar('model'),
				'serial' => $this->request->getVar('serial'),
				'entry_date' => $this->request->getVar('entry_date'),
				'retired_date' => $this->request->getVar('retired_date'),
				'cal_date' => $this->request->getVar('cal_date'),
				'cal_due' => $this->request->getVar('cal_due'),
				'location' => $this->request->getVar('location'),
				'invoice' => $this->request->getVar('invoice'),
				'invoice_date' => $this->request->getVar('invoice_date'),
				'vendor' => $this->request->getVar('vendor'),
				'status' => $status,
				'used_by' => $this->request->getVar('used_by'),
				'updated_by' => $this->request->getVar('updated_by'),
				'update_date' => gmdate("Y-m-d H:i:s")
			];
			$data['member'] = $newData;
			if (! $this->validate($rules)) {
				$data['validation'] = $this->validator;
			}else{
				if($id > 0){
					$newData['id'] = $id;
					$newData['update_date'] = gmdate("Y-m-d H:i:s");
					$message = 'Assets successfully updated.';
				}else{
					$message = 'Assets successfully added.';
				}
				$model->save($newData);
				$session = session();
				$session->setFlashdata('success', $message);
			}
		}

		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('InventoryMaster/form', $data);
		echo view('templates/footer');
	}

	public function delete(){
		if (session()->get('is-admin')){
			$id = $this->returnParams();
			$model = new InventoryMasterModel();
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