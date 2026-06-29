<template>
  <v-container v-observe-visibility="onVisible" class="no-padding-margin" style="
      padding: 0 !important;
      height: 100%;
      display: flex;
      flex-direction: column;
      margin: 0;
      width: 100%;
      max-width: 100%;
    ">
    <v-template :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data"
      :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll"
      delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url"
      :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader"
      @before-save="confirmSaveAdd" @before-edit="confirmSaveEdit" @save-notification="onSaveNotification"
      @edit-notification="onSaveNotification">
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
      <template v-slot:item.created="props">
        <span>By:</span> {{ props.item.created_by_name }}<br />
        <span>Date:</span> {{ props.item.created_date }}<br />
      </template>
      <template v-slot:item.modified="props">
        <span>By:</span> {{ props.item.modified_by_name }}<br />
        <span>Date:</span> {{ props.item.modified_date }}<br />
      </template>
      <template v-slot:prepend-dialog-add>
        <dialog-reset-trigger
          mode="add"
          :handler="initializeManufacturingForDialog"
        ></dialog-reset-trigger>
      </template>
      <template v-slot:append-dialog-add>
        <div
          v-if="partialItemEnabled"
          style="margin:16px 12px 0; padding:20px; border:1px solid #e0e0e0; border-radius:8px; background:#fafafa;"
        >
          <div style="font-size:15px; font-weight:700; color:#1a237e; margin-bottom:10px; padding-bottom:10px; border-bottom:2px solid #e8eaf6; letter-spacing:0.3px;">
            Manufacturing Allocation
          </div>
          <div style="display:flex; flex-wrap:wrap; margin-bottom:16px; background:#fff; border:1px solid #e0e0e0; border-radius:6px; overflow:hidden;">
            <span style="flex:1; padding:10px 16px; font-size:13px; color:#555; line-height:1.5; border-right:1px solid #e0e0e0; min-width:120px;">Total Qty: <b style="display:block; font-size:16px; color:#1a237e; margin-top:2px;">{{ formatAllocationQty(subledgerQtyLimit) }}</b></span>
            <span style="flex:1; padding:10px 16px; font-size:13px; color:#555; line-height:1.5; border-right:1px solid #e0e0e0; min-width:120px;">Allocated: <b style="display:block; font-size:16px; color:#1a237e; margin-top:2px;">{{ formatAllocationQty(getManufacturingAllocatedQty()) }}</b></span>
            <span style="flex:1; padding:10px 16px; font-size:13px; color:#555; line-height:1.5; min-width:120px;">Remaining: <b style="display:block; font-size:16px; color:#1a237e; margin-top:2px;">{{ formatAllocationQty(getManufacturingRemainingQty()) }}</b></span>
          </div>
          <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center; margin-bottom:4px; background:#fff; border:1px solid #e8eaf6; border-radius:6px; padding:14px;" class="manufacturing-allocation-form">
            <v-autocomplete
              v-model="manufacturingAllocationDraft.manufacturing_order_id"
              :items="manufacturingOrderOptions"
              item-text="text"
              item-value="value"
              label="Manufacturing Order"
              filled
              dense
              clearable
              hide-details
              style="flex:1 1 220px; min-width:200px;"
              :loading="manufacturingOrderLoading"
              @focus="loadManufacturingOrders"
              @change="onManufacturingOrderChanged"
            ></v-autocomplete>
            <v-autocomplete
              v-model="manufacturingAllocationDraft.manufacturing_order_detail_id"
              :items="filteredManufacturingItemOptions"
              item-text="displayText"
              item-value="value"
              label="Manufacturing Item"
              filled
              dense
              clearable
              hide-details
              style="flex:1 1 220px; min-width:200px;"
              :loading="manufacturingItemLoading"
              :disabled="!manufacturingAllocationDraft.manufacturing_order_id"
            ></v-autocomplete>
            <v-text-field
              v-model="manufacturingAllocationDraft.qty"
              label="Qty Used"
              filled
              dense
              hide-details
              type="text"
              style="flex:0 1 130px; min-width:110px;"
              @keypress="intKeypress"
            ></v-text-field>
            <v-btn
              color="primary"
              outlined
              small
              style="flex:0 0 auto; min-width:90px;"
              @click="addManufacturingAllocation"
              :disabled="!canAddManufacturingAllocation"
            >
              <v-icon small left>mdi-plus</v-icon>Add
            </v-btn>
          </div>
          <div
            v-if="manufacturingAllocationRows.length > 0"
            style="margin-top:16px;"
          >
            <div
              v-for="(item, index) in manufacturingAllocationRows"
              :key="'manufacturing-allocation-add-' + index"
              style="display:flex; align-items:center; justify-content:space-between; gap:16px; background:#ffffff; border:1px solid #dde1f0; border-top:3px solid #5c6bc0; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.10), 0 0 0 1px rgba(92,107,192,0.05); padding:16px 18px; margin-bottom:12px;"
            >
              <div style="display:flex; flex-direction:column; gap:6px; flex:1; min-width:0;">
                <div style="display:flex; align-items:center; gap:4px; font-size:11px; color:#7986cb; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">
                  <v-icon x-small color="#5c6bc0">mdi-factory</v-icon>
                  {{ item.manufacturing_order_title || "-" }}
                </div>
                <div style="font-size:14px; font-weight:700; color:#263238; word-break:break-word; line-height:1.45;">{{ item.manufacturing_item_name || "-" }}</div>
                <div v-if="item.manufacturing_order_detail_id" style="display:inline-flex; align-items:center; width:fit-content; font-size:11px; color:#3949ab; background:#e8eaf6; border:1px solid #c5cae9; border-radius:20px; padding:2px 10px; font-weight:500;">
                  Detail ID: {{ item.manufacturing_order_detail_id }}
                </div>
              </div>
              <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:4px; flex-shrink:0; min-width:100px; padding-left:16px; border-left:1px solid #eef0f8;">
                <div style="font-size:10px; color:#9e9e9e; text-transform:uppercase; letter-spacing:0.6px; font-weight:600;">Qty Used</div>
                <div style="font-size:22px; font-weight:800; color:#3949ab; line-height:1.1;">{{ formatAllocationQty(item.qty) }}</div>
                <v-btn
                  color="error"
                  text
                  x-small
                  style="margin-top:2px;"
                  @click="removeManufacturingAllocation(index)"
                >
                  <v-icon x-small left>mdi-trash-can-outline</v-icon>Hapus
                </v-btn>
              </div>
            </div>
          </div>
        </div>
      </template>
      <template v-slot:prepend-dialog-edit>
        <dialog-reset-trigger
          mode="edit"
          :handler="initializeManufacturingForDialog"
        ></dialog-reset-trigger>
      </template>
      <template v-slot:append-dialog-edit>
        <div
          v-if="partialItemEnabled"
          style="margin:16px 12px 0; padding:20px; border:1px solid #e0e0e0; border-radius:8px; background:#fafafa;"
        >
          <div style="font-size:15px; font-weight:700; color:#1a237e; margin-bottom:10px; padding-bottom:10px; border-bottom:2px solid #e8eaf6; letter-spacing:0.3px;">
            Manufacturing Allocation
          </div>
          <div style="display:flex; flex-wrap:wrap; margin-bottom:16px; background:#fff; border:1px solid #e0e0e0; border-radius:6px; overflow:hidden;">
            <span style="flex:1; padding:10px 16px; font-size:13px; color:#555; line-height:1.5; border-right:1px solid #e0e0e0; min-width:120px;">Total Qty: <b style="display:block; font-size:16px; color:#1a237e; margin-top:2px;">{{ formatAllocationQty(subledgerQtyLimit) }}</b></span>
            <span style="flex:1; padding:10px 16px; font-size:13px; color:#555; line-height:1.5; border-right:1px solid #e0e0e0; min-width:120px;">Allocated: <b style="display:block; font-size:16px; color:#1a237e; margin-top:2px;">{{ formatAllocationQty(getManufacturingAllocatedQty()) }}</b></span>
            <span style="flex:1; padding:10px 16px; font-size:13px; color:#555; line-height:1.5; min-width:120px;">Remaining: <b style="display:block; font-size:16px; color:#1a237e; margin-top:2px;">{{ formatAllocationQty(getManufacturingRemainingQty()) }}</b></span>
          </div>
          <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center; margin-bottom:4px; background:#fff; border:1px solid #e8eaf6; border-radius:6px; padding:14px;" class="manufacturing-allocation-form">
            <v-autocomplete
              v-model="manufacturingAllocationDraft.manufacturing_order_id"
              :items="manufacturingOrderOptions"
              item-text="text"
              item-value="value"
              label="Manufacturing Order"
              filled
              dense
              clearable
              hide-details
              style="flex:1 1 220px; min-width:200px;"
              :loading="manufacturingOrderLoading"
              @focus="loadManufacturingOrders"
              @change="onManufacturingOrderChanged"
            ></v-autocomplete>
            <v-autocomplete
              v-model="manufacturingAllocationDraft.manufacturing_order_detail_id"
              :items="filteredManufacturingItemOptions"
              item-text="displayText"
              item-value="value"
              label="Manufacturing Item"
              filled
              dense
              clearable
              hide-details
              style="flex:1 1 220px; min-width:200px;"
              :loading="manufacturingItemLoading"
              :disabled="!manufacturingAllocationDraft.manufacturing_order_id"
            ></v-autocomplete>
            <v-text-field
              v-model="manufacturingAllocationDraft.qty"
              label="Qty Used"
              filled
              dense
              hide-details
              type="text"
              style="flex:0 1 130px; min-width:110px;"
              @keypress="intKeypress"
            ></v-text-field>
            <v-btn
              color="primary"
              outlined
              small
              style="flex:0 0 auto; min-width:90px;"
              @click="addManufacturingAllocation"
              :disabled="!canAddManufacturingAllocation"
            >
              <v-icon small left>mdi-plus</v-icon>Add
            </v-btn>
          </div>
          <div
            v-if="manufacturingAllocationRows.length > 0"
            style="margin-top:16px;"
          >
            <div
              v-for="(item, index) in manufacturingAllocationRows"
              :key="'manufacturing-allocation-edit-' + index"
              style="display:flex; align-items:center; justify-content:space-between; gap:16px; background:#ffffff; border:1px solid #dde1f0; border-top:3px solid #5c6bc0; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.10), 0 0 0 1px rgba(92,107,192,0.05); padding:16px 18px; margin-bottom:12px;"
            >
              <div style="display:flex; flex-direction:column; gap:6px; flex:1; min-width:0;">
                <div style="display:flex; align-items:center; gap:4px; font-size:11px; color:#7986cb; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">
                  <v-icon x-small color="#5c6bc0">mdi-factory</v-icon>
                  {{ item.manufacturing_order_title || "-" }}
                </div>
                <div style="font-size:14px; font-weight:700; color:#263238; word-break:break-word; line-height:1.45;">{{ item.manufacturing_item_name || "-" }}</div>
                <div v-if="item.manufacturing_order_detail_id" style="display:inline-flex; align-items:center; width:fit-content; font-size:11px; color:#3949ab; background:#e8eaf6; border:1px solid #c5cae9; border-radius:20px; padding:2px 10px; font-weight:500;">
                  Detail ID: {{ item.manufacturing_order_detail_id }}
                </div>
              </div>
              <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:4px; flex-shrink:0; min-width:100px; padding-left:16px; border-left:1px solid #eef0f8;">
                <div style="font-size:10px; color:#9e9e9e; text-transform:uppercase; letter-spacing:0.6px; font-weight:600;">Qty Used</div>
                <div style="font-size:22px; font-weight:800; color:#3949ab; line-height:1.1;">{{ formatAllocationQty(item.qty) }}</div>
                <v-btn
                  color="error"
                  text
                  x-small
                  style="margin-top:2px;"
                  @click="removeManufacturingAllocation(index)"
                >
                  <v-icon x-small left>mdi-trash-can-outline</v-icon>Hapus
                </v-btn>
              </div>
            </div>
          </div>
        </div>
      </template>
    </v-template>
  </v-container>
</template>

<style scoped>
/* Styling untuk Manufacturing Allocation dipindah ke inline style pada elemen,
   karena konten dirender di dalam slot dialog (dipindah ke <body> oleh Vuetify)
   sehingga scope-id dari <style scoped> tidak lagi cocok dengan elemennya. */
</style>

<script>
module.exports = {
  name: "subledger",
  creator: "",
  components: {
    /* 'document-form': 'url:ui/ewis/test/document_form.vue' */
    "dialog-reset-trigger": {
      props: {
        mode: {
          type: String,
          default: "",
        },
        handler: {
          type: Function,
        },
      },
      mounted: function () {
        if (typeof this.handler === "function") {
          this.handler(this.mode);
        }
      },
      render: function (h) {
        return h("div", {
          style: {
            display: "none",
          },
        });
      },
    },
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
      type: Boolean,
      default: false,
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
        sortBy: ["id"],
        sortDesc: [false],
      },
    },
  },
  data: function () {
    return {
      name: "Purchase Requisition Subledger",
      itemKey: "id",
      url: "bom/prsubledger",
      loading: false,
      headers: [
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
          text: "No",
          value: "no",
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
          form: false,
          filter: false,
          groupable: false,
          url: "",
          searchby: [],
          formatter: [],
        },
        {
          text: "Pr Part Id",
          value: "pr_part_id",
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
          text: "Description",
          value: "description",
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
          text: "Partial Item?",
          value: "is_part",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          clearable: false,
          visible: false,
          required: false,
          form: true,
          filter: false,
          groupable: false,
          default: "No",
          data_value: ["Yes", "No"],
          input: function (data) {
            var self = App.$get("subledger");
            self.applyPartialItemState(data.data);
          },
        },
        {
          text: "Manufacturing Order",
          value: "manufacturing_order_id",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          clearable: true,
          visible: false,
          required: false,
          form: false,
          filter: false,
          groupable: false,
          url: App.url + "bom/manufacturing_order",
          searchby: ["title"],
          formatter: ["id", "title"],
          options: {
            filter: {
              flag: 1,
            },
            filterType: {},
            filterCondition: {
              title: "or",
            },
          },
          paging: true,
          page: "1",
          limit: "10",
          input: function (data) {
            var self = App.$get("subledger");
            self.applyManufacturingOrderFilter(data.data);
          },
        },
        {
          text: "Manufacturing Item",
          value: "manufacturing_order_detail_id",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          clearable: true,
          visible: false,
          required: false,
          form: false,
          filter: false,
          groupable: false,
          url: App.url + "bom/manufacturing_order_detail",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {
              manufacturing_order_id: -1,
            },
            filterType: {},
            filterCondition: {
              name: "or",
            },
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Allocation",
          value: "allocation",
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
          form: true,
          filter: true,
          groupable: false,
          data_value: ["NI", "NIS", "SC"],
          input: function (data) {
            var self = App.$get("subledger");
            if (data.data == "NI") self.headersObj.project_id.form = true;
            else self.headersObj.project_id.form = false;
          },
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
          type: "list",
          disabled: false,
          visible: false,
          required: false,
          form: true,
          filter: false,
          groupable: false,
          default: "IDR",
          data_value: ["IDR", "CNY", "EUR", "USD", "BDT", "SGD"],
          input: function (data) {
            var self = App.$get("subledger");
            // var self = App.page()

            self.headersObj.exchange_rate.required = false;

            // console.log(data.data.trim().toLowerCase());

            self.headersObj.exchange_rate.data = "Loading...";
            self.headers = App.updateObject(self.headers);

            if (data.data.trim().toLowerCase() != "idr") {
              self.headersObj.exchange_rate.required = true;
              self.headersObj.rate_date.required = true;
            } else {
              self.headersObj.exchange_rate.required = false;
              self.headersObj.exchange_rate.data = 0;
              self.headersObj.rate_date.required = false;
            }

            if (data.data.trim().toUpperCase() != "IDR") {
              var ex = data.data.trim().toUpperCase();

              // console.log('ini upper case : '+ex);
              // console.log('ini selft  : '+self.exchangeData[ex]);

              // if(self.exchangeData[ex]) {
              //     self.headersObj.exchange_rate.data = self.exchangeData[ex]
              //     self.headersObj.exchange_rate.update = self.exchangeData[ex]
              //     self.headers = App.updateObject(self.headers)

              //     // console.log('ini kondisi 1 : '+self.exchangeData[ex]);
              // }

              if (self.exchangeData && self.exchangeData[ex]) {
                self.headersObj.exchange_rate.data = self.exchangeData[ex];
                self.headersObj.exchange_rate.update = self.exchangeData[ex];
                self.headers = App.updateObject(self.headers);
              } else {
                // console.log('ini kondisi 2 : '+data.data.trim().toUpperCase());
                axios
                  .get(App.url + "bom/payment/exchange", {
                    params: {
                      currency: data.data.trim().toUpperCase(),
                    },
                  })
                  .then(function (response) {
                    var data = response.data;
                    const exch = data.match(/\d+(.\d+)/)[0];
                    console.log("response : " + exch);

                    // self.headersObj.exchange_rate.default = exch;
                    // self.headersObj.exchange_rate.data = data.match(/\d+(.\d+)/)[0]
                    // self.headers = App.updateObject(self.headers)

                    self.headersObj.exchange_rate.data = exch;
                    self.headers = App.updateObject(self.headers);
                  })
                  .catch(function (error) { });
              }
            } else {
              self.headers = App.updateObject(self.headers);
            }
          },
        },
        {
          text: "Exchange Rate",
          value: "exchange_rate",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          disabled: false,
          visible: false,
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
          precision: "4",
          page: "0",
          limit: "10",
        },
        {
          text: "Rate Date",
          value: "rate_date",
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
          page: "0",
          limit: "10",
        },
        {
          text: "Requirement",
          value: "requirement",
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
          // required: true,
          required: false,
          // form: true,
          form: false,
          filter: true,
          groupable: false,
          data_value: ["Consumable", "Tools", "Project", "Asset"],
        },
        {
          text: "Tahun Budget",
          value: "year_budget",
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
          form: true,
          filter: true,
          groupable: false,
          default: new Date().getFullYear().toString(),
          data_value: [],
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
          form: true,
          filter: true,
          groupable: false,
          data_value: [
            "Project",
            "Operational",
            "Persediaan",
            // "Asset",
          ],
          input: function (val) {
            var self = App.$get("subledger");
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
                self.headersObj.dept_id.form = false;
                self.headersObj.type_operational_id.form = false;
                self.headersObj.sub_type_operational_id.form = false;
                self.headersObj.rnd_id.form = false;
                self.fetchAndSetAssetOrPersediaanRemainingBudget("Persediaan");
              } else if (val.data == "Asset") {
                self.headersObj.project_id.form = false;
                self.headersObj.budget_id.form = false;
                self.headersObj.dept_id.form = false;
                self.headersObj.type_operational_id.form = false;
                self.headersObj.sub_type_operational_id.form = false;
                self.headersObj.rnd_id.form = false;
                self.fetchAndSetAssetOrPersediaanRemainingBudget("Asset");
              } else if (val.data == "R&D") {
                self.headersObj.project_id.form = false;
                self.headersObj.budget_id.form = false;
                self.headersObj.type_operational_id.form = false;
                self.headersObj.sub_type_operational_id.form = false;
                self.headersObj.dept_id.form = false;
                self.headersObj.rnd_id.form = true;
                self.headersObj.text_sisa_budget.form = false;
                self.headersObj.text_sisa_budget.data = null;
                self.headersObj.force_budget_minus_reason.form = false;
                self.headersObj.force_budget_minus_reason.required = false;
                self.headersObj.force_budget_minus_reason.data = null;
              } else {
                self.headersObj.project_id.form = false;
                self.headersObj.budget_id.form = false;
                self.headersObj.type_operational_id.form = true;
                self.headersObj.sub_type_operational_id.form = true;
                self.headersObj.dept_id.form = true;
                self.headersObj.rnd_id.form = false;
                self.headersObj.text_sisa_budget.form = false;
                self.headersObj.text_sisa_budget.data = null;
                self.headersObj.force_budget_minus_reason.form = false;
                self.headersObj.force_budget_minus_reason.required = false;
                self.headersObj.force_budget_minus_reason.data = null;
              }
            }
            self.headers = App.updateObject(self.headers);
          },
        },
        {
          text: "Department",
          value: "dept_id",
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
          form: false,
          filter: true,
          groupable: false,
          clearable: true,
          url: App.url + "bom/department",
          searchby: ["id", "dept_name"],
          formatter: ["id", "dept_name"],
          input: function (val) {
            var self = App.$get("subledger");
            self.headersObj.type_operational_id.data = false;
            self.headersObj.sub_type_operational_id.data = false;
            self.headersObj.type_operational_id.update = false;
            self.headersObj.sub_type_operational_id.update = false;

            delete self.headersObj.sub_type_operational_id.options.filter
              .type_operational_id;

            if (val.data) {
              self.headersObj.type_operational_id.options.filter.department_id =
                val.data;
              self.headersObj.sub_type_operational_id.options.filter.department_id =
                val.data;
            } else {
              delete self.headersObj.type_operational_id.options.filter
                .department_id;
              delete self.headersObj.sub_type_operational_id.options.filter
                .department_id;
            }
            if (self.headersObj.project_type.data === "Operational") {
              var deptId = self.headersObj.dept_id.data;
              var typeId = self.headersObj.type_operational_id.data;
              var subTypeId = self.headersObj.sub_type_operational_id.data;
              if (deptId && typeId && subTypeId) {
                self.fetchAndSetOperationalRemainingBudget(
                  deptId,
                  typeId,
                  subTypeId,
                );
              } else {
                self.headersObj.text_sisa_budget.form = false;
                self.headersObj.text_sisa_budget.data = null;
                self.headersObj.force_budget_minus_reason.form = false;
                self.headersObj.force_budget_minus_reason.required = false;
                self.headersObj.force_budget_minus_reason.data = null;
              }
            }
            self.headers = App.updateObject(self.headers);
          },
          options: {
            filter: {},
            filterType: {},
            filterCondition: {
              id: "or",
              dept_name: "or",
            },
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "dept_name",
        },
        {
          text: "Operational Type",
          value: "type_operational_id",
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
          form: false,
          filter: true,
          groupable: false,
          clearable: true,
          url: App.url + "bom/type",
          searchby: ["id", "name"],
          formatter: ["id", "name"],
          input: function (val) {
            var self = App.$get("subledger");
            if (val.data) {
              self.headersObj.sub_type_operational_id.options.filter.type_operational_id =
                val.data;
              if (self.headersObj.dept_id.data) {
                self.headersObj.sub_type_operational_id.options.filter.department_id =
                  self.headersObj.dept_id.data;
              }
              self.headersObj.sub_type_operational_id.data = false;
              self.headersObj.sub_type_operational_id.update = false;
            } else {
              delete self.headersObj.sub_type_operational_id.options.filter
                .type_operational_id;
              self.headersObj.sub_type_operational_id.data = false;
              self.headersObj.sub_type_operational_id.update = false;
            }
            if (self.headersObj.project_type.data === "Operational") {
              var deptId = self.headersObj.dept_id.data;
              var typeId = self.headersObj.type_operational_id.data;
              var subTypeId = self.headersObj.sub_type_operational_id.data;
              if (deptId && typeId && subTypeId) {
                self.fetchAndSetOperationalRemainingBudget(
                  deptId,
                  typeId,
                  subTypeId,
                );
              } else {
                self.headersObj.text_sisa_budget.form = false;
                self.headersObj.text_sisa_budget.data = null;
                self.headersObj.force_budget_minus_reason.form = false;
                self.headersObj.force_budget_minus_reason.required = false;
                self.headersObj.force_budget_minus_reason.data = null;
              }
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
          text: "Operational Sub Type",
          value: "sub_type_operational_id",
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
          form: false,
          filter: true,
          groupable: false,
          clearable: true,
          url: App.url + "bom/subtype",
          searchby: ["id", "name"],
          formatter: ["id", "name"],
          input: function () {
            var self = App.$get("subledger");
            if (self.headersObj.project_type.data === "Operational") {
              var deptId = self.headersObj.dept_id.data;
              var typeId = self.headersObj.type_operational_id.data;
              var subTypeId = self.headersObj.sub_type_operational_id.data;
              if (deptId && typeId && subTypeId) {
                self.fetchAndSetOperationalRemainingBudget(
                  deptId,
                  typeId,
                  subTypeId,
                );
              } else {
                self.headersObj.text_sisa_budget.form = false;
                self.headersObj.text_sisa_budget.data = null;
                self.headersObj.force_budget_minus_reason.form = false;
                self.headersObj.force_budget_minus_reason.required = false;
                self.headersObj.force_budget_minus_reason.data = null;
              }
              self.headers = App.updateObject(self.headers);
            }
          },
          options: {
            filter: {
              // [NEW FEATURED PENDING]
              // only_has_budget: 1,
            },
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
          visible: true,
          required: true,
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
          text: "Project No",
          value: "project_id",
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
          form: false,
          filter: true,
          groupable: false,
          clearable: true,
          // "default": App.page().selected.project_no,
          url: App.url + "project/project",
          searchby: ["full"],
          pk: "id",
          // "formatter": ["id", "full"],
          formatter: function (val) {
            return {
              value: val.id,
              text: val.full,
              category_item: val.category,
            };
          },
          input: function (data) {
            var self = App.$get("subledger");
            if (data.data) {
              var val = data.data_value.filter(
                (val) => val.value == data.data,
              )[0];
              //console.log(val)

              if (val.category_item == "Aset") {
                self.headersObj.category_item_id.form = true;
                self.headersObj.alokasi_pembelian.form = false;
              } else if (val.value == 8) {
                self.headersObj.alokasi_pembelian.form = true;
                self.headersObj.category_item_id.form = false;
              } else {
                self.headersObj.category_item_id.form = false;
                self.headersObj.alokasi_pembelian.form = false;
              }
              self.headersObj.budget_id.options.filter.project_id = data.data;
              self.headersObj.budget_id.data = null;
              self.headersObj.budget_id.update = null;
              self.headersObj.text_sisa_budget.form = false;
              self.headersObj.text_sisa_budget.data = null;
              self.headersObj.force_budget_minus_reason.form = false;
              self.headersObj.force_budget_minus_reason.required = false;
              self.headersObj.force_budget_minus_reason.data = null;
            }
            self.headers = App.updateObject(self.headers);
          },
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
            category_item: true,
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "project_no",
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
          visible: true,
          required: true,
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
            const self = App.$get("subledger");

            const project_id = self.headersObj.project_id.data;
            const budget_id = self.headersObj.budget_id.data;

            if (project_id && budget_id) {
              self.fetchAndSetRemainingBudget(project_id, budget_id);
            } else {
              self.headersObj.text_sisa_budget.form = false;
              self.headersObj.text_sisa_budget.data = null;
              self.headersObj.force_budget_minus_reason.form = false;
              self.headersObj.force_budget_minus_reason.required = false;
              self.headersObj.force_budget_minus_reason.data = null;
              self.headers = App.updateObject(self.headers);
            }
          },
        },

        {
          text: "",
          value: "text_sisa_budget",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: true, // tidak bisa diklik / diedit
          visible: true,
          required: false,
          form: false,
          filter: false,
          groupable: false,
          readonly: true,
        },
        {
          text: "Force input reasons?",
          value: "force_budget_minus_reason",
          align: "start",
          sortable: false,
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
          text: "Project Name",
          value: "project_name",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: true,
          groupable: false,
          url: "",
          searchby: "",
          formatter: "",
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
          text: "Category Item",
          value: "category_item_id",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: false,
          required: true,
          form: false,
          filter: true,
          groupable: false,
          clearable: true,
          // "default": App.page().selected.categoryitem_name,
          url: App.url + "bom/categoryitem",
          searchby: ["full"],
          formatter: ["id", "full"],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "categoryitem_name",
        },
        {
          text: "Alokasi Pembelian",
          value: "alokasi_pembelian",
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
          clearable: true,
          form: false,
          filter: true,
          groupable: false,
          url: App.url + "bom/alokasipembelian",
          searchby: ["keterangan"],
          formatter: ["alokasi_pembelian", "keterangan"],
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
          text: "Qty",
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
          input: function (data) {
            var self = App.$get("subledger");
            self.subledgerQtyLimit = self.toNumber(data.data);
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Unit Price",
          value: "unit_price",
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
      ],
      //row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
      selected: false,
      //berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
      selectedAll: [],
      isInDom: false,
      isInViewport: false,
      remainingBudgetValue: null,
      partialItemEnabled: false,
      subledgerQtyLimit: 0,
      manufacturingOrderLoading: false,
      manufacturingItemLoading: false,
      manufacturingOrderOptions: [],
      manufacturingItemOptions: [],
      manufacturingAllocationDraft: {
        manufacturing_order_id: null,
        manufacturing_order_detail_id: null,
        qty: null,
      },
      manufacturingAllocationRows: [],
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
    formFileObj: function () {
      var tmp = {},
        self = this;
      Object.keys(self.formFile).map((key) => {
        var val = self.formFile[key];
        tmp[val.value] = val;
      });
      return tmp;
    },
    canAddManufacturingAllocation: function () {
      return !!(
        this.manufacturingAllocationDraft.manufacturing_order_id &&
        this.manufacturingAllocationDraft.manufacturing_order_detail_id &&
        Number(this.manufacturingAllocationDraft.qty || 0) > 0 &&
        this.getManufacturingRemainingQty() > 0
      );
    },
    filteredManufacturingItemOptions: function () {
      var self = this;
      var selectedOrderId = self.manufacturingAllocationDraft.manufacturing_order_id;
      return self.manufacturingItemOptions.filter(function (item) {
        return !self.manufacturingAllocationRows.some(function (row) {
          return (
            String(row.manufacturing_order_id) === String(selectedOrderId) &&
            String(row.manufacturing_order_detail_id) === String(item.value)
          );
        });
      });
    },
  },
  methods: {
    setYearBudgetOptions: function () {
      var self = this;
      var currentYear = new Date().getFullYear();
      self.headersObj.year_budget.data_value = [
        currentYear.toString(),
        (currentYear - 1).toString(),
        (currentYear - 2).toString(),
        (currentYear - 3).toString(),
      ];
      if (!self.headersObj.year_budget.data) {
        self.headersObj.year_budget.data = currentYear.toString();
      }
      self.headers = App.updateObject(self.headers);
    },
    intKeypress: function (e) {
      if (
        !/[0-9]/.test(e.key) &&
        !["Backspace", "Delete", "ArrowLeft", "ArrowRight", "Tab"].includes(
          e.key,
        )
      ) {
        e.preventDefault();
      }
    },
    formatAllocationQty: function (value) {
      return this.toNumber(value).toLocaleString("id-ID", {
        maximumFractionDigits: 0,
      });
    },
    resetManufacturingDraft: function () {
      this.manufacturingAllocationDraft = {
        manufacturing_order_id: null,
        manufacturing_order_detail_id: null,
        qty: null,
      };
      this.manufacturingItemOptions = [];
    },
    resetManufacturingAllocationState: function () {
      this.partialItemEnabled = false;
      this.subledgerQtyLimit = 0;
      this.manufacturingOrderOptions = [];
      this.manufacturingItemOptions = [];
      this.manufacturingAllocationRows = [];
      this.resetManufacturingDraft();
    },
    initializeManufacturingForDialog: function (mode) {
      this.resetManufacturingAllocationState();
      if (mode === "edit" && this.selected) {
        this.subledgerQtyLimit = this.toNumber(this.selected.qty);
        if (this.isPartialItemValue(this.selected.is_part)) {
          this.partialItemEnabled = true;
          this.loadManufacturingOrders();
        }
      }
    },
    loadManufacturingOrders: async function () {
      var self = this;
      if (self.manufacturingOrderOptions.length > 0) return;
      self.manufacturingOrderLoading = true;
      try {
        var response = await axios.get(App.url + "bom/manufacturing_order", {
          params: {
            limit: -1,
            filter: {
              flag: 1,
            },
            filterType: {},
            filterCondition: {},
          },
        });
        var rows =
          response &&
          response.data &&
          Array.isArray(response.data.data)
            ? response.data.data
            : [];
        self.manufacturingOrderOptions = rows.map(function (item) {
          return {
            text: item.title || "-",
            value: item.id,
            raw: item,
          };
        });
      } catch (e) {
        self.manufacturingOrderOptions = [];
      } finally {
        self.manufacturingOrderLoading = false;
      }
    },
    loadManufacturingItems: async function (manufacturingOrderId) {
      var self = this;
      if (!manufacturingOrderId) {
        self.manufacturingItemOptions = [];
        return;
      }
      self.manufacturingItemLoading = true;
      try {
        var response = await axios.get(
          App.url + "bom/manufacturing_order_detail",
          {
            params: {
              limit: -1,
              filter: {
                manufacturing_order_id: manufacturingOrderId,
              },
              filterType: {},
              filterCondition: {},
            },
          },
        );
        var rows =
          response &&
          response.data &&
          Array.isArray(response.data.data)
            ? response.data.data
            : [];
        self.manufacturingItemOptions = rows.map(function (item) {
          var itemIdentity = [];
          if (item.id !== undefined && item.id !== null) {
            itemIdentity.push("Detail ID: " + item.id);
          }
          if (item.manufacturing_order_id !== undefined && item.manufacturing_order_id !== null) {
            itemIdentity.push("MO ID: " + item.manufacturing_order_id);
          }
          return {
            text: item.name || "-",
            displayText:
              (item.name || "-") +
              (itemIdentity.length ? " (" + itemIdentity.join(" | ") + ")" : ""),
            value: item.id,
            raw: item,
          };
        });
      } catch (e) {
        self.manufacturingItemOptions = [];
      } finally {
        self.manufacturingItemLoading = false;
      }
    },
    onManufacturingOrderChanged: async function (value) {
      this.manufacturingAllocationDraft.manufacturing_order_detail_id = null;
      await this.loadManufacturingItems(value);
    },
    getManufacturingAllocatedQty: function () {
      return this.manufacturingAllocationRows.reduce(function (total, item) {
        return total + Number(item.qty || 0);
      }, 0);
    },
    getManufacturingRemainingQty: function () {
      return Math.max(
        0,
        Number(this.subledgerQtyLimit || 0) - this.getManufacturingAllocatedQty(),
      );
    },
    addManufacturingAllocation: function () {
      var self = this;
      var draft = self.manufacturingAllocationDraft;
      var qty = Number(draft.qty || 0);

      if (
        !draft.manufacturing_order_id ||
        !draft.manufacturing_order_detail_id ||
        qty <= 0
      ) {
        App.errorMsg("Manufacturing item and qty cannot be empty!");
        return;
      }

      if (qty > self.getManufacturingRemainingQty()) {
        App.errorMsg("Qty used exceeds remaining qty!");
        return;
      }

      var selectedOrder = self.manufacturingOrderOptions.find(function (item) {
        return String(item.value) === String(draft.manufacturing_order_id);
      });
      var selectedItem = self.manufacturingItemOptions.find(function (item) {
        return String(item.value) === String(draft.manufacturing_order_detail_id);
      });

      if (!selectedOrder || !selectedItem) {
        App.errorMsg("Manufacturing order or item not found!");
        return;
      }

      self.manufacturingAllocationRows.push({
        manufacturing_order_id: draft.manufacturing_order_id,
        manufacturing_order_title: selectedOrder.text,
        manufacturing_order_detail_id: draft.manufacturing_order_detail_id,
        manufacturing_item_name: selectedItem.text,
        manufacturing_item_label: selectedItem.displayText || "-",
        qty: qty,
      });

      self.resetManufacturingDraft();
    },
    removeManufacturingAllocation: function (index) {
      var removed = this.manufacturingAllocationRows.splice(index, 1);
      if (
        removed.length > 0 &&
        this.manufacturingAllocationDraft.manufacturing_order_id &&
        String(removed[0].manufacturing_order_id) ===
          String(this.manufacturingAllocationDraft.manufacturing_order_id)
      ) {
        this.loadManufacturingItems(
          this.manufacturingAllocationDraft.manufacturing_order_id,
        );
      }
    },
    prepareManufacturingAllocationPayload: function (opt) {
      if (!this.partialItemEnabled) {
        opt.params.manufacturing_allocations = [];
        return;
      }

      if (Number(this.subledgerQtyLimit || 0) <= 0) {
        throw new Error("Qty must be filled before adding manufacturing allocation!");
      }

      if (this.manufacturingAllocationRows.length === 0) {
        throw new Error("Manufacturing allocation cannot be empty!");
      }

      if (this.getManufacturingAllocatedQty() > Number(this.subledgerQtyLimit || 0)) {
        throw new Error("Manufacturing allocation exceeds qty!");
      }

      opt.params.manufacturing_allocations = this.manufacturingAllocationRows.map(
        function (item) {
          return {
            manufacturing_order_id: item.manufacturing_order_id,
            manufacturing_order_detail_id: item.manufacturing_order_detail_id,
            qty: Number(item.qty || 0),
          };
        },
      );
    },
    setRemainingBudgetMessage: function (message, isError) {
      var self = this;
      if (isError) {
        self.remainingBudgetValue = null;
      }
      self.headersObj.text_sisa_budget.form = true;
      self.headersObj.text_sisa_budget.data = message;
      if (isError) {
        self.headersObj.force_budget_minus_reason.form = false;
        self.headersObj.force_budget_minus_reason.required = false;
        self.headersObj.force_budget_minus_reason.data = null;
      }
      self.headers = App.updateObject(self.headers);
    },
    applyRemainingBudgetState: function (remaining, options) {
      var self = this;
      var isOperational =
        self.headersObj.project_type &&
        self.headersObj.project_type.data === "Operational";
      var skipMinusValidation = !!options?.skipMinusValidation;
      var formattedRemaining = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(remaining);

      self.remainingBudgetValue = Number(remaining);
      self.headersObj.text_sisa_budget.form = true;
      self.headersObj.text_sisa_budget.data =
        "Remaining Budget : " + formattedRemaining;
      if (skipMinusValidation) {
        self.headersObj.force_budget_minus_reason.form = false;
        self.headersObj.force_budget_minus_reason.required = false;
        self.headersObj.force_budget_minus_reason.data = null;
        self.headers = App.updateObject(self.headers);
        return;
      }
      self.headersObj.force_budget_minus_reason.form =
        remaining < 0 && !isOperational;
      self.headersObj.force_budget_minus_reason.required =
        remaining < 0 && !isOperational;
      if (!(remaining < 0) || isOperational) {
        self.headersObj.force_budget_minus_reason.data = null;
      }
      self.headers = App.updateObject(self.headers);
    },
    toNumber: function (value) {
      if (value === null || value === undefined || value === "") return 0;
      if (typeof value === "number") return value;
      var normalized = String(value).replace(/,/g, "");
      var parsed = parseFloat(normalized);
      return isNaN(parsed) ? 0 : parsed;
    },
    isPartialItemValue: function (value) {
      return (
        value === "Yes" ||
        value === 1 ||
        value === "1" ||
        value === true
      );
    },
    applyManufacturingOrderFilter: function (manufacturingOrderId) {
      var self = this;
      if (!self.headersObj.manufacturing_order_detail_id) return;

      self.headersObj.manufacturing_order_detail_id.data = null;
      self.headersObj.manufacturing_order_detail_id.update = null;
      self.headersObj.manufacturing_order_detail_id.data_selected = null;
      self.headersObj.manufacturing_order_detail_id.data_value = [];

      if (manufacturingOrderId) {
        self.headersObj.manufacturing_order_detail_id.options.filter.manufacturing_order_id =
          manufacturingOrderId;
      } else {
        self.headersObj.manufacturing_order_detail_id.options.filter.manufacturing_order_id =
          -1;
      }

      self.headers = App.updateObject(self.headers);
    },
    applyPartialItemState: function (value) {
      var self = this;
      var isPartialItem = self.isPartialItemValue(value);

      self.partialItemEnabled = isPartialItem;
      self.headersObj.manufacturing_order_id.form = false;
      self.headersObj.manufacturing_order_id.required = false;
      self.headersObj.manufacturing_order_detail_id.form = false;
      self.headersObj.manufacturing_order_detail_id.required = false;

      if (!isPartialItem) {
        self.headersObj.manufacturing_order_id.data = null;
        self.headersObj.manufacturing_order_id.update = null;
        self.headersObj.manufacturing_order_id.data_selected = null;
        self.headersObj.manufacturing_order_id.data_value = [];
        self.manufacturingAllocationRows = [];
        self.applyManufacturingOrderFilter(null);
        self.resetManufacturingDraft();
        return;
      }

      self.manufacturingAllocationRows = [];
      self.resetManufacturingDraft();
      self.loadManufacturingOrders();
      self.applyManufacturingOrderFilter(
        self.headersObj.manufacturing_order_id.data,
      );
      self.headers = App.updateObject(self.headers);
    },
    isOperationalBudgetInsufficient: function () {
      var self = this;
      if (
        !self.headersObj.project_type ||
        self.headersObj.project_type.data !== "Operational"
      ) {
        return false;
      }
      if (
        self.remainingBudgetValue === null ||
        self.remainingBudgetValue === undefined
      ) {
        return false;
      }
      var qty = self.toNumber(self.headersObj.qty?.data);
      var unitPrice = self.toNumber(self.headersObj.unit_price?.data);
      var currency = String(self.headersObj.currency?.data || "IDR")
        .trim()
        .toUpperCase();
      var exchangeRate = self.toNumber(self.headersObj.exchange_rate?.data);
      var effectiveRate = currency === "IDR" ? 1 : exchangeRate;
      var totalNeed = qty * unitPrice * effectiveRate;
      return self.remainingBudgetValue < totalNeed;
    },
    fetchAndSetRemainingBudget: function (projectId, budgetId) {
      var self = this;
      self.remainingBudgetValue = null;
      self.headersObj.text_sisa_budget.form = true;
      self.headersObj.text_sisa_budget.data = "Calculate Remaining Budget...";
      self.headersObj.force_budget_minus_reason.form = false;
      self.headersObj.force_budget_minus_reason.required = false;
      self.headers = App.updateObject(self.headers);

      axios
        .get(
          `https://panel.buanamultiteknik.com/api/budget/project-budget/index?project_id=${projectId}&budget_id=${budgetId}`,
        )
        .then(function (response) {
          try {
            var remaining = response?.data?.budget?.remaining;
            if (remaining === undefined || remaining === null) {
              throw new Error("Data kosong");
            }

            self.applyRemainingBudgetState(remaining);
          } catch (error) {
            self.setRemainingBudgetMessage(
              "Error : Failed to load remaining budget",
              true,
            );
          }
        })
        .catch(function () {
          self.setRemainingBudgetMessage(
            "Error : Failed to load remaining budget",
            true,
          );
        });
    },
    fetchAndSetOperationalRemainingBudget: function (
      deptId,
      typeOperationalId,
      subTypeOperationalId,
    ) {
      var self = this;
      self.remainingBudgetValue = null;
      self.headersObj.text_sisa_budget.form = true;
      self.headersObj.text_sisa_budget.data = "Calculate Remaining Budget...";
      self.headersObj.force_budget_minus_reason.form = false;
      self.headersObj.force_budget_minus_reason.required = false;
      self.headers = App.updateObject(self.headers);

      axios
        .get(
          `https://panel.buanamultiteknik.com/api/budget/operational-budget/index?dept_id=${deptId}&type_operational_id=${typeOperationalId}&sub_type_operational_id=${subTypeOperationalId}`,
        )
        .then(function (response) {
          try {
            var remaining = response?.data?.budget?.remaining;
            if (remaining === undefined || remaining === null) {
              throw new Error("Data kosong");
            }
            self.applyRemainingBudgetState(remaining);
          } catch (error) {
            self.setRemainingBudgetMessage(
              "Error : Failed to load remaining budget",
              true,
            );
          }
        })
        .catch(function () {
          self.setRemainingBudgetMessage(
            "Error : Failed to load remaining budget",
            true,
          );
        });
    },
    fetchAndSetAssetOrPersediaanRemainingBudget: function (projectType) {
      var self = this;
      self.remainingBudgetValue = null;
      self.headersObj.text_sisa_budget.form = true;
      self.headersObj.text_sisa_budget.data = "Calculate Remaining Budget...";
      self.headersObj.force_budget_minus_reason.form = false;
      self.headersObj.force_budget_minus_reason.required = false;
      self.headersObj.force_budget_minus_reason.data = null;
      self.headers = App.updateObject(self.headers);

      var endpoint =
        projectType === "Asset"
          ? "https://panel.buanamultiteknik.com/api/budget/asset/summary"
          : "https://panel.buanamultiteknik.com/api/budget/persediaan/summary";

      axios
        .get(endpoint)
        .then(function (response) {
          try {
            var remaining =
              response?.data?.budget?.remaining ??
              response?.data?.remaining ??
              response?.data?.data?.remaining ??
              response?.data?.summary?.remaining;

            if (remaining === undefined || remaining === null) {
              throw new Error("Data kosong");
            }

            self.applyRemainingBudgetState(remaining, {
              skipMinusValidation: true,
            });
          } catch (error) {
            self.setRemainingBudgetMessage(
              "Error : Failed to load remaining budget",
              true,
            );
          }
        })
        .catch(function () {
          self.setRemainingBudgetMessage(
            "Error : Failed to load remaining budget",
            true,
          );
        });
    },
    confirmSaveAdd: async function (opt) {
      // Requirement input is intentionally hidden in form; send fallback value.
      this.headersObj.requirement.data = "-";
      this.prepareManufacturingAllocationPayload(opt);
      if (this.isOperationalBudgetInsufficient()) {
        App.errorMsg("Sisa Budget Tidak Cukup");
        const err = new Error("INSUFFICIENT_OPERATIONAL_BUDGET_ADD");
        err.isBudgetInsufficient = true;
        return Promise.reject(err);
      }
      if (!this.headersObj.force_budget_minus_reason.form) return;
      const c = confirm(
        "Sisa budget project minus. Apakah Anda yakin tetap ingin menyimpan?",
      );
      if (!c) {
        const err = new Error("USER_CANCEL_MINUS_BUDGET_SAVE");
        err.isUserCancel = true;
        return Promise.reject(err);
      }
    },
    confirmSaveEdit: async function (opt) {
      // Requirement input is intentionally hidden in form; send fallback value.
      this.headersObj.requirement.data = "-";
      this.prepareManufacturingAllocationPayload(opt);
      if (this.isOperationalBudgetInsufficient()) {
        App.errorMsg("Sisa Budget Tidak Cukup");
        const err = new Error("INSUFFICIENT_OPERATIONAL_BUDGET_EDIT");
        err.isBudgetInsufficient = true;
        return Promise.reject(err);
      }
      if (!this.headersObj.force_budget_minus_reason.form) return;
      const c = confirm(
        "Sisa budget project minus. Apakah Anda yakin tetap ingin menyimpan?",
      );
      if (!c) {
        const err = new Error("USER_CANCEL_MINUS_BUDGET_EDIT");
        err.isUserCancel = true;
        return Promise.reject(err);
      }
    },
    onSaveNotification: function (err) {
      if (err) {
        // if (
        //   err.isUserCancel ||
        //   err.message === "USER_CANCEL_ADD_SAVE" ||
        //   err.message === "USER_CANCEL_MINUS_BUDGET_SAVE"
        // ) {
        // }
        // App.errorMsg(err);
        return;
      }
      this.resetManufacturingAllocationState();
      App.successMsg();
    },
    onSelectedRow: function (val) {
      var self = this;
      if (val === undefined) {
        self.selected = false;
        self.resetManufacturingAllocationState();
      } else {
        self.selected = val;

        // --- Reset semua field kondisional dulu ---
        self.headersObj.project_id.form = false;
        self.headersObj.budget_id.form = false;
        self.headersObj.budget_id.options.filter.project_id =
          val.project_id || -1;
        self.headersObj.type_operational_id.form = false;
        self.headersObj.sub_type_operational_id.form = false;
        self.headersObj.dept_id.form = false;
        self.headersObj.category_item_id.form = false;
        self.headersObj.alokasi_pembelian.form = false;
        self.headersObj.text_sisa_budget.form = false;
        self.headersObj.text_sisa_budget.data = null;
        self.headersObj.force_budget_minus_reason.form = false;
        self.headersObj.force_budget_minus_reason.required = false;
        self.headersObj.force_budget_minus_reason.data = null;
        self.headersObj.manufacturing_order_id.form = false;
        self.headersObj.manufacturing_order_id.required = false;
        self.headersObj.manufacturing_order_id.data = null;
        self.headersObj.manufacturing_order_id.update = null;
        self.headersObj.manufacturing_order_id.data_selected = null;
        self.headersObj.manufacturing_order_id.data_value = [];
        self.headersObj.manufacturing_order_detail_id.form = false;
        self.headersObj.manufacturing_order_detail_id.required = false;
        self.applyManufacturingOrderFilter(null);
        self.remainingBudgetValue = null;
        self.subledgerQtyLimit = self.toNumber(val.qty);

        // Reset currency fields
        self.headersObj.exchange_rate.required = false;
        self.headersObj.rate_date.required = false;
        if (val.currency && val.currency.trim().toLowerCase() !== "idr") {
          self.headersObj.exchange_rate.required = true;
          self.headersObj.rate_date.required = true;
        }

        self.applyPartialItemState(val.is_part);
        if (self.isPartialItemValue(val.is_part)) {
          self.headersObj.manufacturing_order_id.data =
            val.manufacturing_order_id || null;
          self.headersObj.manufacturing_order_id.update =
            val.manufacturing_order_id || null;
          self.applyManufacturingOrderFilter(val.manufacturing_order_id || null);
          self.headersObj.manufacturing_order_detail_id.data =
            val.manufacturing_order_detail_id || null;
          self.headersObj.manufacturing_order_detail_id.update =
            val.manufacturing_order_detail_id || null;
        }

        // --- Logika project_type ---
        if (val.project_type == "Project") {
          self.headersObj.project_id.form = true;
          self.headersObj.budget_id.form = true;

          // Cek category dari project yang terpilih
          if (val.project_id) {
            var projectData = self.headersObj.project_id.data_value
              ? self.headersObj.project_id.data_value.filter(
                (p) => p.value == val.project_id,
              )[0]
              : null;

            if (projectData) {
              if (projectData.category_item == "Aset") {
                self.headersObj.category_item_id.form = true;
              } else if (projectData.value == 8) {
                self.headersObj.alokasi_pembelian.form = true;
              }
            }
          }

          // Tampilkan Remaining Budget jika project & budget sudah ada
          if (val.project_id && val.budget_id) {
            self.fetchAndSetRemainingBudget(val.project_id, val.budget_id);
          }
        } else if (val.project_type == "Operational") {
          self.headersObj.dept_id.form = true;
          self.headersObj.type_operational_id.form = true;
          self.headersObj.sub_type_operational_id.form = true;

          if (val.dept_id) {
            self.headersObj.type_operational_id.options.filter.department_id =
              val.dept_id;
            self.headersObj.sub_type_operational_id.options.filter.department_id =
              val.dept_id;
          } else {
            delete self.headersObj.type_operational_id.options.filter
              .department_id;
            delete self.headersObj.sub_type_operational_id.options.filter
              .department_id;
          }

          // Set filter sub_type berdasarkan type yang sudah ada
          if (val.type_operational_id) {
            self.headersObj.sub_type_operational_id.options.filter.type_operational_id =
              val.type_operational_id;
          } else {
            delete self.headersObj.sub_type_operational_id.options.filter
              .type_operational_id;
          }

          // Tampilkan Remaining Budget jika operational lengkap
          if (
            val.dept_id &&
            val.type_operational_id &&
            val.sub_type_operational_id
          ) {
            self.fetchAndSetOperationalRemainingBudget(
              val.dept_id,
              val.type_operational_id,
              val.sub_type_operational_id,
            );
          }
        } else if (
          val.project_type == "Persediaan" ||
          val.project_type == "Asset"
        ) {
          self.fetchAndSetAssetOrPersediaanRemainingBudget(val.project_type);
        }

        self.headers = App.updateObject(self.headers);
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
  },
  mounted: function () {
    var self = this;
    self.setYearBudgetOptions();
  },
};

// kode lama + tambahan GPT yang fix
</script>
