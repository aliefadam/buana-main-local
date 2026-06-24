<?php namespace App\Controllers\Bom;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Bom\SubledgerRevisemutationModel;

class SubledgerRevisemutation extends ResourceController
{
    use ResponseTrait;

    private function decodeRequestValue($value, $default)
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_object($value)) {
            return (array) $value;
        }
        if ($value === null || $value === '') {
            return $default;
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : $default;
    }

    public function index()
    {
        try {
            $model = new SubledgerRevisemutationModel();
            $json = $_REQUEST;
            $json["filter"] = $this->decodeRequestValue($json["filter"] ?? null, []);
            $json["filterType"] = $this->decodeRequestValue($json["filterType"] ?? null, []);
            $json["filterCondition"] = $this->decodeRequestValue($json["filterCondition"] ?? null, []);
            $json["sortBy"] = $this->decodeRequestValue($json["sortBy"] ?? null, []);
            $json["sortDesc"] = $this->decodeRequestValue($json["sortDesc"] ?? null, []);
            $json = (Object) $json;
            $data = $model->read($json);
            if (!$data[0]) {
                return $this->respond([
                    'status' => false,
                    'data' => $data[1],
                    'total' => $data[2] ?? 0,
                    'debug' => [
                        'request' => $json,
                    ],
                ], 200);
            }
            return $this->respond(['status' => true, 'data' => $data[1], 'total' => $data[2]], 200);
        } catch (\Throwable $e) {
            return $this->respond([
                'status' => false,
                'data' => $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ], 200);
        }
    }
}
