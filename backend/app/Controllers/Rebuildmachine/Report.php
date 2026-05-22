<?php namespace App\Controllers\Rebuildmachine;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Rebuildmachine\ReportModel;
use App\Models\Rebuildmachine\ReportItemGroupModel;

date_default_timezone_set("Asia/Jakarta");
 
class Report extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $uri = service('uri');
			$segment = [];
			$segment = $uri->getSegments();
            $model = new ReportModel();
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
                
                helper(['Query_helper']);
				$json = itemsOptions($json);
                $json = (Object) $json;
                if(isset($json->filterType["section_id"])){
                    $json->filterType["section_id"] = '%';
                }
                if(isset($json->filterType["type"])){
                    $json->filterType["type"] = '%';
                }
                if(isset($json->filterType["s_subassy"])){
                    $json->filterType["s_subassy"] = '%';
                }
                if(isset($json->filterType["partno_id"])){
                    $json->filterType["partno_id"] = '%';
                }
                if(isset($json->filterType["sub_register_id"])){
                    $json->filterType["sub_register_id"] = '=';
                }
                
                $data = $model->read($json, $segment);
				$ex = explode('.', end($segment));
                
				if(end($ex)=='xlsx'){ 
				    helper(['Excel_helper']);
					exportExcel($data[1], "Daily Report Rebuild Machine.xlsx");
				}
				else
                    return $this->respond(['status' => $data[0], 'data'=>$data[1]], 200);
            }
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }

 
    public function create()
    {
        try{

        $model = new ReportModel();
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
        if(in_array("created_date", $model->allowedFields))
			    $data["created_date"] = date("Y-m-d H:i:s");
                
           
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

   public function data($date){
        try {
            // $date=new \DateTime('yesterday');
			// $date=$date->format('Y-m-d');
			
			
            $session = session();
            $model = new ReportItemGroupModel();
            
            $data = $model->db->query("select   case when ri.type=1 then 'Replacement' when ri.type=2 then 'Repair' when ri.type=3 then 'Installation' when ri.type=4 then 'Pre-assembly' end as type_report , count(ri.type) as value, count(DISTINCT ri.subassy_id) as subassy from report_parent_rebuild s left join report_item_rebuild ri on ri.parent_id = s.id where s.date='$date' and ri.subassy_id is not null and ri.flag=1 and s.flag=1 group by type_report");
            if(!$data)
				return ["status"=>false, "data"=>$model->db->error()];
			$data = $data->getResult();
			
			if(count($data) == 0){
				$response = [
					'status'  => true,
					'data'    => $data
				];
			}else{
                $response = [
					'status'  => false,
					'data'    => $data
				];
            }
            return $response;
        } catch (Exception $e) {
            return ['status'   => false, 'data'    => $e];
        }
    }
    
    public function sendwa(){
        try{
            $date="2024-10-01";
            $data = $this->data($date);
            

           

            $tmp = [];
            $items=[];
//             $date=new \DateTime('yesterday');
// 			$date=$date->format('Y-m-d');

            
            if(count($data)==0)
            return "Ups No Data Found!";
            foreach($data['data'] as $key => $value) 
                {
                   $tmp[]=$value;
                }
                  
                  $msg = view('email/daily_report_rebuild',
                  ['items' => json_decode(json_encode($tmp), true),
				'dateactivity' => $date]
                  );
            helper(['Wa_helper']);
    		//send wa ke Group
    		$msgWa = preg_replace([
    		    '/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', //remove all style tags
    		    '/<(?:br|p|tr)[^>]*>/i', //replace br p with ' '
                '/<[^>]*>/',  //replace any tag with ''
                '/\s+/u', //remove run on space - replace using the unicode flag
                '/^\s+|\s+$/u', //trim - replace using the unicode flag
                '/\~nl\~/i'
    	        ],['', "~nl~", '', ' ', '', "\r\n"], $msg);
                // sendWa('120363210021737750', $msgWa); //grup test
    		//   sendWa('120363182221547703', $msgWa);
            // sendwa('120363210021737750@g.us', $msgWa); // grup test
            sendwa('120363182221547703@g.us', $msgWa); //grup rebuild
    		   $response = [
            'status'   => true,
            'data' => "Success"
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

    public function update($pk = null)
    {
        try{
            $model = new ReportModel();
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
        $model = new ReportModel();
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

    public function chart(){
        try{
            $model = new ReportModel();
            $data = $model->db->query("
            select sum(case when type_subassy='ve' then 1 else 0 end) as total_ve,
            sum(case when type_subassy='ve' and n2 is not null then 1 else 0 end) as reported_ve,
            sum(case when type_subassy='max' then 1 else 0 end) as total_max,
            sum(case when type_subassy='max' and n2 is not null then 1 else 0 end) as reported_max,
            sum(case when type_subassy='se' then 1 else 0 end) as total_se,
            sum(case when type_subassy='se' and n2 is not null then 1 else 0 end) as reported_se,
            sum(case when type_subassy='hcf' then 1 else 0 end) as total_hcf,
            sum(case when type_subassy='hcf' and n2 is not null then 1 else 0 end) as reported_hcf
            from (select row_number() over(partition by s.type_subassy, s.sub_assembly, s.part_number) as n, s.type_subassy, s.sub_assembly, s.part_number from subassembly s where s.flag=1)a
            left join (select row_number() over(partition by s.section_id, s.subassy_id, s.partno_id) as n2, s.section_id, s.subassy_id, s.part_number as partno_id from report_item_rebuild s where s.flag=1 and s.type!=2)b 
            on b.section_id = a.type_subassy and b.subassy_id = a.sub_assembly and b.partno_id = a.part_number and b.n2 = a.n;
            ");
            if(!$data)
				return ["status"=>false, "data"=>$model->db->error()];
			$data = $data->getResult();

            $response = [
                'status'  => true,
                'data'    => $data
            ];
            
            return $this->respond($response);
        } catch (Exception $e) {
            $response = ['status'   => false, 'data'    => $e];
            
            return $this->respond($response);
        }
    }

    public function chart2(){
        try{
            $model = new ReportModel();
            $data = $model->db->query("
			SELECT 
			sum(
			  CASE WHEN unreported = 0 THEN 1 ELSE 0 END
			) AS complete, 
			sum(
			  CASE WHEN unreported = 0 THEN 0 ELSE 1 END
			) AS incomplete, 
			type_subassy 
		  FROM 
			(
			  SELECT 
				sum(
				  CASE WHEN n2 IS NOT NULL THEN 1 ELSE 0 END
				) AS reported, 
				sum(CASE WHEN n2 IS NULL THEN 1 ELSE 0 END) AS unreported, 
				a.type_subassy, 
				a.sub_assembly 
			  FROM 
				(
				  SELECT 
					row_number() OVER (
					  PARTITION BY s.type_subassy, s.sub_assembly, 
					  s.part_number
					) AS n, 
					s.type_subassy, 
					s.sub_assembly, 
					s.part_number 
				  FROM 
					subassembly s where s.flag=1
				) a 
				LEFT JOIN (
				  SELECT 
					row_number() OVER (
					  PARTITION BY s.section_id, s.subassy_id, 
					  s.partno_id
					) AS n2, 
					s.section_id, 
					s.subassy_id, 
					s.partno_id
				  FROM 
					report_item_rebuild s where s.flag=1 and s.type!=2
				) b ON b.section_id = a.type_subassy 
				AND b.subassy_id = a.sub_assembly 
				AND SUBSTRING_INDEX(b.partno_id, ' - ', 1) = a.part_number 
				AND b.n2 = a.n 
			  GROUP BY 
				a.type_subassy, 
				a.sub_assembly
			) s 
		  GROUP BY 
			type_subassy;		  
            ");
            if(!$data)
				return ["status"=>false, "data"=>$model->db->error()];
			$data = $data->getResult();

            $response = [
                'status'  => true,
                'data'    => $data
            ];
            
            return $this->respond($response);
        } catch (Exception $e) {
            $response = ['status'   => false, 'data'    => $e];
            
            return $this->respond($response);
        }
    }

    public function chart3(){
        try{
            $model = new ReportModel();
            $data = $model->db->query("
				select *, (reported+unreported) as total from ( select sum(case when n2 is not null then 1 else 0 end) as reported, 
				sum(case when n2 is null then 1 else 0 end) as unreported, 
				a.type_subassy, a.sub_assembly 
				from (
					select row_number() over(partition by s.type_subassy, s.sub_assembly, s.part_number) as n, s.type_subassy, s.sub_assembly, s.part_number 
					from subassembly s where s.flag=1)a 
				left join (
					select row_number() over(partition by s.section_id, s.subassy_id, s.partno_id) as n2, s.section_id, s.subassy_id, s.part_number as partno_id 
					from report_item_rebuild s where s.flag=1 and s.type!=2
				)b on b.section_id = a.type_subassy and b.subassy_id = a.sub_assembly and b.partno_id = a.part_number and b.n2 = a.n 
				group by a.type_subassy, a.sub_assembly)s;
            ");
            if(!$data)
				return ["status"=>false, "data"=>$model->db->error()];
			$data = $data->getResult();

            $response = [
                'status'  => true,
                'data'    => $data
            ];
            
            return $this->respond($response);
        } catch (Exception $e) {
            $response = ['status'   => false, 'data'    => $e];
            
            return $this->respond($response);
        }
    }
 
}