<?php namespace App\Controllers;
use CodeIgniter\Controller;
use App\Models\EmailModel;
use CodeIgniter\API\ResponseTrait;
 
class Email extends Controller
{
    use ResponseTrait;
    public function index(){
		$json = $this->request->getJSON();
		$model = new EmailModel();
       
        return '';
    }

    function invoice_paid(){
		try {
			$model = new EmailModel();
			$res = $model->db->query("select id, message from email where sender = 'klikbcabisnis@klikbca.com' and subject = 'Notification' and id > coalesce((select last_id from email_process where process_name = 'invoice_paid'), 0);");
			$inv = [];
			$id = 0;
			$data = $res->getResult();
			foreach ($data as $i => $val) {
				preg_match_all('/INV(.*?)\n/', $val->message, $matches);
				$id = $val->id;
				try {
					$inv[] = "'INV".trim($matches[1][0])."'";
				} catch (\Throwable $th) {
				}
			}

			if(count($inv)>0){
				$res = $model->db->query("update invoice set is_paid = 1 where invoice_no in (".implode(",", $inv).")");
			}
			$res = $model->db->query("replace into email_process(process_name, last_id) values('invoice_paid', $id);");
			$response = [
				'status'   => true,
				'data' => $inv
			];
		} catch (\Throwable $th) {
			$response = [
				'status'   => false,
				'data'    => $th->getMessage()
			];
		}

		return $this->respondCreated($response, 200);
	}

	function read2(){
		try {
			$model = new EmailModel();
			$imap   = imap_open('{mail.buanamultiteknik.com:993/imap/ssl/novalidate-cert}INBOX', 'internal@buanamultiteknik.com', 'BMTinter@2023', OP_READONLY);
			$date = "2024-07-01";
			$res = $model->db->query("Select email_date from email order by id desc limit 1");
			$data = $res->getResult();
			if(isset($data[0]))
				$date = $data[0]->email_date;
			
			$some   = imap_search($imap, 'SINCE "2024-07-01 00:00"');
			$response = [
				'status'   => true,
				'data' => $some,
				'msg' => 'SINCE "'.$date.'"',
				'msg2' => 'test'
			];
		}catch (\Throwable $th) {
			$response = [
				'status'   => false,
				'data'    => $th->getMessage()
			];
		}

		return $this->respondCreated($response, 200);
		
	}

	function read(){
		try {
			$model = new EmailModel();
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

			$date = "2023-10-10";
			$date2 = "2023-10-10 00:00:00";
			$res = $model->db->query("Select DATE(email_date) as email_date, email_date as email_datetime from email where id not in (861, 862) order by id desc limit 1");
			$data = $res->getResult();
			if(isset($data[0])){
				$date = $data[0]->email_date;
				$date2 = $data[0]->email_datetime;
			}
			try {
				// Get all emails (messages)
				// PHP.net imap_search criteria: http://php.net/manual/en/function.imap-search.php
				$mailsIds = $mailbox->searchMailbox('SINCE "'.$date.'"');
			} catch(\PhpImap\Exceptions\ConnectionException $ex) {
				echo "IMAP connection failed: " . implode(",", $ex->getErrors('all'));
				die();
			}

			// If $mailsIds is empty, no emails could be found
// 			if(!$mailsIds) {
// 				die('Mailbox is empty');
// 			}

			// Get the first message
			// If '__DIR__' was defined in the first line, it will automatically
			// save all attachments to the specified directory
			$mails = [];
			$since = strtotime($date2);
			foreach ($mailsIds as $i => $val) {
				$mail = $mailbox->getMail($val, false);
				try {
					$email_date = strtotime(str_replace('T', ' ', explode('+', $mail->date)[0]));
					if($email_date > $since)
						$mails[] = [
							"receiver" => $mail->toString,
							"sender" => $mail->senderAddress,
							"cc" => $mail->ccString,
							"subject" => $mail->subject,
							"message" => $mail->textPlain,
							"message_id" => $mail->headers->message_id,
							"type" => "receive",
							//"valid" => $email_date > $since,
							"email_date" => str_replace('T', ' ', explode('+', $mail->date)[0]),
						];
				} catch (\Throwable $th) {
				}
			}

			$model->insertBatch($mails);
			$response = [
				'status'   => true,
				'data' => $mails
			];
		} catch (\Throwable $th) {
			$response = [
				'status'   => false,
				'data'    => $th->getMessage()
			];
		}

		return $this->respondCreated($response, 200);
		
	}

	function read3(){
		try {
			$model = new EmailModel();
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

			$date = "2023-10-10";
			$date2 = "2023-10-10 00:00:00";
			/*$res = $model->db->query("Select DATE(email_date) as email_date, email_date as email_datetime from email order by id desc limit 1");
			$data = $res->getResult();
			if(isset($data[0])){
				$date = $data[0]->email_date;
				$date2 = $data[0]->email_datetime;
			}*/
			try {
				// Get all emails (messages)
				// PHP.net imap_search criteria: http://php.net/manual/en/function.imap-search.php
				$mailsIds = $mailbox->searchMailbox('SINCE "2024-07-01"');
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
			$mails = [];
			$since = strtotime($date2);
			foreach ($mailsIds as $i => $val) {
				$mail = $mailbox->getMail($val, false);
				try {
					$email_date = strtotime(str_replace('T', ' ', explode('+', $mail->date)[0]));
					if($email_date > $since)
						$mails[] = [
							"receiver" => $mail->toString,
							"sender" => $mail->senderAddress,
							"cc" => $mail->ccString,
							"subject" => $mail->subject,
							"message" => $mail->textPlain,
							"message_id" => $mail->headers->message_id,
							"type" => "receive",
							//"valid" => $email_date > $since,
							"email_date" => str_replace('T', ' ', explode('+', $mail->date)[0]),
						];
				} catch (\Throwable $th) {
				}
			}

			//$model->insertBatch($mails);
			$response = [
				'status'   => true,
				'mails'	=> $mails
			];
		} catch (\Throwable $th) {
			$response = [
				'status'   => false,
				'data'    => $th->getMessage()
			];
		}

		return $this->respondCreated($response, 200);
		
	}
}