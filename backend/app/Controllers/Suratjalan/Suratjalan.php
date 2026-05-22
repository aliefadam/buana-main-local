<?php namespace App\Controllers\Suratjalan;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Suratjalan\SuratJalanModel;
 
class Suratjalan extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new SuratJalanModel();
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
                if(isset($json->filterType["item_in_sj"])){
                    $json->filterType["item_in_sj"] = '%';
                }
                $data = $model->read($json);
                return $this->respond(['status' => $data[0], 'data'=>$data[1], 'total' => $data[2]], 200);
            }
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }

 
    public function create()
    {
        try{

        $model = new SuratJalanModel();
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

            $y = date("Y");
            $m = date('m');
            $m = (int)$m;

            function numberToRomanRepresentation($number) {
                $map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
                $returnValue = '';
                while ($number > 0) {
                    foreach ($map as $roman => $int) {
                        if($number >= $int) {
                            $number -= $int;
                            $returnValue .= $roman;
                            break;
                        }
                    }
                }
                return $returnValue;
            }

            
            $noQuery = $model->lastNumber($data['from_info']);
            if(!$noQuery[0])
                return $this->respondCreated(['status' => false, 'data'=>$noQuery[1]], 200);
            $no = $noQuery[1][0]->no;
            $from_code = $noQuery[1][0]->from_code;
            if (!isset($data['sj_no'])){
                $data["sj_no"] = "$no/BMT-".$from_code."/".numberToRomanRepresentation($m)."/$y";
                // $data["sj_no"] = "abcd";
                $data["flag"]=1;
            }
           
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
        catch(\Throwable $e){
        $response = [
            'status'   => false,
            'data' => $e->getMessage()
        ];
        return $this->respond($response);
    }
}
	
	public function insertInfo($info, $uid, $pk, $approved){
		$model = new SuratJalanModel();
		$data = $model->db->query("insert into info (info, uid, url) values('".$info."', '".$uid."', 'https://internal.buanamultiteknik.com/api/suratjalan/suratjalan/info?approved=".$approved."&id=".$pk."')");
	}

	public function revisi(){
        try{
            $model = new SuratJalanModel();
            $json = $_REQUEST;
            $q = $model->query("select sj.*, u.name as pengirim_name, w.name as created_name, w.no_hp as created_phone from surat_jalan sj left join users u on u.id = sj.pengirim left join users w on w.id= sj.created_by where  sj.id = ".$json['id']);
            $d = $q->getResult()[0];
            $session = session();
            $s = $session->get();
            if($d->approved<=3 && isset($s["userid"])){
                $revBy = $s["userid"];
                $revised_notes=$json['revised_notes'];
                $revised_date=date("Y-m-d H:i:s");
                $model->db->query("
                insert into surat_jalan_history (   
                    `id`, `sj_no`, `sj_date`, `up`, `place`, `from_info`, `to_info`, `sj_notes`, `approved`, `pengirim`, `ask_approval_date`, `ask_approval_notes`, `approved_by`, `approved_date`, `approved_notes`, `rev`, `revised_by`, `revised_date`, `revised_notes`, `rejected_by`, `rejected_notes`, `created_date`, `created_by`, `modified_by`, `modified_date`, `idx`, `jenis_kendaraan`, `nopol`,  `nama`, `no_hp`, `jam_berangkat`, `flag`
                )
                (SELECT `id`, `sj_no`, `sj_date`, `up`, `place`, `from_info`, `to_info`, `sj_notes`, `approved`, `pengirim`, `ask_approval_date`, `ask_approval_notes`, `approved_by`, `approved_date`, `approved_notes`, `rev`, ".$revBy.", ?, ?, `rejected_by`, `rejected_notes`, `created_date`, `created_by`, `modified_by`, `modified_date`, `idx`, `jenis_kendaraan`, `nopol`,   `nama`, `no_hp`, `jam_berangkat`, `flag` FROM `surat_jalan` where id = ".$json['id'].")
                ", [$revised_date, $revised_notes]);
                
                $rev = number_format($d->rev, 0)+1;
                $model->db->query("update surat_jalan set 
                approved_by = null, 
                approved_date = null, 
                rejected_date = null,
                rejected_by = null, 
                approved = 0, 
                ask_approval_date = null,
                ask_approval_notes = '', 
                approved_notes = '',  
                rev = ".$rev.",
                revised_by =?,
                revised_date=?,
                revised_notes = ?
                where id = ".$json['id']."", [$revBy, $revised_date, $revised_notes]);
                $response = [
                    'status'   => true
                ];
    
                $msg = view('email/sj_revised_pengirim',[
                    'sj_no' => $d->sj_no,
                    'revised_by_name' => $revBy,
                    'revised_date'=>$revised_date,
                    'revised_notes'=>$revised_notes
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
                sendWa($d->created_phone, $msgWa);
            }
            else{
                $response = [
                    'status'   => false,
                    'data'	=> $q->approved<3 ? 'You are not allowed to use this feature!' : 'You are not allowed to use this feature!'
                ];
            }
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

	public function info(){
        $model = new SuratJalanModel();
        $json = $_REQUEST;
		$q = $model->query("select s.*, 
		a.name as approved_by_name
		from surat_jalan s
		left join users a on a.id = s.approved_by
		where s.id = ".$json["id"]);
		$data = $q->getResult()[0];
 		if($json['approved']==3)
 			$text = "Surat Jalan No: ".$data->sj_no."</br>Approved by: ".$data->approved_by_name."</br>Approved Date: ".$data->approved_date;
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
            $model = new SuratJalanModel();
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
                $q = $model->info($pk, $s["userid"]);
                if($data["approved"]==1){
                    $d = $q[1][0];
                    if(in_array("ask_approval_date", $model->allowedFields))
                        $data["ask_approval_date"] = date("Y-m-d H:i:s");
                    if(in_array("ask_approval_notes", $model->allowedFields))
                        $data["ask_approval_notes"] = $json->approval_notes;
                        
                    // $email->setFrom('internal@buanamultiteknik.com', 'Internal PT Buana Multi Teknik');
                    // $email->setTo($d->pengirim_email);
                    // $email->setTo("fenty@buanamultiteknik.com");
                    $msg = view('email/sj_asking_pengirim',[
                        'sj_no' => $d->sj_no,
                        'from' => $d->from_info,
                        'place'=> $d->place,
                        'pengirim_name'=>$d->pengirim_name,
                        'sj_notes'=>$d->sj_notes
                    ]);
                    // $email->setSubject("Permintaan Approval Surat Jalan");
                    // $email->setMessage($msg);
                    // $email->setMailType('html');
                    // $email->send();

                    // helper(['Wa_helper']);
                    // $msgWa = preg_replace([
                    //     '/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', //remove all style tags
                    //     '/<(?:br|p|tr)[^>]*>/i', //replace br p with ' '
                    //     '/<[^>]*>/',  //replace any tag with ''
                    //     '/\s+/u', //remove run on space - replace using the unicode flag
                    //     '/^\s+|\s+$/u', //trim - replace using the unicode flag
                    //     '/\~nl\~/i'
                    // ],[
                    //     '', "~nl~", '', ' ', '', "\r\n"
                    // ], $msg);
                    // sendWa('6285640013092', $msgWa);
                      
                }

			    if($data["approved"]==3){
				    $data["approved_by"] = $s["userid"];
				    $data["approved_date"] =  date("Y-m-d H:i:s");
				    $this->insertInfo("Approved by Pengirim", "sj_pengirim_".$pk, $pk, 3);
                    if($q[0]){
                        $d = $q[1][0];
                        if(in_array("approved_by", $model->allowedFields) && isset($s["userid"]))
    					    $data["approved_by"] = $s["userid"];
                         if(in_array("approved_date", $model->allowedFields))
                            $data["approved_date"] = date("Y-m-d H:i:s");
                        if(in_array("approved_notes", $model->allowedFields))
                            $data["approved_notes"] = $json->approved_notes;
                        // $email->setFrom('internal@buanamultiteknik.com', 'Internal PT Buana Multi Teknik');
                        // $email->setTo($d->created_email);
                        // $email->setTo("fenty@buanamultiteknik.com");
                        $msg = view('email/sj_approved_pengirim',[
                        'sj_no' => $d->sj_no,
                        'approved_by_name' => $d->approver_by_name,
                        'approved_date'=> $data["approved_date"] ,
                        'pengirim_name'=>$d->pengirim_name
                        ]);
                        // $email->setSubject("Surat Jalan Approved By Pengirim".$d->npb_no."");
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
                        //sendWa('6285640013092', $msgWa);
                    }
                }
                
			    if($data["approved"]==-1){
				    $data["rejected_by"] = $s["userid"];
				    $data["rejected_date"] =  date("Y-m-d H:i:s");
                    if($q[0]){
                        $d = $q[1][0];
                        if(in_array("rejected_by", $model->allowedFields) && isset($s["userid"]))
    					    $data["rejected_by"] = $s["userid"];
                         if(in_array("rejected_date", $model->allowedFields))
                            $data["rejected_date"] = date("Y-m-d H:i:s");
                        if(in_array("rejected_notes", $model->allowedFields))
                            $data["rejected_notes"] = $json->rejected_notes;
                        // $email->setFrom('internal@buanamultiteknik.com', 'Internal PT Buana Multi Teknik');
                        // $email->setTo($d->created_email);
                        // $email->setTo("fenty@buanamultiteknik.com");
                        $msg = view('email/sj_rejected_pengirim',[
                        'sj_no' => $d->sj_no,
                        'rejected_by_name' => $d->approver_by_name,
                        'rejected_date'=> $data["rejected_date"],
                        'rejected_notes'=>$data["rejected_notes"]
                        ]);
                        // $email->setSubject("Surat Jalan Approved By Pengirim".$d->npb_no."");
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
                        //sendWa('6285640013092', $msgWa);
                    }
			}
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
        $model = new SuratJalanModel();
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