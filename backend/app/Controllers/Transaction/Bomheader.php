<?php namespace App\Controllers\Transaction;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Transaction\BomheaderModel;
use App\Models\Transaction\SubkolipartModel;
 
class Bomheader extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new BomheaderModel();
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
        $model = new BomheaderModel();
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
        $model = new BomheaderModel();
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
        $model = new BomheaderModel();
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

	public function getitems(){
		$json = $_REQUEST;
		$model = new BomheaderModel();
		$q = $model->query("select b.id, b.item_id, i.item_name, b.qty, b.bom_header_id, 0 as source, b.machine_no, i.specification as description, b.name as application
			from bom_receive b 
			left join m_item i on i.id = b.id
			where i.id is not null and b.bom_header_id = ".$json['bom_header_id']."
			");
		$data = $q->getResult();
		$response = [
            'status'   => true,
            'data'    => $data
        ];
        
        return $this->respondCreated($response, 200);
	}

	public function getitemsall(){
		$json = $_REQUEST;
		$model = new BomheaderModel();
		$q = $model->query("select b.id, b.item_id, i.item_name, b.qty, b.bom_header_id, 0 as source, b.machine_no, i.specification as description, b.name as application
			from bom_receive b 
			left join m_item i on i.id = b.id
			where (select count(id) from bom_receive_item where bom_receive_id = b.id) = 0 and i.id is not null and b.bom_header_id = ".$json['bom_header_id']."
			union all
			select bi.id, i.id as item_id, i.item_name, bi.qty, b.bom_header_id, 1 as source, b.machine_no, bi.description, b.name as application
			from bom_receive_item bi
			left join m_item i on i.item_name = bi.part_number
			left join bom_receive b on b.id = bi.bom_receive_id
			where i.id is not null and b.bom_header_id = ".$json['bom_header_id']);
		$data = $q->getResult();
		$response = [
            'status'   => true,
            'data'    => $data
        ];
        
        return $this->respondCreated($response, 200);
	}

	public function getitemsreceive(){
		$json = $_REQUEST;
		$model = new BomheaderModel();
		$q = $model->query("select b.id, b.item_id, i.item_name, b.qty, b.bom_header_id, 0 as source, b.machine_no, i.specification as description, b.name as application
			from bom_receive b 
			left join m_item i on i.id = b.id
			where (select count(id) from bom_receive_item where bom_receive_id = b.id) = 0 and i.id is not null and b.id = ".$json['bom_receive_id']."
			union all
			select bi.id, i.id as item_id, i.item_name, bi.qty, b.bom_header_id, 1 as source, b.machine_no, bi.description, b.name as application
			from bom_receive_item bi
			left join m_item i on i.item_name = bi.part_number
			left join bom_receive b on b.id = bi.bom_receive_id
			where i.id is not null and bi.flag = 1 and bi.bom_receive_id = ".$json['bom_receive_id']);
		$data = $q->getResult();
		$response = [
            'status'   => true,
            'data'    => $data
        ];
        
        return $this->respondCreated($response, 200);
	}

	/* public function getitemsreceive(){
		$json = $_REQUEST;
		$model = new BomheaderModel();
		$q = $model->query("select b.id, b.item_id, i.item_name, b.qty, b.bom_header_id, 0 as source, b.machine_no, '-' as description, b.name as application
			from bom_receive_item bi
			left join bom_receive b on b.id = bi.bom_receive_id
			left join m_item i on i.id = b.item_id
			where i.id is not null and bi.bom_receive_id = ".$json['bom_receive_id']);
		$data = $q->getResult();
		$response = [
            'status'   => true,
            'data'    => $data
        ];
        
        return $this->respondCreated($response, 200);
	} */

	public function getitem(){
		$json = $_REQUEST;
		$model = new BomheaderModel();
		$q = $model->query("
			select bi.id, i.id as item_id, i.item_name, bi.qty, bi.dwg_no, b.bom_header_id, 1 as source, b.machine_no, bi.description, b.name as application
			from bom_receive_item bi
			left join m_item i on i.id = bi.item_id
			left join bom_receive b on b.id = bi.bom_receive_id
			where i.id is not null and bi.id = ".$json['bom_receive_item_id']);
		$data = $q->getResult();
		$response = [
            'status'   => true,
            'data'    => $data
        ];
        
        return $this->respondCreated($response, 200);
	}
	
	
		public function getItemsPack(){
		$json = $_REQUEST;
		$model = new BomheaderModel();
		$q = $model->query("select b.id, b.item_id, b.note, b.qty, b.bom_header_id, 0 as source, b.name as application
			from bom_receive b 
			where b.bom_header_id = ".$json['bom_header_id']."
			");
		$data = $q->getResult();
		$response = [
            'status'   => true,
            'data'    => $data
        ];
        
        return $this->respondCreated($response, 200);
	}

	public function addtopr(){
		$json = $_REQUEST;
		
        $session = session();
		$s = $session->get();
		$created_by = -1;
		if(isset($s["userid"]))
			$created_by = $s["userid"];
		$model = new BomheaderModel();
		$q = $model->query("
			insert into pr_part (pr_id, supplier_id, created_by, quotation_doc_url, item_id, notes, bom_header_id, order_no)
			select *, ROW_NUMBER() OVER(PARTITION BY s.pr_id ) + COALESCE ((select order_no from pr_part where pr_id = s.pr_id order by order_no desc limit 1), 0) as order_no
			 from (
				select distinct ".$json['pr_id']." as pr_id, 0 as supplier_id, -1 as created_by, '', b.item_id, bh.description, b.bom_header_id as bom_header_id
				from bom_receive b 
				left join m_item i on i.id = b.id
				left join bom_receive_header bh on bh.id = b.bom_header_id 
				where b.bom_header_id = ".$json['bom_header_id']."
			)s");
		$q = $model->query("
			 insert into pr_subledger (pr_part_id, project_id, allocation, qty, description, created_by)
			select p.id, bh.project_id, case when bh.project_id is null then 'NIS' else 'NI' end as allocation, b.qty, b.name as description, p.created_by, 
			concat(b.id, '.', b.item_id, '.', b.bom_header_id, '.0') as code
			 from bom_receive b
			left join pr_part p on p.bom_header_id = b.bom_header_id and p.item_id = b.item_id 
			left join pr_subledger ps on ps.pr_part_id = p.id
			left join bom_receive_header bh on bh.id = b.bom_header_id 
			where pr_id = ".$json['pr_id']." and ps.id is null");
		$response = [
            'status'   => $q ? true : false
        ];
        
        return $this->respondCreated($response, 200);
	}

	public function info(){
		$json = $_REQUEST;
		$model = new BomheaderModel();
		$barcode = explode('.', $json['barcode']);
		/* $q = $model->query("select f.*, bi.material, bi.mass, bi.unit_qty, f.received_qty as qty_actual, '-' as arrival_date
			from bom_item_final f 
			left join bom_receive_item bi on bi.id = f.id
			where f.code = '".$json['barcode']."'
			"); */
		 if($barcode[3]==1){
			/* $q = $model->query("
			select i.item_name, bi.description, bi.unit_qty, bi.qty, bi.material, bi.mass, '-' as arrival_date, coalesce((select location from receive_scan where code = '".$json['barcode']."'), r.location) as location, '-' as rack, bi.qty, r.qty as qty_actual, r.created_date,
			(select br.name from bom_receive br where br.id = bi.bom_receive_id) as application, (select br.machine_no from bom_receive br where br.id = bi.bom_receive_id) as machine_no 
			from bom_receive_item bi 
			left join m_item i on i.item_name = bi.part_number
			left join receive_scan r on bi.id = SUBSTRING_INDEX(r.code,'.', 1)
			where bi.id = ".$barcode[0]."
			"); */
			$q=$model->query("select f.*, bi.material, bi.mass, bi.unit_qty, b.note, b.name, f.received_qty as qty_actual, '-' as arrival_date, b.machine_no
			from bom_item_final f 
			left join bom_receive_item bi on bi.id = f.id
			left join bom_receive b on b.id = f.bom_receive_id
			where f.code = '".$json['barcode']."'
			");
		}
		else{
			$q = $model->query("
				select i.item_name, i.specification as description, '-' as material, '-' as mass, '-' as arrival_date, '-' as location, b.qty, b.note,
				'-' as rack, b.name as application, b.machine_no
				from bom_receive b
				left join m_item i on i.id = b.item_id
				where b.id = '".$barcode[0]."'
			");
		} 
		$data = $q->getResult();
		$response = [
            'status'   => true,
            'data'    => $data
        ];
        
        return $this->respondCreated($response, 200);
	}

	public function info2(){
		$json = $_REQUEST;
		$model = new BomheaderModel();
		$barcode = explode('.', $json['barcode']);
		/* $q = $model->query("select f.*, bi.material, bi.mass, bi.unit_qty, f.received_qty as qty_actual, '-' as arrival_date
			from bom_item_final f 
			left join bom_receive_item bi on bi.id = f.id
			where f.code = '".$json['barcode']."'
			"); */
		 if($barcode[2]==1){
			/* $q = $model->query("
			select i.item_name, bi.description, bi.unit_qty, bi.qty, bi.material, bi.mass, '-' as arrival_date, coalesce((select location from receive_scan where code = '".$json['barcode']."'), r.location) as location, '-' as rack, bi.qty, r.qty as qty_actual, r.created_date,
			(select br.name from bom_receive br where br.id = bi.bom_receive_id) as application, (select br.machine_no from bom_receive br where br.id = bi.bom_receive_id) as machine_no 
			from bom_receive_item bi 
			left join m_item i on i.item_name = bi.part_number
			left join receive_scan r on bi.id = SUBSTRING_INDEX(r.code,'.', 1)
			where bi.id = ".$barcode[0]."
			"); */
			$q=$model->query("select distinct i.item_name, b.note, r.location, i.specification as description, bi.material, bi.mass, bi.unit_qty, b.note, r.qty as qty_actual, '-' as arrival_date, bh.machine_name
				from bom_receive_item bi
				left join m_item i on i.item_name = bi.part_number
				left join bom_receive b on b.id = bi.bom_receive_id
				left join bom_receive_header bh on bh.id = b.bom_header_id
				left join receive_scan r on r.code = concat(b.bom_header_id, '.', i.id, '.1')
				where concat(b.bom_header_id, '.', i.id, '.1') = '".$json['barcode']."'
			");
		}
		else{
			$q = $model->query("
				select distinct i.item_name, i.specification as description, '-' as material, '-' as mass, '-' as arrival_date, '-' as location, b.qty, b.note,
				'-' as rack, bh.machine_name
				from bom_receive b
				left join bom_receive_header bh on bh.id = b.bom_header_id
				left join m_item i on i.id = b.item_id
				where concat(b.bom_header_id, '.', b.item_id, '.0') = '".$json['barcode']."'
			");
		} 
		$data = $q->getResult();
		$response = [
            'status'   => true,
            'data'    => $data
        ];
        
        return $this->respondCreated($response, 200);
	}
	
	public function savesubkolipart(){
		$json = $_REQUEST;
		try{
			/* if(!isset($json['item_id']))
				$json = json_decode(json_encode($this->request->getJSON(), true)); */
			if($json instanceof stdClass)
				$json = json_decode(json_encode($json), true);
			if(!isset($json['item_id']))
				$json = json_decode(json_encode($this->request->getJSON()), true);
			$model = new SubkolipartModel();
			//$json = $this->request->getJSON();
			$q = $model->query("
				select * from bom_receive_item bi where bi.part_number = (select item_name from m_item where id = '".$json['item_id']."') and bom_receive_id = '".$json['bom_receive_id']."';
			");
			$data = $q->getResult();
			if(count($data)==0){
				$response = [
					'status'   => false,
					'data'    => "Part number ini tidak ada di bom receive yang di pilih!"
				];
			}
			else{

				$created_by = -1;
				if(isset($s["userid"]))
					$created_by = $s["userid"];
				
				$q = $model->query("
					insert into subkoli_part (subkoli_id, bom_receive_item_id, created_by, photo) values(?, ?, ?, ?);
				", [$json['subkoli_id'], $data[0]['id'], $created_by, $json['photo']]);

				$response = [
					'status'   => true,
					'data'    => $data
				];
			}
			
			return $this->respondCreated($response, 200);
        }
        catch (\Throwable $e) {
            $response = ['status' => false, 'data'=>$e->getMessage(), 'json'=>$json];
			return $this->respondCreated($response, 200);
        }
	}
			
	public function getitemnew(){
		$json = $_REQUEST;
		$model = new BomheaderModel();
		$uniqueid = explode('.', $json['bom_unique_id']);
		$part_number = explode('.', $json['part_number']);
		$q = $model->query("select bi.id, i.id as item_id, i.item_name, bi.qty, b.bom_header_id, 1 as source, b.machine_no, bi.description, b.name as application, bi.bom_unique_id
			from bom_receive_item bi
			left join m_item i on i.item_name = bi.part_number
			left join bom_receive b on b.machine_no=".$uniqueid[0]."
			where i.id is not null and bi.part_number=  '".$json['part_number']."'
			order by  bi.id asc limit 1");
		$data = $q->getResult();
		$response = [
            'status'   => true,
            'data'    => $data
        ];
        
        return $this->respondCreated($response, 200);
	}
	
	public function getItemList(){
		$json = $_REQUEST;
		$model = new BomheaderModel();
		$q = $model->query("select f.id, br.bom_header_id, i.id as item_id, s.bom_unique_id, f.bom_receive_id, h.isPack, f.part_number, f.qty, f.description, coalesce(rs.qty, 0) as received_qty, br.machine_no  from (select bri.bom_unique_id, bri.part_number, bri.qty, bri.description  from bom_receive_item bri WHERE bri.bom_receive_id='".$json['bom_receive_id']."')s
left join bom_receive_item f on f.bom_unique_id=s.bom_unique_id
left join m_item i on i.item_name = f.part_number
left join bom_receive br on br.id=f.bom_receive_id
	left join receive_scan rs on rs.code = concat(f.id, '.', i.id, '.', br.bom_header_id, '.1')
left join bom_receive_header h on h.id=br.bom_header_id and h.isPack=0
WHERE h.isPack is not null
			");
		$data = $q->getResult();
		$response = [
            'status'   => true,
            'data'    => $data
        ];
        
        return $this->respondCreated($response, 200);
	}
 
}