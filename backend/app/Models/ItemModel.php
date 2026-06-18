<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class ItemModel extends Model
{
    protected $table = 'm_item';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'item_no', 'item_name', 'unit', 'original_manufacture', 'manufacture_pn', 'specification', 'item_type', 'is_active', 'created_by', 'created_date', 'modified_by', 'modified_date', 'noid', 'flag', 'datasheet', 'article_no', 'equivalent', 'is_subpart'];
	
	protected function initialize()
    {
        $this->db = db_connect();
		$this->db->query("SET time_zone = '+07:00'");
    }

    function addPrefix($field){
        return 's.'.$field;
    }
   
    function read($json){
		helper(['Query_helper']);
        $limit = $json->limit ?? 10;
        $offset = $json->offset ?? 0;
        $sortBy = $json->sortBy ?? array();
        $sortDesc = $json->sortDesc ?? array();
		$order = order($sortBy, $sortDesc);
        $db = db_connect();
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
        if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1';

        $searchValue = '';
        $searchFields = ['item_no', 'full', 'item_name', 'manufacture_pn', 'article_item'];
        foreach ($searchFields as $field) {
            if (isset($json->filter[$field])) {
                $candidate = trim((string) $json->filter[$field]);
                if ($candidate !== '') {
                    $searchValue = $candidate;
                    break;
                }
            }
        }

        $searchRank = "5 as search_rank";
        if ($searchValue !== '') {
            $escapedExact = $db->escape($searchValue);
            $escapedPrefix = $db->escape($db->escapeLikeString($searchValue) . '%');
            $escapedAnywhere = $db->escape('%' . $db->escapeLikeString($searchValue) . '%');
            $searchRank = "
                case
                    when s.item_no = $escapedExact then 0
                    when s.item_no like $escapedPrefix then 1
                    when s.item_name like $escapedAnywhere then 2
                    when coalesce(s.manufacture_pn, '') like $escapedAnywhere then 3
                    when coalesce(s.original_manufacture, '') like $escapedAnywhere then 4
                    else 5
                end as search_rank
            ";

            $orderBody = trim(preg_replace('/^\s*order by\s+/i', '', $order));
            $order = " order by search_rank asc";
            if ($orderBody !== '') {
                $order .= ", " . $orderBody;
            } else {
                $order .= ", item_no asc";
            }
        }
            
		$q = "select date(s.created_date) as crea_date, date(s.modified_date) as mod_date, poi.price_per_item, 
        $searchRank,
        concat(';', s.id, ';') as item_search, po.currency, concat(s.item_no, ' - ', s.item_name, ' - ', coalesce(s.original_manufacture, ''), ' - ', coalesce(s.manufacture_pn, '')) as full, concat(s.item_name, ' - ',  coalesce(s.article_no, '')) as article_item, 
        u.name as created_by_name, v.name as modified_by_name,  ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." from `$this->table` s
		left join (select poi.price_per_item, poi.purchase_order_id, poi.item_id, row_number() over(partition by poi.item_id order by modified_date, id desc) as ct from purchase_order_item poi)poi on poi.item_id = s.id and poi.ct = 1
		left join purchase_order po on po.id = poi.purchase_order_id
        left join users u on u.id = s.created_by
        left join users v on v.id = s.modified_by
        /* fix subpart muncul */
        where s.is_subpart is null
        or s.is_subpart < 1	
        ";

        $fields="*";
        if(isset($json->full))
			$fields = "distinct id, full, specification, datasheet, item_type, search_rank";

        $query = $db->query("select $fields from ($q) s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
        
		if(!$query)
			return [$this->db->error(), false]; 
		
		$totalQuery = $db->query("select count($fields) as total from ($q) s $where");
		$totalQuery = $totalQuery->getResult();
		return [$query->getResult(), $totalQuery[0]->total];
    }

	function lastNumber($type){
		$query = $this->db->query("select LPAD(IFNULL( (SELECT CAST(noid AS UNSIGNED) FROM (select item_type, cast(noid as UNSIGNED) as noid from `m_item`)s WHERE item_type = '$type' order by noid desc limit 1), 0)+1, 5, 0) as no, IFNULL( (SELECT CAST(noid AS UNSIGNED) FROM (select item_type, cast(noid as UNSIGNED) as noid from `m_item`)s WHERE item_type = '$type' order by noid desc limit 1), 0)+1 as number");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}

	function data($id){
		$query = $this->db->query("select * from m_item where id = $id");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}
}
