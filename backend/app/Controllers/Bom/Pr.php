<?php namespace App\Controllers\Bom;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Bom\PrModel;
use App\Models\PurchaseOrderModel;
class Pr extends ResourceController
{
    use ResponseTrait;
    public function index()
    {
        try {
            $model = new PrModel();
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
        $model = new PrModel();
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
		$noQuery = $model->lastNumber();
		if(!$noQuery[0])
			return $this->respondCreated(['status' => false, 'data'=>$noQuery[1]], 200);
		$no = $noQuery[1][0]->no;
		$data["pr_no"] = date("Ym").$no;
        $data["flag"]=1;
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
	public function insertInfo($info, $uid, $pk){
		$model = new PrModel();
		$data = $model->db->query("insert into info (info, uid, url) values('".$info."', '".$uid."', 'https://internaldev.buanamultiteknik.com/api/pr/info?id=".$pk."')");
	}
	public function upload_approval()
{
    $model = new PrModel();
    try {
        $session = session();
        $db = \Config\Database::connect();
        $s = $session->get();
        $user_id = $s["userid"]; // approved_by
        $new_status = 3; // approved
        $pr_id = $this->request->getPost('id');
        if (empty($pr_id)) {
            return $this->fail('ID tidak ditemukan', 400);
        }
        // ========================
        // HANDLE FILE
        // ========================
        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) {
            return $this->fail('File approval wajib diupload', 400);
        }
        // Generate nama file baru
        $newName = $file->getRandomName();
        // Simpan ke folder public/uploads/approval
        $file->move(FCPATH . 'uploads/approval', $newName);
        // ========================
        // RAW QUERY UPDATE
        // ========================
        $sql = "UPDATE pr 
                SET status = ?, 
                    approved_by = ?, 
                    file_proofment = ?
                WHERE id = ?";
        $db->query($sql, [
            $new_status,
            $user_id,
            $newName,
            $pr_id
        ]);
        return $this->respondCreated([
            'status' => true,
            'data'   => "PR Approved",
        ], 200);
    } catch (\Throwable $e) {
        return $this->fail([
            'status'  => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
    public function uploadprint()
    {
        $json = $_REQUEST;
        $model = new PrModel();
		$session = session();
		$s = $session->get();
		$doc = $this->request->getFile('file');
		if(!empty($doc)){
			try {
				if (!$doc->hasMoved()) {
					$name = $doc->getRandomName();
					$realName = $doc->getName();
					$filepath = './uploads/pr'.$json["id"].'/';
					$data["filepath"] = $name."+++".$realName;
					//$data['name'] = $realName;
					$doc->move($filepath, $name);
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
		$model->update($json["id"], $data);
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
	public function revisi(){
        $model = new PrModel();
        $json = $_REQUEST;
    //   return $this->respond($json);
	//   check if there is revision last 20 seconds
    // 	 $c = $model->query("SELECT FROM `pr_history` where id = ".$json['id']." and ABS(TIMESTAMPDIFF(SECOND, revision_date, now())) <= 20;");
        $c = $model->db->query("SELECT * FROM `pr_history` where id = ".$json['id']." and ABS(TIMESTAMPDIFF(SECOND, revision_date, now())) <= 20;");
		$d = $c->getResult();
		if(count($d)>0){
			$response = [
				'status'   => false,
				'data'	=> 'PR already revised!'
			];
			return $this->respond($response);
		}
		$q = $model->db->query("select s.*,
		c.name as created_by_name,
        c.no_hp as created_phone,
		m.name as modified_by_name,
		pm.name as peminta,
		pn.name as menyetujui,
		rv.name as revise_by_name
		from pr s
		left join users c on c.id = s.created_by
		left join users m on m.id = s.modified_by
		left join users pm on pm.id = s.pr_peminta
		left join users pn on pn.id = s.pr_menyetujui
		left join users rv on rv.id = s.revise_by
		where s.id = ".$json['id']);
// 		return $this->respond($q);
// 		return $q;
		$d = $q->getResult()[0];
		$session = session();
		$s = $session->get();
		if(isset($s["userid"])){
			$revBy = $s["userid"];
			$revisi_notes = $json['revisi_notes'];
			$model->db->query("
			insert into pr_history (
				`id`, `revision_by`, `pr_no`, `pr_date`, `pr_subject`, `pr_notes`, `pr_peminta`, `pr_menyetujui`, `created_date`, `created_by`, `modified_date`, `modified_by`, `peminta_setuju`, `penyetuju_setuju`, `ask_approval`, `askapproval_notes`, `approval1_notes`, `approval2_notes`, `reject_notes`, `revisi_notes`, `rev`
			)
			(SELECT `id`, ".$revBy.", `pr_no`, `pr_date`, `pr_subject`, `pr_notes`, `pr_peminta`, `pr_menyetujui`, `created_date`, `created_by`, `modified_date`, `modified_by`, `peminta_setuju`, `penyetuju_setuju`, `ask_approval`, `askapproval_notes`, `approval1_notes`, `approval2_notes`, `reject_notes`, ?, `rev` FROM `pr` where id = ".$json['id'].")
			", [$revisi_notes]);
			$model->db->query("
			insert into pr_part_history (
				`id`, `flag`, `item_id`, `supplier_id`, `quotation_no`, `quotation_date`, `notes`, `pr_id`, `created_date`, `created_by`, `modified_date`, `modified_by`, `rev`
			)
			(SELECT `id`, `flag`, `item_id`, `supplier_id`, `quotation_no`, `quotation_date`, `notes`, (select idx from pr_history where id = ".$json['id']." order by idx desc limit 1) as `pr_id`, `created_date`, `created_by`, `modified_date`, `modified_by`, `rev` FROM `pr_part` where pr_id = ".$json['id'].")
			");
			
			$model->db->query("
			insert into pr_subledger_history (
				`id`, `pr_part_id`, `description`, `qty`, `unit_price`, `allocation`, `project_id`, `created_date`, `created_by`, `modified_date`, `modified_by`, `project_type`, `dept_id`, `type_operational_id`, `sub_type_operational_id`
			)
			(
				SELECT `id`, (select idx from pr_part_history where id = s.pr_part_id and pr_id = (select idx from pr_history where id = ".$json['id']." order by idx desc limit 1)), 
				`description`, `qty`, `unit_price`, `allocation`, `project_id`, `created_date`, `created_by`, `modified_date`, `modified_by`, `project_type`, `dept_id`, `type_operational_id`, `sub_type_operational_id`
				FROM `pr_subledger` s where s.flag = 1 and pr_part_id in
				(select id from pr_part_history where pr_id = (select idx from pr_history where id = ".$json['id']." order by idx desc limit 1))
			)
			");


			$model->db->query("update pr_part set rev = 0 where pr_id = ".$json['id']);
			
			$rev = number_format($d->rev, 0)+1;
            $rev_date=date("Y-m-d H:i:s");
			$pr_no = explode("-REV", $d->pr_no);
			$tmp_no = $pr_no[0]."-REV.".sprintf('%02d', $rev);
            //return print_r([$rev, $pr_no, $tmp_no]);
			$msg = "";
			/* $data["peminta_setuju"] = null;
				$data["penyetuju_setuju"] = null;
				$data["ask_approval"] = null;
				$data["askapproval_notes"] = null;
				$data["approval1_notes"] = null;
				$data["approval2_notes"] = null; */
			$res = $model->db->query("update pr set 
            status = 0,
			reject_notes = null, 
			approval1_notes = null, 
			approval2_notes = null, 
			askapproval_notes = null, 
			peminta_setuju = null, 
			penyetuju_setuju = null, 
			ask_approval = null,
            rev_date= ?,
			revisi_notes = ?,
			rev = ".$rev.", pr_no = '".$tmp_no."' where id = ".$json['id']."",  [$rev_date, $json['revisi_notes']]);
			if(!$res)
				$msg = $model->db->error();
			$response = [
				'status'   => true,
				'data' => "
			insert into pr_history (
				`id`, `revision_by`, `pr_no`, `pr_date`, `pr_subject`, `pr_notes`, `pr_peminta`, `pr_menyetujui`, `created_date`, `created_by`, `modified_date`, `modified_by`, `peminta_setuju`, `penyetuju_setuju`, `ask_approval`, `askapproval_notes`, `approval1_notes`, `approval2_notes`, `reject_notes`, `revisi_notes`, `rev`
			)
			(SELECT `id`, ".$revBy.", `pr_no`, `pr_date`, `pr_subject`, `pr_notes`, `pr_peminta`, `pr_menyetujui`, `created_date`, `created_by`, `modified_date`, `modified_by`, `peminta_setuju`, `penyetuju_setuju`, `ask_approval`, `askapproval_notes`, `approval1_notes`, `approval2_notes`, `reject_notes`, ".$revisi_notes.", `rev` FROM `pr` where id = ".$json['id'].")
			"
			];
			$msg = view('email/pr_revisi',[
				'pr_no' => $d->pr_no,
				'pr_subject' => $d->pr_subject,
				'title' => "Revise",
				'pr_notes' => $json['revisi_notes'],
				'revise_notes' => $json['revisi_notes'],
                'revise_name' => $d->revise_by_name
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
				'data'	=> $q->approved!=3 ? 'PR not yet approved!' : 'You are not allowed to use this feature!'
			];
		}
		return $this->respond($response);
	}
	public function info(){
        $model = new PrModel();
        $json = $_REQUEST;
		$text = "";
		$q = $model->query("select s.*, 
		c.name as created_by_name,
		m.name as modified_by_name,
		pm.name as peminta,
		pn.name as menyetujui
		from pr s
		left join users c on c.id = s.created_by
		left join users m on m.id = s.modified_by
		left join users pm on pm.id = s.pr_peminta
		left join users pn on pn.id = s.pr_menyetujui
		where s.id = ".$json['id']);
		$data = $q->getResult()[0];
		$id = explode('_', $json['uid']);
		if($id[1]=="peminta")
			$text = "PR No: ".$data->pr_no."</br>Subject: ".$data->pr_subject."</br>Approved By Requestor: ".$data->peminta."</br>Approved Date: ".$data->peminta_setuju;
		if($id[1]=="penyetuju" && $data->isAn==1)
			$text = "PR NO: ".$data->pr_no."</br>Subject: ".$data->pr_subject."</br>Validated By Approver: ".$data->peminta."</br>Approved date ".$data->penyetuju_setuju;
        else if($id[1]=="penyetuju" && $data->isAn==0)
			$text = "PR NO: ".$data->pr_no."</br>Subject: ".$data->pr_subject."</br>Validated By Approver: ".$data->menyetujui."</br>Approved date ".$data->penyetuju_setuju;
		/* if($json['approval']==3)
			$text = "Approved by ".$data->approved_by3_name."</br>Approved date ".$data->approved3_date; */
		/* $q = $model->info($json['id']);
		$text = $q[1];
		if($q[0]){
			$d = $q[1][0];
			$text = "
			<table class='tpl'>
			<tr><td><div style='min-width: 100px;'><b>PO No: </b></div></td><td style='width: 100%;'>".$d->po_no."</td></tr>
			<tr><td><div style='min-width: 100px;'><b>PO Date: </b></div></td><td>".$d->po_date."</td></tr>
			<tr><td><div style='min-width: 100px;'><b>PO Title: </b></div></td><td>".$d->title."</td></tr>
			<tr><td><div style='min-width: 100px;'><b>Supplier: </b></div></td><td>".$d->supplier."</td></tr>
			<tr><td><div style='min-width: 100px;'><b>Ask for approval date: </b></div></td><td>".$d->approval_date."</td></tr>
			<tr><td><div style='min-width: 100px;'><b>Approved date: </b></div></td><td>".$d->approval_2_date."</td></tr>
			<tr><td><div style='min-width: 100px;'><b>Approved by: </b></div></td><td>".$d->approved2_by_name."</td></tr>
			";
		} */
        $response = [
            'status'   => true,//$q[0],
            'data'    => $text,
			'title'   => "Purchase Requisition Information"
        ];
        return $this->respondCreated($response, 200);
	}
	function readable_random_string($length = 6)
	{  
		$string = '';
		$vowels = array("a","e","i","o","u");  
		$consonants = array(
			'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 
			'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'
		);  
		$max = $length / 2;
		for ($i = 1; $i <= $max; $i++)
		{
			$string .= $consonants[rand(0,19)];
			$string .= $vowels[rand(0,4)];
		}
		return $string;
	}
    public function update($pk = null)
    {
        $model = new PrModel();
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
		//get info
		$q = $model->query("select s.*, 
		c.name as created_by_name,
        c.no_hp as created_phone,
		m.name as modified_by_name,
		pm.name as peminta,
		pn.name as menyetujui,
        db.name as delete_by_name
		from pr s
		left join users c on c.id = s.created_by
		left join users m on m.id = s.modified_by
		left join users pm on pm.id = s.pr_peminta
		left join users pn on pn.id = s.pr_menyetujui
        left join users db on db.id = s.delete_by
		where s.id = ".$pk);
		$d = $q->getResult()[0];
		//end of get info
		$msg = false;
		$notes = false;
		if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			$data["modified_by"] = $s["userid"];
		if(in_array("modified_date", $model->allowedFields))
			$data["modified_date"] = date("Y-m-d H:i:s");
		if(isset($json->approved_notes)){
			if(!isset($s["userid"])){
				return $this->respond([
					'status' => false,
					'data' => 'You are not allowed to use this feature!'
				], 200);
			}
			$res = $model->db->query("
				UPDATE pr
				SET
					status = 3,
					ask_approval = now(),
					peminta_setuju = now(),
					penyetuju_setuju = now(),
					approved_by = ?,
					approved_notes = ?,
					modified_by = ?,
					modified_date = now()
				WHERE id = ?
			", [$s["userid"], $json->approved_notes, $s["userid"], $pk]);
			if(!$res){
				return $this->respond([
					'status' => false,
					'data' => $model->db->error()
				], 200);
			}
			$model->query("insert into pr_notes (pr_id, notes, type, created_by) values(?, ?, ?, ?)", [$pk, $json->approved_notes, "Approved Notes", $s["userid"]]);
			$this->insertInfo("<b>Approved:</b> ".date("Y-m-d H:i:s")."", "pr_approved_".$pk, $pk);
			return $this->respond([
				'status' => true
			], 200);
		}
		if(isset($json->approved)){
			if($json->approved==1 && $json->isAn==1){
				$msg = "Approved by Approval 1";
				$notes = $data["approval1_notes"];
				$data["peminta_setuju"] = date("Y-m-d H:i:s");
				$this->insertInfo("<b>Approved by Peminta:</b> ".date("Y-m-d H:i:s")."", "pr_peminta_".$pk, $pk);
				if($d->menyetujui == "Buana Susilo" ){
                    $msg="Approved by Approval 2";
                    $data["status"]=3;
				    $data["penyetuju_setuju"] = date("Y-m-d H:i:s");
				    $this->insertInfo("<b>Approved by Penyetuju:</b> ".date("Y-m-d H:i:s")."", "pr_penyetuju_".$pk, $pk);
				}
                $msg = view('email/pr_approved',[
                    'pr_no' => $d->pr_no,
                    'pr_subject' => $d->pr_subject,
                    'title' => $msg,
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
            if($json->approved==1 && $json->isAn==0){
				$msg = "Approved by Approval 1";
				$notes = $data["approval1_notes"];
				$data["peminta_setuju"] = date("Y-m-d H:i:s");
				$this->insertInfo("<b>Approved by Peminta:</b> ".date("Y-m-d H:i:s")."", "pr_peminta_".$pk, $pk);
				// if($d->menyetujui == "Buana Susilo"){
                //     $msg="Approved by Approval 2";
                //     $data["status"]=3;
				//     $data["penyetuju_setuju"] = date("Y-m-d H:i:s");
				//     $this->insertInfo("<b>Approved by Penyetuju:</b> ".date("Y-m-d H:i:s")."", "pr_penyetuju_".$pk, $pk);
				// }
                $msg = view('email/pr_approved',[
                    'pr_no' => $d->pr_no,
                    'pr_subject' => $d->pr_subject,
                    'title' => $msg,
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
			if($json->approved==2){
				$msg = "Approved by Approval 2";
				$notes = $data["approval2_notes"];
				$data["penyetuju_setuju"] = date("Y-m-d H:i:s");
				$this->insertInfo("<b>Approved by Penyetuju:</b> ".date("Y-m-d H:i:s")."", "pr_penyetuju_".$pk, $pk);
                // $q = $model->query("select s.*
                // from pr s
                // where s.id = ".$pk);
                // $d = $q->getResult()[0];
                $msg = view('email/pr_approved',[
                    'pr_no' => $d->pr_no,
                    'pr_subject' => $d->pr_subject,
                    'title' => $msg,
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
            if($json->approved==-2 && $json->isAn==1){
				$msg = "Cancelation Approved by Approval 1";
				$notes = $data["approval-3_notes"];
				$data["canceled_date"] = date("Y-m-d H:i:s");
				$this->insertInfo("<b>Cancelation Approved by Requestor:</b> ".date("Y-m-d H:i:s")."", "pr_peminta_".$pk, $pk);
				if($d->menyetujui == "Buana Susilo" ){
                    $msg="Cancelation Approved by Approval 2";
                    $data["status"]=-4;
				    $data["penyetuju_setuju"] = date("Y-m-d H:i:s");
				    $this->insertInfo("<b>Cancelation Approved by Validator:</b> ".date("Y-m-d H:i:s")."", "pr_penyetuju_".$pk, $pk);
				}
                $msg = view('email/pr_cancel',[
                    'pr_no' => $d->pr_no,
                    'pr_subject' => $d->pr_subject,
                    'title' => $msg,
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
            if($json->approved==-2 && $json->isAn==0){
				$msg = "Cancelation Approved by Approval 1";
				$notes = $data["approval-3_notes"];
				$data["canceled_date"] = date("Y-m-d H:i:s");
				$this->insertInfo("<b>Cancelation Approved by Requestor:</b> ".date("Y-m-d H:i:s")."", "pr_peminta_".$pk, $pk);
				// if($d->menyetujui == "Buana Susilo" ){
                //     $msg="Cancelation Approved by Approval 2";
                //     $data["status"]=-4;
				//     $data["penyetuju_setuju"] = date("Y-m-d H:i:s");
				//     $this->insertInfo("<b>Cancelation Approved by Validator:</b> ".date("Y-m-d H:i:s")."", "pr_penyetuju_".$pk, $pk);
				// }
                $msg = view('email/pr_cancel',[
                    'pr_no' => $d->pr_no,
                    'pr_subject' => $d->pr_subject,
                    'title' => $msg,
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
            if($json->approved==-3){
                $msg = "Cancelation Approved by Approval 2";
				$data["canceled_2_date"] = date("Y-m-d H:i:s");
				$this->insertInfo("<b>Cancelation Approved by Validator:</b> ".date("Y-m-d H:i:s")."", "pr_penyetuju_".$pk, $pk);
                $msg = view('email/pr_approved',[
                    'pr_no' => $d->pr_no,
                    'pr_subject' => $d->pr_subject,
                    'title' => $msg,
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
		}
		if(isset($json->askapproval)){
			if($json->askapproval==1)
				$data["ask_approval"] = date("Y-m-d H:i:s");
		}
		if(isset($json->askcancel)){
		    if($json->askcancel==1)
		    $pr_no = explode("-CANCELED", $d->pr_no);
			$pr_no = $pr_no[0]."-CANCELED";
		        $data["ask_canceled_date"] = date("Y-m-d H:i:s");
                $data['canceled_by']=$s["userid"];
		        $data["pr_no"] = $pr_no;
		}
		if(isset($json->reviser)){
			if($json->reviser==1){
				$data['revise_date']=date("Y-m-d H:i:s");
				$data['revise_by']=$s["userid"];
				$msg="";
				$msg = view('email/pr_revisi',[
					'pr_no' => $d->pr_no,
					'pr_subject' => $d->pr_subject,
					'title' => "Revise",
					'revise_notes' => $data['revise_notes'],
					'revise_name' => $s["userid"],
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
		}
		if(isset($json->reject)){
			if($json->reject==1){
				$msg = "Rejected";
				$notes = $data["reject_notes"];
				// $data["peminta_setuju"] = null;
				$data["rejected_by"] = $s["userid"];
				$data["rejected_date"] = date("Y-m-d H:i:s");
                $this->insertInfo("<b>Rejected by Approver:</b> ".date("Y-m-d H:i:s")."", "pr_rejector_".$pk, $pk);
				// $data["penyetuju_setuju"] = null;
				// $data["ask_approval"] = null;
				// $data["askapproval_notes"] = null;
				// $data["approval1_notes"] = null;
				// $data["approval2_notes"] = null;
                $msg = view('email/pr_rejected',[
                    'pr_no' => $d->pr_no,
                    'pr_subject' => $d->pr_subject,
                    'title' => $msg,
                    'rejected_name' => $data["rejected_by"],
                    'rejected_notes' => $notes
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
		}
        if(isset($json->flag)){
			if($json->flag==0){
                $msg = "Deleted";
                $data["delete_by"] = $s["userid"];
				$data["delete_date"] = date("Y-m-d H:i:s");
            }
		}
		// if($msg != false){
		// 	$q = $model->query("select s.*
		// 	from pr s
		// 	where s.id = ".$pk);
		// 	$d = $q->getResult()[0];
		// 	$msg = view('email/pr_revisi',[
		// 		'pr_no' => $d->pr_no,
		// 		'pr_subject' => $d->pr_subject,
		// 		'title' => $msg,
		// 		'pr_notes' => $notes
		// 	]);
		// 	helper(['Wa_helper']);
		// 	$msgWa = preg_replace([
		// 		'/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', //remove all style tags
		// 		'/<(?:br|p|tr)[^>]*>/i', //replace br p with ' '
		// 		'/<[^>]*>/',  //replace any tag with ''
		// 		'/\s+/u', //remove run on space - replace using the unicode flag
		// 		'/^\s+|\s+$/u', //trim - replace using the unicode flag
		// 		'/\~nl\~/i'
		// 	],[
		// 		'', "~nl~", '', ' ', '', "\r\n"
		// 	], $msg);
		// 	sendWa('6285640013092', $msgWa);
		// }
		//notes history
		if(isset($json->reject_notes)){
			$model->query("insert into pr_notes (pr_id, notes, type, created_by) values(?, ?, ?, ?)", [$pk, $json->reject_notes, "Rejected Notes", $s["userid"]]);
		}
		if(isset($json->askapproval_notes)){
			$model->query("insert into pr_notes (pr_id, notes, type, created_by) values(?, ?, ?, ?)", [$pk, $json->askapproval_notes, "Asking for Approval Notes", $s["userid"]]);
		}
		if(isset($json->approval1_notes)){
			$model->query("insert into pr_notes (pr_id, notes, type, created_by) values(?, ?, ?, ?)", [$pk, $json->approval1_notes, "Approval 1 Notes", $s["userid"]]);
		}
		if(isset($json->approval2_notes)){
			$model->query("insert into pr_notes (pr_id, notes, type, created_by) values(?, ?, ?, ?)", [$pk, $json->approval2_notes, "Approval 2 Notes", $s["userid"]]);
		}
		if(isset($json->revisi_notes)){
			$model->query("insert into pr_notes (pr_id, notes, type, created_by) values(?, ?, ?, ?)", [$pk, $json->revisi_notes, "Revised Notes", $s["userid"]]);
		}
		if(isset($json->revise_notes)){
			$model->query("insert into pr_notes (pr_id, notes, type, created_by) values(?, ?, ?, ?)", [$pk, $json->revise_notes, "Revised Notes From Approver", $s["userid"]]);
		}
        if(isset($json->delete_notes)){
			$model->query("insert into pr_notes (pr_id, notes, type, created_by) values(?, ?, ?, ?)", [$pk, $json->delete_notes, "Cancel Notes", $s["userid"]]);
		}
		if(isset($json->ask_canceled_note)){
			$model->query("insert into pr_notes (pr_id, notes, type, created_by) values(?, ?, ?, ?)", [$pk, $json->ask_canceled_note, "Asking for Cancelation Notes", $s["userid"]]);
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
    public function deletey($pk = null)
    {
        $model = new PrModel();
        $data = $model->find($pk);
        if($data){
            //$model->delete($pk);
			//$data = [];
			//$data["flag"] = 0;
			//$session = session();
			//$s = $session->get();
			//if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			//	$data["modified_by"] = $s["userid"];
			//if(in_array("modified_date", $model->allowedFields))
			//	$data["modified_date"] = date('Y-m-d H:i:s', now());
            $model->update($pk, ["flag"=>0]);
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
	public function total_pr(){
        $model = new PrModel();
		$res = $model->db->query("
			select (select count(pr.pr_no)  from pr where pr.status=0 and pr.flag=1) as pr_new, (select count(pr.pr_no)  from pr where (pr.status=1 or pr.status=2 or pr.status=-2 or pr.status=-3) and pr.flag=1) as pr_pending, (select count(pr.pr_no)  from pr where pr.status=3 and pr.flag=1) as pr_approved, (select count(pr.pr_no) from pr where pr.status=-1 and pr.flag=1) as pr_rejected,  (select count(pr.pr_no)  from pr where pr.status=-4 and pr.flag=1) as pr_canceled,  (select count(pr.pr_no)  from pr where pr.flag=0) as pr_deleted;
			");
		$result = $res->getResult();
		return $this->respond(['status' => true, 'data' => $result], 200);
	}
}
