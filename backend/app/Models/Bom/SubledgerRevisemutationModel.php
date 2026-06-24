<?php namespace App\Models\Bom;

use CodeIgniter\Model;

class SubledgerRevisemutationModel extends Model
{
    protected $table = 'subledger_revise_mutation';
    protected $returnType = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id',
        'pr_id',
        'pr_part_id',
        'pr_subledger_id',
        'old_data',
        'created_by',
        'created_at',
    ];

    protected function initialize()
    {
        $this->db = db_connect();
        $this->db->query("SET time_zone = '+07:00'");
    }

    function read($json)
    {
        $limit = $json->limit ?? 10;
        $offset = $json->offset ?? 0;
        $sortBy = is_array($json->sortBy ?? null) ? $json->sortBy : ['id'];
        $sortDesc = is_array($json->sortDesc ?? null) ? $json->sortDesc : [true];
        $filter = is_array($json->filter ?? null) ? $json->filter : [];

        try {
            $builder = $this->db->table($this->table . ' s');
            $builder->select('s.*, u.name as created_by_name');
            $builder->join('users u', 'u.id = s.created_by', 'left');

            if (isset($filter['pr_subledger_id']) && $filter['pr_subledger_id'] !== '') {
                $builder->where('s.pr_subledger_id', $filter['pr_subledger_id']);
            }
            if (isset($filter['pr_id']) && $filter['pr_id'] !== '') {
                $builder->where('s.pr_id', $filter['pr_id']);
            }
            if (isset($filter['pr_part_id']) && $filter['pr_part_id'] !== '') {
                $builder->where('s.pr_part_id', $filter['pr_part_id']);
            }

            foreach ($sortBy as $index => $field) {
                $direction = ($sortDesc[$index] ?? false) === true || ($sortDesc[$index] ?? 'false') === 'true'
                    ? 'DESC'
                    : 'ASC';
                $allowedSort = ['id', 'created_at', 'pr_id', 'pr_part_id', 'pr_subledger_id'];
                if (in_array($field, $allowedSort, true)) {
                    $builder->orderBy('s.' . $field, $direction);
                }
            }

            $countBuilder = clone $builder;
            $total = $countBuilder->countAllResults();

            if ((int) $limit !== -1) {
                $builder->limit((int) $limit, (int) $offset);
            }

            $query = $builder->get();
            if (!$query) {
                return [false, $this->db->error(), 0];
            }

            return [true, $query->getResult(), $total];
        } catch (\Throwable $e) {
            return [false, $e->getMessage(), 0];
        }
    }
}
