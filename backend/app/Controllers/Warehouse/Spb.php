<?php namespace App\Controllers\Warehouse;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Warehouse\SpbModel;
 
class Spb extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new SpbModel();
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
 
    public function create()
    {
        $model = new SpbModel();
        $json = $this->request->getJSON();
        
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
	
	public function insertInfo($info, $uid, $pk, $approval){
		$model = new SpbModel();
		$data = $model->db->query("insert into info (info, uid, url) values('".$info."', '".$uid."', 'https://internal.buanamultiteknik.com/api/transaction/spb/info?approval=".$approval."&id=".$pk."')");
	}

	public function info(){
        $model = new SpbModel();
        $json = $_REQUEST;
		$q = $model->query("select s.*, 
		a.name as approved_by_name,
		b.name as approved_by2_name,
		c.name as approved_by3_name 
		
		from spb s
		
		left join users a on a.id = s.approved_by
		left join users b on b.id = s.approved2_by
		left join users c on c.id = s.approved3_by
		
		where s.id = ".$json['id']);
		$data = $q->getResult()[0];
		if($json['approval']==1)
			$text = "Approved by ".$data->approved_by_name."</br>Approved date ".$data->approved_date;
		if($json['approval']==2)
			$text = "Approved by ".$data->approved_by2_name."</br>Approved date ".$data->approved2_date;
		if($json['approval']==3)
			$text = "Approved by ".$data->approved_by3_name."</br>Approved date ".$data->approved3_date;

		$response = [
            'status'   => true,
            'data'    => $text
        ];
        
        return $this->respondCreated($response, 200);
	}

	public function revisi(){
        $model = new SpbModel();
        $json = $_REQUEST;
		$q = $model->query("select s.*,
		LPAD(s.id, 4, 0) as spb_no
		
		from spb s
		
		where s.id = ".$json['id']);
		$d = $q->getResult()[0];
		$session = session();
		$s = $session->get();
		if($d->approved<4 && isset($s["userid"])){
			$revBy = $s["userid"];
			$model->db->query("
			insert into spb_history (
				`id`, `revision_by`, `po_id`, `approved_by`, `approved_date`, `approved2_by`, `approved2_date`, `approved3_by`, `approved3_date`, `rejected_date`, `rejected_by`, `created_date`, `created_by`, `modified_date`, `modified_by`, `flag`, `approved`, `kelog_id`, `notes`, `kabag_id`, `ask_approval_notes`, `approval_notes`, `approval2_notes`, `approval3_notes`, `reject_notes`, `revisi_notes`, `reject2_notes`, `photo`, `bom_receive_id`, `rev`
			)
			(SELECT `id`, $revBy, `po_id`, `approved_by`, `approved_date`, `approved2_by`, `approved2_date`, `approved3_by`, `approved3_date`, `rejected_date`, `rejected_by`, `created_date`, `created_by`, `modified_date`, `modified_by`, `flag`, `approved`, `kelog_id`, `notes`, `kabag_id`, `ask_approval_notes`, `approval_notes`, `approval2_notes`, `approval3_notes`, `reject_notes`, `revisi_notes`, `reject2_notes`, `photo`, `bom_receive_id`, `rev` FROM `spb` where id = ".$json['id'].")
			");

			$model->db->query("
			insert into stock_history (
				`id`, `item_id`, `qty`, `allocation`, `po_id`, `created_date`, `created_by`, `modified_date`, `modified_by`, `purchase_order_item_id`, `flag`, `spb_id`, `notes`, `photo`, `transfer_from`
			)
			(SELECT `id`, `item_id`, `qty`, `allocation`, `po_id`, `created_date`, `created_by`, `modified_date`, `modified_by`, `purchase_order_item_id`, `flag`, (select idx from spb_history where id = ".$json['id']." order by id desc limit 1) as `spb_id`, `notes`, `photo`, `transfer_from` FROM `stock` where spb_id = ".$json['id'].")
			");
			$rev = number_format($d->rev, 0)+1;
			//$spb_no = explode("-REV", $d->pr_no);
			//$spb_no = $spb_no[0]."-REV.".sprintf('%02d', $rev);
			/* $data["peminta_setuju"] = null;
				$data["penyetuju_setuju"] = null;
				$data["ask_approval"] = null;
				$data["askapproval_notes"] = null;
				$data["approval1_notes"] = null;
				$data["approval2_notes"] = null; */
			$model->db->query("update spb set 
			approved_by = null, 
			approved_date = null, 
			approved2_by = null, 
			approved2_date = null, 
			approved3_by = null, 
			approved3_date = null, 
			rejected_date = null,
			rejected_by = null, 
			approved = 0, 
			ask_approval_notes = '', 
			approval_notes = '', 
			approval2_notes = '', 
			approval3_notes = '', 
			rev = ".$rev." where id = ".$json['id']."");
			$response = [
				'status'   => true
			];

			$msg = view('email/spb_revisi',[
				'spb_no' => $d->spb_no,
				'notes' => $d->notes
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
			sendWa('6285247163916', $msgWa); 
		}
		else{
			$response = [
				'status'   => false,
				'data'	=> $q->approved<4 ? 'SPB already approved!' : 'You are not allowed to use this feature!'
			];
		}
		return $this->respond($response);
	}
	
	public function test(){
	    $email = \Config\Services::email();
	    $email->setFrom('internal@buanamultiteknik.com', 'Internal PT Buana Multi Teknik');
        $email->setTo('fenty@buanamultiteknik.com');
        $msg = view('email/spb_approved',[
			'spb_no' => 'asd'
	    ]);
        $email->setSubject("Asking approval Kepala Bagian");
        $email->setMessage('test');
        $email->setMailType('html');

        $email->send();
	}
 
    public function update($pk = null)
    {
        try{
            $model = new SpbModel();
            $email = \Config\Services::email();
            $json = $this->request->getJSON();
			$table = "spb";
			if(isset($json->is_bom)){
				if($json->is_bom == 1){
						$table = "bom_spb";
				}
			}
			$model->setTable($table);
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
    
            // $q = $model->query("select s.*, 
            //     a.name as approved_by_name,
            //     b.name as approved_by2_name,
            //     c.name as approved_by3_name,
            //     LPAD(s.id, 4, 0) as spb_no
                
            //     from spb s
                
            //     left join users a on a.id = s.approved_by
            //     left join users b on b.id = s.approved2_by
            //     left join users c on c.id = s.approved3_by
                
            //     where s.id = ".$pk);
                
            // $d = $q->getResult()[0];
    
    		if(isset($data["approved"])){
                $q = $model->info($pk);
                if($data["approved"]==1){
                    // $data["approved_by"] = $s["userid"];
    				// $data["approved_date"] =  date("Y-m-d H:i:s");
    				// $this->insertInfo("<b>Asking Approval Peminta:</b> ".date("Y-m-d H:i:s")."", "spb_approved_".$pk, $pk, 1);
   
                    //send email to peminta
                    if($q[0]){
                         $d = $q[1][0];
                         // $email->setFrom('internal@buanamultiteknik.com', 'Internal PT Buana Multi Teknik');
                         // $email->setTo($d->peminta_email);
                        $msg = view('email/spb_asking_peminta',[
                            'spb_no' => $d->spb_no,
                            'notes' => $d->notes,
                            'validator_name'=> $d->kelog_name,
                            'kabag_name'=>$d->kabag_name
        			    ]);
                        //   $email->setSubject("Logistik: Permintaan Approval Kedatangan Barang ".$d->spb_no."");
                        //   $email->setMessage($msg);
                        //   $email->setMailType('html');
                        //   $email->send();
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
                        sendWa($d->kelog_phone, $msgWa);
                    }
                }
    			if($data["approved"]==2){
    				$data["approved_by"] = $s["userid"];
    				$data["approved_date"] =  date("Y-m-d H:i:s");
    				$this->insertInfo("<b>Approved by Kepala Logistik:</b> ".date("Y-m-d H:i:s")."", "spb_approved_".$pk, $pk, 1);
   
                    //send email to kabag
                    if($q[0]){
                        $d = $q[1][0];
                        // $email->setFrom('internal@buanamultiteknik.com', 'Internal PT Buana Multi Teknik');
                        // $email->setTo($d->kabag_email);
                        $msg = view('email/spb_asking_kabag',[
                            'spb_no' => $d->spb_no,
                            'notes' => $d->notes,
                            'validator_name'=> $d->kelog_name,
                            'kabag_name'=>$d->kabag_name
        			    ]);
                        // $email->setSubject("Logistik: Permintaan Approval Kedatangan Barang ".$d->spb_no."");
                        // $email->setMessage($msg);
                        // $email->setMailType('html');
                        // $email->send();
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
                        sendWa($d->kabag_phone, $msgWa);
                    }
    			}
    			if($data["approved"]==3){
    				$data["approved2_by"] = $s["userid"];
    				$data["approved2_date"] =  date("Y-m-d H:i:s");
    				$this->insertInfo("<b>Approved by Kepala Logistik:</b> ".date("Y-m-d H:i:s")."", "spb_approved2_".$pk, $pk, 2);
    			}
    			if($data["approved"]==4){
    				$data["approved3_by"] = $s["userid"];
    				$data["approved3_date"] =  date("Y-m-d H:i:s");
    				$this->insertInfo("<b>Approved by Kabag:</b> ".date("Y-m-d H:i:s")."", "spb_approved3_".$pk, $pk, 3);

                    if($q[0]){
                        $d = $q[1][0];
                        // $email->setFrom('internal@buanamultiteknik.com', 'Internal PT Buana Multi Teknik');
                        $email->setTo($d->created_email);
                        $msg = view('email/spb_approved',[
                            'spb_no' => $d->spb_no,
                            'notes' => $d->notes,
                            'validator_name'=> $d->kelog_name,
                            'approver_name'=>$d->kabag_name
        			    ]);
                        // $email->setSubject("Logistik: Permintaan Approval Kedatangan Barang ".$d->spb_no."");
                        // $email->setMessage($msg);
                        // $email->setMailType('html');
                        // $email->send();

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
                        sendWa($d->created_phone, $msgWa);
                    }
    			}
    			if($data["approved"]==-1){
    				$data["rejected_by"] = $s["userid"];
    				$data["rejected_date"] =  date("Y-m-d H:i:s");

                    if($q[0]){
                        $d = $q[1][0];
                        // $email->setFrom('internal@buanamultiteknik.com', 'Internal PT Buana Multi Teknik');
                        $email->setTo($d->created_email);
                        $msg = view('email/spb_rejected',[
                            'spb_no' => $d->spb_no,
                            'notes' => $d->notes,
                            'rejected_by'=> $data["rejected_by"],
        			    ]);
                        // $email->setSubject("Logistik: Permintaan Approval Kedatangan Barang ".$d->spb_no."");
                        // $email->setMessage($msg);
                        // $email->setMailType('html');
                        // $email->send();

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
                        sendWa($d->created_phone, $msgWa);
                    }

    			}
    
    			/*$msg = view('email/spb_approved',[
    				'approval' => $data["approved"],
    				'date' => date("Y-m-d H:i:s"),
    				'peminta' => $d->approved_by_name,
    				'kabag' => $d->approved_by3_name,
    				'spb_no' => $d->spb_no
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
    			sendWa('6285640013092', $msgWa);*/
    		}
    		$model->transBegin();
            $model->update($pk, $data);
    		if(isset($data["approved"])){
    			if($data["approved"]==4){
					if($table=="spb")
    					$model->query("update stock set flag = 1 where spb_id = ".$pk);
					else
    					$model->query("update stock set flag = 1 where bom_spb_id = ".$pk);
				}
    		}
    		$model->transComplete();
    		if($model->transStatus() === false){
    			$model->transRollback();
    			$response = [
    				'status'   => false,
    				'data'    => $model->errors(),
					'debug' => $data
    			];
    		}
    		else{
    			$model->transCommit();
    			$affected = $model->affectedRows();
    			/* if($affected != 0)
    				$response = [
    					'status'   => true
    				];
    			else */
    			$response = [
    				'status'   => true
    			];
    		}
            return $this->respond($response);
        }
        catch(\Throwable $e){
            $response = [
                'status'   => false,
				'data' => $e->getMessage(),
				'debug' => $data
            ];
            
            return $this->respond($response);
        }
    }
 
    public function delete($pk = null)
    {
        $model = new SpbModel();
        $data = $model->find($pk);
        if($data){
            //$model->delete($pk);
			$json = $_REQUEST;
			$data = [];
			$data["flag"] = $json["flag"] ?? 0;
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
        }else{
            $response = [
                'status'   => false,
				'data' => 'Data not found!'
            ];
            
            return $this->respond($response);
        }
         
    }

	public function spb(){
		$json = $_REQUEST;
		$model = new SpbModel();
		//$d = $model->getOne($json["id"]);
		$response = [
            'status'   => true,
            //'data'    => "<b>Approved Date:</b> ".$d[1][0]->approved2_date." <br/><b>Approved By:</b> ".$d[1][0]->approved2." <br/>"
        ];
		return $this->respondCreated($response, 200);
	}
 
}