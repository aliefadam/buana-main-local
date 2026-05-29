<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseOrderModel extends Model
{
    protected $table = 'purchase_order';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        "id",
        "po_no",
        "dept_id",
        "po_date",
        "eta_date",
        "received_date",
        "order_type",
        "supplier_id",
        "promised_delivery_date",
        "currency",
        "exchange_rate",
        "rate_date",
        "final_quote_url",
        "signed_po_url",
        "signed_pr_url",
        "no",
        "year",
        "charge1",
        "charge2",
        "charge1_desc",
        "charge2_desc",
        "note",
        "title",
        "brand",
        "jenis_barang",
        'flag',
        'approved',
        'ref_offer_no',
        'ref_internal_bmt',
        'pr_id',
        'rfq_id',
        'askapproval_note',
        'ask_approval_date',
        'approval_date',
        'approval_by',
        'approval_2_date',
        'approval_2_by',
        'rejected_by',
        'rejected_date',
        'modified_date',
        'modified_by',
        "approval_note",
        "created_by",
        "created_date",
        "new_po_no",
        "reject_note1",
        "reject_note2",
        "payment_term",
        "despatch",
        "shipping_address",
        "miscellaneous",
        "discount_desc",
        "other_charge",
        "ppn",
        "pph",
        "discount",
        "complete",
        "ask_canceled_date",
        "ask_canceled_note",
        "ask_canceled_by",
        "canceled_by",
        "canceled_note",
        "canceled_date",
        "canceled_2_by",
        "canceled_2_date",
        "ask_draft_date",
        "ask_draft_note",
        "ask_draft_by",
        "approval_draft_by",
        "approval_draft_date",
        "rev_date",
        "send_po",
        "send_po_by",
        "requested_delivery_date",
        "delivery_time",
        "revision_approval",
        "revision_approval_by",
        "revision_note",
        "ask_revision_approval_note",
        "ask_revision_approval_by",
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
        $q = "select date(s.created_date) as crea_date, date(s.modified_date) as mod_date, date(approval_2_date) as approval2date, (select count(*) from purchase_order_item where flag = 1 and purchase_order_id = s.id) as item_count, concat(s.po_no, ' - ', s.title) as info,
            (select notes from purchase_order_comment where purchase_order_id = s.id order by id desc limit 1) as comment,
            (select created_date from purchase_order_comment where purchase_order_id = s.id order by id desc limit 1) as comment_date,
            (select u.name from purchase_order_comment c left join users u on u.id = c.created_by where purchase_order_id = s.id order by c.id desc limit 1) as comment_creator, 
            coalesce((select sum(payment_pct) from invoice where po_id = s.id and flag = 1), 0) as total_payment_pct, ms.pic_name, ms.address, 
            " . join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)) . ", d.dept_code, d.dept_name, ms.name as supplier_name, u.name as created_by_name, v.name as approved_by_name, w.name as approved2_by_name, x.name as modified_by_name, y.name as rejected_by_name, z.name as canceled_by_name, r.name as canceled_2_by_name, e.name as approval_draft_by_name, f.name as ask_draft_by_name, sp.name as sp_name,
            i.grand_total_price,
            (coalesce(i.grand_total_price, 0) + coalesce(tx.total_ppn_value, 0) - coalesce(tx.total_pph_value, 0)) as total_item_after_tax,
            ms.email, rfq.rfq_no, pr.pr_no, pr.id as prr_id,
            (
                (
                    coalesce(i.grand_total_price, 0)
                    + coalesce(s.charge1, 0)
                    + coalesce(s.charge2, 0)
                    + coalesce(s.other_charge, 0)
                    - coalesce(s.discount, 0)
                )
                + coalesce(tx.total_ppn_value, 0)
                - coalesce(tx.total_pph_value, 0)
            ) as grand_total,
            -- FIELD UNTUK SORTING PRIORITAS
            CASE 
                WHEN s.approved = 2 THEN 1 
                ELSE 0 
            END as sort_priority,
            s.approved as approved_status  -- tambah field approved asli jika perlu
            from `$this->table` s 
            left join m_supplier ms on ms.id = s.supplier_id 
            left join m_department d on d.id = s.dept_id
            left join rfq_header rfq on rfq.id= s.rfq_id
            left join pr pr on pr.id = s.pr_id
            left join users u on u.id = s.created_by
            left join users x on x.id = s.modified_by
            left join users v on v.id = s.approval_by
            left join users w on w.id = s.approval_2_by
            left join users y on y.id = s.rejected_by
            left join users z on z.id = s.canceled_by
            left join users r on r.id = s.canceled_2_by
            left join users f on f.id = s.ask_draft_by
            left join users e on e.id = s.approval_draft_by
            left join users sp on sp.id = s.send_po_by
            left join (
                select sum(order_qty * price_per_item) as grand_total_price, purchase_order_id
                from purchase_order_item where flag = 1
                group by purchase_order_id
            ) i on i.purchase_order_id = s.id
            left join (
                select
                    purchase_order_id,
                    coalesce(sum(ppn_value), 0) as total_ppn_value,
                    coalesce(sum(pph_value), 0) as total_pph_value
                from purchase_order_item
                where flag = 1
                group by purchase_order_id
            ) tx on tx.purchase_order_id = s.id
            ";
        $finalOrder = $order; // default

        if (isset($json->prioritizeApproved2) && $json->prioritizeApproved2) {
            // SELALU prioritaskan approved=2 tanpa cek filter
            $priorityOrder = "ORDER BY sort_priority DESC, crea_date ASC";

            // Jika ada sorting custom dari frontend, tambahkan setelah prioritas
            if (!empty(trim($order))) {
                $customOrder = str_ireplace('order by', '', $order);
                $finalOrder = "$priorityOrder, $customOrder";
            } else {
                $finalOrder = $priorityOrder;
            }
        }

        $query = $db->query("select * from ($q) s $where $finalOrder " . ($limit == -1 ? '' : "limit $offset, $limit"));

        if (isset($json->debug))
            return [$q, 0];
        if (!$query)
            return [$this->db->error(), false];

        $totalQuery = $db->query("select count(*) as total from ($q)s $where");
        $totalQuery = $totalQuery->getResult();
        return [$query->getResult(), $totalQuery[0]->total];
    }

    function readAll($id)
    {
        helper(['Query_helper']);
        $db = db_connect();
        $q = "`$this->table` s left join m_supplier ms on ms.id = s.supplier_id left join m_department d on d.id = s.dept_id where s.id = $id";
        $query = $db->query("select " . join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)) . ", d.dept_code, d.dept_name, ms.name as supplier_name from $q");

        if (!$query)
            return [$this->db->error(), false];

        $res = $query->getResult();
        if (count($res) > 0) {
            $id = $res[0]->id;
            $query = $db->query("
				select
					sum(order_qty * price_per_item) as total_price,
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
				where s.purchase_order_id = $id and s.flag = 1
			");
            if (!$query)
                return [$this->db->error(), false];
            $query = $query->getResult();
            $res[0]->grand_total = $query[0]->grand_total;
            $res[0]->total_price = $query[0]->total_price;
        }
        return $res;
    }

    function lastNumber($y, $id)
    {
        $query = $this->db->query("select (select dept_code from m_department where id = '$id') as dept_code, LPAD(IFNULL( (SELECT no FROM `$this->table` where `year` = '$y' order by no desc limit 1), 0)+1, 4, 0) as no, IFNULL( (SELECT no FROM `$this->table` where `year` = '$y' order by no desc limit 1), 0)+1 as number");

        if (!$query)
            return [false, $this->db->error()];
        return [true, $query->getResult()];
    }

    function info($id)
    {
        $query = $this->db->query("select i.supplier_id, day(i.approval_date) as approval_day, i.approved, i.ref_internal_bmt, i.po_no, i.po_date, day(i.po_date) as po_date_day, i.title, s.name as supplier, i.ask_approval_date, day(i.ask_approval_date) as ask_day, i.approval_date, i.approval_by, u.name as approved_by_name, approval_2_date, t.name as approved2_by_name, i.rev, rfq.rfq_no, pr.pr_no from purchase_order i
		left join m_supplier s on s.id = i.supplier_id 
		left join users u on u.id = i.approval_by
		left join users t on t.id = i.approval_2_by
        left join rfq_header rfq on rfq.id= i.rfq_id
        left join pr pr on pr.id = i.pr_id
		where i.id=$id");

        if (!$query)
            return [false, $this->db->error()];
        return [true, $query->getResult()];
    }
    function getPo($po_no)
    {
        $query = $this->db->query("
        select
            pb.po_no,
            pb.total_in_idr
        from po_budget pb
        where po_no = '$po_no'
        ");
        if (!$query)
            return [false, $this->db->error()];
        return [true, $query->getResult()];
    }
    function getPoRev($po_no)
    {
        $query = $this->db->query(
            "
            SELECT 
                s.grand_total * (
                    SELECT COALESCE(NULLIF(poh2.exchange_rate, 0), 1)
                    FROM purchase_order_history poh2 
                    WHERE poh2.po_no = s.po_no
                    LIMIT 1
                ) AS total_in_idr
            FROM (
                SELECT 
                    poh.po_no,
                    (
                        poh.charge1 + poh.charge2 + poh.other_charge - poh.discount
                    ) AS total_charge,

                    SUM(poih.price_per_item * poih.order_qty) AS total_price,

                    (
                        SUM(poih.price_per_item * poih.order_qty) + 
                        (poh.charge1 + poh.charge2 + poh.other_charge - poh.discount)
                    ) AS grand_total

                FROM purchase_order_item_history poih
                LEFT JOIN purchase_order_history poh 
                    ON poih.purchase_order_id = poh.id
                WHERE poh.po_no = '$po_no'
                    AND poh.flag = 1
                    AND poih.flag = 1
                AND poih.flag = 1
                GROUP BY poh.po_no, poh.charge1, poh.charge2, poh.other_charge, poh.discount
            ) s
            "
        );
        if (!$query)
            return [false, $this->db->error()];
        return [true, $query->getResult()];
    }
}
