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

    function getEmailHtml($emailTitle, $emailBody, $referenceLink, $referenceLinkText, $templateType = 2){
       
		$baseUrl = base_url();
		$logo = "http://info.viosrdtest.in/Docsgo-Logo.png";
		
        if($templateType == 1){
            $templatePath = './templates/email/email_1.html';
            $image_1 =  $baseUrl."/templates/images/template1_image_1.png";
            $image_2 =  $baseUrl."/templates/images/template1_image_2.png";
        }else{
            $templatePath = './templates/email/email_2.html';
            $image_1 =  $baseUrl."/templates/images/template2_image_1.png";
        }
       
        $doc = new \DOMDocument();

        libxml_use_internal_errors(true);
        $doc->loadHTMLFile($templatePath);
        libxml_use_internal_errors(false);

        $img = $doc->getElementById('logo');
		$img->setAttribute('src', $logo);

		$img = $doc->getElementById('image_1');
		$img->setAttribute('src', $image_1);

        if($templateType == 1){
	        $img = $doc->getElementById('image_2');
		    $img->setAttribute('src', $image_2);
        }
        
		$title = $doc->getElementById('title');
		$title->nodeValue = $emailTitle;

		$message_body = $doc->getElementById('message_body');
		$message_body->nodeValue = $emailBody;
		
		$link = $doc->getElementById('link');
		$link->setAttribute('href', $referenceLink);
		
		$link_text = $doc->getElementById('link_text');
		$link_text->nodeValue = $referenceLinkText;

		$html = $doc->saveHTML();
		
		return $html;
    }

?>