<?php namespace App\Controllers\Bom;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\PurchaseOrderHistoryModel;
use App\Models\PaymentModel;
 
class Purchaseorderhistory extends ResourceController
{
    use ResponseTrait;
    // get all product
    public function index()
    {
        try {
            $model = new PurchaseOrderHistoryModel();
			$uri = service('uri');
			$segment = [];
			$segment = $uri->getSegments();
            if($this->request->getMethod() == 'put'){
                $json = $this->request->getJSON();
                return $this->update($json->pk);
            }
            else if($this->request->getMethod() == 'delete'){
                $json = $_REQUEST;
                return $this->delete($json[$model->primaryKey]);
            }
            else{
                $json = $_REQUEST;
                $json["filter"] = json_decode(json_encode($json["filter"] ?? '{}', true), true);
                $json["filterType"] = json_decode(json_encode($json["filterType"] ?? '{}', true), true);
                $json["filterCondition"] = json_decode(json_encode($json["filterCondition"] ?? '{}', true), true);
                $json = (Object) $json;
				if(isset($json->invoice) && isset($json->filter["id"])){
					unset($json->filter["total_payment_pct"]);
				}
				//return $this->respond(["a"=>$json]);
				
					/* helper(['Wa_helper']);
					sendWa('628113525765', "a\r\na"); */
				
                $data = $model->read($json);
				$ex = explode('.', end($segment));
				if(end($ex)=='xlsx'){
					$exporter = new \Xerobase\ExcelReporter\Export();
					$exporter->export($data[0]);
				}
				else
                	return $this->respond(['status' => true, 'data'=>$data[0], 'total' => $data[1]], 200);
            }
            //code...
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }

    public function addcharge(){
        $model = new PurchaseOrderHistoryModel();
        $json = $this->request->getJSON();
        $data = ["charge1"=>$json->charge1, "charge2"=>$json->charge2];
        $res = $model->where('id', $json->purchase_order_id)
        ->set($data)
        ->update();
        if(!$res)
            return $this->respondCreated(["status"=>false, "data"=>$model->errors()], 201);
        
        return $this->respondCreated(["status"=>true], 201);
    }

	/* public function revisi(){
        $model = new PurchaseOrderHistoryModel();
        $json = $_REQUEST;
		$q = $model->info($json['id']);
		$d = $q[1][0];
		$session = session();
		$s = $session->get();
		if($d->approved==3 && isset($s["userid"])){
			$revBy = $s["userid"];
			$model->db->query("
			insert into purchase_order_history (
				`revision_by`, `po_no`, `dept_id`, `po_date`, `order_type`, `supplier_id`, `promised_delivery_date`, `currency`, `exchange_rate`, `rate_date`, `final_quote_url`, `signed_po_url`, `signed_pr_url`, `no`, `year`, `charge1`, `charge2`, `charge1_desc`, `charge2_desc`, `note`, `title`, `flag`, `approved`, `created_date`, `is_complete`, `ref_offer_no`, `ref_internal_bmt`, `ask_approval_date`, `approval_date`, `approval_2_date`, `approval_2_by`, `approval_by`, `created_by`, `modified_date`, `modified_by`, `approval_note`, `new_po_no`, `reject_note1`, `reject_note2`, `other_charge`, `discount`, `payment_term`, `despatch`, `shipping_address`, `miscellaneous`, `eta_date`, `received_date`, `rev`, `purchase_order_id`
			)
			(SELECT ".$revBy.", `po_no`, `dept_id`, `po_date`, `order_type`, `supplier_id`, `promised_delivery_date`, `currency`, `exchange_rate`, `rate_date`, `final_quote_url`, `signed_po_url`, `signed_pr_url`, `no`, `year`, `charge1`, `charge2`, `charge1_desc`, `charge2_desc`, `note`, `title`, `flag`, `approved`, `created_date`, `is_complete`, `ref_offer_no`, `ref_internal_bmt`, `ask_approval_date`, `approval_date`, `approval_2_date`, `approval_2_by`, `approval_by`, `created_by`, `modified_date`, `modified_by`, `approval_note`, `new_po_no`, `reject_note1`, `reject_note2`, `other_charge`, `discount`, `payment_term`, `despatch`, `shipping_address`, `miscellaneous`, `eta_date`, `received_date`, `rev`, `id` as purchase_order_id FROM `purchase_order` where id = ".$json['id'].")
			");

			$model->db->query("
			insert into purchase_order_comment_history (
				`notes`, `purchase_order_id`, `created_date`, `created_by`
			)
			(SELECT `notes`, (select id from purchase_order_history where purchase_order_id = ".$json['id']." order by id desc limit 1) as `purchase_order_id`, `created_date`, `created_by` FROM `purchase_order_comment` where purchase_order_id = ".$json['id'].")
			");

			$model->db->query("
			insert into purchase_order_item_history (
				`purchase_order_id`, `bom_id`, `price_per_item`, `item_id`, `order_qty`, `active`, `flag`, `allocation`, `pr_part_id`, `complete_qty`, `modified_date`, `modified_by`, `created_date`, `created_by`
			)
			(SELECT (select id from purchase_order_history where purchase_order_id = ".$json['id']." order by id desc limit 1) as `purchase_order_id`, `bom_id`, `price_per_item`, `item_id`, `order_qty`, `active`, `flag`, `allocation`, `pr_part_id`, `complete_qty`, `modified_date`, `modified_by`, `created_date`, `created_by` FROM `purchase_order_item` where purchase_order_id = ".$json['id'].")
			");
			$rev = number_format($d->rev, 0)+1;
			$po_no = explode("-REV", $d->po_no);
			$po_no = $po_no[0]."-REV.".sprintf('%02d', $rev);
			$model->db->query("update purchase_order set 
			reject_note1 = null, 
			reject_note2 = null, 
			approval_by = null, 
			approval_2_by = null, 
			approval_2_date = null, 
			approval_date = null, 
			ask_approval_date = null, 
			approved = 0, 
			approval_note = '', 
			rev = ".$rev.", po_no = '".$po_no."' where id = ".$json['id']."");
			$response = [
				'status'   => true
			];

			$msg = view('email/po_revisi',[
				'approval' => 1,
				'po_no' => $d->po_no,
				'title' => $d->title,
				'rfq' => $ref[0]??"-",
				'pr_no' => $ref[1]??"-"
			]);
			helper(['Wa_helper']);
			$msgWa = preg_replace([
				'/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', //remove all style tags
				'/<(?:br|p|tr)[^>]*>/i', //replace br p with ' '
				'/<[^>]*>/',  //replace any tag with ''
				'/\s+/u', //remove run on space - replace using the unicode flag
				'/^\s+|\s+$/u', //trim - replace using the unicode flag
				'/\~nl\~/i'
			],[
				'', "~nl~", '', ' ', '', "\r\n"
			], $msg);
			sendWa('62811309386', $msgWa);
		}
		else{
			$response = [
				'status'   => false,
				'data'	=> $q->approved!=3 ? 'PO not yet approved!' : 'You are not allowed to use this feature!'
			];
		}
		return $this->respond($response);
	} */
 
    // create a product
    public function create()
    {
        $model = new PurchaseOrderHistoryModel();
        $json = $this->request->getJSON();
        $options = [
            'cost' => 11  
        ];
        
        $data = [
        ];

        foreach($model->allowedFields as $value) 
        {
			if(isset($json->{$value}))
				$data[$value] = $json->{$value};
        }
		
		$session = session();
		$s = $session->get();
		if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
			$data["created_by"] = $s["userid"];
		
		$y = date("Y");
		$m = date('m');

		$noQuery = $model->lastNumber($y, $data['dept_id']);
		if(!$noQuery[0])
			return $this->respondCreated(['status' => false, 'data'=>$noQuery[1]], 200);
		$no = $noQuery[1][0]->no;
		$dept_code = $noQuery[1][0]->dept_code;
		if(!isset($data["po_no"])){
			$data["po_no"] = "$no/".$dept_code."-PO/$m/$y";
			$data["year"] = $y;
			$data["no"] = $noQuery[1][0]->number;
		}
		else{
			$tmp = explode('/', $data["po_no"]);
			$data["year"] = $tmp[3];
			$data["no"] = str_pad($tmp[0],4,"0", STR_PAD_LEFT);
		}
		
        $model->insert($data);
        $response = [
            'status'   => true,
            'data'    => 'Data Saved'
        ];
        
        return $this->respondCreated($response, 200);
    }

	public function insertInfo($info, $uid, $pk){
		$model = new PaymentModel();
		$data = $model->db->query("insert into info (info, uid, url) values('".$info."', '".$uid."', 'https://internal.buanamultiteknik.com/api/bom/purchase_order/info?id=".$pk."')");
	}

	public function info(){
        $model = new PurchaseOrderHistoryModel();
        $json = $_REQUEST;
		$q = $model->info($json['id']);
		$text = $q[1];
		if($q[0]){
			$d = $q[1][0];
			$date = $d->approval_date;
// 			if($d->approval_day<15 && ($d->supplier_id == 3 || $d->supplier_id == 8 || $d->supplier_id == 59 || $d->supplier_id == 11)){
// 			    $tmp = explode('-', $date);
// 			    $tmp[2] = explode(' ', $tmp[2]);
// 			    $tmp[2][0] = 15;
// 			    $tmp[2] = implode(' ', $tmp[2]);
// 			    $date = implode('-', $tmp);
// 			}
			$date2 = $d->po_date;
// 			if($d->po_date_day<15 && ($d->supplier_id == 3 || $d->supplier_id == 8 || $d->supplier_id == 59 || $d->supplier_id == 11)){
// 			    $tmp = explode('-', $date2);
// 			    $tmp[2] = explode(' ', $tmp[2]);
// 			    $tmp[2][0] = 15;
// 			    $tmp[2] = implode(' ', $tmp[2]);
// 			    $date2 = implode('-', $tmp);
// 			}
			//ask_day
			$date3 = $d->ask_approval_date;
// 			if($d->ask_day<15 && ($d->supplier_id == 3 || $d->supplier_id == 8 || $d->supplier_id == 59 || $d->supplier_id == 11)){
// 			    $tmp = explode('-', $date2);
// 			    $tmp[2] = explode(' ', $tmp[2]);
// 			    $tmp[2][0] = 15;
// 			    $tmp[2] = implode(' ', $tmp[2]);
// 			    $date3 = implode('-', $tmp);
// 			}
			$text = "
			a. PO No: ".$d->po_no."<br/>
			b. PO Date: ".$date2."<br/>
			c. PO Title: ".$d->title."<br/>
			d. PSupplier: ".$d->supplier."<br/>
			e. Ask for approval date: ".$date3."<br/>
			f. Approved date: ".$date."<br/>
			g. Approved by: ".$d->approved_by_name."<br/>
			";
		}
        $response = [
            'status'   => $q[0],
            'data'    => $text
        ];
        
        return $this->respondCreated($response, 200);
	}
 
    public function update($pk = null)
    {
		
		$email = \Config\Services::email();
        $model = new PurchaseOrderHistoryModel();
        $json = $this->request->getJSON();
        if($json){
            
            $data = [
            ];

            foreach($json as $key => $value) 
            {
                if($key!='pk')
                    $data[$key] = $value;
            }
        }

		$session = session();
		$s = $session->get();
		if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			$data["modified_by"] = $s["userid"];
		if(in_array("modified_date", $model->allowedFields))
			$data["modified_date"] = date("Y-m-d H:i:s");
        $supplier_id = -1;
        $po_id = -1;
		if(isset($data["approved"])){
			$q = $model->info($pk);
			if($data["approved"]==-1){
				//email ask approval 1

				if($q[0]){
					$d = $q[1][0];
					$ref = explode('//', $d->ref_internal_bmt ?? "");
					$msg = view('email/po_rejected',[
						'approval' => 1,
						'po_no' => $d->po_no,
						'title' => $d->title,
						'rfq' => $ref[0]??"-",
						'pr_no' => $ref[1]??"-"
					]);
					helper(['Wa_helper']);
					$msgWa = preg_replace([
		                '/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', //remove all style tags
		                '/<(?:br|p|tr)[^>]*>/i', //replace br p with ' '
                		'/<[^>]*>/',  //replace any tag with ''
                		'/\s+/u', //remove run on space - replace using the unicode flag
                		'/^\s+|\s+$/u', //trim - replace using the unicode flag
                		'/\~nl\~/i'
	                ],[
		                '', "~nl~", '', ' ', '', "\r\n"
					], $msg);
					sendWa('62811309386', $msgWa);
				}
			}

			if($data["approved"]==1){
				//email ask approval 1

				if($q[0]){
					$d = $q[1][0];
					/*$email->setFrom('internal@buanamultiteknik.com', 'Internal');
					$email->setTo('fenty@buanamultiteknik.com');*/
					$ref = explode('//', $d->ref_internal_bmt ?? "");
					$msg = view('email/ask_approval',[
						'approval' => 1,
						'po_no' => $d->po_no,
						'title' => $d->title,
						'rfq' => $ref[0]??"-",
						'pr_no' => $ref[1]??"-"
					]);
					/*$email->setSubject("Ask Approval 1 ".$d->po_no);
					$email->setMessage($msg);
					$email->setMailType('html');

					$email->send();*/
					helper(['Wa_helper']);
					//send wa ke approval 1
					$msgWa = preg_replace([
		                '/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', //remove all style tags
		                '/<(?:br|p|tr)[^>]*>/i', //replace br p with ' '
                		'/<[^>]*>/',  //replace any tag with ''
                		'/\s+/u', //remove run on space - replace using the unicode flag
                		'/^\s+|\s+$/u', //trim - replace using the unicode flag
                		'/\~nl\~/i'
	                ],[
		                '', "~nl~", '', ' ', '', "\r\n"
					], $msg);
					sendWa('62811309386', $msgWa);
				}
			}
			if($data["approved"]==2){
				if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
					$data["approval_by"] = $s["userid"];

				if(in_array("approval_date", $model->allowedFields))
					$data["approval_date"] = date("Y-m-d H:i:s");
				// info kepada admin jika telah d aproval 1
				

				if($q[0]){
					$d = $q[1][0];
					//email info approved 1
					/*$email->setFrom('internal@buanamultiteknik.com', 'Internal');
					$email->setTo('fenty@buanamultiteknik.com');*/
					$ref = explode('//', $d->ref_internal_bmt ?? "");
					$msg = view('email/approved',[
						'approval' => 1,
						'date' => $data["approval_date"],
						'po_no' => $d->po_no,
						'title' => $d->title,
						'rfq' => $ref[0]??"-",
						'pr_no' => $ref[1]??"-"
					]);
					/*$email->setSubject("PO ".$d->po_no." Approved by approval 1");
					$email->setMessage($msg);
					$email->setMailType('html');

					$email->send();*/

					helper(['Wa_helper']);
					//send wa ke admin
					$msgWa = preg_replace([
		                '/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', //remove all style tags
		                '/<(?:br|p|tr)[^>]*>/i', //replace br p with ' '
                		'/<[^>]*>/',  //replace any tag with ''
                		'/\s+/u', //remove run on space - replace using the unicode flag
                		'/^\s+|\s+$/u', //trim - replace using the unicode flag
                		'/\~nl\~/i'
	                ],[
		                '', "~nl~", '', ' ', '', "\r\n"
					], $msg);
					//send ke admin
					//sendWa('628113013485', $msgWa);

					// email ask approval 2
					/*$email->clear();
					$email->setFrom('internal@buanamultiteknik.com', 'Internal');
					$email->setTo('fenty@buanamultiteknik.com');*/
					$ref = explode('//', $d->ref_internal_bmt ?? "");
					$msg = view('email/ask_approval',[
						'approval' => 2,
						'po_no' => $d->po_no,
						'title' => $d->title,
						'rfq' => $ref[0]??"-",
						'pr_no' => $ref[1]??"-"
					]);
					/*$email->setSubject("Ask Approval 2 ".$d->po_no);
					$email->setMessage($msg);
					$email->setMailType('html');
					$email->send();*/
					helper(['Wa_helper']);
					//send wa ke approval 2
					$msgWa = preg_replace([
		                '/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', //remove all style tags
		                '/<(?:br|p|tr)[^>]*>/i', //replace br p with ' '
                		'/<[^>]*>/',  //replace any tag with ''
                		'/\s+/u', //remove run on space - replace using the unicode flag
                		'/^\s+|\s+$/u', //trim - replace using the unicode flag
                		'/\~nl\~/i'
	                ],[
		                '', "~nl~", '', ' ', '', "\r\n"
					], $msg);
					//sendWa('6285640013092', $msgWa);
				//$this->insertInfo("<b>Approved:</b> ".date("Y-m-d H:i:s")."", "po_approved_".$pk, $pk);
				}
			}

			if($data["approved"]==3){
				if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
					$data["approval_2_by"] = $s["userid"];

				if(in_array("approval_2_date", $model->allowedFields))
					$data["approval_2_date"] = date("Y-m-d H:i:s");
				
				$this->insertInfo("<b>Approved:</b> ".date("Y-m-d H:i:s")."", "po_approved_".$pk, $pk);

				if($q[0]){
					$d = $q[1][0];
					$supplier_id = $d->supplier_id;
					$po_id = $pk;
					//$email->setFrom('internal@buanamultiteknik.com', 'Internal');
					//$email->setTo('fenty@buanamultiteknik.com');
					$ref = explode('//', $d->ref_internal_bmt ?? "");
					$msg = view('email/approved',[
						'approval' => 2,
						'date' => $data["approval_2_date"],
						'po_no' => $d->po_no,
						'title' => $d->title,
						'rfq' => $ref[0]??"-",
						'pr_no' => $ref[1]??"-"
					]);
					/*$email->setSubject("PO ".$d->po_no." Approved by approval 2");
					$email->setMessage($msg);
					$email->setMailType('html');

					$email->send();*/
					helper(['Wa_helper']);
					//send wa ke admin
					$msgWa = preg_replace([
		                '/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', //remove all style tags
		                '/<(?:br|p|tr)[^>]*>/i', //replace br p with ' '
                		'/<[^>]*>/',  //replace any tag with ''
                		'/\s+/u', //remove run on space - replace using the unicode flag
                		'/^\s+|\s+$/u', //trim - replace using the unicode flag
                		'/\~nl\~/i'
	                ],[
		                '', "~nl~", '', ' ', '', "\r\n"
					], $msg);
					sendWa('628113013485', $msgWa);
				}
			}

			if($data["approved"]==1){

				if(in_array("ask_approval_date", $model->allowedFields))
					$data["ask_approval_date"] = date("Y-m-d H:i:s");
			}
		}
        // Insert to Database
        $model->update($pk, $data);
		if(isset($data["approved"])){
		    if($data["approved"]==3){
		        if($supplier_id == 3 || $supplier_id == 8 || $supplier_id == 59)
		            $this->autoemailsupplierfrompo($supplier_id, $po_id);
		    }
		}
        $response = [
            'status'   => true
        ];
        return $this->respond($response);
    }
 
    // delete product
    public function delete($pk = null)
    {
        $model = new PurchaseOrderHistoryModel();
        $data = $model->find($pk);
        if($data){
            //$model->delete($pk);
            $model->update($pk, ["flag"=>0]);
            $response = [
                'status'   => true
            ];
             
            return $this->respond($response);
        }else{
            $response = [
                'status'   => false
            ];
            
            return $this->respond($response);
        }
         
    }

	function filename_sanitizer($unsafeFilename){

		// our list of "unsafe characters", add/remove characters if necessary
		$dangerousCharacters = array(" ", '"', "'", "&", "/", "\\", "?", "#");
	  
		// every forbidden character is replaced by an underscore
		$safe_filename = str_replace($dangerousCharacters, '_', $unsafeFilename);
	  
		return $safe_filename;
	}

	public function emailsupplier(){
        $model = new PurchaseOrderHistoryModel();
		$json = $this->request->getJSON();
		//return $this->respond(["a"=>$json->cc]);
		$result = $model->db->query("select * from m_supplier where id = ".$json->supplier_id);
		$po = $model->db->query("select * from purchase_order where id = ".$json->po_id);
		$po = $po->getResult()[0];
		$msg = $result->getResult()[0]->salutation;
		$msg = str_replace("{PO No}",$po->po_no,$msg);
		$msg = trim(str_replace("{Title PO}",$po->title,$msg));
		//return $this->respond(["a"=>$msg, "b"=> implode(",", explode(";;",$json->to)), "c"=> implode(",", explode(";;",$json->cc))]);
		try {
			$session = session();
			$s = $session->get();
			$email = \Config\Services::email();
			$email->setFrom('internal@buanamultiteknik.com', 'Internal');
			$email->setBCC('internal@buanamultiteknik.com,fenty@buanamultiteknik.com');
			//$email->setTo('fenty@buanamultiteknik.com');
			//email asli to
			$email->setTo(implode(",", explode(";;",$json->to)));
			//email asli cc
			$email->setCc(implode(",", explode(";;",$json->cc)));
			//$ref = explode('//', $d->ref_internal_bmt ?? "");
			/* $msg = view('email/supplier',[
				'approval' => 1,
				'date' => $data["approval_date"],
				'po_no' => $d->po_no,
				'title' => $d->title,
				'rfq' => $ref[0]??"-",
				'pr_no' => $ref[1]??"-",
				'sender' => $ref[1]??"-"
			]); */
			//get attachment
			if($po->signed_po_url!="" && $po->signed_po_url!=null){
				//return $this->respond(["a"=>pathinfo($po->signed_po_url, PATHINFO_EXTENSION )]);
				$f = $this->filename_sanitizer($po->po_no);
				$f = $f.".".pathinfo($po->signed_po_url, PATHINFO_EXTENSION );
				$file_name = "./download/".$f;
				file_put_contents($file_name, file_get_contents($po->signed_po_url));
				$email->attach($file_name);
			}
			$email->setSubject("Signed PO ".$po->po_no." - ".$po->title);
			$email->setMessage($msg);
			//$email->setMailType('html');

			$email->send();
			if($po->signed_po_url!="" && $po->signed_po_url!=null){
				unlink($file_name);
			}
            $response = [
                'status'   => true
            ];
            
            return $this->respond($response);
		} catch (Exception $e) {
            $response = [
                'status'   => false,
				"message" => $e
            ];
            
            return $this->respond($response);
		}
	}

	public function autoemailsupplierfrompo($supplier_id, $po_id){
        $model = new PurchaseOrderHistoryModel();
		//$json = $this->request->getJSON();
		//return $this->respond(["a"=>$json->cc]);
		$result = $model->db->query("select * from m_supplier where id = ".$supplier_id);
		$po = $model->db->query("select * from purchase_order where id = ".$po_id);
		$po = $po->getResult()[0];
		$msg = $result->getResult()[0]->salutation;
		$to = $result->getResult()[0]->email;
		$to = preg_replace('/\s+/', '', $to);
		$to = preg_replace('/\;$/', '', $to);
		$to = explode(';', $to);
		$msg = str_replace("{PO No}",$po->po_no,$msg);
		$msg = trim(str_replace("{Title PO}",$po->title,$msg));
		//return $this->respond(["a"=>$msg, "b"=> implode(",", explode(";;",$json->to)), "c"=> implode(",", explode(";;",$json->cc))]);
		try {
			$session = session();
			$s = $session->get();
			$email = \Config\Services::email();
			$email->setFrom('internal@buanamultiteknik.com', 'Internal');
			$email->setBCC('internal@buanamultiteknik.com,fenty@buanamultiteknik.com');
			//$email->setTo('fenty@buanamultiteknik.com');
			//email asli to
			$email->setTo($to[0]);
			//email asli cc
			$email->setCc(implode(",",array_slice($to, 1)));
			
			//get attachment
			//if($po->signed_po_url!="" && $po->signed_po_url!=null){
			$name = $this->filename_sanitizer($po->po_no);
			$url = 'https://decafet.com/api/report?type=pdf&file=po&filename=' . $name . '&engine=easytemplate&po_id=' . $po_id;
			$f = $name.".pdf";
			$file_name = "./download/".$f;
			file_put_contents($file_name, file_get_contents($url));
			$email->attach($file_name);
			//}
			$email->setSubject("Signed PO ".$po->po_no." - ".$po->title);
			$email->setMessage($msg);
			//$email->setMailType('html');

			$email->send();
			if($po->signed_po_url!="" && $po->signed_po_url!=null){
				unlink($file_name);
			}
            /*$response = [
                'status'   => true
            ];*/
            
            return true;//$this->respond($response);
		} catch (Exception $e) {
            /*$response = [
                'status'   => false,
				"message" => $e
            ];*/
            
            return false;//$this->respond($response);
		}
	}

	public function autoemailsupplier(){
        $model = new PurchaseOrderHistoryModel();
		$json = $this->request->getJSON();
		//return $this->respond(["a"=>$json->cc]);
		$result = $model->db->query("select * from m_supplier where id = ".$json->supplier_id);
		$po = $model->db->query("select * from purchase_order where id = ".$json->po_id);
		$po = $po->getResult()[0];
		$msg = $result->getResult()[0]->salutation;
		$to = $result->getResult()[0]->email;
		$to = preg_replace('/\s+/', '', $to);
		$to = preg_replace('/\;$/', '', $to);
		$to = explode(';', $to);
		$msg = str_replace("{PO No}",$po->po_no,$msg);
		$msg = trim(str_replace("{Title PO}",$po->title,$msg));
		//return $this->respond(["a"=>$msg, "b"=> implode(",", explode(";;",$json->to)), "c"=> implode(",", explode(";;",$json->cc))]);
		try {
			$session = session();
			$s = $session->get();
			$email = \Config\Services::email();
			$email->setFrom('internal@buanamultiteknik.com', 'Internal');
			$email->setBCC('internal@buanamultiteknik.com,fenty@buanamultiteknik.com');
			$email->setTo('fenty@buanamultiteknik.com');
			//email asli to
			//$email->setTo($to[0]);
			//email asli cc
			//$email->setCc(array_slice($to, 1));
			
			//get attachment
			//if($po->signed_po_url!="" && $po->signed_po_url!=null){
			$name = $this->filename_sanitizer($po->po_no);
			$url = 'https://decafet.com/api/report?type=pdf&file=po&filename=' . $name . '&engine=easytemplate&po_id=' . $json->po_id;
			$f = $name.".pdf";
			$file_name = "./download/".$f;
			file_put_contents($file_name, file_get_contents($url));
			$email->attach($file_name);
			//}
			$email->setSubject("Signed PO ".$po->po_no." - ".$po->title);
			$email->setMessage($msg);
			//$email->setMailType('html');

			$email->send();
			if($po->signed_po_url!="" && $po->signed_po_url!=null){
				unlink($file_name);
			}
            /*$response = [
                'status'   => true
            ];*/
            
            return true;//$this->respond($response);
		} catch (Exception $e) {
            /*$response = [
                'status'   => false,
				"message" => $e
            ];*/
            
            return false;//$this->respond($response);
		}
	}
 
    // cancel product
    public function cancel()
    {
		//select * from invoice i left join payment_item pi on pi.invoice_id = i.id left join payment p on p.id = pi.payment_id where p.flag = 1 and pi.flag = 1
        $model = new PurchaseOrderHistoryModel();
		$json = $_REQUEST;
		$json = (Object) $json;
		$session = session();
		$s = $session->get();
		if(!isset($s["userid"])){
			$response = [
				'status'   => false,
				"message" => "Anda tidak mempunyai akses!"
			];
		}
		else{
			$data = $model->db->query("select i.id from invoice i left join payment_item pi on pi.invoice_id = i.id left join payment p on p.id = pi.payment_id where p.flag = 1 and pi.flag = 1 and i.po_id = ".$json->pk);
			$data = $data->getResult();
			if(count($data)>0){
				$response = [
					'status'   => false,
					"message" => "PO telah mempunyai payment!"
				];
			}
			else{
				$model->update($pk, ["flag"=>-4, "canceled_by"=>$s["userid"], "canceled_date"=>date("Y-m-d H:i:s")]);
				$model->db->query("update invoice set flag = -4 where po_id = ".$json->pk);
				$response = [
					'status'   => true
				];
			}
		}
		
		return $this->respond($response);
    }
 
}