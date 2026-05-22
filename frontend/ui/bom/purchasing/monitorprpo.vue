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
      export-excel
      :show-select="false"
      :table-loading.sync="loading"
      :show-expand="true"
      :single-expand="true"
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
    >
      <template v-slot:title-body v-if="$refs.template">
        <b>Count Rows: </b>{{ $refs.template.itemsTotal }}
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

      <template v-slot:menu-after-edit>
        <!-- <v-btn color="primary" outlined small @click="openReport" :disabled="allowPrint">
					<v-icon small left>mdi-printer</v-icon><template>Preview</template><template v-else>Print</template>
				</v-btn> -->
        <v-btn
          color="primary"
          outlined
          small
          @click="openReport2"
          :disabled="allowPrint"
        >
          <v-icon small left>mdi-printer</v-icon><template>Preview</template>
        </v-btn>
        <!--            <v-btn color="primary" outlined small @click="openReportNoPrice" :disabled="allowPrint">-->
        <!--                <v-icon small left>mdi-printer</v-icon>Print Without price-->
        <!--            </v-btn>-->
        <v-btn color="primary" outlined small @click="toExcel"> Excel </v-btn>
      </template>
      <template v-slot:item.pr_id="props">
        <a
          :href="props.item.signed_pr_url"
          v-if="props.item.signed_pr_url"
          target="_blank"
          >{{ props.item.pr_no }}</a
        ><span v-else>{{ props.item.pr_no }}</span>
      </template>
      <template v-slot:item.total_received_percentage="props">
        {{ props.item.total_received_percentage }}
      </template>
      <template v-slot:item.in_status="props">
        <span
          :class="{
            'd-inline pa-2 green--text': props.item.in_status === 'Complete',
            'd-inline pa-2 red--text': props.item.in_status == 'Inprogress',
          }"
        >
          {{ props.item.in_status }}
        </span>
      </template>
      <template v-slot:item.rfq_no="props">
        <a
          :href="`https://main.buanamultiteknik.com/#/rfq/rfq/${props.item.rfq_no.match(/\d+$/)[0]}`"
          target="_blank"
        >
          {{ props.item.rfq_no }}
        </a>
      </template>

      <!-- 
            <template v-slot:append-menu>
            </template>
			 -->
      <!--<template v-slot:item.po_approved="props">-->
      <!--    {{approvedStatus(props.item.po_approved, props.item.new_po_no)}}-->
      <!--</template>-->
      <!--<template v-slot:item.last_updated="props">-->
      <!--    {{props.item.rfq_hist_date ?? props.item.rfq_modified_date ?? props.item.rfq_created_date}}-->
      <!--</template>-->
      <!--        <template v-slot:item.rfq_id="props">-->
      <!--<div style="white-space: nowrap;">-->
      <!--	<a :href="'#/rfq/rfq/'+props.item.rfq_id">{{props.item.rfq_no}}</a>-->
      <!--</div>-->
      <!--        </template>-->
      <!--    <template v-slot:item.pr_no="props">-->
      <!--     <a :href="props.item.signed_pr_url" v-if="props.item.signed_pr_url" target="_blank">{{props.item.pr_no}}</a><span v-else>{{ props.item.pr_no }}</span>-->
      <!--</template>-->
      <template v-slot:expanded-item="props">
        <td
          ref="expanded"
          :colspan="props.headers.length"
          style="height: auto"
          :key="props.item[itemKey2]"
        >
          <purchase-item-group
            :table-fixed-header="false"
            :item-height-as-min-height="true"
            :table-fill="false"
            is-dashboard
            table-only
            :key="props.item[itemKey2]"
            name=""
            :data="{
              pr_id: props.item[itemKey2],
            }"
          ></purchase-item-group>
        </td>
      </template>
      <!--        <template v-slot:item.grand_total_price="props">-->
      <!--<div style="white-space: nowrap;">-->
      <!--   <b>Grand Total</b><br />-->
      <!--   {{props.item.currency}} {{Number(props.item.grand_total).format(2,3)}}<br /><br />-->
      <!-- <b>Total Item</b> <br />-->
      <!--{{props.item.currency}} {{Number(props.item.grand_total_price).format(2,3)}}-->
      <!--</div>-->
      <!--        </template>-->
    </v-template>
  </v-container>
</template>

<style scoped></style>

<script>
module.exports = {
  name: "",
  creator: "",
  components: {
    /* 'document-form': 'url:ui/ewis/test/document_form.vue' */
    "purchase-item-group": "url:ui/bom/purchasing/monitoringprpoitem.vue",
  },
  props: {
    value: undefined,
    data: {
      type: Object,
    },
    tableOnly: {
      type: Boolean,
      default: true,
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
      type: Boolean,
      default: false,
    },
    // showExpand: {
    //     type: Boolean,
    //     default: false
    // },
    singleExpand: {
      type: Boolean,
      default: true,
    },
    itemsOptions: {
      type: Object,
      default: {
        filter: {},
        filterType: {},
        sortBy: ["created_date"],
      },
    },
  },
  data: function () {
    return {
      name: "monitoring",
      itemKey: "pr_id",
      itemKey2: "pr_id",
      url: "bom/monitoringprpo",
      loading: false,
      headers: [
        /* {
                        "text": "Id",
                        "value": "id",
                        "align": "start",
                        "sortable": true,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "type": "int",
                        "disabled": false,
                        "visible": false,
                        "required": true,
                        "form": false,
                        "filter": true,
                        "groupable": false,
                        "url": "",
                        "searchby": [],
                        "formatter": [],
                        "options": {
                            "filter": {},
                            "filterType": {},
                            "filterCondition": {}
                        },
                        "paging": true,
                        "page": "1",
                        "limit": "10"
                    },  */
        {
          text: "",
          value: "data-table-expand",
        },
        {
          text: "PR No",
          value: "pr_id",
          align: "start",
          sortable: true,
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
          filter: true,
          groupable: false,
          url: App.url + "bom/pr",
          searchby: ["pr_no"],
          formatter: ["id", "pr_no"],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "pr_no",
        },
        {
          text: "PR Status",
          value: "pr_status",
          form: false,
          type: "varchar",
          filter: true,
        },
        {
          text: "PR Subject",
          value: "pr_subject",
          form: false,
          type: "varchar",
          filter: true,
        },
        {
          text: "Created By",
          value: "name",
          form: false,
          type: "varchar",
          filter: true,
        },
        //                 {
        //                     "text": "Department",
        //                     "value": "dept_id",
        //                     "align": "start",
        //                     "sortable": true,
        //                     "filterable": false,
        //                     "divider": false,
        //                     "class": "",
        //                     "width": "auto",
        //                     "type": "list",
        //                     "disabled": false,
        // 		"data_value": [],
        //                     "visible": false,
        //                     "required": true,
        //                     "form": true,
        //                     "filter": true,
        //                     "groupable": false,
        //                     "url": App.url+"bom/department",
        //                     "searchby": ['dept_name'],
        //                     "formatter": ['id', 'dept_name'],
        // 		"pk": "id",
        //                     "options": {
        //                         "filter": {},
        //                         "filterType": {},
        //                         "filterCondition": {}
        //                     },
        //                     "paging": true,
        //                     "page": "1",
        //                     "limit": "10",
        // 		"alias": "dept_name"
        //                 },
        // {
        //     "text": "Price",
        //     "value": "grand_total_price",
        //     "align": "center",
        //     "sortable": false,
        //     "filterable": false,
        //     "divider": false,
        //     "class": "",
        //     "width": "auto",
        //     "type": "float",
        //     "disabled": false,
        //     "visible": true,
        //     "required": false,
        //     "form": false,
        //     "filter": false,
        //     "groupable": false
        // },
        // {
        //     "text": "Grand Total Price",
        //     "value": "grand_total",
        //     "align": "start",
        //     "sortable": true,
        //     "filterable": false,
        //     "divider": false,
        //     "class": "",
        //     "width": "auto",
        //     "type": "float",
        //     "disabled": false,
        //     "visible": false,
        //     "required": false,
        //     "form": false,
        //     "filter": false,
        //     "groupable": false
        // },
        // {
        //     "text": "Currency",
        //     "value": "currency",
        //     "align": "start",
        //     "sortable": true,
        //     "filterable": false,
        //     "divider": false,
        //     "class": "",
        //     "width": "auto",
        //     "type": "list",
        //     "disabled": false,
        //     "visible": true,
        //     "required": false,
        //     "form": true,
        //     "filter": false,
        //     "groupable": false,
        //     "data_value": ["IDR", "CNY", "EUR", "USD"],
        // },
        // {
        //     "text": "PO Created By",
        //     "value": "po_created_by",
        //     "form": false,
        //     "type": "varchar",
        // },
        {
          text: "Created Date",
          value: "created_date",
          form: false,
          type: "datetime",
          filter: true,
        },
        {
          text: "RFQ-NO",
          value: "rfq_no",
          form: false,
          type: "varchar",
          filter: true,
          //   "data_value": [{ text:"New", value:"0"} ,{ text:"Asking Approval 1", value:1} , { text:"Asking Approval 2", value:2} , { text:"Asking Cancellation 1", value:-2} ,{ text:"Asking Cancellation 2", value:-3}  , {text: "Clarification", value: 4}, {text: "Approval 2 Approved", value:3}]
        },
        {
          text: "Ragic Status",
          value: "ragic_status",
          form: false,
          type: "varchar",
          filter: true,
        },
        {
          text: "Order %",
          value: "total_ordered_percentage",
          form: false,
          type: "varchar",
          filter: true,
        },
        {
          text: "Receive %",
          value: "total_received_percentage",
          form: false,
          type: "varchar",
          filter: true,
        },
        {
          text: "Status",
          value: "in_status",
          form: false,
          type: "varchar",
          sortable: true,
          filter: true,
        },
        {
          text: "PO No",
          value: "ipo_no",
          form: false,
          visible: false,
          type: "varchar",
          align: "start",
          sortable: false,
          filterable: false,
          filter: true,
        },

        //     {
        //         "text": "RFQ No",
        //         "value": "rfq_id",
        //         "align": "start",
        //         "sortable": true,
        //         "filterable": false,
        //         "divider": false,
        //         "class": "",
        //         "width": "auto",
        //         "type": "list",
        //         "data_value": [],
        //         "disabled": false,
        //         "visible": true,
        //         "required": false,
        //         "form": true,
        //         "filter": true,
        //         "groupable": false,
        //         "url": App.url + "rfq/rfq",
        //         "searchby": ['rfq_no'],
        //         "formatter": ['id', 'rfq_no'],
        //         "options": {
        //             "filter": {},
        //             "filterType": {},
        //             "filterCondition": {},
        //         },
        //         "paging": true,
        //         "page": "1",
        //         "limit": "10",
        //         "alias": "rfq_no"
        //     }, {
        //         "text": "RFQ Title",
        //         "value": "rfq_title",
        //         "form": false,
        //         "type": "varchar",
        //         "filter": true,
        //         "visible": false
        //     }, {
        //         "text": "RFQ Group",
        //         "value": "rfq_group",
        //         "form": false,
        //         "type": "list",
        //         "filter": true,
        //         "data_value": ["Overseas Beijing", "Overseas Germany", "Local", "Overseas Others"],
        //     },{
        //     "text": "RFQ Allocation",
        //     "value": "allocation",
        //     "align": "start",
        //     "sortable": true,
        //     "filterable": false,
        //     "divider": false,
        //     "class": "",
        //     "width": "auto",
        //     "type": "list",
        //     "disabled": false,
        //     "visible": true,
        //     "required": true,
        //     "form": true,
        //     "filter": true,
        //     "groupable": false,
        //     "data_value": ["Primary", "Secondary", "Utility", "RND", "Tools", "Electric", "Others"],
        // }, {
        //         "text": "RFQ Status",
        //         "value": "rfq_status",
        //         "form": false,
        //         "type": "list",
        //         "filter": true,
        //         "data_value": ["New RFQ", "RFQ Submitted", "Clarification", "Quotation Received", "Final Quotation", "PR Approved",  "Final PO", "Pending", "Cancel"]
        //     }, {
        //         "text": "RFQ Last Updated Date",
        //         "value": "last_updated",
        //         "form": false,
        //         "type": "varchar",
        //         "visible": false
        //     }, {
        //         "text": "Project No",
        //         "value": "mr_task_no",
        //         "form": false,
        //         "type": "varchar",
        //         "filter": true,
        //         "visible": true
        //     }, {
        //         "text": "PR No",
        //         "value": "pr_no",
        //         "form": false,
        //         "type": "varchar",
        //         "filter": true
        //     }, {
        //         "text": "Ragic No",
        //         "value": "ragic_id",
        //         "align": "start",
        //         "sortable": true,
        //         "filterable": false,
        //         "divider": false,
        //         "class": "",
        //         "width": "auto",
        //         "type": "list",
        //         "data_value": [],
        //         "disabled": false,
        //         "visible": true,
        //         "required": false,
        //         "form": true,
        //         "filter": true,
        //         "groupable": false,
        //         "url": App.url + "ragic/ragic",
        //         "searchby": ['order_id'],
        //         "formatter": ['r_id', 'order_id'],
        //         "options": {
        //             "filter": {},
        //             "filterType": {},
        //             "filterCondition": {},
        //         },
        //         "paging": true,
        //         "page": "1",
        //         "limit": "10",
        //         "alias": "order_id"
        //     }, {
        //         "text": "Ragic Status",
        //         "value": "ragic_status",
        //         "form": false,
        //         "type": "varchar",
        //     }, {
        //         "text": "Ragic Created By",
        //         "value": "ragic_created_by",
        //         "form": false,
        //         "type": "varchar",
        //         "visible": false
        //     }, {
        //         "text": "Ragic Created Date",
        //         "value": "ragic_created_date",
        //         "form": false,
        //         "type": "varchar",
        //         "visible": false
        //     }, {
        //         "text": "Ragic Modified Date",
        //         "value": "ragic_modified_date",
        //         "form": false,
        //         "type": "varchar",
        //         "visible": false
        //     }, {
        //         "text": "Ragic Vendor",
        //         "value": "vendor",
        //         "form": false,
        //         "type": "varchar",
        //         "visible": false
        //     }, {
        //         "text": "Ragic Summary",
        //         "value": "ragic_description",
        //         "form": false,
        //         "type": "text",
        //         "visible": false
        //     }, {
        //         "text": "Invoice No",
        //         "value": "invoice_no",
        //         "form": false,
        //         "type": "text",
        //         "visible": false
        //     }, {
        //         "text": "Payment %",
        //         "value": "total_payment_pct",
        //         "form": false,
        //         "type": "float",
        //         "visible": true,
        //         "filter": true,
        //     },
        //     {
        //         "text": "Received %",
        //         "value": "total_received_pct",
        //         "form": false,
        //         "type": "float",
        //         "visible": true,
        //       "filter": true,
        //     }
      ],
      //row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
      selected: false,
      //berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
      selectedAll: [],
      isInDom: false,
      isInViewport: false,
      total_item_price: 0,
    };
  },
  watch: {},
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
    allowPrint: function () {
      var self = this;
      if (self.selected.new_po_no != null) {
        if (self.selected.new_po_no.trim() != "") return true;
      }
      if (!self.selected) return true;
      if (self.selected.approved == -1) return true;
      /* if(self.selected.approved == 2)
					return false */
      return false;
    },
    allowPrint2: function () {
      var self = this;
      if (!self.selected) return true;
      if (self.selected.approved == 2) return false;
      return true;
    },
  },
  methods: {
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
    approvedStatus: function (f, new_po_no) {
      if (new_po_no != null) {
        if (new_po_no.trim() != "") return "Revised Final PO";
      }
      if (f == 0) {
        return "New";
      }
      if (f == 1) {
        return "Asking for Approval 1";
      }
      if (f == 2) {
        return "Asking for Approval 2";
      }
      if (f == 3) {
        return "Approval 2 Approved";
      }
      /* if(f == 4){
                	return 'Approval 2 approved'
                } */
      if (f == -1) {
        return "Rejected";
      }
    },
    openReport: function () {
      var self = this;
      var name = self.selected.po_no.replace(/\//g, "_").replace(/\-/g, "_");
      var randomid = randomId();
      // window.open('https://decafet.com/api/report?type=pdf&file=po&filename=' + name + '&engine=easytemplate&po_id=' + self.selected.id)
      window.open(
        "https://main.buanamultiteknik.com/api/data/reportpo?id=" +
          self.selected.po_id +
          "&filename=" +
          name +
          "&idx=" +
          randomid,
      );
    },
    openReport2: function () {
      var w = screen.width * 0.75;
      var h = screen.height * 0.5;
      var left = screen.width / 2 - w / 2;
      var top = screen.height / 2 - h / 2;

      var randomid = randomId();
      window.open(
        "https://main.buanamultiteknik.com/api/report/pr/index?pr_id=" +
          this.selected.pr_id +
          "&uid=" +
          randomId(),
        "PR " + this.selected.pr_no,
        `scrollbars=yes,resizable=yes,status=no,location=no,toolbar=no,menubar=no,width=${w},height=${h},left=${left},top=${top}`,
      );
    },
    openReportNoPrice: function () {
      var self = this;
      var name = self.selected.po_no.replace(/\//g, "_").replace(/\-/g, "_");

      var randomid = randomId();
      window.open(
        "https://main.buanamultiteknik.com/api/data/reportponoprice?id=" +
          self.selected.po_id +
          "&filename=" +
          name +
          "&idx=" +
          randomid,
      );
    },
    toExcel: function () {
      var self = this;
      self.$refs.template.getItems();
      self.$nextTick(() => {
        tableToExcel(
          self.$refs.template.$el.querySelector(
            ".v-data-table__wrapper > table",
          ),
          "Document Ready for PR",
        );
      });
    },
  },
  mounted: function () {},
};
</script>
