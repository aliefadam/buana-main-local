<?php namespace App\Controllers;
 
require 'vendor/autoload.php';
use CodeIgniter\Controller;
use CodeIgniter\API\ResponseTrait;
use App\Models\ListModel;
 
use Symfony\Component\Filesystem\Filesystem,
Xthiago\PDFVersionConverter\Converter\GhostscriptConverterCommand,
Xthiago\PDFVersionConverter\Converter\GhostscriptConverter;
use Xthiago\PDFVersionConverter\Guesser\RegexGuesser;


class Test extends Controller
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new ListModel();
            if($this->request->getMethod() == 'put'){
                //parse_str(file_get_contents("php://input"), $json);
				$json = $this->PUTData();
				$json2 = $_REQUEST;
				$json3 = $this->request->getJSON();
				$doc = $this->request->getFile('datasheet');
				return $this->respond(['status' => false, 'data'=>$json, 'data2'=>$json2, 'data3'=>$json3, "data4"=>$doc], 200);
                return $this->update($json["pk"]);
            }
            else if($this->request->getMethod() == 'delete'){
                $json = $_REQUEST;
                return $this->delete($json[$model->primaryKey]);
            }
            else{
                $json = $_REQUEST;
                $json["filter"] = json_decode($json["filter"] ?? '{}', true);
                $json["filterType"] = json_decode($json["filterType"] ?? '{}', true);
                $json["filterCondition"] = json_decode($json["filterCondition"] ?? '{}', true);
                $json = (Object) $json;
                $data = $model->read($json);
                return $this->respond(['status' => $data[0], 'data'=>$data[1], 'total' => $data[2]], 200);
            }
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }


	function reademail(){
		// Create PhpImap\Mailbox instance for all further actions
		$mailbox = new \PhpImap\Mailbox(
			'{mail.buanamultiteknik.com:993/imap/ssl/novalidate-cert}INBOX', // IMAP server and mailbox folder
			'internal@buanamultiteknik.com', // Username for the before configured mailbox
			'BMTinter@2023', // Password for the before configured username
			__DIR__, // Directory, where attachments will be saved (optional)
			'UTF-8', // Server encoding (optional)
			true, // Trim leading/ending whitespaces of IMAP path (optional)
			false // Attachment filename mode (optional; false = random filename; true = original filename)
		);


		try {
			// Get all emails (messages)
			// PHP.net imap_search criteria: http://php.net/manual/en/function.imap-search.php
			$mailsIds = $mailbox->searchMailbox('SUBJECT "Notification"');
		} catch(\PhpImap\Exceptions\ConnectionException $ex) {
			echo "IMAP connection failed: " . implode(",", $ex->getErrors('all'));
			die();
		}

		// If $mailsIds is empty, no emails could be found
		if(!$mailsIds) {
			die('Mailbox is empty');
		}

		// Get the first message
		// If '__DIR__' was defined in the first line, it will automatically
		// save all attachments to the specified directory
		$mail = $mailbox->getMail($mailsIds[0]);

		// Show, if $mail has one or more attachments
		echo "\nMail has attachments? ";
		if($mail->hasAttachments()) {
			echo "Yes\n";
		} else {
			echo "No\n";
		}

		// Print all information of $mail
		print_r($mail);

		// Print all attachements of $mail
		//echo "\n\nAttachments:\n";
		//print_r($mail->getAttachments());
		
	}

	

	function PUTData(){

		$data = file_get_contents("php://input");
		$lines = preg_split('/\n|\r\n?/', $data);
		$keyLinePrefix = 'Content-Disposition: form-data; name="';
	
		$PUT = [];
	
		$key = false;
		foreach($lines as $num => $line){
			if(strpos($line, $keyLinePrefix) !== false){
				$key = explode('"', substr($line, 38, -1))[0];
			}
			if($key!=false && strpos($line, "FormBoundary")== false){
				$PUT[$key] = $line;
			}
		}
	
		return $PUT;
	
	}

	/* function PUT(string $name, string $data):string{

		$lines = file('php://input');
		$keyLinePrefix = 'Content-Disposition: form-data; name="';
	
		$PUT = [];
		$findLineNum = null;
	
		foreach($lines as $num => $line){
			if(strpos($line, $keyLinePrefix) !== false){
				if($findLineNum){ break; }
				if($name !== substr($line, 38, -3)){ continue; }
				$findLineNum = $num;
			} else if($findLineNum){
				$PUT[] = $line;
			}
		}
	
		array_shift($PUT);
		array_pop($PUT);
	
		return mb_substr(implode('', $PUT), 0, -2, 'UTF-8');
	
	} */
 
    public function create()
    {
        $model = new ListModel();
        $json = $_REQUEST;
        
        $data = [
        ];

        foreach($model->allowedFields as $value) 
        {
			if(isset($json[$value]) && $value != 'datasheet')
				$data[$value] = $json[$value];
        }

		$doc = $this->request->getFile('datasheet');
		if(!empty($doc)){
			try {
				if (! $doc->hasMoved()) {
					$name = $doc->getRandomName();
					$filepath = './uploads/' . $name;
					$data['datasheet'] = $name;
					$doc->move('./uploads/', $name);
				}
				else{
					$response = [
						'status'   => false
					];
				
					return $this->respondCreated($response, 200);
				}
			} catch (Exception $err) {
				$response = [
					'status'   => false,
					'data'	=> $err
				];
			
				return $this->respondCreated($response, 200);
			}
		}

		$session = session();
		$s = $session->get();
		if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
			$data["created_by"] = $s["userid"];
        $model->insert($data);
		$affected = $model->affectedRows();
		if($affected != 0)
			$response = [
				'status'   => true,
				'data'    => 'Data Saved'
			];
		else
			$response = [
				'status'   => false,
				'data'    => $model->errors()
			];
        
        return $this->respondCreated($response, 200);
    }
 
    public function update($pk = null)
    {
        $model = new ListModel();
		$json = $this->PUTData();
		$data = [
		];
        if($json){

            foreach($json as $key => $value) 
            {
                if($key!='pk')
                    $data[$key] = $value;
            }
        }
		$doc = $this->request->getFile('datasheet');
        return $this->respond(["status"=>false, "data"=>$doc]);
		if(!empty($doc)){
			try {
				if (! $doc->hasMoved()) {
					$name = $doc->getRandomName();
					$filepath = './uploads/' . $name;
					$data['datasheet'] = $name;
					$doc->move('./uploads/', $name);
				}
				else{
					$response = [
						'status'   => false
					];
				
					return $this->respondCreated($response, 200);
				}
			} catch (Exception $err) {
				$response = [
					'status'   => false,
					'data'	=> $err
				];
			
				return $this->respondCreated($response, 200);
			}
		}

        $session = session();
		$s = $session->get();
		if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			$data["modified_by"] = $s["userid"];
		if(in_array("modified_date", $model->allowedFields))
			$data["modified_date"] = date("Y-m-d H:i:s");
        $model->update($pk, $data);
		$affected = $model->affectedRows();
		if($affected != 0)
			$response = [
				'status'   => true
			];
		else
			$response = [
				'status'   => false,
				'data'    => $model->errors()
			];
        return $this->respond($response);
    }
 
    public function delete($pk = null)
    {
        $model = new ListModel();
        $data = $model->find($pk);
        if($data){
            $model->delete($pk);
			//$data = [];
			//$data["flag"] = 0;
			//$session = session();
			//$s = $session->get();
			//if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			//	$data["modified_by"] = $s["userid"];
			//if(in_array("modified_date", $model->allowedFields))
			//	$data["modified_date"] = date('Y-m-d H:i:s', now());
            //$model->update($pk, ["flag"=>0]);
			$affected = $model->affectedRows();
			if($affected != 0)
				$response = [
					'status'   => true
				];
			else
				$response = [
					'status'   => false,
					'data'    => $model->errors()
				];
            return $this->respond($response);
        }else{
            $response = [
                'status'   => false,
				'data' => 'Data not found!'
            ];
            
            return $this->respond($response);
        }
         
    }
 
	public function pdf(){
		print('1693479619_8d29f8261f724a5c2602');
		try {
			$guesser = new RegexGuesser();
			$path = './uploads/1693479619_8d29f8261f724a5c2602.pdf';//https://internal.buanamultiteknik.com/api/uploads/rfq749/1693794332_45b129484bedebda9d02.pdf
			$a = $guesser->guess(trim($path));
			var_dump($a);
			$command = new GhostscriptConverterCommand();
			$filesystem = new Filesystem();
			print('<br/>');
			$converter = new GhostscriptConverter($command, $filesystem);
			$converter->convert($path, '1.4');
			$a = $guesser->guess(trim($path));
			var_dump($a);
			$oPDF = new \setasign\Fpdi\Fpdi();
			$iPageCount = $oPDF->setSourceFile(trim($path));
		} catch (\Throwable $th) {
			print('invalid');
		}
	}
	
	public function check(){
	    $session = session();
	    $s = $session->get();
	    $str =  '<pre>' . htmlentities(json_encode($s, JSON_PRETTY_PRINT)) . '</pre>';
        //if ($echo) echo $str;
        return $str;
	}

	public function wa(){
		//echo exec("ping -c 3 www.google.com");
		$json = $_REQUEST;
		$content = file_get_contents('https://loripsum.net/api/1/short/plaintext');
		$json = (Object) $json;
		helper(['Wa_helper']);
		$res = sendWa($json->number, $content);
		return $res;
	}

	public function groupwa(){
		$curl = curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://api.fonnte.com/get-whatsapp-group',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_HTTPHEADER => array(
			'Authorization: ut@xFGbs#NTkJfs#JMUF'
		),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		echo $response;
	}

	

	function thumbnail(){
		$json = $_REQUEST;
		$json = (Object) $json;
		$im = ImageCreateFromString(file_get_contents($json->url));
		$tmp = array();
		if ($im) {
			$width = imagesx($im);
			$height = imagesy($im);
			$newwidth = 200;
			$percent = $newwidth/$width*100;
			$newheight = $height * $percent/100;
		
			$thumb = imagecreatetruecolor($newwidth, $newheight);
		
			// Resize
			imagecopyresized($thumb, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		
			// Output
			ob_start();
			imagejpeg($thumb);
			$im = ob_get_clean();
			ob_end_clean();
			imagedestroy($thumb);
			
		}
		header('Content-Type: image/jpeg');
		header('Content-Disposition: inline; filename="'.basename($json->url).'"');  // Add the file name
		return $im;//$this->respond($im, 200);
	}
}