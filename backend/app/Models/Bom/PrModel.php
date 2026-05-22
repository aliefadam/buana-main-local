<?php namespace App\Models\Bom;

use CodeIgniter\Model;

class PrModel extends Model
{
    protected $table = 'pr';
    protected $returnType = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        "id", "pr_no", "pr_date", "pr_subject", "status", "currency", "exchange_rate", "rate_date", "pr_notes",
        "ask_approval", "pr_peminta", "pr_menyetujui", "peminta_setuju", "penyetuju_setuju", "created_date",
        "rejected_by", "created_by", "modified_date", "rejected_date", "reject_notes", "modified_by", "attachment",
        "askapproval_notes", "approval1_notes", "approval2_notes", "approved_notes", "rev", "rev_date", "isAn",
        "filepath", "ragic_id", "rfq_id", "revisi_notes", "delete_by", "delete_notes", "delete_date", "flag",
        "revise_by", "revise_date", "revise_notes", "approved_by", "validated_by", "ask_canceled_date",
        "canceled_by", "ask_canceled_note", "canceled_note", "canceled_date", "canceled_2_by", "canceled_2_date",
        "user_request_date"
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
        $db = $this->db;
        $where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
        // if(trim($where)=='')
        //     $where = 'where s.flag = 1';
        // else
        //     $where .= ' and s.flag = 1';
        $q = "select
            " . join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)) . " , date(s.created_date) as crea_date, date(s.modified_date) as mod_date,
            (select q.rfq_no from rfq_header q where q.id=s.rfq_id) as rfq_no, (select count(p.id) from purchase_order p where p.pr_id = s.id and p.flag=1 and p.approved BETWEEN 1 and 2) as in_po,
            (select distinct r.order_id from ragic_data r where r.ragic_id = s.ragic_id limit 1) as ragic_no,
            (select (select name from users where id = a.revision_by) from pr_history a where id = s.id order by idx desc limit 1) as revision_by_name,
            (select revision_date from pr_history a where id = s.id order by idx desc limit 1) as revision_date,
            c.name as created_by_name,
            m.name as modified_by_name,
            pm.name as peminta,
            pn.name as menyetujui,
            r.name as rejected_by_name,
            c1.name as cancel_by_name,
            c2.name as cancel_2_by_name,
            r1.name as revise_by_name,
            ap.name as approved_by_name,
            vd.name as validated_by_name,
            concat(s.pr_no, ' - ', s.pr_subject) as selectdesc, ppi.item_in_pr
            from `$this->table` s
            left join users r on r.id = s.rejected_by
            left join users c on c.id = s.created_by
            left join users m on m.id = s.modified_by
            left join users pm on pm.id = s.pr_peminta
            left join users pn on pn.id = s.pr_menyetujui
            left join users ap on ap.id = s.approved_by
            left join users vd on pn.id = s.validated_by
            left join users c1 on c1.id = s.canceled_by
            left join users c2 on c2.id = s.canceled_2_by
            left join users r1 on r1.id = s.revise_by
            left join (SELECT group_concat(concat(';',pp.item_id,';')) as item_in_pr, pp.pr_id  FROM `pr_part` pp
            left join m_item i on i.id=pp.item_id  group by pp.pr_id)ppi on ppi.pr_id=s.id";

        $query = $db->query("select * from
        ($q)s $where $order " . ($limit == -1 ? '' : "limit $offset, $limit"));

        if (!$query) {
            return [false, $this->db->error(), 0];
        }

        $totalQuery = $db->query("select count(*) as total from ($q)s $where");
        if (!$totalQuery) {
            return [false, $this->db->error()];
        }

        $totalQuery = $totalQuery->getResult();
        $session = session();
        $s = $session->get();
        $admin = false;
        if (isset($s["userid"])) {
            if (is_string($s["groups"])) {
                if (str_contains($s["groups"], 'administrator')) {
                    $admin = true;
                }
            }
        }

        return [
            true,
            isset($json->debug) && $admin ? [$q, $json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? [], $where] : $query->getResult(),
            $totalQuery[0]->total
        ];
    }

    function lastNumber()
    {
        $query = $this->db->query("select LPAD(IFNULL( (SELECT CAST(SUBSTRING(pr_no, 7) AS UNSIGNED) as no FROM `$this->table` where SUBSTRING(pr_no, 1, 6) = DATE_FORMAT(now(), '%Y%m') order by id desc limit 1), 0)+1, 3, 0) as no");

        if (!$query) {
            return [false, $this->db->error()];
        }

        return [true, $query->getResult()];
    }

    function info($id)
    {
        $query = $this->db->query("select pr.pr_no, pr.pr_subject, u.name as peminta_name, pr.peminta_setuju, v.name as penyetuju_name, pr.penyetuju_setuju,  w.name as created_name, w.no_hp as created_phone from pr pr
        left join users u on u.id = pr.pr_peminta
        left join users v on v.id = pr.pr_menyetujui
        left join users w on w.id= pr.created_by
        where i.id=$id");

        if (!$query) {
            return [false, $this->db->error()];
        }

        return [true, $query->getResult()];
    }
}
