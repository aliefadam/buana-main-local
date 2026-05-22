<?php namespace App\Controllers\Warehouse;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Warehouse\PengembalianModel;
 
class Pengembalian extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new PengembalianModel();
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
        $model = new PengembalianModel();
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
		$model = new PengembalianModel();
		$data = $model->db->query("insert into info (info, uid, url) values('".$info."', '".$uid."', 'https://internal.buanamultiteknik.com/api/transaction/pengembalian/info?approval=".$approval."&id=".$pk."')");
	}

	public function info(){
        $model = new PengembalianModel();
        $json = $_REQUEST;
		$q = $model->query("select s.*, 
		a.name as approved_by_name,
		b.name as approved_by2_name,
		c.name as approved_by3_name 
		
		from pengembalian s
		
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
        $model = new PengembalianModel();
        $json = $_REQUEST;
		$q = $model->query("select s.*,
		LPAD(s.id, 4, 0) as pengembalian_no
		
		from pengembalian s
		
		where s.id = ".$json['id']);
		$d = $q->getResult()[0];
		$session = session();
		$s = $session->get();
		if($d->approved<4 && isset($s["userid"])){
			$revBy = $s["userid"];
			$model->db->query("
			insert into pengembalian_history (
				`id`, `revision_by`, `po_id`, `approved_by`, `approved_date`, `approved2_by`, `approved2_date`, `approved3_by`, `approved3_date`, `rejected_date`, `rejected_by`, `created_date`, `created_by`, `modified_date`, `modified_by`, `flag`, `approved`, `peminta_id`, `notes`, `kabag_id`, `ask_approval_notes`, `approval_notes`, `approval2_notes`, `approval3_notes`, `photo`, `rev`
			)
			(SELECT `id`, $revBy, `po_id`, `approved_by`, `approved_date`, `approved2_by`, `approved2_date`, `approved3_by`, `approved3_date`, `rejected_date`, `rejected_by`, `created_date`, `created_by`, `modified_date`, `modified_by`, `flag`, `approved`, `peminta_id`, `notes`, `kabag_id`, `ask_approval_notes`, `approval_notes`, `approval2_notes`, `approval3_notes`, `photo`, `rev` FROM `pengembalian` where id = ".$json['id'].")
			");

			$model->db->query("
			insert into stock_history (
				`id`, `item_id`, `qty`, `allocation`, `po_id`, `created_date`, `created_by`, `modified_date`, `modified_by`, `purchase_order_item_id`, `flag`, `pengembalian_id`, `notes`, `photo`, `transfer_from`
			)
			(SELECT `id`, `item_id`, `qty`, `allocation`, `po_id`, `created_date`, `created_by`, `modified_date`, `modified_by`, `purchase_order_item_id`, `flag`, (select idx from pengembalian_history where id = ".$json['id']." order by id desc limit 1) as `pengembalian_id`, `notes`, `photo`, `transfer_from` FROM `stock` where pengembalian_id = ".$json['id'].")
			");
			$rev = number_format($d->rev, 0)+1;
			//$pengembalian_no = explode("-REV", $d->pr_no);
			//$pengembalian_no = $pengembalian_no[0]."-REV.".sprintf('%02d', $rev);
			/* $data["peminta_setuju"] = null;
				$data["penyetuju_setuju"] = null;
				$data["ask_approval"] = null;
				$data["askapproval_notes"] = null;
				$data["approval1_notes"] = null;
				$data["approval2_notes"] = null; */
			$model->db->query("update pengembalian set 
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

			$msg = view('email/pengembalian_revisi',[
				'pengembalian_no' => $d->pengembalian_no,
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
			sendWa('6285640013092', $msgWa);
		}
		else{
			$response = [
				'status'   => false,
				'data'	=> $q->approved<4 ? 'pengembalian already approved!' : 'You are not allowed to use this feature!'
			];
		}
		return $this->respond($response);
	}
 
    public function update($pk = null)
    {
        $model = new PengembalianModel();
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
			if($data["approved"]==2){
				$data["approved_by"] = $s["userid"];
				$data["approved_date"] =  date("Y-m-d H:i:s");
				$this->insertInfo("<b>Approved by Peminta:</b> ".date("Y-m-d H:i:s")."", "pengembalian_approved_".$pk, $pk, 1);
			}
			if($data["approved"]==3){
				$data["approved2_by"] = $s["userid"];
				$data["approved2_date"] =  date("Y-m-d H:i:s");
				$this->insertInfo("<b>Approved by Kepala Logistik:</b> ".date("Y-m-d H:i:s")."", "pengembalian_approved2_".$pk, $pk, 2);
			}
			if($data["approved"]==4){
				$data["approved3_by"] = $s["userid"];
				$data["approved3_date"] =  date("Y-m-d H:i:s");
				$this->insertInfo("<b>Approved by Kabag:</b> ".date("Y-m-d H:i:s")."", "pengembalian_approved3_".$pk, $pk, 3);
			}
			if($data["approved"]==-1){
				$data["rejected_by"] = $s["userid"];
				$data["rejected_date"] =  date("Y-m-d H:i:s");
			}

			$q = $model->query("select s.*, 
				a.name as approved_by_name,
				b.name as approved_by2_name,
				c.name as approved_by3_name,
				LPAD(s.id, 4, 0) as pengembalian_no
				
				from pengembalian s
				
				left join users a on a.id = s.approved_by
				left join users b on b.id = s.approved2_by
				left join users c on c.id = s.approved3_by
				
				where s.id = ".$pk);
				
			$d = $q->getResult()[0];
			
			/*$msg = view('email/pengembalian_approved',[
				'approval' => $data["approved"],
				'date' => date("Y-m-d H:i:s"),
				'peminta' => $d->approved_by_name,
				'kabag' => $d->approved_by3_name,
				'pengembalian_no' => $d->pengembalian_no
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
			if($data["approved"]==4)
				$model->query("update stock set flag = 1 where pengembalian_id = ".$pk);
		}
		$model->transComplete();
		if($model->transStatus() === false){
			$model->transRollback();
			$response = [
				'status'   => false,
				'data'    => $model->errors()
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
 
    public function delete($pk = null)
    {
        $model = new PengembalianModel();
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

	public function pengembalian(){
		$json = $_REQUEST;
		$model = new PengembalianModel();
		//$d = $model->getOne($json["id"]);
		$response = [
            'status'   => true,
            //'data'    => "<b>Approved Date:</b> ".$d[1][0]->approved2_date." <br/><b>Approved By:</b> ".$d[1][0]->approved2." <br/>"
        ];
		return $this->respondCreated($response, 200);
	}
 
}