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
      v-model="value"
      ref="template"
      :item-key="itemKey"
      :url="url"
      :headers="headers"
      :name="name"
      :items-options="itemsOptions"
      :hide-edit-button="true"
      :hide-delete-button="true"
      @update:selected-row="onSelectedRow"
      @before-save="beforeSave"
    >
      <template v-slot:title-body v-if="$refs.template">
        <b>Count Rows: </b>{{ $refs.template.itemsTotal }}
      </template>

      <template v-slot:menu-after-filter>
        <v-btn
          color="primary"
          outlined
          small
          :disabled="!selected"
          @click="openDetailDialog"
        >
          <v-icon small left>mdi-file-document-outline</v-icon>Detail
        </v-btn>
      </template>

      <template v-slot:item.created_date="props">
        {{ props.item.created_date || props.item.created_at || "-" }}
      </template>

      <template v-slot:item.created_by="props">
        {{
          props.item.created_by_name ||
          props.item.created_by ||
          props.item.creator_name ||
          "-"
        }}
      </template>

      <template v-slot:prepend-dialog-add>
        <div style="padding-top: 12px"></div>
      </template>
    </v-template>

    <v-action-dialog
      v-model="dialogDetail"
      title="Manufacturing Order Detail"
      fullscreen
      :actions="false"
    >
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
        <div style="padding: 12px; display: flex; justify-content: flex-end">
          <v-btn
            color="primary"
            outlined
            small
            :disabled="!selectedDetail"
            @click="openSelectPoDialog"
            style="margin-right: 8px"
          >
            <v-icon small left>mdi-clipboard-list-outline</v-icon>Select PO
          </v-btn>
          <v-btn color="primary" outlined small @click="openAddItemDialog">
            <v-icon small left>mdi-plus</v-icon>Add Item
          </v-btn>
        </div>
        <v-template
          v-model="detailValue"
          ref="detailTemplate"
          :show-expand="true"
          :single-expand="false"
          :item-key="detailItemKey"
          :url="detailUrl"
          :headers="detailHeaders"
          :name="detailName"
          :items-options="detailItemsOptions"
          :hide-add-button="true"
          :hide-edit-button="true"
          :hide-delete-button="true"
          :hide-filter-button="true"
          :show-select="false"
          @update:selected-row="onSelectedDetailRow"
          @after-get-items="afterGetDetailItems"
          table-only
        >
          <template v-slot:expanded-item="props">
            <td :colspan="props.headers.length" class="manufacturing-po-expand">
              <div
                v-if="
                  !attachedPoMap[props.item.id] ||
                  attachedPoMap[props.item.id].loading
                "
                class="manufacturing-po-expand__state"
              >
                Loading...
              </div>
              <div
                v-else-if="attachedPoMap[props.item.id].rows.length === 0"
                class="manufacturing-po-expand__state"
              >
                No PO attached
              </div>
              <div v-else class="manufacturing-po-list">
                <div
                  v-for="po in attachedPoMap[props.item.id].rows"
                  :key="'attached-po-' + props.item.id + '-' + po.id"
                  class="manufacturing-po-card"
                >
                  <div class="manufacturing-po-card__head">
                    <div class="manufacturing-po-card__title">
                      {{ po.po_no || "-" }}
                    </div>
                    <div class="manufacturing-po-card__meta">
                      {{ po.po_title || "No Title" }}
                    </div>
                  </div>
                  <div class="manufacturing-po-card__table-wrap">
                    <table class="manufacturing-po-table">
                    <thead>
                      <tr>
                        <th>Item No</th>
                        <th>Item Name</th>
                        <th class="manufacturing-po-table__qty">Qty</th>
                        <th class="manufacturing-po-table__action">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-if="po.itemsLoading">
                        <td colspan="4" class="manufacturing-po-table__empty">
                          Loading items...
                        </td>
                      </tr>
                      <tr v-else-if="!po.items || po.items.length === 0">
                        <td colspan="4" class="manufacturing-po-table__empty">
                          No PO items
                        </td>
                      </tr>
                      <tr
                        v-else
                        v-for="item in po.items"
                        :key="'po-item-' + po.purchase_order_id + '-' + item.id"
                      >
                        <td class="manufacturing-po-table__item-no">
                          {{ item.item_no || "-" }}
                        </td>
                        <td>{{ item.item_name || "-" }}</td>
                        <td class="manufacturing-po-table__qty">
                          {{ formatQty(item.order_qty) }}
                        </td>
                        <td class="manufacturing-po-table__action">
                          <v-btn
                            color="primary"
                            outlined
                            x-small
                            @click="openUsePartDialog(props.item, po, item)"
                          >
                            Gunakan Part
                          </v-btn>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  </div>
                </div>
              </div>
            </td>
          </template>
        </v-template>
      </v-container>
    </v-action-dialog>

    <v-action-dialog
      class="manufacturing-order-add-item-dialog"
      v-model="dialogAddItem"
      title="Add Item"
      min-height="0"
      :scrollable="false"
      @save="saveItemDraft"
      :save-loading="saveItemLoading"
      :disable-save="!partValid"
    >
      <div style="padding-top: 12px; overflow: hidden">
        <form-template
          :headers="itemHeaders"
          :valid.sync="partValid"
          ref="itemForm"
        ></form-template>
      </div>
    </v-action-dialog>

    <v-action-dialog
      class="manufacturing-order-select-po-dialog"
      v-model="dialogSelectPo"
      title="Select PO"
      min-height="0"
      :scrollable="false"
      @save="saveSelectedPoDraft"
      :save-loading="savePoLoading"
      :disable-save="!poValid"
    >
      <div style="padding-top: 12px; overflow: hidden">
        <form-template
          :headers="poHeaders"
          :valid.sync="poValid"
          ref="poForm"
        ></form-template>
      </div>
    </v-action-dialog>

    <v-action-dialog
      v-model="dialogUsePart"
      title="Konfirmasi Gunakan Part"
      min-height="0"
      @save="confirmUsePart"
      :disable-save="
        usePartQty === null ||
        usePartQty === undefined ||
        String(usePartQty).trim() === ''
      "
    >
      <div style="padding-top: 12px; line-height: 1.7">
        <div>
          Item: <b>{{ selectedUsePart.item_name || "-" }}</b>
        </div>
        <div>
          Item No: <b>{{ selectedUsePart.item_no || "-" }}</b>
        </div>
        <div>
          PO: <b>{{ selectedUsePart.po_no || "-" }}</b>
        </div>
        <div>
          Qty PO: <b>{{ formatQty(selectedUsePart.order_qty) }}</b>
        </div>
        <div style="margin-top: 12px">
          <v-text-field
            v-model="usePartQty"
            label="Qty yang ingin digunakan"
            type="number"
            filled
            dense
            min="1"
          ></v-text-field>
        </div>
      </div>
    </v-action-dialog>
  </v-container>
</template>

<style>
.manufacturing-order-add-item-dialog .v-action-dialog-card {
  overflow: hidden !important;
}

.manufacturing-order-select-po-dialog .v-action-dialog-card {
  overflow: hidden !important;
}

/* Remove Vuetify's default grey background on expanded rows */
.v-data-table__expanded.v-data-table__expanded__content {
  box-shadow: none !important;
  background: #fff !important;
}

.manufacturing-po-expand {
  padding: 0 !important;
  background: #fff !important;
}

.manufacturing-po-expand__state {
  padding: 14px 16px;
  text-align: center;
  color: #9ca3af;
  font-size: 13px;
  background: #fafbfc;
  border-top: 1px solid #e8eaed;
}

.manufacturing-po-list {
  display: flex;
  flex-direction: column;
}

.manufacturing-po-card {
  border-top: 1px solid #e8eaed;
}

.manufacturing-po-card__head {
  padding: 8px 16px;
  background: #f5f7fa;
  border-bottom: 1px solid #e8eaed;
  display: flex;
  align-items: center;
  gap: 8px;
}

.manufacturing-po-card__title {
  font-size: 12px;
  font-weight: 600;
  color: #374151;
}

.manufacturing-po-card__meta {
  font-size: 12px;
  color: #9ca3af;
}

.manufacturing-po-card__table-wrap {
  padding: 0;
}

.manufacturing-po-table {
  width: 100%;
  border-collapse: collapse;
}

.manufacturing-po-table th {
  padding: 8px 16px;
  background: #fafbfc;
  border-bottom: 1px solid #e8eaed;
  font-size: 12px;
  font-weight: 500;
  color: #6b7280;
  text-align: left;
}

.manufacturing-po-table td {
  padding: 8px 16px;
  border-bottom: 1px solid #f0f2f4;
  font-size: 13px;
  color: #374151;
  background: #fff;
}

.manufacturing-po-table tbody tr:last-child td {
  border-bottom: none;
}

.manufacturing-po-table tbody tr:hover td {
  background: #f9fafb;
}

.manufacturing-po-table__item-no {
  width: 140px;
  color: #374151;
  white-space: nowrap;
}

.manufacturing-po-table__qty {
  width: 120px;
  text-align: right;
  white-space: nowrap;
}

.manufacturing-po-table__action {
  width: 140px;
  text-align: center;
  white-space: nowrap;
}

.manufacturing-po-table__empty {
  text-align: center;
  color: #9ca3af;
  padding: 12px 16px !important;
  font-size: 13px;
}
</style>

<script>
module.exports = {
  name: "",
  components: {
    "form-template": "url:ui/admin/form-template.vue",
  },
  props: {
    value: undefined,
  },
  data: function () {
    return {
      detailValue: undefined,
      name: "Manufacturing Order",
      itemKey: "id",
      url: "bom/manufacturing_order",
      detailName: "Manufacturing Order Detail",
      detailItemKey: "id",
      detailUrl: "bom/manufacturing_order_detail",
      selected: false,
      selectedDetail: false,
      dialogDetail: false,
      dialogAddItem: false,
      dialogSelectPo: false,
      dialogUsePart: false,
      partValid: false,
      poValid: false,
      saveItemLoading: false,
      savePoLoading: false,
      attachedPoMap: {},
      selectedUsePart: {},
      usePartQty: 1,
      itemsOptions: {
        filter: {
          flag: 1,
        },
        filterType: {},
      },
      detailItemsOptions: {
        filter: {
          manufacturing_order_id: 0,
        },
        filterType: {},
      },
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
          page: 0,
          limit: 10,
        },
        {
          text: "Title",
          value: "title",
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
          page: 0,
          limit: 10,
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
          type: "text",
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
          type: "text",
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: false,
          groupable: false,
        },
      ],
      itemHeaders: [
        {
          text: "Item",
          value: "item_id",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: true,
          required: true,
          form: true,
          filter: false,
          groupable: false,
          url: App.url + "bom/item",
          searchby: ["item_no", "item_name"],
          formatter: function (val) {
            return {
              text: (val.item_no || "-") + " - " + (val.item_name || "-"),
              value: val.id,
            };
          },
          options: {
            filter: {
              flag: 1,
            },
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: 1,
          limit: 10,
          clearable: true,
          data: null,
        },
        {
          text: "Qty",
          value: "qty",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "int",
          disabled: false,
          visible: true,
          required: true,
          form: true,
          filter: false,
          groupable: false,
          data: null,
        },
      ],
      detailHeaders: [
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
          page: 0,
          limit: 10,
        },
        {
          text: "Name",
          value: "name",
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
      ],
      poHeaders: [
        {
          text: "PO",
          value: "purchase_order_id",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: true,
          required: true,
          form: true,
          filter: false,
          groupable: false,
          url: App.url + "bom/purchaseorder",
          searchby: ["po_no"],
          formatter: ["id", "po_no"],
          options: {
            filter: {
              flag: 1,
            },
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: 1,
          limit: 10,
          clearable: true,
          data: null,
        },
      ],
    };
  },
  methods: {
    formatQty: function (value) {
      var number = Number(value || 0);
      if (Number.isInteger(number)) {
        return number.toLocaleString("id-ID", {
          maximumFractionDigits: 0,
        });
      }
      return number.toLocaleString("id-ID", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 3,
      });
    },
    onSelectedRow: function (val) {
      this.selected = val === undefined ? false : val;
    },
    onSelectedDetailRow: function (val) {
      this.selectedDetail = val === undefined ? false : val;
    },
    afterGetDetailItems: function (data) {
      var self = this;
      var rows = data && data.data && Array.isArray(data.data) ? data.data : [];
      self.loadAttachedPos(rows);
    },
    loadAttachedPos: async function (detailRows) {
      var self = this;
      var rows = Array.isArray(detailRows) ? detailRows : [];
      var nextMap = {};
      rows.forEach(function (row) {
        nextMap[row.id] = {
          loading: true,
          rows: [],
        };
      });
      self.attachedPoMap = nextMap;

      await Promise.all(
        rows.map(async function (row) {
          try {
            var relRes = await axios.get(
              App.url + "bom/manufacturing_order_detail_po",
              {
                params: {
                  limit: -1,
                  filter: {
                    manufacturing_order_detail_id: row.id,
                  },
                  filterType: {},
                  filterCondition: {},
                },
              },
            );
            var relRows =
              relRes && relRes.data && Array.isArray(relRes.data.data)
                ? relRes.data.data
                : [];

            var poRows = await Promise.all(
              relRows.map(async function (po) {
                var poRow = Object.assign({}, po, {
                  items: [],
                  itemsLoading: true,
                });
                try {
                  var itemRes = await axios.get(
                    App.url + "bom/purchase_order_item",
                    {
                      params: vTableParam({
                        limit: 1000,
                        filter: {
                          purchase_order_id: po.purchase_order_id,
                        },
                        filterType: {},
                        filterCondition: {},
                      }),
                    },
                  );
                  poRow.items =
                    itemRes && itemRes.data && Array.isArray(itemRes.data.data)
                      ? itemRes.data.data
                      : [];
                } catch (e) {
                  poRow.items = [];
                }
                poRow.itemsLoading = false;
                return poRow;
              }),
            );

            self.$set(self.attachedPoMap, row.id, {
              loading: false,
              rows: poRows,
            });
          } catch (e) {
            self.$set(self.attachedPoMap, row.id, {
              loading: false,
              rows: [],
            });
          }
        }),
      );
    },
    openDetailDialog: function () {
      var self = this;
      if (!self.selected || !self.selected.id) return;
      self.selectedDetail = false;
      self.attachedPoMap = {};
      self.detailItemsOptions = {
        filter: {
          manufacturing_order_id: self.selected.id,
        },
        filterType: {},
      };
      self.dialogDetail = true;
      self.$nextTick(function () {
        if (
          self.$refs.detailTemplate &&
          typeof self.$refs.detailTemplate.getItems === "function"
        ) {
          self.$refs.detailTemplate.getItems();
        }
      });
    },
    openSelectPoDialog: function () {
      this.poValid = false;
      this.poHeaders = [
        {
          text: "PO",
          value: "purchase_order_id",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: true,
          required: true,
          form: true,
          filter: false,
          groupable: false,
          url: App.url + "bom/purchaseorder",
          searchby: ["po_no"],
          formatter: ["id", "po_no"],
          options: {
            filter: {
              flag: 1,
            },
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: 1,
          limit: 10,
          clearable: true,
          data: null,
          data_value: [],
          data_selected: null,
        },
      ];
      this.dialogSelectPo = true;
    },
    openUsePartDialog: function (detailItem, po, item) {
      this.selectedUsePart = Object.assign({}, item, {
        detail_id: detailItem && detailItem.id,
        detail_name: detailItem && detailItem.name,
        po_id: po && po.purchase_order_id,
        po_no: po && po.po_no,
        po_title: po && po.po_title,
      });
      this.usePartQty = 1;
      this.dialogUsePart = true;
    },
    openAddItemDialog: function () {
      this.partValid = false;
      this.itemHeaders = [
        {
          text: "Item",
          value: "item_id",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: true,
          required: true,
          form: true,
          filter: false,
          groupable: false,
          url: App.url + "bom/item",
          searchby: ["item_no", "item_name"],
          formatter: function (val) {
            return {
              text: (val.item_no || "-") + " - " + (val.item_name || "-"),
              value: val.id,
            };
          },
          options: {
            filter: {
              flag: 1,
            },
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: 1,
          limit: 10,
          clearable: true,
          data: null,
          data_value: [],
          data_selected: null,
        },
        {
          text: "Qty",
          value: "qty",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "int",
          disabled: false,
          visible: true,
          required: true,
          form: true,
          filter: false,
          groupable: false,
          data: null,
        },
      ];
      this.dialogAddItem = true;
    },
    saveItemDraft: function () {
      var self = this;
      var itemHeader = self.itemHeaders.find(function (item) {
        return item.value === "item_id";
      });
      var qtyHeader = self.itemHeaders.find(function (item) {
        return item.value === "qty";
      });
      var selectedItemId = itemHeader ? itemHeader.data : null;
      var qty = qtyHeader ? qtyHeader.data : null;

      if (!self.selected || !self.selected.id) {
        App.errorMsg("Manufacturing order not selected!");
        return;
      }

      if (
        !selectedItemId ||
        qty === null ||
        qty === undefined ||
        String(qty).trim() === ""
      ) {
        App.errorMsg("Item and qty cannot be empty!");
        return;
      }

      var selectedOption = itemHeader ? itemHeader.data_selected : null;
      if (!selectedOption && itemHeader && Array.isArray(itemHeader.data_value)) {
        selectedOption = itemHeader.data_value.find(function (option) {
          return option && String(option.value) === String(selectedItemId);
        });
      }

      var selectedRow = null;
      if (itemHeader && Array.isArray(itemHeader.real_data)) {
        selectedRow = itemHeader.real_data.find(function (row) {
          return String(row.id) === String(selectedItemId);
        });
      }

      var itemName = selectedRow ? selectedRow.item_name : "";
      if (!itemName && selectedOption && selectedOption.text) {
        var parts = String(selectedOption.text).split(" - ");
        itemName = parts.length > 1 ? parts.slice(1).join(" - ") : selectedOption.text;
      }

      if (!itemName) {
        App.errorMsg("Item name not found!");
        return;
      }

      self.saveItemLoading = true;
      axios
        .post(App.url + "bom/manufacturing_order_detail", {
          manufacturing_order_id: self.selected.id,
          name: itemName,
          qty: Number(qty || 0),
        })
        .then(function (r) {
          if (!r.data.status) {
            App.errorMsg(r.data);
            return;
          }
          self.dialogAddItem = false;
          if (
            self.$refs.detailTemplate &&
            typeof self.$refs.detailTemplate.getItems === "function"
          ) {
            self.$refs.detailTemplate.getItems();
          }
          App.successMsg();
        })
        .catch(function (e) {
          App.errorMsg(e);
        })
        .finally(function () {
          self.saveItemLoading = false;
        });
    },
    confirmUsePart: function () {
      this.dialogUsePart = false;
    },
    saveSelectedPoDraft: function () {
      var self = this;
      var poHeader = self.poHeaders.find(function (item) {
        return item.value === "purchase_order_id";
      });
      var purchaseOrderId = poHeader ? poHeader.data : null;

      if (!self.selectedDetail || !self.selectedDetail.id) {
        App.errorMsg("Manufacturing order detail not selected!");
        return;
      }

      if (!purchaseOrderId) {
        App.errorMsg("PO cannot be empty!");
        return;
      }

      self.savePoLoading = true;
      axios
        .post(App.url + "bom/manufacturing_order_detail_po", {
          manufacturing_order_detail_id: self.selectedDetail.id,
          purchase_order_id: purchaseOrderId,
        })
        .then(function (r) {
          if (!r.data.status) {
            App.errorMsg(r.data.data || r.data);
            return;
          }
          self.dialogSelectPo = false;
          if (
            self.$refs.detailTemplate &&
            typeof self.$refs.detailTemplate.getItems === "function"
          ) {
            self.$refs.detailTemplate.getItems();
          }
          App.successMsg();
        })
        .catch(function (e) {
          App.errorMsg(e);
        })
        .finally(function () {
          self.savePoLoading = false;
        });
    },
    beforeSave: function (opt) {
      opt.params.flag = 1;
      return opt;
    },
  },
  mounted: function () {},
};
</script>
