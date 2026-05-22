<?php namespace App\Controllers\Transaction;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Transaction\NpbModel;
 
class Npb extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new NpbModel();
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
                if(isset($json->filterType["item_in_npb"])){
                    $json->filterType["item_in_npb"] = '%';
                }
                if(isset($json->filterType["subassembly_in_npb"])){
                    $json->filterType["subassembly_in_npb"] = '%';
                }
                $data = $model->read($json);
                return $this->respond(['status' => $data[0], 'data'=>$data[1], 'total' => $data[2]], 200);
            }
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }

	public function complete(){
		$model = new NpbModel();
		$json = $_REQUEST;
		$json = (Object) $json;
        $data = $model->complete($json);
		return $this->respond(['status' => $data[0], 'data'=>$data[1]], 200);
	}
 
    public function create()
    {
        $model = new NpbModel();
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
		$q = $model->query("select coalesce((SELECT idx from npb where date_format(now(), '%Y') = date_format(created_date, '%Y') order by idx desc limit 1), 0)+1 as number");

		$number = $q->getResult()[0]->number;
		$data["idx"] = $number;
        $model->insert($data);


        
		$id = $model->getInsertID();
        
        $q1=$model->query("select concat(LPAD($number, 4, 0),'/NPB/',date_format(created_date, '%m/%Y'), case when rev > 1 then concat('-REV', LPAD(REV, 2, 0)) else '' end) as npb__no from npb where id=$id");
        $npb__no = $q1->getResult()[0]->npb__no;

        $model->where('id', $id)

        ->set(['npb_no' => "NPB".str_pad($id, 4, "0", STR_PAD_LEFT), 'npb__no'=>$npb__no])
        ->update();

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
		$model = new NpbModel();
		$data = $model->db->query("insert into info (info, uid, url) values('".$info."', '".$uid."', 'https://internal.buanamultiteknik.com/api/transaction/npb/info?approval=".$approval."&id=".$pk."')");
	}

	public function revisi(){
        $model = new NpbModel();
        $json = $_REQUEST;
		$q = $model->query("select s.*,
		concat(LPAD(s.idx, 4, 0),'/NPB/',date_format(s.created_date, '%m/%Y'), 
			case when rev > 1 then
				concat('-REV', LPAD(s.REV, 2, 0))
			else '' end
		) as npbno
		from npb s
		
		where s.id = ".$json['id']);
		$d = $q->getResult()[0];
		$session = session();
		$s = $session->get();
		if($d->approved<=4 && isset($s["userid"])){
			$revBy = $s["userid"];
            $revisi_notes=$json['revisi_notes'];
			$model->db->query("
			insert into npb_history (   
				`id`, `npb_no`, `notes`, `peminta_id`, `kabag_id`, `flag`, `created_date`, `created_by`, `modified_by`, `modified_date`, `approved`, `approved_date`, `approved_by`, `rejected_date`, `rejected_by`, `idx`, `approved2_by`, `approved2_date`, `approved3_by`, `approved3_date`, `tipe`, `ask_approval_notes`, `approval_notes`, `approval2_notes`, `approval3_notes`, `reject_notes`,  `revisi_notes`, `reject2_notes`, `rev`, `revision_by`
			)
			(SELECT `id`, `npb_no`, `notes`, `peminta_id`, `kabag_id`, `flag`, `created_date`, `created_by`, `modified_by`, `modified_date`, `approved`, `approved_date`, `approved_by`, `rejected_date`, `rejected_by`, `idx`, `approved2_by`, `approved2_date`, `approved3_by`, `approved3_date`, `tipe`, `ask_approval_notes`, `approval_notes`, `approval2_notes`, `approval3_notes`, `reject_notes`, '$revisi_notes', `reject2_notes`, `rev`, '$revBy' FROM `npb` where id = ".$json['id'].")
			");

			$model->db->query("
			insert into npb_item (
				`id`, `npb_id`, `item_id`, `qty`, `po_id`, `allocation`, `notes`
			)
			(SELECT `id`, (select idxx from npb_history where id = ".$json['id']." order by id desc limit 1) as `npb_id`, `item_id`, `qty`, `po_id`, `allocation`, `notes` FROM `npb_item` where npb_id = ".$json['id'].")
			");
			$rev = number_format($d->rev, 0)+1;
			//$npbno = explode("-REV", $d->npbno);
			//$npbno = $npbno[0]."-REV.".sprintf('%02d', $rev);
			
			$model->db->query("update npb set 
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
			rev = ".$rev.",
            revisi_notes = ?
            where id = ".$json['id']."", [$revisi_notes]);
			$response = [
				'status'   => true
			];

			$msg = view('email/npb_revisi',[
				'npb_no' => $d->npbno,
				'notes' => $revisi_notes,
                'revise_name'=>$revBy
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
			sendWa('6285247163916', $msgWa);  //comment sementara
		}
		else{
			$response = [
				'status'   => false,
				'data'	=> $q->approved<4 ? 'You are not allowed to use this feature!' : 'You are not allowed to use this feature!'
			];
		}
		return $this->respond($response);
	}

	public function info(){
        $model = new NpbModel();
        $json = $_REQUEST;
		$q = $model->query("select s.*, 
		a.name as approved_by_name,
		b.name as approved_by2_name,
		c.name as approved_by3_name 
		
		from npb s
		
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
 
    public function update($pk = null)
    {
        try{
		    helper(['Wa_helper']);
            $model = new NpbModel();
            $email = \Config\Services::email();
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
		    if(isset($data["approved"])){
                $q = $model->info($pk);
                if($data["approved"]==1){
                    $q = $model->info($pk);
                    if($q[0]){
                        $d = $q[1][0];
                        // $email->setFrom('internal@buanamultiteknik.com', 'Internal PT Buana Multi Teknik');
                        // $email->setTo($d->kabag_email);
                        // $email->setTo("fenty@buanamultiteknik.com");
                        $msg = view('email/npb_asking_peminta',[
                            'npb_no' => $d->npbno,
                            'notes' => $d->notes,
                            'peminta_name'=> $d->peminta_name,
                            'kabag_name'=>$d->kabag_name
                        ]);
                        // $email->setSubject("Logistik: Permintaan Approval Pengeluaran Barang ".$d->npb_no."");
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
                        sendWa($d->peminta_phone, $msgWa); //comment sementara
                    }
                    }

			    if($data["approved"]==2){
				    $data["approved_by"] = $s["userid"];
				    $data["approved_date"] =  date("Y-m-d H:i:s");
				    $this->insertInfo("<b>Approved by Peminta:</b> ".date("Y-m-d H:i:s")."", "npb_approved_".$pk, $pk, 1);
                    if($q[0]){
                        $d = $q[1][0];
                        // $email->setFrom('internal@buanamultiteknik.com', 'Internal PT Buana Multi Teknik');
                        // $email->setTo($d->kabag_email);
                        // $email->setTo("fenty@buanamultiteknik.com");
                        $msg = view('email/npb_asking_kabag',[
                            'npb_no' => $d->npbno,
                            'notes' => $d->notes,
                            'validator_name'=> $d->peminta_name,
                            'kabag_name'=>$d->kabag_name
                        ]);
                        // $email->setSubject("Logistik: Permintaan Approval Pengeluaran Barang ".$d->npb_no."");
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
                        // sendWa('6285640013092', $msgWa);
                        sendWa($d->kabag_phone, $msgWa); //comment sementara
                    }
                }
			    if($data["approved"]==3){
				    $data["approved2_by"] = $s["userid"];
				    $data["approved2_date"] =  date("Y-m-d H:i:s");
				    $this->insertInfo("<b>Approved by Kepala Logistik:</b> ".date("Y-m-d H:i:s")."", "npb_approved2_".$pk, $pk, 2);
			    }
			    if($data["approved"]==4){
				    $data["approved3_by"] = $s["userid"];
				    $data["approved3_date"] =  date("Y-m-d H:i:s");
				    $this->insertInfo("<b>Approved by Direktur:</b> ".date("Y-m-d H:i:s")."", "npb_approved3_".$pk, $pk, 3);

                    if($q[0]){
                        $d = $q[1][0];
                        // $email->setFrom('internal@buanamultiteknik.com', 'Internal PT Buana Multi Teknik');
                        // $email->setTo($d->created_email);
                        // $email->setTo("fenty@buanamultiteknik.com");
                        $msg = view('email/npb_approved',[
                            'npb_no' => $d->npbno,
                            'notes' => $d->notes,
                            'validator_name'=> $d->validator_name,
                            'approver_name'=>$d->kabag_name,
                            'npb_id'=> $d->npb_id
                        ]);
                        // $email->setSubject("Logistik: Approval Pengeluaran Barang ".$d->npb_no."");
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
                        sendWa($d->wa_id, $msgWa); //comment sementara
			    }
            }
			    if($data["approved"]==-1){
				    $data["rejected_by"] = $s["userid"];
				    $data["rejected_date"] =  date("Y-m-d H:i:s");

                    if($q[0]){
                        $d = $q[1][0];
                        // $email->setFrom('internal@buanamultiteknik.com', 'Internal PT Buana Multi Teknik');
                        // $email->setTo($d->created_email);
                        // $email->setTo("fenty@buanamultiteknik.com");
                        $msg = view('email/npb_rejected',[
                            'npb_no' => $d->npbno,
                            'notes' => $d->notes,
                            'rejected_by'=> $data["rejected_by"],
                        ]);
                        // $email->setSubject("Logistik: Approval Pengeluaran Barang ".$d->npb_no."");
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
                        sendWa($d->created_phone, $msgWa); //comment sementara
			    }
			}

			// $q = $model->query("select s.*, 
			// 	a.name as approved_by_name,
			// 	b.name as approved_by2_name,
			// 	c.name as approved_by3_name,
			// 	concat(LPAD(s.idx, 4, 0),'/NPB/',date_format(s.created_date, '%m/%Y')) as npbno
				
			// 	from npb s
				
			// 	left join users a on a.id = s.approved_by
			// 	left join users b on b.id = s.approved2_by
			// 	left join users c on c.id = s.approved3_by
				
			// 	where s.id = ".$pk);
				
			// $d = $q->getResult()[0];
			
			/*$msg = view('email/spb_approved',[
				'approval' => $data["approved"],
				'date' => date("Y-m-d H:i:s"),
				'peminta' => $d->approved_by_name,
				'kabag' => $d->approved_by3_name,
				'npb_no' => $d->npbno
			]);
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
        
    catch(\Throwable $e){
        $response = [
            'status'   => false,
            'data' => $e->getMessage()
        ];
        return $this->respond($response);
    }
}
 
    public function delete($pk = null)
    {
        $model = new NpbModel();
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
 
}