/*
/home/buanamultiteknik/main.buanamultiteknik.com/ui/bom/purchasing/monitoringitem.vue
*/

<template>
  <v-container
    v-observe-visibility="onVisible"
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
      :item-height-as-min-height="itemHeightAsMinHeight"
      :table-fill="tableFill"
      :table-fixed-header="tableFixedHeader"
      :items-options="itemsOptions"
      :data="data"
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
      <template v-slot:item.item_name="props">
        <a
          :href="props.item.datasheet"
          v-if="props.item.datasheet"
          target="_blank"
          >{{ props.item.item_name }}</a
        ><span v-else>{{ props.item.item_name }}</span>
      </template>
      <template v-slot:item.price_per_item="props">
        {{ parseFloat(props.item.Itemprice).format(2, 3) }}
      </template>
      <!-- <template v-slot:item.price="props">
				<div style="white-space: nowrap;">
				   <b>Per Unit</b><br />
				   {{props.item.currency}} {{parseFloat(props.item.Itemprice).format(2,3)}}<br /><br />
				 <b>Total</b> <br />
				{{props.item.currency}} {{Number(props.item.Totalprice).format(2,3)}}
				</div>
            </template> -->
      <template v-slot:item.itemdetail="props">
        <b>Item No:</b> {{ props.item.item_no }}<br />
        <b>Item Name:</b> {{ props.item.item_name }}<br />
        <!-- <b>Original Manufacture:</b> {{props.item.original_manufacture}}<br/>
                <b>Manufacture PN:</b> {{props.item.manufacture_pn}}<br/>
                <b>Unit:</b> {{ props.item.unit }}<br/>
                <b>Specification:</b> {{ props.item.specification }} -->
      </template>
      <template v-slot:item.receiveddetail="props">
        <!--<b>SPB No:</b> <br/>-->
        <!--<b>Received Date:</b> <br/>-->
        <b>QTY: {{ props.item.complete_qty }}</b
        ><br />
      </template>
      <template v-slot:item.ipo_no="props">
        <ul v-if="props.item.ipo_no">
          <li
            v-for="(po, index) in [
              ...new Set(props.item.ipo_no.split(/\s*,\s*/)),
            ]"
            :key="po"
          >
            <a
              :href="`https://main.buanamultiteknik.com/api/data/reportpo2?id=${props.item.ipo_id.split(/\s*,\s*/)[index]}&filename=${po.replace(/\s*\(.*?\)\s*/g, '').replace(/\//g, '_')}&idx=${randomId()}`"
              target="_blank"
            >
              {{ po }}
            </a>
          </li>
        </ul>
        <span v-else>-</span>
        <!-- Tampilkan "-" jika tidak ada PO -->
      </template>
      <template v-slot:item.paymentpo_status="props">
        <ul v-if="props.item.paymentpo_status">
          <li
            v-for="payment in [
              ...new Set(props.item.paymentpo_status.split(/\s*,\s*/)),
            ]"
            :key="payment"
          >
            {{ payment.split("(")[1].split(")")[0] }}
          </li>
        </ul>
        <span v-else>-</span>
        <!-- Tampilkan "-" jika tidak ada Payment -->
      </template>
    </v-template>
  </v-container>
</template>

<style scoped></style>

<script>
module.exports = {
  name: "itemgroup",
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
      default: true,
    },
    isDashboard: {
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
    itemsOptions: {
      type: Object,
      default: {
        filter: {},
        filterType: {},
        sortBy: ["pp_created"],
        sortDesc: [false, false],
      },
    },
  },
  components: {},
  data: function () {
    return {
      name: "Purchase Order",
      processData: {},
      order_no: 0,
      itemKey: "item_no",
      url: "bom/prpo_item_group",
      headers: [
        {
          text: "Item Detail",
          value: "itemdetail",
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
          form: false,
          filter: true,
          groupable: false,
        },
        {
          text: "Item No",
          value: "item_id",
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
          form: false,
          filter: true,
          groupable: false,
          alias: "item_no",
          url: App.url + "bom/item",
          searchby: ["item_name"],
          formatter: ["id", "item_name"],
          options: {
            filter: {
              is_active: 1,
            },
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Item Name",
          value: "item_name",
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
        },
        {
          text: "Original Manufacture",
          value: "original_manufacture",
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
        },
        {
          text: "Manufacture PN",
          value: "manufacture_pn",
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
        },
        {
          text: "Specification",
          value: "specification",
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
        },
        {
          text: "Unit",
          value: "unit",
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
        },
        {
          text: "PR Quantity",
          value: "qty",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: true,
          groupable: false,
        },
        {
          text: "PO Quantity",
          value: "orderQty",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: true,
          groupable: false,
        },
        {
          text: "Currency",
          value: "currency",
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
        },
        {
          text: "Order",
          value: "order_no",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          disabled: false,
          visible: false,
          required: false,
          form: true,
          filter: false,
          groupable: false,
          precision: 2,
        },
        {
          text: "ReceivedQty",
          value: "ReceivedQty",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: true,
          groupable: false,
        },
        {
          text: "PO",
          value: "ipo_no",
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
          filter: false,
          groupable: false,
        },
        {
          text: "Payment",
          value: "paymentpo_status",
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
          filter: false,
          groupable: false,
        },
        {
          text: "SPB No",
          value: "spb_no",
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
          form: true,
          filter: false,
          groupable: false,
          precision: 2,
        },
      ],
      dialogOrderNo: false,
      dialogItem: false,
      selected: false,
      total_item_price: 0,
      grand_total_price: 0,
      dataid: {},
      poNoSplit: [],
    };
  },
  watch: {
    dialogItem: function (val) {
      var self = this;
      if (!val) {
        self.$refs.template.getItems();
      }
    },
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
    poNumberSplitter: function () {
      this.$refs.template.getItems();
      var self = this;
      self.poNoSplit = [];
      self.$refs.template.items.forEach((val) => {
        const re = /\s*,\s*/;
        if (val.ipo_no) {
          // Pastikan val.ipo_no bukan undefined atau null
          const pisah = val.ipo_no.split(re);
          //console.log(pisah);  // Menampilkan hasil pemisahan
          self.poNoSplit.push(...pisah);
        } else {
          console.log("ipo_no is undefined or empty");
        }
      });
      console.log(self.poNoSplit);
    },

    onSelectedRow: function (val) {
      var self = this;
      if (val === undefined) {
        self.selected = false;
        self.processData = {};
        self.dataid = {};
      } else {
        self.selected = val;
        self.pr_no = val.pr_no;
        self.processData = {
          pr_id: self.data.pr_id,
          item_id: val.item_id,
        };
        self.dataid = {
          pr_id: self.data.pr_id,
          item_id: val.item_id,
        };
      }
    },
    onSelectedRowAll: function (val) {
      var self = this;
      self.selectedAll = val;
    },
    afterGetItems: function (val) {
      var self = this;
      var tmp = 0;
      if (val.data[0]) {
        self.total_item_price = 0;
        self.grand_total_price = 0;
        val.data.map((val) => {
          self.total_item_price = val.total_item_price;
          self.grand_total_price += Number(val.total_price_per_item);
        });
      } else {
        self.total_item_price = 0;
        self.grand_total_price = 0;
      }
    },
    onVisible: function (isVisible) {
      var self = this;
      if (isVisible) {
        // self.itemsOptions.filter = {
        // 	pr_id: self.pr_id,
        // }
        self.$refs.template.defaultItemsOptions.filter = {
          pr_id: self.data.pr_id,
        };
        self.$refs.template.getItems();
      }
    },
  },
  mounted: function () {
    var self = this;

    //const checkDataReady = setInterval(() => {
    //    if (self.$refs.template.items && self.$refs.template.items.length > 0) {
    //        clearInterval(checkDataReady);  // Data sudah siap, hentikan interval
    //        self.poNumberSplitter();  // Panggil poNumberSplitter
    //    }
    //}, 500);  // Memeriksa setiap 500msle.log(this.data)
    if (self.data.pr_id) {
      self.itemsOptions.filter.pr_id = self.data.pr_id;
      self.$refs.template.getItems();
    }
  },
};
</script>
