<?php namespace App\Controllers;

use App\Models\SettingsModel;
use App\Models\TeamModel;

class Settings extends BaseController
{
    public function index()
    {
        $data = [];

        $data['pageTitle'] = 'Settings';
        $data['addBtn'] = false;
        $data['backUrl'] = '/projects';

        $model = new SettingsModel();
        $data['dropdownData'] = $model->where('type', 'dropdown')->findAll();		
        $data['configData'] = $model->where('type', 'url')->findAll();	
        $data['propertiesData'] = $model->where('type', 'properties')->findAll();

        $teamModel = new TeamModel();
        $data['teamMembers'] = $teamModel->getMembers();

        echo view('templates/header');
        echo view('templates/pageTitle', $data);
        echo view('Admin/Settings/list', $data);
        echo view('templates/footer');

    }

    public function addEnums()
    {
        if (session()->get('is-admin')) {
            if ($this->request->getMethod() == 'post') {
                $id = $this->request->getVar('id');
                $identifier = $this->request->getVar('identifier');
                $options =  $this->request->getVar('options');

                $rules = [
                    "id" => "required",
                    "identifier" => 'required',
                    "options" => 'required',
                ];
                $validation =  \Config\Services::validation();
                $validation->setRules($rules);

                if (! $this->validate($rules)) {
                    echo json_encode($validation->getErrors());
                }else{
                    $newData = ["id" => $id, "identifier" => $identifier, "options" => $options];
                    $model = new SettingsModel();
                    $model->save($newData);
                    $response = array('success' => "True");
                    echo json_encode($response);
                }
            }
        } else {
            $response = array('success' => "False");
            $response["error"] = "You are not authorized to perform this task.";
            echo json_encode($response);
        }
    
    }
    
    public function updateRequirementValues(){
        if (session()->get('is-admin')) {
            if ($this->request->getMethod() == 'post') {
                $keyId = $this->request->getVar('key');
                $keyValue = $this->request->getVar('value');
                $keyisRoot = ($this->request->getVar('isRoot') == 'false') ? false : true;
                
                $model = new SettingsModel();
                $data = $model->where('identifier', 'requirementsCategory')->findAll();	
                $dataList = $data; 	
                $data = json_decode($dataList[0]['options'], true);
                foreach($data as $key=>$val){
                    if($val['key'] == $keyId && $val['value'] == $keyValue) {
                        $data[$key]['isRoot'] = $keyisRoot;
                    }
                }
                $updateOptions = json_encode($data);
                $newData = ["id" => $dataList[0]['id'], "identifier" => $dataList[0]['identifier'], "options" => $updateOptions];
                $model->save($newData);
                $response = array('success' => "True");
                echo json_encode($response);
            }
        } else {
            $response = array('success' => "False");
            $response["error"] = "You are not authorized to perform this task.";
            echo json_encode($response);
        }

    }

    

}
