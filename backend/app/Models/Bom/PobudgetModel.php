<?php

namespace App\Models\Bom;

use CodeIgniter\Model;

class PobudgetModel extends Model
{
    protected $table = 'po_budget';
    protected $returnType     = 'object';
    protected $primaryKey = 'po_no';
    protected $allowedFields = ["po_no", "po_month", "po_id", "cny", "idr", "eur", "usd", "total_in_idr", "exchange_rate"];

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
        $db = $this->db;
        $where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
        /* if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; */
        $q = "select p.currency, p.approved, date(p.approval_2_date) as approval_2_date, p.note,p.year,  (select count(distinct i.id) from payment_item p left join invoice i on i.flag = 1 and i.id = p.invoice_id left join payment py on py.flag >=0 and py.approved >=0 and py.id = p.payment_id where i.po_id = s.po_id and p.flag = 1) as total_invoice_in_payment,  (select count(distinct py.id) from payment_item p left join invoice i on i.flag = 1 and i.id = p.invoice_id left join payment py on py.flag >=0 and py.approved >=0 and py.id = p.payment_id where i.po_id = s.po_id and p.flag = 1 and py.done = 0 ) as payment_not_done, (select sum(case when i.payment_pct_fiat != 0 then i.payment_pct_fiat else (i.grand_total_price * (i.payment_pct/100)) end) from invoice i where i.id in (select distinct i.id from payment_item p left join invoice i on i.flag = 1 and i.id = p.invoice_id left join payment py on py.flag >=0 and py.approved >=0 and py.id = p.payment_id where i.po_id = s.po_id and p.flag = 1)) as total_payment, coalesce((select sum(i.payment_pct) from invoice i where i.po_id = s.po_id and i.flag > 0), 0) as total_payment_pct, p.title, p.supplier_id, u.name as supplier_name, p.pr_no, i.grand_total_price, r.mr_task_no, p.charge1, p.charge1_desc, p.charge2, p.charge2_desc, " . join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)) . "
			from `$this->table` s 
			left join purchase_order p on p.id = s.po_id
            left join rfq_header r on r.id = p.rfq_id
			left join m_supplier u on u.id=p.supplier_id
			left join (select sum(order_qty * price_per_item) as grand_total_price, purchase_order_id from purchase_order_item where flag = 1 group by purchase_order_id) i on i.purchase_order_id = s.po_id";
        $query = $db->query("select * from
		($q)s $where $order " . ($limit == -1 ? '' : "limit $offset, $limit"));

        if (isset($json->debug))
            if ($json->debug == '8080')
                return [true, $q, 0];
        if (!$query)
            return [false, $this->db->error(), 0];

        $totalQuery = $db->query("select count(*) as total from ($q)s $where");
        if (!$totalQuery)
            return [false, $this->db->error()];
        $totalQuery = $totalQuery->getResult();
        return [true, $query->getResult(), $totalQuery[0]->total];
    }


    function getDataProject($json)
    {
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
        $q = "
            SELECT 
            s.project_no, 
            s.project_name, 
            format(
                sum(s.total_in_idr), 
                2
            ) as idr 
            FROM 
            (
                
                /*Query Monitoring PO - Payment*/
                SELECT 
                po.po_no as pono, 
                po.year, 
                ms.name, 
                po.title, 
                case when po.approved = '0' then 'New' when po.approved = '1' then 'Asking Approval 1' when po.approved = '2' then 'Asking Approval 2' when po.approved = '3' then 'Final Approve' when po.approved = '4' then 'Clarification' when po.approved = '5' then 'Asking For Draft PO' when po.approved = '6' then 'Approval 2 Approved Draft' when po.approved = '-1' then 'Rejected' when po.approved = '-2' then 'Asking For Canceled 1' when po.approved = '-3' then 'Asking For Canceled 2' when po.approved = '-4' then 'Canceled' end as PO_Status, 
                pr.pr_no, 
                #i.grand_total_price as PO_grandTotal, 
                rfq.reference_no, 
                mp.project_name, 
                mp.project_no, 
                rfq.mr_task_no, 
                (
                    select 
                    count(distinct i.id) 
                    from 
                    payment_item p 
                    left join invoice i on i.flag = 1 
                    and i.id = p.invoice_id 
                    left join payment py on py.flag >= 0 
                    and py.approved >= 0 
                    and py.id = p.payment_id 
                    where 
                    i.po_id = po.id 
                    and p.flag = 1
                ) as invoice_yang_dikeluarkan, 
                (
                    select 
                    count(distinct py.id) 
                    from 
                    payment_item p 
                    left join invoice i on i.flag = 1 
                    and i.id = p.invoice_id 
                    left join payment py on py.flag >= 0 
                    and py.approved >= 0 
                    and py.id = p.payment_id 
                    where 
                    i.po_id = po.id 
                    and p.flag = 1 
                    and i.is_paid != 1 #payment done = invoice.is_paid = 1
                    ) as invoice_belum_payment, 
                (
                    select 
                    #          sum(
                    #            case when i.payment_pct_fiat != 0 then i.payment_pct_fiat else (
                    #              i.grand_total_price * (i.payment_pct / 100)
                    #            ) end
                    #          ) 
                    FORMAT(
                        SUM(
                        CASE WHEN i.payment_pct_fiat != 0 THEN (
                            i.payment_pct_fiat + i.invoice_charge
                        ) - i.invoice_reduction WHEN i.as_reference = 0 THEN (
                            (
                            i.grand_total_price * (i.payment_pct / 100)
                            ) + i.invoice_charge
                        ) - i.invoice_reduction ELSE i.payment_amount + i.invoice_charge - i.invoice_reduction END
                        ), 
                        2
                    ) #          SUM((i.payment_amount + i.invoice_charge) - i.invoice_reduction)
                    from 
                    invoice i 
                    where 
                    i.id in (
                        select 
                        distinct i.id 
                        from 
                        payment_item p 
                        left join invoice i on i.flag = 1 
                        and i.id = p.invoice_id 
                        left join payment py on py.flag >= 0 
                        and py.approved >= 0 
                        and py.id = p.payment_id 
                        where 
                        i.po_id = po.id 
                        and p.flag = 1 
                        and i.is_paid = 1
                    )
                ) as total_payment, 
                coalesce(
                    (
                    select 
                        sum(i.payment_pct) 
                    from 
                        invoice i 
                    where 
                        i.po_id = po.id 
                        and i.flag > 0 
                        and i.is_paid = 1
                    ), 
                    0
                ) as total_payment_percentage, 
                pb.*, 
                FORMAT(pb.cny, 2) as f_cny, 
                FORMAT(pb.idr, 2) as f_idr, 
                format(pb.eur, 2) as f_eur, 
                format(pb.usd, 2) as f_usd, 
                format(pb.total_in_idr, 2) as f_total_in_idr 
                FROM 
                purchase_order po 
                LEFT JOIN invoice i on i.po_id = po.id 
                LEFT JOIN rfq_header rfq on rfq.id = po.rfq_id 
                LEFT JOIN ragic r ON (
                    r.item_name REGEXP CONCAT(
                    '\\b', 
                    REPLACE(
                        rfq.rfq_no, 
                        rfq.rfq_no, 
                        substring(
                        rfq.rfq_no 
                        from 
                            5
                        )
                    ), 
                    '\\b'
                    )
                ) 
                OR (r.order_id = rfq.reference_no) 
                LEFT JOIN m_project mp on (mp.id = rfq.project_id) 
                or (mp.project_no = rfq.mr_task_no) 
                left join pr pr on (pr.id = po.pr_id) 
                or (pr.pr_no = po.pr_no) 
                LEFT JOIN po_budget pb on pb.po_id = po.id 
                LEFT JOIN m_supplier ms on ms.id = po.supplier_id 
                WHERE 
                po.flag = 1 
                group by 
                po.po_no 
                order by 
                po.created_date desc
            ) s 
            where 
            s.PO_status in(
                'Final Approve', 'New', 'Asking Approval 1', 
                'Asking Approval 2'
            ) 
            group by 
            s.project_no #     WHERE s.total_payment_percentage < 100
            #     and s.PO_grandTotal > s.total_payment

            ";
        $query = $db->query("select * from
		($q)s $where $order " . ($limit == -1 ? '' : "limit $offset, $limit"));

        if (isset($json->debug))
            if ($json->debug == '8080')
                return [true, $q, 0];
        if (!$query)
            return [false, $this->db->error(), 0];

        $totalQuery = $db->query("select count(*) as total from ($q)s $where");
        if (!$totalQuery)
            return [false, $this->db->error()];
        $totalQuery = $totalQuery->getResult();
        return [true, $query->getResult(), $totalQuery[0]->total];
    }

    function getData($json)
    {
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
        $q = "
        SELECT s.*,CONCAT(FORMAT(s.total_in_idr, 0, 'de_DE')) AS rupiah,
        FORMAT(
            ROUND(
            s.total_in_idr * s.total_payment_percentage / 100
            ), 
            0, 
            'de_DE'
        ) AS sudah_dibayar, 
        FORMAT(
            s.total_in_idr - (ROUND(s.total_in_idr * s.total_payment_percentage / 100)),
            0, 'de_DE'
        ) AS remaining_payment
        FROM 
            (
                    /*Query Monitoring PO - Payment*/

                SELECT 
                po.po_no as pono, 
                        po.year,
                        ms.name,
                po.title, 
                case
                        when po.approved = '0' then 'New' 
                        when po.approved = '1' then 'Asking Approval 1'
                        when po.approved = '2' then 'Asking Approval 2'
                        when po.approved = '3' then 'Final Approve'
                        when po.approved = '4' then 'Clarification'
                        when po.approved = '5' then 'Asking For Draft PO'
                        when po.approved = '6' then 'Approval 2 Approved Draft'
                        when po.approved = '-1' then 'Rejected'
                        when po.approved = '-2' then 'Asking For Canceled 1'
                        when po.approved = '-3' then 'Asking For Canceled 2'
                        when po.approved = '-4' then 'Canceled'
                end as PO_Status,
                pr.pr_no, 
                #i.grand_total_price as PO_grandTotal, 
                rfq.reference_no, 
                mp.project_name, 
                mp.project_no, 
                rfq.mr_task_no, 
                (
                    select 
                    count(distinct i.id) 
                    from 
                    payment_item p 
                    left join invoice i on i.flag = 1 
                    and i.id = p.invoice_id 
                    left join payment py on py.flag >= 0 
                    and py.approved >= 0 
                    and py.id = p.payment_id 
                    where 
                    i.po_id = po.id 
                    and p.flag = 1
                ) as invoice_yang_dikeluarkan, 
                (
                    select 
                    count(distinct py.id) 
                    from 
                    payment_item p 
                    left join invoice i on i.flag = 1 
                    and i.id = p.invoice_id 
                    left join payment py on py.flag >= 0 
                    and py.approved >= 0 
                    and py.id = p.payment_id 
                    where 
                    i.po_id = po.id 
                    and p.flag = 1 
                    and i.is_paid != 1 #payment done = invoice.is_paid = 1
                    ) as invoice_belum_payment, 
                (
                    select 
                    #          sum(
                    #            case when i.payment_pct_fiat != 0 then i.payment_pct_fiat else (
                    #              i.grand_total_price * (i.payment_pct / 100)
                    #            ) end
                    #          ) 
                    SUM(
                        CASE WHEN i.payment_pct_fiat != 0 THEN (
                        i.payment_pct_fiat + i.invoice_charge
                        ) - i.invoice_reduction WHEN i.as_reference = 0 THEN (
                        (
                            i.grand_total_price * (i.payment_pct / 100)
                        ) + i.invoice_charge
                        ) - i.invoice_reduction ELSE i.payment_amount + i.invoice_charge - i.invoice_reduction END
                    ) #          SUM((i.payment_amount + i.invoice_charge) - i.invoice_reduction)
                    from 
                    invoice i 
                    where 
                    i.id in (
                        select 
                        distinct i.id 
                        from 
                        payment_item p 
                        left join invoice i on i.flag = 1 
                        and i.id = p.invoice_id 
                        left join payment py on py.flag >= 0 
                        and py.approved >= 0 
                        and py.id = p.payment_id 
                        where 
                        i.po_id = po.id 
                        and p.flag = 1 
                        and i.is_paid = 1
                    )
                ) as total_payment, 
                coalesce(
                    (
                    select 
                        sum(i.payment_pct) 
                    from 
                        invoice i 
                    where 
                        i.po_id = po.id 
                        and i.flag > 0 
                        and i.is_paid = 1
                    ), 
                    0
                ) as total_payment_percentage,
                pb.*
                FROM 
                purchase_order po 
                LEFT JOIN invoice i on i.po_id = po.id 
                LEFT JOIN rfq_header rfq on rfq.id = po.rfq_id 
                LEFT JOIN ragic r ON (
                    r.item_name REGEXP CONCAT(
                    '\\b', 
                    REPLACE(
                        rfq.rfq_no, 
                        rfq.rfq_no, 
                        substring(
                        rfq.rfq_no 
                        from 
                            5
                        )
                    ), 
                    '\\b'
                    )
                ) 
                OR (r.order_id = rfq.reference_no) 
                LEFT JOIN m_project mp 
                            on (mp.id = rfq.project_id)
                            or (mp.project_no = rfq.mr_task_no)
                left join pr pr on (pr.id = po.pr_id) 
                or (pr.pr_no = po.pr_no) 
                LEFT JOIN po_budget pb on pb.po_id = po.id
                        LEFT JOIN m_supplier ms on ms.id = po.supplier_id
                WHERE po.flag = 1


                group by po.po_no
                order by 
                po.created_date desc
            ) s
            ";
        $query = $db->query("select * from
		($q)s $where $order " . ($limit == -1 ? '' : "limit $offset, $limit"));

        if (isset($json->debug))
            if ($json->debug == '8080')
                return [true, $q, 0];
        if (!$query)
            return [false, $this->db->error(), 0];

        $totalQuery = $db->query("select count(*) as total from ($q)s $where");
        if (!$totalQuery)
            return [false, $this->db->error()];
        $totalQuery = $totalQuery->getResult();
        return [true, $query->getResult(), $totalQuery[0]->total];
    }

    function getDataPoNoInvoice($json)
    {
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
        $q = "
        SELECT
			s.pono,
            s.title,
            s.project_no, 
            s.project_name,
            s.PO_status,
            format(s.total_in_idr,0) as total_in_idr
            #   format(
            #     sum(s.total_in_idr), 
            #     2
            #   ) as idr 
            FROM 
            (
                
                /*Query Monitoring PO - Payment*/
                SELECT 
                po.po_no as pono, 
                po.year, 
                ms.name, 
                po.title, 
                case when po.approved = '0' then 'New' when po.approved = '1' then 'Asking Approval 1' when po.approved = '2' then 'Asking Approval 2' when po.approved = '3' then 'Final Approve' when po.approved = '4' then 'Clarification' when po.approved = '5' then 'Asking For Draft PO' when po.approved = '6' then 'Approval 2 Approved Draft' when po.approved = '-1' then 'Rejected' when po.approved = '-2' then 'Asking For Canceled 1' when po.approved = '-3' then 'Asking For Canceled 2' when po.approved = '-4' then 'Canceled' end as PO_Status, 
                pr.pr_no, 
                #i.grand_total_price as PO_grandTotal, 
                rfq.reference_no, 
                mp.project_name, 
                mp.project_no, 
                rfq.mr_task_no, 
                (
                    select 
                    count(distinct i.id) 
                    from 
                    payment_item p 
                    left join invoice i on i.flag = 1 
                    and i.id = p.invoice_id 
                    left join payment py on py.flag >= 0 
                    and py.approved >= 0 
                    and py.id = p.payment_id 
                    where 
                    i.po_id = po.id 
                    and p.flag = 1
                ) as invoice_yang_dikeluarkan, 
                (
                    select 
                    count(distinct py.id) 
                    from 
                    payment_item p 
                    left join invoice i on i.flag = 1 
                    and i.id = p.invoice_id 
                    left join payment py on py.flag >= 0 
                    and py.approved >= 0 
                    and py.id = p.payment_id 
                    where 
                    i.po_id = po.id 
                    and p.flag = 1 
                    and i.is_paid != 1 #payment done = invoice.is_paid = 1
                    ) as invoice_belum_payment, 
                (
                    select 
                    #          sum(
                    #            case when i.payment_pct_fiat != 0 then i.payment_pct_fiat else (
                    #              i.grand_total_price * (i.payment_pct / 100)
                    #            ) end
                    #          ) 
                    FORMAT(
                        SUM(
                        CASE WHEN i.payment_pct_fiat != 0 THEN (
                            i.payment_pct_fiat + i.invoice_charge
                        ) - i.invoice_reduction WHEN i.as_reference = 0 THEN (
                            (
                            i.grand_total_price * (i.payment_pct / 100)
                            ) + i.invoice_charge
                        ) - i.invoice_reduction ELSE i.payment_amount + i.invoice_charge - i.invoice_reduction END
                        ), 
                        2
                    ) #          SUM((i.payment_amount + i.invoice_charge) - i.invoice_reduction)
                    from 
                    invoice i 
                    where 
                    i.id in (
                        select 
                        distinct i.id 
                        from 
                        payment_item p 
                        left join invoice i on i.flag = 1 
                        and i.id = p.invoice_id 
                        left join payment py on py.flag >= 0 
                        and py.approved >= 0 
                        and py.id = p.payment_id 
                        where 
                        i.po_id = po.id 
                        and p.flag = 1 
                        and i.is_paid = 1
                    )
                ) as total_payment, 
                coalesce(
                    (
                    select 
                        sum(i.payment_pct) 
                    from 
                        invoice i 
                    where 
                        i.po_id = po.id 
                        and i.flag > 0 
                        and i.is_paid = 1
                    ), 
                    0
                ) as total_payment_percentage, 
                pb.*, 
                FORMAT(pb.cny, 2) as f_cny, 
                FORMAT(pb.idr, 2) as f_idr, 
                format(pb.eur, 2) as f_eur, 
                format(pb.usd, 2) as f_usd, 
                format(pb.total_in_idr, 2) as f_total_in_idr 
                FROM 
                purchase_order po 
                LEFT JOIN invoice i on i.po_id = po.id 
                LEFT JOIN rfq_header rfq on rfq.id = po.rfq_id 
                LEFT JOIN ragic r ON (
                    r.item_name REGEXP CONCAT(
                    '\\b', 
                    REPLACE(
                        rfq.rfq_no, 
                        rfq.rfq_no, 
                        substring(
                        rfq.rfq_no 
                        from 
                            5
                        )
                    ), 
                    '\\b'
                    )
                ) 
                OR (r.order_id = rfq.reference_no) 
                LEFT JOIN m_project mp on (mp.id = rfq.project_id) 
                or (mp.project_no = rfq.mr_task_no) 
                left join pr pr on (pr.id = po.pr_id) 
                or (pr.pr_no = po.pr_no) 
                LEFT JOIN po_budget pb on pb.po_id = po.id 
                LEFT JOIN m_supplier ms on ms.id = po.supplier_id 
                WHERE 
                po.flag = 1 
                group by 
                po.po_no 
                order by 
                po.created_date desc
            ) s 
            where s.invoice_yang_dikeluarkan = 0
            and NOT s.PO_status = 'Rejected'
            ";
        $query = $db->query("select * from
		($q)s $where $order " . ($limit == -1 ? '' : "limit $offset, $limit"));

        if (isset($json->debug))
            if ($json->debug == '8080')
                return [true, $q, 0];
        if (!$query)
            return [false, $this->db->error(), 0];

        $totalQuery = $db->query("select count(*) as total from ($q)s $where");
        if (!$totalQuery)
            return [false, $this->db->error()];
        $totalQuery = $totalQuery->getResult();
        return [true, $query->getResult(), $totalQuery[0]->total];
    }
    function getDataNoInvoice($json)
    {
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
        $q = "
            SELECT

            s.project_no, 
            s.project_name,
            format(sum(s.total_in_idr),0) as total_in_idr
            #   format(
            #     sum(s.total_in_idr), 
            #     2
            #   ) as idr 
            FROM 
            (
                
                /*Query Monitoring PO - Payment*/
                SELECT 
                po.po_no as pono, 
                po.year, 
                ms.name, 
                po.title, 
                case when po.approved = '0' then 'New' when po.approved = '1' then 'Asking Approval 1' when po.approved = '2' then 'Asking Approval 2' when po.approved = '3' then 'Final Approve' when po.approved = '4' then 'Clarification' when po.approved = '5' then 'Asking For Draft PO' when po.approved = '6' then 'Approval 2 Approved Draft' when po.approved = '-1' then 'Rejected' when po.approved = '-2' then 'Asking For Canceled 1' when po.approved = '-3' then 'Asking For Canceled 2' when po.approved = '-4' then 'Canceled' end as PO_Status, 
                pr.pr_no, 
                #i.grand_total_price as PO_grandTotal, 
                rfq.reference_no, 
                mp.project_name, 
                mp.project_no, 
                rfq.mr_task_no, 
                (
                    select 
                    count(distinct i.id) 
                    from 
                    payment_item p 
                    left join invoice i on i.flag = 1 
                    and i.id = p.invoice_id 
                    left join payment py on py.flag >= 0 
                    and py.approved >= 0 
                    and py.id = p.payment_id 
                    where 
                    i.po_id = po.id 
                    and p.flag = 1
                ) as invoice_yang_dikeluarkan, 
                (
                    select 
                    count(distinct py.id) 
                    from 
                    payment_item p 
                    left join invoice i on i.flag = 1 
                    and i.id = p.invoice_id 
                    left join payment py on py.flag >= 0 
                    and py.approved >= 0 
                    and py.id = p.payment_id 
                    where 
                    i.po_id = po.id 
                    and p.flag = 1 
                    and i.is_paid != 1 #payment done = invoice.is_paid = 1
                    ) as invoice_belum_payment, 
                (
                    select 
                    #          sum(
                    #            case when i.payment_pct_fiat != 0 then i.payment_pct_fiat else (
                    #              i.grand_total_price * (i.payment_pct / 100)
                    #            ) end
                    #          ) 
                    FORMAT(
                        SUM(
                        CASE WHEN i.payment_pct_fiat != 0 THEN (
                            i.payment_pct_fiat + i.invoice_charge
                        ) - i.invoice_reduction WHEN i.as_reference = 0 THEN (
                            (
                            i.grand_total_price * (i.payment_pct / 100)
                            ) + i.invoice_charge
                        ) - i.invoice_reduction ELSE i.payment_amount + i.invoice_charge - i.invoice_reduction END
                        ), 
                        2
                    ) #          SUM((i.payment_amount + i.invoice_charge) - i.invoice_reduction)
                    from 
                    invoice i 
                    where 
                    i.id in (
                        select 
                        distinct i.id 
                        from 
                        payment_item p 
                        left join invoice i on i.flag = 1 
                        and i.id = p.invoice_id 
                        left join payment py on py.flag >= 0 
                        and py.approved >= 0 
                        and py.id = p.payment_id 
                        where 
                        i.po_id = po.id 
                        and p.flag = 1 
                        and i.is_paid = 1
                    )
                ) as total_payment, 
                coalesce(
                    (
                    select 
                        sum(i.payment_pct) 
                    from 
                        invoice i 
                    where 
                        i.po_id = po.id 
                        and i.flag > 0 
                        and i.is_paid = 1
                    ), 
                    0
                ) as total_payment_percentage, 
                pb.*, 
                FORMAT(pb.cny, 2) as f_cny, 
                FORMAT(pb.idr, 2) as f_idr, 
                format(pb.eur, 2) as f_eur, 
                format(pb.usd, 2) as f_usd, 
                format(pb.total_in_idr, 2) as f_total_in_idr 
                FROM 
                purchase_order po 
                LEFT JOIN invoice i on i.po_id = po.id 
                LEFT JOIN rfq_header rfq on rfq.id = po.rfq_id 
                LEFT JOIN ragic r ON (
                    r.item_name REGEXP CONCAT(
                    '\\b', 
                    REPLACE(
                        rfq.rfq_no, 
                        rfq.rfq_no, 
                        substring(
                        rfq.rfq_no 
                        from 
                            5
                        )
                    ), 
                    '\\b'
                    )
                ) 
                OR (r.order_id = rfq.reference_no) 
                LEFT JOIN m_project mp on (mp.id = rfq.project_id) 
                or (mp.project_no = rfq.mr_task_no) 
                left join pr pr on (pr.id = po.pr_id) 
                or (pr.pr_no = po.pr_no) 
                LEFT JOIN po_budget pb on pb.po_id = po.id 
                LEFT JOIN m_supplier ms on ms.id = po.supplier_id 
                WHERE 
                po.flag = 1 
                group by 
                po.po_no 
                order by 
                po.created_date desc
            ) s 
            where s.invoice_yang_dikeluarkan = 0
            and NOT s.PO_status = 'Rejected'
            group by s.project_no
            ";
        $query = $db->query("select * from
		($q)s $where $order " . ($limit == -1 ? '' : "limit $offset, $limit"));

        if (isset($json->debug))
            if ($json->debug == '8080')
                return [true, $q, 0];
        if (!$query)
            return [false, $this->db->error(), 0];

        $totalQuery = $db->query("select count(*) as total from ($q)s $where");
        if (!$totalQuery)
            return [false, $this->db->error()];
        $totalQuery = $totalQuery->getResult();
        return [true, $query->getResult(), $totalQuery[0]->total];
    }
    function getDataPaid($json)
    {
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
        $q = "
        select 
        s.project_no, 
        s.project_name,
        FORMAT(SUM(CASE WHEN s.is_paid = 1 THEN COALESCE(s.total_idr, 0) ELSE 0 END), 0) AS paid_in_idr,
        FORMAT(SUM(CASE WHEN s.is_paid = 0 THEN COALESCE(s.total_idr, 0) ELSE 0 END), 0) AS remaining_idr
        from 
        (
            SELECT 
            po.po_no, 
            p.payment_no, 
            ms.name as supplier,
            i.invoice_no, 
            rfq.mr_task_no, 
            mp.project_no, 
            mp.project_name, 
            i.is_paid, 
            pp.inrp as total_idr, 
            CASE WHEN i.payment_pct_fiat != 0 THEN (
                i.payment_pct_fiat + i.invoice_charge
            ) - i.invoice_reduction WHEN i.as_reference = 0 THEN (
                (
                i.grand_total_price * (i.payment_pct / 100)
                ) + i.invoice_charge
            ) - i.invoice_reduction ELSE i.payment_amount + i.invoice_charge - i.invoice_reduction END AS invoice_total_price, 
            COALESCE(
                po.exchange_rate, i.exchange_rate
            ) AS exchange_rate, 
            pi.payment_id, 
            COALESCE(po.currency, ms.currency) AS currency 
            FROM 
            payment_item pi 
            LEFT JOIN invoice i ON i.id = pi.invoice_id 
            LEFT JOIN purchase_order po ON po.id = i.po_id 
            left join pr pr on (pr.id = po.pr_id) 
            or (pr.pr_no = po.pr_no) 
            left join rfq_header rfq on (po.rfq_id = rfq.id) 
            LEFT JOIN m_project mp on (mp.id = rfq.project_id) 
            or (mp.project_no = rfq.mr_task_no) 
            LEFT JOIN m_supplier ms ON ms.id = i.supplier_id 
            LEFT JOIN payment p on p.id = pi.payment_id 
            left join popayment pp on pp.invoice_id = pi.invoice_id 
            WHERE 
            pi.flag = 1 
            AND i.flag = 1 
            #and mp.project_no = '01123-BI-PT'
        ) s 
        group by 
        s.project_name
            ";
        $query = $db->query("select * from
		($q)s $where $order " . ($limit == -1 ? '' : "limit $offset, $limit"));

        if (isset($json->debug))
            if ($json->debug == '8080')
                return [true, $q, 0];
        if (!$query)
            return [false, $this->db->error(), 0];

        $totalQuery = $db->query("select count(*) as total from ($q)s $where");
        if (!$totalQuery)
            return [false, $this->db->error()];
        $totalQuery = $totalQuery->getResult();
        return [true, $query->getResult(), $totalQuery[0]->total];
    }
    public function monitoringPayment($json)
    {
        helper(['Query_helper']);
        $mode = $json->mode ?? 0;
        $customGroup = $json->customGroup ?? 0;
        $limit = $json->limit ?? 10;
        $offset = $json->offset ?? 0;
        $sortBy = $json->sortBy ?? array();
        $sortDesc = $json->sortDesc ?? array();
        $order = order($sortBy, $sortDesc);
        $db = $this->db;
        $where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);

        //return print_r($json);
        /* if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; */

        //$customed = ($customGroup === '0') ? 'po.po_no' : 's.name';

        $q2 = "
        select 
        po.po_no, 
        case when po.approved = '0' then 'New' when po.approved = '1' then 'Asking Approval 1' when po.approved = '2' then 'Asking Approval 2' when po.approved = '3' then 'Final Approve' when po.approved = '4' then 'Clarification' when po.approved = '5' then 'Asking For Draft PO' when po.approved = '6' then 'Approval 2 Approved Draft' when po.approved = '-1' then 'Rejected' when po.approved = '-2' then 'Asking For Canceled 1' when po.approved = '-3' then 'Asking For Canceled 2' when po.approved = '-4' then 'Canceled' end as PO_Status, 
        po.currency, 
        po.exchange_rate, 
        s.name, 
        po.year, 
        sum(pp.payment_pct_fiat) as payment_pct_fiat, 
        SUM(
            COALESCE(s.grand_total_price, 0) + COALESCE(po.charge1, 0) + COALESCE(po.charge2, 0) + COALESCE(po.other_charge, 0) - COALESCE(po.discount, 0)
        ) AS po_grandTotal, 
        coalesce(
            sum(pp.invoice_total_price), 
            0
        ) as i_grandTotal, 
        sum(pp.total_price) as total_price, 
        sum(pp.invoice_charge) as invoice_charge, 
        sum(pp.invoice_reduction) as invoice_reduction, 
        pp.invoice_no, 
        pp.payment_no, 
        i.is_paid 
        from 
        purchase_order po 
        left join popayment pp on po.id = pp.po_id 
        left join (
            select 
            sum(order_qty * price_per_item) as grand_total_price, 
            purchase_order_id 
            from 
            purchase_order_item 
            where 
            flag = 1 
            group by 
            purchase_order_id
        ) s on s.purchase_order_id = po.id 
        left join invoice i on i.id = pp.invoice_id 
        left join m_supplier s on s.id = po.supplier_id 
        where 
        po.flag = 1 
        AND po.year NOT IN ('22', '23', '2023', '2022') 
        group by 
        po.po_no
        ";
        $q = "
        select 
        po.po_no, 
        case when po.approved = '0' then 'New' when po.approved = '1' then 'Asking Approval 1' when po.approved = '2' then 'Asking Approval 2' when po.approved = '3' then 'Final Approve' when po.approved = '4' then 'Clarification' when po.approved = '5' then 'Asking For Draft PO' when po.approved = '6' then 'Approval 2 Approved Draft' when po.approved = '-1' then 'Rejected' when po.approved = '-2' then 'Asking For Canceled 1' when po.approved = '-3' then 'Asking For Canceled 2' when po.approved = '-4' then 'Canceled' end as PO_Status, 
        po.currency, 
        po.exchange_rate,
        s.name,
        po.year,
        pp.payment_pct_fiat,
        coalesce(s.grand_total_price,0) + coalesce(po.charge1,0) + coalesce(po.charge2,0) + coalesce(po.other_charge,0) - coalesce(po.discount,0) as po_grandTotal, 
        coalesce(pp.invoice_total_price,0) as i_grandTotal, 
        pp.total_price,
        pp.invoice_charge,
        pp.invoice_reduction,
        pp.invoice_no, 
        pp.payment_no, 
        i.is_paid 
        from 
        purchase_order po 
        left join popayment pp on po.id = pp.po_id 
        left join (
            select 
            sum(order_qty * price_per_item) as grand_total_price, 
            purchase_order_id 
            from 
            purchase_order_item 
            where 
            flag = 1 
            group by 
            purchase_order_id
        ) s on s.purchase_order_id = po.id 
        left join invoice i on i.id = pp.invoice_id 
        left join m_supplier s on s.id = po.supplier_id
        where po.flag = 1 
        AND po.year not in('22', '23', '2023', '2022')
            ";

        $finalQuery = ($mode === '1') ? $q2 : $q;

        //return (print_r($finalQuery));
        $query = $db->query("select * from
		($finalQuery)s $where $order " . ($limit == -1 ? '' : "limit $offset, $limit"));

        if (isset($json->debug))
            if ($json->debug == '8080')
                return [true, $q, 0];
        if (!$query)
            return [false, $this->db->error(), 0];

        $totalQuery = $db->query("select count(*) as total from ($finalQuery)s $where");
        if (!$totalQuery)
            return [false, $this->db->error()];
        $totalQuery = $totalQuery->getResult();
        return [true, $query->getResult(), $totalQuery[0]->total];
    }
    function poWaitingApproval($json)
    {
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
        $q = "
            SELECT 
            -- po.po_no, 
            -- pp.invoice_no,
            -- pp.payment_no,
            -- po.id as poid,
            -- po.year, 
            -- case
            -- when po.approved = '0' then 'New' 
            -- when po.approved = '1' then 'Asking Approval 1'
            -- when po.approved = '2' then 'Asking Approval 2'
            -- when po.approved = '3' then 'Final Approve'
            -- when po.approved = '4' then 'Clarification'
            -- when po.approved = '5' then 'Asking For Draft PO'
            -- when po.approved = '6' then 'Approval 2 Approved Draft'
            -- when po.approved = '-1' then 'Rejected'
            -- when po.approved = '-2' then 'Asking For Canceled 1'
            -- when po.approved = '-3' then 'Asking For Canceled 2'
            -- when po.approved = '-4' then 'Canceled'
            -- end as po_status, 
            -- mi.item_no, 
            -- po.currency, 
            -- poi.order_qty, 
            -- poi.allocation, 
            -- po.exchange_rate, 
            -- poi.price_per_item, 
            sum(poi.price_per_item * poi.order_qty)as total_price,
            sum(poi.price_per_item * COALESCE(NULLIF(po.exchange_rate, 0), 1) * poi.order_qty) as total_idr,
            ms.name as supplier, 
            mp.project_no, 
            mp.project_name 
            FROM 
            purchase_order po 
            left join purchase_order_item poi on po.id = poi.purchase_order_id 
            left join m_supplier ms on po.supplier_id = ms.id 
            left join m_item mi on mi.id = poi.item_id 
            left join pr_subledger ps on poi.subledger_id = ps.id 
            left join m_project mp on mp.id = ps.project_id 
            left join popayment pp on pp.po_id = po.id
            where 
            po.flag = 1 
            and poi.flag = 1 
            -- and po.po_no = '0473/LOG-PO/08/2025'
            and pp.invoice_no is null 
            and po.approved in ('0','1','2')
            group by ms.name, mp.project_no, mp.project_name
  
            ";
        $query = $db->query("select * from
		($q)s $where $order " . ($limit == -1 ? '' : "limit $offset, $limit"));

        if (isset($json->debug))
            if ($json->debug == '8080')
                return [true, $q, 0];
        if (!$query)
            return [false, $this->db->error(), 0];

        $totalQuery = $db->query("select count(*) as total from ($q)s $where");
        if (!$totalQuery)
            return [false, $this->db->error()];
        $totalQuery = $totalQuery->getResult();
        return [true, $query->getResult(), $totalQuery[0]->total];
    }
    function poApprovedOutstanding($json)
    {
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
        $q = "
            SELECT 
            s.invoice_total_price,
            s.total_price,
            GROUP_CONCAT(po.po_no) as po_list,
            SUM(poi.price_per_item * poi.order_qty) as p_total_price,
            SUM(poi.price_per_item * COALESCE(NULLIF(po.exchange_rate, 0), 1) * poi.order_qty) as p_total_idr,
            SUM(poi.price_per_item * COALESCE(NULLIF(po.exchange_rate, 0), 1) * poi.order_qty) / s.total_price as p_share,
            LEAST(
                (SUM(poi.price_per_item * COALESCE(NULLIF(po.exchange_rate, 0), 1) * poi.order_qty) / s.total_price) * s.invoice_total_price,
                SUM(poi.price_per_item * COALESCE(NULLIF(po.exchange_rate, 0), 1) * poi.order_qty)
            ) as paid_allocated_idr,
            GREATEST(
                (SUM(poi.price_per_item * COALESCE(NULLIF(po.exchange_rate, 0), 1) * poi.order_qty)) 
                - ((SUM(poi.price_per_item * COALESCE(NULLIF(po.exchange_rate, 0), 1) * poi.order_qty) / s.total_price) * s.invoice_total_price),
                0
            ) as remaining_idr,
            ms.name as supplier, 
            mp.project_no, 
            mp.project_name 
            FROM purchase_order po 
            LEFT JOIN purchase_order_item poi ON po.id = poi.purchase_order_id 
            LEFT JOIN m_supplier ms ON po.supplier_id = ms.id 
            LEFT JOIN m_item mi ON mi.id = poi.item_id 
            LEFT JOIN pr_subledger ps ON poi.subledger_id = ps.id 
            LEFT JOIN m_project mp ON mp.id = ps.project_id 
            LEFT JOIN (
                SELECT 
                pp.po_id,
                pp.total_price as total_price,
                SUM(pp.invoice_total_price) as invoice_total_price
                FROM popayment pp 
                LEFT JOIN invoice i ON i.id = pp.invoice_id
                WHERE i.is_paid = 1
                GROUP BY pp.po_id
            ) s ON po.id = s.po_id
            WHERE po.flag = 1 
            AND poi.flag = 1 
            -- AND s.invoice_total_price IS NOT NULL
            -- and po.po_no = '0473/LOG-PO/08/2025'
            AND po.approved IN ('3')
            AND po.year NOT IN ('22','22','2022','2023')
            GROUP BY ms.name, mp.project_no, mp.project_name
            ";
        $query = $db->query("select * from
		($q)s $where $order " . ($limit == -1 ? '' : "limit $offset, $limit"));

        if (isset($json->debug))
            if ($json->debug == '8080')
                return [true, $q, 0];
        if (!$query)
            return [false, $this->db->error(), 0];

        $totalQuery = $db->query("select count(*) as total from ($q)s $where");
        if (!$totalQuery)
            return [false, $this->db->error()];
        $totalQuery = $totalQuery->getResult();
        return [true, $query->getResult(), $totalQuery[0]->total];
    }

    function poMonintoringProject($json)
    {
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
        $q = "
            SELECT 
            po.po_no, 
            po.approved, 
            poi.allocation,
            pmd.po_grand_total, 
            pmd.invoice_total, 
            pmd.charge, 
            pmd.reduction, 
            pmd.invoice_grand_total, 
            pmd.payment_percentage, 
            pmd.payment_status, 
            po.currency, 
            po.exchange_rate, 
            SUM(
                poi.price_per_item * poi.order_qty
            ) AS total_price, 
            SUM(
                poi.price_per_item * COALESCE(
                NULLIF(po.exchange_rate, 0), 
                1
                ) * poi.order_qty
            ) AS total_idr, 
            ms.name AS supplier, 
            SUM(
                (
                poi.price_per_item * poi.order_qty
                ) * (pmd.payment_percentage / 100)
            ) AS paid_amount, 
            -- sisa pembayaran
            SUM(
                poi.price_per_item * poi.order_qty
            ) - SUM(
                (
                poi.price_per_item * poi.order_qty
                ) * (pmd.payment_percentage / 100)
            ) AS remaining, 
            -- project_no: pertahankan per-baris subledger; fallback invoice hanya untuk baris bertipe Project
            CASE
                WHEN (
                    COALESCE(NULLIF(TRIM(ps.project_type), ''), '') = 'Project'
                    OR (
                        NULLIF(CAST(ps.project_id AS CHAR), '') IS NOT NULL
                        AND CAST(ps.project_id AS CHAR) <> '0'
                    )
                )
                THEN COALESCE(
                    mp.project_no,
                    (
                        SELECT MAX(mp3.project_no)
                        FROM invoice i3
                        JOIN m_project mp3 ON mp3.id = i3.project_id
                        WHERE i3.po_id = po.id
                        AND i3.flag = 1
                        AND mp3.project_no IS NOT NULL
                    )
                )
                ELSE mp.project_no
            END AS project_no,
            
            (
                SELECT 
                CASE WHEN mi.item_type != 'Jasa' THEN 'Material' ELSE mi.item_type END 
                FROM 
                m_item mi 
                WHERE 
                poi.item_id = mi.id
            ) AS item_type, 
            
            CASE
                WHEN (
                    COALESCE(NULLIF(TRIM(ps.project_type), ''), '') = 'Project'
                    OR (
                        NULLIF(CAST(ps.project_id AS CHAR), '') IS NOT NULL
                        AND CAST(ps.project_id AS CHAR) <> '0'
                    )
                )
                THEN COALESCE(
                    mp.project_name,
                    (
                        SELECT MAX(mp3.project_name)
                        FROM invoice i3
                        JOIN m_project mp3 ON mp3.id = i3.project_id
                        WHERE i3.po_id = po.id
                        AND i3.flag = 1
                        AND mp3.project_name IS NOT NULL
                    )
                )
                ELSE mp.project_name
            END AS project_name,
            
            -- project_type per baris (hindari ketimpa MAX lintas PO)
            COALESCE(
                NULLIF(TRIM(ps.project_type), ''),
                CASE
                    WHEN ps.rnd_id IS NOT NULL AND ps.rnd_id <> 0 THEN 'R&D'
                    WHEN ps.dept_id IS NOT NULL AND ps.dept_id <> 0 THEN 'Operational'
                    WHEN NULLIF(CAST(ps.project_id AS CHAR), '') IS NOT NULL AND CAST(ps.project_id AS CHAR) <> '0' THEN 'Project'
                    ELSE 'Persediaan'
                END
            ) AS project_type,
            
            -- gunakan data relasi pada baris subledger saat ini
            rnd.name AS rnd_name,
            md.dept_code AS dept_code,
            md.dept_name AS dept_name,
            top.name AS type_operational_name,
            tsop.name AS type_sub_operational_name
        
        
            
            FROM 
            purchase_order po 
            LEFT JOIN purchase_order_item poi ON po.id = poi.purchase_order_id 
            LEFT JOIN m_supplier ms ON po.supplier_id = ms.id 
            LEFT JOIN m_item mi ON mi.id = poi.item_id 
            LEFT JOIN pr_subledger ps ON poi.subledger_id = ps.id AND ps.flag = 1
            LEFT JOIN m_project mp ON mp.id = ps.project_id 
            LEFT JOIN research_and_developments rnd ON rnd.id = ps.rnd_id
            LEFT JOIN m_department md ON md.id = ps.dept_id
            LEFT JOIN type_operationals top ON top.id = ps.type_operational_id
            LEFT JOIN type_sub_operationals tsop ON tsop.id = ps.sub_type_operational_id
            LEFT JOIN po_monitoring_data pmd ON pmd.po_number = po.po_no 
            WHERE 
            po.flag = 1 
            AND poi.flag = 1 
            and po.approved = 3 -- AND po.po_no = '0600/EL-PO/09/2025'
            AND po.year NOT IN ('22', '23', '2023') -- AND mp.project_no = '02125-NJ-RT'
            GROUP BY 
            po.po_no, 
            pmd.po_grand_total, 
            pmd.invoice_total, 
            pmd.charge, 
            pmd.reduction, 
            pmd.invoice_grand_total, 
            pmd.payment_percentage, 
            pmd.payment_status, 
            po.currency, 
            po.exchange_rate, 
            ms.name, 
            mp.project_no, 
            mp.project_name,
            ps.project_type,
            ps.project_id,
            ps.rnd_id,
            ps.dept_id,
            rnd.name,
            md.dept_code,    -- Tambahkan ke GROUP BY
            md.dept_name,   -- Tambahkan ke GROUP BY
            top.name,
            tsop.name
            union all 
            SELECT 
            i.invoice_no AS po_no, 
            (
                SELECT 
                p.approved 
                FROM 
                payment_item pi 
                LEFT JOIN payment p ON p.id = pi.payment_id 
                WHERE 
                pi.invoice_id = i.id 
                AND pi.flag = 1 
                and p.approved = 4 
                LIMIT 
                1
            ) AS approved, 
            '-' as allocation, 
            i.total_price as po_grand_total, 
            i.total_price as invoice_total, 
            i.invoice_charge as charge, 
            i.invoice_reduction as reduction, 
            (
                i.grand_total_price + i.invoice_charge
            ) - i.invoice_reduction as invoice_grand_total, 
            i.payment_pct as payment_percentage, 
            i.is_paid as payment_status, 
            'IDR' as currency, 
            i.exchange_rate as exchange_rate, 
            i.total_price as total_price, 
            i.grand_total_price as total_idr, 
            (
                select 
                s.name 
                from 
                m_supplier s 
                where 
                i.supplier_id = s.id
            ) as supplier, 
            case when i.is_paid = 1 then i.grand_total_price when i.is_paid = 0 then 0 end as paid_amount, 
            case when i.is_paid = 0 then i.grand_total_price when i.is_paid = 1 then 0 end as remaining, 
            (
                select 
                mp.project_no 
                from 
                m_project mp 
                where 
                i.project_id = mp.id
            ) as project_no, 
            'Jasa' as item_type, 
            (
                select 
                mp.project_name 
                from 
                m_project mp 
                where 
                i.project_id = mp.id
            ) as project_name,
            i.project_type as project_type,
            NULL as rnd_name,      -- Tambahan untuk union
            (
                select md.dept_code
                from m_department md
                where md.id = i.dept_id
            ) as dept_code,
            (
                select md.dept_name
                from m_department md
                where md.id = i.dept_id
            ) as dept_name,
            (
                select top.name
                from type_operationals top
                where top.id = i.type_operational_id
            ) as type_operational_name,
            (
                select tsop.name
                from type_sub_operationals tsop
                where tsop.id = i.sub_type_operational_id
            ) as type_sub_operational_name
            FROM 
            invoice i 
            WHERE 
            -- i.petty_cash = 1 AND 
            i.flag = 1 
            and i.po_id is null

            ";
        $query = $db->query("select * from
		($q)s $where $order " . ($limit == -1 ? '' : "limit $offset, $limit"));

        if (isset($json->debug))
            if ($json->debug == '8080')
                return [true, $q, 0];
        if (!$query)
            return [false, $this->db->error(), 0];

        $totalQuery = $db->query("select count(*) as total from ($q)s $where");
        if (!$totalQuery)
            return [false, $this->db->error()];
        $totalQuery = $totalQuery->getResult();
        return [true, $query->getResult(), $totalQuery[0]->total];
    }

    function poItem($json)
    {
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
        $q = "
            SELECT 
            p.po_no, 
            CASE WHEN p.approved = 0 THEN 'New' WHEN p.approved = 1 THEN 'Asking Approval 1' WHEN p.approved = 2 THEN 'Asking Approval 2' WHEN p.approved = 3 THEN 'Final Approved' WHEN p.approved = 4 THEN 'Clarification' WHEN p.approved = 5 THEN 'Asking for Draft PO' WHEN p.approved = 6 THEN 'Approval 2 Approved Draft' WHEN p.approved = -1 THEN 'Rejected' WHEN p.approved = -2 THEN 'Asking for canceled 1' WHEN p.approved = -3 THEN 'Asking for canceled 2' WHEN p.approved = -4 THEN 'Canceled' ELSE 'Other Status' END as status, 
            s.*, 
            order_qty * price_per_item as total_price_per_item, 
            i.specification
            
            from 
            (
                select 
                s.order_no, 
                s.id, 
                s.flag, 
                s.purchase_order_id, 
                s.item_id, 
                i.item_no, 
                i.item_name, 
                i.unit, 
                i.original_manufacture, 
                i.manufacture_pn, 
                i.article_no, 
                sum(s.order_qty) as order_qty, 
                sum(s.complete_qty) as complete_qty, 
                (
                    select 
                    price_per_item 
                    from 
                    purchase_order_item 
                    where 
                    purchase_order_id = s.purchase_order_id 
                    and item_id = s.item_id 
                    and flag = 1 
                    limit 
                    1
                ) as price_per_item 
                from 
                purchase_order_item s 
                left join m_item i on i.id = s.item_id 
                where 
                i.id is not null 
                and s.flag = 1 
                group by 
                s.item_id, 
                i.original_manufacture, 
                i.manufacture_pn, 
                i.specification, 
                i.unit, 
                i.article_no, 
                s.purchase_order_id
            ) s 
            
            left join m_item i on i.id = s.item_id 
            left join purchase_order p on p.id = s.purchase_order_id 
            left join m_supplier ms on ms.id = p.supplier_id 
            left join rfq_header rfq on rfq.id = p.rfq_id
            LEFT JOIN m_project mp 
                on (mp.id = rfq.project_id)
                or (mp.project_no = rfq.mr_task_no)
                
            order by s.order_no asc


            ";
        $query = $db->query("select * from
		($q)s $where $order " . ($limit == -1 ? '' : "limit $offset, $limit"));

        if (isset($json->debug))
            if ($json->debug == '8080')
                return [true, $q, 0];
        if (!$query)
            return [false, $this->db->error(), 0];

        $totalQuery = $db->query("select count(*) as total from ($q)s $where");
        if (!$totalQuery)
            return [false, $this->db->error()];
        $totalQuery = $totalQuery->getResult();
        return [true, $query->getResult(), $totalQuery[0]->total];
    }
}
