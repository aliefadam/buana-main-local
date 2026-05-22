<?php namespace App\Models\Machine\Gb;
 
use CodeIgniter\Model;
 
class DataModel extends Model
{
    protected $table = 'prod_reg1';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","production","waste","created_date"];
	
	protected function initialize()
    {
        $this->db = db_connect('machine_gb');
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
		$table = $json->table;
		$order = order($sortBy, $sortDesc);
        $db = $this->db;
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
        /* if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; */
		$q = "select 
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s";
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

	function uptime($json){
		/* $q = "select s.barcode, p.part_name from (select distinct i.barcode from tracking_item i left join tracking_m_part mp on mp.barcode = i.barcode where mp.group_name is null and process = 'dismantle')s
			left join tracking_m_part p on p.barcode = s.barcode
			left join tracking_item i on i.barcode = s.barcode
		"; */
		$this->db->query("SET time_zone = '+07:00'");
		$q = "
		select *, coalesce((SELECT quality FROM prod_reg1 a where a.created_date >= NOW() - INTERVAL 10 MINUTE and quality = 1 ORDER BY `id` DESC limit 1), 0) as quality,
		case 
		when day_changed = 0 then CURRENT_DATE()
		when day_changed = 1 and now() > CONCAT(CURDATE(), ' 00:00:00') then DATE_SUB(CURRENT_DATE(), interval 1 day)
		when day_changed = 1 and now() < CONCAT(CURDATE(), ' 00:00:00') then CURRENT_DATE()
		end as  start_date,
		case 
		when day_changed = 0 then CURRENT_DATE()
		when day_changed = 1 and now() > CONCAT(CURDATE(), ' 00:00:00') then CURRENT_DATE()
		when day_changed = 1 and now() < CONCAT(CURDATE(), ' 00:00:00') then DATE_ADD(CURRENT_DATE(), interval 1 day)
		end as  end_date, (production-waste) as good, CURRENT_TIME() as time, (select machine_speed from parameter_reg1 order by id desc limit 1) as current_speed,
		case 
		when lapse_day = 0 then a.speed * (TIME_TO_SEC(TIMEDIFF(CURRENT_TIME(), a.start))/60)
		when lapse_day = 1 and day_changed = 0 then a.speed * (TIME_TO_SEC(TIMEDIFF(CURRENT_TIME(), a.start))/60)
		when lapse_day = 1 and day_changed = 1 then a.speed * ((TIME_TO_SEC(TIMEDIFF(CURRENT_TIME(), time('00:00:00')))/60) + (TIME_TO_SEC(TIMEDIFF(time('00:00:00'), a.start))/60)) 
		ELSE 0
		END
		as total_output
		from(
			select a.*, ma.speed, ma.line, ma.brand, ma.machine_type, CURRENT_TIME() as created_time, sa.start, sa.end, sa.lapse_day, sa.start > time(a.created_date) as day_changed from (select *, 1 as machine_id from prod_reg1 where quality = 1 order by created_date desc limit 1)a 
			left join machine ma on ma.id = a.machine_id
			left join (select *, start > end as lapse_day from machine_shift where flag = 1 order by shift_date desc) sa on sa.machine_id = a.machine_id and sa.shift_date <= a.created_date and (CURRENT_TIME() >= sa.start or (sa.lapse_day = 1 and CURRENT_TIME() > CONCAT(CURDATE(), ' 00:00:00'))) and (CURRENT_TIME() <= sa.end or (sa.lapse_day = 1 and CURRENT_TIME() < CONCAT(CURDATE(), ' 00:00:00')))
			limit 1
			)a

		union all 

		select *, coalesce((SELECT quality FROM prod_reg2 a where a.created_date >= NOW() - INTERVAL 10 MINUTE and quality = 1 ORDER BY `id` DESC limit 1), 0) as quality,
		case 
		when day_changed = 0 then CURRENT_DATE()
		when day_changed = 1 and now() > CONCAT(CURDATE(), ' 00:00:00') then DATE_SUB(CURRENT_DATE(), interval 1 day)
		when day_changed = 1 and now() < CONCAT(CURDATE(), ' 00:00:00') then CURRENT_DATE()
		end as  start_date,
		case 
		when day_changed = 0 then CURRENT_DATE()
		when day_changed = 1 and now() > CONCAT(CURDATE(), ' 00:00:00') then CURRENT_DATE()
		when day_changed = 1 and now() < CONCAT(CURDATE(), ' 00:00:00') then DATE_ADD(CURRENT_DATE(), interval 1 day)
		end as  end_date, (production-waste) as good, CURRENT_TIME() as time, (select machine_speed from parameter_reg2 order by id desc limit 1) as current_speed, 
		case 
		when lapse_day = 0 then b.speed * (TIME_TO_SEC(TIMEDIFF(CURRENT_TIME(), b.start))/60)
		when lapse_day = 1 and day_changed = 0 then b.speed * (TIME_TO_SEC(TIMEDIFF(CURRENT_TIME(), b.start))/60)
		when lapse_day = 1 and day_changed = 1 then b.speed * ((TIME_TO_SEC(TIMEDIFF(CURRENT_TIME(), time('00:00:00')))/60) + (TIME_TO_SEC(TIMEDIFF(time('00:00:00'), b.start))/60)) 
		ELSE 0
		END
		as total_output
		from(
			select a.*, ma.speed, ma.line, ma.brand, ma.machine_type, CURRENT_TIME() as created_time, sa.start, sa.end, sa.lapse_day, sa.start > time(a.created_date) as day_changed from (select *, 2 as machine_id from prod_reg2 where quality = 1 order by created_date desc limit 1)a 
			left join machine ma on ma.id = a.machine_id
			left join (select *, start > end as lapse_day from machine_shift where flag = 1 order by shift_date desc) sa on sa.machine_id = a.machine_id and sa.shift_date <= a.created_date and (CURRENT_TIME() >= sa.start or (sa.lapse_day = 1 and CURRENT_TIME() > CONCAT(CURDATE(), ' 00:00:00'))) and (CURRENT_TIME() <= sa.end or (sa.lapse_day = 1 and CURRENT_TIME() < CONCAT(CURDATE(), ' 00:00:00')))
			limit 1
		)b

		union all 

		select *, coalesce((SELECT quality FROM prod_reg3 a where a.created_date >= NOW() - INTERVAL 10 MINUTE and quality = 1 ORDER BY `id` DESC limit 1), 0) as quality,
		case 
		when day_changed = 0 then CURRENT_DATE()
		when day_changed = 1 and now() > CONCAT(CURDATE(), ' 00:00:00') then DATE_SUB(CURRENT_DATE(), interval 1 day)
		when day_changed = 1 and now() < CONCAT(CURDATE(), ' 00:00:00') then CURRENT_DATE()
		end as  start_date,
		case 
		when day_changed = 0 then CURRENT_DATE()
		when day_changed = 1 and now() > CONCAT(CURDATE(), ' 00:00:00') then CURRENT_DATE()
		when day_changed = 1 and now() < CONCAT(CURDATE(), ' 00:00:00') then DATE_ADD(CURRENT_DATE(), interval 1 day)
		end as  end_date, (production-waste) as good, CURRENT_TIME() as time, (select machine_speed from parameter_reg3 order by id desc limit 1) as current_speed, 
		case 
		when lapse_day = 0 then c.speed * (TIME_TO_SEC(TIMEDIFF(CURRENT_TIME(), c.start))/60)
		when lapse_day = 1 and day_changed = 0 then c.speed * (TIME_TO_SEC(TIMEDIFF(CURRENT_TIME(), c.start))/60)
		when lapse_day = 1 and day_changed = 1 then c.speed * ((TIME_TO_SEC(TIMEDIFF(CURRENT_TIME(), time('00:00:00')))/60) + (TIME_TO_SEC(TIMEDIFF(time('00:00:00'), c.start))/60)) 
		ELSE 0
		END
		as total_output
		from(
			select a.*, ma.speed, ma.line, ma.brand, ma.machine_type, CURRENT_TIME() as created_time, sa.start, sa.end, sa.lapse_day, sa.start > time(a.created_date) as day_changed from (select *, 3 as machine_id from prod_reg3 where quality = 1 order by created_date desc limit 1)a 
			left join machine ma on ma.id = a.machine_id
			left join (select *, start > end as lapse_day from machine_shift where flag = 1 order by shift_date desc) sa on sa.machine_id = a.machine_id and sa.shift_date <= a.created_date and (CURRENT_TIME() >= sa.start or (sa.lapse_day = 1 and CURRENT_TIME() > CONCAT(CURDATE(), ' 00:00:00'))) and (CURRENT_TIME() <= sa.end or (sa.lapse_day = 1 and CURRENT_TIME() < CONCAT(CURDATE(), ' 00:00:00')))
			limit 1
		)c
		";

		$query = $this->db->query($q);
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}
   
    function data($q, $params){
        $db = db_connect('machine_gb');
        $query = $db->query("$q", $params);
        
		if(!$query)
			return [false, $this->db->error()]; 
		return [true, is_bool($query) ? 'executed!' : $query->getResult()];
    }
}