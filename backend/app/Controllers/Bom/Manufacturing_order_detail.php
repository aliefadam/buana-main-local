<?php namespace App\Controllers\Bom;

use App\Models\Bom\Manufacturing_order_detailModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

class Manufacturing_order_detail extends ResourceController
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
            $model = new Manufacturing_order_detailModel();
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
        $model = new Manufacturing_order_detailModel();
        $json = $this->request->getJSON();

        $qty = isset($json->qty) ? (int) $json->qty : 1;
        if ($qty < 1) {
            $qty = 1;
        }

        $data = [];
        foreach ($model->allowedFields as $value) {
            if (isset($json->{$value})) {
                $data[$value] = $json->{$value};
            }
        }

        $inserted = 0;
        for ($i = 0; $i < $qty; $i++) {
            $model->insert($data);
            $inserted += $model->affectedRows();
        }

        if ($inserted > 0) {
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
        $model = new Manufacturing_order_detailModel();
        $json = $this->request->getJSON();
        $data = [];

        if ($json) {
            foreach ($json as $key => $value) {
                if ($key != 'pk') {
                    $data[$key] = $value;
                }
            }
        }

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
        $model = new Manufacturing_order_detailModel();
        $data = $model->find($pk);

        if ($data) {
            $model->delete($pk);
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
