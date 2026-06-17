<?php namespace App\Models\Bom;

use CodeIgniter\Model;

class Manufacturing_orderModel extends Model
{
    protected $table = 'manufacturing_order';
    protected $returnType = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id',
        'title',
        'created_by',
        'created_date',
        'flag',
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
        if (trim($where) == '') {
            $where = 'where s.flag = 1';
        } else {
            $where .= ' and s.flag = 1';
        }

        $q = "select "
            . join(',', array_map([$this, 'addPrefix'], $this->allowedFields))
            . ", u.name as created_by_name
            from `$this->table` s
            left join users u on u.id = s.created_by";

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
