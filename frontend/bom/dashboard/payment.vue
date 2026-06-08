<template>
  <v-container
    class="payment-dashboard-page"
    :class="{ 'is-mobile-view': isMobileView }"
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
      class="payment-dashboard-template"
      custom-sort
      v-model="value"
      ref="template"
      :item-key="itemKey"
      :url="url"
      :headers="headers"
      :items-options="itemsOptions"
      :name="name"
      @update:selected-row="onSelectedRow"
      @update:selected-row-all="onSelectedRowAll"
      :table-only="tableOnly"
      :show-expand="true"
      :single-expand="true"
    >
      <template v-slot:title-body v-if="$refs.template">
        <div class="payment-mobile-sticky-head">
          <b>Count Rows: </b>{{ $refs.template.itemsTotal }}
        </div>
      </template>
      <template v-slot:menu-after-filter>
        <!-- <v-btn color="primary" outlined small @click="dialogItem = true" :disabled="!selected">
                    Add Invoice
                </v-btn> -->
        <v-btn
          v-if="type == 2 || type == 1"
          color="primary"
          outlined
          small
          @click="openReportWeb"
          :disabled="!selected"
        >
          <v-icon small left>mdi-print</v-icon>Print
        </v-btn>
        <!-- <v-btn
          v-if="(type == 2 || type == 1) && check_user(['titik', 'dp', 'satrio'])"
          color="primary"
          outlined
          small
          @click="openReportWeb"
          :disabled="!selected"
        >
          <v-icon small left>mdi-print</v-icon>Print Web
        </v-btn> -->
        <template v-if="!nointeraction">
          <v-btn
            color="primary"
            v-if="type == 1"
            outlined
            small
            @click="approve"
            :disabled="checkApproval1"
          >
            Approve
          </v-btn>
          <v-btn
            color="primary"
            v-if="type == 2"
            outlined
            small
            @click="approve"
            :disabled="checkApproval2"
          >
            Approve
          </v-btn>
          <v-btn
            color="primary"
            outlined
            small
            @click="reject"
            :disabled="!selected"
          >
            Reject
          </v-btn>
        </template>
      </template>
      <template v-slot:expanded-item="props">
        <td
          ref="expanded"
          :colspan="props.headers.length"
          style="height: auto"
          :key="props.item[itemKey]"
        >
          <payment-item
            table-only
            :data="{
              payment_id: props.item[itemKey],
            }"
            :key="props.item[itemKey]"
            :table-fixed-header="false"
            :item-height-as-min-height="true"
            :table-fill="false"
          ></payment-item>
        </td>
      </template>
      <template v-slot:item.payment_no="props">
        <b>Payment No:</b> {{ props.item.payment_no }}<br />
        <b>Payment Date:</b> {{ props.item.payment_date }}
      </template>
      <template v-slot:item.approved="props">
        <b>Status:</b> {{ approvedStatus(props.item.approved) }}<br />
        <span v-if="props.item.approved1_date"
          ><b>Validated date:</b> {{ props.item.approved1_date }}<br
        /></span>
        <span v-if="props.item.approved1"
          ><b>Validated by:</b> {{ props.item.approved1 }}<br
        /></span>
        <span v-if="props.item.approved2_date"
          ><b>Approved date:</b> {{ props.item.approved2_date }}<br
        /></span>
        <span v-if="props.item.approved2"
          ><b>Approved by:</b> {{ props.item.approved2 }}</span
        >
      </template>
      <template v-slot:append-body="slotProps">
        <div v-if="isMobileView" class="payment-mobile-cards">
          <div
            v-if="!slotProps.items || slotProps.items.length === 0"
            class="payment-mobile-empty"
          >
            No data available
          </div>
          <v-card
            v-for="item in slotProps.items"
            :key="'mobile-dashboard-payment-' + item.id"
            outlined
            class="payment-mobile-card"
            :class="{ 'payment-mobile-card--selected': isSelectedRow(item) }"
            @click="selectMobileRow(item)"
          >
            <div class="payment-mobile-card__head">
              <div>
                <div class="payment-mobile-card__po">
                  {{ item.payment_no || "-" }}
                </div>
                <div class="payment-mobile-card__title">
                  {{ item.notes || "-" }}
                </div>
              </div>
              <v-chip
                x-small
                label
                :color="statusChipColor(item.approved)"
                text-color="#1f1f1f"
              >
                {{ approvedStatus(item.approved) }}
              </v-chip>
            </div>

            <div class="payment-mobile-card__grid">
              <div><b>Payment Date:</b> {{ item.payment_date || "-" }}</div>
              <div><b>Title:</b> {{ item.notes || "-" }}</div>
              <div>
                <b>Grand Total:</b> Rp.
                {{ item.totalrp || "0.00" }}
              </div>
              <div v-if="item.approved1_date">
                <b>Validated date:</b> {{ item.approved1_date }}
              </div>
              <div v-if="item.approved1">
                <b>Validated by:</b> {{ item.approved1 }}
              </div>
              <div v-if="item.approved2_date">
                <b>Approved date:</b> {{ item.approved2_date }}
              </div>
              <div v-if="item.approved2">
                <b>Approved by:</b> {{ item.approved2 }}
              </div>
            </div>

            <div class="payment-mobile-card__actions">
              <v-btn small color="primary" outlined @click.stop="openItemsByRow(item)">
                <v-icon small left>mdi-format-list-bulleted</v-icon>Items
              </v-btn>
              <v-btn
                v-if="type == 2 || type == 1"
                small
                color="primary"
                outlined
                @click.stop="openReportWebByRow(item)"
              >
                <v-icon small left>mdi-printer</v-icon>Print
              </v-btn>
              <template v-if="!nointeraction">
                <v-btn
                  v-if="type == 1"
                  small
                  color="success"
                  outlined
                  @click.stop="approveByRow(item)"
                  :disabled="!canApprove1(item)"
                >
                  Approve
                </v-btn>
                <v-btn
                  v-if="type == 2"
                  small
                  color="success"
                  outlined
                  @click.stop="approveByRow(item)"
                  :disabled="!canApprove2(item)"
                >
                  Approve
                </v-btn>
                <v-btn
                  small
                  color="red"
                  outlined
                  @click.stop="rejectByRow(item)"
                  :disabled="!item"
                >
                  Reject
                </v-btn>
              </template>
            </div>

          </v-card>
        </div>
      </template>
    </v-template>
    <v-action-dialog
      :actions="false"
      v-model="dialogItem"
      title="Payment Item"
      min-height="75%"
      fullscreen
      :key="selected ? selected.id : 'payment-detail'"
    >
      <payment-item
        v-if="selected"
        table-only
        :data="processData"
        :key="selected.id"
      ></payment-item>
    </v-action-dialog>
  </v-container>
</template>

<style scoped>
.v-data-table__wrapper > table > tbody > tr > td {
  font-size: 0.775rem;
}

.payment-mobile-cards {
  flex: 1;
  min-height: 0;
  padding: 10px;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

.payment-mobile-empty {
  padding: 14px;
  text-align: center;
  color: #666;
}

.payment-mobile-card {
  margin-bottom: 10px;
  padding: 10px;
  border-radius: 10px;
}

.payment-mobile-card--selected {
  border-color: #1976d2 !important;
  box-shadow: 0 0 0 1px #1976d2 inset;
}

.payment-mobile-card__head {
  display: flex;
  justify-content: space-between;
  gap: 8px;
}

.payment-mobile-card__po {
  font-weight: 700;
}

.payment-mobile-card__title {
  font-size: 0.82rem;
  color: #333;
}

.payment-mobile-card__grid {
  margin-top: 10px;
  display: grid;
  grid-template-columns: 1fr;
  gap: 4px;
  font-size: 0.82rem;
}

.payment-mobile-card__actions {
  margin-top: 10px;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

</style>

<style>
.payment-dashboard-page.is-mobile-view
  .payment-dashboard-template
  .table-container,
.payment-dashboard-page.is-mobile-view
  .payment-dashboard-template
  .template-table,
.payment-dashboard-page.is-mobile-view
  .payment-dashboard-template
  .v-data-footer,
.payment-dashboard-page.is-mobile-view
  .payment-dashboard-template
  .v-data-table,
.payment-dashboard-page.is-mobile-view
  .payment-dashboard-template
  .v-data-table__wrapper {
  display: none !important;
}

.payment-dashboard-page.is-mobile-view {
  overflow-y: auto !important;
  -webkit-overflow-scrolling: touch;
}

.payment-dashboard-page.is-mobile-view .payment-dashboard-template {
  min-height: 0;
  height: auto !important;
}

.payment-dashboard-page.is-mobile-view .payment-dashboard-template .v-card {
  min-height: 0;
}

.payment-dashboard-page.is-mobile-view
  .payment-dashboard-template
  .payment-mobile-sticky-head {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 30;
  display: flex;
  align-items: center;
  min-height: 46px;
  padding: 8px 52px 8px 12px;
  background: #f3f3f3;
  border-top: 1px solid #e2e2e2;
  border-bottom: 1px solid #d9d9d9;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
  font-size: 15px;
}

.payment-dashboard-page.is-mobile-view
  .payment-dashboard-template
  .payment-mobile-cards {
  padding-top: 62px;
}

.payment-dashboard-page.is-mobile-view
  .payment-dashboard-template
  .payment-mobile-fixed-menu-btn {
  position: fixed !important;
  right: 10px;
  z-index: 35;
  width: 34px;
  height: 34px;
  min-width: 34px !important;
  background: #f3f3f3 !important;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12);
  border-radius: 50%;
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
    type: {
      default: 0,
    },
    tableOnly: {
      type: Boolean,
      default: false,
    },
    nointeraction: {
      type: Boolean,
      default: false,
    },
    itemsOptions: {
      type: Object,
      default: {
        filter: {
          approved: 1,
        },
      },
    },
  },
  components: {
    "payment-item": "url:ui/bom/payment/payment_item.vue",
  },
  data: function () {
    return {
      name: "Dashboard",
      processData: {},
      itemKey: "id",
      url: "bom/payment",
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
          page: "0",
          limit: "10",
        },
        {
          text: "Payment No",
          value: "payment_no",
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
          text: "payment Date",
          value: "payment_date",
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
          default: new Date().formatDate("YYYY-MM-DD"),
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
          text: "Title",
          value: "notes",
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
          text: "Grand Total Price (RP)",
          value: "totalrp",
          align: "center",
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
          text: "Department",
          value: "dept_name",
          form: false,
          visible: false,
          type: "varchar",
          align: "start",
          sortable: false,
          filterable: false,
          filter: false,
        },
        {
          text: "Approval History",
          value: "approved",
          form: false,
          visible: true,
          type: "varchar",
          align: "start",
          sortable: false,
          filterable: false,
          filter: false,
        },
        {
          text: "Signed Payment",
          value: "signed_payment_doc_url",
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
        { text: "", value: "data-table-expand" },
      ],
      dialogItem: false,
      selected: false,
      dataid: {},
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
    checkApproval1: function () {
      var self = this;
      if (!self.selected) return true;
      if (self.selected.approved == 1) return false;
      return true;
    },
    checkApproval2: function () {
      var self = this;
      if (!self.selected) return true;
      if (self.selected.approved == 3) return false;
      return true;
    },
    isMobileView: function () {
      return !!(
        this.$vuetify &&
        this.$vuetify.breakpoint &&
        this.$vuetify.breakpoint.smAndDown
      );
    },
  },
  watch: {
    isMobileView: function () {
      var self = this;
      self.$nextTick(function () {
        self.applyMobileHeaderFix();
      });
    },
  },
  methods: {
    applyMobileHeaderFix: function () {
      var self = this;
      if (!self.$el) return;
      var oldFixedButtons = self.$el.querySelectorAll(
        ".payment-mobile-fixed-menu-btn",
      );
      oldFixedButtons.forEach(function (el) {
        el.classList.remove("payment-mobile-fixed-menu-btn");
        el.style.top = "";
      });
      if (!self.isMobileView) return;
      var stickyHead = self.$el.querySelector(".payment-mobile-sticky-head");
      var dotsIcon =
        self.$el.querySelector(".mdi-dots-vertical") ||
        self.$el.querySelector(".mdi-dots-horizontal");
      if (stickyHead) {
        var topOffset = 64;
        var tabsEl =
          document.querySelector(".v-tabs") ||
          document.querySelector(".v-tabs-bar") ||
          document.querySelector(".v-slide-group");
        if (tabsEl) {
          var tabsRect = tabsEl.getBoundingClientRect();
          topOffset = Math.max(0, tabsRect.bottom);
        }
        stickyHead.style.top = topOffset + "px";
      }
      if (!dotsIcon) return;
      var dotsButton = dotsIcon.closest("button, .v-btn");
      if (!dotsButton) return;
      dotsButton.classList.add("payment-mobile-fixed-menu-btn");
      if (stickyHead) {
        var rect = stickyHead.getBoundingClientRect();
        var btnSize = 34;
        var top = Math.max(0, rect.top + (rect.height - btnSize) / 2);
        dotsButton.style.top = top + "px";
      }
    },
    syncMobileHeaderFix: function () {
      var self = this;
      window.requestAnimationFrame(function () {
        self.applyMobileHeaderFix();
      });
    },
    isSelectedRow: function (item) {
      return !!(this.selected && item && this.selected.id === item.id);
    },
    selectMobileRow: function (item) {
      this.onSelectedRow(item);
    },
    openItemsByRow: function (item) {
      var self = this;
      self.onSelectedRow(item);
      self.$nextTick(function () {
        self.dialogItem = true;
      });
    },
    canApprove1: function (item) {
      return !!(item && item.approved == 1);
    },
    canApprove2: function (item) {
      return !!(item && item.approved == 3);
    },
    approveByRow: function (item) {
      this.onSelectedRow(item);
      this.approve();
    },
    rejectByRow: function (item) {
      this.onSelectedRow(item);
      this.reject();
    },
    openReport: function (item) {
      var self = this;
      var target = item || self.selected;
      if (!target) return;
      window.open(
        "https://main.buanamultiteknik.com/api/report/payment/index?id=" +
          target.id,
      );
    },
    openReportWeb: function (item) {
      var self = this;
      var target = item || self.selected;
      if (!target) return;
      window.open(
        "https://main.buanamultiteknik.com/api/report/payment/index?debughtml=true&id=" +
          target.id +
          "&rand=" +
          randomId() +
          "&pagebreak=0",
      );
    },
    openReportWebByRow: function (item) {
      this.onSelectedRow(item);
      this.openReportWeb(item);
    },
    onSelectedRow: function (val) {
      var self = this;
      if (val === undefined) {
        self.selected = false;
        self.processData = {};
      } else {
        self.selected = val;
        self.processData = {
          payment_id: val.id,
        };
      }
    },
    onSelectedRowAll: function (val) {
      var self = this;
      self.selectedAll = val;
    },
    approve: async function () {
      var self = this;
      var c = confirm("Are you sure?");
      if (!c) return true;
      var params = {
        approved: self.selected.approved == 1 ? 2 : 4,
        pk: self.selected.id,
      };
      if (self.selected.approved == 1) {
        params.approved1 = App.userData.data[0].name;
        params.approved1_date = new Date().formatDate("YYYY-MM-DD HH:mm:ss");
      }
      if (self.selected.approved == 3) {
        params.approved2 = App.userData.data[0].name;
        params.approved2_date = new Date().formatDate("YYYY-MM-DD HH:mm:ss");
      }
      var r = await axios.put(App.url + "bom/payment", params);
      if (!r.data.status) App.errorMsg(r.data);
      else {
        self.$refs.template.getItems();
        App.successMsg();
      }
    },
    comment: async function () {
      var self = this;
      var r = await axios.post(App.url + "bom/payment_comment", {
        notes: self.po_comment,
        payment_id: self.selected.id,
      });
      if (!r.data.status) App.errorMsg(r.data);
      else {
        self.$refs.template.getItems();
        App.successMsg();
        self.dialogComment = false;
        self.$refs.template.getItems();
      }
    },
    reject: async function () {
      var self = this;
      var r = await axios.put(App.url + "bom/payment", {
        approved: self.selected.approved == 1 ? -1 : -2,
        pk: self.selected.id,
      });
      if (!r.data.status) App.errorMsg(r.data);
      else {
        self.$refs.template.getItems();
        App.successMsg();
      }
    },
    approvedStatus: function (f) {
      if (f == 0) {
        return "New";
      }
      if (f == 1) {
        return "Asking for Approval 1";
      }
      if (f == 2) {
        return "Approved Approval 1";
      }
      if (f == -1) {
        return "Rejected Approval 1";
      }
      if (f == 3) {
        return "Asking for Approval 2";
      }
      if (f == 4) {
        return "Approved Approval 2";
      }
      if (f == -2) {
        return "Rejected Approval 2";
      }
    },
    statusChipColor: function (approved) {
      if (approved == 0) return "#f5e699";
      if (approved == -1 || approved == -2) return "#f88686";
      if (approved == 1 || approved == 2 || approved == 3 || approved == 4)
        return "#ffcc99";
      return "#e0e0e0";
    },
  },
  mounted: function () {
    var self = this;
    window.addEventListener("resize", self.syncMobileHeaderFix);
    window.addEventListener("scroll", self.syncMobileHeaderFix, {
      passive: true,
    });
    this.syncMobileHeaderFix();
  },
  updated: function () {
    this.syncMobileHeaderFix();
  },
  beforeDestroy: function () {
    window.removeEventListener("resize", this.syncMobileHeaderFix);
    window.removeEventListener("scroll", this.syncMobileHeaderFix);
  },
};
</script>
