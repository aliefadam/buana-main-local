<template>
  <v-container
    style="
      padding: 0 !important;
      height: 100%;
      display: flex;
      flex-direction: column;
      margin: 0;
      width: 100%;
      max-width: 100%;
    "
  >
    <v-template
      :items-options="itemsOptions"
      export-excel
      @close-add="resetHeader"
      :disable-edit-button="disableEditButton"
      :disable-delete-button="disableDeleteButton"
      v-model="value"
      ref="template"
      :item-key="itemKey"
      :url="url"
      :headers="headers"
      :name="name"
      @update:selected-row="onSelectedRow"
      @update:selected-row-all="onSelectedRowAll"
      :table-only="tableOnly"
    >
      <template v-slot:menu-after-filter>
        <v-btn
          color="primary"
          outlined
          small
          @click="openPOItems"
          :disabled="!selected"
        >
          PO Items
        </v-btn>
        <v-btn
          color="primary"
          outlined
          small
          @click="openDetail"
          :disabled="!selected"
        >
          Details
        </v-btn>
      </template>
      <template v-slot:item.invoice="props">
        <b>Invoice No:</b> {{ props.item.invoice_no }}<br />
        <b>Tranfer List No:</b> {{ props.item.payment_no }}<br />
        <b>Invoice Date:</b> {{ props.item.invoice_date }}<br />
        <b>Created By:</b> {{ props.item.created_by_name }}<br />
        <b>Created Date:</b> {{ props.item.created_date }}
      </template>
      <template v-slot:item.invoice_detail="props">
        <span v-if="props.item.po_no"
          ><b>No PO:</b>
          <a @click="openReport2">{{ props.item.po_no }}</a></span
        ><span v-else
          ><b
            >No: {{ props.item.as_reference == 0 ? "" : "As Reference" }}</b
          ></span
        ><br />
        <b>Title</b> :{{
          props.item.as_reference == 0 ? props.item.title : props.item.uraian
        }}
        <br />
        <b>Supplier</b> :{{ props.item.supplier_name }}<br />
        <b>Reference No</b> :{{ props.item.ref_invoice_no }}<br />
        <!-- <b>Payment Code</b> :{{ props.item.kode_pembayaran }}<br /> -->
        <b>Credit Note</b> : {{ props.item.credit_code }}<br />
        <b>Proof of Invoice:</b>
        <a
          :href="props.item.invoice_doc_url"
          v-if="props.item.invoice_doc_url"
          target="_blank"
          >Open Link</a
        ><span v-else>-</span><br />
        <b>Notes:</b> {{ props.item.notes }}
      </template>
      <template v-slot:item.project="props">
        <span v-if="isProjectInfoLoading" style="color: #888">
          <b>Loading project info...</b>
        </span>
        <span v-else-if="projectInfoError" style="color: #d32f2f">
          {{ projectInfoError }}
        </span>
        <span v-else>
          <span v-if="hasProjectTypeInfo(props.item.po_no || props.item.invoice_no)">
            <span v-if="getProjectList(props.item.po_no || props.item.invoice_no).length">
              <b>Project(s):</b>
              <ul>
                <li
                  v-for="project in getProjectList(props.item.po_no || props.item.invoice_no)"
                  :key="
                    (project.project_no ||
                      project.project_name ||
                      project.project_type ||
                      '-') +
                    '-' +
                    (project.allocation || '')
                  "
                  style="
                    margin-bottom: 10px;
                    padding: 8px;
                    border-bottom: 1px solid #eee;
                  "
                >
                  <div style="font-weight: bold; color: #1976d2">
                    {{ project.project_name || project.project_type || "-" }}
                  </div>
                  <div>
                    <span style="display: inline-block; min-width: 110px">
                      <b>Project No:</b>
                    </span>
                    {{ project.project_no || "-" }} {{ project.allocation }}
                  </div>
                  <div>
                    <span style="display: inline-block; min-width: 110px">
                      <b>Alokasi:</b>
                    </span>
                    <span
                      v-if="
                        getAllocationNumerator(props.item, project) > 0 &&
                        getAllocationBase(props.item, project) > 0
                      "
                    >
                      {{ getAllocationPct(props.item, project) }}%
                      <span style="color: #888"
                        >({{
                          formatAmount(getAllocationNumerator(props.item, project))
                        }}
                        /
                        {{ formatAmount(getAllocationBase(props.item, project)) }})</span
                      >
                    </span>
                    <span v-else>-</span>
                  </div>
                </li>
              </ul>
            </span>
            <span v-if="hasPersediaan(props.item.po_no || props.item.invoice_no)">
              <b>Persediaan</b>
              <ul>
                <li
                  style="
                    margin-bottom: 10px;
                    padding: 8px;
                    border-bottom: 1px solid #eee;
                  "
                >
                  <div style="font-weight: bold; color: #1976d2">Persediaan</div>
                  <div>
                    <span style="display: inline-block; min-width: 110px">
                      <b>Alokasi:</b>
                    </span>
                    <span
                      v-if="
                        getPersediaanAllocationNumerator(
                          props.item,
                          props.item.po_no || props.item.invoice_no
                        ) > 0 &&
                        getPersediaanAllocationBase(
                          props.item,
                          props.item.po_no || props.item.invoice_no
                        ) > 0
                      "
                    >
                      {{
                        getPersediaanAllocationPct(
                          props.item,
                          props.item.po_no || props.item.invoice_no
                        )
                      }}%
                      <span style="color: #888"
                        >({{
                          formatAmount(
                            getPersediaanAllocationNumerator(
                              props.item,
                              props.item.po_no || props.item.invoice_no
                            )
                          )
                        }}
                        /
                        {{
                          formatAmount(
                            getPersediaanAllocationBase(
                              props.item,
                              props.item.po_no || props.item.invoice_no
                            )
                          )
                        }})</span
                      >
                    </span>
                    <span v-else>-</span>
                  </div>
                </li>
              </ul>
            </span>
            <span v-if="getOperationalList(props.item.po_no || props.item.invoice_no).length">
              <b>Operational(s):</b>
              <ul>
                <li
                  v-for="dept in getOperationalList(props.item.po_no || props.item.invoice_no)"
                  :key="
                    'op-' +
                    (dept.dept_code || dept.dept_name || '') +
                    '-' +
                    (dept.type_operational_name || '') +
                    '-' +
                    (dept.type_sub_operational_name || '')
                  "
                  style="
                    margin-bottom: 10px;
                    padding: 8px;
                    border-bottom: 1px solid #eee;
                  "
                >
                  <div style="font-weight: bold; color: #1976d2">
                    {{ dept.dept_name || "-" }}
                  </div>
                  <div>
                    <span style="margin-bottom: 5px" v-if="dept.dept_code">
                      <b>Department :</b> ({{ dept.dept_code }}) {{ dept.dept_name }}
                    </span>
                  </div>
                  <div style="margin-bottom: 5px" v-if="dept.type_operational_name">
                    <b>Tipe Operasional :</b> {{ dept.type_operational_name }}
                  </div>
                  <div v-if="dept.type_sub_operational_name">
                    <b>Tipe Sub Operasional :</b>
                    {{ dept.type_sub_operational_name }}
                  </div>
                </li>
              </ul>
            </span>
            <span v-if="getRndList(props.item.po_no || props.item.invoice_no).length">
              <b>R&D(s):</b>
              <ul>
                <li
                  v-for="rnd in getRndList(props.item.po_no || props.item.invoice_no)"
                  :key="
                    'rnd-' +
                    (rnd.project_no || '') +
                    '-' +
                    (rnd.dept_code || '') +
                    '-' +
                    (rnd.allocation || '')
                  "
                  style="
                    margin-bottom: 10px;
                    padding: 8px;
                    border-bottom: 1px solid #eee;
                  "
                >
                  <div style="font-weight: bold; color: #1976d2">
                    {{ rnd.project_name || "-" }}
                  </div>
                  <div>
                    <span style="display: inline-block; min-width: 110px">
                      <b>Type R&D:</b>
                    </span>
                    {{ rnd.rnd_name || "-" }}
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
              <li
                style="
                  margin-bottom: 10px;
                  padding: 8px;
                  border-bottom: 1px solid #eee;
                "
              >
                <div style="font-weight: bold; color: #1976d2">
                  {{ props.item.project_name || "-" }}
                </div>
                <span style="display: inline-block; min-width: 110px">
                  <b>Project No:</b>
                </span>
                {{ props.item.project_no || "-" }}
                <span style="display: inline-block; min-width: 110px">
                  <b>Alokasi:</b>
                </span>
                {{ props.item.project_id ? "-" : "100%" }}
                {{
                  props.item.invoice_total_price
                    ? "(" +
                      Number(props.item.invoice_total_price).toLocaleString() +
                      " / " +
                      Number(props.item.invoice_total_price).toLocaleString() +
                      ")"
                    : ""
                }}
              </li>
            </ul>
          </span>
          <span v-else>
            <b>No Project</b>
            <br />
          </span>
        </span>
      </template>
      <template v-slot:item.uraian="props">
        <span>{{
          props.item.as_reference == 0 ? props.item.title : props.item.uraian
        }}</span>
      </template>
      <template v-slot:item.invoice_amount="props">
        <b>Currency:</b> {{ props.item.currency }}<br />
        <b>Amount:</b> {{ Number(props.item.invoice_total_price).format(2, 3)
        }}<br />
        <b>PCT:</b> {{ Number(props.item.payment_pct) }} %
      </template>
      <!--<template v-slot:item.as_reference="props">-->
      <!--	{{props.item.as_reference == 0 ? "No" : "Yes"}}-->
      <!--</template>-->
      <template v-slot:item.invoice_doc_url="props">
        <a
          :href="props.item.invoice_doc_url"
          v-if="props.item.invoice_doc_url"
          target="_blank"
          >Invoice Document</a
        ><span v-else>Invoice Document: -</span><br />
        <a
          :download="props.item.proof_of_transfer.trim().split('+++')[1]"
          target="_blank"
          :href="
            'https://internal.buanamultiteknik.com/api/uploads/invoice' +
            props.item.id +
            '/' +
            props.item.proof_of_transfer.trim().split('+++')[0]
          "
          v-if="props.item.proof_of_transfer.trim() != ''"
          >Proof of Payment </a
        ><span v-else>Proof of Payment: -</span>
      </template>
      <template v-slot:item.total_price="props">
        {{ parseFloat(props.item.total_price).format(2, 3) }}
      </template>
      <template v-slot:item.is_paid="props">
        <v-icon v-if="props.item.is_paid == 0" color="error"
          >mdi-close-thick</v-icon
        >
        <v-icon v-else color="success">mdi-check-bold</v-icon><br />
      </template>
      <template v-slot:item.bank_info="props">
        <span v-if="props.item.reimburse_id == null">
          <b>Bank name:</b> {{ props.item.bank }}<br />
          <b>Account no/IBAN:</b> {{ props.item.bank_account_no }}<br />
          <b>Account name:</b> {{ props.item.bank_account_name }}<br />
          <b>BIC/Swift Code:</b> {{ props.item.bic_swift_code }}</span
        >
        <span v-else>
          <b>Bank name:</b> {{ props.item.r_bank }}<br />
          <b>Account no/IBAN:</b> {{ props.item.r_bank_account }}<br />
          <b>Account name:</b> {{ props.item.r_bank_account_name }}<br />
          <b>BIC/Swift Code:</b> {{ props.item.r_bic_swift_code }}
        </span>
      </template>
      <template v-slot:item.payment_info="props">
        <b>Payment Date:</b> {{ props.item.paid_date }}<br />
        <span v-if="props.item.proof_of_transfer.trim() != ''"
          ><b>Proof of Payment:</b>
          <a
            :download="props.item.proof_of_transfer.trim().split('+++')[1]"
            target="_blank"
            :href="
              'https://internal.buanamultiteknik.com/api/uploads/invoice' +
              props.item.id +
              '/' +
              props.item.proof_of_transfer.trim().split('+++')[0]
            "
            >Open Link</a
          ></span
        ><span v-else><b>Proof of Payment:</b> -</span>
      </template>
      <template v-slot:item.created="props">
        <span>By:</span> {{ props.item.created_by_name }}<br />
        <span>Date:</span> {{ props.item.created_date }}<br />
      </template>
      <template v-slot:item.modified="props">
        <span>By:</span> {{ props.item.modified_by_name }}<br />
        <span>Date:</span> {{ props.item.modified_date }}<br />
      </template>
    </v-template>
    <!-- <v-action-dialog :actions="false" v-model="dialogItem" title="Detail" min-height="75%" fullscreen :key="selected.id">
            <table style="width: 100%; height: 100%;">
                <tr>
                    <td style="vertical-align: top;">
                        <v-autoform v-model="poInf" hide-details></v-autoform>
                    </td>
                    <td style="vertical-align: top;">
                        <v-autoform v-model="supplierInf" hide-details></v-autoform>
                    </td>
                    <td style="vertical-align: top;">
                        <v-autoform v-model="billInf" hide-details></v-autoform>
                        
                        <v-btn color="primary" outlined small @click="saveBill">
                            Save
                        </v-btn>
                    </td>
                </tr>
            </table>
        </v-action-dialog> -->
    <v-action-dialog
      class="long-dialog"
      :actions="false"
      v-model="dialogItem"
      title="Detail"
      min-height="75%"
      :key="selected.id"
    >
      <!-- <v-card flat>
                <v-card-title primary-title> Supplier Information </v-card-title>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Bank Info</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                {{ supplierfObj.supplier_name.data }}
                            </td>
                            <td>
                                <b>Bank name:</b> {{ supplierfObj.bank.data }}<br />
                                <b>Account no/IBAN:</b> {{ supplierfObj.bank_account_no.data
                }}<br />
                                <b>Currency:</b> {{ poinfObj.currency.data }}<br />
                                <b>Account name:</b> {{ supplierfObj.bank_account_name.data
                }}<br />
                                <b>BIC/Swift Code:</b> {{ supplierfObj.bic_swift_code.data }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </v-card> -->
      <v-card flat>
        <v-card-title primary-title> Reference Detail </v-card-title>
        <table class="table">
          <thead>
            <tr>
              <th colspan="4" class="text-white">PURCHASE ORDER</th>
            </tr>
            <tr>
              <th colspan="2" class="text-white">Supplier</th>
              <th colspan="2" class="text-white">Bank Info</th>
            </tr>
            <tr>
              <td colspan="2">
                {{ supplierfObj.supplier_name.data }}
              </td>
              <td colspan="2">
                <b>Bank name:</b> {{ supplierfObj.bank.data }}<br />
                <b>Account no/IBAN:</b> {{ supplierfObj.bank_account_no.data
                }}<br />
                <b>Currency:</b> {{ poinfObj.currency.data }}<br />
                <b>Account name:</b> {{ supplierfObj.bank_account_name.data
                }}<br />
                <b>BIC/Swift Code:</b> {{ supplierfObj.bic_swift_code.data }}
              </td>
            </tr>
            <tr>
              <th class="text-white">Currency</th>
              <th class="text-white">Total Item Price</th>
              <th class="text-white">Charge Detail</th>
              <th class="text-white">Grand Total Price</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="text-align: center">
                {{ poinfObj.currency.data }}
              </td>
              <td style="text-align: center">
                {{ poinfObj.total_price.data }}
              </td>
              <td>
                <b>Charge 1:</b> {{ poinfObj.charge1.data }}<br />
                <b>Charge 1 Desc:</b> {{ poinfObj.charge1_desc.data }}<br />
                <b>Charge 2:</b> {{ poinfObj.charge2.data }}<br />
                <b>Charge 2 Desc:</b> {{ poinfObj.charge2_desc.data }}
              </td>
              <td style="text-align: center">
                {{ poinfObj.grand_total.data }}
              </td>
            </tr>
          </tbody>
        </table>
        <table class="table">
          <thead>
            <tr>
              <th colspan="4" class="text-white">INVOICE</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td
                colspan="2"
                class="text-white"
                style="background-color: #2b688c; text-align: center"
              >
                Total Amount
              </td>
              <td
                colspan="2"
                class="text-white"
                style="background-color: #2b688c; text-align: center"
              >
                Remaining Amount
              </td>
            </tr>
            <tr v-if="selected && selected.as_reference == 0">
              <td colspan="2" style="text-align: center">
                {{ poinfObj.grand_total.data }}
              </td>
              <td colspan="2" style="text-align: center">
                {{ Number(selected.remaining_payment).format(2, 3) }}
              </td>
            </tr>
            <tr v-if="selected && selected.as_reference == 1">
              <td colspan="2" style="text-align: center">
                {{ Number(selected.grand_total_invoice).format(2, 3) }}
              </td>
              <td colspan="2" style="text-align: center">
                {{ Number(selected.remaining_payment_2).format(2, 3) }}
              </td>
            </tr>
          </tbody>
        </table>
      </v-card>
      <br />
      <v-card flat>
        <v-card-title> Invoice Detail </v-card-title>
        <v-autoform v-model="billInf" hide-details></v-autoform>
        <div class="flex">
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            outlined
            small
            @click="saveBill"
            v-if="!history"
          >
            Save
          </v-btn>
        </div>
      </v-card>

      <!-- <table style="width: 100%; height: 100%;">
                <tr>
                    <td style="vertical-align: top;">
                        <v-autoform v-model="poInf" hide-details></v-autoform>
                    </td>
                    <td style="vertical-align: top;">
                        <v-autoform v-model="supplierInf" hide-details></v-autoform>
                    </td>
                    <td style="vertical-align: top;">
                        <v-autoform v-model="billInf" hide-details></v-autoform>
                        
                        <v-btn color="primary" outlined small @click="saveBill">
                            Save
                        </v-btn>
                    </td>
                </tr>
            </table> -->
    </v-action-dialog>

    <v-action-dialog
      @save="saveFile"
      title="Paid Info"
      v-model="dialogFile"
      v-if="history"
    >
      <v-autoform v-model="formFile" :valid="valid"></v-autoform>
    </v-action-dialog>

    <v-action-dialog
      :actions="false"
      title="PO Items"
      v-model="dialogPO"
      fullscreen
    >
      <invoice-item :data="invInfo"></invoice-item>
    </v-action-dialog>
  </v-container>
</template>

<style scoped>
.v-data-table__wrapper > table > tbody > tr > td {
  font-size: 0.775rem;
}
</style>
<style>
.v-data-table__wrapper > table > tbody > tr > td {
  font-size: 0.775rem;
}

table.table {
  border: 0px;
  border-collapse: collapse;
  font-size: 12px;
  width: 100%;
}

table.table tr th {
  border-bottom: 1px solid #c5c5c5;
  margin-bottom: 4px;
  background-color: #2b688c;
}

table.table tr:last-child th {
  height: 4px;
}

table.table th > div {
  padding: 0 4px;
  border-radius: 3px;
  text-align: center;
  white-space: nowrap;
}

table.table td > div {
  padding: 0 4px;
  border-radius: 3px;
  white-space: nowrap;
}

table.table td {
  padding: 1.5px;
}

table.table td,
table.table th {
  padding: 2px 4px;
  border: 1px solid black !important;
  min-width: 40px;
}

table.table td.table-title {
  font-weight: bold;
  text-align: center;
}
</style>

<script>
module.exports = {
  name: "invoice",
  props: {
    value: undefined,
    data: {
      type: Object,
    },
    showPaid: {
      type: Boolean,
      default: false,
    },
    tableOnly: {
      type: Boolean,
      default: false,
    },
    history: {
      type: Boolean,
      default: false,
    },
    disableDeleteButton: {
      type: Boolean,
      default: false,
    },
    disableEditButton: {
      type: Boolean,
      default: false,
    },
    itemsOptions: {
      type: Object,
      default: {
        filter: {
          payment_id: null,
        },
        filterType: {
          payment_id: "isnull",
        },
      },
    },
  },
  components: {
    "invoice-item": "url:ui/bom/payment/invoice_items.vue",
  },
  data: function () {
    return {
      valid: false,
      dialogFile: false,
      dialogPO: false,
      formFile: [
        {
          text: "File",
          value: "file",
          type: "file",
          required: true,
        },
        {
          text: "Payment Status",
          value: "is_paid",
          type: "switch",
          data_value: [0, 1],
          default: 0,
          required: false,
        },
        {
          text: "Payment Date",
          value: "paid_date",
          align: "start",
          type: "date",
          required: false,
        },
      ],
      name: "Invoice",
      processData: {},
      itemKey: "id",
      url: "bom/invoice",
      billInf: [
        {
          text: "Invoice Amount",
          value: "payment_pct_fiat",
          readonly: false,
          type: "float",
          form: true,
          default: 100,
          input: function (data) {
            try {
              var self = App.$get("invoice");
              self.$nextTick(function () {
                if (Number(data.data) > 100)
                  self.billfObj.payment_pct.data = 100;
                if (Number(data.data) < 0) self.billfObj.payment_pct.data = 0;
                self.billfObj.payment_pct.data =
                  Number(data.data) * (100 / self.selected.grand_total_price);
                self.billfObj.invoice_total_price.data =
                  Number(self.selected.grand_total_price) *
                    Number(self.billfObj.payment_pct.data / 100) +
                  Number(self.billfObj.invoice_charge.data || 0) -
                  Number(self.billfObj.invoice_reduction.data || 0);
                self.billInf = App.updateObject(self.billInf);
              });
            } catch (err) {
              console.log(err);
            }
          },
        },
        {
          text: "Invoice PCT %",
          value: "payment_pct",
          readonly: false,
          type: "float",
          form: true,
          default: 100,
          input: function (data) {
            try {
              var self = App.$get("invoice");
              self.$nextTick(function () {
                if (Number(data.data) > 100)
                  self.billfObj.payment_pct.data = 100;
                if (Number(data.data) < 0) self.billfObj.payment_pct.data = 0;
                self.billfObj.payment_pct_fiat.data =
                  Number(self.selected.grand_total_price) *
                  Number(self.billfObj.payment_pct.data / 100);
                self.billfObj.invoice_total_price.data =
                  Number(self.selected.grand_total_price) *
                    Number(self.billfObj.payment_pct.data / 100) +
                  Number(self.billfObj.invoice_charge.data || 0) -
                  Number(self.billfObj.invoice_reduction.data || 0);
                self.billInf = App.updateObject(self.billInf);
              });
            } catch (err) {
              console.log(err);
            }
          },
        },
        {
          text: "Exchange Rate",
          value: "exchange_rate",
          readonly: false,
          type: "float",
          form: false,
          default: 0,
        },
        {
          text: "Payment Amount",
          value: "payment_amount",
          readonly: false,
          type: "float",
          form: false,
          default: 0,
          input: function (data) {
            try {
              var self = App.$get("invoice");
              self.$nextTick(function () {
                self.billfObj.invoice_total_price.data =
                  Number(self.billfObj.payment_amount.data) +
                  Number(self.billfObj.invoice_charge.data || 0) -
                  Number(self.billfObj.invoice_reduction.data || 0);
                self.billInf = App.updateObject(self.billInf);
              });
            } catch (err) {
              console.log(err);
            }
          },
        },
        {
          text: "Charge 1",
          value: "charge1",
          type: "float",
          form: false,
          default: 0,
        },
        {
          text: "Charge 2",
          value: "charge2",
          type: "float",
          form: false,
          default: 0,
        },
        {
          text: "Invoice Charge",
          value: "invoice_charge",
          readonly: false,
          type: "float",
          precision: 2,
          input: function (data) {
            var self = App.$get("invoice");
            self.$nextTick(function () {
              if (self.selected.as_reference == 1)
                self.billfObj.invoice_total_price.data =
                  Number(self.billfObj.payment_amount.data) +
                  Number(self.billfObj.invoice_charge.data || 0) -
                  Number(self.billfObj.invoice_reduction.data || 0);
              else
                self.billfObj.invoice_total_price.data =
                  Number(self.selected.grand_total_price) *
                    (Number(self.billfObj.payment_pct.data) / 100) +
                  Number(self.billfObj.invoice_charge.data || 0) -
                  Number(self.billfObj.invoice_reduction.data || 0);
              self.billInf = App.updateObject(self.billInf);
            });
          },
        },
        {
          text: "Invoice Charge Note",
          value: "invoice_charge_note",
          readonly: false,
          type: "text",
        },
        {
          text: "Invoice Reduction",
          value: "invoice_reduction",
          readonly: false,
          type: "float",
          precision: 3,
          input: function (data) {
            var self = App.$get("invoice");
            self.$nextTick(function () {
              if (self.selected.as_reference == 1)
                self.billfObj.invoice_total_price.data =
                  Number(self.billfObj.payment_amount.data) +
                  Number(self.billfObj.invoice_charge.data || 0) -
                  Number(self.billfObj.invoice_reduction.data || 0);
              else
                self.billfObj.invoice_total_price.data =
                  Number(self.selected.grand_total_price) *
                    (Number(self.billfObj.payment_pct.data) / 100) +
                  Number(self.billfObj.invoice_charge.data || 0) -
                  Number(self.billfObj.invoice_reduction.data || 0);
              self.billInf = App.updateObject(self.billInf);
            });
          },
        },
        {
          text: "Invoice Reduction Note",
          value: "invoice_reduction_note",
          readonly: false,
          type: "text",
        },
        {
          text: "Reimburse To",
          value: "reimburse_id",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          data_value: [],
          disabled: false,
          visible: false,
          required: false,
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "bom/supplier",
          searchby: ["bank_info"],
          formatter: ["id", "bank_info"],
          options: {
            filter: {
              is_reimburse: 1,
              flag: 1,
            },
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          // "alias": "reimburse_name",
        },

        {
          text: "Invoice Total Amount",
          value: "invoice_total_price",
          readonly: true,
          type: "float",
          precision: 2,
        },
        {
          text: "Invoice Notes",
          value: "notes",
          readonly: false,
          type: "text",
        },
        {
          text: "Estimate Departure",
          value: "etd",
          readonly: false,
          type: "text",
        },

        {
          text: "Is Paid by Finance?",
          value: "is_paid",
          type: "switch",
          data_value: [0, 1],
          default: 0,
          form: true,
        },
      ],
      supplierInf: [
        {
          text: "Supplier ID",
          value: "supplier_id",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Supplier Name",
          value: "supplier_name",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Bank Name",
          value: "bank",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Account No/IBAN",
          value: "bank_account_no",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Currency",
          value: "supplier_currency",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Account Name",
          value: "bank_account_name",
          readonly: true,
          type: "varchar",
        },
        {
          text: "BIC/SWIFT Code",
          value: "bic_swift_code",
          readonly: true,
          type: "varchar",
        },
      ],
      poInf: [
        {
          text: "Currency",
          value: "currency",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Total Item Price",
          value: "total_price",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Charge 1",
          value: "charge1",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Charge 1 Description",
          value: "charge1_desc",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Charge 2",
          value: "charge2",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Charge 2 Description",
          value: "charge2_desc",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Grand Total Price",
          value: "grand_total",
          readonly: true,
          type: "varchar",
        },
      ],
      headers: [
        {
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
          url: "",
          searchby: [],
          formatter: [],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Invoice",
          value: "invoice",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: false,
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
          page: "1",
          limit: "10",
        },
        {
          text: "TL No",
          value: "payment_no",
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
          page: "1",
          limit: "10",
        },
        {
          text: "Invoice No",
          value: "invoice_no",
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
          page: "1",
          limit: "10",
        },
        {
          text: "Invoice Date",
          value: "invoice_date",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "date",
          disabled: false,
          visible: false,
          required: false,
          form: true,
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
          page: "1",
          limit: "10",
        },
        {
          text: "Title",
          value: "uraian",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
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
          page: "1",
          limit: "10",
        },
        {
          text: "PO No",
          value: "po_id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          data_value: [],
          disabled: false,
          visible: false,
          required: false,
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "bom/purchaseorder",
          searchby: ["po_no"],
          formatter: ["id", "po_no"],
          options: {
            filter: {
              total_payment_pct: "100",
              approved: 3,
            },
            filterType: {
              total_payment_pct: "<",
            },
            filterCondition: {},
            invoice: true,
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "po_no",
        },
        {
          text: "Type",
          value: "project_type",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          clearable: true,
          visible: true,
          required: true,
          form: false,
          filter: true,
          groupable: false,
          data_value: ["Project", "Operational", "Persediaan", "Asset", "R&D"],
          input: function (val) {
            var self = App.$get("invoice");
            if (val.data) {
              if (val.data == "Project") {
                self.headersObj.project_id.form = true;
                self.headersObj.budget_id.form = true;
                self.headersObj.type_operational_id.form = false;
                self.headersObj.sub_type_operational_id.form = false;
                self.headersObj.dept_id.form = false;
                self.headersObj.rnd_id.form = false;
              } else if (val.data == "Persediaan") {
                self.headersObj.project_id.form = false;
                self.headersObj.budget_id.form = false;
                self.headersObj.type_operational_id.form = false;
                self.headersObj.sub_type_operational_id.form = false;
                self.headersObj.rnd_id.form = false;
              } else if (val.data == "Asset") {
                self.headersObj.project_id.form = false;
                self.headersObj.budget_id.form = false;
                self.headersObj.type_operational_id.form = false;
                self.headersObj.sub_type_operational_id.form = false;
                self.headersObj.rnd_id.form = false;
              } else if (val.data == "R&D") {
                self.headersObj.project_id.form = false;
                self.headersObj.budget_id.form = false;
                self.headersObj.type_operational_id.form = false;
                self.headersObj.sub_type_operational_id.form = false;
                self.headersObj.dept_id.form = false;
                self.headersObj.rnd_id.form = true;
              } else {
                self.headersObj.project_id.form = false;
                self.headersObj.budget_id.form = false;
                self.headersObj.type_operational_id.form = true;
                self.headersObj.sub_type_operational_id.form = true;
                self.headersObj.dept_id.form = true;
                self.headersObj.rnd_id.form = false;
              }
            }
            self.headers = App.updateObject(self.headers);
          },
        },
        // {
        //     text: "Chart Of Account",
        //     value: "id",
        //     align: "start",
        //     sortable: true,
        //     filterable: false,
        //     divider: false,
        //     class: "",
        //     width: "auto",
        //     type: "list",
        //     data_value: [],
        //     disabled: false,
        //     visible: false,
        //     required: false,
        //     form: true,
        //     filter: true,
        //     groupable: false,
        //     url: "https://sales.buanamultiteknik.com/api/general/journal/chart-of-account",
        //     searchby: ["name"],
        //     formatter: ["id", "name"],
        //     options: {
        //         // filter: {
        //         //     total_payment_pct: "100",
        //         //     approved: 3,
        //         // },
        //         // filterType: {
        //         //     total_payment_pct: "<",
        //         // },
        //         // filterCondition: {},
        //         // invoice: true,
        //     },
        //     paging: true,
        //     page: "1",
        //     limit: "10",
        //     //alias: "po_no",
        // },

        {
          text: "Project",
          value: "project_id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: true,
          groupable: false,
          url: App.url + "project/project",
          searchby: ["full"],
          formatter: ["id", "full"],
          options: {
            filter: {
              flag: 1,
            },
            filterType: {},
            filterCondition: {},
          },
        },
        {
          text: "Type Department",
          value: "type_operational_id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: true,
          groupable: false,
          clearable: true,
          url: App.url + "bom/type",
          searchby: ["id", "name"],
          formatter: ["id", "name"],
          input: function (val) {
            var self = App.$get("invoice");
            if (val.data) {
              self.headersObj.sub_type_operational_id.options.filter.type_operational_id =
                val.data;
              self.headersObj.sub_type_operational_id.data = false;
              self.headersObj.sub_type_operational_id.update = false;
            }
            self.headers = App.updateObject(self.headers);
          },
          options: {
            filter: {},
            filterType: {},
            filterCondition: {
              id: "or",
              name: "or",
            },
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "type_operational",
        },
        {
          text: "Sub Type Department",
          value: "sub_type_operational_id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: true,
          groupable: false,
          clearable: true,
          url: App.url + "bom/subtype",
          searchby: ["id", "name"],
          formatter: ["id", "name"],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {
              id: "or",
              name: "or",
            },
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "sub_type_operational",
        },
        {
          text: "R&D",
          value: "rnd_id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: true,
          groupable: false,
          clearable: true,
          url: App.url + "pr/rnd",
          searchby: ["id", "name"],
          formatter: ["id", "name"],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {
              id: "or",
              name: "or",
            },
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "rnd_name",
        },
        {
          text: "Budget",
          value: "budget_id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: false,
          required: false,
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "budget/budget",
          searchby: ["budget_name"],
          formatter: ["id", "budget_name"],
          pk: "id",
          options: {
            filter: {
              project_id: -1,
            },
            filterType: {},
            filterCondition: {
              project_id: "and",
              budget_name: "or",
            },
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "budget_name",
          input: function (data) {
            const self = App.$get("invoice");

            const project_id = self.headersObj.project_id.data;
            const budget_id = self.headersObj.budget_id.data;

            /*if (project_id && budget_id) {
                        self.headersObj.text_sisa_budget.form = true;
                        self.headersObj.text_sisa_budget.data = `Menghitung Sisa Budget...`;
                        self.headers = App.updateObject(self.headers)
                    	
                        axios.get(`https://sales.buanamultiteknik.com/api/budget/project-budget/index?project_id=${project_id}&budget_id=${budget_id}`)
                        .then(function (response) {
                            try {
                                const remaining = response?.data?.budget?.remaining;
                    	
                                if (remaining === undefined || remaining === null) {
                                    throw new Error("Data kosong");
                                }
                    	
                                const formattedRemaining = new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR',
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }).format(remaining);
                    	
                                self.headersObj.text_sisa_budget.data = `Sisa Budget : ${formattedRemaining}`;
                    	
                            } catch (error) {
                                self.headersObj.text_sisa_budget.data = `Sisa Budget : -`;
                            }
                    	
                            self.headers = App.updateObject(self.headers);
                        })
                        .catch(function (error) {
                            // Kalau request benar-benar gagal (404, 500, dll)
                            self.headersObj.text_sisa_budget.data = `Sisa Budget : -`;
                            self.headers = App.updateObject(self.headers);
                        });
                    }*/
          },
        },
        {
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
        },
        {
          text: "Project Info",
          value: "project",
          type: "varchar",
          sortable: false,
          form: false,
          visible: true,
        },
        {
          text: "Total Payment Po",
          value: "total_payment_po",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          default: 100,
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: true,
          groupable: false,
        },
        {
          text: "Ref. Invoice No",
          value: "ref_invoice_no",
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
          form: true,
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
          page: "1",
          limit: "10",
        },
        {
          text: "Invoice Amount",
          value: "invoice_amount",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
          form: true,
          filter: false,
          groupable: false,
        },
        {
          text: "Receiver's Bank Info",
          value: "bank_info",
          type: "varchar",
          form: false,
          sortable: false,
        },
        {
          text: "Payment Info",
          value: "payment_info",
          sortable: false,
          readonly: false,
          type: "varchar",
          form: false,
          visible: false,
        },
        {
          text: "Payment Code",
          value: "kode_pembayaran",
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
          filter: false,
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
          page: "1",
          limit: "10",
        },
        {
          text: "Petty Cash",
          value: "petty_cash",
          type: "switch",
          data_value: [0, 1],
          default: 0,
          visible: false,
          // input: function(data) {
          // 	console.log('cash is', Number(data.data) == 1)
          //     if (Number(data.data) == 1) {
          //         App.$get("invoice").billfObj.reimburse_id.data = 96;
          //     } else {
          //         App.$get("invoice").billfObj.reimburse_id.data = null;
          //         // App.$get('invoice').headersObj.grand_total_price.data = null
          //     }
          //     App.$get("invoice").billfObj = App.updateObject(App.$get("invoice").billfObj);
          // },
        },
        {
          text: "Payment Status",
          value: "is_paid",
          type: "switch",
          data_value: [0, 1],
          required: false,
          form: false,
        },
        {
          text: "As Reference",
          value: "as_reference",
          type: "switch",
          data_value: [0, 1],
          default: 0,
          input: function (data) {
            if (data.data == 1) {
              App.$get("invoice").headersObj.supplier_id.data = null;
              App.$get("invoice").headersObj.charge1.data = 0;
              App.$get("invoice").headersObj.charge1_desc.data = "";
              App.$get("invoice").headersObj.charge2.data = 0;
              App.$get("invoice").headersObj.charge2_desc.data = "";
              App.$get("invoice").headersObj.total_price.data = 0;
              App.$get("invoice").headersObj.payment_pct.data = 100;

              App.$get("invoice").headersObj.uraian.form = true;
              App.$get("invoice").headersObj.supplier_id.form = true;
              App.$get("invoice").headersObj.charge1.form = true;
              App.$get("invoice").headersObj.charge1_desc.true = true;
              App.$get("invoice").headersObj.charge2.form = true;
              App.$get("invoice").headersObj.charge2_desc.form = true;
              App.$get("invoice").headersObj.total_price.form = true;

              App.$get("invoice").headersObj.project_id.form = true;
              App.$get("invoice").headersObj.project_type.form = true;

              App.$get("invoice").headersObj.payment_pct.form = true;
              // App.$get('invoice').headersObj.grand_total_price.form = true
              App.$get("invoice").headersObj.po_id.form = false;

              App.$get("invoice").headersObj.po_id.data = null;
            } else {
              App.$get("invoice").headersObj.project_id.form = false;

              App.$get("invoice").headersObj.uraian.form = false;
              App.$get("invoice").headersObj.supplier_id.form = false;
              App.$get("invoice").headersObj.charge1.form = false;
              App.$get("invoice").headersObj.charge1_desc.true = false;
              App.$get("invoice").headersObj.charge2.form = false;
              App.$get("invoice").headersObj.charge2_desc.form = false;
              App.$get("invoice").headersObj.total_price.form = false;
              App.$get("invoice").headersObj.payment_pct.form = false;
              // App.$get('invoice').headersObj.grand_total_price.form = false
              App.$get("invoice").headersObj.po_id.form = true;

              App.$get("invoice").headersObj.uraian.data = null;
              App.$get("invoice").headersObj.supplier_id.data = null;
              App.$get("invoice").headersObj.charge1.data = null;
              App.$get("invoice").headersObj.charge1_desc.data = null;
              App.$get("invoice").headersObj.charge2.data = null;
              App.$get("invoice").headersObj.charge2_desc.data = null;
              App.$get("invoice").headersObj.total_price.data = null;
              App.$get("invoice").headersObj.payment_pct.data = null;
              // App.$get('invoice').headersObj.grand_total_price.data = null
            }
          },
          visible: false,
        },
        {
          text: "Use Credit Note ?",
          value: "use_credit_note",
          type: "switch",
          data_value: [0, 1],
          default: 0,
          visible: false,
          input: function (data) {
            console.log("credit note is", Number(data.data) == 1);
            if (Number(data.data) == 1) {
              App.$get("invoice").headersObj.credit_note_id.form = true;
              App.$get("invoice").headersObj.credit_note_amount.form = true;
            } else {
              App.$get("invoice").headersObj.credit_note_id.form = false;
              App.$get("invoice").headersObj.credit_note_amount.form = false;
            }
          },
        },
        {
          text: "Credit Note",
          value: "credit_note_id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
          groupable: false,
          url: App.url + "bom/creditnote",
          search: ["credit_no"],
          formatter: ["id", "credit_no"],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Credit Note Amount",
          value: "credit_note_amount",
          readonly: false,
          type: "float",
          form: false,
          default: 0,
          visible: false,
          required: false,
          filter: false,
          groupable: false,
        },
        {
          text: "Document URL",
          value: "invoice_doc_url",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          disabled: false,
          visible: false,
          required: false,
          form: true,
          filter: false,
          groupable: false,
        },
        {
          text: "Payment Status",
          value: "is_paid",
          readonly: false,
          type: "switch",
          form: false,
          default: 0,
          data_value: [0, 1],
          visible: false,
        },
        {
          text: "Supplier",
          value: "supplier_id",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          data_value: [],
          filter: false,
          groupable: false,
          url: App.url + "bom/supplier",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Total",
          value: "total_price",
          readonly: false,
          type: "float",
          form: false,
          default: 100,
          visible: false,
        },
        {
          text: "Total Paid Fiat",
          value: "total_paid_fiat",
          readonly: false,
          type: "float",
          form: false,
          default: 100,
          visible: false,
        },
        {
          text: "Charge 1",
          value: "charge1",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
          precision: 2,
          groupable: false,
        },
        {
          text: "Charge 1 Desc",
          value: "charge1_desc",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
          groupable: false,
        },
        {
          text: "Charge 2",
          value: "charge2",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
          precision: 2,
          groupable: false,
        },
        {
          text: "Charge 2 Desc",
          value: "charge2_desc",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
          groupable: false,
        },
        {
          text: "Payment %",
          value: "payment_pct",
          readonly: false,
          type: "float",
          form: false,
          default: 100,
          filter: true,
          visible: false,
        },
        {
          text: "Due date",
          value: "due_date",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "date",
          disabled: false,
          visible: false,
          required: false,
          form: true,
          filter: false,
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
          page: "1",
          limit: "10",
        },
        {
          text: "Created",
          value: "created",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
          groupable: false,
        },
        {
          text: "Created By",
          value: "created_by",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: true,
          groupable: false,
          url: App.url + "users/info",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {
              group_name: "master",
              sub_group_name: "master",
            },
            filterType: {},
            filterCondition: {
              group_name: "or",
              sub_group_name: "or",
            },
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "created_by_name",
        },
        {
          text: "Created Date",
          value: "created_date",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "date",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
          groupable: false,
        },
        {
          text: "Created Date",
          value: "crea_date",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "date",
          disabled: false,
          visible: false,
          required: true,
          form: false,
          filter: true,
          groupable: false,
          url: "",
          search: [],
          formatter: [],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Modified",
          value: "modified",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
          groupable: false,
        },
        {
          text: "Modified By",
          value: "modified_by",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: true,
          groupable: false,
          url: App.url + "users/info",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {
              group_name: "master",
              sub_group_name: "master",
            },
            filterType: {},
            filterCondition: {
              group_name: "or",
              sub_group_name: "or",
            },
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "modified_by_name",
        },
        {
          text: "Modified Date",
          value: "modified_date",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "date",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
          groupable: false,
        },
        {
          text: "Modified Date",
          value: "mod_date",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "date",
          disabled: false,
          visible: false,
          required: true,
          form: false,
          filter: true,
          groupable: false,
          url: "",
          search: [],
          formatter: [],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        /* ,
                {
                    "text": "Grand Total Price",
                    "value": "grand_total_price",
                    "readonly": false,
                    "type": "float",
                    "form": false,
                    "default": 100
                } */
        {
          text: "",
          value: "data-table-expand",
        },
      ],
      dialogItem: false,
      selected: false,
      dataid: {},
      apiData: [],
      isProjectInfoLoading: false,
      projectInfoError: null,
    };
  },
  computed: {
    invInfo: function () {
      var self = this;
      return {
        invoice_id: self.selected.id,
      };
    },
    headersObj: function () {
      var tmp = {},
        self = this;
      Object.keys(self.headers).map((key) => {
        var val = self.headers[key];
        tmp[val.value] = val;
      });
      return tmp;
    },
    poinfObj: function () {
      var tmp = {},
        self = this;
      Object.keys(self.poInf).map((key) => {
        var val = self.poInf[key];
        tmp[val.value] = val;
      });
      return tmp;
    },
    supplierfObj: function () {
      var tmp = {},
        self = this;
      Object.keys(self.supplierInf).map((key) => {
        var val = self.supplierInf[key];
        tmp[val.value] = val;
      });
      return tmp;
    },
    billfObj: function () {
      var tmp = {},
        self = this;
      Object.keys(self.billInf).map((key) => {
        var val = self.billInf[key];
        tmp[val.value] = val;
      });
      return tmp;
    },
  },
  methods: {
    toNumber: function (val) {
      if (val === null || val === undefined || val === "") {
        return 0;
      }
      if (typeof val === "number") {
        return val;
      }
      var str = String(val).trim().replace(/\s/g, "");
      if (!str) {
        return 0;
      }

      var hasDot = str.indexOf(".") !== -1;
      var hasComma = str.indexOf(",") !== -1;

      if (hasDot && hasComma) {
        var lastDot = str.lastIndexOf(".");
        var lastComma = str.lastIndexOf(",");
        if (lastComma > lastDot) {
          str = str.replace(/\./g, "").replace(",", ".");
        } else {
          str = str.replace(/,/g, "");
        }
      } else if (hasComma && !hasDot) {
        str = str.replace(",", ".");
      } else {
        str = str.replace(/,/g, "");
      }

      return Number(str) || 0;
    },
    formatAmount: function (val) {
      var num = this.toNumber(val);
      if (typeof Number(num).format === "function") {
        return Number(num).format(2, 3);
      }
      return Number(num).toLocaleString("id-ID", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    },
    getAllocationBase: function (item, project) {
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
    getProjectTotalForRef: function (refNo) {
      var projects = this.getProjectList(refNo);
      var total = 0;
      for (var i = 0; i < projects.length; i++) {
        total += this.getRowTotalPrice(projects[i]);
      }
      return total;
    },
    getCombinedAllocationTotalForRef: function (refNo) {
      var total = this.getProjectTotalForRef(refNo) + this.getPersediaanTotal(refNo);
      if (total > 0) {
        return total;
      }
      return this.getProjectTotalForRef(refNo);
    },
    getAllocationNumerator: function (item, project) {
      var refNo = (item && (item.po_no || item.invoice_no)) || "";
      var invoiceTotal = this.toNumber(item && item.invoice_total_price);
      var projectTotal = this.getRowTotalPrice(project);

      if (invoiceTotal > 0 && projectTotal > 0 && refNo) {
        var grandAllocationTotal = this.getCombinedAllocationTotalForRef(refNo);
        if (grandAllocationTotal > 0) {
          return invoiceTotal * (projectTotal / grandAllocationTotal);
        }
      }

      var paidAmount = this.toNumber(project && project.paid_amount);
      if (paidAmount > 0) {
        return paidAmount;
      }
      return projectTotal;
    },
    getAllocationPct: function (item, project) {
      var total = this.getAllocationNumerator(item, project);
      var base = this.getAllocationBase(item, project);
      if (total > 0 && base > 0) {
        return ((total / base) * 100).toFixed(2);
      }
      return "0.00";
    },
    getProjectList: function (refNo) {
      if (!refNo || !Array.isArray(this.apiData)) {
        return [];
      }
      var list = this.apiData.filter(function (p) {
        var type = String(p.project_type || "")
          .trim()
          .toLowerCase();
        return (
          p.po_no === refNo &&
          (p.project_no || p.project_name) &&
          type !== "r&d" &&
          type !== "asset" &&
          type !== "operational" &&
          type !== "persediaan"
        );
      });
      var byKey = {};
      for (var i = 0; i < list.length; i++) {
        var p = list[i] || {};
        var no = (p.project_no || "").trim();
        var baseNo = no.split(" ")[0];
        var key =
          baseNo ||
          (p.project_name || "").trim() ||
          ((p.project_type || "").trim() + "|" + (p.allocation || "").trim());
        if (!key) {
          continue;
        }
        if (!byKey[key]) {
          byKey[key] = p;
          continue;
        }
        var current = byKey[key];
        var currentIsNis = String(current.allocation || "").toUpperCase() === "NIS";
        var candidateIsNis = String(p.allocation || "").toUpperCase() === "NIS";
        if (currentIsNis && !candidateIsNis) {
          byKey[key] = p;
        }
      }
      var result = Object.keys(byKey).map(function (k) {
        return byKey[k];
      });
      return result;
    },
    getPersediaanList: function (refNo) {
      if (!refNo || !Array.isArray(this.apiData)) {
        return [];
      }
      return this.apiData.filter(function (p) {
        return p.po_no === refNo && String(p.project_type || "").trim().toLowerCase() === "persediaan";
      });
    },
    hasPersediaan: function (refNo) {
      return this.getPersediaanList(refNo).length > 0;
    },
    getRndList: function (refNo) {
      if (!refNo || !Array.isArray(this.apiData)) {
        return [];
      }
      return this.apiData.filter(function (p) {
        return p.po_no === refNo && String(p.project_type || "").trim().toLowerCase() === "r&d";
      });
    },
    hasAsset: function (refNo) {
      if (!refNo || !Array.isArray(this.apiData)) {
        return false;
      }
      return this.apiData.some(function (p) {
        return p.po_no === refNo && String(p.project_type || "").trim().toLowerCase() === "asset";
      });
    },
    hasProjectTypeInfo: function (refNo) {
      return (
        this.getProjectList(refNo).length > 0 ||
        this.hasPersediaan(refNo) ||
        this.getOperationalList(refNo).length > 0 ||
        this.getRndList(refNo).length > 0 ||
        this.hasAsset(refNo)
      );
    },
    getRowTotalPrice: function (row) {
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
    getPersediaanTotal: function (refNo) {
      var list = this.getPersediaanList(refNo);
      var total = 0;
      for (var i = 0; i < list.length; i++) {
        total += this.getRowTotalPrice(list[i]);
      }
      return total;
    },
    getPersediaanAllocationBase: function (item, refNo) {
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
    getPersediaanAllocationNumerator: function (item, refNo) {
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
    getPersediaanAllocationPct: function (item, refNo) {
      var numerator = this.getPersediaanAllocationNumerator(item, refNo);
      var base = this.getPersediaanAllocationBase(item, refNo);
      if (numerator > 0 && base > 0) {
        return ((numerator / base) * 100).toFixed(2);
      }
      return "0.00";
    },
    getOperationalList: function (refNo) {
      if (!refNo || !Array.isArray(this.apiData)) {
        return [];
      }
      return this.apiData.filter(function (p) {
        return (
          p.po_no === refNo &&
          (p.project_type === "Operational" ||
            p.dept_code ||
            p.dept_name ||
            p.type_operational_name ||
            p.type_sub_operational_name)
        );
      });
    },
    fetchData: function () {
      this.isProjectInfoLoading = true;
      this.projectInfoError = null;

      const apiUrl = "https://main.buanamultiteknik.com/api/bom/pobudget/monitoringproject?limit=-1";

      axios
        .get(apiUrl)
        .then((response) => {
          this.isProjectInfoLoading = false;
          if (response.data && response.data.status && response.data.data) {
            this.apiData = response.data.data;
          } else {
            this.projectInfoError = "Format data project tidak valid";
          }
        })
        .catch((error) => {
          this.isProjectInfoLoading = false;
          console.error("Error fetching data:", error);
          this.projectInfoError =
            "Gagal mengambil data project. Pastikan Anda terhubung ke jaringan internal.";
        });
    },
    saveFile: async function () {
      var self = this;
      const formData = new FormData();
      self.formFile.map(function (val, i) {
        var key = val.value;
        formData.append(key, val.data);
      });
      formData.append("id", self.selected.id);
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
        self.getAttachment();
      }
    },
    checkHeader: function () {
      var self = this;
      if (this.selected.as_reference == 1) {
        self.headersObj.uraian.form = true;
        self.headersObj.supplier_id.form = true;
        self.headersObj.charge1.form = true;
        self.headersObj.charge1_desc.true = true;
        self.headersObj.charge2.form = true;
        self.headersObj.charge2_desc.form = true;
        self.headersObj.total_price.form = true;
        self.headersObj.payment_pct.form = true;
        self.billfObj.exchange_rate.form = true;
        self.headersObj.po_id.form = false;
        self.headersObj.project_id.form = true;
        // self.billfObj.reimburse_id.data="96";
      } else {
        self.headersObj.uraian.form = false;
        self.headersObj.supplier_id.form = false;
        self.headersObj.charge1.form = false;
        self.headersObj.charge1_desc.true = false;
        self.headersObj.charge2.form = false;
        self.headersObj.charge2_desc.form = false;
        self.headersObj.total_price.form = false;
        self.headersObj.payment_pct.form = false;
        self.billfObj.exchange_rate.form = false;
        self.headersObj.po_id.form = true;
        self.headersObj.project_id.form = false;
        // self.billfObj.reimburse_id.data=null;
      }
      // console.log(this.selected.use_credit_note);
      if (this.selected.use_credit_note == 1) {
        self.headersObj.credit_note_id.form = true;
        self.headersObj.credit_note_amount.form = true;
      } else {
        self.headersObj.credit_note_id.form = false;
        self.headersObj.credit_note_amount.form = false;
      }
    },
    resetHeader: function () {
      var self = this;
      self.headersObj.uraian.form = false;
      self.headersObj.supplier_id.form = false;
      self.headersObj.charge1.form = false;
      self.headersObj.charge1_desc.true = false;
      self.headersObj.charge2.form = false;
      self.headersObj.charge2_desc.form = false;
      self.headersObj.total_price.form = false;
      self.headersObj.payment_pct.form = false;
      // self.headersObj.grand_total_price.form = false
      self.headersObj.po_id.form = true;
      self.headersObj.credit_note_id.form = false;
      self.headersObj.credit_note_amount.form = false;
    },
    openPOItems: function () {
      var self = this;
      self.dialogPO = true;
    },
    openDetail: async function () {
      var self = this;
      if (self.selected.as_reference == 1) {
        self.billfObj.payment_pct.form = true;
        if (self.poinfObj.currency.data != null)
          if (self.poinfObj.currency.data.toUpperCase() != "IDR") {
            /* var response = await axios.get(App.url+'bom/payment/exchange', {
                            params: {
                                currency: self.poinfObj.currency.data.toUpperCase()
                            }
                        })
                        var data = response.data
                        self.billfObj.exchange_rate.data = data.match(/\d+(.\d+)/)[0] */
            axios
              .get(App.url + "bom/payment/exchange", {
                params: {
                  currency: self.poinfObj.currency.data.toUpperCase(),
                },
              })
              .then(function (response) {
                var data = response.data;
                self.billfObj.exchange_rate.data = data.match(/\d+(.\d+)/)[0];
              })
              .catch(function (error) {});
          }
        //self.billfObj.payment_amount.form = true
      } else {
        self.billfObj.payment_pct.form = true;
        // self.billfObj.reimburse_id.data=null;
        //self.billfObj.payment_amount.form = false
      }

      if (self.selected.petty_cash == 1) {
        self.billfObj.reimburse_id.data = "96";
      }

      self.billInf = App.updateObject(self.billInf);
      self.dialogItem = true;
    },
    onSelectedRow: function (val) {
      var self = this;
      // console.log(self);
      if (val === undefined) {
        self.selected = false;
      } else {
        val.as_reference = parseInt(val.as_reference);
        val.petty_cash = parseInt(val.petty_cash);
        val.use_credit_note = parseInt(val.use_credit_note);

        self.checkHeader();

        if (val.payment_id !== null) {
          self.disableDeleteButton = true;
          self.disableEditButton = true;
        } else {
          self.disableDeleteButton = false;
          self.disableEditButton = false;
        }

        if (val.payment_flag == 0) {
          self.disableDeleteButton = false;
          self.disableEditButton = false;
        }

        if (val.payment_approved < 0) {
          self.disableDeleteButton = false;
          self.disableEditButton = false;
        }
        self.selected = val;

        self.poinfObj.currency.data = val.currency;
        self.poinfObj.total_price.data = Number(val.total_price).format(2, 3);
        self.poinfObj.charge1.data = Number(val.charge1).format(2, 3);
        self.poinfObj.charge1_desc.data = val.charge1_desc;
        self.poinfObj.charge2.data = Number(val.charge2).format(2, 3);
        self.poinfObj.charge2_desc.data = val.charge2_desc;
        self.selected.remaining_payment =
          val.grand_total_price - val.total_paid_fiat;
        self.selected.remaining_payment_2 =
          val.grand_total_invoice - val.total_paid_fiat;
        self.selected.grand_total_invoice = val.grand_total_invoice;
        self.selected.as_reference = val.as_reference;
        self.selected.use_credit_note = val.use_credit_note;
        self.selected.use_credit_note = val.use_credit_note;
        // console.log(val.as_reference);
        // console.log(val.use_credit_note);
        // console.log(val.use_credit_note);

        self.poinfObj.grand_total.data = Number(val.grand_total_price).format(
          2,
          3,
        );
        // self.poinfObj.total_payment_po.data=Number(val.total_payment_po).format(2, 3);

        self.supplierfObj.supplier_id.data = val.supplier_id;
        self.supplierfObj.supplier_name.data = val.supplier_name;
        self.supplierfObj.bank.data = val.bank;
        self.supplierfObj.bank_account_no.data = val.bank_account_no;
        self.supplierfObj.supplier_currency.data = val.supplier_currency;
        self.supplierfObj.bank_account_name.data = val.bank_account_name;
        self.supplierfObj.bic_swift_code.data = val.bic_swift_code;

        self.billfObj.payment_pct_fiat.data =
          val.payment_pct_fiat == 0 && val.payment_pct != 0
            ? Number(self.selected.grand_total_price) *
              Number(self.selected.payment_pct / 100)
            : Number(val.payment_pct_fiat);
        self.billfObj.payment_pct.data = Number(val.payment_pct).format(2);
        self.billfObj.invoice_charge.data = numberFormat(
          Number(val.invoice_charge),
          4,
        );
        self.billfObj.invoice_charge_note.data = val.invoice_charge_note;
        self.billfObj.invoice_reduction.data = numberFormat(
          Number(val.invoice_reduction),
          4,
        );
        self.billfObj.invoice_reduction_note.data = val.invoice_reduction_note;
        self.billfObj.reimburse_id.data = val.reimburse_id;
        self.billfObj.notes.data = val.notes;
        self.billfObj.etd.data = val.etd;
        self.billfObj.invoice_total_price.data = Number(
          val.invoice_total_price,
        ).format(2, 3);
      }
    },
    onSelectedRowAll: function (val) {
      var self = this;
      self.selectedAll = val;
    },
    openReport2: function () {
      var self = this;
      //dialogReport=true
      var name = self.selected.po_no.replace(/\//g, "_").replace(/\-/g, "_");
      var randomid = randomId();
      window.open(
        "https://internal.buanamultiteknik.com/api/data/reportpo2?id=" +
          self.selected.po_id +
          "&filename=" +
          name +
          "&idx=" +
          randomid,
      );
    },
    saveBill: async function () {
      var self = this;
      self.$nextTick(async () => {
        setTimeout(async () => {
          // if(self.billfObj.payment_pct_fiat.data > self.selected.remaining_payment){
          // 	App.errorMsg("Payment Melebihi Remaining Payment!")
          // }
          // else{
          try {
            var res = await axios.post(App.url + self.url + "/savebill", {
              id: self.selected.id,
              reimburse_id: self.billfObj.reimburse_id.data,
              payment_pct: self.billfObj.payment_pct.data,
              payment_pct_fiat: self.billfObj.payment_pct_fiat.data,
              payment_amount: self.billfObj.payment_amount.data,
              invoice_charge: self.billfObj.invoice_charge.data,
              invoice_charge_note: self.billfObj.invoice_charge_note.data,
              invoice_reduction: self.billfObj.invoice_reduction.data,
              invoice_reduction_note: self.billfObj.invoice_reduction_note.data,
              notes: self.billfObj.notes.data,
              etd: self.billfObj.etd.data,
              is_paid: self.billfObj.is_paid.data,
            });
            if (!res.data.status) throw res.data;
            App.successMsg();
            self.$refs.template.getItems();
            self.dialogItem = false;
          } catch (e) {
            App.errorMsg(e);
          }
          // }
        });
      });
    },
  },
  mounted: function () {
    var self = this;
    self.fetchData();
    if (self.showPaid) {
      self.headersObj.is_paid.visible = true;
    }
    if (self.history) {
      self.headersObj.po_id.options = {
        filter: {},
        filterType: {},
        filterCondition: {},
      };
      self.billfObj.exchange_rate.readonly = true;
      self.billfObj.payment_pct_fiat.readonly = true;
      self.billfObj.payment_pct.readonly = true;
      self.billfObj.payment_amount.readonly = true;
      self.billfObj.invoice_charge.readonly = true;
      self.billfObj.invoice_charge_note.readonly = true;
      self.billfObj.invoice_reduction.readonly = true;
      self.billfObj.invoice_reduction_note.readonly = true;
      self.billfObj.reimburse_id.readonly = true;
      self.billfObj.notes.readonly = true;
      self.billfObj.etd.readonly = true;
      self.billfObj.is_paid.readonly = true;
      self.headersObj.payment_info.visible = true;
    }
  },
};
</script>
