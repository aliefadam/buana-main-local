<?php namespace App\Controllers\Project;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
//use CodeIgniter\Model\ProjectModel;
use App\Models\Project\ProjectModel;
 
class Project extends ResourceController
{
    use ResponseTrait;
    // get all product
    public function index()
    {
        
        $session = session();
		$s = $session->get();
        $authorization = false;
		if(!isset($s["userid"]))
            $authorization = false;
        else
            $authorization = true;

        if(isset(apache_request_headers()["Authorization"]))
            $authorization = apache_request_headers()["Authorization"];
        $host = apache_request_headers()["Host"];
        if($authorization!='19e4002f-f824-4dca-afaf-73fa6526ea33'){
            $authorization = false;
        }
        // if(!$authorization)
// 			return $this->respond(['status' => false, 'message' => 'Unauthorized'], 401);

        try {
            $model = new ProjectModel();
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
                $json["filter"] = json_decode(json_encode($json["filter"] ?? '{}', true), true);
                $json["filterType"] = json_decode(json_encode($json["filterType"] ?? '{}', true), true);
                $json["filterCondition"] = json_decode(json_encode($json["filterCondition"] ?? '{}', true), true);
                $json = (Object) $json;
                $data = $model->read($json);
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
        //try {
			$model = new ProjectModel();
			$json = $this->request->getJSON();
			$options = [
				'cost' => 11  
			];
			
			$data = array();

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

			//$noQuery = $model->lastNumber($y);
			// if(!$noQuery[0])
			// 	return $this->respondCreated(['status' => false, 'data'=>$noQuery[1]], 200);
			// $no = $noQuery[1][0]->no;
			// $data["project_no"] = "PRJ/BMT/".$no."/".$y;
			// $data["prj_year"] = $y;
			// $data["no"] = $noQuery[1][0]->number;
			
			$model->insert($data);
			$response = [
				'status'   => true,
				'data'    => 'Data Saved'
			];
			
            //return print_r($noQuery);
			return $this->respondCreated($response, 201);
			//code...
		// } catch (Exception $e) {
		// 	return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
		// }
    }
 
    // update product
    public function update($pk = null)
    {
        $model = new ProjectModel();
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
            $session = session();
		    $s = $session->get();
		    if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			$data["modified_by"] = $s["userid"];
		    if(in_array("modified_date", $model->allowedFields))
			$data["modified_date"] = date("Y-m-d H:i:s");
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
        $model = new ProjectModel();
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

    public function no(){
        $model = new ProjectModel();
        //select 18 as extra_fields_id, id as bind_id, project_no as value  from projects_no where id not in (select bind_id from extra_fields_list where extra_fields_id = 18);
        $json = $_REQUEST;
        $data = $model->db->query("select * from buanamultiteknik_project.projects_no where id in (".$json['id'].")");
        $value = $data->getResult(); 
        $response = [
            'status'   => true,
            'data' => $value
        ];
            
        return $this->respond($response);
    }
    
    public function projectBudget($project_id = null)
    {
     
        // Untuk debugging, tampilkan semua error
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        
        try {
            $db = \Config\Database::connect();
            
            $builder = $db->table('project_budgets pb');
            
            $builder->select('
                pb.*,
                b.name as budget_name,
                CAST(pbs.total_price AS DECIMAL(15,2)) as used_budget,
            ');
            
            $builder->join('budgets b', 'b.id = pb.budget_id', 'left');
            
            // PASTIKAN menggunakan alias 'pb', bukan 'project_budgets'
            $builder->join('project_budget_summary pbs', 
                "CAST(pbs.budget_id AS UNSIGNED) = CAST(pb.id AS UNSIGNED) 
                 AND CAST(pbs.project_id AS UNSIGNED) = CAST(pb.project_id AS UNSIGNED)", 
                'left'
            );
            
            if ($project_id) {
                $builder->where('pb.project_id', $project_id); // Gunakan alias 'pb'
            }
            
            // Optional: tambah order by
            $builder->orderBy('pb.budget_id', 'ASC');
            
            $query = $builder->get();
            
            // Debug: Tampilkan query SQL
            // echo $builder->getCompiledSelect(); die();
            
            $data = $query->getResult();
            
            return $this->respond([
                'status' => true,
                'data' => $data,
                'debug' => [
                    'sql' => $builder->getCompiledSelect(),
                    'num_rows' => $query->getNumRows()
                ]
            ]);
            
        } catch (\Exception $e) {
            // Log error
            log_message('error', 'Error in projectBudget: ' . $e->getMessage());
            
            return $this->respond([
                'status' => false,
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'db_error' => isset($db) ? $db->error() : null
            ], 500);
        }
    }
    
    public function projectBudgetDetail($id = null)
    {
    
         // Untuk debugging, tampilkan semua error
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        
        try {
            $db = \Config\Database::connect();
            
            $builder = $db->table('project_budgets pb');
            
            $builder->select('
                pb.*,
                b.name as budget_name,
                p.project_name as project_name,
                p.project_no as project_code,
                pbs.total_price as used_amount
            ');
            
            $builder->join('budgets b', 'b.id = pb.budget_id', 'left');
            $builder->join('m_project p', 'p.id = pb.project_id', 'left');
            $builder->join('project_budget_summary pbs', 'pb.id = pbs.budget_id', 'left');
            
            if ($id) {
                $builder->where('pb.id', $id); // Gunakan alias 'pb'
            }
            
            $query = $builder->get();
            
            // Debug: Tampilkan query SQL
            // echo $builder->getCompiledSelect(); die();
            
            $data = $query->getResult();
            
            return $this->respond([
                'status' => true,
                'data' => $data,
                'debug' => [
                    'sql' => $builder->getCompiledSelect(),
                    'num_rows' => $query->getNumRows()
                ]
            ]);
            
        } catch (\Exception $e) {
            // Log error
            log_message('error', 'Error in projectBudget: ' . $e->getMessage());
            
            return $this->respond([
                'status' => false,
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'db_error' => isset($db) ? $db->error() : null
            ], 500);
        }
    }
    

 
}