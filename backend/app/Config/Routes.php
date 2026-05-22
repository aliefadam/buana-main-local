<?php



namespace Config;



// Create a new instance of our RouteCollection class.

$routes = Services::routes();



// Load the system's routing file first, so that the app and ENVIRONMENT

// can override as needed.

if (is_file(SYSTEMPATH . 'Config/Routes.php')) {

    require SYSTEMPATH . 'Config/Routes.php';

}



/*

 * --------------------------------------------------------------------

 * Router Setup

 * --------------------------------------------------------------------

 */

$routes->setDefaultNamespace('App\Controllers');

$routes->setDefaultController('Home');

$routes->setDefaultMethod('index');

$routes->setTranslateURIDashes(false);

$routes->set404Override();

// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps

// where controller filters or CSRF protection are bypassed.

// If you don't want to define all routes, please use the Auto Routing (Improved).

// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.

$routes->setAutoRoute(true);



/*

 * --------------------------------------------------------------------

 * Route Definitions

 * --------------------------------------------------------------------

 */



// We get a performance increase by specifying the default

// route since we don't have to scan directories.

$routes->get('/', 'Home::index');



$routes->resource('budget/mutation', ['except' => 'show']);

$routes->resource('budget/budget', ['except' => 'show']);

$routes->resource('pr/rnd', ['except' => 'show']);



$routes->resource('otp', ['except' => 'show']);

$routes->get('otp/check', 'Otp::check');



//Module administration

$routes->resource('administration/department/department', ['except' => 'show']);

$routes->resource('administration/item/item', ['except' => 'show']);

$routes->resource('administration/supplier/supplier', ['except' => 'show']);

$routes->resource('administration/project/project', ['except' => 'show']);

$routes->resource('administration/project/projectitem', ['except' => 'show']);

//$routes->get('administration/project/projectitem/debug', 'Administration\Project\ProjectItem::debug');



$routes->resource('payment/popaymentvalue', ['except' => 'show']);



$routes->resource('test', ['except' => 'show']);

$routes->get('test/pdf', 'Test::pdf');



$routes->resource('machine/gb/arduino', ['except' => 'show']);

$routes->resource('machine/gbreg01', ['except' => 'show']);

$routes->resource('machine/gbreg02', ['except' => 'show']);

$routes->resource('machine/gbreg03', ['except' => 'show']);

$routes->resource('machine/gb/data', ['except' => 'show']);

$routes->get('machine/gb/data/uptime', 'Machine\Gb\Data::uptime');



//bom receive

$routes->resource('transaction/bomheader', ['except' => 'show']);

$routes->resource('transaction/receivescan', ['except' => 'show']);

$routes->resource('transaction/bom', ['except' => 'show']);

$routes->resource('transaction/bomitem', ['except' => 'show']);

$routes->resource('transaction/bomitemspb', ['except' => 'show']);

$routes->get('transaction/bomheader/getitemsall', 'Transaction\Bomheader::getitemsall');

$routes->get('transaction/bomheader/getitems', 'Transaction\Bomheader::getitems');

$routes->get('transaction/bomheader/getitemsreceive', 'Transaction\Bomheader::getitemsreceive');

$routes->get('transaction/bomheader/getitem', 'Transaction\Bomheader::getitem');

$routes->get('transaction/bomheader/info', 'Transaction\Bomheader::info');

$routes->get('transaction/bomheader/info2', 'Transaction\Bomheader::info2');

$routes->get('transaction/bomheader/addtopr', 'Transaction\Bomheader::addtopr');

$routes->post('transaction/bomheader/savesubkolipart', 'Transaction\Bomheader::savesubkolipart');

$routes->resource('transaction/bomreport', ['except' => 'show']);

$routes->resource('transaction/bomreportitem', ['except' => 'show']);

$routes->post('transaction/koli/auto', 'Transaction\Koli::auto');

$routes->post('transaction/koli/addscan', 'Transaction\Koli::addscan');

$routes->post('transaction/koli/updatescan', 'Transaction\Koli::updatescan');

$routes->post('transaction/subkoli/auto', 'Transaction\Subkoli::auto');

$routes->get('transaction/subkolipart/report', 'Transaction\Subkolipart::report');

$routes->post('transaction/subkolipart/update_container', 'Transaction\Subkolipart::update_container');

$routes->post('transaction/subkolipart/updatekoliphoto', 'Transaction\Subkolipart::updateKoliPhoto');



//report data

$routes->get('data/podata', 'Data::podata');

$routes->get('data/postatus', 'Data::postatus');

$routes->get('data/po', 'Data::po');

$routes->get('data/reportpo', 'Data::reportpo');



$routes->resource('ragic/ragic', ['except' => 'show']);

$routes->get('ragic/ragic/import', 'Ragic\Ragic::import');

$routes->get('ragic/ragic/import2', 'Ragic\Ragic::import2');

$routes->get('ragic/ragic/logistic', 'Ragic\Ragic::logistic');



//finance pettycash

$routes->resource('finance/transaksi', ['except' => 'show']);

$routes->resource('finance/pettycash', ['except' => 'show']);

$routes->post('finance/pettycashdetail/transaksiexcel', 'Finance\Pettycashdetail::create2');

#$routes->get('finance/pettycash/report', 'Finance\Pettycash::report');

$routes->post('finance/pettycash/reportperiodic', 'Finance\Pettycash::reportperiodic');

$routes->resource('finance/pettycashdetail', ['except' => 'show']);

$routes->resource('finance/rinciantransaksi', ['except' => 'show']);

$routes->resource('finance/akun',['except' => 'show']);

$routes->resource('finance/transaction',['except' => 'show']);

$routes->get('finance/transaksi/datalaporan', 'Finance\Transaksi::dataLaporan');



//pr

$routes->resource('pr/notes', ['except' => 'show']);



//po

$routes->resource('po/comment', ['except' => 'show']);

$routes->resource('po/requestarival', ['except' => 'show']);



$routes->resource('users/info', ['except' => 'show']);

$routes->resource('users', ['except' => 'show']);

$routes->get('users.(:any)', 'Users::index');

$routes->resource('info');

$routes->resource('barcode/itemall', ['except' => 'show']);

$routes->resource('barcode/noinput', ['except' => 'show']);

$routes->get('barcode/noinput/alreadytracked', 'Barcode\Noinput::alreadytracked');

$routes->resource('barcode/item', ['except' => 'show']);

$routes->get('barcode/item/report', 'Barcode\Item::report');

$routes->get('barcode/item/image', 'Barcode\Item::image');

$routes->get('barcode/item/imagefull', 'Barcode\Item::imagefull');

$routes->get('barcode/item/imagebarcode', 'Barcode\Item::imagebarcode');

$routes->get('barcode/item/infopercent', 'Barcode\Item::infopercent');

$routes->get('barcode/item/infoarea', 'Barcode\Item::infoarea');

$routes->get('barcode/item/partnotregistered', 'Barcode\Item::partnotregistered');

$routes->resource('barcode/mpart');

/* transaction */

$routes->resource('transaction/npb', ['except' => 'show']);

$routes->get('transaction/npb/complete', 'Transaction\Npb::complete');

$routes->get('transaction/npb/revisi', 'Transaction\Npb::revisi');

$routes->get('transaction/npb/info', 'Transaction\Npb::info');

$routes->resource('transaction/container', ['except' => 'show']);

$routes->resource('transaction/koli', ['except' => 'show']);

$routes->resource('transaction/subkoli', ['except' => 'show']);

$routes->resource('transaction/subkolipart', ['except' => 'show']);

$routes->resource('transaction/subkolilist', ['except' => 'show']);

//$routes->resource('transaction/npbitem');

$routes->resource('transaction/npbitem', ['except' => 'show']);

$routes->get('transaction/npbitem/create_from_bom', 'Transaction\Npbitem::create_from_bom');

$routes->resource('transaction/stock');

$routes->resource('transaction/stock', ['except' => 'show']);

$routes->resource('transaction/stockgroup');

$routes->resource('transaction/stockgroup', ['except' => 'show']);

/* BOM */

$routes->resource('bom/supplier');

$routes->resource('bom/item', ['except' => 'show']);

$routes->get('bom/item/create_from_bom', 'Bom\Item::create_from_bom');

$routes->resource('bom/department');

$routes->resource('bom/bom_header');

$routes->resource('bom/bom_item');



$routes->resource('bom/type', ['except' => 'show']);

$routes->resource('bom/subtype', ['except' => 'show']);



$routes->resource('document/document', ['except' => 'show']);

$routes->resource('document/container', ['except' => 'show']);

$routes->resource('document/fields', ['except' => 'show']);

$routes->get('document/fields/(:any)', 'Document\Fields');

$routes->resource('document/lists', ['except' => 'show']);

$routes->resource('document/listvalues', ['except' => 'show']);

$routes->resource('document/notes', ['except' => 'show']);

$routes->get('document/notes/(:any)', 'Document\Notes');

$routes->post('document/notes/(:any)', 'Document\Notes::create');

$routes->delete('document/notes/(:any)', 'Document\Notes');

$routes->put('document/notes/(:any)', 'Document\Notes');



$routes->resource('bom/poitem', ['except' => 'show']);

$routes->resource('bom/spb', ['except' => 'show']);



$routes->resource('bom/purchaseorderhistory', ['except' => 'show']);

$routes->resource('bom/purchase_order_item_history', ['except' => 'show']);

$routes->resource('bom/purchase_order_item_group_history', ['except' => 'show']);



$routes->resource('bom/monitoringpr', ['except' => 'show']);

$routes->resource('bom/monitoring', ['except' => 'show']);

$routes->resource('bom/monitoringitem', ['except' => 'show']);



$routes->resource('bom/pobudget', ['except' => 'show']);

$routes->get('bom/pobudget/data', 'Bom\Pobudget::getData');

$routes->get('bom/pobudget/getdataproject', 'Bom\Pobudget::getDataProject');

$routes->get('bom/pobudget/getdatanoinvoice', 'Bom\Pobudget::getDataNoInvoice');

$routes->get('bom/pobudget/getdataponoinvoice', 'Bom\Pobudget::getDataPoNoInvoice');

$routes->get('bom/pobudget/getdatapaid', 'Bom\Pobudget::getDataPaid');

$routes->get('bom/pobudget/monitoringpayment', 'Bom\Pobudget::getDataMonitoringPayment');

$routes->get('bom/pobudget/waitingapproval', 'Bom\Pobudget::getDataWaitingApproval');

$routes->get('bom/pobudget/approvedoutstanding', 'Bom\Pobudget::getDataApprovedOutstanding');

$routes->get('bom/pobudget/monitoringproject', 'Bom\Pobudget::getDataMonitoringProject');

$routes->get('bom/pobudget/poitem', 'Bom\Pobudget::getDataPoItem');



$routes->resource('bom/pobudget2', ['except' => 'show']);

$routes->resource('bom/popayment', ['except' => 'show']);



$routes->resource('fake/purchaseorder', ['except' => 'show']);

$routes->resource('fake/purchase_order_item_group', ['except' => 'show']);

$routes->resource('bom/purchase_order_item', ['except' => 'show']);



$routes->resource('bom/creditnote', ['except' => 'show']);

$routes->post('bom/creditnote/upload', 'Bom\Creditnote::upload');

$routes->post('bom/creditnote/kurs','Bom\Creditnote::kurs');

$routes->resource('bom/pr', ['except' => 'show']);

$routes->get('bom/pr/revisi', 'Bom\Pr::revisi');

$routes->get('pr/info', 'Bom\Pr::info');

$routes->post('bom/pr/uploadprint', 'Bom\Pr::uploadprint');

$routes->resource('bom/prpart', ['except' => 'show']);

$routes->resource('bom/prsubledger', ['except' => 'show']);

$routes->get('bom/pr/total_pr', 'Bom\Pr::total_pr');

$routes->resource('bom/categoryitem', ['except' => 'show']);

$routes->resource('bom/purchase_order', ['except' => 'show']);

$routes->post('bom/purchase_order/info', 'Bom\Purchase_order::info');

$routes->post('bom/purchase_order/emailsupplier', 'Bom\Purchase_order::emailsupplier');

$routes->post('bom/purchase_order/autoemailsupplier', 'Bom\Purchase_order::autoemailsupplier');

$routes->resource('bom/purchaseorder', ['except' => 'show']);

$routes->get('bom/(purchaseorder\.xlsx)', 'Bom\Purchaseorder::index');
$routes->get('bom/purchaseorder/revisi', 'Bom\Purchaseorder::revisi');
$routes->get('bom/purchaseorder/askapprovalrevisi', 'Bom\Purchaseorder::askapprovalrevisi');
$routes->get('bom/purchaseorder/getdataexistpaymentlist', 'Bom\Purchaseorder::getdataexistpaymentlist');
$routes->get('bom/purchaseorder/titleoptions', 'Bom\Purchaseorder::titleoptions');
$routes->post('bom/purchaseorder/sendpo', 'Bom\Purchaseorder::sendpo');
$routes->get('bom/purchaseorder/cancel', 'Bom\Purchaseorder::cancel');
$routes->post('bom/purchaseorder/addcharge', 'Bom\Purchaseorder::addcharge');
$routes->post('bom/purchaseorder/complete', 'Bom\Purchaseorder::complete');

$routes->post('bom/purchaseorder/testemail', 'Bom\Purchaseorder::testemail');

$routes->post('bom/purchaseorder/fakedata', 'Bom\Purchaseorder::fakeData');

$routes->resource('bom/purchase_order_item', ['except' => 'show']);

$routes->resource('bom/purchaseorder_item', ['except' => 'show']);

$routes->resource('bom/purchase_order_item_group', ['except' => 'show']);

$routes->post('bom/purchase_order_item_group/save_tax_usage', 'Bom\Purchase_order_item_group::saveTaxUsage');

$routes->post('bom/purchase_order_item/addbom', 'Bom\Purchase_order_item::addbom');

$routes->resource('bom/invoice', ['except' => 'show']);

$routes->resource('bom/invoiceitems', ['except' => 'show']);

$routes->post('bom/invoice/paid', 'Bom\Invoice::paid');

$routes->post('bom/invoice/test', 'Bom\Invoice::test');

$routes->get('bom/(invoice\.xlsx)', 'Bom\Invoice::index');

$routes->resource('bom/payment', ['except' => 'show']);

$routes->resource('bom/asreference', ['except' => 'show']);

$routes->resource('bom/asreferenceitem', ['except' => 'show']);

$routes->get('bom/payment/complete', 'Bom\Payment::complete');

$routes->get('bom/payment/exchange', 'Bom\Payment::exchange');

$routes->get('bom/payment/transferstock', 'Bom\Payment::transferstock');

$routes->get('bom/payment/transferstock2', 'Bom\Payment::transferstock2');

$routes->post('bom/payment/transferstock2', 'Bom\Payment::transferstock2');

$routes->post('bom/payment/transferstock3', 'Bom\Payment::transferstock3');

$routes->get('bom/payment/info_validated', 'Bom\Payment::info_validated');

$routes->get('bom/payment/info_approved', 'Bom\Payment::info_approved');

$routes->get('bom/payment/revisi', 'Bom\Payment::revisi');

$routes->resource('bom/paymentitem', ['except' => 'show']);

$routes->resource('bom/comment', ['except' => 'show']);

$routes->resource('bom/commentpr', ['except' => 'show']);

$routes->resource('bom/pocompleteitem', ['except' => 'show']);

$routes->resource('bom/list_transfer_finance', ['except' => 'show']);

$routes->post('bom/list_transfer_finance/paid', 'Bom\List_transfer_finance::paid');

$routes->resource('bom/list_transfer_item_finance', ['except' => 'show']);

$routes->get('bom/(list_transfer_item_finance\.xlsx)', 'Bom\List_transfer_item_finance::index');



$routes->resource('admin/users', ['except' => 'show']);

$routes->resource('admin/user_group', ['except' => 'show']);

$routes->resource('admin/modules', ['except' => 'show']);

$routes->resource('admin/general');

$routes->resource('admin/general_category');

$routes->resource('admin/filters', ['except' => 'show']);

$routes->resource('admin/nowa', ['except' => 'show']);

$routes->get('admin/filters/available', 'Admin\Filters::available');



$routes->resource('rfq/rfq', ['except' => 'show']);

$routes->get('rfq/(rfq\.xlsx)', 'Rfq\Rfq::index');

$routes->resource('rfq/rfqcomment', ['except' => 'show']);

$routes->resource('rfq/dashboard', ['except' => 'show']);

$routes->resource('rfq/rfqlist', ['except' => 'show']);

$routes->resource('rfq/rfqhist', ['except' => 'show']);

$routes->resource('rfq/attachment', ['except' => 'show']);

$routes->get('rfq/rfq/total_rfq', 'Rfq\Rfq::total_rfq');



//project

$routes->resource('pm/project', ['except' => 'show']);

$routes->resource('pm/projectpart', ['except' => 'show']);

$routes->resource('pm/projecthist', ['except' => 'show']);

$routes->resource('pm/delivery', ['except' => 'show']);

$routes->resource('pm/po', ['except' => 'show']);



$routes->resource('project/project', ['except' => 'show']);

$routes->get('project/project/no', 'Project\Project::no');

$routes->get('project/project-budget/(:any)', 'Project\Project::projectBudget/$1');

$routes->get('project/project-budget-detail/(:any)', 'Project\Project::projectBudgetDetail/$1');

$routes->resource('bom/alokasipembelian', ['except' => 'show']);



//dev

$routes->resource('bomdev/supplier');

$routes->resource('bomdev/item');

$routes->resource('bomdev/project');

$routes->resource('bomdev/department');

$routes->resource('bomdev/bom_header');

$routes->resource('bomdev/bom_item');



$routes->resource('bomdev/purchase_order', ['except' => 'show']);

$routes->post('bomdev/purchase_order/info', 'Bom\Purchase_order::info');

$routes->resource('bomdev/purchaseorder', ['except' => 'show']);

$routes->post('bomdev/purchaseorder/addcharge', 'Bom\Purchaseorder::addcharge');

$routes->resource('bomdev/purchase_order_item', ['except' => 'show']);

$routes->resource('bomdev/purchase_order_item_group', ['except' => 'show']);

$routes->post('bomdev/purchase_order_item/addbom', 'Bom\Purchase_order_item::addbom');

$routes->post('bomdev/purchase_order_item/addpr', 'Bom\Purchase_order_item::addpr');

$routes->resource('bomdev/invoice', ['except' => 'show']);

$routes->resource('bomdev/payment', ['except' => 'show']);

$routes->get('bomdev/payment/complete', 'Bom\Payment::complete');

$routes->get('bomdev/payment/transferstock', 'Bom\Payment::transferstock');

$routes->resource('bomdev/paymentitem', ['except' => 'show']);

$routes->resource('bomdev/comment', ['except' => 'show']);



$routes->resource('warehouse/spb', ['except' => 'show']);

$routes->get('warehouse/spb/spb', 'Warehouse\Spb::spb');

$routes->get('warehouse/spb/revisi', 'Warehouse\Spb::revisi');

$routes->get('warehouse/spb/email', 'Warehouse\Spb::test');

$routes->get('transaction/spb/info', 'Warehouse\Spb::info');

$routes->resource('warehouse/rack', ['except' => 'show']);

$routes->resource('warehouse/item', ['except' => 'show']);

$routes->resource('warehouse/spbitem', ['except' => 'show']);



$routes->resource('rebuildmachine/report', ['except' => 'show']);

$routes->get('rebuildmachine/(report\.xlsx)', 'Rebuildmachine\Report::index');

$routes->get('rebuildmachine/chart', 'Rebuildmachine\Report::chart');

$routes->get('rebuildmachine/chart2', 'Rebuildmachine\Report::chart2');

$routes->resource('rebuildmachine/reportitemgroup', ['except' => 'show']);

$routes->resource('rebuildmachine/reportitem', ['except' => 'show']);

$routes->resource('rebuildmachine/pic', ['except' => 'show']);

$routes->resource('rebuildmachine/listpart', ['except' => 'show']);



$routes->resource('maintenance/msubassembly', ['except' => 'show']);



$routes->resource('suratjalan/suratjalan', ['except' => 'show']);

$routes->resource('suratjalan/suratjalanitemgroup', ['except' => 'show']);

$routes->resource('suratjalan/suratjalanitem', ['except' => 'show']);

$routes->post('suratjalan/suratjalanitem/addnpb', 'Warehouse\suratjalanitem::addnpb');

$routes->post('suratjalan/Suratjalanitemgroup/addnpb', 'Warehouse\Suratjalanitemgroup::addnpb');



$routes->post('rebuildmachine/report/sendwa', 'Rebuildmachine\Report::sendwa');



$routes->resource('email', ['except' => 'show']);

$routes->resource('wa', ['except' => 'show']);

$routes->resource('pr/monitoring', ['except' => 'show']);

$routes->post('pr/monitoring/upload', 'Pr\Monitoring::upload');

$routes->post('wa/send', 'Wa::send');



$routes->resource('nomenclature/bast', ['except' => 'show']);



$routes->post('monitoring/monitoring', 'Monitoring::monitoring');

$routes->post('monitoring/monitoring_log', 'Monitoring::monitoring_log');

$routes->get('monitoring/monitoring_rfq', 'Monitoring::monitoring_rfq');

$routes->resource('monitoring/monitoringrfqall', ['except' => 'show']);

$routes->resource('monitoring/listrfq', ['except' => 'show']);

$routes->resource('monitoring/listrfqclarification', ['except' => 'show']);

$routes->resource('monitoring/listrfqquotation', ['except' => 'show']);

$routes->resource('monitoring/listrfqreadyforpr', ['except' => 'show']);

$routes->resource('monitoring/listprreadyforpr', ['except' => 'show']);

$routes->resource('monitoring/listprinprocess', ['except' => 'show']);

$routes->resource('monitoring/listprreadyforpo', ['except' => 'show']);

$routes->resource('monitoring/listporeadyforpo', ['except' => 'show']);

$routes->resource('monitoring/listpoinprocess', ['except' => 'show']);

$routes->resource('monitoring/listporeadyforapproval', ['except' => 'show']);

$routes->resource('monitoring/listrfqprimary', ['except' => 'show']);

$routes->resource('tickets/tickets', ['except' => 'show']);

$routes->get('monitor/tickets/getsummary', 'Tickets\Tickets::getSummary');



//s

$routes->get('monitor/summarycurr', 'Monitoring\Listporeadyforapproval::summary');



//planner



$routes->resource('planner/related', ['except' => 'show']);

$routes->resource('planner/reference', ['except' => 'show']);

$routes->resource('planner/client', ['except' => 'show']);

$routes->resource('planner/watcher', ['except' => 'show']);

$routes->resource('planner/task_member', ['except' => 'show']);

$routes->resource('planner/notes', ['except' => 'show']);

$routes->post('planner/notes/add', 'Planner\Notes::add');

$routes->resource('planner/access', ['except' => 'show']);

$routes->get('planner/access/access', 'Planner\Access::access');

$routes->resource('planner/task', ['except' => 'show']);

$routes->get('planner/task/related', 'Planner\Task::related');

$routes->resource('planner/dashboard', ['except' => 'show']);

$routes->get('planner/dashboard/my_task', 'Planner\Dashboard::my_task');

$routes->resource('planner/field', ['except' => 'show']);

$routes->get('planner/field/task_category', 'Planner\Field::task_category');

$routes->resource('planner/project_member', ['except' => 'show']);

$routes->resource('planner/project', ['except' => 'show']);

$routes->get('planner/project/member', 'Planner\Project::member');



$routes->get('api/uuid-login', 'Api\Auth::uuidLogin');



/*

 * --------------------------------------------------------------------

 * Additional Routing

 * --------------------------------------------------------------------

 *

 * There will often be times that you need additional routing and you

 * need it to be able to override any defaults in this file. Environment

 * based routes is one such time. require() additional route files here

 * to make that happen.

 *

 * You will have access to the $routes object within that file without

 * needing to reload it.

 */

if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {

    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';

}

