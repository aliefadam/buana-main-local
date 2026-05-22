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
      @open-add="onOpenAdd"
      @close-add="onCloseAdd"
      :data="data"
      @after-get-items="afterGetItems"
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
          @click="dialogPR = true"
          v-if="!isDashboard"
        >
          <v-icon small left>mdi-plus</v-icon>Add From PR
        </v-btn>
        <!-- <v-btn color="primary" outlined small @click="dialogBOM = true" v-if="!isDashboard">
                    <v-icon small left>mdi-plus</v-icon>Add BOM
                </v-btn> -->
        <v-btn
          color="primary"
          outlined
          :disabled="!selected"
          small
          @click="openRawData"
          v-if="!isDashboard"
        >
          <!-- <v-icon small left>mdi-plus</v-icon> -->Show raw data
        </v-btn>
        <!-- <v-btn color="primary" outlined small @click="onAddItem" v-if="!isDashboard">
                    <v-icon small left>mdi-plus</v-icon>Add Item Manual
                </v-btn> -->
        <v-btn
          color="warning"
          :disabled="!selected"
          outlined
          small
          @click="dialogOrderNo = true"
          v-if="!isDashboard"
        >
          <v-icon small left>mdi-pencil</v-icon>Edit Order No
        </v-btn>
        <v-btn
          color="success"
          outlined
          small
          @click="onSaveUpdateData"
          v-if="!isDashboard"
        >
          <v-icon small left>mdi-content-save-edit</v-icon>Save / Update Data
        </v-btn>
      </template>
      <template v-slot:item.item_name="props">
        <a
          :href="props.item.datasheet"
          v-if="props.item.datasheet"
          target="_blank"
          >{{ props.item.item_name }}</a
        ><span v-else>{{ props.item.item_name }}</span>
      </template>
      <template v-slot:prepend-body>
        <div style="font-weight: bold">
          Total Item Price: {{ parseFloat(grand_total_price).format(2, 3) }}
        </div>
      </template>
      <template v-slot:item.price_per_item="props">
        <div style="white-space: nowrap">
          <b>Per Unit</b><br />
          {{ props.item.currency }}
          <span
            :style="{
              color: getPerUnitLabelColor(props.item.text_label_color),
            }"
          >
            {{ parseFloat(props.item.price_per_item).format(2, 3) }}<br /><br />
          </span>
          <!--{{ parseFloat(props.item.price_per_item).format(2, 3) }}<br /><br />-->
          <b>Total</b> <br />
          {{ props.item.currency }}
          {{ Number(props.item.total_price_per_item).format(2, 3) }}<br />
          <b>PPN ({{ Number(props.item.ppn || 0).format(0, 3) }}%)</b> =
          {{ Number(props.item.ppn_value || 0).format(2, 3) }}<br />
          <b>PPH ({{ Number(props.item.pph || 0).format(0, 3) }}%)</b> =
          {{ Number(props.item.pph_value || 0).format(2, 3) }}<br />
          <br />

          <b>Total After Tax</b> <br />
          {{
            Number(
              Number(props.item.total_price_per_item || 0) +
                Number(props.item.ppn_value || 0) -
                Number(props.item.pph_value || 0),
            ).format(2, 3)
          }}
        </div>
      </template>
      <template v-slot:item.use_ppn="props">
        <v-icon
          v-if="isDashboard"
          :color="getTaxUsage(props.item.item_id, 'ppn') ? 'success' : 'error'"
        >
          {{ getTaxUsage(props.item.item_id, "ppn") ? "mdi-check-bold" : "mdi-close-thick" }}
        </v-icon>
        <v-checkbox
          v-else
          :input-value="getTaxUsage(props.item.item_id, 'ppn')"
          @change="onTaxUsageChange(props.item.item_id, 'ppn', $event)"
          hide-details
          dense
          class="ma-0 pa-0"
        ></v-checkbox>
      </template>
      <template v-slot:item.use_pph="props">
        <v-icon
          v-if="isDashboard"
          :color="getTaxUsage(props.item.item_id, 'pph') ? 'success' : 'error'"
        >
          {{ getTaxUsage(props.item.item_id, "pph") ? "mdi-check-bold" : "mdi-close-thick" }}
        </v-icon>
        <v-checkbox
          v-else
          :input-value="getTaxUsage(props.item.item_id, 'pph')"
          @change="onTaxUsageChange(props.item.item_id, 'pph', $event)"
          hide-details
          dense
          class="ma-0 pa-0"
        ></v-checkbox>
      </template>
    </v-template>
    <v-action-dialog
      v-model="dialogBOM"
      title="Purchase Order Item"
      min-height="75%"
      @save="addBom"
    >
      <v-autoform v-model="formBom"></v-autoform>
    </v-action-dialog>
    <v-action-dialog
      v-model="dialogOrderNo"
      title="Edit Order No"
      min-height="75%"
      @save="saveOrderNo"
    >
      <v-input-number v-model="order_no"></v-input-number>
    </v-action-dialog>
    <v-action-dialog
      v-model="dialogPR"
      title="Import Form PR"
      fullscreen
      @save="addPR"
    >
      <div style="height: 100%; display: flex; flex-direction: column">
        <v-autoform v-model="formPR"></v-autoform>
        <div v-if="subledgerError.length > 0" style="color: #ff5252">
          Error Occured for Subledger {{ subledgerError.join(", ") }}!!!
        </div>
        <div style="flex: 1">
          <v-template
            :single-select="false"
            :headers="subledgerHeaders"
            item-key="id"
            table-only
            url="bom/prsubledger"
            ref="subledgerTable"
            table-fill
            table-fixed-header
            :items-options="subledgerOptions"
            @update:selected-row="onSubledgerSelectionChange"
            @update:selected-row-all="onSubledgerSelectionChange"
            v-if="isSubledger"
          >
            <template v-slot:item.project_name="props">
              <span>{{ getProjectNameText(props.item.id) }}</span>
            </template>
            <template v-slot:item.budget_name="props">
              <span>{{ getBudgetNameText(props.item.id) }}</span>
            </template>
            <template v-slot:item.remaining_budget="props">
              <span
                :style="{
                  color: isSubledgerBudgetWarning(props.item.id)
                    ? '#ff5252'
                    : '',
                }"
              >
                {{ getRemainingBudgetText(props.item.id) }}
              </span>
            </template>
            <template v-slot:item.force_reason="props">
              <v-text-field
                v-if="isSubledgerBudgetWarning(props.item.id)"
                dense
                outlined
                hide-details
                :placeholder="'Reason for force minus budget'"
                :value="getForceReasonText(props.item.id)"
                @input="setForceReasonText(props.item.id, $event)"
              ></v-text-field>
              <span v-else>-</span>
            </template>
          </v-template>
        </div>
      </div>
      <!-- <div>Selected Subledger</div>
			<div v-for="(item, index) in selectedSubledger" :key="index">Part: {{item.item_name}}, Subledger: {{item.subledger}}</div> -->
    </v-action-dialog>
    <purchase-item
      style="display: none"
      @after-save="onAfterSave"
      :sel="sel"
      :data="data"
      ref="purchaseItem"
    ></purchase-item>
    <v-action-dialog
      :actions="false"
      v-model="dialogItem"
      title="Purchase Order Item"
      min-height="75%"
      fullscreen
    >
      <purchase-item
        :table-only="itemDialogReadOnly"
        :key="selected.item_id + '_' + data.purchase_order_id"
        :sel="processData"
        name=""
        :data="dataid"
      ></purchase-item>
    </v-action-dialog>
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
        sortBy: ["order_no", "id"],
        sortDesc: [false, false],
      },
    },
  },
  components: {
    "purchase-item": "url:ui/bom/purchasing/purchaseorderitem.vue",
  },
  data: function () {
    return {
      name: "Purchase Order",
      processData: {},
      selectedSubledger: [],
      taxUsageState: {},
      currentItemIds: [],
      subledgerError: [],
      subledgerProjectName: {},
      subledgerBudgetName: {},
      subledgerRemainingBudget: {},
      subledgerBudgetWarning: {},
      subledgerBudgetLoading: false,
      subledgerForceReason: {},
      order_no: 0,
      isSubledger: false,
      itemKey: "item_no",
      url: "bom/purchase_order_item_group",
      headers: [
        {
          text: "Order",
          value: "order_no",
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
          form: true,
          filter: true,
          groupable: false,
          precision: 2,
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
          visible: true,
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
          visible: true,
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
          visible: true,
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
          visible: true,
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
          visible: true,
          required: false,
          form: false,
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
          visible: true,
          required: false,
          form: false,
          filter: true,
          groupable: false,
        },
        {
          text: "Order Qty",
          value: "order_qty",
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
          text: "Price",
          value: "price_per_item",
          align: "center",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          disabled: false,
          visible: true,
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
          precision: "precision",
          page: "0",
          limit: "10",
        },
        {
          text: "Total Price per Item",
          value: "total_price_per_item",
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
          text: "Complete Qty",
          value: "complete_qty",
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
          text: "Use PPN",
          value: "use_ppn",
          align: "center",
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
          text: "Use PPH",
          value: "use_pph",
          align: "center",
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
      ],
      formBom: [
        {
          text: "BOM",
          value: "bom_id",
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
          url: App.url + "bom/bom_header",
          searchby: ["project_name", "bom_no"],
          formatter: ["id", ["project_name", "bom_no"]],
          options: {
            filter: {
              status: "Final",
            },
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
        },
      ],
      subledgerHeaders: [
        {
          text: "Subledger Name",
          value: "text",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
        },
        {
          text: "Project Name",
          value: "project_name",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
        },
        {
          text: "Budget Name",
          value: "budget_name",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
        },
        {
          text: "Remaining Budget",
          value: "remaining_budget",
          width: "auto",
          type: "float",
          disabled: false,
          visible: true,
        },
        {
          text: "Force Reason Input Minus Budget",
          value: "force_reason",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
        },
      ],
      subledgerOptions: {
        filter: {},
        filterType: {},
        filterCondition: {},
      },
      formPR: [
        {
          text: "PR",
          value: "pr_id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          // 	"data_value": [],
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: false,
          groupable: false,
          readonly: true,
          // "paging": true,
          // "page": "1",
          // "limit": "10",
        },
        {
          text: "PR",
          value: "pr_no",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          data_value: [],
          disabled: false,
          visible: true,
          required: false,
          form: true,
          filter: true,
          groupable: false,
          readonly: true,
        },
        {
          text: "Part",
          value: "pr_part_id",
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
          required: true,
          form: true,
          filter: true,
          data: null,
          groupable: false,
          url: App.url + "bom/prpart",
          searchby: ["itemfull"],
          formatter: ["id", "itemfull"],
          options: {
            filter: {},
            filterType: {
              itemfull: "or",
            },
          },
          paging: true,
          page: "1",
          limit: "10",
          input: function (data) {
            //App.$get('itemgroup').formPR[3].form = true

            App.$get("itemgroup").formPR[3].options.filter = {
              pr_part_id: data.data,
              in_po: 0,
            };

            App.$get("itemgroup").subledgerOptions = {
              filter: {
                pr_part_id: data.data,
                in_po: 0,
              },
              filterType: {},
              filterCondition: {},
            };

            App.$get("itemgroup").subledgerOptions = App.updateObject(
              App.$get("itemgroup").subledgerOptions,
            );

            App.$get("itemgroup").isSubledger = true;
            App.$get("itemgroup").formPR[5].form = true;

            App.$get("itemgroup").formPR[5].data = null;
            App.$get("itemgroup").formPR[5].update = null;
            App.$get("itemgroup").subledgerProjectName = {};
            App.$get("itemgroup").subledgerBudgetName = {};
            App.$get("itemgroup").subledgerRemainingBudget = {};
            App.$get("itemgroup").subledgerBudgetWarning = {};
            App.$get("itemgroup").subledgerBudgetLoading = false;
            App.$get("itemgroup").subledgerForceReason = {};

            App.$get("itemgroup").formPR[3].data = null;
            App.$get("itemgroup").formPR[3].update = null;
            App.$get("itemgroup").formPR = App.updateObject(
              App.$get("itemgroup").formPR,
            );
            App.$get("itemgroup").$nextTick(() => {
              setTimeout(() => {
                if (App.$get("itemgroup").$refs.subledgerTable) {
                  App.$get("itemgroup").$refs.subledgerTable.selectedRowTmp =
                    [];
                  App.$get("itemgroup").$refs.subledgerTable.getItems();
                }
              }, 500);
            });
          },
        },
        {
          text: "Subledger",
          value: "subledger_id",
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
          required: true,
          form: false,
          filter: true,
          data: null,
          groupable: false,
          url: App.url + "bom/prsubledger",
          searchby: ["text"],
          formatter: ["id", "text"],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          input: function (data) {
            // App.$get('itemgroup').formPR[4].form = true
            App.$get("itemgroup").formPR[5].form = true;

            // App.$get('itemgroup').formPR[4].data = null
            // App.$get('itemgroup').formPR[4].update = null
            App.$get("itemgroup").formPR[5].data = null;
            App.$get("itemgroup").formPR[5].update = null;
            App.$get("itemgroup").formPR = App.updateObject(
              App.$get("itemgroup").formPR,
            );
          },
        },
        {
          text: "Order Qty",
          value: "order_qty",
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
          form: false,
          filter: true,
          groupable: false,
        },
        {
          text: "Price per item",
          value: "price_per_item",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          data_value: [],
          disabled: false,
          visible: true,
          required: true,
          form: false,
          filter: true,
          data: null,
          groupable: false,
          input: function () {
            App.$get("itemgroup").triggerBudgetSubledgerApi();
          },
        },
      ],
      formCharge: [
        {
          text: "Charge1",
          value: "charge1",
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
          form: true,
          filter: true,
          groupable: false,
          precision: 2,
        },
        {
          text: "Charge2",
          value: "charge2",
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
          form: true,
          filter: true,
          groupable: false,
          precision: 2,
        },
      ],
      dialogOrderNo: false,
      dialogPR: false,
      dialogBOM: false,
      dialogItem: false,
      allowUpdateInDialog: false,
      dialogCharge: false,
      selected: false,
      total_item_price: 0,
      grand_total_price: 0,
      dataid: {},
    };
  },
  watch: {
    dialogItem: function (val) {
      var self = this;
      if (!val) {
        self.allowUpdateInDialog = false;
        self.$refs.template.getItems();
      }
    },
    dialogPR(visible) {
      var self = this;
      if (!visible) {
        self.formPR[2].data = null;
        self.formPR[2].update = null;
        self.formPR = App.updateObject(self.formPR);
        self.$refs.subledgerTable.selectedRowTmp = [];
        self.subledgerProjectName = {};
        self.subledgerBudgetName = {};
        self.subledgerRemainingBudget = {};
        self.subledgerBudgetWarning = {};
        self.subledgerBudgetLoading = false;
        self.subledgerForceReason = {};
      }
    },
  },
  computed: {
    itemDialogReadOnly: function () {
      return this.isDashboard && !this.allowUpdateInDialog;
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
    prObj: function () {
      var tmp = {},
        self = this;
      Object.keys(self.formPR).map((key) => {
        var val = self.formPR[key];
        tmp[val.value] = val;
      });
      return tmp;
    },
  },
  methods: {
    openRawData: function () {
      this.allowUpdateInDialog = false;
      this.dialogItem = true;
    },
    openUpdateData: function () {
      this.allowUpdateInDialog = true;
      this.dialogItem = true;
    },
    onSaveUpdateData: function () {
      var self = this;
      var rows = (self.currentItemIds || []).map((itemId) => ({
        item_id: itemId,
        use_ppn: !!(
          self.taxUsageState[itemId] && self.taxUsageState[itemId].use_ppn
        ),
        use_pph: !!(
          self.taxUsageState[itemId] && self.taxUsageState[itemId].use_pph
        ),
      }));

      axios
        .post(App.url + self.url + "/save_tax_usage", {
          purchase_order_id: self.sel.purchase_order_id,
          items: rows,
        })
        .then(function (res) {
          if (!res.data.status) throw res.data;
          App.successMsg();
          self.taxUsageState = {};
          self.$refs.template.getItems();
          if (
            App.$get("po") &&
            App.$get("po").$refs &&
            App.$get("po").$refs.template
          ) {
            App.$get("po").$refs.template.getItems();
          }
        })
        .catch(function (e) {
          App.errorMsg(e);
        });
    },
    getTaxUsage: function (itemId, type) {
      var state = this.taxUsageState[itemId];
      if (!state) return false;
      return !!state[type === "ppn" ? "use_ppn" : "use_pph"];
    },
    onTaxUsageChange: function (itemId, type, value) {
      if (!this.taxUsageState[itemId]) {
        this.taxUsageState[itemId] = {
          use_ppn: false,
          use_pph: false,
        };
      }
      if (type === "ppn") {
        this.taxUsageState[itemId].use_ppn = !!value;
      } else {
        this.taxUsageState[itemId].use_pph = !!value;
      }
      this.taxUsageState = App.updateObject(this.taxUsageState);
    },
    getPerUnitLabelColor: function (color) {
      var normalized = String(color || "")
        .trim()
        .toLowerCase();
      if (["red", "green", "yellow"].includes(normalized)) return normalized;
      return "inherit";
    },
    getProjectNameText: function (subledgerId) {
      var self = this;
      if (self.subledgerBudgetLoading) {
        return "Loading...";
      }
      if (self.subledgerProjectName[subledgerId] === undefined) {
        return "-";
      }
      return self.subledgerProjectName[subledgerId];
    },
    getBudgetNameText: function (subledgerId) {
      var self = this;
      if (self.subledgerBudgetLoading) {
        return "Loading...";
      }
      if (self.subledgerBudgetName[subledgerId] === undefined) {
        return "-";
      }
      return self.subledgerBudgetName[subledgerId];
    },
    getRemainingBudgetText: function (subledgerId) {
      var self = this;
      if (self.subledgerBudgetLoading) {
        return "Calculate Remaining Budget...";
      }
      if (self.subledgerRemainingBudget[subledgerId] === undefined) {
        return "-";
      }
      return Number(self.subledgerRemainingBudget[subledgerId]).format(2, 3);
    },
    isSubledgerBudgetWarning: function (subledgerId) {
      var self = this;
      return !!self.subledgerBudgetWarning[subledgerId];
    },
    getForceReasonText: function (subledgerId) {
      var self = this;
      return self.subledgerForceReason[subledgerId] || "";
    },
    setForceReasonText: function (subledgerId, val) {
      var self = this;
      self.$set(self.subledgerForceReason, subledgerId, val);
    },
    triggerBudgetSubledgerApi: async function () {
      var self = this;
      var price = self.formPR[5] ? self.formPR[5].data : null;
      var subledgerIds = [];
      if (self.$refs.subledgerTable && self.$refs.subledgerTable.items) {
        subledgerIds = self.$refs.subledgerTable.items.map((d) => d.id);
      }

      if (!price || subledgerIds.length === 0) {
        self.subledgerBudgetLoading = false;
        self.subledgerProjectName = {};
        self.subledgerBudgetName = {};
        self.subledgerRemainingBudget = {};
        self.subledgerBudgetWarning = {};
        self.subledgerForceReason = {};
        return;
      }

      try {
        self.subledgerBudgetLoading = true;
        var budgetRes = await axios.get(
          "https://sales.buanamultiteknik.com/api/budget/project-budget/subledger",
          {
            params: {
              price: price,
              subledger_id: subledgerIds.join(","),
            },
          },
        );
        var budgetData =
          budgetRes &&
          budgetRes.data &&
          budgetRes.data.data &&
          Array.isArray(budgetRes.data.data)
            ? budgetRes.data.data
            : [];
        var projectNameMap = {};
        var budgetNameMap = {};
        var remainingMap = {};
        var warningMap = {};
        budgetData.map((d) => {
          projectNameMap[d.subledger_id] = d.project_name;
          budgetNameMap[d.subledger_id] = d.budget_name;
          remainingMap[d.subledger_id] = d.remaining;
          warningMap[d.subledger_id] = !!d.is_warning;
        });
        self.subledgerProjectName = projectNameMap;
        self.subledgerBudgetName = budgetNameMap;
        self.subledgerRemainingBudget = remainingMap;
        self.subledgerBudgetWarning = warningMap;
        self.subledgerBudgetLoading = false;
        console.log("Budget API response:", budgetRes.data);
      } catch (e) {
        self.subledgerProjectName = {};
        self.subledgerBudgetName = {};
        self.subledgerRemainingBudget = {};
        self.subledgerBudgetWarning = {};
        self.subledgerForceReason = {};
        self.subledgerBudgetLoading = false;
        console.log("Budget API error:", e);
      }
    },
    onSubledgerSelectionChange: function () {
      var self = this;
      self.triggerBudgetSubledgerApi();
    },
    addPR: async function () {
      var self = this;
      var proms = [],
        errorMsg = [];
      var selectedSubledgerIds = self.$refs.subledgerTable.selectedRowTmp.map(
        (d) => d.id,
      );
      if (!self.formPR[5].data || selectedSubledgerIds.length === 0) {
        App.errorMsg({
          message: "Please fill price and select at least one subledger.",
        });
        return;
      }
      self.$refs.subledgerTable.selectedRowTmp.map((d) => {
        proms.push(
          new Promise(async (resolve, reject) => {
            try {
              var res = await axios.post(App.url + self.url + "/addpr", {
                pr_part_id: self.formPR[2].data,
                subledger_id: d.id,
                purchase_order_id: self.sel.purchase_order_id,
                price_per_item: self.formPR[5].data,
                is_warning: !!self.subledgerBudgetWarning[d.id],
                force_budget_minus_reason:
                  self.subledgerForceReason[d.id] || "",
              });
              if (!res.data.status) throw res.data;
            } catch (e) {
              errorMsg.push(d.text);
            }
            resolve(true);
          }),
        );
      });
      await Promise.all(proms);
      self.$refs.template.getItems();

      if (errorMsg.length == 0) {
        App.successMsg();
        self.formPR[2].data = null;
        self.formPR[2].update = null;
        self.formPR = App.updateObject(self.formPR);
        self.dialogPR = false;
        self.subledgerError = [];
      } else {
        self.subledgerError = errorMsg;
        App.errorMsg();
      }
    },
    onSelectedRow: function (val) {
      var self = this;
      if (val === undefined) {
        self.selected = false;
        self.processData = {};
        self.dataid = {};
      } else {
        self.selected = val;
        self.order_no = val.order_no;
        self.processData = {
          purchase_order_id: self.data.purchase_order_id,
          item_id: val.item_id,
          pr_id: self.data.pr_id,
        };
        self.dataid = {
          purchase_order_id: self.data.purchase_order_id,
          item_id: val.item_id,
          pr_id: self.data.pr_id,
        };
      }
    },
    onSelectedRowAll: function (val) {
      var self = this;
      self.selectedAll = val;
    },
    saveOrderNo: async function () {
      var self = this;
      try {
        var res = await axios.post(App.url + self.url + "/edit_order_no", {
          item_id: self.selected.item_id,
          purchase_order_id: self.sel.purchase_order_id,
          order_no: self.order_no,
        });
        if (!res.data.status) throw res.data;
        App.successMsg();
        self.$refs.template.getItems();
        self.dialogOrderNo = false;
      } catch (e) {
        App.errorMsg(e);
      }
    },
    addBom: async function () {
      var self = this;
      try {
        var res = await axios.post(App.url + self.url + "/addbom", {
          bom_id: self.formBom[0].data,
          purchase_order_id: self.sel.purchase_order_id,
          currency: self.sel.currency,
        });
        if (!res.data.status) throw res.data;
        App.successMsg();
        self.$refs.template.getItems();
        self.dialogItem = false;
      } catch (e) {
        App.errorMsg(e);
      }
    },
    addCharge: async function () {
      var self = this;
      try {
        var res = await axios.post(App.url + "bom/purchaseorder/addcharge", {
          charge1: self.formCharge[0].data,
          charge2: self.formCharge[1].data,
          purchase_order_id: self.sel.purchase_order_id,
        });
        if (!res.data.status) throw res.data;
        App.successMsg();
        self.$refs.template.getItems();
        App.$get("po").$refs.template.getItems();
        self.dialogCharge = false;
      } catch (e) {
        App.errorMsg(e);
      }
    },
    afterGetItems: function (val) {
      var self = this;
      if (val.data[0]) {
        self.total_item_price = 0;
        self.grand_total_price = 0;
        self.currentItemIds = [];
        self.taxUsageState = {};
        val.data.map((val) => {
          self.total_item_price = val.total_item_price;
          self.currentItemIds.push(val.item_id);
          self.taxUsageState[val.item_id] = {
            use_ppn: Number(val.use_ppn) === 1,
            use_pph: Number(val.use_pph) === 1,
          };
          self.grand_total_price +=
            Number(val.total_price_per_item || 0) +
            Number(val.ppn_value || 0) -
            Number(val.pph_value || 0);
        });
        self.taxUsageState = App.updateObject(self.taxUsageState);
      } else {
        self.total_item_price = 0;
        self.grand_total_price = 0;
        self.currentItemIds = [];
        self.taxUsageState = {};
      }
    },
    onOpenAdd: function () {
      var self = this;
      self.$nextTick(() => {
        setTimeout(() => {
          var tmp = {};
          self.$refs.template.formModelAdd.map((val) => {
            tmp[val.value] = val;
          });
          tmp.item_id.form = true;
          tmp.item_id.required = true;
          tmp.order_qty.form = true;
          tmp.order_qty.required = true;
          tmp.currency.form = true;
          tmp.currency.required = true;
          tmp.currency.readonly = true;
          tmp.currency.data = self.sel.currency;
        }, 100);
      });
    },
    onCloseAdd: function () {
      var self = this;
      self.$nextTick(() => {
        setTimeout(() => {
          var tmp = {};
          self.$refs.template.formModelAdd.map((val) => {
            tmp[val.value] = val;
          });
          tmp.item_id.form = false;
          tmp.item_id.required = false;
          tmp.order_qty.form = false;
          tmp.order_qty.required = false;
          tmp.currency.form = false;
          tmp.currency.required = false;
        }, 100);
      });
    },
    onAddItem: function () {
      var self = this;
      self.$refs.purchaseItem.$refs.template.openAdd();
      //self.$refs.purchaseItem.$refs.template.headersObj.price_per_item =
    },
    onAfterSave: function () {
      var self = this;
      setTimeout(() => {
        self.$refs.template.getItems();
        self.$refs.purchaseItem.$refs.template.getItems();
      }, 100);
    },
    onVisible: function (isVisible) {
      var self = this;
      if (isVisible) {
        self.itemsOptions.filter = {
          purchase_order_id: self.data.purchase_order_id,
        };
        self.$refs.template.defaultItemsOptions.filter = {
          purchase_order_id: self.data.purchase_order_id,
        };
        self.$refs.template.getItems();
      }
    },
  },
  mounted: function () {
    var self = this;

    self.formCharge[0].data = self.sel.charge1;
    self.formCharge[1].data = self.sel.charge2;
    self.formPR[0].data = self.sel.pr_id;
    self.formPR[1].data = self.sel.pr_no;
    self.formPR[2].options.filter = {
      pr_id: self.sel.pr_id,
      in_po: 0,
    };
    /* self.formBom[0].options = {
                "filter": {
					"status": 'Final'
                },
                "filterType": {},
                "filterCondition": {}
            } */

    self.$refs.template.getItems();
  },
};
</script>
