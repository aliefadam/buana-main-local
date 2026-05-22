<?php

namespace App\Controllers\Bom;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\PurchaseOrderModel;
use App\Models\PurchaseOrderItemModel;
use App\Models\UsersModel;
use App\Models\PaymentModel;

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

    public function titleoptions()
    {
        try {
            $model = new PurchaseOrderModel();
            $json = $_REQUEST;
            $json["filter"] = json_decode(json_encode($json["filter"] ?? '{}', true), true);
            $json = (object) $json;
            $filter = $json->filter ?? [];

            $builder = $model->db->table('po_subject_items psi');
            $builder->select('psi.id as item_id, psi.po_subject_id, psi.name as item_name, psi.description, ps.name as subject_name');
            $builder->join('po_subject ps', 'ps.id = psi.po_subject_id', 'left');
            $builder->groupStart()->where('ps.flag', 1)->orWhere('ps.flag', null)->groupEnd();
            $builder->groupStart()->where('psi.flag', 1)->orWhere('psi.flag', null)->groupEnd();

            if (!empty($filter['value'])) {
                $builder->where('psi.name', $filter['value']);
            } else if (!empty($filter['text'])) {
                $keyword = trim((string) $filter['text']);
                $builder->groupStart()
                    ->like('ps.name', $keyword)
                    ->orLike('psi.name', $keyword)
                    ->orLike('psi.description', $keyword)
                    ->groupEnd();
            }

            $rows = $builder
                ->orderBy('ps.name', 'ASC')
                ->orderBy('psi.name', 'ASC')
                ->get()
                ->getResult();

            $data = [];
            $activeParent = null;
            foreach ($rows as $row) {
                $parentName = trim((string) ($row->subject_name ?? ''));
                if ($parentName === '') {
                    $parentName = 'Others';
                }

                if ($activeParent !== $parentName) {
                    $activeParent = $parentName;
                    $data[] = [
                        'text' => $parentName,
                        'value' => '__group_' . ($row->po_subject_id ?? 'x'),
                        'disabled' => true,
                    ];
                }

                $itemName = trim((string) ($row->item_name ?? ''));
                if ($itemName === '') {
                    continue;
                }

                $description = trim((string) ($row->description ?? ''));
                $data[] = [
                    'text' => $description !== '' ? ($itemName . ' - ' . $description) : $itemName,
                    'value' => $itemName,
                    'po_subject_id' => (int) ($row->po_subject_id ?? 0),
                    'po_subject_item_id' => (int) ($row->item_id ?? 0),
                ];
            }

            return $this->respond([
                'status' => true,
                'data' => $data,
                'total' => count($data),
            ], 200);
        } catch (\Throwable $e) {
            return $this->respond(['status' => false, 'data' => $e->getMessage()], 200);
        }
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
                $model->query("insert into purchase_order_comment (purchase_order_id, notes, type, created_by) values(?, ?, ?, ?)", [$json->id, $json->ask_canceled_note, "Canceled Notes", $s["userid"]]);
            }

            $model->db->query("
			insert into purchase_order_history (
				`canceled_by`, `po_no`, `dept_id`, `po_date`, `order_type`, `supplier_id`, `promised_delivery_date`, `currency`, `exchange_rate`, `rate_date`, `final_quote_url`, `signed_po_url`, `signed_pr_url`, `no`, `year`, `charge1`, `charge2`, `charge1_desc`, `charge2_desc`, `note`, `title`, `flag`, `approved`, `created_date`, `is_complete`, `ref_offer_no`, `ref_internal_bmt`, `ask_approval_date`, `approval_date`, `approval_2_date`, `approval_2_by`, `approval_by`, `created_by`, `modified_date`, `modified_by`, `approval_note`, `new_po_no`, `reject_note1`, `reject_note2`, `other_charge`, `discount`, `payment_term`, `despatch`, `shipping_address`, `miscellaneous`, `eta_date`, `received_date`, `rev`, `purchase_order_id`
			)
			(SELECT " . $canceledBy . ", `po_no`, `dept_id`, `po_date`, `order_type`, `supplier_id`, `promised_delivery_date`, `currency`, `exchange_rate`, `rate_date`, `final_quote_url`, `signed_po_url`, `signed_pr_url`, `no`, `year`, `charge1`, `charge2`, `charge1_desc`, `charge2_desc`, `note`, `title`, `flag`, `approved`, `created_date`, `is_complete`, `ref_offer_no`, `ref_internal_bmt`, `ask_approval_date`, `approval_date`, `approval_2_date`, `approval_2_by`, `approval_by`, `created_by`, `modified_date`, `modified_by`, `approval_note`, `new_po_no`, `reject_note1`, `reject_note2`, `other_charge`, `discount`, `payment_term`, `despatch`, `shipping_address`, `miscellaneous`, `eta_date`, `received_date`, `rev`, `id` FROM `purchase_order` where id = " . $json['id'] . ")
			");

            $model->db->query("
			insert into purchase_order_comment_history (
				`notes`, `purchase_order_id`, `created_date`, `created_by`, `type`
			)
			(SELECT `notes`, (select id from purchase_order_history where purchase_order_id = " . $json['id'] . " order by id desc limit 1) as `purchase_order_id`, `created_date`, `created_by`, `type` FROM `purchase_order_comment` where purchase_order_id = " . $json['id'] . ")
			");

            $model->db->query("
			insert into purchase_order_item_history (
				`purchase_order_id`, `bom_id`, `price_per_item`, `item_id`, `order_qty`, `active`, `flag`, `allocation`, `pr_part_id`, `complete_qty`, `modified_date`, `modified_by`, `created_date`, `created_by`
			)
			(SELECT (select id from purchase_order_history where purchase_order_id = " . $json['id'] . " order by id desc limit 1) as `purchase_order_id`, `bom_id`, `price_per_item`, `item_id`, `order_qty`, `active`, `flag`, `allocation`, `pr_part_id`, `complete_qty`, `modified_date`, `modified_by`, `created_date`, `created_by` FROM `purchase_order_item` where purchase_order_id = " . $json['id'] . ")
			");
            $po_no = explode("-CANCELED", $d->po_no);
            $po_no = $po_no[0] . "-CANCELED";
            $model->db->query("update purchase_order set 
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
            $model->db->query("update purchase_order set 
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
                $model->query("insert into purchase_order_comment (purchase_order_id, notes, type, created_by) values(?, ?, ?, ?)", [$pk, $json->revisi_note, "Revisi Notes", $s["userid"]]);
            }

            $model->db->query("
			insert into purchase_order_history (
				`revision_by`, `po_no`, `dept_id`, `po_date`, `order_type`, `supplier_id`, `promised_delivery_date`, `currency`, `exchange_rate`, `rate_date`, `final_quote_url`, `signed_po_url`, `signed_pr_url`, `no`, `year`, `charge1`, `charge2`, `charge1_desc`, `charge2_desc`, `note`, `title`, `flag`, `approved`, `created_date`, `is_complete`, `ref_offer_no`, `ref_internal_bmt`, `ask_approval_date`, `approval_date`, `approval_2_date`, `approval_2_by`, `approval_by`, `created_by`, `modified_date`, `modified_by`, `approval_note`, `new_po_no`, `reject_note1`, `reject_note2`, `other_charge`, `discount`, `payment_term`, `despatch`, `shipping_address`, `miscellaneous`, `eta_date`, `received_date`, `rev`, `purchase_order_id`, `revisi_note`
			)
			(SELECT " . $revBy . ", `po_no`, `dept_id`, `po_date`, `order_type`, `supplier_id`, `promised_delivery_date`, `currency`, `exchange_rate`, `rate_date`, `final_quote_url`, `signed_po_url`, `signed_pr_url`, `no`, `year`, `charge1`, `charge2`, `charge1_desc`, `charge2_desc`, `note`, `title`, `flag`, `approved`, `created_date`, `is_complete`, `ref_offer_no`, `ref_internal_bmt`, `ask_approval_date`, `approval_date`, `approval_2_date`, `approval_2_by`, `approval_by`, `created_by`, `modified_date`, `modified_by`, `approval_note`, `new_po_no`, `reject_note1`, `reject_note2`, `other_charge`, `discount`, `payment_term`, `despatch`, `shipping_address`, `miscellaneous`, `eta_date`, `received_date`, `rev`, `id`, ? as purchase_order_id FROM `purchase_order` where id = " . $json['id'] . ")
			", [$json['revisi_note']]);

            $model->db->query("
			insert into purchase_order_comment_history (
				`notes`, `purchase_order_id`, `created_date`, `created_by`, `type`
			)
			(SELECT `notes`, (select id from purchase_order_history where purchase_order_id = " . $json['id'] . " order by id desc limit 1) as `purchase_order_id`, `created_date`, `created_by`, `type` FROM `purchase_order_comment` where purchase_order_id = " . $json['id'] . ")
			");

            $model->db->query("
			insert into purchase_order_item_history (
				`purchase_order_id`, `bom_id`, `price_per_item`, `item_id`, `order_qty`, `active`, `flag`, `allocation`, `pr_part_id`, `complete_qty`, `modified_date`, `modified_by`, `created_date`, `created_by`
			)
			(SELECT (select id from purchase_order_history where purchase_order_id = " . $json['id'] . " order by id desc limit 1) as `purchase_order_id`, `bom_id`, `price_per_item`, `item_id`, `order_qty`, `active`, `flag`, `allocation`, `pr_part_id`, `complete_qty`, `modified_date`, `modified_by`, `created_date`, `created_by` FROM `purchase_order_item` where purchase_order_id = " . $json['id'] . ")
			");
            $rev = number_format($d->rev, 0) + 1;
            $po_no = explode("-REV", $d->po_no);
            $po_no = $po_no[0] . "-REV." . sprintf('%02d', $rev);
            $model->db->query("update purchase_order set 
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

            $msg = view('email/po_revisi', [
                'po_no' => $d->po_no,
                'title' => $d->title,
                'date' => $date,
                'rfq' => $d->rfq_no,
                'pr_no' => $d->pr_no,
                'supplier' => $d->supplier,
                'user' => $user,
                'revised_notes' => $rev_notes,
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
            // sendWa('62811309386', $msgWa);
            sendWa('120363316928543400@g.us', $msgWa);
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

            $data = ["ask_revision_approval_note" =>  $json["ask_approval_revision_note"], "ask_revision_approval_by" =>$s["userid"],'revision_approval'=>1];
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
        $data = $model->db->query("insert into info (info, uid, url) values('" . $info . "', '" . $uid . "', 'https://main.buanamultiteknik.com/api/bom/purchase_order/info?id=" . $pk . "')");
    }

    public function complete()
    {
        $model = new PurchaseOrderModel();
        $model->db->query("update purchase_order set complete = 1 where id in (select a.purchase_order_id from (SELECT a.purchase_order_id, sum(a.order_qty) as order_qty, COALESCE(sum(b.qty), 0) as arrived_qty FROM purchase_order_item a 
		left join stock b on b.item_id = a.item_id and b.po_id = a.purchase_order_id
		left join purchase_order c on c.id = a.purchase_order_id
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
                        $d = $q[1][0];
                        $ref = explode('//', $d->ref_internal_bmt ?? "");
                        $msg = view('email/po_rejected', [
                            'po_no' => $d->po_no,
                            'title' => $d->title,
                            'rfq' => $d->rfq_no,
                            'pr_no' => $d->pr_no,
                            'supplier' => $d->supplier,
                            'user' => $user,
                            'date' => $data["rejected_date"],
                            'rejected_notes' => $rejected_note,
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
                        // sendWa('62811309386', $msgWa);
                        sendWa('120363316928543400@g.us', $msgWa);
                    }
                }

                if ($data["approved"] == 1) {


                    //email ask approval 1

                    // if($q[0]){
                    // 	$d = $q[1][0];
                    // 	$email->setFrom('internal@buanamultiteknik.com', 'Internal');
                    // 	$email->setTo('fenty@buanamultiteknik.com');
                    // 	$ref = explode('//', $d->ref_internal_bmt ?? "");
                    // 	$msg = view('email/ask_approval',[
                    // 		'approval' => 1,
                    // 		'po_no' => $d->po_no,
                    // 		'title' => $d->title,
                    // 		'rfq' => $ref[0]??"-",
                    // 		'pr_no' => $ref[1]??"-"
                    // 	]);
                    // 	$email->setSubject("Ask Approval 1 ".$d->po_no);
                    // 	$email->setMessage($msg);
                    // 	$email->setMailType('html');

                    // 	$email->send();
                    // 	helper(['Wa_helper']);
                    // 	// //send wa ke approval 1
                    // 	$msgWa = preg_replace([
                    //         '/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', //remove all style tags
                    //         '/<(?:br|p|tr)[^>]*>/i', //replace br p with ' '
                    // 		'/<[^>]*>/',  //replace any tag with ''
                    // 		'/\s+/u', //remove run on space - replace using the unicode flag
                    // 		'/^\s+|\s+$/u', //trim - replace using the unicode flag
                    // 		'/\~nl\~/i'
                    //     ],[
                    //         '', "~nl~", '', ' ', '', "\r\n"
                    // 	], $msg);
                    // 	sendWa('62811309386', $msgWa);
                    // }

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
                        $d = $q[1][0];
                        //email info approved 1
                        $email->setFrom('internal@buanamultiteknik.com', 'Internal');
                        $email->setTo('fenty@buanamultiteknik.com, titik@buanamultiteknik.com');
                        $ref = explode('//', $d->ref_internal_bmt ?? "");
                        $msg = view('email/approved', [
                            'approval' => 1,
                            'date' => $data["approval_date"],
                            'po_no' => $d->po_no,
                            'title' => $d->title,
                            'rfq' => $d->rfq_no,
                            'pr_no' => $d->pr_no,
                            'supplier' => $d->supplier,
                            'user' => $user,
                        ]);
                        $email->setSubject("PO " . $d->po_no . " Approved by approval 1");
                        $email->setMailType('html');
                        $email->setMessage(nl2br($msg));
                        $email->setMailType('html');

                        // $email->send();

                        // helper(['Wa_helper']);
                        // //send wa ke admin
                        // $msgWa = preg_replace([
                        //     '/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', //remove all style tags
                        //     '/<(?:br|p|tr)[^>]*>/i', //replace br p with ' '
                        // 	'/<[^>]*>/',  //replace any tag with ''
                        // 	'/\s+/u', //remove run on space - replace using the unicode flag
                        // 	'/^\s+|\s+$/u', //trim - replace using the unicode flag
                        // 	'/\~nl\~/i'
                        // ],[
                        //     '', "~nl~", '', ' ', '', "\r\n"
                        // ], $msg);
                        //send ke admin
                        //sendWa('628113013485', $msgWa);

                        // email ask approval 2
                        /*$email->clear();
    					$email->setFrom('internal@buanamultiteknik.com', 'Internal');
    					$email->setTo('fenty@buanamultiteknik.com');*/
                        // $ref = explode('//', $d->ref_internal_bmt ?? "");
                        // $msg = view('email/ask_approval',[
                        // 	'approval' => 2,
                        // 	'po_no' => $d->po_no,
                        // 	'title' => $d->title,
                        // 	'rfq' => $ref[0]??"-",
                        // 	'pr_no' => $ref[1]??"-"
                        // ]);
                        // /*$email->setSubject("Ask Approval 2 ".$d->po_no);
                        // $email->setMessage($msg);
                        // $email->setMailType('html');
                        // $email->send();*/
                        // helper(['Wa_helper']);
                        // //send wa ke approval 2
                        // $msgWa = preg_replace([
                        //     '/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', //remove all style tags
                        //     '/<(?:br|p|tr)[^>]*>/i', //replace br p with ' '
                        // 	'/<[^>]*>/',  //replace any tag with ''
                        // 	'/\s+/u', //remove run on space - replace using the unicode flag
                        // 	'/^\s+|\s+$/u', //trim - replace using the unicode flag
                        // 	'/\~nl\~/i'
                        // ],[
                        //     '', "~nl~", '', ' ', '', "\r\n"
                        // ], $msg);
                        //sendWa('6285640013092', $msgWa);
                        //$this->insertInfo("<b>Approved:</b> ".date("Y-m-d H:i:s")."", "po_approved_".$pk, $pk);
                    }
                }

                if ($data["approved"] == 3) {
                    if (in_array("created_by", $model->allowedFields) && isset($s["userid"])) {
                        $data["approval_2_by"] = $s["userid"];
                        $u = $model1->info($data["approval_2_by"]);
                        if ($u[0]) {
                            $v = $u[1][0];
                            $user = $v->name;
                        }
                    }

                    if (in_array("approval_2_date", $model->allowedFields))
                        $data["approval_2_date"] = date("Y-m-d H:i:s");

                    $this->insertInfo("<b>Approved:</b> " . date("Y-m-d H:i:s") . "", "po_approved_" . $pk, $pk);

                    if ($q[0]) {
                        $d = $q[1][0];
                        $supplier_id = $d->supplier_id;
                        $po_id = $pk;
                        $email->setFrom('internal@buanamultiteknik.com', 'Internal');
                        $email->setTo('fenty@buanamultiteknik.com, titik@buanamultiteknik.com');
                        $ref = explode('//', $d->ref_internal_bmt ?? "");
                        $msg = view('email/approved', [
                            'approval' => 2,
                            'date' => $data["approval_2_date"],
                            'po_no' => $d->po_no,
                            'title' => $d->title,
                            'rfq' => $d->rfq_no,
                            'pr_no' => $d->pr_no,
                            'supplier' => $d->supplier,
                            'user' => $user,
                        ]);
                        $email->setSubject("PO " . $d->po_no . " Approved by approval 2");
                        $email->setMailType('html');
                        $email->setMessage(nl2br($msg));

                        // $email->send();
                        helper(['Wa_helper']);
                        //send wa ke admin
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
                        sendWa('120363316928543400@g.us', $msgWa);
                        // sendWa('628113013485', $msgWa);
                    }
                }

                if ($data["approved"] == '-3') {
                    if (in_array("canceled_by", $model->allowedFields)) {
                        $data["canceled_by"] = $s["userid"];
                        $u = $model1->info($data["canceled_by"]);
                        if ($u[0]) {
                            $v = $u[1][0];
                            $user = $v->name;
                        }
                    }
                    if (in_array("canceled_date", $model->allowedFields))
                        $data["canceled_date"] = date("Y-m-d H:i:s");
                    if (in_array("approval_note", $model->allowedFields))
                        $data["canceled_note"] = $data["approval_note"];
                }

                if ($data["approved"] == '-4') {
                    if (in_array("canceled_2_by", $model->allowedFields)) {
                        $data["canceled_2_by"] = $s["userid"];
                        $u = $model1->info($data["canceled_2_by"]);
                        if ($u[0]) {
                            $v = $u[1][0];
                            $user = $v->name;
                        }
                    }
                    if (in_array("canceled_2_date", $model->allowedFields))
                        $data["canceled_2_date"] = date("Y-m-d H:i:s");

                    $this->insertInfo("<b>Canceled:</b> " . date("Y-m-d H:i:s") . "", "po_canceled_" . $pk, $pk);
                    if ($q[0]) {
                        $d = $q[1][0];
                        $ref = explode('//', $d->ref_internal_bmt ?? "");
                        $msg = view('email/po_canceled', [
                            'approval' => 1,
                            'po_no' => $d->po_no,
                            'title' => $d->title,
                            'date' => $data["canceled_2_date"],
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
                        // sendWa('62811309386', $msgWa);
                        sendWa('120363316928543400@g.us', $msgWa);
                    }
                }

                if ($data["approved"] == '5') {
                    if (in_array("ask_draft_by", $model->allowedFields)) {
                        $data["ask_draft_by"] = $s["userid"];
                        $u = $model1->info($data["ask_draft_by"]);
                        if ($u[0]) {
                            $v = $u[1][0];
                            $user = $v->name;
                        }
                    }
                    if (in_array("ask_draft_date", $model->allowedFields))
                        $data["ask_draft_date"] = date("Y-m-d H:i:s");
                    if (in_array("ask_draft_note", $model->allowedFields))
                        $data["ask_draft_note"] = $data["ask_draft_note"];
                }

                if ($data["approved"] == '6') {
                    if (in_array("approval_draft_by", $model->allowedFields)) {
                        $data["approval_draft_by"] = $s["userid"];
                        $u = $model1->info($data["approval_draft_by"]);
                        if ($u[0]) {
                            $v = $u[1][0];
                            $user = $v->name;
                        }
                    }
                    if (in_array("approval_draft_date", $model->allowedFields))
                        $data["approval_draft_date"] = date("Y-m-d H:i:s");

                    $this->insertInfo("<b>Draft Approved:</b> " . date("Y-m-d H:i:s") . "", "po_draft_approved_" . $pk, $pk);
                    if ($q[0]) {
                        $d = $q[1][0];
                        $supplier_id = $d->supplier_id;
                        $po_id = $pk;
                        $ref = explode('//', $d->ref_internal_bmt ?? "");
                        $msg = view('email/po_drafted', [
                            'po_no' => $d->po_no,
                            'title' => $d->title,
                            'rfq' => $d->rfq_no,
                            'pr_no' => $d->pr_no,
                            'supplier' => $d->supplier,
                            'date' => $data["approval_draft_date"],
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
                        // sendWa('62811309386', $msgWa);
                        sendWa('120363316928543400@g.us', $msgWa);
                    }
                }
            }

            //notes history
            if (isset($json->askapproval_note)) {
                $model->query("insert into purchase_order_comment (purchase_order_id, notes, `type`, created_by) values(?, ?, ?, ?)", [$pk, $json->askapproval_note, "Asking Approval Notes", $s["userid"]]);
            }

            if (isset($json->approved)) {
                if ($json->approved == 2) {

                    $model->query("insert into purchase_order_comment (purchase_order_id, notes, `type`, created_by) values(?, ?, ?, ?)", [$pk, $json->approval_note, "Approval 1 Notes", $s["userid"]]);
                }
                if ($json->approved == 5)
                    $model->query("insert into purchase_order_comment (purchase_order_id, notes, `type`, created_by) values(?, ?, ?, ?)", [$pk, $json->ask_draft_note, "Ask Approval Draft Note", $s["userid"]]);

                /*if($json->approved==3)
    				$model->query("insert into purchase_order_comment (purchase_order_id, notes, `type`, created_by) values(?, ?, ?, ?)", [$pk, $json->approval_note, "Approval 2 Notes", $s["userid"]]);*/
            }

            if (isset($json->reject_note1)) {
                $data["approval_by"] = null;
                $data["approval_2_by"] = null;
                $data["approval_2_date"] = null;
                $data["approval_date"] = null;
                $model->query("insert into purchase_order_comment (purchase_order_id, notes, `type`, created_by) values(?, ?, ?, ?)", [$pk, $json->reject_note1, "Reject Approval 1 Notes", $s["userid"]]);
            }

            if (isset($json->reject_note2)) {
                $data["approval_by"] = null;
                $data["approval_2_by"] = null;
                $data["approval_2_date"] = null;
                $data["approval_date"] = null;
                $model->query("insert into purchase_order_comment (purchase_order_id, notes, `type`, created_by) values(?, ?, ?, ?)", [$pk, $json->reject_note2, "Reject Approval 2 Notes", $s["userid"]]);
            }

            if (isset($data["revision_approval"])) {

                if ($data["revision_approval"] == 2) {
                    $model->query("insert into purchase_order_comment (purchase_order_id, notes, `type`, created_by) values(?, ?, ?, ?)", [$pk, $json->approval_revision_note, "Approval Revision Notes (2)", $s["userid"]]);


                    // return print_r([$model->allowedFields,$json->approval_revision_note,$data]);
                    if (in_array("revision_note", $model->allowedFields))
                        $data["revision_note"] = $json->approval_revision_note;
                        $data["revision_approval_by"] = $s['userid'];
                }
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
                if ($data["approved"] == 3) {
                    if ($supplier_id == 3 || $supplier_id == 8 || $supplier_id == 59)
                        $this->autoemailsupplierfrompo($supplier_id, $po_id, false);
                }
                if ($data["approved"] == 6) {
                    if ($supplier_id == 3 || $supplier_id == 8 || $supplier_id == 59)
                        $this->autoemailsupplierfrompo($supplier_id, $po_id, false);
                    //$this->autoemailsupplierfrompo(8, 1650, true);
                }
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

    public function emailsupplier()
    {
        $model = new PurchaseOrderModel();
        $json = $this->request->getJSON();
        //return $this->respond(["a"=>$json->cc]);
        $result = $model->db->query("select * from m_supplier where id = " . $json->supplier_id);
        $po = $model->db->query("select * from purchase_order where id = " . $json->po_id);
        $po = $po->getResult()[0];
        $msg = $result->getResult()[0]->salutation;
        $msg = str_replace("{PO No}", $po->po_no, $msg);
        $msg = trim(str_replace("{Title PO}", $po->title, $msg));
        //return $this->respond(["a"=>$msg, "b"=> implode(",", explode(";;",$json->to)), "c"=> implode(",", explode(";;",$json->cc))]);
        try {
            $session = session();
            $s = $session->get();
            $email = \Config\Services::email();
            $email->setFrom('internal@buanamultiteknik.com', 'Internal');
            $email->setBCC('internal@buanamultiteknik.com,fenty@buanamultiteknik.com');
            //$email->setTo('fenty@buanamultiteknik.com');
            //email asli to
            $email->setTo(implode(",", explode(";;", $json->to)));
            //email asli cc
            $email->setCc(implode(",", explode(";;", $json->cc)));
            //$ref = explode('//', $d->ref_internal_bmt ?? "");
            /* $msg = view('email/supplier',[
				'approval' => 1,
				'date' => $data["approval_date"],
				'po_no' => $d->po_no,
				'title' => $d->title,
				'rfq' => $ref[0]??"-",
				'pr_no' => $ref[1]??"-",
				'sender' => $ref[1]??"-"
			]); */
            //get attachment
            // if($po->signed_po_url!="" && $po->signed_po_url!=null){
            // 	//return $this->respond(["a"=>pathinfo($po->signed_po_url, PATHINFO_EXTENSION )]);
            // 	$f = $this->filename_sanitizer($po->po_no);
            // 	$f = $f.".".pathinfo($po->signed_po_url, PATHINFO_EXTENSION );
            // 	$file_name = "./download/".$f;
            // 	file_put_contents($file_name, file_get_contents($po->signed_po_url));
            // 	$email->attach($file_name);
            // }
            if (strlen($po->po_no) > 18) $email->setSubject("Revision/Cancelation PO " . $po->po_no . " - " . $po->title);
            $email->setSubject("Signed PO " . $po->po_no . " - " . $po->title);
            $email->setMailType('html');
            $email->setMessage(nl2br($msg));
            //$email->setMailType('html');

            $email->send();
            if ($po->signed_po_url != "" && $po->signed_po_url != null) {
                unlink($file_name);
            }
            $response = [
                'status'   => true
            ];

            return $this->respond($response);
        } catch (\Throwable $e) {
            $response = [
                'status'   => false,
                "message" => $e
            ];

            return $this->respond($response);
        }
    }

    public function testemail()
    {
        $this->autoemailsupplierfrompo(316, 5, true);
        $response = [
            'status'   => true
        ];

        return $this->respond($response);
    }

    public function fakedata()
    {
        $model = new PurchaseOrderModel();
		try{
       		$json = $this->request->getJSON();
            //return $this->respond(['status' => false, 'data' => $json], 200);
			$model->db->query("
			insert ignore into fake_purchase_order (
				`canceled_by`, `po_no`, `dept_id`, `po_date`, `order_type`, `supplier_id`, `promised_delivery_date`, `currency`, `exchange_rate`, `rate_date`, `final_quote_url`, `signed_po_url`, `signed_pr_url`, `no`, `year`, `charge1`, `charge2`, `charge1_desc`, `charge2_desc`, `note`, `title`, `flag`, `approved`, `created_date`, `is_complete`, `ref_offer_no`, `ref_internal_bmt`, `ask_approval_date`, `approval_date`, `approval_2_date`, `approval_2_by`, `approval_by`, `created_by`, `modified_date`, `modified_by`, `approval_note`, `new_po_no`, `reject_note1`, `reject_note2`, `other_charge`, `discount`, `payment_term`, `despatch`, `shipping_address`, `miscellaneous`, `eta_date`, `received_date`, `rev`, `id`
			)
			(SELECT `canceled_by`, `po_no`, `dept_id`, `po_date`, `order_type`, `supplier_id`, `promised_delivery_date`, `currency`, `exchange_rate`, `rate_date`, `final_quote_url`, `signed_po_url`, `signed_pr_url`, `no`, `year`, `charge1`, `charge2`, `charge1_desc`, `charge2_desc`, `note`, `title`, `flag`, `approved`, `created_date`, `is_complete`, `ref_offer_no`, `ref_internal_bmt`, `ask_approval_date`, `approval_date`, `approval_2_date`, `approval_2_by`, `approval_by`, `created_by`, `modified_date`, `modified_by`, `approval_note`, `new_po_no`, `reject_note1`, `reject_note2`, `other_charge`, `discount`, `payment_term`, `despatch`, `shipping_address`, `miscellaneous`, `eta_date`, `received_date`, `rev`, `id` FROM `purchase_order` where id = " . $json->po_id . ")
			");

			$model->db->query("
				insert into purchase_order_comment_history (
					`notes`, `purchase_order_id`, `created_date`, `created_by`, `type`
				)
				(SELECT `notes`, `purchase_order_id`, `created_date`, `created_by`, `type` FROM `purchase_order_comment` where purchase_order_id = " . $json->po_id . ")
			");

			$model->db->query("
			insert into fake_purchase_order_item (
				`purchase_order_id`, `bom_id`, `price_per_item`, `item_id`, `order_qty`, `active`, `flag`, `allocation`, `pr_part_id`, `complete_qty`, `modified_date`, `modified_by`, `created_date`, `created_by`
			)
			(SELECT `purchase_order_id`, `bom_id`, `price_per_item`, `item_id`, `order_qty`, `active`, `flag`, `allocation`, `pr_part_id`, `complete_qty`, `modified_date`, `modified_by`, `created_date`, `created_by` FROM `purchase_order_item` where purchase_order_id = " . $json->po_id . ")
			");
			$response = [
				'status'   => true
			];

			return $this->respond($response);
        } catch (Throwable $e) {
            return $this->respond(['status' => false, 'data' => $e->getMessage()], 200);
        }
    }

    public function autoemailsupplierfrompo($supplier_id, $po_id, $debug)
    {
        $model = new PurchaseOrderModel();
        //$json = $this->request->getJSON();
        //return $this->respond(["a"=>$json->cc]);
        $result = $model->db->query("select * from m_supplier where id = " . $supplier_id);
        $po = $model->db->query("select * from purchase_order where id = " . $po_id);
        $po = $po->getResult()[0];
        $msg = $result->getResult()[0]->salutation;
        $to = $result->getResult()[0]->email;
        $to = preg_replace('/\s+/', '', $to);
        $to = preg_replace('/\;$/', '', $to);
        $to = explode(';', $to);
        $msg = str_replace("{PO No}", $po->po_no, $msg);
        $msg = trim(str_replace("{Title PO}", $po->title, $msg));

        //return $this->respond(["a"=>$msg, "b"=> implode(",", explode(";;",$json->to)), "c"=> implode(",", explode(";;",$json->cc))]);
        try {
            $session = session();
            $s = $session->get();
            $email = \Config\Services::email();
            $email->setFrom('internal@buanamultiteknik.com', 'Internal');
            // $email->setCc('lindaduan@suliya.cn');
            $email->setBCC('internal@buanamultiteknik.com', 'fenty@buanamultiteknik.com');
            if ($debug) {
                $email->setTo('satrio@buanamultiteknik.com');
                //  $email->setTo('claudiachen@suliya.cn');
                //  $email->setCc('lindaduan@suliya.cn');
                //  $email->setCc('titik@buanamultiteknik.com');
            } else {
                //email asli to
                $email->setTo($to[0]);
                //email asli cc
                $email->setCc(implode(",", array_slice($to, 1)));
            }

            //get attachment
            //if($po->signed_po_url!="" && $po->signed_po_url!=null){
            $name = $this->filename_sanitizer($po->po_no);
            $url = 'https://main.buanamultiteknik.com/api/data/reportpo2?id=' . $po_id . '&filename=' . $name . '&idx=' . uniqid();
            $msg = trim(str_replace("{Document PO}", $url, $msg));
            $f = $name . ".pdf";
            $file_name = "./download/" . $f;
            file_put_contents($file_name, file_get_contents($url));
            $email->attach($file_name);
            // }

            //return print_r($po);
            if ($po->approved == -4) {
                $email->setSubject("CANCELATION PO " . $po->po_no . " - " . $po->title);
                $type = 'Canceled';
                $msg = trim(str_replace("{Type PO}", $type, $msg));
            };
            if ($po->approved == 3) {
                if (strlen($po->po_no) > 18) {
                    $email->setSubject("REVISION PO " . $po->po_no . " - " . $po->title);
                    $type = 'Signed';
                    $msg = trim(str_replace("{Type PO}", $type, $msg));
                } else {
                    $email->setSubject("Signed PO " . substr($po->po_no, 0, 18) . " - " . $po->title);
                    $type = 'Signed';
                    $msg = trim(str_replace("{Type PO}", $type, $msg));
                }
                // $email->setSubject("Signed PO ".$po->po_no." - ".$po->title);
                // $type='Signed';
                // $msg=trim(str_replace("{Type PO}", $type, $msg));
            };
            if ($po->approved == 6) {
                $email->setSubject("Draft PO " . $po->po_no . " - " . $po->title);
                $type = 'Draft';
                $msg = trim(str_replace("{Type PO}", $type, $msg));
            };
            $email->setMailType('html');
            $email->setMessage(nl2br($msg));
            //$email->setMailType('html');

            $email->send();
            if ($po->signed_po_url != "" && $po->signed_po_url != null) {
                unlink($file_name);
            }
            /*$response = [
                'status'   => true
            ];*/

            return true; //$this->respond($response);
        } catch (\Throwable $e) {
            /*$response = [
                'status'   => false,
				"message" => $e
            ];*/

            var_dump($e);

            return false; //$this->respond($response);
        }
    }

    public function autoemailsupplier()
    {
        $model = new PurchaseOrderModel();
        $json = $this->request->getJSON();
        //return $this->respond(["a"=>$json->cc]);
        $result = $model->db->query("select * from m_supplier where id = " . $json->supplier_id);
        $po = $model->db->query("select * from purchase_order where id = " . $json->po_id);
        $po = $po->getResult()[0];
        $msg = $result->getResult()[0]->salutation;
        $to = $result->getResult()[0]->email;
        $to = preg_replace('/\s+/', '', $to);
        $to = preg_replace('/\;$/', '', $to);
        $to = explode(';', $to);
        $msg = str_replace("{PO No}", $po->po_no, $msg);
        $msg = trim(str_replace("{Title PO}", $po->title, $msg));
        //return $this->respond(["a"=>$msg, "b"=> implode(",", explode(";;",$json->to)), "c"=> implode(",", explode(";;",$json->cc))]);
        try {
            $session = session();
            $s = $session->get();
            $email = \Config\Services::email();
            $email->setFrom('internal@buanamultiteknik.com', 'Internal');
            $email->setBCC('internal@buanamultiteknik.com,fenty@buanamultiteknik.com');
            $email->setTo('fenty@buanamultiteknik.com');
            //email asli to
            //$email->setTo($to[0]);
            //email asli cc
            //$email->setCc(array_slice($to, 1));

            //get attachment
            if ($po->signed_po_url != "" && $po->signed_po_url != null) {
                $name = $this->filename_sanitizer($po->po_no);
                //$url = 'https://decafet.com/api/report?type=pdf&file=po&filename=' . $name . '&engine=easytemplate&po_id=' . $json->po_id;
                $f = $name . ".pdf";
                $file_name = "./download/" . $f;
                $url = "http://103.171.84.71/api/report?type=pdf&file=po&filename=" . $name . "&engine=easytemplate&po_id=" . $json->po_id . "&idx=" . uniqid();
                //$file = "./download/".$json['filename'].".pdf";
                $ch = curl_init();
                //Save content from string to .html file.
                $fp = fopen($file_name, 'wb');
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                //curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_exec($ch);
                // Closes a cURL session and frees all resources 
                curl_close($ch);
                // Close file 
                fclose($fp);
                //file_put_contents($file_name, file_get_contents($url));
                $email->attach($file_name);
            }
            $email->setSubject("Replacement Email for Signed PO " . $po->po_no . " - " . $po->title);
            $email->setMailType('html');
            $email->setMessage(nl2br($msg));
            //$email->setMailType('html');

            $email->send();
            if ($po->signed_po_url != "" && $po->signed_po_url != null) {
                unlink($file_name);
            }
            /*$response = [
                'status'   => true
            ];*/

            return true; //$this->respond($response);
        } catch (\Throwable $e) {
            /*$response = [
                'status'   => false,
				"message" => $e
            ];*/

            return false; //$this->respond($response);
        }
    }

    // cancel product
    public function cancel1()
    {
        //select * from invoice i left join payment_item pi on pi.invoice_id = i.id left join payment p on p.id = pi.payment_id where p.flag = 1 and pi.flag = 1
        $model = new PurchaseOrderModel();
        $json = $_REQUEST;
        $json = (object) $json;
        $session = session();
        $s = $session->get();
        if (!isset($s["userid"])) {
            $response = [
                'status'   => false,
                "message" => "Anda tidak mempunyai akses!"
            ];
        } else {
            $data = $model->db->query("select i.id from invoice i left join payment_item pi on pi.invoice_id = i.id left join payment p on p.id = pi.payment_id where p.flag = 1 and pi.flag = 1 and i.po_id = " . $json->pk);
            $data = $data->getResult();
            if (count($data) > 0) {
                $response = [
                    'status'   => false,
                    "message" => "PO telah mempunyai payment!"
                ];
            } else {
                $model->update($pk, ["flag" => -4, "canceled_by" => $s["userid"], "canceled_date" => date("Y-m-d H:i:s"), "canceled_note" => $json->canceled_note]);
                $model->db->query("update invoice set flag = -4 where po_id = " . $json->pk);
                $response = [
                    'status'   => true
                ];
            }
        }

        return $this->respond($response);
    }

    public function total_po()
    {
        $model = new PurchaseOrderModel();
        $res = $model->db->query("
			select (select count(po.po_no)  from purchase_order po where po.approved=0 and po.flag=1) as po_new, (select count(po.po_no)  from purchase_order po where (po.approved=1 or po.approved=2) and po.flag=1) as po_pending, (select count(po.po_no)  from purchase_order po where po.approved=3 and po.flag=1) as po_approved, (select count(po.po_no) from purchase_order po where po.approved=-1 and po.flag=1) as po_rejected, (select count(po.po_no) from purchase_order po where po.complete=1 and po.flag=1) as po_completed ;
			");
        $result = $res->getResult();
        return $this->respond(['status' => true, 'data' => $result], 200);
    }

    function getdataexistpaymentlist()
    {
        $id = $this->request->getGet('id'); // ambil id dari query string

        $model = new PurchaseOrderModel();
        $res = $model->db->query(
            '
                    SELECT 
                        purchase_order.*, 
                        invoice.id AS invoice_id, 
                        payment_item.id AS payment_item_id 
                    FROM purchase_order 
                    LEFT JOIN invoice ON invoice.po_id = purchase_order.id 
                    LEFT JOIN payment_item ON payment_item.invoice_id = invoice.id 
                    WHERE purchase_order.id = ?
                    AND (invoice.is_flag = 0 OR invoice.is_flag IS NULL)',
            [$id]
        );

        $row = $res->getRow();

        $hasPayment = ($row && $row->payment_item_id !== null) ? true : false;

        return $this->respond(['status' => $hasPayment], 200);
    }
}
