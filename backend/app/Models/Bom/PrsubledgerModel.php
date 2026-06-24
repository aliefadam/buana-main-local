<?php namespace App\Models\Bom;
 
use CodeIgniter\Model;
 
class PrsubledgerModel extends Model
{
    protected $table = 'pr_subledger';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","unit_price","requirement","budget_id","pr_part_id","description","qty", "allocation","project_id", "alokasi_pembelian", "category_item_id", "created_date","created_by","modified_date","modified_by", "flag", "project_type", "type_operational_id", "sub_type_operational_id", "dept_id","currency","exchange_rate","rate_date","rnd_id","is_warning","force_budget_minus_reason","year_budget","is_part"];
	
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
        $db = $this->db;
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
        if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; 
		$q = "select *, qty - total_order as left_qty from (select ROW_NUMBER() OVER(PARTITION BY s.pr_part_id ) as no, rnd.name as rnd_name, pr.id as pr_id, i.item_name, p.item_id, concat(s.qty, '-', s.description) as text, pp.project_no as project_no, pp.project_name,
				".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", ci.name as categoryitem_name,
				c.name as created_by_name, top.name as type_operational, sto.name as sub_type_operational, md.dept_name, b.name as budget_name,
				m.name as modified_by_name, 
				(
					select count(pi.id) from purchase_order_item pi 
					left join purchase_order po on po.id=pi.purchase_order_id where pi.subledger_id = s.id and po.approved>=0 and pi.flag=1 and po.flag=1
				) as in_po,
				coalesce(
					(
						select sum(pi.order_qty) from purchase_order_item pi 
						left join purchase_order po on po.id=pi.purchase_order_id where pi.subledger_id = s.id and po.approved>=0 and pi.flag=1
					), 0
				) as total_order
				from `$this->table` s
				left join pr_part p on p.id = s.pr_part_id
				left join pr on pr.id = p.pr_id
				left join m_item i on i.id = p.item_id
				left join m_department md on md.id = s.dept_id
				left join m_project pp on pp.id = s.project_id
				left join m_category_item ci on ci.id=s.category_item_id
				left join type_operationals top on top.id=s.type_operational_id
				left join type_sub_operationals sto on sto.id=s.sub_type_operational_id
				left join project_budgets pb on pb.id=s.budget_id
				left join research_and_developments rnd on rnd.id=s.rnd_id
				left join budgets b on b.id = pb.budget_id
				left join users c on c.id = s.created_by
				left join users m on m.id = s.modified_by
			)s";
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

    function info($id){
		$query = $this->db->query("select pr.rev as pr_rev, pi.project_id, pi.description, pi.allocation, pi.qty, pp.id as pp_id
		from pr_subledger pi
        left join pr_part pp on pp.id=pi.pr_part_id
		left join pr on pr.id=pp.pr_id
		where pi.id=$id");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}

    function mutationSnapshot($id){
		$query = $this->db->query("
            select
                s.id as pr_subledger_id,
                s.pr_part_id,
                pp.pr_id,
                s.description,
                s.qty,
                s.unit_price,
                s.allocation,
                s.project_id,
                mp.project_no,
                mp.project_name,
                s.budget_id,
                b.name as budget_name,
                s.project_type,
                s.dept_id,
                md.dept_name,
                s.type_operational_id,
                top.name as type_operational_name,
                s.sub_type_operational_id,
                sto.name as sub_type_operational_name,
                s.category_item_id,
                ci.name as category_item_name,
                s.alokasi_pembelian,
                s.rnd_id,
                rnd.name as rnd_name,
                s.currency,
                s.exchange_rate,
                s.rate_date,
                s.year_budget,
                s.requirement,
                s.created_by,
                s.created_date,
                s.modified_by,
                s.modified_date
            from pr_subledger s
            left join pr_part pp on pp.id = s.pr_part_id
            left join m_project mp on mp.id = s.project_id
            left join project_budgets pb on pb.id = s.budget_id
            left join budgets b on b.id = pb.budget_id
            left join m_department md on md.id = s.dept_id
            left join type_operationals top on top.id = s.type_operational_id
            left join type_sub_operationals sto on sto.id = s.sub_type_operational_id
            left join m_category_item ci on ci.id = s.category_item_id
            left join research_and_developments rnd on rnd.id = s.rnd_id
            where s.id = ?
            limit 1
        ", [$id]);

		if(!$query)
			return [false, $this->db->error()];

        $result = $query->getResult();
        if(!count($result))
			return [false, 'Data not found'];

		return [true, $result[0]];
    }
}
