<?php namespace App\Controllers;

use App\Models\SettingsModel;
use CodeIgniter\I18n\Time;
use TP\Tools\Pandoc;
use TP\Tools\PandocExtra;
use App\Models\ProjectModel;
use App\Models\DocumentModel;
use PhpOffice\PhpWord\Shared\ZipArchive;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

function myCustomErrorHandler(int $errNo, string $errMsg, string $file, int $line) {
	echo $errMsg;
	alert($errMsg);
	exit;
	return false;
}
set_error_handler('App\Controllers\myCustomErrorHandler');

class GenerateDocuments extends BaseController
{
	
	public function index(){
		$this->downloadDocuments();
	}

	public function downloadDocuments()  {
		$pandoc = new Pandoc();
		ini_set("display_errors", "1");
		error_reporting(E_ALL);

		$params = $this->returnParams();
		$typeNumber = $params[0];
		switch($typeNumber) {
			case 1:
				$type = 'document';
				break;
			case 2:
				$type = 'project';
				break;
			case 3:
				$type = 'document';
				break;
		}
		$main_id = $params[1];

		$model = new DocumentModel();
		$str = $model->getDocumentsData($type, $main_id); 
		if(isset($str) && count($str) == 0) {
			echo "no data";
			return false;
		}
		//Fetching the doc-properties(Global seetings) from Model
		$settingsModel = new SettingsModel();
		$documentProperties = $settingsModel->getSettings("documentProperties");
		$documentProperties = json_decode($documentProperties[0]['options'], true);
		$documentTitle = ''; $documentIcon = ''; $documentFooterMsg='';
		foreach($documentProperties as $key => $val){
			if($val['key'] == "docTitle"){
				$documentTitle = $val["value"];
			}
			if($val["key"] == "docIcon"){
				$documentIcon = $val["value"];
			}
			if($val["key"] == "docConfidential"){
				$documentFooterMsg = $val["value"];
			}
		}

		$jsonGetId = $str;
		
		$idArray = array_keys($jsonGetId);
		$count = 0;

		foreach ($idArray as $id) {
			$jsonMain = $str;
			$fileNameLev1 = str_replace(",", "_", $jsonMain[$id]['file-name'] . ".docx");
			$fileName = str_replace(" ", "_", $fileNameLev1);
			$fileName = str_replace("&", "_", $fileName);
			$fileName = str_replace("/", "_", $fileName);
			$jsonObj = json_decode($jsonMain[$id]['json-object'], true);
			$documentType = array_keys($jsonObj);
			$json = $jsonObj[$documentType[0]];

			// Creating the new document...
			$phpWord = new \PhpOffice\PhpWord\PhpWord();
			$phpWord->getSettings()->setUpdateFields(true);

			$section = $phpWord->addSection();

			//Applying the paragraph styles...
			$phpWord->addTitleStyle(1, array('name' => $json['section-font'], 'size' => $json['section-font-size'], 'bold' => TRUE));
			$phpWord->setDefaultParagraphStyle(
					array(
						'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH,
						'spaceAfter' => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(0),
						'spacing' => 100,
					)
			);
			$multilevelNumberingStyleName = 'multilevel';
			$phpWord->addNumberingStyle(
					$multilevelNumberingStyleName, array(
				'type' => 'multilevel',
				'levels' => array(
					array('format' => 'decimal', 'text' => '%1.', 'left' => 500, 'hanging' => 360, 'tabPos' => 500),
					array('format' => 'upperLetter', 'text' => '%2.', 'left' => 720, 'hanging' => 360, 'tabPos' => 720),
				),
					)
			);
		
			//Header for all pages
			$subsequent = $section->addHeader();
			$documentIconImage = $documentIcon; 
			if($documentIconImage == ''){
				if (trim($json['cp-icon']) != "") {
					$documentIconImage = $json['cp-icon'];
				}else{
					$documentIconImage = base_url().'/assets/images/vios_logo.jpg';
				}
			}
			if($documentFooterMsg == ''){
				$documentFooterMsg = 'Murata Vios CONFIDENTIAL';
			}
		
			//Footer for all pages
			$footer = $section->addFooter();
			$footer->addPreserveText($documentFooterMsg.'                              Page {PAGE} of {NUMPAGES}                             ' . $json['cp-line4'] . ' ' . $json['cp-line5'], null, array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT));

			//#-2: Adding iamge at first page header line
			$section->addTextBreak();
			$section->addTextBreak();
			$path = base_url().'/assets/images/vios_logo.jpg';
			$section->addImage($documentIconImage, array('width' => 150, 'height' => 100, 'align' => 'center'));
			$section->addTextBreak();
			$section->addTextBreak();
			

			// Inline font style
			$fontStyle['name'] = 'Arial';
			$fontStyle['size'] = 16;
			$fontStyle['bold'] = TRUE;

			$cp_line3 = $json['cp-line3'];
			$fontStyle['name'] = 'Arial';
			$fontStyle['size'] = 16;
			$fontStyle['bold'] = TRUE;		
			$firstHeaderStyles = array( 
				'spaceBefore' => 215, 'spaceAfter' => 8, 'align' => 'center'
			);
			$DocID = 'Doc ID: '.$json['cp-line4'];
			$section->addText($json['cp-line3'], $fontStyle, $firstHeaderStyles);
			$section->addText($DocID, $fontStyle, $firstHeaderStyles);
			$section->addTextBreak();
			$section->addTextBreak();
			$section->addTextBreak();

			$fontStyle['name'] = $json['section-font'];
			$fontStyle['size'] = $json['section-font-size'];
			$fontStyle['bold'] = TRUE;
			$section->addText('Change History', $fontStyle);

			$fontStyle['name'] = $json['section-font'];
			$fontStyle['size'] = $json['section-font-size'];
			$fontStyle['bold'] = FALSE;

			$tableContent = $pandoc->convert($json['cp-change-history'], "gfm", "html5");
			$tableContent = $this->addTableStylesToContent($tableContent);
			\PhpOffice\PhpWord\Shared\Html::addHtml($section, $tableContent, false, false);

			$section->addTextBreak();

			$section = $phpWord->addSection();
			$fontStyle12 = array('spaceAfter' => 60, 'size' => $json['section-font']);
			$fontStyle10 = array('size' => 10);
			$phpWord->addTitleStyle(null, array('size' => $json['section-font'], 'bold' => true));
			$phpWord->addTitleStyle(1, array('size' => $json['section-font'], 'color' => '333333', 'bold' => true));
			$phpWord->addTitleStyle(2, array('size' => $json['section-font'], 'color' => '666666'));
			$phpWord->addTitleStyle(3, array('size' => $json['section-font'], 'italic' => true));
			$phpWord->addTitleStyle(4, array('size' => $json['section-font']));
			// Add text elements
			$section->addText('TABLE OF CONTENTS', $fontStyle, ['align' => \PhpOffice\PhpWord\Style\Cell::VALIGN_CENTER]);
			// $section->addTitle('TABLE OF CONTENTS', 0);
			$section->addTextBreak(2);

			// Add TOC...
			$toc = $section->addTOC($fontStyle12, 'tabLeader');
			$section->addTextBreak(2);

			$section = $phpWord->addSection();
			try{
				for ($i = 0; $i < count($json['sections']); $i++) {
					$section->addTitle($i + 1 . ". " . strtoupper($json['sections'][$i]['title']));
					$contentSection = '<b></b>';
					$org = $json['sections'][$i]['content'];
					$contentSection = $pandoc->convert($org, "gfm", "html5");
					//DONT DELETE THE BELOW CODE< HANDLING MULTIPLE SECNARIOS FOR DOC RENDER VIEWS|ISSUES
					/*
					if($json['sections'][$i]['content'] != ''){
						if ((strpos($json['sections'][$i]['title'], 'Risk Assessment') !== false) || (strpos($json['sections'][$i]['title'], 'Risk Management') !== false)){
							$org = $json['sections'][$i]['content'];
							$org = str_replace("<br>", "", $org);
							$org = str_replace("<br/>", "", $org);
							if( (strpos($org, '```') !== false) || (strpos($org, '``') !== false) || (strpos($org, '````') !== false)){
								$org = handleCodeblocks($org);
							}
							$contentSection = $pandoc->convert($org, "gfm", "html5");
						}else{
							$org = $json['sections'][$i]['content'];
							if((strpos($org, '```') !== false) || (strpos($org, '``') !== false) || (strpos($org, '````') !== false)){
								$org = handleCodeblocks($org);
							}
							$org = htmlspecialchars($org);
							$contentSection = $pandoc->convert($org, "gfm", "html5");	
						}
					}
					*/
					if (strpos($contentSection, '<table>') !== false) {
						$tableContentFormatted = $this->addTableStylesToContent($contentSection);
						//setOutputEscapingEnabled is added for gfm markdown
						\PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
						\PhpOffice\PhpWord\Shared\Html::addHtml($section, $tableContentFormatted, false, false);
					} else {
						\PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
						\PhpOffice\PhpWord\Shared\Html::addHtml($section, $contentSection, false, false);
					}
					
					$section->addTextBreak();
				}
			}
			catch (Error $e) {
				echo "Error caught: " . $e->getMessage();
				return false;
		  	}
			try{
				// Saving the document as OOXML file...
				\PhpOffice\PhpWord\Settings::setCompatibility(false);
				$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
				ob_clean();
				$count++;
			}
			catch (Error $e) {
				echo "Error caught: " . $e->getMessage();
				return false;
			}
			try{
				if ($type == "project") {
					$objWriter->save($fileName);
					$directoryName = "Project_Documents";
					if (!is_dir($directoryName)) {
						mkdir($directoryName, 0777);
					}
					rename($fileName, $directoryName . '/' . $fileName);
					if (count($idArray) == $count) {
						$zip_file = $directoryName .'_'.$main_id.'.zip';
						$rootPath = realpath($directoryName);
						// / Initiate a new instance of ZipArchive  
						$zip = new ZipArchive();  
						$res = $zip->open($zip_file, ZipArchive::CREATE);
						if ($zip->open($zip_file, ZipArchive::CREATE)) {
							$files = new RecursiveIteratorIterator(
								new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY
							);
							foreach ($files as $name => $file) {
								if (!$file->isDir()) {
									$filePath = $file->getRealPath();
									$relativePath = substr($filePath, strlen($rootPath) + 1);
									$zip->addFile($filePath, $relativePath);
									$filesToDelete[] = $filePath;
								}
							}
							$zip->close();
							header('Content-Description: File Transfer');
							header('Content-Type: '.mime_content_type($zip_file).'');
							header("Content-Disposition: attachment; filename=\"".basename($zip_file)."\";");
							header('X-Sendfile: '.$zip_file);
							header('Content-Transfer-Encoding: binary');
							header("Cache-Control: no-cache");
							header('Content-Length: ' . filesize($zip_file));
							//removing the document files
							if (is_dir($directoryName)) {
								foreach ($filesToDelete as $file) {
									unlink($file);
								}
								rmdir($directoryName);
							}
							readfile($zip_file);
						}else{
							echo "unable to create zip file";
						}
					}
				}else{
					$rootDirName = $_SERVER['DOCUMENT_ROOT'];
					$directoryName = "Project_Documents_".$str[0]['project-id'];		
					if (!is_dir($directoryName)) {
						mkdir($directoryName, 0777);
					}
					$objWriter->save($directoryName.'/'.$fileName);
					if($typeNumber == 1){
						header("Cache-Control: no-cache");
						header("Content-Description: File Transfer");
						header("Content-Disposition: attachment; filename=".$fileName);
						header("Content-Transfer-Encoding: binary");  
						readfile($directoryName.'/'.$fileName);
						unlink($directoryName.'/'.$fileName);
					}else{
						$outputFileName = str_replace("docx", "html", $fileName);
						$cmd = "pandoc --extract-media ./media '".$directoryName."/".$fileName."' --metadata title='vios' -s -o ".$outputFileName;
						$html = shell_exec($cmd);
						$html = file_get_contents($outputFileName);
						$html = $this->addTableStylesToContent($html);
						$html = $this->addImagePaths($html, $documentTitle);
						echo $html;
						unlink($directoryName.'/'.$fileName);
						unlink($outputFileName);
						return false;
					}
				}
			}
			catch (Error $e) {
				echo "Error caught: " . $e->getMessage();
				return false;
			}
				
		}
	}

	function sectionNumber($sectionStr) {
		return (int) filter_var($sectionStr, FILTER_SANITIZE_NUMBER_INT);
	}

	function addTableStylesToContent($rawContent) {
		$fontFamily = 'Arial, sans-serif';
		$fontSize = '11';
		$replaceContent = str_replace("<table>", '<table class="pandoc-mark-css" style="border-spacing: 0 10px; layout: fixed; font-family:' . $fontFamily . '; font-size: ' . $fontSize . ';width: 100%; table-layout: fixed; word-wrap: break-word; padding: 10px; border: 1px #000000 solid; border-collapse: collapse;" border="1" cellpadding="5">', $rawContent);
		$replaceContent = str_replace("<th>", "<th style='padding-top: 8px;font-weight: bold; height: 50px;text-align: center; background-color:#cbebf2;'>", $replaceContent);
		$replaceContent = str_replace("<br/>", " <br/> ", $replaceContent);
		$replaceContent = str_replace("</table>", " </table><br/> ", $replaceContent);
		return $replaceContent;
	}

	function addImagePaths($content, $title) {
		$url = base_url().'/media/media';
		$content = str_replace("./media/media", $url, $content);
		if($title !='' && $title != null){
			$content = str_replace('<header id="title-block-header">', '<header id="title-block-header-display" style="display: none">', $content);
		}else{
			$content = str_replace('<header id="title-block-header">', '<header id="title-block-header" style="display: none">', $content);
		}
		$content = str_replace('<h1 id=', '<h1 style="font-size: x-large;font-weight: bold;" id=', $content);
		return $content;
	}

	function handleCodeblocks($content) {
		$content = str_replace("```", "", $content);
		$content = str_replace("````", "", $content);
		$content = str_replace("``", "", $content);
		return $content;
	}

	private function returnParams(){
		$uri = $this->request->uri;
		$id = $uri->getSegment(3);
		$type = $uri->getSegment(4);
		return [$id, $type];
	}

	public function checkGenerateDocuments(){
		$projectId = $this->returnProjectID();
		$model = new ProjectModel();
		$pathList = $model->select('download-path')->where('project-id', $projectId)->first();
		$pathList = json_decode($pathList['download-path'], true);

		if($pathList == "" || $pathList == null || $pathList == 'null'){
			//JSON not available, Goto fresh download
			$response = array('success' => "False", "description"=>'No downloads available');
			echo json_encode( $response );	
		}else{
			//JSON is available, check all document's update-date is lowerthan the zipfile timestamp
			$current_date =  gmdate("Y-m-d H:i:s");
			$json_time = $pathList['timeStamp'];
			$data = $model->getDownloadedProjectStatus($projectId, $json_time);
			$zip_file = "Project_Documents_".$projectId.".zip";
			if($data[0]['count'] > 0){
				//JSON aviable, but its old one, so delete and goto fresh download
				if(is_file($zip_file))
					unlink($zip_file);
				$res = $model->updateGenerateDocumentPath($projectId, NULL);
				$response = array('success' => "False", "description"=>"Download is deprecated");
				echo json_encode( $response );	
			}else{
				//JSON avilable, no need to download new, use existing one
				header('Content-Description: File Transfer');
				header('Content-Type: '.mime_content_type($zip_file).'');
				header("Content-Disposition: attachment; filename=\"".basename($zip_file)."\";");
				header('X-Sendfile: '.$zip_file);
				header('Content-Transfer-Encoding: binary');
				header("Cache-Control: no-cache");
				header('Content-Length: ' . filesize($zip_file));
				readfile($zip_file);
			}
		}
	}

	public function updateGenerateDocumentPath() {
		$projectId = $this->returnProjectID();
		$current_date =  gmdate("Y-m-d H:i:s");
		$downloadZipFileName = "Project_Documents_".$projectId.".zip";
		$json_data = array("timeStamp"=>$current_date, "filePath"=>$downloadZipFileName);

		$model = new ProjectModel();
		$res = $model->updateGenerateDocumentPath($projectId, json_encode($json_data));
		$response = array('success' => "True", "description"=>"Link Updated");
		echo json_encode( $response );	
	}

	
	public function returnProjectID(){
		$uri = $this->request->uri;
		$id = $uri->getSegment(3);
		return $id;
	}
	
}