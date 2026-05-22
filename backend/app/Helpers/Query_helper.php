<?php
	function where($search = array(), $searchType = array(), $searchCondition = array(), $isDebug = false){
		$whereCondition = [
			"AND" => [],
			"OR" => []
		];
		$params = [
			"AND" => [],
			"OR" => []
		];

		if(is_object($search)){
			$search = (array) $search;
		}

		if(is_object($searchType)){
			$searchType = (array) $searchType;
		}

		if(is_object($searchCondition)){
			$searchCondition = (array) $searchCondition;
		}

		if(is_string($search))
			$search = json_decode($search, true);

		if(is_string($searchCondition))
			$searchCondition = json_decode($searchCondition, true);
		if($isDebug)
			var_dump($searchCondition);

		if(is_string($searchType))
			$searchType = json_decode($searchType, true);
		
		
		foreach ($search as $key => $value) {
			$tmpKey =  $key;
			/*if(is_array($value))
				foreach ($value as $k => $val) {
					if(!is_array($val)){
						$value[$k] = explode(",", $val);
					}
				}
			else
				$value = explode(",", $value);
			//var_dump($value);
			if(isset($searchType[$key]))
				if($searchType[$key] == 'btw'){
					array_walk_recursive($value, function ($item, $key) use (&$results){$results[$key] = $item;});//array_merge(...$value);
					$value = array_chunk($value, 2);
				}*/
			if(!is_array($value) && !is_object($value)){
				$value = [$value];
			}
			$searchCondition[$key] = [$searchCondition[$key] ?? "AND"];
			$condition = strtoupper(trim($searchCondition[$key][0]));
			if($isDebug){
				var_dump($key);
				var_dump($searchCondition);
				var_dump($condition);
			}
			$result = [];
			foreach ($value as $i => $val) {
				if(gettype($val) == 'function'){
					$res = $val();
					array_push($result, $res);
				}
				else if(isset($searchType[$key])){
					$type = $searchType[$key];
					if(gettype($searchType[$key]) != 'string'){
						$type = $searchType[$key][$i] ?? $searchType[$key][0];
					}

					switch ($type) {
						case "*":
							array_push($result, "$tmpKey like ?");
							array_push($params[$condition], $val);
							break;
						case "%":
							array_push($result, "$tmpKey like ?");
							array_push($params[$condition], "%" . (string) $val . "%");
							break;
						case "!%":
							array_push($result, "$tmpKey not like ?");
							array_push($params[$condition], "%" . $val . "%");
							break;
						case "=":
							array_push($result, "$tmpKey = ?");
							array_push($params[$condition], $val);
							break;
						case "!=":
							array_push($result, "$tmpKey != ?");
							array_push($params[$condition], $val);
							break;
						case ">":
							array_push($result, "$tmpKey > ?");
							array_push($params[$condition], $val);
							break;
						case ">=":
							array_push($result, "$tmpKey >= ?");
							array_push($params[$condition], $val);
							break;
						case "<":
							array_push($result, "$tmpKey < ?");
							array_push($params[$condition], $val);
							break;
						case "<=":
							array_push($result, "$tmpKey <= ?");
							array_push($params[$condition], $val);
							break;
						case "notnull":
							array_push($result, "$tmpKey is not null");
							break;
						case "isnull":
							array_push($result, "$tmpKey is null");
							break;
						case "ddif":
							array_push($result, "datediff(day, $tmpKey, ?) = 0");
							array_push($params[$condition], $val);
							break;
						case "in":
							array_push($result, "$tmpKey in ($val)");
							//params.push(val)
							break;
						case "is":
							array_push($result, "$tmpKey is ?");
							array_push($params[$condition], $val);
							break;
						case "notin":
							array_push($result, "$tmpKey not in ($val)");
							break;
						case "btw":
							array_push($result, "$tmpKey between ? and ?");
							if(!is_array($val))
							    $tmpVal = explode(',', $val);
							else{
								array_walk_recursive($val, function ($item, $key) use (&$results){$results[$key] = $item;});//array_merge(...$value);
								$value = array_chunk($val, 2);
							    $tmpVal = $val;
							}
							//try {
								/*if(is_array($tmpVal[0])){
									array_push($params[$condition], $tmpVal[0][0]);
									array_push($params[$condition], $tmpVal[0][1]);
								}
								else{*/
									array_push($params[$condition], $tmpVal[0]);
									array_push($params[$condition], $tmpVal[1]);
								//}
							/*} catch (\Throwable $th) {
								array_push($params[$condition], $tmpVal);
							}*/
							break;
						default:
							array_push($result, "$tmpKey = ?");
							array_push($params[$condition], $val);
							break;
					}
				}
				else{
					if($val===null)
						array_push($result, "$tmpKey is ?");
					else
						array_push($result, "$tmpKey = ?");
					array_push($params[$condition], $val);
				}
			}

			$innerCond = $searchCondition[$key][1] ?? "OR";

			if($isDebug){
				var_dump($innerCond);
			}
			array_push($whereCondition[$condition], " (".join(" ".$innerCond." ", $result).") ");
		}

		if($isDebug){
			var_dump($whereCondition);
		}
		if (count($whereCondition["AND"]) > 0 || count($whereCondition["OR"]) > 0) {
			$whereOpt = [];
			if(count($whereCondition["AND"])>0)
				array_push($whereOpt, "( ".join(" AND ", $whereCondition["AND"])." )");
			if(count($whereCondition["OR"])>0)
				array_push($whereOpt, "( ".join(" OR ", $whereCondition["OR"])." )");
			$params = array_merge($params["AND"], $params["OR"]);
			$result = [
				"where"=> " WHERE " . join(" AND ", $whereOpt),
				"params"=> $params
			];
		} else {
			$result = [
				"where"=> "",
				"params"=> array_merge($params["AND"], $params["OR"])
			];
		}

		foreach ($result["params"] as $key => $value){
			$result["where"] = preg_replace("/\?/", "'$value'", $result["where"], 1);
		}
		if($isDebug){
			var_dump($result["where"]);
		}

		return $result["where"];
	}

	function order($sortBy, $sortDesc){
		if(!is_array($sortBy)){
			$sortBy = [$sortBy];
		}
		if(!is_array($sortDesc)){
			$sortDesc = [$sortDesc];
		}

		$order = [];
		foreach ($sortBy as $key => $sort) {
			$desc = isset($sortDesc[$key]) ? $sortDesc[$key] : "false";
			$desc = $desc == "true" ? 'desc' : 'asc';
			$order[] = "$sort $desc";
		}

		if(count($order) == 0)
			return '';

		return " order by ".implode(', ', $order);
	}

	function itemsOptions($json){
		if(!isset($json["filter"]))
			$json["filter"] = array();
		if(!isset($json["filterType"]))
			$json["filterType"] = array();
		if(!isset($json["filterCondition"]))
			$json["filterCondition"] = array();
		if(is_array($json["filter"])){
			$json["filter"] = json_encode($json["filter"] ?? array());
			$json["filterType"] = json_encode($json["filterType"] ?? array());
			$json["filterCondition"] = json_encode($json["filterCondition"] ?? array());
		}
		$json["filter"] = json_decode($json["filter"] ?? '{}', true);
		$json["filterType"] = json_decode($json["filterType"] ?? '{}', true);
		$json["filterCondition"] = json_decode($json["filterCondition"] ?? '{}', true);
		return $json;
	}

	function dbQuery($q, $params = array(), $driver = false){
		$ret = (object) [
			'status' => true,
			'data' => []
		];
		try {
			$q = $driver->query($q, $params);
			if(!$q){
				throw new Exception($driver->error()['message']);
				/* $ret->status = false;
				$ret->data = $driver->error()['message']; */
			}
			else{
				$ret->data = $q->getResult();
			}
		} catch (\Throwable $th) {
			$ret->status = false;
			$ret->data = $th->getMessage();
		}
		return $ret;
	}

	function dbPaging($json, $q, $params = array(), $driver = false){
		$ret = (object) [
			'status' => true,
			'data' => [],
			'total' => 0
		];
		try {
			$limit = $json->limit ?? 10;
			$offset = $json->offset ?? 0;
			$sortBy = $json->sortBy ?? array();
			$sortDesc = $json->sortDesc ?? array();
			$order = order($sortBy, $sortDesc);
			$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
			
			$qData = dbQuery("select * from ($q)s 
				$where $order " . ($limit==-1 ? '' : "limit $offset, $limit"), 
				$params, $driver);
			if(!$qData->status) throw new Exception($qData->data);
			$totalQuery = dbQuery("select count(*) as total from ($q)s $where", [], $driver);
			if(!$totalQuery->status) throw new Exception($totalQuery->data);
			$ret->data = $qData->data;
			$ret->total = $totalQuery->data[0]->total;
			//$ret->q = $q;

		} catch (\Throwable $th) {
			$ret->status = false;
			$ret->data = $th->getMessage();
			//$ret->q = $q;
		}
		return $ret;
	}

	function dbTransaction($qs, $params = array(), $driver = false){
		$ret = (object) [
			'status' => true,
			'data' => []
		];
		$driver->transBegin();
		try {
			foreach ($qs as $i => $val) {
				$q = $driver->query($val, $params[$i] ?? []);
				if(!$q){
					throw new Exception($driver->error()['message']);
				}
			}
			$driver->transCommit();
			if(!is_bool($q)){
				$ret->data = $q->getResult();
			}
		} catch (\Throwable $th) {
			$ret->status = false;
			$ret->data = $th->getMessage();
			$driver->transRollback();
		}
		return $ret;
	}

	function dbInsert($json, $allowedFields, $table, $driver){
		$ret = (object) [
			'status' => true,
			'data' => [],
			'inserted_id'=>-1
		];
		try {
			$builder = $driver->table($table);
			$data = [
			];

			foreach($allowedFields as $value) 
			{
				if(isset($json->{$value}))
					$data[$value] = $json->{$value};
			}
			//var_dump($builder->getInsertID);

			$session = session();
			$s = $session->get();
			if(in_array("created_by", $allowedFields) && isset($s["username"]))
				$data["created_by"] = $s["username"];
			$builder->insert($data);
			//var_dump($driver->connID->insert_id);
			//var_dump($builder);
			$affected = $driver->affectedRows();
			if($affected == 0)
				throw new Exception($driver->error()['message']);
			$id = $driver->connID->insert_id;
			$ret->inserted_id = $id;
			$ret->affected = $affected;
		} catch (\Throwable $th) {
			$ret->status = false;
			$ret->data = $th->getMessage();
		}
		return $ret;
	}

	function dbInsertBatch($json, $table, $driver){
		$ret = (object) [
			'status' => true,
			'data' => []
		];
		try {
			$builder = $driver->table($table);
			$builder->insertBatch($json);
			$affected = $driver->affectedRows();
			if($affected == 0)
				throw new Exception($driver->error()['message']);
			$ret->affected = $affected;
		} catch (\Throwable $th) {
			$ret->status = false;
			$ret->data = $th->getMessage();
		}
		return $ret;
	}

	function dbUpdate($json, $allowedFields, $pk, $table, $driver){
		$ret = (object) [
			'status' => true,
			'data' => []
		];
		try {
			$builder = $driver->table($table);
			$data = [
			];

			foreach($json as $key => $value) 
            {
                if($key!='pk')
                    $data[$key] = $value;
            }

			$session = session();
			$s = $session->get();
			if(in_array("modified_by", $allowedFields) && isset($s["username"]))
				$data["modified_by"] = $s["username"];
			if(in_array("modified_date", $allowedFields))
				$data["modified_date"] = date("Y-m-d H:i:s");
			$builder->where($pk ?? 'id', $json->pk);
			$builder->update($data);
			$affected = $driver->affectedRows();
			if($affected != 0){
				$ret->affected = $affected;
			}
			else{
				throw new Exception($driver->error()['message']);
			}
		} catch (\Throwable $th) {
			$ret->status = false;
			$ret->data = $th->getMessage();
		}
		return $ret;
	}

	function dbDelete($col, $pk, $allowedFields, $table, $driver, $flag = false){
		$ret = (object) [
			'status' => true,
			'data' => []
		];

		try {
			$builder = $driver->table($table);
			$data = $builder->where($col, $pk);
			if($data){
				if($flag == false){
					$builder->where($col, $pk);
    				$builder->delete();
            		//$driver->delete($pk);
					$affected = $driver->affectedRows();
					if($affected != 0){
						$ret->affected = $affected;
					}
					else{
						throw new Exception($driver->error()['message']);
					}
				}
				else{
					$data = [];
					$data[$flag] = 0;
					$session = session();
					$s = $session->get();
					if(in_array("modified_by", $allowedFields) && isset($s["username"]))
						$data["modified_by"] = $s["username"];
					if(in_array("modified_date", $allowedFields))
						$data["modified_date"] = date("Y-m-d H:i:s");
					$builder->update($pk, $data);
					$affected = $driver->affectedRows();
					if($affected != 0){
						$ret->affected = $affected;
					}
					else{
						throw new Exception($driver->error()['message']);
					}
				}
			}
		} catch (\Throwable $th) {
			$ret->status = false;
			$ret->data = $th->getMessage();
		}
		return $ret;
	}