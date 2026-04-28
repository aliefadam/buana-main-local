<?php namespace App\Controllers\Bom;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ListTransferItemFinanceModel;
 
class List_transfer_item_finance extends ResourceController
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
			$uri = service('uri');
			$segment = [];
			$segment = $uri->getSegments();
            $model = new ListTransferItemFinanceModel();
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
                $ex = explode('.', end($segment));
				if(end($ex)=='xlsx'){
				    helper(['Excel_helper']);
					exportExcel($data[0], "invoice.xlsx");
				}
				else
					return $this->respond(['status' => true, 'data'=>$data[0], 'total' => $data[1]], 200);
            }
            //code...
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }
 
    // create a product
    public function create()
    {
        $model = new ListTransferItemFinanceModel();
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
        //$data = json_decode(file_get_contents("php://input"));
        //$data = $this->request->getPost();
        $model->insert($data);
        $response = [
            'status'   => true,
            'data'    => 'Data Saved'
        ];
        
        return $this->respondCreated($response, 201);
    }
 
    // update product
    public function update($pk = null)
    {
        $model = new ListTransferItemFinanceModel();
        $json = $this->request->getJSON();
        if($json){
            $options = [
                'cost' => 11  
            ];

            
            $data = [
            ];

            foreach($json as $key => $value) 
            {
                if($key!='pk')
                    $data[$key] = $value;
            }
        }
        // Insert to Database
        $model->update($pk, $data);
        $response = [
            'status'   => true
        ];
        return $this->respond($response);
    }
 
    // delete product
    public function delete($pk = null)
    {
        $model = new ListTransferItemFinanceModel();
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

    public function excel(){
		$json = $_REQUEST;
        $json = (Object) $json;
        $model = new ListTransferItemFinanceModel();
        //$transfer=$json->transfer_type;
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
			when i.petty_cash = 1 then CONCAT('Petty Cash / ',substring(po.po_no, 1,18))
			when i.as_reference then 'As Reference'
			else substring(po.po_no, 1,18)
		end as `Remark 1`,  
		
		substring(i.kode_pembayaran, 1,18) as `Remark 2`,
		ms.bic_swift_code as `Receiver Bank Cd`, ms.bank as `Receiver Bank Name`, ms.bank_account_name as `Receiver Name`, ms.bank_account_type as `Receiver Cust. Type`, ms.bank_account_residence as `Receiver Cust. Residen`, i.transaction_code as `Transaction Cd`, CONCAT_WS(',', 'internal@buanamultiteknik.com', 'titik@buanamultiteknik.com', 'finance@buanamultiteknik.com') as `Beneficiary Email`, p.payment_no as payment_no from payment p
        left join payment_item pi on pi.payment_id = p.id
        left join invoice i on i.id = pi.invoice_id
        left join m_supplier ms on ms.id = COALESCE(i.reimburse_id, i.supplier_id)
        left join purchase_order po on po.id = i.po_id
            where pi.id in ($json->ids) and pi.flag=1 ;";
        $data = $model->db->query($q);
        if(!$data)
			return ["status"=>false, "data"=>$model->db->error()]; 
		$data = $data->getResult();
        $name = $data[0]->payment_no;
        foreach($data as $val){
            unset($val->payment_no);
       }
		helper(['Excel_helper']);
		exportExcel($data, ".$name.xlsx");
    }
 
}