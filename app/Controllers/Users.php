<?php namespace App\Controllers;

// use App\Models\UserModel;
use App\Models\TeamModel;
use TP\Tools\Openfire;

class Users extends BaseController
{
	public function index()
	{
		$data = [];
		helper(['form']);


		if ($this->request->getMethod() == 'post') {
			//let's do the validation here
			$rules = [
				'email' => 'required|min_length[6]|max_length[50]|valid_email',
				'password' => 'required|min_length[8]|max_length[255]|validateUser[email,password]',
			];

			$errors = [
				'password' => [
					'validateUser' => 'Email or Password don\'t match'
				]
			];

			if (! $this->validate($rules, $errors)) {
				$data['validation'] = $this->validator;
			}else{
				$model = new TeamModel();

				$user = $model->where('email', $this->request->getVar('email'))
											->first();

				$this->setUserSession($user);
				return redirect()->to('projects');

			}
		}

		echo view('templates/header', $data);
		echo view('login');
		echo view('templates/footer');
	}

	private function setUserSession($user){
		$data = [
			'id' => $user['id'],
			'name' => $user['name'],
			'email' => $user['email'],
			'is-admin' => $user['is-admin'],
			'is-manager' => $user['is-manager'],
			'isLoggedIn' => true,
		];

		session()->set($data);
		return true;
	}

	public function profile(){
		
		$data = [];
		$data['pageTitle'] = 'My Profile';
		$data['addBtn'] = False;
		$data['backUrl'] = "/";
		helper(['form']);
		$model = new TeamModel();

		if ($this->request->getMethod() == 'post') {
			//let's do the validation here
			$rules = [
				'name' => 'required|min_length[3]|max_length[50]',
				];

			if($this->request->getPost('password') != ''){
				$rules['password'] = 'required|min_length[8]|max_length[255]';
				$rules['password_confirm'] = 'matches[password]';
			}

			if (! $this->validate($rules)) {
				$data['validation'] = $this->validator;
			}else{

				$newData = [
					'id' => session()->get('id'),
					'name' => $this->request->getPost('name'),
				];
				if($this->request->getPost('password') != ''){
					$newData['password'] = $this->request->getPost('password');
				}

				$model->save($newData);

				if(getenv('OF_ENABLED') == "true"){
					$this->updateOpenfireUser($newData);
				}

				session()->setFlashdata('success', 'Successfuly Updated');

				return redirect()->to('/profile');
			}
		}

		$data['user'] = $model->where('id', session()->get('id'))->first();
		echo view('templates/header', $data);
		echo view('templates/pageTitle', $data);
		echo view('profile');
		echo view('templates/footer');
	}

	private function updateOpenfireUser($user){
		$user['email'] = session()->get('email');
		$username = substr($user['email'], 0, strrpos($user['email'], '@'));
		$userDetails = [
			"email" => $user['email'],
			"name" => $user['name'], 
			"password" => $user['password'], 
			"username" => $username, 
		];
		
		$openfire = new Openfire();
		$openfire->updateUser($userDetails);
	}

	public function updateAdminStatus(){
		$response = array();
		if ($this->request->getMethod() == 'post') {
			$id = $this->request->getPost('id');
			$model = new TeamModel();
			$user = $model->find($id);
			// $user['is-admin'] = !$user['is-admin'];
			$model->updateAdminStatus($id, !$user['is-admin']);
			$response['success'] = 'true';
			echo json_encode($response);
	
		}else{
			$response['success'] = 'false';
		}
		
	}

	public function logout(){
		session()->destroy();
		return redirect()->to('/');
	}

}
