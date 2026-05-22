<template>
  <v-container
    style="
      padding: 1em !important;
      height: 100%;
      display: flex;
      flex-direction: column;
      margin: 0;
      width: 100%;
      max-width: 100%;
      padding: 1em;
    "
  >
    <!--<div class="text-h5 font-weight-bold" style="padding: 0.25em 0.25em 0px 0.75em; margin: 0"></div>-->
    <v-row
      align="center"
      justify="center"
      flex-col
      transition="slide-y-transition"
      style="padding: 0.25em; flex: 0"
    >
      <template v-slot:item.created="props">
        <v-col md="4" lg="3" xl="2">
          <v-card flat class="appbox" disabled>
            <v-list-item-avatar size="80" tile>
              <v-img
                :src="'https://main.buanamultiteknik.com/img/departments.svg'"
              ></v-img>
            </v-list-item-avatar>
            <v-card-item>
              <div>
                <div>Department</div>
                <div class="text-h6 mb-1">
                  Total: {{ props.item.created_by_name }}
                </div>
              </div>
            </v-card-item>
          </v-card>
        </v-col>
        <v-col md="4" lg="3" xl="2">
          <v-card flat class="appbox" disabled>
            <v-list-item-avatar size="80" tile>
              <v-img
                :src="'https://main.buanamultiteknik.com/img/box.svg'"
              ></v-img>
            </v-list-item-avatar>
            <v-card-item>
              <div>
                <div>Items</div>
                <div class="text-h6 mb-1">Total:</div>
              </div>
            </v-card-item>
          </v-card>
        </v-col>
        <v-col md="4" lg="3" xl="2">
          <v-card flat class="appbox" disabled>
            <v-list-item-avatar size="80" tile>
              <v-img
                :src="'https://main.buanamultiteknik.com/img/human.svg'"
              ></v-img>
            </v-list-item-avatar>
            <v-card-item>
              <div>
                <div>Supplier</div>
                <div class="text-h6 mb-1">Total:</div>
              </div>
            </v-card-item>
          </v-card>
        </v-col>
      </template>
    </v-row>
  </v-container>
</template>

<style scoped>
.appbox {
  border-radius: 0.5em;
  border: 1px solid rgba(0, 0, 0, 0.1);
  height: 100px;
  display: flex;
  justify-content: left;
  align-items: center;
  font-weight: 800;
  margin: 0.25em;
  margin-left: 0;
  margin-top: 0;
  cursor: pointer;
  font-size: 1.5em;
  padding: 0 8px;
  background-color: #0f4083;
  color: #ffffff;
}
</style>

<script>
module.exports = {
  name: "",
  props: {
    value: undefined,
    data: {
      type: Object,
    },
    tableOnly: {
      type: Boolean,
      default: false,
    },
  },
  components: {
    "v-print": "url:ui/base/print.vue",
  },
  data: function () {
    return {
      name: "item",
      itemKey: "id",
      url: "bom/item",
      dialogPrint: false,
      images: [],
      headers: [
        {
          text: "Id",
          value: "id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: 100,
          type: "auto",
          disabled: false,
          form: false,
          visible: false,
          filter: false,
          groupable: false,
          cellClass: "",
        },
        {
          text: "Item No",
          value: "item_no",
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
          visible: true,
          required: true,
          form: true,
          filter: true,
        },
        {
          text: "Item Unit",
          value: "unit",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: true,
          required: true,
          form: true,
          filter: true,
          groupable: false,
          data_value: [
            "SHEET",
            "TON",
            "LTR",
            "KG",
            "PACK",
            "BOX",
            "PCS",
            "MTR",
            "DZN",
            "LOT",
            "LOG",
            "SET",
            "UNIT",
            "ROLL",
            "SQM",
          ],
        },
        /*{
					"text": "Price",
					"value": "price_per_item",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "float",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": false,
					"filter": true,
					"groupable": false
				}, {
					"text": "Currency",
					"value": "currency",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": false,
					"filter": true,
					"groupable": false
				}, */ {
          text: "Item Type",
          value: "item_type",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: true,
          required: true,
          form: true,
          filter: true,
          groupable: false,
          data_value: ["Jasa", "Material", "Part", "Tool"],
          paging: true,
          page: "0",
          limit: "10",
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
          visible: true,
          required: false,
          form: true,
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
          visible: true,
          required: false,
          form: true,
          filter: true,
          groupable: false,
        },
        {
          text: "Article No",
          value: "article_no",
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
          form: true,
          filter: true,
          groupable: false,
        },
        {
          text: "Specification",
          value: "specification",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          disabled: false,
          visible: true,
          required: false,
          form: true,
          filter: true,
          groupable: false,
        },
        {
          text: "Equivalent",
          value: "equivalent",
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
          url: App.url + "bom/item",
          searchby: ["full"],
          formatter: ["id", "full"],
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
          alias: "item_no",
        },
        {
          text: "Datasheet",
          value: "datasheet",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          disabled: false,
          visible: true,
          required: true,
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
          page: "0",
          limit: "10",
        },
        {
          text: "is active",
          value: "is_active",
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
          form: true,
          filter: false,
          groupable: false,
          default: 1,
          data_value: [0, 1],
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
          visible: true,
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
              group_name: "adm_admin",
              sub_group_name: "adm_admin",
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
              group_name: "adm_admin",
              sub_group_name: "adm_admin",
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
      ],
    };
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
    closeEdit: function () {
      var self = this;
      self.headersObj.unit.readonly = false;
      self.headersObj.item_type.readonly = false;
    },
    openEdit: function () {
      var self = this;
      self.headersObj.unit.readonly = true;
      self.headersObj.item_type.readonly = true;
    },
    textToBase64Barcode: async function () {
      var self = this;
      var res = await axios.get(App.url + self.url, {
        params: Object.assign(
          {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          self.$refs.template.defaultItemsOptions,
        ),
      });

      var canvas = document.createElement("canvas"),
        images = [],
        tmp = [];
      self.$refs.template.items.map(function (val, i) {
        if (i % 4 == 0 && i != 0) {
          images.push(tmp);
          tmp = [];
        }
        JsBarcode(canvas, val.item_no, { format: "CODE128" });
        tmp.push(canvas.toDataURL("image/png"));
      });
      if (tmp.length != 0) images.push(tmp);
      self.images = images;
      self.$nextTick(() => {
        //tableToExcel(document.getElementById('qr'))
        self.dialogPrint = true;
      });
    },
  },
  mounted: function () {
    loadMultipleLibrary(
      "https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/barcodes/JsBarcode.code128.min.js",
    );
  },
};
</script>
