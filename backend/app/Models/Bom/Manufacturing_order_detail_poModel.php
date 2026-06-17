<?php namespace App\Models\Bom;

use CodeIgniter\Model;

class Manufacturing_order_detail_poModel extends Model
{
    protected $table = 'manufacturing_order_detail_po';
    protected $returnType = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id',
        'manufacturing_order_detail_id',
        'purchase_order_id',
    ];

    protected function initialize()
    {
        $this->db = db_connect();
        $this->db->query("SET time_zone = '+07:00'");
    }

    public function addPrefix($field)
    {
        return 's.' . $field;
    }

    public function read($json)
    {
        helper(['Query_helper']);
        $limit = $json->limit ?? 10;
        $offset = $json->offset ?? 0;
        $sortBy = $json->sortBy ?? [];
        $sortDesc = $json->sortDesc ?? [];
        $order = order($sortBy, $sortDesc);
        $db = $this->db;
        $where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);

        $q = "select "
            . join(',', array_map([$this, 'addPrefix'], $this->allowedFields))
            . ", po.po_no, po.title as po_title
            from `$this->table` s
            left join purchase_order po on po.id = s.purchase_order_id";

        $query = $db->query("select * from ($q) s $where $order " . ($limit == -1 ? '' : "limit $offset, $limit"));
        if (!$query) {
            return [false, $this->db->error(), 0];
        }

        $totalQuery = $db->query("select count(*) as total from ($q) s $where");
        if (!$totalQuery) {
            return [false, $this->db->error(), 0];
        }

        $totalQuery = $totalQuery->getResult();

        return [true, $query->getResult(), $totalQuery[0]->total];
    }
}
