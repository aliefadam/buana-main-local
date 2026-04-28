<?php namespace App\Controllers;
use CodeIgniter\Controller;
use App\Models\HelperModel;
use CodeIgniter\API\ResponseTrait;
 
class Helper extends Controller
{
    use ResponseTrait;
    //$view = new \CodeIgniter\View\View();
    public function index(){
        $model = new HelperModel();
        $json = $_REQUEST;
        $data = $model->ddl($json["table"]);
        $tmp = array();
        $fields = [];
		$invi = ["modified_by", "modified_date", "created_by", "created_date"];
        foreach ($data as $key => $value) {
            $value = (array) $value;
            array_push($fields, $value["COLUMN_NAME"]);
            array_push($tmp, json_decode('{
                "text": "'.$value["COLUMN_NAME"].'",
                "value": "'.$value["COLUMN_NAME"].'",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "'.$value["DATA_TYPE"].'",
                "disabled": false,
                "visible": '.(($value["DATA_TYPE"] == 'int' && $value["COLUMN_KEY"] == 'PRI') ? 'false' : 'true').',
                "required": '.($value["IS_NULLABLE"] == 'no' ? 'true' : 'false').',
                "form": '.(in_array($value["COLUMN_NAME"], $invi) || $value["COLUMN_KEY"] == 'PRI' ? 'false' : 'true').',
                "filter": true,
                "groupable": false,
                "url": "",
                "search": [],
                "formatter": [],
                "options": {
                    "filter": {},
                    "filterType": {},
                    "filterCondition": {}
                },
                "paging": true,
                "page": "0",
                "limit": "10"
            }'));
        }
        if(isset($json["debug"]))
            return $this->respond(['status'   => true, "data" => $data]);
        else{
            return $this->respond(['status'   => true, "data" => $tmp, 'fields' => $fields, 'ddl' => $data]);
            echo join(",", $fields);
            echo '<br>';
            echo htmlspecialchars("[".join(",", $tmp)."];");
        }
    }
 
}