<?php namespace App\Models\Document;
 
use CodeIgniter\Model;
 
class FieldsModel extends Model
{
    protected $table = 'document_fields';
    protected $returnType     = 'object';
    protected $primaryKey = 'document_field_id';
    protected $allowedFields = ["document_field_id","datatype","document_id","name","listurl","listurlsearch","listurltext",
	"listurlvalue","created_date","created_by","modified_date","modified_by","flag","sort","width_xs","width_sm","width_m",
	"width_l","width_xl","list","listdocument","is_timeline","required","format_as","style","formula","onload","oninput",
	"document_alias_id","visible","filter","custom","formdata"];
	
	protected function initialize()
    {
        $this->db = db_connect();
        $this->db->query("SET time_zone = '+07:00'");
    }

    function addPrefix($field){
        return 's.'.$field;
    }

    function read($json, $segment){
        helper(['Query_helper']);
        $limit = $json->limit ?? 10;
        $offset = $json->offset ?? 0;
		$sortBy = $json->sortBy ?? array();
        $sortDesc = $json->sortDesc ?? array();
		$order = order($sortBy, $sortDesc);
        $db = $this->db;
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);

		$wseg = ['1=1'];
		if(isset($segment[2])){
			$q = $db->query("select * from document_alias where alias = '".$segment[2]."'");
			$res = $q->getResult();
			$dt = $res[0]->table_name;
			$dtId = $res[0]->alias_id;
			$wseg[] = "document_alias_id = $dtId";
		}

        /* if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; */
		if(isset($segment)){
			if(isset($segment[2])){
				$identifier = $segment[2];
				$q = $db->query("select document_id from $dt where identifier = '$identifier'");
				$res = $q->getResult();
				if(isset($res[0]))
					$wseg[] = "document_id = ".$res[0]->document_id;
			}
		}

		$wseg = implode(' and ', $wseg);
		$q = "select c.name as created_by_name, m.name as modified_by_name, a.alias as alias_name,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
			left join users c on c.id = s.created_by
			left join users m on m.id = s.modified_by
			left join document_alias a on a.alias_id = s.document_alias_id
			where $wseg";
        $query = $db->query("select * from
		($q)s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
        
		if(!$query)
			return [false, $this->db->error(), 0]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		return [true, $query->getResult(), $totalQuery[0]->total];
    }
}