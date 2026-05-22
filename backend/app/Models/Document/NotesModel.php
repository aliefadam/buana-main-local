<?php namespace App\Models\Document;
 
use CodeIgniter\Model;
 
class NotesModel extends Model
{
    protected $table = 'notes';
    protected $returnType     = 'object';
    protected $primaryKey = 'note_id';
    protected $allowedFields = ["note_id","document_id","created_date","created_by","modified_by","modified_date","flag","parent_id","container_id"];
	
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
        /* if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; */

		//select table from alias
		$segTable = explode('.', $segment[2]);
		$q = $db->query("select * from document_alias where alias = '".$segTable[0]."'");
		$res = $q->getResult();
		$dt = $res[0]->table_name;
		$docId = $res[0]->alias_id;
		$tmpDt = explode('dt_', $dt);
		$notes = 'notes_'.$tmpDt[1];
		$values = 'values_'.$tmpDt[1];

		$document_notes = "SELECT
		n.note_id,
		n.document_id,
		n.created_date,
		n.created_by,
		n.modified_by,
		n.modified_date,
		n.flag,
		n.parent_id,
		n.container_id,
		f.name AS name,
		v.document_value_id,
		f.document_field_id,
		v.value_numeric,
		v.value_text,
		v.value_varchar,
		v.value_datetime,
		v.value_document,
		v.value_note,
		v.value_int,
		v.value_list,
		v.value_attachment,
		f.sort,
		f.width_xs,
		f.width_sm,
		f.width_m,
		f.width_l,
		f.width_xl,
		f.datatype,
		f.is_timeline,
		f.list,
		f.listurl,
		f.listurlsearch,
		f.listurltext,
		f.listurlvalue,
		f.listdocument
	  FROM $notes n 
	  LEFT JOIN $dt d ON (d.document_id = n.document_id)
	  LEFT JOIN document_fields f ON (f.document_alias_id = $docId)
	  LEFT JOIN $values v ON (
			v.document_field_id = f.document_field_id AND v.notes_id = n.note_id
		)";

		$wseg = ['s.flag=1'];
		if(isset($segment)){
			if(isset($segment[2])){
				$identifier = $segment[2];
				$q = $db->query("select document_id from $dt where identifier = '$identifier'");
				$res = $q->getResult();
				if(isset($res[0]))
					$wseg[] = "s.document_id = ".$res[0]->document_id;
			}
		}

		$wseg = implode(' and ', $wseg);
		$q = "select 
			c.name as created_by_name, m.name as modified_by_name,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", 
			(select name from $dt where document_id = d.root_id) as root_name, d.root_id, i.identifier, d.identifier as doc_identifier
			from `$notes` s
			left join users c on c.id = s.created_by
			left join users m on m.id = s.modified_by
			left join document_identifier i on i.note_id = s.note_id and i.document_id = s.document_id
			left join $dt d on d.document_id = s.document_id
			where $wseg";
        $query = $db->query("select * from
		($q)s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
        
		if(!$query)
			return [false, $this->db->error(), 0]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		$result = $query->getResult();

		$dbdatatype = [
			"decimal"=> "numeric",
			"int"=> "int",
			"list"=> "list",
			"listurl"=> "list",
			"document"=> "document",
			"note"=> "note",
			"date"=> "datetime",
			"datetime"=> "datetime",
			"time"=> "datetime",
			"listurl"=> "list"
		];

		//return [true, $result, $totalQuery[0]->total];

		foreach ($result as $i => $val) {
			$q = $db->query("select name, document_field_id, datatype, is_timeline, 
			listurl, listurlsearch, listurltext, listurlvalue, 
			sort, 
			width_xs,
			coalesce(width_sm, width_xs) as width_sm, 
			coalesce(width_m, width_sm, width_xs) as width_m, 
			coalesce(width_l, width_m, width_sm, width_xs) as width_l, 
			coalesce(width_xl, width_l, width_m, width_sm, width_xs) as width_xl,
			list, listdocument from document_fields where document_id = ".$val->document_id);
			$res = $q->getResult();
			//$q = $db->query("select * from document_fields where document_id = ".$val->document_id);
			//$fields = $q->getResult();
			//$val->fields = $res;
			$q = $db->query("select v.*, f.format_as from (SELECT d.*, row_number() OVER (partition by d.note_id, d.name order by d.document_value_id desc) as rn FROM ($document_notes where n.note_id = ".$val->note_id.")d )v
			left join document_fields f on f.document_field_id = v.document_field_id
			where v.rn = 1;");
			
			//$val->r = $_SERVER;
			//$val->q = "select * from (SELECT *, row_number() OVER (partition by note_id, name order by document_value_id desc) as rn FROM documents where note_id = ".$val->note_id.")v where v.rn = 1";
			$value = $q->getResult();
			foreach($value as $vi => $vval){
				//`field_${dbdatatype[val.datatype] || val.datatype}_${val.document_field_id}`
				$name = "field_".($dbdatatype[$vval->datatype] ?? $vval->datatype)."_".$vval->document_field_id;
				if($vval->datatype == 'list' && $vval->{"value_".($dbdatatype[$vval->datatype] ?? $vval->datatype)} != null){
					$q = $db->query("select * from document_list_values where document_list_id = ? and value = ? ", [$vval->list, $vval->{"value_".($dbdatatype[$vval->datatype] ?? $vval->datatype)}]);
					$list = $q->getResult();
					if($list)
						$val->{$name."_text"} = $list[0]->text ?? $list[0]->value;
				}

				$val->{$name} = $vval->{"value_".($dbdatatype[$vval->datatype] ?? $vval->datatype)};
			}

		}
		// $a = "select 
		// c.name as created_by_name, m.name as modified_by_name,
		// ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
		// from `$this->table` s
		// left join users c on c.id = s.created_by
		// left join users m on m.id = s.modified_by
		// where $wseg";
		return [true, $result, $totalQuery[0]->total];
    }
}