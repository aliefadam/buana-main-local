<?php



namespace App\Models;



use CodeIgniter\Model;



class PurchaseOrderItemGroupModel extends Model

{

    protected $table = 'purchase_order_item';

    protected $returnType     = 'object';

    protected $primaryKey = 'id';

    protected $allowedFields = [

        "id",

        "purchase_order_id",

        "bom_id",

        "price_per_item",

        "order_qty",

        "item_id",

        "active",

        'flag',

        'pr_part_id',

        "allocation",

        'complete_qty',

        'created_date',

        'created_by',

        'modified_date',

        'modified_by',

        'order_no',

        'subledger_id',
        'use_ppn',
        'use_pph',
        'ppn_value',
        'pph_value',
        "is_warning",
        "force_budget_minus_reason",
    ];



    protected function initialize()

    {

        $this->db = db_connect();

        $this->db->query("SET time_zone = '+07:00'");

    }



    function addPrefix($field)

    {

        return 's.' . $field;

    }



    function read($json)

    {

        helper(['Query_helper']);

        $limit = $json->limit ?? 10;

        $offset = $json->offset ?? 0;

        $sortBy = $json->sortBy ?? array();

        $sortDesc = $json->sortDesc ?? array();

        $order = order($sortBy, $sortDesc);

        $db = db_connect();

        $where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);

        if (trim($where) == '')

            $where = 'where s.flag = 1';

        else

            $where .= ' and s.flag = 1';

            

//         $q = "

// 		SELECT i.datasheet, ms.pic_name, p.currency, s.*, order_qty*price_per_item as total_price_per_item, i.specification from (

// 			select s.order_no, s.id, s.flag, s.purchase_order_id, s.item_id, i.item_no, i.item_name, i.unit, i.original_manufacture, i.manufacture_pn, i.article_no, sum(s.order_qty) as order_qty, sum(s.complete_qty) as complete_qty,

// 			(select price_per_item from purchase_order_item where purchase_order_id = s.purchase_order_id and item_id = s.item_id and flag=1 limit 1) as price_per_item 

// 			from purchase_order_item s left join m_item i on i.id = s.item_id

// 			where i.id is not null and s.flag = 1

// 			group by s.item_id, i.original_manufacture, i.manufacture_pn, i.specification, i.unit, i.article_no, s.purchase_order_id) s

// 		left join m_item i on i.id = s.item_id

// 		left join purchase_order p on p.id = s.purchase_order_id

// 		left join m_supplier ms on ms.id = p.supplier_id

// 		";



        $q = "

            SELECT 

                x.*,

        

                (x.order_qty * x.price_per_item) as total_price_per_item,

        

                CASE

                    WHEN x.price_per_item_awal IS NULL OR x.price_per_item_awal = 0 THEN 0

                    ELSE (((x.price_per_item - x.price_per_item_awal) / x.price_per_item_awal) * 100)

                END as selisih_persen,

        

                CASE

                    WHEN x.price_per_item_awal IS NULL OR x.price_per_item_awal = 0 THEN 'hijau'

                    WHEN (((x.price_per_item - x.price_per_item_awal) / x.price_per_item_awal) * 100) > 2 THEN 'red'

                    WHEN (((x.price_per_item - x.price_per_item_awal) / x.price_per_item_awal) * 100) > 0 

                         AND (((x.price_per_item - x.price_per_item_awal) / x.price_per_item_awal) * 100) <= 2 THEN 'yellow'

                    ELSE 'green'

                END as text_label_color

        

            FROM (

                SELECT 

                    s.order_no, 

                    s.id, 

                    s.flag, 

                    s.purchase_order_id, 

                    s.item_id, 

                    i.item_no, 

                    i.item_name, 

                    max(i.datasheet) as datasheet,

                    i.unit, 

                    i.original_manufacture, 

                    i.manufacture_pn, 

                    i.article_no, 
                    max(coalesce(s.use_ppn, 0)) as use_ppn,
                    max(coalesce(s.use_pph, 0)) as use_pph,
                    sum(coalesce(s.ppn_value, 0)) as ppn_value,
                    sum(coalesce(s.pph_value, 0)) as pph_value,
                    max((select coalesce(po.ppn, 0) from purchase_order po where po.id = s.purchase_order_id limit 1)) as ppn,
                    max((select coalesce(po.pph, 0) from purchase_order po where po.id = s.purchase_order_id limit 1)) as pph,
                    SUM(s.order_qty) as order_qty, 
                    SUM(s.complete_qty) as complete_qty,
                    

                    (

                        SELECT poi.price_per_item

                        FROM purchase_order_item poi

                        WHERE poi.purchase_order_id = s.purchase_order_id 

                          AND poi.item_id = s.item_id 

                          AND poi.flag = 1

                        ORDER BY poi.id ASC

                        LIMIT 1

                    ) as price_per_item,

        

                    (

                        SELECT ps.unit_price

                        FROM purchase_order_item poi

                        LEFT JOIN pr_subledger ps ON ps.id = poi.subledger_id

                        WHERE poi.purchase_order_id = s.purchase_order_id 

                          AND poi.item_id = s.item_id 

                          AND poi.flag = 1

                        ORDER BY poi.id ASC

                        LIMIT 1

                    ) as price_per_item_awal

        

                FROM purchase_order_item s 

                LEFT JOIN m_item i ON i.id = s.item_id

                WHERE i.id IS NOT NULL 

                  AND s.flag = 1

                GROUP BY 

                    s.item_id, 

                    i.original_manufacture, 

                    i.manufacture_pn, 

                    i.specification, 

                    i.unit, 

                    i.article_no, 

                    s.purchase_order_id

            ) x

        ";

		

        $query = $db->query("select * from ($q) s $where $order limit $offset, $limit");



        if (!$query)

            return [$this->db->error(), false];



        $totalQuery = $db->query("select count(*) as total from ($q) s $where");

        $totalQuery = $totalQuery->getResult();

        $res = $query->getResult();

        if (count($res) > 0) {

            $id = $res[0]->purchase_order_id;

            $query = $db->query("

                select
                    (
                        (
                            coalesce((select sum(total) as total from (select order_qty*price_per_item as total from purchase_order_item where purchase_order_id = $id and flag=1)k), 0)
                            + coalesce(a.charge1, 0)
                            + coalesce(a.charge2, 0)
                            + coalesce(a.other_charge, 0)
                            - coalesce(a.discount, 0)
                        )
                        + coalesce(sum(s.ppn_value), 0)
                        - coalesce(sum(s.pph_value), 0)
                    ) as grand_total
                from purchase_order_item s
                left join purchase_order a on a.id = s.purchase_order_id
                where s.purchase_order_id = $id and s.flag=1
                limit 1
            ");

            $query = $query->getResult();

            $res[0]->grand_total = $query[0]->grand_total;
        }

        return [$res, $totalQuery[0]->total];

    }



    /* readAll($pid){

        helper(['Query_helper']);

        $db = db_connect();

        $q = "`$this->table` s left join bom_header h on h.id = s.bom_id left join m_item i on i.id = s.item_id left join purchase_order p on p.id = s.purchase_order_id where purchase_order_id = $pid";

        $query = $db->query("select 

        (select sum(order_qty * price_per_item) from purchase_order_item where purchase_order_id = s.purchase_order_id) as total_item_price , 

        s.* from

        (select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", h.bom_no, i.item_name, i.item_no, i.original_manufacture, i.manufacture_pn, i.specification, p.currency, (s.order_qty*s.price_per_item) as total_price_per_item from $q) s");

        

        if(!$query)

            return [$this->db->error(), false];

    

        $totalQuery = $db->query("select count(*) as total from $q");

        $totalQuery = $totalQuery->getResult();

        $res = $query->getResult();

        if(count($res) > 0){

            $id = $res[0]->purchase_order_id;

            $query = $db->query("

                select
                    (
                        (
                            coalesce(sum(order_qty * price_per_item), 0)
                            + coalesce(a.charge1, 0)
                            + coalesce(a.charge2, 0)
                            + coalesce(a.other_charge, 0)
                            - coalesce(a.discount, 0)
                        )
                        + coalesce(sum(s.ppn_value), 0)
                        - coalesce(sum(s.pph_value), 0)
                    ) as grand_total
                from purchase_order_item s
                left join purchase_order a on a.id = s.purchase_order_id
                where s.purchase_order_id = $id
            ");

            $query = $query->getResult();

            $res[0]->grand_total = $query[0]->grand_total;

        }

        return [$res, $totalQuery[0]->total];

    } */



    function read_bom($id, $pid)

    {

        helper(['Query_helper']);

        $db = db_connect();

        $q = " bom_header h inner join bom_item bi on bi.bom_header_id = h.id left join m_item i on i.id = bi.item_id where h.id = $id and i.is_active = 1";

        $query = $db->query("

            select s.id, s.item_id, s.order_qty from `$this->table` s where purchase_order_id = $pid and active = 1 and flag = 1

            union all

            select null as id, i.id as item_id, bi.order_qty from $q

        ");

        if (!$query)

            return [false, $this->db->error()];

        return $query->getResult();

    }



    function read_pr_part($id)

    {

        helper(['Query_helper']);

        $db = $this->db;

        $query = $db->query("

		SELECT s.id, s.allocation, s.qty as order_qty, p.item_id FROM `pr_subledger` s

		left join pr_part p on p.id=s.pr_part_id

		WHERE s.id= $id

        ");

        /* $query = $db->query("

            select  *, (select sum(qty) from pr_subledger where pr_part_id = $id) as order_qty, (select allocation from pr_subledger where pr_part_id = $id) as allocation from pr_part where id = $id

        "); */

        if (!$query)

            return [false, $this->db->error()];

        return $query->getResult();

    }



    function readSubledgerByPo($po_id)

    {

        $db = db_connect();

        $query = $db->query("

            select 

                ps.id as subledger_id,

                ps.description as subledger_name,

                s.order_qty as qty,

                max(s.price_per_item) as price,

                max(po.exchange_rate) as exchange_rate,

                max(po.currency) as currency,

                i.item_name as part_name,

                pp.id as pr_part_id,

                mp.id as project_id,

                mp.project_no as project_no,

                mp.project_name as project_name,

                pb.id as project_budget_id,

                b.name as budget_name,

                max(po.charge1) as charge1,

                max(po.charge1_desc) as charge1_desc,

                max(po.charge2) as charge2,

                max(po.charge2_desc) as charge2_desc,

                max(po.discount) as discount,

                max(s.is_warning) as is_warning,

                max(s.force_budget_minus_reason) as force_budget_minus_reason,

                i.item_no as item_no

            from purchase_order_item s

            left join pr_subledger ps on ps.id = s.subledger_id

            left join pr_part pp on pp.id = ps.pr_part_id

            left join m_item i on i.id = pp.item_id

            left join m_project mp on mp.id = ps.project_id

            left join project_budgets pb on pb.id = ps.budget_id

            left join budgets b on b.id = pb.budget_id

            left join purchase_order po on po.id = s.purchase_order_id

            where s.flag = 1 and s.purchase_order_id = ?

            group by ps.id, ps.description, s.order_qty, i.item_name, pp.id, mp.id, mp.project_no, mp.project_name, pb.id, b.name

            order by mp.project_no, b.name, i.item_name, ps.description

        ", [$po_id]);



        if (!$query)

            return [false, $this->db->error()];



        return [true, $query->getResult()];

    }

}

