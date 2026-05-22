<?php namespace App\Controllers\Administration\Project;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Project\ProjectModel;

class Project extends ResourceController
{
    use ResponseTrait;

    private function getAuthorization()
    {
        $request = service('request');

        $auth = $request->getHeaderLine('Authorization');
        if (!empty($auth)) {
            return trim($auth);
        }

        if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            return trim($_SERVER['HTTP_AUTHORIZATION']);
        }

        if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            return trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
        }

        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (!empty($headers['Authorization'])) {
                return trim($headers['Authorization']);
            }
        }

        return null;
    }

    public function index()
    {
        $request = service('request');

        // $authorization = $this->getAuthorization();

        // if (empty($authorization)) {
        //     return $this->respond([
        //         'status'  => false,
        //         'message' => 'Authorization header not found'
        //     ], 401);
        // }

        // $validToken = 'TEST90901212DEWAWEB';

        // if ($authorization !== $validToken) {
        //     return $this->respond([
        //         'status'  => false,
        //         'message' => 'Invalid Authorization token'
        //     ], 401);
        // }

        try {
            $model = new ProjectModel();

            if ($request->getMethod() === 'put') {
                $json = $request->getJSON();
                return $this->update($json->pk ?? null);
            }

            if ($request->getMethod() === 'delete') {
                $json = $request->getVar();
                return $this->delete($json[$model->primaryKey] ?? null);
            }

            // GET
            $params = $request->getVar();
            $params['filter']          = json_decode($params['filter'] ?? '{}', true);
            $params['filterType']      = json_decode($params['filterType'] ?? '{}', true);
            $params['filterCondition'] = json_decode($params['filterCondition'] ?? '{}', true);

            $data = $model->read((object) $params);

            return $this->respond([
                'status' => true,
                'data'   => $data[0],
                'total'  => $data[1]
            ], 200);

        } catch (\Throwable $e) {
            return $this->respond([
                'status' => false,
                'error'  => $e->getMessage()
            ], 500);
        }
    }
}
