<?php namespace App\Controllers\Finance;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Finance\PettycashModel;
 
class Pettycash extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new PettycashModel();
			if($this->request->getMethod() == 'put'){
				$json = $this->request->getJSON();
				if(isset($json->pk_subkoli))
					return $this->updateKoliPhoto($json->pk_koli);
				if(isset($json->pk_koli))
					return $this->updateSubkoliPhoto($json->pk_subkoli);
				// else
	//             	return $this->update($json->pk);
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
		} catch (Throwable $e) {
			return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
		}
    }
 
    public function create()
    {
        try{
			$model = new PettycashModel();
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
        catch (\Throwable $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }
 
    public function update($pk = null)
    {
        $model = new PettycashModel();
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
 
    public function delete($pk = null)
    {
        $model = new PettycashModel();
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

	public function report(){
		try {
			$model = new PettycashModel();
			$json = $_REQUEST;
			$q = $model->query("
				select *, (select sum(debet) from petty_cash_detail where petty_cash_id = s.id and flag = 1) as total_debet, (select sum(kredit) from petty_cash_detail where petty_cash_id = s.id and flag = 1) as total_kredit from petty_cash s where id in (".$json["id"].")
			");
			$data = $q->getResult();

			

			foreach($data as $key => $value) 
            {
                $q = $model->query("
					select s.*, p.nama as nama_pos from petty_cash_detail s left join pos_akun p on p.kode = s.pos_akun_kode where petty_cash_id = ?
				", $value->id);
				$detail = $q->getResult();
				$value->detail = $detail;
            }

			$response = [
				'status'   => true,
				"data" => $data
			];
			return $this->respond($response);
        } catch (Throwable $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
	}

	public function reportperiodic(){
		try {
			$model = new PettycashModel();
			$json = $_REQUEST;
			$q = $model->query("
				select *, (select sum(debet) from petty_cash_detail where petty_cash_id = s.id and flag = 1) as total_debet, (select sum(kredit) from petty_cash_detail where petty_cash_id = s.id and flag = 1) as total_kredit from petty_cash s where s.header_date >= ? and s.header_date <= ?
			", [$json["date1"], $json["date2"]]);
			$data = $q->getResult();

			

			foreach($data as $key => $value) 
            {
                $q = $model->query("
					select s.*, p.nama as nama_pos from petty_cash_detail s left join pos_akun p on p.kode = s.pos_akun_kode where petty_cash_id = ?
				", $value->id);
				$detail = $q->getResult();
				$value->detail = $detail;
            }

			$response = [
				'status'   => true,
				"data" => $data
			];
			return $this->respond($response);
        } catch (Throwable $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
	}

	public function reportnew(){
		try {
			$model = new PettycashModel();
			$json = $_REQUEST;
			$q = $model->query("
				SELECT s.*,
				c.name as created_by_name
				FROM `petty_cash_detail` s
				left join buanamultiteknik_internal.users c on c.id = s.created_by 
				where s.pos_akun_kode = '-' and s.flag = 1 and s.date_voucher <= ? order by s.id desc limit 1
			", [$json["date1"]]);
			$data = $q->getResult();
			$q2 = $model->query("
				SELECT sum(debet) as debet, sum(kredit) as kredit, sum(ppn) as ppn, sum(pph23) as pph23, sum(other) as other
				FROM `petty_cash_detail` s
				where s.date_voucher >= ? and s.pos_akun_kode != '-' and s.pos_akun_kode != '111.400-00' and s.flag = 1 and s.date_voucher < ?
			", [$data[0]->date_voucher,$json["date1"]]);
			$data2 = $q2->getResult();

			$lastSaldo = (float) $data[0]->debet + ((float) $data2[0]->debet - ((float) $data2[0]->kredit + (float) $data2[0]->ppn + (float) $data2[0]->pph23 + (float) $data2[0]->other));
			
			$q3 = $model->query("
				SELECT s.*, k.nama as nama_pos,
				c.name as created_by_name
				FROM `petty_cash_detail` s
				left join pos_akun k on k.kode = s.pos_akun_kode
				left join buanamultiteknik_internal.users c on c.id = s.created_by where s.pos_akun_kode != '-' and s.pos_akun_kode != '111.400-00' and s.flag = 1 and s.date_voucher >= ? and s.date_voucher <= ? order by s.date_voucher
			", [$json["date1"], $json["date2"]]);
			$data3 = $q3->getResult();

			$data[0]->saldo = $lastSaldo;
			$data[0]->nama_pos = 'Saldo';
			$data[0]->description = 'Saldo';
			foreach($data3 as $key => $value) 
            {
				$value->net_amount = ((float) $value->debet - ((float) $value->kredit + (float) $value->ppn + (float) $value->pph23 + (float) $value->other));
				$lastSaldo = $lastSaldo + $value->net_amount;
				$value->saldo = $lastSaldo;
			}

			array_unshift( $data3, $data[0] );

			$response = [
				'status'   => true,
				"data" => $data,
				"data2" => $data2,
				"data3" => $data3,
				"lastSaldo" => $lastSaldo
			];
			return $this->respond($response);
        } catch (Throwable $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
	}
 
}