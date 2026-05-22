<?php namespace App\Controllers\Bom;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ListTransferFinanceModel;
use App\Models\PurchaseOrderModel;
 
class List_transfer_finance extends ResourceController
{
    use ResponseTrait;
    // get all product
    public function index()
    {
		$session = session();
		$s = $session->get();
		if(!isset($s["userid"]))
			return $this->respond(['status' => false, 'message' => 'Unauthorized'], 401);
        try {
            $model = new ListTransferFinanceModel();
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
                $json["filter"] = json_decode($json["filter"], true);
                $json["filterType"] = json_decode($json["filterType"], true);
                $json["filterCondition"] = json_decode($json["filterCondition"], true);
                $json = (Object) $json;
                $data = $model->read($json);
                return $this->respond(['status' => true, 'data'=>$data[0], 'total' => $data[1]], 200);
            }
            //code...
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }
    
    public function excelbca(){
		$json = $_REQUEST;
        $json = (Object) $json;
        $model = new ListTransferFinanceModel();
        $transfer=$json->transfer_type;
        $q = "select ROW_NUMBER() OVER() AS `No`, i.invoice_no as `Transaction ID`, i.transfer_type as `Transfer Type`, i.debited_acc as `Debited Acc.`, '' as `Beneficiary ID`, ms.bank_account_no as `Credited Acc.`, 
        REPLACE(format(case when i.payment_pct_fiat != 0 then (i.payment_pct_fiat + i.invoice_charge) - invoice_reduction 
        when as_reference = 0 then ((i.grand_total_price * (i.payment_pct/100)) + i.invoice_charge) - invoice_reduction 
        else
            i.payment_amount + i.invoice_charge - i.invoice_reduction
        end, 2), ',', '')
        as `Amount`,
        DATE_FORMAT(p.eff_date, '%d %M %Y', 'id_ID') as `Eff. Date`, '' as `Transaction Purpose`, 
        case when i.reimburse_id is not null then ms.currency else coalesce(po.currency, ms.currency) end as `Currency`, 
        p.charges_type as `Charges Type`, i.debited_acc as `Charges Acc.`,   
		case
			when i.petty_cash = 1 then 'Petty Cash'
			when i.as_reference then 'As Reference'
			else substring(po.po_no, 1,18)
		end as `Remark 1`,  
		substring(i.kode_pembayaran, 1,18) as `Remark 2`, ms.bic_swift_code as `Receiver Bank Cd`, ms.bank as `Receiver Bank Name`, ms.bank_account_name as `Receiver Name`, ms.bank_account_type as `Receiver Cust. Type`, ms.bank_account_residence as `Receiver Cust. Residen`, i.transaction_code as `Transaction Cd`, CONCAT_WS(',', 'internal@buanamultiteknik.com', 'titik@buanamultiteknik.com', 'finance@buanamultiteknik.com') as `Beneficiary Email`, p.payment_no as payment_no from payment p
        left join payment_item pi on pi.payment_id = p.id
        left join invoice i on i.id = pi.invoice_id
        left join m_supplier ms on ms.id = COALESCE(i.reimburse_id, i.supplier_id)
        left join purchase_order po on po.id = i.po_id
            where p.id = $json->id and i.is_paid=0 and i.transfer_type='$transfer' and pi.flag=1 ;";
        $data = $model->db->query($q);
        
        if(!$data)
			return ["status"=>false, "data"=>$model->db->error()]; 
        $data = $data->getResult();
        if(empty($data))
            return "Data status paid atau transfer type tidak sesuai!";
        $name = $data[0]->payment_no;
        foreach($data as $val){
            unset($val->payment_no);
       }
		helper(['Excel_helper']);
		exportExcel($data, ".$name.xlsx");
    }

	public function complete(){
		$model = new ListTransferFinanceModel();
		$json = $_REQUEST;
		$json["filter"] = json_decode($json["filter"], true);
		$json["filterType"] = json_decode($json["filterType"], true);
		$json["filterCondition"] = json_decode($json["filterCondition"], true);
		$json = (Object) $json;
		$data = $model->complete($json);
		return $this->respond(['status' => $data[0], 'data' => $data[1], 'total' => $data[2]], 200);
	}

	public function transferstock(){
        $session = session();
		$model = new ListTransferFinanceModel();
		$json = $_REQUEST;
		$json = (Object) $json;
		$s = $session->get();
		$data = $model->transferstock($json, $s["userid"]);
		return $this->respond(['status' => $data[0], 'data' => $data[1]], 200);
	}

	public function exchange(){
		$json = $_REQUEST;
		$json = (Object) $json;
		$start =  date('Y-m-d', strtotime('-7 days'));
		$end =  date('Y-m-d');

		$data = file_get_contents('https://www.bi.go.id/biwebservice/wskursbi.asmx/getSubKursLokal3?mts='.$json->currency.'&startdate='.$start.'&enddate='.$end);
		/* $xml = new \SimpleXMLElement($data);
		print($xml->DataSet->diffgram->NewDataSet->Table); */
		preg_match_all("%\<Table((?s).*?)</Table>%", $data, $mathes);
		preg_match_all("%\<jual_subkurslokal((?s).*?)</jual_subkurslokal>%", $mathes[0][0], $mathes2);
		return $mathes2[0][0];
		/* $dom = new \domDocument;

		$dom->loadHTML($data);
		$dom->preserveWhiteSpace = false;
		$tables = $dom->getElementsByTagName('table');
		
		var_dump($tables);
		$rows = $tables->item(1)->getElementsByTagName('tr');
		//var_dump($rows);

		$tmp = [];

		foreach ($rows as $row) {
			$cols = $row->getElementsByTagName('td');
			$key = trim($cols->item(0)->nodeValue);
			$tmp[$key] = [];
			foreach ($cols as $col) {
				$tmp[$key][] = trim($col->nodeValue);
			}
		}
		return $tmp[$json->currency][2]; */
	}
 
    // create a product
    public function create()
    {
        $model = new ListTransferFinanceModel();
        $modelP = new PurchaseOrderModel();
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
		
        $model->insert($data);

        $id = $model->getInsertID();
        $model->where('id', $id)
        ->set(['payment_no' => "PAY".str_pad($id, 4, "0", STR_PAD_LEFT)])
        ->update();
        
        $response = [
            'status'   => true,
            'data'    => 'Data Saved'
        ];
        
        return $this->respondCreated($response, 200);
    }

	public function insertInfo($info, $uid, $url){
		$model = new ListTransferFinanceModel();
		$data = $model->db->query("insert into info (info, uid, url) values('".$info."', '".$uid."', '".$url."')");
	}

	public function info_validated(){
		$json = $_REQUEST;
		$model = new ListTransferFinanceModel();
		$d = $model->getOne($json["id"]);
		$response = [
            'status'   => true,
            'data'    => "<b>Validated Date:</b> ".$d[1][0]->approved1_date." <br/><b>Validated By:</b> ".$d[1][0]->approved1." <br/>"
        ];
		return $this->respondCreated($response, 200);
	}

	public function info_approved(){
		$json = $_REQUEST;
		$model = new ListTransferFinanceModel();
		$d = $model->getOne($json["id"]);
		$response = [
            'status'   => true,
            'data'    => "<b>Approved Date:</b> ".$d[1][0]->approved2_date." <br/><b>Approved By:</b> ".$d[1][0]->approved2." <br/>"
        ];
		return $this->respondCreated($response, 200);
	}

	public function revisi(){
        $model = new PurchaseOrderModel();
        $json = $_REQUEST;
		$q = $model->query("select s.*
		
		from payment s
		
		where s.id = ".$json['id']);
		$d = $q->getResult()[0];
		$session = session();
		$s = $session->get();
		if($d->approved<4 && isset($s["userid"])){
			$revBy = $s["userid"];
			$model->db->query("
			insert into payment_history (
				`id`, `payment_no`, `payment_date`, `signed_payment_doc_url`, `notes`, `flag`, `approved`, `approved1_date`, `approved2_date`, `approved1`, `approved2`, `done`, `rev`, `revision_by`
			)
			(SELECT `id`, `payment_no`, `payment_date`, `signed_payment_doc_url`, `notes`, `flag`, `approved`, `approved1_date`, `approved2_date`, `approved1`, `approved2`, `done`, `rev`, $revBy FROM `payment` where id = ".$json['id'].")
			");

			$model->db->query("
			insert into payment_item_history (
				`id`, `invoice_id`, `proof_url`, `payment_id`, `invoice_payment_notes`, `flag`
			)
			(SELECT `id`, `invoice_id`, `proof_url`, (select idx from payment_history where id = ".$json['id']." order by id desc limit 1) as `payment_id`, `invoice_payment_notes`, `flag` FROM `payment_item` where payment_id = ".$json['id'].")
			");
			$rev = number_format($d->rev, 0)+1;
			$payment_no = explode("-REV", $d->payment_no);
			$payment_no = $payment_no[0]."-REV.".sprintf('%02d', $rev);
			$model->db->query("update payment set 
			approved1_date = null, 
			approved2_date = null, 
			approved = 0, 
			done = 0, 
			approved1 = '', 
			approved2 = '', 
			rev = ".$rev.",
			payment_no = '".$payment_no."'
			where id = ".$json['id']."");
			$response = [
				'status'   => true
			];

			$msg = view('email/payment_revisi',[
				'payment_no' => $d->payment_no,
				'notes' => $d->notes,
				'msg'=> 'Revisi'
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
			sendWa('628113013485', $msgWa);
		}
		else{
			$response = [
				'status'   => false,
				'data'	=> $q->approved<4 ? 'Payment already approved!' : 'You are not allowed to use this feature!'
			];
		}
		return $this->respond($response);
	}
 
    // update product
    public function update($pk = null)
    {
        $model = new ListTransferFinanceModel();
        $json = $this->request->getJSON();
		
        if($json){            
            $data = [
            ];

            foreach($json as $key => $value) 
            {
                if($key!='pk')
                    $data[$key] = $value;
            }

            $session = session();
		    $s = $session->get();
		    if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			$data["modified_by"] = $s["userid"];
		    if(in_array("modified_date", $model->allowedFields))
			$data["modified_date"] = date("Y-m-d H:i:s");
        }

        $msg = false;
		if(isset($data["approved"])){
			if($data["approved"]==2){
			    $msg = 'Payment validated';
				$this->insertInfo("", "payment_validated_".$pk, "https://internal.buanamultiteknik.com/api/bom/payment/info_validated?id=".$pk);
				//$this->info("<b>Approved:</b> $approved <br/>Invoice: ".implode(', ', $invoice), "payment_approved".$uniqid);
			}
			if($data["approved"]==4){
			    $msg = 'Payment approved';
				$this->insertInfo("", "payment_approved_".$pk, "https://internal.buanamultiteknik.com/api/bom/payment/info_approved?id=".$pk);
			}
		}
		
		if($msg!=false){
		    $q = $model->query("select s.*
		
    		from payment s
    		
    		where s.id = ".$pk);
    		$d = $q->getResult()[0];
		    $msg = view('email/payment_revisi',[
				'payment_no' => $d->payment_no,
				'notes' => $d->notes,
				'msg'=> $msg
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
			sendWa('628113013485', $msgWa);
		}
        $model->update($pk, $data);
        $response = [
            'status'   => true
        ];
        return $this->respond($response);
    }
 
    public function paid()
    {
        try{
            $json=$this->request->getJSON();
            $model = new ListTransferFinanceModel();
            $data["eff_date"] = $json->eff_date;
            $data["charges_type"] = $json->charges_type;
		    $session = session();
		    $s = $session->get();
		
		    $model->update($json->id, $data);
		    $affected = $model->affectedRows();
		    if($model)
			    $response = [
				    'status'   => true
			    ];
		    else
			    $response = [
				    'status'   => false,
				    'data'    => $model->errors()
			    ];
        }
        catch (\Throwable $e) {
            $response = ['status' => false, 'data'=>$e->getMessage(), 'json'=>$json];
        }
        
        return $this->respond($response);
	}

    // delete product
    public function delete($pk = null)
    {
        $model = new ListTransferFinanceModel();
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
 
}