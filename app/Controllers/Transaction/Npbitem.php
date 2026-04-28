<?php namespace App\Controllers\Transaction;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Transaction\NpbitemModel;
 
class Npbitem extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new NpbitemModel();
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
                $json["filterType"] = json_decode($json["fireportlterType"] ?? '{}', true);
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
        $model = new NpbitemModel();
        $json = $this->request->getJSON();
        try{
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
				'data'    => $model->errors(),
                't'=>$data
			];
        }
        catch(\Throwable $e){
            $response = [
                'status' => false,
                'data'=>$e->getMessage()
            ];
        }
        
        return $this->respondCreated($response, 200);
    }

	public function create_from_bom(){
		$json = $_REQUEST;
        try {
			$model = new NpbitemModel();
			//$json = $this->request->getJSON();
			$data = $model->db->query("
				insert into npb_item(npb_id, item_id, qty, po_id, allocation, notes)
				select * from (
					select ".$json["npb_id"]." as npb_id, s.item_id, received_qty-coalesce((
						select sum(qty) as q from npb n
						left join npb_item ni on ni.npb_id = n.id and po_id = s.po_id and flag = 1
						where ni.item_id = s.item_id and n.flag = 1
					), 0) as qty, 
					s.po_id, 'NI' as allocation, '' as notes from (
						SELECT s.bom_receive_id, s.part_number, s.unit_qty, s.description, s.material, s.mass, s.id, i.id as item_id, i.item_no,
						sum(s.qty) as qty, sum(coalesce(t.qty, 0)) as received_qty, br.po_id
						FROM bom_receive_item s
						left join bom_receive br on br.id = s.bom_receive_id
						left join m_item i on i.item_name = s.part_number and i.bom_receive_id=s.bom_receive_id
						left join spb b on b.bom_receive_id = s.bom_receive_id
						left join stock t on t.spb_id = b.id and t.item_id = i.id and t.flag > 0
						where br.id is not null and s.bom_receive_id = ".$json["bom_id"]."
						group by s.bom_receive_id, br.po_id, s.part_number, i.id
					)s
				)ss where ss.qty > 0;
			");
			
			$response = [
				    'status'   => true
				    ];
        return $this->respond($response);
		} catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
	}
 
    public function update($pk = null)
    {
        $model = new NpbitemModel();
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
        $model = new NpbitemModel();
        $data = $model->find($pk);
        if($data){
            $model->delete($pk);
			//$data = [];
			//$data["flag"] = 0;
			//$session = session();
			//$s = $session->get();
			//if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			//	$data["modified_by"] = $s["userid"];
			//if(in_array("modified_date", $model->allowedFields))
			//	$data["modified_date"] = date('Y-m-d H:i:s', now());
            //$model->update($pk, ["flag"=>0]);
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