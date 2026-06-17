<?php namespace App\Controllers\Bom;

use App\Models\Bom\Manufacturing_orderModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

class Manufacturing_order extends ResourceController
{
    use ResponseTrait;

    public function index()
    {
        $session = session();
        $s = $session->get();
        if (!isset($s['userid'])) {
            return $this->respond(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $model = new Manufacturing_orderModel();
            if ($this->request->getMethod() == 'put') {
                $json = $this->request->getJSON();

                return $this->update($json->pk);
            } elseif ($this->request->getMethod() == 'delete') {
                $json = $_REQUEST;

                return $this->delete($json[$model->primaryKey]);
            } else {
                $json = $_REQUEST;
                $json['filter'] = json_decode($json['filter'] ?? '{}', true);
                $json['filterType'] = json_decode($json['filterType'] ?? '{}', true);
                $json['filterCondition'] = json_decode($json['filterCondition'] ?? '{}', true);
                $json = (object) $json;
                $data = $model->read($json);

                return $this->respond(['status' => $data[0], 'data' => $data[1], 'total' => $data[2]], 200);
            }
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'data' => $e->getMessage()], 200);
        }
    }

    public function create()
    {
        $model = new Manufacturing_orderModel();
        $json = $this->request->getJSON();

        $data = [];
        foreach ($model->allowedFields as $value) {
            if (isset($json->{$value})) {
                $data[$value] = $json->{$value};
            }
        }

        $session = session();
        $s = $session->get();
        if (in_array('created_by', $model->allowedFields) && isset($s['userid'])) {
            $data['created_by'] = $s['userid'];
        }
        if (in_array('created_date', $model->allowedFields) && !isset($data['created_date'])) {
            $data['created_date'] = date('Y-m-d H:i:s');
        }
        if (in_array('flag', $model->allowedFields) && !isset($data['flag'])) {
            $data['flag'] = 1;
        }

        $model->insert($data);
        $affected = $model->affectedRows();
        if ($affected != 0) {
            $response = [
                'status' => true,
                'data' => 'Data Saved',
            ];
        } else {
            $response = [
                'status' => false,
                'data' => $model->errors(),
            ];
        }

        return $this->respondCreated($response, 200);
    }

    public function update($pk = null)
    {
        $model = new Manufacturing_orderModel();
        $json = $this->request->getJSON();
        $data = [];

        if ($json) {
            foreach ($json as $key => $value) {
                if ($key != 'pk') {
                    $data[$key] = $value;
                }
            }
        }

        $session = session();
        $s = $session->get();
        $model->update($pk, $data);
        $affected = $model->affectedRows();
        if ($affected != 0) {
            $response = [
                'status' => true,
            ];
        } else {
            $response = [
                'status' => false,
                'data' => $model->errors(),
            ];
        }

        return $this->respond($response);
    }

    public function delete($pk = null)
    {
        $model = new Manufacturing_orderModel();
        $data = $model->find($pk);

        if ($data) {
            $update = ['flag' => 0];

            $model->update($pk, $update);
            $affected = $model->affectedRows();
            if ($affected != 0) {
                $response = [
                    'status' => true,
                ];
            } else {
                $response = [
                    'status' => false,
                    'data' => $model->errors(),
                ];
            }

            return $this->respond($response);
        } else {
            $response = [
                'status' => false,
                'data' => 'Data not found!',
            ];

            return $this->respond($response);
        }
    }
}
