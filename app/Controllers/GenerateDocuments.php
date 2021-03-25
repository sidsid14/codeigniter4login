<?php namespace App\Controllers;

use App\Models\SettingsModel;
use CodeIgniter\I18n\Time;
use TP\Tools\Pandoc;
use TP\Tools\PandocExtra;
use Mpdf;
use Mpdf\Config;
use DOMDocument;
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
		$doc = new DOMDocument();
		ini_set("display_errors", "1");
		error_reporting(E_ALL);

		$params = $this->returnParams();
		$typeOfRequest = $params[0];
		$type = $this->getActionType($params[0]);
		$project_document_id = $params[1];

		$model = new DocumentModel();
		$documentData = $model->getDocumentsData($type, $project_document_id); 
		//Findout number of documents count
		if(isset($documentData) && count($documentData) == 0) {
			echo "no data";
			return false;
		}

		$docData = $this->getDocumentProperties();
		$documentTitle = $docData['title']; $documentIcon = $docData["image"]; $documentFooterMsg = $docData["footer"];
		$documentObject = array_keys($documentData);
		$count = 0;
		foreach ($documentObject as $id) {
			$jsonMain = $documentData;
			$fileName = preg_replace('/[^A-Za-z0-9\-]/', '_', $jsonMain[$id]['file-name']);
			$fileName = $fileName . ".pdf";	

			$jsonObj = json_decode($jsonMain[$id]['json-object'], true);
			$documentType = array_keys($jsonObj);
			$json = $jsonObj[$documentType[0]];

			$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
			$fontDirs = $defaultConfig['fontDir'];

			$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
			$fontData = $defaultFontConfig['fontdata'];
			
			$mpdf = new \Mpdf\Mpdf([
				'fontDir' => array_merge($fontDirs, [
					__DIR__ . '/custom/font/directory',

				]),
				'fontdata' => $fontData + [
					'frutiger' => [
						'R' => "ARIAL.TTF",
						'B' => "ARIALBD 1.TTF",
						'I' => "ARIALI 1.TTF",
						'BI' => "ARIALBI 1.TTF",
						'useOTL' => 0xFF,
						'useKashida' => 75,
					]
				],
				'default_font' => 'frutiger',
				'anchor2Bookmark' => 1,
				'tempDir' => '/tmp',
			]);

			$mpdf->InsertIndex(true, false, "es_ES.utf8", "Spanish_Spain");
			$mpdf->SetAnchor2Bookmark(1);
			$mpdf->h2toc = array('H1' => 0, 'H2' => 1, 'H3' => 1);
			$stylesheet = $this->getMPDFInlineStyles();
			$mpdf->WriteHTML($stylesheet,1);

			//#-1 Adding Header section
			$documentIconImage = $documentIcon; 
			if($documentFooterMsg == ''){
				$documentFooterMsg = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			}

			//#-1 Footer for all pages
			$mpdf->SetHTMLFooter('
			<div style="font-family: frutiger; font-size: 11pt;">
				<span>' . $documentFooterMsg . '</span> 
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Page<span style="font-weight: bold;"> {PAGENO} </span> of <span style="font-weight: bold;">{nb}</span> 
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<span>QFT-01-00 Rev. B</span>
			</div>');

			$mpdf->WriteHTML('&nbsp;');

			//#-2: Adding image at first page header line
			$mpdf->WriteHTML('<div style="position: absolute; left:340; right: 0; top: 200; bottom: 0;">
				<img src="' . $documentIconImage . '" 
				style="width: 30mm; height: 22mm; margin: 0;" />
			</div>');

			$DocID = 'Doc ID: ' . $json['cp-line4'];
			
			//#-3 Adding Document title
			$mpdf->WriteHTML('<div style="position: absolute; left:0; right: 0; top: 360;text-align: center;font-family: frutiger; font-weight: bold; font-size: 16pt;">
			' . $json['cp-line3'] . '
			</div>');
			
			//#-4 Adding Document ID
			$mpdf->WriteHTML('<div style="position: absolute; left:0; right: 0; top: 390;text-align: center;font-family: frutiger; font-weight: bold; font-size: 16pt;">
			' . $DocID . '
			</div>');

			//#-5: Adding change history section
			$mpdf->WriteHTML('<div style="position: absolute; left:80; right: 0; top: 450; bottom: 0;font-family: frutiger;font-weight: bold;">Change History</div>');
			$tableContent = $pandoc->convert($json['cp-change-history'], "gfm", "html5");
			$tableContent = $this->addTableStylesToContent($tableContent, '90');
			$mpdf->WriteHTML('<div style="position: absolute; left:80; right: 0; top: 480; bottom: 0;font-family: frutiger;">
			' . $tableContent . '
			</div>');

			//#-6 Adding page layout
			$mpdf->AddPage(
				'', // L - landscape, P - portrait 
				'',
				'',
				'',
				'',
				20, // margin_left
				20, // margin right
				30, // margin top
				25, // margin bottom
				12, // margin header
				5
			);

			//#-7 Adding TOC
			$mpdf->TOCpagebreakByArray(array(
				'tocfont' => 'frutiger',
				'tocfontsize' => '10',
				'tocindent' => '0',
				'TOCusePaging' => true,
				'TOCuseLinking' => true,
				'toc_orientation' => 'P',
				'toc_mgl' => '',
				'toc_mgr' => '',
				'toc_mgt' => '',
				'toc_mgb' => '',
				'toc_mgh' => '',
				'toc_mgf' => '',
				'toc_ohname' => '',
				'toc_ehname' => '',
				'toc_ofname' => '',
				'toc_efname' => '',
				'toc_ohvalue' => 0,
				'toc_ehvalue' => 0,
				'toc_ofvalue' => 0,
				'toc_efvalue' => 0,
				'toc_preHTML' => '<h4 style="text-align:center;font-family:frutiger;padding-bottom:0.3in">TABLE OF CONTENTS</h4>',
				'toc_postHTML' => '',
				'toc_bookmarkText' => '',
				'resetpagenum' => '',
				'pagenumstyle' => '',
				'suppress' => '',
				'orientation' => '',
				'mgl' => '',
				'mgr' => '',
				'mgt' => '',
				'mgb' => '',
				'mgh' => '',
				'mgf' => '',
				'ohname' => '',
				'ehname' => '',
				'ofname' => '',
				'efname' => '',
				'ohvalue' => 0,
				'ehvalue' => 0,
				'ofvalue' => 0,
				'efvalue' => 0,
				'toc_id' => 0,
				'pagesel' => '',
				'toc_pagesel' => '',
				'sheetsize' => '',
				'toc_sheetsize' => '',
			));

			//#-8 Adding logo left corner section every page
			$mpdf->SetHTMLHeader('<div style="text-align:left;padding-bottom:30mm;"><img src="'.$documentIconImage.'" style="width: 20mm; height: 17mm; margin: 0;"/></div>','O',true);
			
			//#-9 Adding sections
			try{
				for ($i = 0; $i < count($json['sections']); $i++) {
					$index = (string)$i+1;
					$sectionCount = $index.'.0';
					$mpdf->WriteHTML('<div style="font-family: frutiger; font-weight: bold; font-size: 12pt;"><h1 style="font-size: 12pt;">'.$sectionCount.'&nbsp;&nbsp;'.strtoupper($json['sections'][$i]['title']).'</h1></div>');
					$contentSection = '<b></b>';
					$org = $json['sections'][$i]['content'];
					$contentSection = $pandoc->convert($org, "gfm", "html5");
					//Adding the sub,sub-sub indexing values
					$contentSection = $this->handleSubIndexNumbers($contentSection, $index);
					$contentSection = $this->handleSunHeadingAlignments($contentSection);
					if (strpos($contentSection, '<table>') !== false) {
						if(strtolower($json['sections'][$i]['title']) == 'traceability matrix'){
							$contentSection = str_replace('<br/>', '; ', $contentSection);
						}
						$contentSection = str_replace('</table>', '</table><br/>', $contentSection);
						$contentSection = str_replace('</table><br/><strong>', '</table><strong>', $contentSection);
						$tableContentFormatted = $this->addTableStylesToContent($contentSection, '100');
						if(strtolower($json['sections'][$i]['title']) == 'risk assessment'){
							$tableContentFormatted = $this->addWordWrap($tableContentFormatted);
							libxml_use_internal_errors(true); //To restrict the DocType errors from the content 
							$doc->loadHTML(mb_convert_encoding($tableContentFormatted, 'HTML-ENTITIES', 'UTF-8'));
							$html= $this->checkLargeTables($doc);

							$mpdf->WriteHTML($html);
						}else{
							$mpdf->WriteHTML('<div style="font-family: frutiger; font-weight: normal; font-size: 11pt;margin-left: 0.4in;">
							'.$tableContentFormatted.'
							</div>');
						}
					} else {
						$mpdf->WriteHTML('<div style="font-family: frutiger; font-weight: normal; font-size: 11pt;margin-left: 0.4in;">
						'.$contentSection.'
						</div>');
					}
					$mpdf->WriteHTML('<br>');
				}
			}
			catch (Error $e) {
				echo "Error caught: " . $e->getMessage();
				return false;
			}
			try{
				// Saving the document as OOXML file...
				$count++;
			}
			catch (Error $e) {
				echo "Error caught: " . $e->getMessage();
				return false;
			}			
			try{
				if ($typeOfRequest == 2) {
					$directoryName = "Project_Documents_".$documentData[0]['project-id'];		
					if (!is_dir($directoryName)) {
						mkdir($directoryName, 0777);
					}
					$mpdf->Output($directoryName . '/' . $fileName);
					if (count($documentObject) == $count) {
						$zip_file = $directoryName.'.zip';
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
							/*
							if (is_dir($directoryName)) {
								foreach ($filesToDelete as $file) {
									unlink($file);
								}
								rmdir($directoryName);
							}
							*/
							readfile($zip_file);
						}else{
							echo "unable to create zip file";
						}
					}
				}else{
					$rootDirName = $_SERVER['DOCUMENT_ROOT'];
					$directoryName = "Project_Documents_".$documentData[0]['project-id'];		
					if (!is_dir($directoryName)) {
						mkdir($directoryName, 0777);
					}
					ob_clean();
					if($typeOfRequest == 1){
						$mpdf->Output($fileName, 'D');
					}else{
						$dir = "PreviewDocx";
						if (!is_dir($dir)) {
							mkdir($dir, 0777);
						}
						$mpdf->Output($dir.'/'.$fileName);
						$response = array('success' => "True", "fileName"=>$dir.'/'.$fileName);
						echo json_encode( $response );	
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

	public function handleSubIndexNumbers($data, $index){
		preg_match_all( '|<h[^>]+>(.*)</h[^>]+>|iU', $data, $matches);
		//if any h2, h3 matches found
		if(count($matches[0]) >= 1){
			$h3TagPosition = 0;	
			$h3TagIncrement = 0;
			$h3TagLoopIncrement = 0;
			$h3ParentIndex = 0;
			$currentIndex = 0;
			foreach ($matches[0] as $x => $val) {
				$key = $x;
				//findout h3 is exists or not....
				$h3Exist = strpos($val, '</h3>');
				$orgVal = $val;
				if($h3Exist){
					if($h3TagPosition == 0){
						$h3TagLoopIncrement = 0;
						$x = $currentIndex;
						if($currentIndex == 0){
							$x = $x;
						}
						$h3ParentIndex = $x;
						$h3TagPosition = $h3ParentIndex;
					}
					$incKey = $index.".".$h3ParentIndex.".".($h3TagLoopIncrement+1)." ";
					$h3TagIncrement = $h3TagIncrement+1;
					$h3TagLoopIncrement = $h3TagLoopIncrement +1;
					$val = str_replace(">", ">".$incKey, $val);
					//update the index numbering string into original string
					$data = $this->updateSubIndexNumbers($orgVal, $incKey, $data, 'h3');
				}else{
					$orgVal = $val;
					$h3TagPosition = 0;
					$h3TagLoopIncrement = 0;
					$currentIndex = ($x+1-$h3TagIncrement); 
					$incKey = $index.".".($x+1-$h3TagIncrement)." ";
					$data = $this->updateSubIndexNumbers($orgVal, $incKey, $data, 'h2');
				}
			}

		}
		return $data;
	}

	public function updateSubIndexNumbers($orgVal, $incKey, $data, $type) {
		$orgValString = preg_replace("/\s+/",' ',$orgVal);  
		preg_match_all( '|<'.$type.'(.*)>(.*)</'.$type.'>|iU', $orgValString, $orgValMatch);
		if(!empty($orgValMatch)) {
			$changeHeader = $orgValMatch[2][0];
			$newValue = '">'.$incKey.$changeHeader;
			if (!(ctype_upper($changeHeader))) { 
				$newValue = '">'.$incKey.ucwords($changeHeader);
			}
			//may be same heading name will be present in content also, so to avoid the number appending at that content we checked with tag(>,</) characters
			$data  = str_replace('">'.$changeHeader.'</', $newValue.'</', $data);
		}
		return $data;
	}

	public function handleSunHeadingAlignments($contentSection){
		//Adjusting the sub heading content display position, applying the font to code block
		$contentSection = str_replace("<h2 ", '<h2 style="position: absolute; margin-left: -28pt; font-weight: normal;" ', $contentSection);
		$contentSection = str_replace("<h3 ", '<h3 style="position: absolute; margin-left: -28pt; font-weight: normal;" ', $contentSection);
		$contentSection = str_replace("<code>", '<code style="font-family: frutiger;">', $contentSection);
		return $contentSection;
	}

	public function addWordWrap($tableContentFormatted) {
		$tableContentFormatted = str_replace(',"', ', "', $tableContentFormatted);
		$tableContentFormatted = str_replace('https://', ' https://', $tableContentFormatted);
		$tableContentFormatted = str_replace('},{', '}, {', $tableContentFormatted);
		$tableContentFormatted = str_replace('":"', '": "', $tableContentFormatted);
		return $tableContentFormatted;
	}

	public function checkLargeTables(&$doc)
	{
		//new code to split table large cells
		foreach ($doc->getElementsByTagName('table') as $table) {
			// iterate over each row in the table
			$trs = $table->getElementsByTagName('tr');
			$cloneArr = [];
			foreach ($trs as $tr) {
				$cloned = 0;
				foreach ($tr->getElementsByTagName('td') as $td) { // get the columns in this row
					if (strlen($td->textContent) > 2000) {
						$longValue = $td->nodeValue;
						$breaktill = strpos($td->nodeValue, '.', 800);
						if ($cloned == 0) {
							$cloneNode = $tr->cloneNode(TRUE);
							$cloned = 1;
							$cloneArr[] = ["node" => $cloneNode, 'row' => $tr, 'breaktill' => $breaktill];
						}
						$td->textContent = substr($longValue, 0, $breaktill) . '. (cont.)';
						// $td->setAttribute("style:", "word-break: break-all; font-size: 12pt");
						$td->setAttribute("style:", "white-space: nowrap; word-break: break-all; font-size: 12pt !important");
						$td->setAttribute("width", "84%");
					}
				}
			}
			//here insert new nodes
			foreach ($cloneArr as $cloneData) {
				$this->insertNewNodes($cloneData, $table);    //this will be recursive function to split row multiple times if needed
			}
		}
		return @$doc->saveHTML();
	}
	
	public function insertNewNodes(&$cloneData, &$table, $start = 800)
	{
		//processing cloneNodes
		$cloned = 0;
		foreach ($cloneData['node']->getElementsByTagName('td') as $td) {
			$longValue = $td->textContent;
			if (strlen($longValue) > $start) {
				$breaktill = strpos($longValue, '.', $start); //starting point after first fullstop
				if (strlen($longValue) > ($breaktill + 800)) {
					$endPoint = $breaktill + 800;
					$end = strpos($longValue, '.', $endPoint) - $breaktill; //end point till last sentence
				} else {
					$end = 800;
				}
				if (strlen($longValue) > $end + $breaktill && $cloned == 0) {
					$cloned = 1;
					$newNode = [];
					$newNode['node'] = $cloneData['node']->cloneNode(TRUE);
					$newNode['row'] = $cloneData['node'];
				}
				$td->textContent = '(cont\'d)' . substr($longValue, $breaktill + 1, $end);
			} else {
				$td->textContent = '';
			}
		}
		try {
			$cloneData['row']->parentNode->insertBefore($cloneData['node'], $cloneData['row']->nextSibling);
		} catch (\Exception $e) {
			$table->appendChild($cloneData['node']);
		}
		if ($cloned == 1) {
			$this->insertNewNodes($newNode, $table, $start + 800);
		}
	}

	public function getMPDFInlineStyles() {
		$stylesheet = '
			body{
				font-family: frutiger !important;
				font-size: 11pt  !important;
			}
			table{
				border-spacing: 0 10pt !important; layout: fixed !important; font-family:frutiger !important; font-size: 11pt !important; table-layout: fixed !important; word-wrap: break-word !important; padding: 50px !important; border: 1px #000000 solid !important; border-collapse: collapse !important;
			}
			th{
				font-weight: bold !important; padding-left:10pt !important; background-color:#d9d9d9 !important; font-size: 11pt !important; text-align:left !important; font-family: frutiger !important; height: 30px !important;
			}
			td{
				padding-left:10pt !important; font-size: 11pt !important; font-family: frutiger !important;
			}
			table td {
				word-wrap:break-word;
				white-space: normal;
			}
			h1{
				font-size: 12pt !important; font-family: frutiger !important;
			}
			h2{
				font-size: 11pt !important; font-family: frutiger !important;
			}
			h3{
				font-size: 10pt !important; font-family: frutiger !important;
			}
			div.mpdf_toc {
				font-family: frutiger !important;
				font-size: 11pt;
			}
			a.mpdf_toc_a  {
				text-decoration: none;
				color: black;
			}
			
			/* Whole line level 0 */
			div.mpdf_toc_level_0 {
				line-height: 2.0;
				margin-left: 0;
				padding-right: 2em;
			}
			
			/* padding-right should match e.g <dottab outdent="2em" /> 0 is default */
			/* Title level 0 - may be inside <a> */
			span.mpdf_toc_t_level_0 {
				font-weight: bold;
			}
			
			/* Page no. level 0 - may be inside <a> */
			span.mpdf_toc_p_level_0 {}
			
			/* Whole line level 1 */
			div.mpdf_toc_level_1 {
				margin-left: 0;
				text-indent: -2em;
				padding-right: 2em;
			}
			
			/* padding-right should match <dottab outdent="2em" /> 2em is default */
			/* Title level 1 */
			span.mpdf_toc_t_level_1 {
				font-style: normal;
				font-weight: normal;
			}
			
			/* Page no. level 1 - may be inside <a> */
			span.mpdf_toc_p_level_1  {}
			
			/* Whole line level 2 */
			div.mpdf_toc_level_2 {
				margin-left: 4em;
				text-indent: -2em;
				padding-right: 2em;
			}	
			';
		return $stylesheet;
	}

	public function startPDFDocxConvertion() {
		$filePath = '';
		$params = $this->returnParams();
		$projectId = $params[0];
		$fileName = $params[1];
		
		//PDF to DOCX Convertion
		$url = 'https://api2.docconversionapi.com/jobs/create';
		$filePath = 'https://info.viosrdtest.in/Project_Documents_'.$projectId.'/'.$fileName;
		$fields = array(
			'inputFile' => $filePath,
			'conversionParameters' => '{"pdfType" : "1B", "fitToPage" : true}',
			'outputFormat' => 'docx',
			'async' => 'false'
		);
		//url-ify the data for the POST
		$fields_string = '';
		foreach ($fields as $key => $value) {
			$fields_string .= $key . '=' . $value . '&';
		}
		$fields_string = rtrim($fields_string, '&');
		//open connection
		$ch = curl_init();
		//set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'X-ApplicationID: 7976cebc-cf1c-41bc-89c1-bd12f32c43a9',
			'X-SecretKey: e1a11159-daad-4949-8112-1b13a9bb84de'
		));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		//execute post
		$result = curl_exec($ch);
		echo '';
		return false;	
	}

	public function updateDownloadUrl() {
		$id = $this->request->getVar('id');
		$projectId = $this->request->getVar('project-id');
		$path = $this->request->getVar('path');
		$fileName = $this->request->getVar('name');
		$isDBUpdate = $this->request->getVar('isDBUpdate');
		$lastItem = $this->request->getVar('lastItem');
		if($isDBUpdate){
			$model = new DocumentModel();
			$documentData = $model->updateDownloadUrl($projectId, $id, $path); 
		}
		$directoryName = "Project_Word_Documents_".$projectId;		
		if (!is_dir($directoryName)) {
			mkdir($directoryName, 0777);
		}
		$fileName = preg_replace('/[^A-Za-z0-9\-]/', '_', $fileName);
		$fileName = $fileName.'.docx';
		$lastItem = preg_replace('/[^A-Za-z0-9\-]/', '_', $lastItem);
		$lastItem = $lastItem.'.docx';

		$output_filename = $directoryName."/".$fileName;
		$output_filename = str_replace('_pdf', '', $output_filename);

		$host = $path;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $host);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, false);
		curl_setopt($ch, CURLOPT_REFERER, "http://www.xcontest.org");
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$result = curl_exec($ch);
		curl_close($ch);

		// the following lines write the contents to a file in the same directory (provided permissions etc)
		$fp = fopen($output_filename, 'w');
		fwrite($fp, $result);
		fclose($fp);
		//Goto zip folder download //Write zip code:
		if($lastItem == $fileName){
			$id = $this->request->getVar('id');
			$projectId = $this->request->getVar('project-id');
			$path = $this->request->getVar('path');
			$fileName = $this->request->getVar('name');
			$isDBUpdate = $this->request->getVar('isDBUpdate');
			$lastItem = $this->request->getVar('lastItem');	

			$zip = new ZipArchive();
			$filename = $directoryName.".zip";
			if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
				exit("cannot open <$filename>\n");
			}
			$dir = $directoryName.'/';
			// Create zip
			if (is_dir($dir)){
				if ($dh = opendir($dir)){
					while (($file = readdir($dh)) !== false){
						// If file
						if (is_file($dir.$file)) {
							if($file != '' && $file != '.' && $file != '..'){
								$zip->addFile($dir.$file);
							}
						}
					}
					closedir($dh);
				}
			}
			$zip->close();
			echo $filename;
		}else{
			$response = array('success' => "True", "status"=>'single-Download-zip', "name"=>$fileName, 'lastName' => $lastItem, "data"=>$documentData, "path"=>$path, "proId"=>$projectId, "id"=>$id);
			echo json_encode( $response );	
		}
	}

	public function getWordDocumentFileList() {
		//First check all approved files download path is available or not
		$projectId = $this->returnProjectID();
		$model = new ProjectModel();
		$pathList = $model->select('download-path')->where('project-id', $projectId)->first();
		$pathList = json_decode($pathList['download-path'], true);

		if($pathList == "" || $pathList == null || $pathList == 'null'){
			//JSON not available, Goto fresh download
			//check if any documents are in approved state for the requested project
			$docModel = new DocumentModel();
			$dataCount = $docModel->getApprovedFilesCount($projectId);
			if($dataCount == 0 || $dataCount == null){
				$response = array('success' => "False", "description"=>'No downloads available');
				echo json_encode( $response );
			}else{
				$response = array('success' => "False", "description"=>'Download path is not available');
				echo json_encode( $response );	
			}
		}else{
			//JSON is available, check all document's update-date is lowerthan the zipfile timestamp
			$current_date =  gmdate("Y-m-d H:i:s");
			$json_time = $pathList['timeStamp'];
			$data = $model->getDownloadedProjectStatus($projectId, $json_time);
			$zip_file = "Project_Documents_".$projectId.".zip";
			if($data[0]['count'] > 0){
				//JSON aviable, but its old one, so delete and goto fresh download
				$this->updateGenerateDownloadPath($projectId);
				$response = array('success' => "False", "description"=>"Download is deprecated");
				echo json_encode( $response );	
			}else{
				$model = new DocumentModel();
				//Will get either [filenames] or [file-name, downloadPath] array with tag fileNames=true/false 
				$documentData = $model->getDownloadPaths($projectId); 
				$response = array('success' => "True", "data"=>$documentData);
				echo json_encode( $response );	
			}
		}
	}

	public function getActionType($id){
		switch($id) {
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
		return $type;
	}

	public function getDocumentProperties(){
		//Fetching the doc-properties(Global seetings) from Model
		$settingsModel = new SettingsModel();
		$documentProperties = $settingsModel->getSettings("documentProperties");
		$documentProperties = json_decode($documentProperties[0]['options'], true);
		$docData = [ "title" => "",  "image" => "",  "footer" => "" ];
		foreach($documentProperties as $key => $val){
			if($val['key'] == "docTitle"){
				$docData["title"] = $val["value"];
			}
			if($val["key"] == "docIcon"){
				$docData["image"] = $val["value"];
			}
			if($val["key"] == "docConfidential"){
				$docData["footer"] = $val["value"];
			}
		}
		return $docData;
	}

	function sectionNumber($sectionStr) {
		return (int) filter_var($sectionStr, FILTER_SANITIZE_NUMBER_INT);
	}

	function addTableStylesToContent($rawContent, $tablewidth)
	 {
		$fontFamily = 'frutiger, sans-serif';
		$fontSize = '11pt';
		$replaceContent = str_replace("<table>", '<table style="overflow: wrap; font-family:' . $fontFamily . '; font-size: ' . $fontSize . '; width: '.$tablewidth.'%; table-layout: fixed; word-wrap: break-word; padding: 50pt; border: 1pt #000000 solid; border-collapse: collapse;" border="1" cellpadding="5">', $rawContent);
		$replaceContent = str_replace("<th>", "<th style='font-weight: bold; padding-left:10pt; background-color:#d9d9d9;word-wrap: break-word'>", $replaceContent);
		$replaceContent = str_replace("<td>", "<td style='padding-left:10pt; font-size:11pt; word-wrap: font-size:20pt; break-word'>", $replaceContent);
		$replaceContent = str_replace("<br/>", " <br/> ", $replaceContent);
		return $replaceContent;
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
			//check if any documents are in approved state for the requested project
			$docModel = new DocumentModel();
			$dataCount = $docModel->getApprovedFilesCount($projectId);
			if($dataCount == 0 || $dataCount == null){
				$response = array('success' => "False", "description"=>'No downloads available');
				echo json_encode( $response );
			}else{
				$response = array('success' => "False", "description"=>'Download path is not available');
				echo json_encode( $response );	
			}
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
				$this->updateGenerateDownloadPath($projectId);
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
		$this->updateGenerateDownloadPath($projectId);
		$response = array('success' => "True", "description"=>"Link Updated");
		echo json_encode( $response );	
	}

	public function updateGenerateDownloadPath($projectId){
		$model = new DocumentModel();
		$model->updateGenerateDownloadPath($projectId, NULL);
	}

	public function returnProjectID(){
		$uri = $this->request->uri;
		$id = $uri->getSegment(3);
		return $id;
	}
	
}