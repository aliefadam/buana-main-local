<?php

namespace App\Controllers\Fake;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Fake\PurchaseOrderModel;
use App\Models\Fake\PurchaseOrderItemModel;
use App\Models\UsersModel;
use App\Models\Fake\PaymentModel;

class Purchaseorder extends ResourceController
{
    use ResponseTrait;
    // get all product
    public function index()
    {
        try {
            $model = new PurchaseOrderModel();
            /* $email = \Config\Services::email();
			$q = $model->info(284);
			//email ask approval 1

			if($q[0]){
				$d = $q[1][0];
				$email->setFrom('internal@buanamultiteknik.com', 'Internal');
				$email->setTo('internal@buanamultiteknik.com');
				$ref = explode('//', $d->ref_internal_bmt ?? "");
				$msg = view('email/ask_approval',[
					'approval' => 1,
					'po_no' => $d->po_no,
					'title' => $d->po_no,
					'rfq' => $ref[0]??"-",
					'pr_no' => $ref[1]??"-"
				]);
				$email->setSubject("Ask Approval 1 ".$d->po_no);
				$email->setMessage($msg);
				$email->setMailType('html');

				$email->send();
			} */

            //send ke admin
            //sendWa('628113013485', $msgWa);
            $uri = service('uri');
            $segment = [];
            $segment = $uri->getSegments();
            if ($this->request->getMethod() == 'put') {
                $json = $this->request->getJSON();
                return $this->update($json->pk);
            } else if ($this->request->getMethod() == 'delete') {
                $json = $_REQUEST;
                return $this->delete($json[$model->primaryKey]);
            } else {
                $json = $_REQUEST;
                $json["filter"] = json_decode(json_encode($json["filter"] ?? '{}', true), true);
                $json["filterType"] = json_decode(json_encode($json["filterType"] ?? '{}', true), true);
                $json["filterCondition"] = json_decode(json_encode($json["filterCondition"] ?? '{}', true), true);
                $json = (object) $json;
                //return $this->respond(['status' => true, 'data'=>$json], 200);
                if (isset($json->invoice) && isset($json->filter["id"])) {
                    unset($json->filter["total_payment_pct"]);
                }
                //return $this->respond(["a"=>$json]);

                /* helper(['Wa_helper']);
					sendWa('628113525765', "a\r\na"); */

                $data = $model->read($json);
                $ex = explode('.', end($segment));
                $tmpXls = [];

                if (end($ex) == 'xlsx') {
                    helper(['Excel_helper']);
                    $exporter = new \Xerobase\ExcelReporter\Export();
                    $modelItem = new PurchaseOrderItemModel();

                    $columns = [];
                    $i = 1;
                    foreach ($data[0] as $x => $val) {

                        //get oclumns
                        $tmpXls[] = $val;
                        if (count($columns) == 0) {
                            $columns = array_keys($array = json_decode(json_encode($val), true));
                        }
                        //get items
                        $ins = $modelItem->readItem($val->id);
                        foreach ($ins[0] as $x2 => $val2) {
                            if (!isset($val2->grand_total))
                                $val2->grand_total = "";
                        }
                        $ins[0][] = null;
                        array_splice($data[0], $i, 0, $ins[0]);
                        //array_splice( $data[0], $i, 0, null );
                        $i = $i + count($ins) + 2;
                        /*$itemColumns = new \stdClass();
						$itemFooter = new \stdClass();
						$itemFooter2 = new \stdClass();
						//$items = [];
						foreach ($columns as $key => $value) {
							$col = array_keys(json_decode(json_encode($ins[0][0] ?? []), true));
							$itemColumns->{$value} = $col[$key] ?? "";
							$itemFooter->{$value} = "";
							$itemFooter2->{$value} = $value;
						}
						$tmpXls[] = $itemColumns;

						foreach ($ins[0] as $key => $value) {
							$t = new \stdClass();
							$v = array_values(json_decode(json_encode($value), true));
							foreach ($columns as $key2 => $value2) {
								$t->{$value2} = $v[$key2] ?? "";
							}

							$tmpXls[] = $t;
						}

						$tmpXls[] = $itemFooter;
						$tmpXls[] = $itemFooter2;*/

                        $i++;
                    };
                    //return $this->respond(['status' => true, 'data'=>$data[0]], 200);
                    //$exporter->export($tmpXls);
                    exportExcel($data[0], "po.xlsx");
                    // return $this->respond(['status' => true, 'data'=>$data[0], 'total' => $data[1]], 200);
                } else
                    return $this->respond(['status' => true, 'data' => $data[0], 'total' => $data[1]], 200);
            }
            //code...
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data' => $e->getMessage()], 200);
        }
    }

    public function addcharge()
    {
        $model = new PurchaseOrderModel();
        $json = $this->request->getJSON();
        $data = ["charge1" => $json->charge1, "charge2" => $json->charge2];
        $res = $model->where('id', $json->purchase_order_id)
            ->set($data)
            ->update();
        if (!$res)
            return $this->respondCreated(["status" => false, "data" => $model->errors()], 201);

        return $this->respondCreated(["status" => true], 201);
    }

    public function cancel()
    {
        $model = new PurchaseOrderModel();
        $model1 = new UsersModel();
        $json = $_REQUEST;
        $q = $model->info($json['id']);
        $d = $q[1][0];
        $session = session();
        $s = $session->get();
        if ($d->approved == 3 && isset($s["userid"])) {
            $canceledBy = $s["userid"];
            $u = $model1->info($canceledBy);
            if ($u[0]) {
                $v = $u[1][0];
                $user = $v->name;
            }
            $date = date("Y-m-d H:i:s");
            $ask_canceled_notes = $json['ask_canceled_note'];


            if (isset($json->ask_canceled_note)) {
                $model->query("insert into fake_purchase_order_comment (purchase_order_id, notes, type, created_by) values(?, ?, ?, ?)", [$json->id, $json->ask_canceled_note, "Canceled Notes", $s["userid"]]);
            }

            $model->db->query("
			insert into fake_purchase_order_history (
				`canceled_by`, `po_no`, `dept_id`, `po_date`, `order_type`, `supplier_id`, `promised_delivery_date`, `currency`, `exchange_rate`, `rate_date`, `final_quote_url`, `signed_po_url`, `signed_pr_url`, `no`, `year`, `charge1`, `charge2`, `charge1_desc`, `charge2_desc`, `note`, `title`, `flag`, `approved`, `created_date`, `is_complete`, `ref_offer_no`, `ref_internal_bmt`, `ask_approval_date`, `approval_date`, `approval_2_date`, `approval_2_by`, `approval_by`, `created_by`, `modified_date`, `modified_by`, `approval_note`, `new_po_no`, `reject_note1`, `reject_note2`, `other_charge`, `discount`, `payment_term`, `despatch`, `shipping_address`, `miscellaneous`, `eta_date`, `received_date`, `rev`, `purchase_order_id`
			)
			(SELECT " . $canceledBy . ", `po_no`, `dept_id`, `po_date`, `order_type`, `supplier_id`, `promised_delivery_date`, `currency`, `exchange_rate`, `rate_date`, `final_quote_url`, `signed_po_url`, `signed_pr_url`, `no`, `year`, `charge1`, `charge2`, `charge1_desc`, `charge2_desc`, `note`, `title`, `flag`, `approved`, `created_date`, `is_complete`, `ref_offer_no`, `ref_internal_bmt`, `ask_approval_date`, `approval_date`, `approval_2_date`, `approval_2_by`, `approval_by`, `created_by`, `modified_date`, `modified_by`, `approval_note`, `new_po_no`, `reject_note1`, `reject_note2`, `other_charge`, `discount`, `payment_term`, `despatch`, `shipping_address`, `miscellaneous`, `eta_date`, `received_date`, `rev`, `id` FROM `fake_purchase_order` where id = " . $json['id'] . ")
			");

            $model->db->query("
			insert into fake_purchase_order_comment_history (
				`notes`, `purchase_order_id`, `created_date`, `created_by`, `type`
			)
			(SELECT `notes`, (select id from fake_purchase_order_history where purchase_order_id = " . $json['id'] . " order by id desc limit 1) as `purchase_order_id`, `created_date`, `created_by`, `type` FROM `fake_purchase_order_comment` where purchase_order_id = " . $json['id'] . ")
			");

            $model->db->query("
			insert into fake_purchase_order_item_history (
				`purchase_order_id`, `bom_id`, `price_per_item`, `item_id`, `order_qty`, `active`, `flag`, `allocation`, `pr_part_id`, `complete_qty`, `modified_date`, `modified_by`, `created_date`, `created_by`
			)
			(SELECT (select id from fake_purchase_order_history where purchase_order_id = " . $json['id'] . " order by id desc limit 1) as `purchase_order_id`, `bom_id`, `price_per_item`, `item_id`, `order_qty`, `active`, `flag`, `allocation`, `pr_part_id`, `complete_qty`, `modified_date`, `modified_by`, `created_date`, `created_by` FROM `fake_purchase_order_item` where purchase_order_id = " . $json['id'] . ")
			");
            $po_no = explode("-CANCELED", $d->po_no);
            $po_no = $po_no[0] . "-CANCELED";
            $model->db->query("update fake_purchase_order set 
			reject_note1 = null, 
			reject_note2 = null, 
			approval_by = null, 
			approval_2_by = null, 
			approval_2_date = null, 
			approval_date = null, 
			ask_approval_date = null, 
			approval_note = '', 
            approved='-2',
			ask_canceled_by='" . $user . "',
            ask_canceled_date='" . $date . "',
            ask_canceled_note='" . $json['ask_canceled_note'] . "',
             po_no = '" . $po_no . "' where id = " . $json['id'] . "");
            $response = [
                'status'   => true
            ];

            $msg = view('email/po_cancel', [
                'po_no' => $d->po_no,
                'title' => $d->title,
                'date' => $date,
                'ask_canceled_note' => $ask_canceled_notes,
                'rfq' => $d->rfq_no,
                'pr_no' => $d->pr_no,
                'supplier' => $d->supplier,
                'user' => $user,
            ]);
            helper(['Wa_helper']);
            $msgWa = preg_replace([
                '/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', //remove all style tags
                '/<(?:br|p|tr)[^>]*>/i', //replace br p with ' '
                '/<[^>]*>/',  //replace any tag with ''
                '/\s+/u', //remove run on space - replace using the unicode flag
                '/^\s+|\s+$/u', //trim - replace using the unicode flag
                '/\~nl\~/i'
            ], [
                '',
                "~nl~",
                '',
                ' ',
                '',
                "\r\n"
            ], $msg);
            // sendWa('6285640013092', $msgWa);
            sendWa('120363316928543400@g.us', $msgWa);
        } else {
            $response = [
                'status'   => false,
                'data'    => $d->approved != 3 ? 'PO is on process!' : 'You are not allowed to use this feature!'
            ];
        }
        return $this->respond($response);
    }

    public function sendpo()
    {
        try {
            $model = new PurchaseOrderModel();
            $session = session();
            $json = $_REQUEST;
            $s = $session->get();
            $date = date('Y-m-d', strtotime($json['send_po']));
            $model->db->query("update fake_purchase_order set 
				send_po_by = " . $s['userid'] . ",
				send_po = '" . $date . "'
				where id = " . $json['pk'] . "");
            $response = [
                'status'   => true,
                200
            ];
            return $this->respond($response);
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data' => $e->getMessage()], 200);
        }
    }

    public function revisi()
    {
        $model = new PurchaseOrderModel();
        $model1 = new UsersModel();
        $json = $_REQUEST;
        $q = $model->info($json['id']);
        $d = $q[1][0];
        $session = session();
        $s = $session->get();
        if ($d->approved == 3 && isset($s["userid"])) {
            $revBy = $s["userid"];
            $u = $model1->info($s["userid"]);
            if ($u[0]) {
                $v = $u[1][0];
                $user = $v->name;
            }
            $date = date("Y-m-d H:i:s");
            $rev_notes = $json['revisi_note'];


            if (isset($json->revisi_notes)) {
                $model->query("insert into fake_purchase_order_comment (purchase_order_id, notes, type, created_by) values(?, ?, ?, ?)", [$pk, $json->revisi_note, "Revisi Notes", $s["userid"]]);
            }

            $model->db->query("
			insert into fake_purchase_order_history (
				`revision_by`, `po_no`, `dept_id`, `po_date`, `order_type`, `supplier_id`, `promised_delivery_date`, `currency`, `exchange_rate`, `rate_date`, `final_quote_url`, `signed_po_url`, `signed_pr_url`, `no`, `year`, `charge1`, `charge2`, `charge1_desc`, `charge2_desc`, `note`, `title`, `flag`, `approved`, `created_date`, `is_complete`, `ref_offer_no`, `ref_internal_bmt`, `ask_approval_date`, `approval_date`, `approval_2_date`, `approval_2_by`, `approval_by`, `created_by`, `modified_date`, `modified_by`, `approval_note`, `new_po_no`, `reject_note1`, `reject_note2`, `other_charge`, `discount`, `payment_term`, `despatch`, `shipping_address`, `miscellaneous`, `eta_date`, `received_date`, `rev`, `purchase_order_id`, `revisi_note`
			)
			(SELECT " . $revBy . ", `po_no`, `dept_id`, `po_date`, `order_type`, `supplier_id`, `promised_delivery_date`, `currency`, `exchange_rate`, `rate_date`, `final_quote_url`, `signed_po_url`, `signed_pr_url`, `no`, `year`, `charge1`, `charge2`, `charge1_desc`, `charge2_desc`, `note`, `title`, `flag`, `approved`, `created_date`, `is_complete`, `ref_offer_no`, `ref_internal_bmt`, `ask_approval_date`, `approval_date`, `approval_2_date`, `approval_2_by`, `approval_by`, `created_by`, `modified_date`, `modified_by`, `approval_note`, `new_po_no`, `reject_note1`, `reject_note2`, `other_charge`, `discount`, `payment_term`, `despatch`, `shipping_address`, `miscellaneous`, `eta_date`, `received_date`, `rev`, `id`, ? as purchase_order_id FROM `fake_purchase_order` where id = " . $json['id'] . ")
			", [$json['revisi_note']]);

            $model->db->query("
			insert into fake_purchase_order_comment_history (
				`notes`, `purchase_order_id`, `created_date`, `created_by`, `type`
			)
			(SELECT `notes`, (select id from fake_purchase_order_history where purchase_order_id = " . $json['id'] . " order by id desc limit 1) as `purchase_order_id`, `created_date`, `created_by`, `type` FROM `fake_purchase_order_comment` where purchase_order_id = " . $json['id'] . ")
			");

            $model->db->query("
			insert into fake_purchase_order_item_history (
				`purchase_order_id`, `bom_id`, `price_per_item`, `item_id`, `order_qty`, `active`, `flag`, `allocation`, `pr_part_id`, `complete_qty`, `modified_date`, `modified_by`, `created_date`, `created_by`
			)
			(SELECT (select id from fake_purchase_order_history where purchase_order_id = " . $json['id'] . " order by id desc limit 1) as `purchase_order_id`, `bom_id`, `price_per_item`, `item_id`, `order_qty`, `active`, `flag`, `allocation`, `pr_part_id`, `complete_qty`, `modified_date`, `modified_by`, `created_date`, `created_by` FROM `fake_purchase_order_item` where purchase_order_id = " . $json['id'] . ")
			");
            $rev = number_format($d->rev, 0) + 1;
            $po_no = explode("-REV", $d->po_no);
            $po_no = $po_no[0] . "-REV." . sprintf('%02d', $rev);
            $model->db->query("update fake_purchase_order set 
			reject_note1 = null, 
			reject_note2 = null, 
			approval_by = null, 
			approval_2_by = null, 
			approval_2_date = null, 
			approval_date = null, 
			ask_approval_date = null, 
			send_po = null,
			approved = 0, 
			approval_note = '', 
			rev = " . $rev . ", po_no = '" . $po_no . "' where id = " . $json['id'] . "");
            $response = [
                'status'   => true
            ];
        } else {
            $response = [
                'status'   => false,
                'data'    => $d->approved != 3 ? 'PO not yet approved!' : 'You are not allowed to use this feature!'
            ];
        }
        return $this->respond($response);
    }

    function askapprovalrevisi()
    {
        $model = new PurchaseOrderModel();
        $json = $_REQUEST;

        $session = session();
        $s = $session->get();
        try {

            $data = ["ask_revision_approval_note" =>  $json["ask_approval_revision_note"], "ask_revision_approval_by" =>$s["userid"], 'revision_approval'=>1];
            $res = $model->where('id', $json["id"])
            ->set($data)
            ->update();

            $response = [
                'status'   => true,
                'data' => $res,
            ];
            return $this->respond($response);
        } catch (\Throwable $th) {
            $response = [
                'status'   => false,
                'data' => $th->getTraceAsString()
            ];
            return $this->respond($response);
        }
    }

    // create a product
    public function create()
    {
        $model = new PurchaseOrderModel();
        $json = $this->request->getJSON();
        $options = [
            'cost' => 11
        ];

        $data = [];

        foreach ($model->allowedFields as $value) {
            if (isset($json->{$value}))
                $data[$value] = $json->{$value};
        }

        $session = session();
        $s = $session->get();
        if (in_array("created_by", $model->allowedFields) && isset($s["userid"]))
            $data["created_by"] = $s["userid"];

        $y = date("Y");
        $m = date('m');

        $noQuery = $model->lastNumber($y, $data['dept_id']);
        if (!$noQuery[0])
            return $this->respondCreated(['status' => false, 'data' => $noQuery[1]], 200);
        $no = $noQuery[1][0]->no;
        $dept_code = $noQuery[1][0]->dept_code;
        if (!isset($data["po_no"])) {
            $data["po_no"] = "$no/" . $dept_code . "-PO/$m/$y";
            $data["year"] = $y;
            $data["no"] = $noQuery[1][0]->number;
        } else {
            $tmp = explode('/', $data["po_no"]);
            $data["year"] = $tmp[3];
            $data["no"] = str_pad($tmp[0], 4, "0", STR_PAD_LEFT);
        }

        $model->insert($data);
        $response = [
            'status'   => true,
            'data'    => 'Data Saved'
        ];

        return $this->respondCreated($response, 200);
    }

    public function insertInfo($info, $uid, $pk)
    {
        $model = new PaymentModel();
        //$data = $model->db->query("insert into info (info, uid, url) values('" . $info . "', '" . $uid . "', 'https://internal.buanamultiteknik.com/api/bom/purchase_order/info?id=" . $pk . "')");
    }

    public function complete()
    {
        $model = new PurchaseOrderModel();
        $model->db->query("update purchase_order set complete = 1 where id in (select a.purchase_order_id from (SELECT a.purchase_order_id, sum(a.order_qty) as order_qty, COALESCE(sum(b.qty), 0) as arrived_qty FROM fake_purchase_order_item a 
		left join fake_stock b on b.item_id = a.item_id and b.po_id = a.purchase_order_id
		left join fake_purchase_order c on c.id = a.purchase_order_id
		where a.flag = 1 and c.flag = 1 and c.complete = 0 group by a.purchase_order_id)a where a.order_qty = a.arrived_qty);");
        return $this->respondCreated(["status" => true], 201);
    }

    public function info()
    {
        $model = new PurchaseOrderModel();
        $json = $_REQUEST;
        $q = $model->info($json['id']);
        $text = $q[1];
        if ($q[0]) {
            $d = $q[1][0];
            $date = $d->approval_date;
            // 			if($d->approval_day<15 && ($d->supplier_id == 3 || $d->supplier_id == 8 || $d->supplier_id == 59 || $d->supplier_id == 11)){
            // 			    $tmp = explode('-', $date);
            // 			    $tmp[2] = explode(' ', $tmp[2]);
            // 			    $tmp[2][0] = 15;
            // 			    $tmp[2] = implode(' ', $tmp[2]);
            // 			    $date = implode('-', $tmp);
            // 			}
            $date2 = $d->po_date;
            // 			if($d->po_date_day<15 && ($d->supplier_id == 3 || $d->supplier_id == 8 || $d->supplier_id == 59 || $d->supplier_id == 11)){
            // 			    $tmp = explode('-', $date2);
            // 			    $tmp[2] = explode(' ', $tmp[2]);
            // 			    $tmp[2][0] = 15;
            // 			    $tmp[2] = implode(' ', $tmp[2]);
            // 			    $date2 = implode('-', $tmp);
            // 			}
            //ask_day
            $date3 = $d->ask_approval_date;
            // 			if($d->ask_day<15 && ($d->supplier_id == 3 || $d->supplier_id == 8 || $d->supplier_id == 59 || $d->supplier_id == 11)){
            // 			    $tmp = explode('-', $date2);
            // 			    $tmp[2] = explode(' ', $tmp[2]);
            // 			    $tmp[2][0] = 15;
            // 			    $tmp[2] = implode(' ', $tmp[2]);
            // 			    $date3 = implode('-', $tmp);
            // 			}
            $text = "
			a. PO No: " . $d->po_no . "<br/>
			b. PO Date: " . $date2 . "<br/>
			c. PO Title: " . $d->title . "<br/>
			d. PSupplier: " . $d->supplier . "<br/>
			e. Ask for approval date: " . $date3 . "<br/>
			f. Approved date: " . $date . "<br/>
			g. Approved by: " . $d->approved_by_name . "<br/>
			";
        }
        $response = [
            'status'   => $q[0],
            'data'    => $text
        ];

        return $this->respondCreated($response, 200);
    }

    public function update($pk = null)
    {
        try {



            $email = \Config\Services::email();
            $model = new PurchaseOrderModel();
            $model1 = new UsersModel();
            $json = $this->request->getJSON();
            if ($json) {

                $data = [];

                foreach ($json as $key => $value) {
                    if ($key != 'pk')
                        $data[$key] = $value;
                }
            }

            // return print_r($json);

            //return print_r($json);
            $session = session();
            $s = $session->get();
            if (in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
                $data["modified_by"] = $s["userid"];
            if (in_array("modified_date", $model->allowedFields))
                $data["modified_date"] = date("Y-m-d H:i:s");
            $supplier_id = -1;
            $po_id = -1;
            if (isset($data["approved"])) {
                $q = $model->info($pk);
                if ($data["approved"] == -1) {
                    if (in_array("rejected_by", $model->allowedFields) && isset($s["userid"])) {
                        $data["rejected_by"] = $s["userid"];
                        $u = $model1->info($data["rejected_by"]);
                        if ($u[0]) {
                            $v = $u[1][0];
                            $user = $v->name;
                        }
                    }
                    if (in_array("rejected_date", $model->allowedFields))
                        $data["rejected_date"] = date("Y-m-d H:i:s");
                    if (isset($json->reject_note1)) {
                        $rejected_note = $data['reject_note1'];
                    }
                    if (isset($json->reject_note2)) {
                        $rejected_note = $data['reject_note2'];
                    }

                    if ($q[0]) {
                        
                    }
                }

                if ($data["approved"] == 1) {

                    if (in_array("ask_approval_date", $model->allowedFields))
                        $data["ask_approval_date"] = date("Y-m-d H:i:s");
                }

                if ($data["approved"] == 2) {
                    // Check PO revision percentage difference
                    if (isset($data["po_no"])) {
                        $po_no = substr($data["po_no"], 0, 18);
                    }
                    $po = $model->getPo($data["po_no"]);
                    $porev = $model->getPoRev($po_no);

                    if (!empty($porev[1])) {
                        $persentase = (($porev[1][0]->total_in_idr - $po[1][0]->total_in_idr) / $porev[1][0]->total_in_idr) * 100;
                        if (abs($persentase) <= 5) {
                            $data["approved"] = 3;
                            $data["approval2_by"] = $s["userid"];
                            $data["approval2_date"] = date("Y-m-d H:i:s");
                        } else {
                            $data["approved"] = 2; //jika lebih dari 5% tidak bisa di approve langsung
                        }
                    } else $data["approved"] = 2; // jika tidak ada revisi, langsung approve 2



                    if (in_array("created_by", $model->allowedFields) && isset($s["userid"])) {
                        $data["approval_by"] = $s["userid"];
                        $u = $model1->info($data["approval_by"]);
                        if ($u[0]) {
                            $v = $u[1][0];
                            $user = $v->name;
                        }
                    }

                    if (in_array("approval_date", $model->allowedFields))
                        $data["approval_date"] = date("Y-m-d H:i:s");
                    // info kepada admin jika telah d aproval 1


                    if ($q[0]) {
                        
                    }
                }

                if ($data["approved"] == 3) {
                    
                }

                if ($data["approved"] == '-3') {
                    
                }

                if ($data["approved"] == '-4') {
                    
                }

                if ($data["approved"] == '5') {
                    
                }

                if ($data["approved"] == '6') {
                    
                }
            }

            //notes history
            if (isset($json->askapproval_note)) {
                $model->query("insert into fake_purchase_order_comment (purchase_order_id, notes, `type`, created_by) values(?, ?, ?, ?)", [$pk, $json->askapproval_note, "Asking Approval Notes", $s["userid"]]);
            }

            if (isset($json->approved)) {
                if ($json->approved == 2) {

                    $model->query("insert into fake_purchase_order_comment (purchase_order_id, notes, `type`, created_by) values(?, ?, ?, ?)", [$pk, $json->approval_note, "Approval 1 Notes", $s["userid"]]);
                }
                if ($json->approved == 5)
                    $model->query("insert into fake_purchase_order_comment (purchase_order_id, notes, `type`, created_by) values(?, ?, ?, ?)", [$pk, $json->ask_draft_note, "Ask Approval Draft Note", $s["userid"]]);

            }

            if (isset($json->reject_note1)) {

			}

            if (isset($json->reject_note2)) {

			}

            if (isset($data["revision_approval"])) {
            }

            // if(isset($json->ask_canceled_note)){
            // 	$model->query("insert into purchase_order_comment (purchase_order_id, notes, `type`, created_by) values(?, ?, ?, ?)", [$pk, $json->ask_canceled_note, "Canceled Asking 1 Notes", $s["userid"]]);
            // }

            // if(isset($json->canceled_note)){
            // 	$model->query("insert into purchase_order_comment (purchase_order_id, notes, `type`, created_by) values(?, ?, ?, ?)", [$pk, $json->canceled_note, "Canceled Approval 1 Notes", $s["userid"]]);
            // }

            // Insert to Database
            $model->update($pk, $data);

            if (isset($data["approved"])) {
                
            }

            $response = [
                'status'   => true,
                'data' => $data,
                'pk' => $pk
            ];
            return $this->respond($response);
        } catch (\Throwable $th) {
            $response = [
                'status'   => false,
                'data' => $th->getTraceAsString()
            ];
            return $this->respond($response);
        }
    }

    // delete product
    public function delete($pk = null)
    {
        $model = new PurchaseOrderModel();
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

    function filename_sanitizer($unsafeFilename)
    {

        // our list of "unsafe characters", add/remove characters if necessary
        $dangerousCharacters = array(" ", '"', "'", "&", "/", "\\", "?", "#");

        // every forbidden character is replaced by an underscore
        $safe_filename = str_replace($dangerousCharacters, '_', $unsafeFilename);

        return $safe_filename;
    }
}
