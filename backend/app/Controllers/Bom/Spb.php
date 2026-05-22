<?php namespace App\Controllers\Bom;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Bom\SpbModel;
 
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
 
    public function update($pk = null)
    {
        try{
            $model = new SpbModel();
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
    				$this->insertInfo("<b>Approved by Kepala Logistik:</b> ".date("Y-m-d H:i:s")."", "spb_bom_approved_".$pk, $pk, 1);
   
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
    				$this->insertInfo("<b>Approved by Kepala Logistik:</b> ".date("Y-m-d H:i:s")."", "spb_bom_approved2_".$pk, $pk, 2);
    			}
    			if($data["approved"]==4){
    				$data["approved2_by"] = $s["userid"];
    				$data["approved2_date"] =  date("Y-m-d H:i:s");
    				$this->insertInfo("<b>Approved by Kabag:</b> ".date("Y-m-d H:i:s")."", "spb_bom_approved2_".$pk, $pk, 3);

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
            $model->delete($pk);
			//$json = $_REQUEST;
			//$data = [];
			//$data["flag"] = $json["flag"] ?? 0;
			//$session = session();
			//$s = $session->get();
			//if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			//	$data["modified_by"] = $s["userid"];
			//if(in_array("modified_date", $model->allowedFields))
			//	$data["modified_date"] = date("Y-m-d H:i:s");
            //$model->update($pk, $data);
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