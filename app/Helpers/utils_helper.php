<?php
    use App\Models\ProjectModel;
    
    function setPrevUrl($name, $vars){
        $session = session();
        if(isset($_SESSION['PREV_URL'])){
            $session->remove('PREV_URL');
        }

        $PREV_URL = [
            'name'  => $name,
            'vars'  => $vars
        ];

        $session->set('PREV_URL', $PREV_URL );
    }

    function getActiveProjectId()
    {

        $projectModel = new ProjectModel();
        $activeProject = $projectModel->where("status", "Active")->orderBy('start-date', 'desc')->first();

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

    function sendEmail($to, $cc, $subject, $message)
    {
		$email = \Config\Services::email();

        $from = getenv('email.SMTPUser');
		$email->setFrom($from, "DocsGo");
		$email->setTo($to);
		$email->setCC($cc);
		$email->setSubject($subject);
		$email->setMessage($message);

		if($email->send()){
			return true;
		}else{
			// $data = $email->printDebugger(['headers']);
			// print_r($data);
            return false;
		}
    }

?>