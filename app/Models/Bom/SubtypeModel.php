<?php

namespace App\Models\Bom;

use CodeIgniter\Model;

class SubtypeModel extends Model
{
    protected $table = 'type_sub_operationals';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id", "name", "type_operational_id", "is_active", "use_memo", "created_at", "updated_at"];

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

        /*
         * [NEW FEATURED PENDING]
         * Filter subtype agar hanya tampil yang punya budget amount > 0.
         *
         * $filter = $json->filter ?? [];
         * $filterType = $json->filterType ?? [];
         * $filterCondition = $json->filterCondition ?? [];
         *
         * if (is_object($filter)) {
         *     $filter = (array) $filter;
         * }
         * if (is_object($filterType)) {
         *     $filterType = (array) $filterType;
         * }
         * if (is_object($filterCondition)) {
         *     $filterCondition = (array) $filterCondition;
         * }
         *
         * $onlyHasBudget = false;
         * if (array_key_exists("only_has_budget", $filter)) {
         *     $onlyHasBudget = in_array((string) $filter["only_has_budget"], ["1", "true", "TRUE"], true);
         *     unset($filter["only_has_budget"]);
         *     unset($filterType["only_has_budget"]);
         *     unset($filterCondition["only_has_budget"]);
         * }
         *
         * $where = where($filter, $filterType, $filterCondition);
         * if ($onlyHasBudget) {
         *     $budgetFilter = "exists (
         *         select 1
         *         from budget_type_subs bts
         *         where bts.type_sub_operational_id = s.id
         *         and bts.amount > 0
         *     )";
         *     if (trim($where) == "") {
         *         $where = " WHERE " . $budgetFilter;
         *     } else {
         *         $where .= " AND " . $budgetFilter;
         *     }
         * }
         */
        /* if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; */

        $q = "select 
			" . join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)) . " 
			from `$this->table` s";
        $query = $db->query("select * from
		($q)s $where $order " . ($limit == -1 ? '' : "limit $offset, $limit"));

        if (!$query)
            return [false, $this->db->error(), 0];

        $totalQuery = $db->query("select count(*) as total from ($q)s $where");
        if (!$totalQuery)
            return [false, $this->db->error()];
        $totalQuery = $totalQuery->getResult();
        return [true, $query->getResult(), $totalQuery[0]->total];
    }
}
