<template>
  <v-container
    v-observe-visibility="onVisible"
    class="no-padding-margin"
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
      @after-get-items="getParent"
      @open-add="checkDate"
      :table-loading.sync="loading"
      :show-expand="showExpand"
      :single-expand="singleExpand"
      :data="data"
      :items-options="itemsOptions"
      @update:selected-row="onSelectedRow"
      @update:selected-row-all="onSelectedRowAll"
      delete-mode="delete"
      :active-column="activeColumn"
      v-model="value"
      ref="template"
      :item-key="itemKey"
      :url="url"
      :headers="headers"
      :name="name"
      :table-only="tableOnly"
      :table-fixed-header="tableFixedHeader"
      @save="save"
    >
      <template v-slot:menu-after-filter>
        <v-btn small color="info" outlined @click="uploadExcel = true"
          >Upload Excel</v-btn
        >
      </template>
      <template v-slot:title-body v-if="$refs.template">
        <b>Count Rows: </b>{{ $refs.template.itemsTotal }},
        <div style="flex: 1"></div>
        <b>SUM: </b>{{ Number(sum).format(2, 3) }}
      </template>
      <!-- <template v-slot:expanded-item="props">
				<td ref="expanded" :colspan="props.headers.length" style="height: auto;" :key="props.item[itemKey]">
					
				</td>
			</template> -->
      <!-- 
			<template v-slot:expanded-item="props">
				<td :colspan="props.headers.length" style="height: auto;">
				</td>
			</template>
			 -->
      <!-- 
			<template v-slot:item.item_name="props">
			</template>
			 -->
      <!-- 
			<template v-slot:append-dialog-add>>
			</template>
			 -->
      <!-- 
            <template v-slot:prepend-menu>
            </template>
			 -->
      <!-- 
            <template v-slot:menu-after-edit>
            </template>
			 -->
      <!-- 
            <template v-slot:append-menu>
            </template>
			 -->
      <template v-slot:item.saldo_berjalan="props">
        {{ Number(props.item.saldo_berjalan).format(2, 3) }}
      </template>

      <template v-slot:item.bukti="props">
        <p v-if="props.item.bukti && props.item.bukti !== '-'">
          <a :href="props.item.bukti" target="_blank">File</a>
        </p>
        <p v-else>-</p>
      </template>

      <template v-slot:item.debet="props">
        {{ Number(props.item.debet).format(2, 3) }}
      </template>
      <template v-slot:item.kredit="props">
        {{ Number(props.item.kredit).format(2, 3) }}
      </template>
      <template v-slot:item.ppn="props">
        {{ Number(props.item.ppn).format(2, 3) }}
      </template>
      <template v-slot:item.pph23="props">
        {{ Number(props.item.pph23).format(2, 3) }}
      </template>
      <template v-slot:item.other="props">
        {{ Number(props.item.other).format(2, 3) }}
      </template>
      <template v-slot:item.net_amount="props">
        {{ Number(props.item.net_amount).format(2, 3) }}
      </template>
    </v-template>
    <v-action-dialog v-model="uploadExcel" @save="saveTransactionlist">
      <v-excel-reader v-model="file" ref="file"></v-excel-reader>

      <table
        class="simple-table default-table default"
        style="margin-top: 10px"
      >
        <thead>
          <tr>
            <th>#</th>
            <th>rincian</th>
            <th>bukti</th>
            <th>pos_akun_kode</th>
            <th>debet</th>
            <th>kredit</th>
            <th>date_voucher</th>
            <th>no_voucher</th>
            <th>rekon_bank</th>
            <th>ppn</th>
            <th>pph23</th>
            <th>other</th>
            <th>beneficiary_name</th>
            <th>bank_account</th>
            <th>npwp</th>
            <th>po_reference</th>
            <th>invoice_date</th>
            <th>due_date</th>
            <th>invoice_ref</th>
            <th>nsfp</th>
            <th>status</th>
            <th>notes</th>
            <!-- <th>Date Voucher</th>
						<th>No. Voucher</th>
						<th>Rekon Bank</th>
						<th>POS</th>
						<th style="width: 15%">Debet</th>
						<th style="width: 15%">Kredit</th>
						<th style="width: 15%">PPN</th>
						<th style="width: 15%">PPH23</th>
						<th style="width: 15%">Other</th>
						<th style="width: 15%">Net Amount</th>
						<th></th>
						<th>Description</th>
						<th>Beneficiary Name</th>
						<th>Bank Account</th>
						<th>NPWP</th>
						<th>PO Reference</th>
						<th>Invoice Date</th>
						<th>Due Date</th>
						<th>Invoice Ref</th>
						<th>NSFP - VAT IN</th>
						<th>Status</th>
						<th>Notes</th> -->
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in data" :key="index">
            <td>
              <v-icon small v-if="item[0] === true" color="success"
                >mdi-check</v-icon
              ><v-icon small v-else color="error">mdi-close</v-icon>
            </td>
            <td>{{ item[1] }}</td>
            <td>{{ item[2] }}</td>
            <td>{{ item[3] }}</td>
            <td>{{ item[4] }}</td>
            <td>{{ item[5] }}</td>
            <td>{{ item[6] }}</td>
            <td>{{ item[7] }}</td>
            <td>{{ item[8] }}</td>
            <td>{{ item[9] }}</td>
            <td>{{ item[10] }}</td>
            <td>{{ item[11] }}</td>
            <td>{{ item[12] }}</td>
            <td>{{ item[13] }}</td>
            <td>{{ item[14] }}</td>
            <td>{{ item[15] }}</td>
            <td>{{ item[16] }}</td>
            <td>{{ item[17] }}</td>
            <td>{{ item[18] }}</td>
            <td>{{ item[19] }}</td>
            <td>{{ item[20] }}</td>
            <td>{{ item[21] }}</td>
          </tr>
        </tbody>
      </table>
    </v-action-dialog>
  </v-container>
</template>

<style scoped></style>

<script>
module.exports = {
  name: "detail",
  creator: "",
  components: {
    /* 'document-form': 'url:ui/ewis/test/document_form.vue' */
  },
  props: {
    value: undefined,
    data: {
      type: Object,
    },
    tableOnly: {
      type: Boolean,
      default: false,
    },
    tableFixedHeader: {
      type: Boolean,
      default: true,
    },
    disableTable: {
      type: Boolean,
      default: false,
    },
    activeColumn: {
      default: "flag",
    },
    showExpand: {
      type: Boolean,
      default: false,
    },
    singleExpand: {
      type: Boolean,
      default: true,
    },
    itemsOptions: {
      type: Object,
      default: {
        filter: {},
        filterType: {},
      },
    },
  },
  data: function () {
    return {
      name: "Daily Transaction Detail",
      itemKey: "id",
      url: "finance/pettycashdetail",
      loading: false,
      uploadExcel: false,
      data: [],
      file: false,
      sum: 0,
      headers: [
        {
          text: "-",
          value: "petty_cash_id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "int",
          disabled: false,
          visible: false,
          required: true,
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
          text: "Id",
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
          required: true,
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
          text: "Saldo",
          value: "saldo_berjalan",
          align: "float",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "date",
          disabled: false,
          visible: true,
          required: true,
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
          text: "Date Voucher",
          value: "date_voucher",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "date",
          disabled: false,
          visible: true,
          required: true,
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
          text: "Pos Akun",
          value: "nama",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: true,
          groupable: false,
        },
        {
          text: "Kode",
          value: "pos_akun_kode",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: true,
          groupable: false,
        },
        {
          text: "No. Voucher",
          value: "no_voucher",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
          required: true,
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
          text: "Rekon Bank",
          value: "rekon_bank",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "date",
          disabled: false,
          visible: true,
          required: true,
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
          text: "Rincian",
          value: "rincian",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          disabled: false,
          visible: true,
          required: true,
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
          text: "Bukti",
          value: "bukti",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "file",
          // "rules": [/* v => (v || '' ).trim().length > 10 */ v =>
          // 	new RegExp(
          // 		"^([a-zA-Z]+:\\/\\/)?" + // protocol
          // 		"((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|" + // domain name
          // 		"((\\d{1,3}\\.){3}\\d{1,3}))" + // OR IP (v4) address
          // 		"(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*" + // port and path
          // 		"(\\?[;&a-z\\d%_.)" + // query string
          // 		"(\\#[-a-z\\d_]*)?$", // fragment locator
          //         "/^(?:(?:https?|ftp):\/\/)?(?:www\.)?[a-z0-9-]+(?:\.[a-z0-9-]+)+[^\s]*$/",
          // 		"i",
          // 	).test(v) || 'Datasheet must be valid URL!'],
          disabled: false,
          visible: true,
          required: true,
          form: true,
          filter: false,
          groupable: false,
          // "url": "",
          // "searchby": [],
          // "formatter": [],
          // "options": {
          // 	"filter": {},
          // 	"filterType": {},
          // 	"filterCondition": {}
          // },
          // "paging": true,
          // "page": "0",
          // "limit": "10"
        },
        {
          text: "Pos Akun Kode",
          value: "pos_akun_kode",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: false,
          required: true,
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "finance/posakun",
          searchby: ["kode", "nama"],
          formatter: ["kode", ["kode", "nama", "tipe_akun"]],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {
              kode: "or",
              nama: "or",
            },
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Debet",
          value: "debet",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          disabled: false,
          visible: true,
          required: true,
          default: 0,
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
          text: "Kredit",
          value: "kredit",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          disabled: false,
          visible: true,
          required: true,
          default: 0,
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
          text: "PPN 11%",
          value: "is_ppn",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "switch",
          disabled: false,
          visible: false,
          required: true,
          default: 0,
          form: true,
          filter: true,
          groupable: false,
          data_value: [0, 1],
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
          input: function (data) {
            if (data.data == true) {
              var self = App.$get("detail");
              var net =
                Number(self.headersObj.debet.data) -
                Number(self.headersObj.kredit.data);
              //alert(net)
              self.headersObj.ppn.update = (net / 100) * 10;
              self.headersObj.ppn.data = (net / 100) * 10;
              self.headers = App.updateObject(self.headers);
            }
          },
        },
        {
          text: "ppn",
          value: "ppn",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          disabled: false,
          visible: true,
          required: true,
          default: 0,
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
          text: "PPH 23",
          value: "is_pph",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "switch",
          disabled: false,
          visible: false,
          required: true,
          default: 0,
          form: true,
          filter: true,
          groupable: false,
          data_value: [0, 1],
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
          input: function (data) {
            if (data.data == true) {
              var self = App.$get("detail");
              var net =
                Number(self.headersObj.debet.data) -
                Number(self.headersObj.kredit.data);
              //alert(net)
              self.headersObj.pph23.update = (net / 100) * 2;
              self.headersObj.pph23.data = (net / 100) * 2;
              self.headers = App.updateObject(self.headers);
            }
          },
        },
        {
          text: "PPH23",
          value: "pph23",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          disabled: false,
          visible: true,
          required: true,
          default: 0,
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
          text: "Other",
          value: "other",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          disabled: false,
          visible: true,
          required: true,
          default: 0,
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
          text: "Net Amount",
          value: "net_amount",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          disabled: false,
          visible: true,
          required: true,
          default: 0,
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
          text: "Beneficiary Name",
          value: "beneficiary_name",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
          required: true,
          default: 0,
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
          text: "Bank Account",
          value: "bank_account",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
          required: false,
          default: 0,
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
          text: "NPWP",
          value: "npwp",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
          required: false,
          default: 0,
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
          text: "PO Reference",
          value: "po_reference",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
          required: false,
          default: 0,
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
          visible: true,
          required: false,
          default: 0,
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
          text: "Due Date",
          value: "due_date",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "date",
          disabled: false,
          visible: true,
          required: false,
          default: 0,
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
          text: "invoice_ref",
          value: "invoice_ref",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
          required: false,
          default: 0,
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
          text: "NSFP",
          value: "nsfp",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
          required: false,
          default: 0,
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
          text: "Status",
          value: "status",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
          required: false,
          default: 0,
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
          text: "Notes",
          value: "notes",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          disabled: false,
          visible: true,
          required: false,
          default: 0,
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
          text: "Created By",
          value: "created_by",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "int",
          disabled: false,
          visible: true,
          required: true,
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
          type: "datetime",
          disabled: false,
          visible: true,
          required: true,
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
          text: "Modified By",
          value: "modified_by",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "int",
          disabled: false,
          visible: true,
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
          type: "datetime",
          disabled: false,
          visible: true,
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
          text: "Flag",
          value: "flag",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "int",
          disabled: false,
          visible: true,
          required: true,
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
      ],
      //row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
      selected: false,
      //berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
      selectedAll: [],
      isInDom: false,
      isInViewport: false,
    };
  },
  watch: {
    file: function (val) {
      var self = this,
        data;
      if (val) {
        data = val[0].data.filter((val) => val.length > 2).slice(1);
        //console.log(this.data)
        var wb = new ExcelJS.Workbook();
        var reader = new FileReader();
        reader.readAsArrayBuffer(val[0].info);
        var tmp = [];
        reader.onload = () => {
          var buffer = reader.result;
        };
        self.data = data;
      }
    },
    // async file(val){
    // 	var self = this, idx = 0
    // 	for (var [i, v] of Object.entries(val[0].data)){
    // 		if((v[1]||'').toLowerCase() == 'no'){
    // 			idx = i
    // 			break
    // 		}
    // 	}
    // 	//console.log(val)
    // 	var rows = val[0].data.slice(idx+1)
    // 	var tmp = []
    // 	for (var [i, cell] of Object.entries(rows)){
    // 		//console.log(cell)
    // 		tmp.push([].concat(cell[3], cell[4], cell[5], (cell[6]||'').trim()))
    // 	}
    // 	self.data = tmp
    /*var wb = new ExcelJS.Workbook();
                var reader = new FileReader()
				reader.readAsArrayBuffer(val[0].info)
				var tmp = []
				reader.onload = () => {

					var buffer = reader.result;
					wb.xlsx.load(buffer).then(w => {
						var sheet = w.worksheets[0]
						for (const image of w.worksheets[0].getImages()) {
							// fetch the media item with the data (it seems the imageId matches up with m.index?)
							const img = workbook.model.media.find(m => m.index === image.imageId);
							var data = sheet.getRow(image.range.tl.nativeRow+1)._cells.map(val=>val.value ? (val.value.result ? val.value.result : val.value) : val.value)
							data[image.range.tl.nativeCol] = 'data:image/'+img.extension+';base64,'+img.buffer.toString('base64')
							tmp.push(data)
						}
						self.images = tmp
					})
				}*/
    //}
  },
  computed: {
    headersObj: function () {
      var tmp = {},
        self = this;
      Object.keys(self.headers).map((key) => {
        var val = self.headers[key];
        tmp[val.value] = val;
      });
      return tmp;
    },
  },
  methods: {
    saveTransactionlist: function () {
      var self = this;
      setTimeout(async () => {
        var tmp = self.data
          .filter((val) => {
            return val[0] !== true;
          })
          .slice(0, 5);
        var str = [];
        tmp.map((val) => {
          str.push(
            [
              (val[1] || "").toString().trim().length == 0 ? "-" : val[1],
              (val[2] || "").toString().trim().length == 0 ? "-" : val[2],
              (val[3] || "").toString().trim().length == 0 ? "-" : val[3],
              (val[4] || "").toString().trim().length == 0 ? "-" : val[4],
              (val[5] || "").toString().trim().length == 0 ? "-" : val[5],
              (val[6] || "").toString().trim().length == 0 ? "-" : val[6],
              (val[7] || "").toString().trim().length == 0 ? "-" : val[7],
              (val[8] || "").toString().trim().length == 0 ? "-" : val[8],
              (val[9] || "").toString().trim().length == 0 ? "-" : val[9],
              (val[10] || "").toString().trim().length == 0 ? "-" : val[10],
              (val[11] || "").toString().trim().length == 0 ? "-" : val[11],
              (val[12] || "").toString().trim().length == 0 ? "-" : val[12],
              (val[13] || "").toString().trim().length == 0 ? "-" : val[13],
              (val[14] || "").toString().trim().length == 0 ? "-" : val[14],
              (val[15] || "").toString().trim().length == 0 ? "-" : val[15],
              (val[16] || "").toString().trim().length == 0 ? "-" : val[16],
              (val[17] || "").toString().trim().length == 0 ? "-" : val[17],
              (val[18] || "").toString().trim().length == 0 ? "-" : val[18],
              (val[19] || "").toString().trim().length == 0 ? "-" : val[19],
              (val[20] || "").toString().trim().length == 0 ? "-" : val[20],
              (val[21] || "").toString().trim().length == 0 ? "-" : val[21],
              self.$refs.template.items[0].petty_cash_id,
            ].join(";;"),
          );
        });
        str = str.join(";;;");
        var res = await axios.post(App.url + "finance/pettycashdetail", {
          batch: str,
        });
        if (res.data.status == false) {
        } else {
          var tmpVal = self.data.filter((val) => {
            return val[0] !== true;
          });
          for (var i = 0; i < 5; i++) {
            if (tmpVal[i]) tmpVal[i][0] = true;
          }
          if (
            self.data.filter((val) => {
              return val[0] !== true;
            }).length > 0
          ) {
            self.data = App.updateObject(self.data);
            self.saveTransactionlist();
            App.succesMsg();
          } else {
            self.data = App.updateObject(self.data);
          }
        }
      }, 100);
    },
    save: async function (params) {
      var self = this;
      self.overlay = true;
      try {
        const formData = new FormData();
        //formData.append('file', files);
        Object.keys(params).forEach(function (key) {
          var val = params[key];
          formData.append(key, val);
        });
        var res = await axios.post(
          App.url + "finance/pettycashdetail",
          formData,
          {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          },
        );

        self.$refs.template.dialogAdd = false;
        self.$refs.template.getItems();
        self.overlay = false;
        App.successMsg();
        self.getAttachment();
      } catch (err) {
        self.overlay = false;
      }
    },

    update: async function (params) {
      var self = this;
      self.overlay = true;
      try {
        const formData = new FormData();
        //formData.append('file', files);
        Object.keys(params).forEach(function (key) {
          var val = params[key];
          formData.append(key, val);
        });
        var res = await axios.post(
          App.url + "finance/pettycashdetail/update",
          formData,
          {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          },
        );

        self.$refs.template.dialogUpdate = false;
        self.$refs.template.getItems();
        self.overlay = false;
        App.successMsg();
        self.getAttachment();
      } catch (err) {
        self.overlay = false;
      }
    },

    onSelectedRow: function (val) {
      var self = this;
      if (val === undefined) {
        self.selected = false;
      } else {
        self.selected = val;
      }
    },
    onSelectedRowAll: function (val) {
      var self = this;
      self.selectedAll = val;
    },
    onVisible: function (isVisible, e) {
      var self = this;
      self.isInViewport = !!isVisible;
      self.isInDom = !!e.target.isConnected;
    },
    checkDate() {
      var self = this;
      //if(!self.selected){
      self.headersObj.date_voucher.update = App.page().selected.header_date;
      //}
    },
    async getParent() {
      var self = this;
      var params = JSON.parse(
        JSON.stringify(self.$refs.template.defaultItemsOptions),
      );
      params.limit = 9999;
      var data = await axios.get(
        "https://main.buanamultiteknik.com/api/finance/pettycashdetail",
        {
          params: params,
        },
      );
      console.log(data.data.data);
      var sum = 0;
      data.data.data.map((v) => {
        sum = sum - Number(v.net_amount);
      });
      //console.log(sum)
      self.sum = sum;
      //self.sum = Number(data.data.data[0].total_kredit) - Number(data.data.data[0].total_debet)
      //console.log(data.data.data[0], data.data.data[0].kredit, data.data.data[0].debet)
    },
  },
  mounted: function () {
    console.log(this);
  },
};
</script>
