<template>
    <v-container style="
    padding: 0 !important;
    height: 100%;
    display: flex;
    flex-direction: column;
    margin: 0;
    width: 100%;
    max-width: 100%;
    ">
        <v-template @open-add="onOpenAdd" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" :item-height-as-min-height="itemHeightAsMinHeight" :table-fill="tableFill" :table-fixed-header="tableFixedHeader" :show-expand="showExpand" :single-expand="singleExpand" :data="data" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :hide-filter-button="hidefilterbutton">
            <template v-slot:item.invoice_total_price="props">
                {{ Number(props.item.invoice_total_price).format(2, 3) }}
            </template>
            <template v-slot:item.btn_payment_info="props" v-if="showPaymentInfo">
                <!-- <v-btn color="primary" outlined small @click="openPaidForm" :disabled="!allowPaymentInfo(props.item)"><v-icon small left>mdi-newspaper</v-icon>
                    Payment Info
                </v-btn> -->
            </template>
            <template v-slot:item.project="props">

                <span v-if="isProjectInfoLoading" style="color: #888;">
                    <b>Loading project info...</b>
                </span>
                <span v-else-if="projectInfoError" style="color: #d32f2f;">
                    {{ projectInfoError }}
                </span>
                <span v-else>
                    <span v-if="hasProjectTypeInfo(props.item.po_no || props.item.invoice_no)">
                        <span v-if="getProjectList(props.item.po_no || props.item.invoice_no).length">
                            <b>Project(s):</b>
                            <ul>
                                <li v-for="project in getProjectList(props.item.po_no || props.item.invoice_no)" :key="(project.project_no || project.project_name || project.project_type || '-') + '-' + (project.allocation || '')" style="margin-bottom: 10px; padding: 8px; border-bottom: 1px solid #eee;">
                                <div style="font-weight: bold; color: #1976d2;">{{ project.project_name || project.project_type || '-' }}</div>
                                <div>
                                    <span style="display: inline-block; min-width: 110px;">
                                    <b>Project No:</b>
                                    </span> {{ project.project_no || '-' }} {{ project.allocation }}
                                </div>
                                <div>
                                    <span style="display: inline-block; min-width: 110px;">
                                    <b>Alokasi:</b>
                                    </span>
                                    <span v-if="getAllocationNumerator(props.item, project) > 0 && getAllocationBase(props.item, project) > 0"> {{ getAllocationPct(props.item, project) }}% <span style="color: #888;">({{ formatAmount(getAllocationNumerator(props.item, project)) }} / {{ formatAmount(getAllocationBase(props.item, project)) }})</span>
                                    </span>
                                    <span v-else>-</span>
                                </div>
                                </li>
                            </ul>
                        </span>
                        <span v-if="hasPersediaan(props.item.po_no || props.item.invoice_no)">
                            <b>Persediaan</b>
                            <ul>
                                <li style="margin-bottom: 10px; padding: 8px; border-bottom: 1px solid #eee;">
                                    <div style="font-weight: bold; color: #1976d2;">Persediaan</div>
                                    <div>
                                        <span style="display: inline-block; min-width: 110px;">
                                            <b>Alokasi:</b>
                                        </span>
                                        <span v-if="getPersediaanAllocationNumerator(props.item, props.item.po_no || props.item.invoice_no) > 0 && getPersediaanAllocationBase(props.item, props.item.po_no || props.item.invoice_no) > 0">
                                            {{ getPersediaanAllocationPct(props.item, props.item.po_no || props.item.invoice_no) }}%
                                            <span style="color: #888;">
                                                ({{ formatAmount(getPersediaanAllocationNumerator(props.item, props.item.po_no || props.item.invoice_no)) }} / {{ formatAmount(getPersediaanAllocationBase(props.item, props.item.po_no || props.item.invoice_no)) }})
                                            </span>
                                        </span>
                                        <span v-else>-</span>
                                    </div>
                                </li>
                            </ul>
                        </span>
                        <span v-if="getOperationalList(props.item.po_no || props.item.invoice_no).length">
                            <b>Operational(s):</b>
                            <ul>
                                <li v-for="dept in getOperationalList(props.item.po_no || props.item.invoice_no)" :key="'op-' + (dept.dept_code || dept.dept_name || '') + '-' + (dept.type_operational_name || '') + '-' + (dept.type_sub_operational_name || '')" style="margin-bottom: 10px; padding: 8px; border-bottom: 1px solid #eee;">
                                    <div style="font-weight: bold; color: #1976d2;">{{ dept.dept_name || '-' }}</div>
                                    <div>
                                        <span style="margin-bottom: 5px;" v-if="dept.dept_code">
                                        <b>Department :</b> ({{ dept.dept_code }}) {{ dept.dept_name }}
                                    </div>
                                    <div style="margin-bottom: 5px;" v-if="dept.type_operational_name">
                                        <b>Tipe Operasional :</b> {{ dept.type_operational_name }}
                                    </div>
                                        
                                    <div v-if="dept.type_sub_operational_name">
                                        <b>Tipe Sub Operasional :</b> {{ dept.type_sub_operational_name }}
                                    </div>
                                    <div>
                                        <span style="display: inline-block; min-width: 110px;">
                                            <b>Alokasi:</b>
                                        </span>
                                        <span v-if="getAllocationNumerator(props.item, dept) > 0 && getAllocationBase(props.item, dept) > 0">
                                            {{ getAllocationPct(props.item, dept) }}%
                                            <span style="color: #888;">
                                                ({{ formatAmount(getAllocationNumerator(props.item, dept)) }} / {{ formatAmount(getAllocationBase(props.item, dept)) }})
                                            </span>
                                        </span>
                                        <span v-else>-</span>
                                    </div>
                                    
                                </li>
                            </ul>
                        </span>
                        <span v-if="getRndList(props.item.po_no || props.item.invoice_no).length">
                            <b>R&D(s):</b>
                            <ul>
                                <li v-for="rnd in getRndList(props.item.po_no || props.item.invoice_no)" :key="'rnd-' + (rnd.project_no || '') + '-' + (rnd.dept_code || '') + '-' + (rnd.allocation || '')" style="margin-bottom: 10px; padding: 8px; border-bottom: 1px solid #eee;">
                                    <div style="font-weight: bold; color: #1976d2;">{{ rnd.project_name || '-' }}</div>
                                    <div>
                                        <span style="display: inline-block; min-width: 110px;">
                                            <b>Type R&D:</b>
                                        </span> {{ rnd.rnd_name || '-' }}
                                    </div>
                                </li>
                            </ul>
                        </span>
                        <span v-if="hasAsset(props.item.po_no || props.item.invoice_no)">
                            <b>Asset</b>
                        </span>
                    </span>
                    <span v-else-if="props.item.project_no || props.item.project_name">
                        <b>Project(s):</b>
                        <ul>
                            <li style="margin-bottom: 10px; padding: 8px; border-bottom: 1px solid #eee;">
                                <div style="font-weight: bold; color: #1976d2;">{{ props.item.project_name || '-' }}</div>
                                <span style="display: inline-block; min-width: 110px;">
                                    <b>Project No:</b>
                                </span> {{ props.item.project_no || '-' }}
                                <span style="display: inline-block; min-width: 110px;">
                                    <b>Alokasi:</b>
                                </span>
                                {{ props.item.project_id ? '-' : '100%' }} {{ props.item.invoice_total_price ? '(' + Number(props.item.invoice_total_price).toLocaleString() + ' / ' + Number(props.item.invoice_total_price).toLocaleString() + ')' : '' }}
                            </li>
                        </ul>
                    </span>
                    <!-- <span v-else-if="props.item.dept_code || props.item.dept_name || props.item.type_operational_name || props.item.type_sub_operational_name">
                        <b>Operational(s):</b>
                        <ul>
                            <li style="margin-bottom: 10px; padding: 8px; border-bottom: 1px solid #eee;">
                                <div style="font-weight: bold; color: #1976d2;">{{ props.item.dept_name || '-' }}</div>
                                <div>
                                    <span style="margin-bottom: 5px;" v-if="dept.dept_code">
                                    <b>Department :</b> ({{ props.item.dept_code }}) {{ props.item.dept_name }}
                                    </span>
                                </div>
                                <div style="margin-bottom: 5px;" v-if="dept.type_operational_name">
                                    <b>Tipe Operasional :</b> {{ props.item.type_operational_name }}
                                </div>
                                    
                                <div v-if="dept.type_sub_operational_name">
                                    <b>Tipe Sub Operasional :</b> {{ props.item.type_sub_operational_name }}
                                </div>
                                
                            </li>
                        </ul>
                    </span> -->
                    
                    <span v-else>
                        <b>No Project</b>
                        <br />
                    </span>
                </span>

            </template>
            <template v-slot:item.amount="props">
                <b>Currency:</b> {{ props.item.currency }}<br />
                <b>Amount Price:</b>
                {{ Number(props.item.invoice_total_price).format(2, 3) }}<br />
                <b>PCT:</b> {{ Number(props.item.payment_pct) }} % 
            </template>
            <template v-slot:item.invoice_detail="props">
                <span  v-if="props.item.po_no"><b>No PO:</b> <a @click="openReport2">{{props.item.po_no}}</a></span><span v-else><b>No:  {{ props.item.as_reference == 0 ? "" : "As Reference" }}</b></span><br />
                <b>Title</b> :{{props.item.as_reference == 0 ? props.item.title : props.item.uraian}}  <br />
                <b>Reference No</b> :{{ props.item.tef_invoice_no }}<br/>
                <!-- <b>Payment Code</b> :{{ props.item.kode_pembayaran }}<br/> -->
                <b>Supplier</b> :{{ props.item.supplier_name }}<br />
                <b>Proof of Invoice:</b>
                <a :href="props.item.invoice_doc_url" v-if="props.item.invoice_doc_url" target="_blank">Open Link</a><span v-else>-</span><br/>
                <b>Notes:</b> {{ props.item.invoice_payment_notes }}
            </template>
            <template v-slot:item.payment_info="props">
                <b>Payment Date:</b> {{ props.item.paid_date }}<br />
                <span v-if="props.item.proof_of_transfer.trim() != ''"><b>Proof of Payment:</b> <a :download="props.item.proof_of_transfer.trim().split('+++')[1]" target="_blank" :href="
            'https://main.buanamultiteknik.com/api/uploads/invoice' +
            props.item.invoice_id +
            '/' +
            props.item.proof_of_transfer.trim().split('+++')[0]
        " >Open Link</a></span><span v-else><b>Proof of Payment:</b> -</span>
            </template>
            <template v-slot:item.bank_info="props">
                <b>Bank name:</b> {{ props.item.bank }}<br />
                <b>Account no/IBAN:</b> {{ props.item.bank_account_no }}<br />
                <b>Account name:</b> {{ props.item.bank_account_name }}<br />
                <b>BIC/Swift Code:</b> {{ props.item.bic_swift_code }}
            </template>
            <template v-slot:item.is_paid="props">
                <v-icon v-if="props.item.is_paid == 0" color="error">mdi-close-thick</v-icon>
                    <v-icon v-else color="success">mdi-check-bold</v-icon>
                <br />
            </template>
        </v-template>

        <v-action-dialog @save="saveFile" title="Payment Info" v-model="dialogFile" v-if="showPaymentInfo">
            <v-autoform v-model="formFile" :valid="valid"></v-autoform>
        </v-action-dialog>
    </v-container>
</template>

<style scoped>
    .v-data-table__wrapper>table>tbody>tr>td {
        font-size: 0.775rem;
    }
</style>

<script>
    module.exports = {
        name: "paymentitem",
        props: {
            value: undefined,
            data: {
                type: Object,
            },
            sel: {
                type: Object,
            },
            tableOnly: {
                type: Boolean,
                default: false,
            },
            history: {
                type: Boolean,
                default: false,
            },
            showExpand: {
                type: Boolean,
                default: false,
            },
            singleExpand: {
                type: Boolean,
                default: false,
            },
            tableFixedHeader: {
                type: Boolean,
                default: true,
            },
            itemHeightAsMinHeight: {
                type: Boolean,
                default: false,
            },
            tableFill: {
                type: Boolean,
                default: true,
            },
            hidefilterbutton: {
                type: Boolean,
                default: true,
            },
            showPaymentInfo: {
                type: Boolean,
                default: false
            }
        },
        data: function() {
            return {
                valid: false,
                dialogFile: false,
                formFile: [{
                    text: "Payment Date",
                    value: "paid_date",
                    align: "start",
                    type: "date",
                    required: false,
                }, 
                {
                    text: "Next Payment Days",
                    value: "next_payment",
                    align: "start",
                    type: "int",
                    required: false,
                    visible: false,
                    form: false
                },
                {
                    text: "File",
                    value: "file",
                    type: "file",
                    required: false,
                    form: false,
                }, {
                    text: "Is Paid",
                    value: "is_paid",
                    type: "switch",
                    data_value: [0, 1],
                    visible:true,
                    required: false,
                }],
                name: "Payment",
                itemKey: "id",
                url: "bom/paymentitem",
                headers: [{
                        text: "id",
                        value: "id",
                        align: "start",
                        sortable: true,
                        filterable: false,
                        divider: false,
                        class: "",
                        width: "auto",
                        type: "int",
                        disabled: false,
                        visible: false,
                        required: false,
                        form: false,
                        filter: false,
                        groupable: false,
                    },
                    {
                        text: "payment id",
                        value: "payment_id",
                        align: "start",
                        sortable: true,
                        filterable: false,
                        divider: false,
                        class: "",
                        width: "auto",
                        type: "int",
                        disabled: false,
                        visible: false,
                        required: false,
                        form: false,
                        filter: false,
                        groupable: false,
                    },
                    {
                        text: "Invoice No",
                        value: "invoice_id",
                        align: "start",
                        sortable: false,
                        filterable: false,
                        divider: false,
                        class: "",
                        width: "auto",
                        type: "list",
                        data_value: [],
                        disabled: false,
                        visible: true,
                        required: false,
                        form: true,
                        filter: false,
                        groupable: false,
                        url: App.url + "bom/invoice",
                        searchby: ["invoice_no"],
                        formatter: function(val) {
                            return {
                                value: val.id,
                                text: val.invoice_no,
                                invoice_no: val.invoice_no
                            };
                        },
                        input: function(data) {
                            App.$get("paymentitem").onInvoiceNoSelected(data);
                        },
                        options: {
                            filter: {
                                payment_id: null,
                            },
                            filterType: {
                                payment_id: "isnull",
                            },
                            filterCondition: {},
                            is_payment: true,
                        },
                        paging: true,
                        page: "1",
                        limit: "10",
                        alias: "invoice_no",
                    },  {
                        text: "Reference No",
                        value: "po_no",
                        align: "start",
                        sortable: true,
                        filterable: false,
                        divider: false,
                        class: "",
                        width: "auto",
                        type: "varchar",
                        disabled: false,
                        visible: false,
                        required: false,
                        form: false,
                        filter: true,
                        groupable: false,
                        url: "",
                        searchby: [],
                        formatter: [],
                        options: {
                            filter: {},
                            filterType: {},
                            filterCondition: {},
                        },
                        paging: true,
                        page: "0",
                        limit: "10",
                    }, {
                        text: "Invoice Detail",
                        value: "invoice_detail",
                        align: "start",
                        sortable: false,
                        filterable: false,
                        divider: false,
                        class: "",
                        width: "auto",
                        type: "varchar",
                        data_value: [],
                        disabled: false,
                        visible: true,
                        required: false,
                        form: false,
                        filter: false,
                        groupable: false,
                        url: "",
                        searchby: "",
                        formatter: "",
                        options: "",
                        paging: true,
                        page: "1",
                        limit: "10",
                    },
                    {
                        text: "PO No",
                        value: "po_no",
                        align: "start",
                        sortable: true,
                        filterable: false,
                        divider: false,
                        class: "",
                        width: "auto",
                        type: "varchar",
                        disabled: false,
                        visible: false,
                        required: false,
                        form: false,
                        filter: true,
                        groupable: false,
                        url: "",
                        searchby: [],
                        formatter: [],
                        options: {
                            filter: {},
                            filterType: {},
                            filterCondition: {},
                        },
                        paging: true,
                        page: "0",
                        limit: "10",
                    },
                    {
                        text: "Title",
                        value: "title",
                        type: "varchar",
                        form: false,
                        visible: false
                    },
                    {
                        text: "invoice date",
                        value: "invoice_date",
                        type: "varchar",
                        form: false,
                        visible: false,
                    },
                    {
                        text: "Supplier",
                        value: "supplier_name",
                        type: "varchar",
                        form: false,
                        visible: false
                    }, 
                    {
                        text: "Project Info",
                        value: "project",
                        type: "varchar",
                        sortable: false,
                        form: false,
                    },                     
                    {
                        text: "Amount",
                        value: "amount",
                        type: "varchar",
                        sortable: false,
                        form: false,
                    }, 
                    {
                        text: "Receiver's Bank Info",
                        value: "bank_info",
                        type: "varchar",
                        sortable: false,
                        form: false,
                    },
                    {
                        text: "Payment Info",
                        value: "payment_info",
                        type: "varchar",
                        sortable:false,
                        form: false,
                        visible:false
                    }, 
                    {
                        text: "Bank Account No",
                        value: "bank_account_no",
                        type: "varchar",
                        form: false,
                        visible: false
                    },
                    {
                        text: "account name",
                        value: "bank_account_name",
                        type: "varchar",
                        form: false,
                        visible: false,
                    },
                    {
                        text: "invoice total price",
                        value: "invoice_total_price",
                        type: "varchar",
                        form: false,
                        visible: false,
                    }, {
                        text: "Paid Date",
                        value: "paid_date",
                        type: "date",
                        form: false,
                        visible: false,
                    },
                    {
                        text: "Proof URL",
                        value: "proof_url",
                        align: "start",
                        sortable: false,
                        filterable: false,
                        divider: false,
                        class: "",
                        width: "auto",
                        type: "text",
                        data_value: [],
                        disabled: false,
                        visible: false,
                        required: false,
                        form: false,
                        filter: false,
                        groupable: false,
                        url: "",
                        searchby: [],
                        paging: true,
                        page: "1",
                        limit: "10",
                    },
                    {
                        text: "Notes",
                        value: "invoice_payment_notes",
                        type: "text",
                        form: true,
                        visible: false,
                        filter: false,
                    },
                    {
                        text: "Payment Status",
                        value: "is_paid",
                        sortable: false,
                        readonly: false,
                        align: "center",
                        type: "switch",
                        form: false,
                        data_value: [0, 1],
                        visible: true,
                        
                    },
                    {
                        text: '',
                        value: 'btn_payment_info',
                        type: "varchar",
                        visible: false,
                        form: false,
                        filter: false
                    }
                ],
                selected: false,
                selectedAll: false,
                apiData: [],
                isProjectInfoLoading: false,
                projectInfoError: null,
                selectedInvoiceData: null,
                isInvoiceNotesLoading: false,
            };
        },
        computed: {
            headersObj: function() {
                var tmp = {},
                    self = this;
                Object.keys(self.headers).map((key) => {
                    var val = self.headers[key];
                    tmp[val.value] = val;
                });
                return tmp;
            },
            formFileObj: function() {
                var tmp = {},
                    self = this;
                Object.keys(self.formFile).map((key) => {
                    var val = self.formFile[key];
                    tmp[val.value] = val;
                });
                return tmp;
            }
        },
        methods: {
            toNumber: function(val) {
                if (val === null || val === undefined || val === '') {
                    return 0;
                }
                if (typeof val === 'number') {
                    return val;
                }
                var str = String(val).trim().replace(/\s/g, '');
                if (!str) {
                    return 0;
                }

                // Handle mixed locale separators safely:
                // - 2.842,88 -> 2842.88
                // - 2,842.88 -> 2842.88
                var hasDot = str.indexOf('.') !== -1;
                var hasComma = str.indexOf(',') !== -1;

                if (hasDot && hasComma) {
                    var lastDot = str.lastIndexOf('.');
                    var lastComma = str.lastIndexOf(',');
                    if (lastComma > lastDot) {
                        // Decimal is comma, dot is thousands
                        str = str.replace(/\./g, '').replace(',', '.');
                    } else {
                        // Decimal is dot, comma is thousands
                        str = str.replace(/,/g, '');
                    }
                } else if (hasComma && !hasDot) {
                    // Assume comma is decimal separator
                    str = str.replace(',', '.');
                } else {
                    // Keep plain/international numeric forms
                    str = str.replace(/,/g, '');
                }

                return Number(str) || 0;
            },
            formatAmount: function(val) {
                var num = this.toNumber(val);
                if (typeof Number(num).format === 'function') {
                    return Number(num).format(2, 3);
                }
                return Number(num).toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            },
            getAllocationBase: function(item, project) {
                var invoiceTotal = this.toNumber(item && item.invoice_total_price);
                if (invoiceTotal > 0) {
                    return invoiceTotal;
                }
                var poGrandTotal = this.toNumber(project && project.po_grand_total);
                if (poGrandTotal > 0) {
                    return poGrandTotal;
                }
                return this.toNumber(project && project.total_price);
            },
            getProjectTotalForRef: function(refNo) {
                var projects = this.getProjectList(refNo);
                var total = 0;
                for (var i = 0; i < projects.length; i++) {
                    total += this.getRowTotalPrice(projects[i]);
                }
                return total;
            },
            getCombinedAllocationTotalForRef: function(refNo) {
                var total = this.getProjectTotalForRef(refNo) +
                    this.getPersediaanTotal(refNo) +
                    this.getOperationalTotal(refNo) +
                    this.getRndTotal(refNo);
                if (total > 0) {
                    return total;
                }
                return this.getProjectTotalForRef(refNo);
            },
            getAllocationNumerator: function(item, project) {
                var refNo = (item && (item.po_no || item.invoice_no)) || '';
                var invoiceTotal = this.toNumber(item && item.invoice_total_price);
                var projectTotal = this.getRowTotalPrice(project);

                // For invoice rows, distribute invoice amount proportionally by project share.
                // This keeps total project allocation aligned with the shown Amount Price.
                if (invoiceTotal > 0 && projectTotal > 0 && refNo) {
                    var grandAllocationTotal = this.getCombinedAllocationTotalForRef(refNo);
                    if (grandAllocationTotal > 0) {
                        return invoiceTotal * (projectTotal / grandAllocationTotal);
                    }
                }

                // Fallback for rows without invoice amount
                var paidAmount = this.toNumber(project && project.paid_amount);
                if (paidAmount > 0) {
                    return paidAmount;
                }
                return projectTotal;
            },
            getAllocationPct: function(item, project) {
                var total = this.getAllocationNumerator(item, project);
                var base = this.getAllocationBase(item, project);
                if (total > 0 && base > 0) {
                    return ((total / base) * 100).toFixed(2);
                }
                return '0.00';
            },
            getProjectList: function(refNo) {
                if (!refNo || !Array.isArray(this.apiData)) {
                    return [];
                }
                var list = this.apiData.filter(function(p) {
                    var type = String(p.project_type || '').trim().toLowerCase();
                    return p.po_no === refNo &&
                        (p.project_no || p.project_name) &&
                        type !== 'r&d' &&
                        type !== 'asset' &&
                        type !== 'operational' &&
                        type !== 'persediaan';
                });
                var byKey = {};
                for (var i = 0; i < list.length; i++) {
                    var p = list[i] || {};
                    var no = (p.project_no || '').trim();
                    var baseNo = no.split(' ')[0];
                    // Normalize: "5425-NJ NIS" and "5425-NJ NI" should be treated as the same project number.
                    var key = baseNo || (p.project_name || '').trim() || ((p.project_type || '').trim() + '|' + (p.allocation || '').trim());
                    if (!key) {
                        continue;
                    }
                    if (!byKey[key]) {
                        byKey[key] = p;
                        continue;
                    }
                    var current = byKey[key];
                    var currentIsNis = String(current.allocation || '').toUpperCase() === 'NIS';
                    var candidateIsNis = String(p.allocation || '').toUpperCase() === 'NIS';
                    // Prefer non-NIS row when both refer to the same normalized project.
                    if (currentIsNis && !candidateIsNis) {
                        byKey[key] = p;
                    }
                }
                var result = Object.keys(byKey).map(function(k) { return byKey[k]; });
                return result;
            },
            getPersediaanList: function(refNo) {
                if (!refNo || !Array.isArray(this.apiData)) {
                    return [];
                }
                return this.apiData.filter(function(p) {
                    return p.po_no === refNo && String(p.project_type || '').trim().toLowerCase() === 'persediaan';
                });
            },
            hasPersediaan: function(refNo) {
                return this.getPersediaanList(refNo).length > 0;
            },
            getRndList: function(refNo) {
                if (!refNo || !Array.isArray(this.apiData)) {
                    return [];
                }
                return this.apiData.filter(function(p) {
                    return p.po_no === refNo && String(p.project_type || '').trim().toLowerCase() === 'r&d';
                });
            },
            hasAsset: function(refNo) {
                if (!refNo || !Array.isArray(this.apiData)) {
                    return false;
                }
                return this.apiData.some(function(p) {
                    return p.po_no === refNo && String(p.project_type || '').trim().toLowerCase() === 'asset';
                });
            },
            hasProjectTypeInfo: function(refNo) {
                return this.getProjectList(refNo).length > 0 ||
                    this.hasPersediaan(refNo) ||
                    this.getOperationalList(refNo).length > 0 ||
                    this.getRndList(refNo).length > 0 ||
                    this.hasAsset(refNo);
            },
            getRowTotalPrice: function(row) {
                var totalPrice = this.toNumber(row && row.total_price);
                if (totalPrice > 0) {
                    return totalPrice;
                }
                var paidAmount = this.toNumber(row && row.paid_amount);
                if (paidAmount > 0) {
                    return paidAmount;
                }
                var qty = this.toNumber(row && row.qty);
                var unitPrice = this.toNumber(row && row.unit_price);
                if (qty > 0 && unitPrice > 0) {
                    return qty * unitPrice;
                }
                return 0;
            },
            getPersediaanTotal: function(refNo) {
                var list = this.getPersediaanList(refNo);
                var total = 0;
                for (var i = 0; i < list.length; i++) {
                    total += this.getRowTotalPrice(list[i]);
                }
                return total;
            },
            getOperationalTotal: function(refNo) {
                var list = this.getOperationalList(refNo);
                var total = 0;
                for (var i = 0; i < list.length; i++) {
                    total += this.getRowTotalPrice(list[i]);
                }
                return total;
            },
            getRndTotal: function(refNo) {
                var list = this.getRndList(refNo);
                var total = 0;
                for (var i = 0; i < list.length; i++) {
                    total += this.getRowTotalPrice(list[i]);
                }
                return total;
            },
            getPersediaanAllocationBase: function(item, refNo) {
                var invoiceTotal = this.toNumber(item && item.invoice_total_price);
                if (invoiceTotal > 0) {
                    return invoiceTotal;
                }
                var combined = this.getCombinedAllocationTotalForRef(refNo);
                if (combined > 0) {
                    return combined;
                }
                return this.getPersediaanTotal(refNo);
            },
            getPersediaanAllocationNumerator: function(item, refNo) {
                var invoiceTotal = this.toNumber(item && item.invoice_total_price);
                var persediaanTotal = this.getPersediaanTotal(refNo);
                if (invoiceTotal > 0 && persediaanTotal > 0) {
                    var combined = this.getCombinedAllocationTotalForRef(refNo);
                    if (combined > 0) {
                        return invoiceTotal * (persediaanTotal / combined);
                    }
                }
                return persediaanTotal;
            },
            getPersediaanAllocationPct: function(item, refNo) {
                var numerator = this.getPersediaanAllocationNumerator(item, refNo);
                var base = this.getPersediaanAllocationBase(item, refNo);
                if (numerator > 0 && base > 0) {
                    return ((numerator / base) * 100).toFixed(2);
                }
                return '0.00';
            },
            getOperationalList: function(refNo) {
                if (!refNo || !Array.isArray(this.apiData)) {
                    return [];
                }
                return this.apiData.filter(function(p) {
                    return p.po_no === refNo &&
                        (p.project_type === 'Operational' || p.dept_code || p.dept_name || p.type_operational_name || p.type_sub_operational_name);
                });
            },
            setAddNotesValue: function(val) {
                var self = this;
                if (self.$refs.template && Array.isArray(self.$refs.template.formModelAdd)) {
                    var noteField = self.$refs.template.formModelAdd.find(function(field) {
                        return field.value === "invoice_payment_notes";
                    });
                    if (noteField) {
                        noteField.data = val;
                    }
                }
                if (self.headersObj.invoice_payment_notes) {
                    self.headersObj.invoice_payment_notes.data = val;
                }
                self.headers = App.updateObject(self.headers);
            },
            onOpenAdd: function() {
                var self = this;
                self.selectedInvoiceData = null;
                self.isInvoiceNotesLoading = false;
                self.setAddNotesValue("");
            },
            onInvoiceNoSelected: async function(data) {
                var self = this;
                try {
                    var selected = null;
                    if (data && data.data_value && Array.isArray(data.data_value)) {
                        selected = data.data_value.find(function(val) {
                            return String(val.value) == String(data.data);
                        });
                    }
                    var invoiceNo = selected ? (selected.invoice_no || selected.text) : null;
                    if (!invoiceNo) {
                        self.selectedInvoiceData = null;
                        self.isInvoiceNotesLoading = false;
                        self.setAddNotesValue("");
                        return;
                    }
                    self.isInvoiceNotesLoading = true;
                    self.setAddNotesValue("Loading...:");
                    var res = await axios.get(App.url + "bom/paymentitem/invoicebyno", {
                        params: {
                            invoice_no: invoiceNo
                        }
                    });
                    if (!res.data.status) {
                        self.selectedInvoiceData = null;
                        self.isInvoiceNotesLoading = false;
                        self.setAddNotesValue("");
                        App.errorMsg(res.data);
                        return;
                    }
                    self.selectedInvoiceData = res.data.data || null;
                    self.isInvoiceNotesLoading = false;
                    self.setAddNotesValue((self.selectedInvoiceData && self.selectedInvoiceData.notes) ? self.selectedInvoiceData.notes : "");
                } catch (err) {
                    self.selectedInvoiceData = null;
                    self.isInvoiceNotesLoading = false;
                    self.setAddNotesValue("");
                    App.errorMsg(err);
                }
            },
            fetchData() {
                this.isProjectInfoLoading = true;
                this.projectInfoError = null;

                // Ganti dengan URL API Anda
                const apiUrl = 'https://main.buanamultiteknik.com/api/bom/pobudget/monitoringproject?limit=-1';

                axios.get(apiUrl)
                    .then(response => {
                        this.isProjectInfoLoading = false;
                        if (response.data && response.data.status && response.data.data) {
                            this.apiData = response.data.data;
                        } else {
                            this.projectInfoError = 'Format data project tidak valid';
                        }
                    })
                    .catch(error => {
                        this.isProjectInfoLoading = false;
                        console.error('Error fetching data:', error);
                        this.projectInfoError = 'Gagal mengambil data project. Pastikan Anda terhubung ke jaringan internal.';
                    });
            },
            allowPaymentInfo: function(item) {
                var self = this
                if(item==undefined)
                    return false
                if (item.approved < 3)
                    return false
                /* if (item.is_paid > 0)
                    return false */
                return true
            },
            openPaidForm: function() {
                var self = this;
                if (self.selected.is_paid == 1) App.errorMsg("Invoice has been paid!");
                else
                self.dialogFile = true;
            },
            saveFile: async function() {
                var self = this;
                const formData = new FormData();
                self.formFile.map(function(val, i) {
                    var key = val.value;
                    formData.append(key, val.data);
                });
                formData.append("id", self.selected.invoice_id);
                var res = await axios.post(App.url + "bom/invoice/paid", formData, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                });
                if (!res.data.status) {
                    App.errorMsg();
                } else {
                    App.successMsg();

                    self.dialogFile = false;
                    // self.getAttachment();
                    self.$refs.template.getItems()
                }
            },
            onSelectedRow: function(val) {
                var self = this;
                if (val === undefined) {
                    self.selected = false;
                } else {
                    self.selected = val;
                    self.formFileObj.is_paid.data = val.is_paid
                    self.formFileObj.paid_date.data = val.paid_date
                    //self.formFileObj.next_payment.data = val.paid_date
                }
            },
            onSelectedRowAll: function(val) {
                var self = this;
                self.selectedAll = val;
            },
            openReport2: function(){
                var self = this
                //dialogReport=true
                var name = self.selected.po_no.replace(/\//g, '_').replace(/\-/g, '_')
                var randomid= randomId()
                window.open( 'https://main.buanamultiteknik.com/api/data/reportpo2?id='+ self.selected.po_id+ '&filename=' + name+'&idx='+ randomid)
            },
            nextPayment: function(date, days) {
                if(date && days){
                    var tmp = new Date(date)
                    return tmp.addbizDays(days || 0).formatDate('YYYY-MM-DD')
                }
                else{
                    return '-'
                }
            }
        },
        mounted: function() {
            var self=this;
            self.fetchData();
            Date.prototype.addbizDays = function(n) {
                var D = this;
                var num = Math.abs(n);
                var tem, count = 0;
                var dir = (n < 0) ? -1 : 1;
                while (count < num) {
                    D.setDate(D.getDate() + dir);
                    //if(D.isHoliday())continue;
                    tem = D.getDay();
                    if (tem != 0 && tem != 6) ++count;
                }
                D = new Date(D)
                return this
            }
            if (self.showPaymentInfo || self.history) {
                self.headersObj.btn_payment_info.visible = true
                self.headersObj.payment_info.visible = true
            }
        },
    };
</script>
