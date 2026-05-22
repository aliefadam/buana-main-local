<?php

namespace App\Controllers\Bom;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\InvoiceModel;
use App\Models\PurchaseOrderModel;

class Invoice extends ResourceController
{
    use ResponseTrait;
    // get all product
    public function index()
    {
        $session = session();
        $s = $session->get();
        if (!isset($s["userid"]))
            return $this->respond(['status' => false, 'message' => 'Unauthorized'], 401);
        try {
            $uri = service('uri');
            $segment = [];
            $segment = $uri->getSegments();
            $model = new InvoiceModel();
            if ($this->request->getMethod() == 'put') {
                $json = $this->request->getJSON();
                return $this->update($json->pk);
            } else if ($this->request->getMethod() == 'delete') {
                $json = $_REQUEST;
                return $this->delete($json[$model->primaryKey]);
            } else {
                $json = $_REQUEST;
                if (is_array($json["filter"])) {
                    $json["filter"] = json_encode($json["filter"] ?? array());
                    $json["filterType"] = json_encode($json["filterType"] ?? array());
                    $json["filterCondition"] = json_encode($json["filterCondition"] ?? array());
                }
                $json["filter"] = json_decode($json["filter"] ?? '{}', true);
                $json["filterType"] = json_decode($json["filterType"] ?? '{}', true);
                $json["filterCondition"] = json_decode($json["filterCondition"] ?? '{}', true);
                $json = (object) $json;
                $data = $model->read($json);
                $ex = explode('.', end($segment));
                if (end($ex) == 'xlsx') {
                    helper(['Excel_helper']);
                    //$exporter = new \Xerobase\ExcelReporter\Export();
                    //$exporter->export($data[0]);
                    exportExcel($data[0], "invoice.xlsx");
                } else
                    return $this->respond(['status' => true, 'data' => $data[0], 'total' => $data[1]], 200);
            }
            //code...
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data' => $e->getMessage()], 200);
        }
    }

    // public function paid()
    // {
    //     $json = $_REQUEST;
    //     $model = new InvoiceModel();
    //     $data["is_paid"] = $json["is_paid"];
    //     $data["paid_date"] = $json["paid_date"];
    //     $data["next_payment"] = $json["next_payment"] ?? 0;
    //     $session = session();
    //     $s = $session->get();

    //     $doc = $this->request->getFile('file');
    //     if (!empty($doc)) {
    //         try {
    //             if (!$doc->hasMoved()) {
    //                 $name = $doc->getRandomName();
    //                 $realName = $doc->getName();
    //                 $filepath = './uploads/invoice' . $json["id"] . '/';
    //                 $data["proof_of_transfer"] = $name . "+++" . $realName;
    //                 //$data['name'] = $realName;
    //                 $doc->move($filepath, $name);
    //             } else {
    //                 $response = [
    //                     'status'   => false,
    //                 ];

    //                 return $this->respondCreated($response, 200);
    //             }
    //         } catch (Exception $err) {
    //             $response = [
    //                 'status'   => false,
    //                 'data'    => $err
    //             ];

    //             return $this->respondCreated($response, 200);
    //         }
    //     }

    //     $model->update($json["id"], $data);
    //     $affected = $model->affectedRows();
    //     if ($affected != 0)
    //         $response = [
    //             'status'   => true
    //         ];
    //     else
    //         $response = [
    //             'status'   => false,
    //             'data'    => $model->errors()
    //         ];

    //     return $this->respond($response);
    // }

    public function paid()
    {
        try {
            $json = $_REQUEST;
            $model = new InvoiceModel();
            $session = session();
            $s = $session->get();

            $targetIdRaw = $json["id"] ?? null;
            if (is_array($targetIdRaw)) {
                $targetIdRaw = reset($targetIdRaw);
            }
            $targetId = (int) trim((string) $targetIdRaw);
            if ($targetId <= 0) {
                throw new \Exception("Invalid invoice id", 400);
            }

            $existing = $model->where('id', $targetId)->first();
            if (!$existing) {
                throw new \Exception("Invoice not found", 404);
            }

            $rawIsPaid = $json["is_paid"] ?? 0;
            if (is_bool($rawIsPaid)) {
                $data["is_paid"] = $rawIsPaid ? 1 : 0;
            } else if (is_numeric($rawIsPaid)) {
                $data["is_paid"] = ((int) $rawIsPaid) > 0 ? 1 : 0;
            } else {
                $rawIsPaid = strtolower(trim((string) $rawIsPaid));
                $data["is_paid"] = in_array($rawIsPaid, ['1', 'true', 'on', 'yes'], true) ? 1 : 0;
            }
            $data["paid_date"] = $json["paid_date"];
            $data["next_payment"] = $json["next_payment"] ?? 0;
            $data["exchange_rate"] = $json["exchange_rate"] ?? 0;
            $data["admin_fee"] = $json["admin_fee"] ?? 0;
            $data["modified_date"] = date("Y-m-d H:i:s");
            if (isset($s["userid"])) {
                $data["modified_by"] = $s["userid"];
            }

            $doc = $this->request->getFile('file');

            // Handle upload file
            if ($doc && $doc->isValid()) {
                if (!$doc->hasMoved()) {
                    $name = $doc->getRandomName();
                    $realName = $doc->getName();
                    $filepath = './uploads/invoice' . $json["id"] . '/';

                    // pastikan folder ada
                    if (!is_dir($filepath)) {
                        mkdir($filepath, 0777, true);
                    }

                    $doc->move($filepath, $name);

                    $data["proof_of_transfer"] = $name . "+++" . $realName;
                } else {
                    throw new \Exception("File already moved.");
                }
            }

            // Update database
            $model->update($targetId, $data);
            if (count($model->errors()) > 0) {
                throw new \Exception(implode("; ", $model->errors()), 400);
            }

            return $this->respond([
                'status' => true,
                'message' => $model->affectedRows() > 0 ? 'Data updated' : 'No value changed, but request processed'
            ]);
        } catch (\Throwable $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                // optional: hapus di production
                'file' => $e->getFile()
            ], 500);
        }
    }

    public function test()
    {
        try {
            $json = $this->request->getJSON();
            $model = new InvoiceModel();
            $session = session();
            $s = $session->get();
            $created_by = $s["userid"];
            $data["debited_acc"] = $json->debited_acc;
            $data["transfer_type"] = $json->transfer_type;
            $data["transaction_code"] = $json->transaction_code;
            if (isset($json->payment_value))
                $data["payment_value"] = $json->payment_value;

            if (isset($json->difference)) {
                $diff = abs($json->invoice_total_price / 100 * $json->difference);
                if ($diff < 5) {
                    $req = $model->db->query("insert into po_payment_value (value_old, value_new, created_by, po_id, invoice_id, payment_id) 
					select 
						case when s.payment_pct_fiat != 0 then s.payment_pct_fiat
						when s.as_reference = 0 then s.grand_total_price
						else s.payment_amount end, 
						'" . $json->payment_value . "',
						'" . $created_by . "',
						s.po_id,
						id,
						" . $json->payment_id . "
					from invoice s where id = " . $json->invoice_id);
                }
            }

            $res = $model->update($json->id, $data);
            $affected = $model->affectedRows();
            if ($res)
                $response = [
                    'status'   => true
                ];
            else
                $response = [
                    'status'   => false,
                    'data'    => $model->errors()
                ];
        } catch (\Throwable $e) {
            $response = ['status' => false, 'data' => $e->getMessage(), 'json' => $json];
        }
        return $this->respond($response);
    }

    public function saveBill()
    {
        $model = new InvoiceModel();
        $json = $this->request->getJSON();

        $data = [
            "reimburse_id" => $json->reimburse_id,
            "payment_pct" => $json->payment_pct,
            "payment_pct_fiat" => $json->payment_pct_fiat,
            "payment_amount" => $json->payment_amount,
            "invoice_charge" => $json->invoice_charge,
            "invoice_charge_note" => $json->invoice_charge_note,
            "invoice_reduction" => $json->invoice_reduction,
            "invoice_reduction_note" => $json->invoice_reduction_note,
            "notes" => $json->notes,
            "etd" => $json->etd,
            "is_paid" => $json->is_paid
        ];
        $res = $model->where("id", $json->id)
            ->set($data)
            ->update();

        if (!$res)
            return $this->respondCreated(["status" => false, "data" => $model->errors()], 201);


        $response = [
            'status'   => true,
            'data'    => 'Data Saved'
        ];

        return $this->respondCreated($response, 200);
    }

    // create a product
    //     public function create()
    //     {
    //         // $json = $this->request->getJSON();
    //         // return $this->respond($json);

    //         try{

    //         $model = new InvoiceModel();
    //         $modelP = new PurchaseOrderModel();
    //         $json = $this->request->getJSON();
    //         $options = [
    //             'cost' => 11  
    //         ];

    //         $data = [
    //         ];

    //         foreach($model->allowedFields as $value) 
    //         {
    // 			if(isset($json->{$value}))
    // 				$data[$value] = $json->{$value};
    //         }

    //         $session = session();
    //         $s = $session->get();
    //         //if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
    //         $data["created_by"] = $s["userid"];

    //         $data["use_credit_note"] = $json->use_credit_note;
    //         if($data["use_credit_note"] == 1){
    //             $data["credit_note_id"] = $json->credit_note_id;
    //         }

    // 		if($data["as_reference"]==0){
    // 			$res = $modelP->readAll($json->po_id);
    // 			$check = $model->select(['payment_pct'])->getWhere(['po_id'=>$json->po_id, 'flag'=>1])->getResult();
    // 			$pct = 0;
    // 			foreach ($check  as $value) {
    // 				$pct+= $value->payment_pct;
    // 			}
    // 			if($pct>=100)
    // 				return $this->respondCreated(["status"=>false, "data"=> "PO telah dibayar 100%!"], 200);
    // 			if(!$res)
    // 				return $this->respondCreated($modelP->errors(), 200);
    // 			$res = $res[0];
    // 			$data["payment_pct"] = 100-$pct;
    // 			$data["charge1"] = $res->charge1;
    // 			$data["charge1_desc"] = $res->charge1_desc;
    // 			$data["charge2"] = $res->charge2;
    // 			$data["charge2_desc"] = $res->charge2_desc;
    // 			$data["supplier_id"] = $res->supplier_id;
    // 			$data["grand_total_price"] = $res->grand_total;
    // 			$data["total_price"] = $res->total_price;
    // 		}
    // 		else{
    // 			//$data["po_id"] = 100-$pct;
    // 			$data["grand_total_price"] = $json->total_price;
    // 		}

    //         $res = $model->insert($data);
    //         $id = $model->getInsertID();
    //         $model->where('id', $id)
    //         ->set(['invoice_no' => "INV".str_pad($id, 4, "0", STR_PAD_LEFT)])
    //         ->update();

    //         $response = [
    //             'status'   => true,
    // 			'res'	=> $res,
    //             'data'    => 'Data Saved'
    //         ];

    //         return $this->respondCreated($response, 200);
    //         } catch (\Throwable $e){
    //              $response = [
    //                 'status'   => false,
    //                 'data'    => $e->getMessage()
    //             ];
    //             return $this->respond($response);
    //         }
    //     }

    public function create()
    {
        try {

            $model = new InvoiceModel();
            $modelP = new PurchaseOrderModel();
            $json = $this->request->getJSON();

            $data = [];

            foreach ($model->allowedFields as $value) {
                if (isset($json->{$value})) {
                    $data[$value] = $json->{$value};
                }
            }

            $session = session();
            $s = $session->get();

            $data["created_by"] = $s["userid"];
            $data["updated_by"] = $s["userid"];

            $data["use_credit_note"] = $json->use_credit_note;

            if ($data["use_credit_note"] == 1) {
                $data["credit_note_id"] = $json->credit_note_id;
            }

            if ($data["as_reference"] == 0) {
                $res = $modelP->readAll($json->po_id);
                $check = $model->select(['payment_pct'])
                    ->getWhere(['po_id' => $json->po_id, 'flag' => 1])
                    ->getResult();

                $pct = 0;
                foreach ($check as $value) {
                    $pct += $value->payment_pct;
                }

                if ($pct >= 100) {
                    return $this->respondCreated([
                        "status" => false,
                        "data"   => "PO telah dibayar 100%!"
                    ], 200);
                }

                if (!$res) {
                    return $this->respondCreated($modelP->errors(), 200);
                }

                $res = $res[0];
                $data["payment_pct"] = 100 - $pct;
                $data["charge1"] = $res->charge1;
                $data["charge1_desc"] = $res->charge1_desc;
                $data["charge2"] = $res->charge2;
                $data["charge2_desc"] = $res->charge2_desc;
                $data["supplier_id"] = $res->supplier_id;
                $data["grand_total_price"] = $res->grand_total;
                $data["total_price"] = $res->total_price;
            } else {
                $data["grand_total_price"] = $json->total_price;
            }

            // Insert invoice
            $res = $model->insert($data);

            if (!$res) {
                return $this->respond([
                    'status' => false,
                    'data'   => $model->errors()
                ]);
            }

            // Ambil ID invoice yang baru diinsert
            $id = $model->getInsertID();

            // Update invoice_no
            $model->where('id', $id)
                ->set(['invoice_no' => "INV" . str_pad($id, 4, "0", STR_PAD_LEFT)])
                ->update();

            // Insert ke table credit_note_mutation jika pakai credit note
            if ($data["use_credit_note"] == 1) {
                $db = \Config\Database::connect();

                $mutationData = [
                    'invoice_id'      => $id, // pakai ID invoice baru
                    'credit_note_id'  => $json->credit_note_id,
                    'amount'          => $json->credit_note_amount,
                    'created_at'      => date('Y-m-d H:i:s'),
                    'updated_at'      => date('Y-m-d H:i:s'),
                    'created_by'      => $s["userid"],
                    'updated_by'      => $s["userid"],
                    'status'          => 'reserved'
                ];

                $db->table('credit_note_mutation')->insert($mutationData);
            }

            $response = [
                'status' => true,
                'res'    => $res,
                'data'   => 'Data Saved'
            ];

            return $this->respondCreated($response, 200);
        } catch (\Throwable $e) {
            $response = [
                'status' => false,
                'data'   => $e->getMessage()
            ];
            return $this->respond($response);
        }
    }

    // update product
    public function update($pk = null)
    {
        $model = new InvoiceModel();
        $modelP = new PurchaseOrderModel();
        $session = session();
        $s = $session->get();
        $json = $this->request->getJSON();
        if ($json) {


            $data = [];

            foreach ($json as $key => $value) {
                if ($key != 'pk')
                    $data[$key] = $value;
            }

            //if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
            $data["modified_by"] = $s["userid"];
            // if(in_array("modified_date", $model->allowedFields))
            $data["modified_date"] = date("Y-m-d H:i:s");
        }
        $json = (object) $json;


        $data["use_credit_note"] = $json->use_credit_note;
        if ($data["use_credit_note"] == 1) {
            $data["credit_note_id"] = $json->credit_note_id;
        }

        if ($data["as_reference"] == 0) {
            $res = $modelP->readAll($json->po_id);
            $check = $model->select(['payment_pct'])->getWhere(['po_id' => $json->po_id, 'flag' => 1])->getResult();
            $pct = 0;
            foreach ($check  as $value) {
                $pct += $value->payment_pct;
            }
            /* if($pct>=100)
				return $this->respondCreated(["status"=>false, "data"=> "PO telah dibayar 100%!"], 200); */
            if (!$res)
                return $this->respondCreated($modelP->errors(), 200);
            $res = $res[0];
            //$data["payment_pct"] = 100-$pct;
            $data["charge1"] = $res->charge1;
            $data["charge1_desc"] = $res->charge1_desc;
            $data["charge2"] = $res->charge2;
            $data["charge2_desc"] = $res->charge2_desc;
            $data["supplier_id"] = $res->supplier_id;
            $data["grand_total_price"] = $res->grand_total;
            $data["total_price"] = $res->total_price;
        } else {
            //$data["po_id"] = 100-$pct;
            $data["grand_total_price"] = $json->total_price;
        }

        // Insert to Database

        $model->update($pk, $data);
        $affected = $model->affectedRows();
        if ($affected != 0 || count($model->errors()) == 0)
            $response = [
                'status'   => true
            ];
        else {
            $response = [
                'status'   => false,
                'data'    => $model->errors()
            ];
        }
        return $this->respond($response);
    }

    // delete product
    public function delete($pk = null)
    {
        $model = new InvoiceModel();
        $data = $model->find($pk);
        if ($data) {
            //$model->delete($pk);
            $model->update($pk, ["flag" => 0]);
            $response = [
                'status'   => true
            ];

            return $this->respond($response);
        } else {
            $response = [
                'status'   => false
            ];

            return $this->respond($response);
        }
    }
}
