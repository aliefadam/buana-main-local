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
    >
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
      <template v-slot:menu-after-filter>
        <v-btn
          color="primary"
          v-if="type < 4"
          outlined
          small
          @click="askApproval"
          :disabled="disableApproval"
        >
          {{ txtApproval[type] }}
        </v-btn>
        <v-btn
          color="primary"
          v-if="[1, 2].includes(type)"
          outlined
          small
          @click="reject"
        >
          {{ txtApproval[type] }}
        </v-btn>
      </template>
      <template v-slot:menu-after-filter>
        <v-btn
          small
          color="primary"
          outlined
          :disabled="selected == false"
          @click="dialogPart = true"
          >Parts</v-btn
        >
        <!-- <v-btn small color="success" outlined v-if="tableOnly && !history" :disabled="selected == false" @click="approve" >Approve</v-btn>
				<v-btn small color="warning" outlined v-if="tableOnly && !history" :disabled="selected == false" @click="reject" >Reject</v-btn>
				<v-btn small color="success" outlined v-if="!tableOnly && !history" :disabled="selected == false" @click="askApproval" >Ask For Approval</v-btn> -->
        <v-btn
          color="primary"
          v-if="type < 4"
          outlined
          small
          @click="askApproval"
          :disabled="disableApproval"
        >
          {{ txtApproval[type] }}
        </v-btn>
        <v-btn
          color="primary"
          v-if="[1, 2].includes(type)"
          outlined
          small
          @click="reject"
        >
          {{ txtApproval[type] }}
        </v-btn>
        <v-btn
          small
          color="success"
          outlined
          v-if="history"
          :disabled="selected == false"
          @click="print"
          >Print</v-btn
        >
      </template>
    </v-template>
    <v-action-dialog
      :actions="false"
      v-model="dialogPart"
      title="Purchase Order Item"
      min-height="75%"
      fullscreen
    >
      <pr-part
        :key="selected.id"
        :data="dataid"
        :table-only="tableOnly"
      ></pr-part>
    </v-action-dialog>
  </v-container>
</template>

<style scoped></style>

<script>
module.exports = {
  name: "",
  creator: "",
  components: {
    "pr-part": "url:ui/pr/part.vue",
  },
  props: {
    value: undefined,
    data: {
      type: Object,
    },
    type: {
      default: 0,
    },
    history: {
      type: Boolean,
      default: false,
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
        filter: {
          ask_approval: null,
        },
        filterType: {
          ask_approval: "isnull",
        },
      },
    },
  },
  data: function () {
    return {
      name: "Purchase Requisition",
      txtApproval: {
        0: "Asking Approval",
        1: "Approve",
        2: "Approve",
      },
      dataid: {},
      itemKey: "id",
      url: "bom/pr",
      loading: false,
      dialogPart: false,
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
          text: "Pr No",
          value: "pr_no",
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
          text: "Pr Date",
          value: "pr_date",
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
          text: "Pr Subject",
          value: "pr_subject",
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
          text: "Pr Notes",
          value: "pr_notes",
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
          text: "Peminta",
          value: "pr_peminta",
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
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "users/info",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {
              group_name: "pr_peminta",
              sub_group_name: "pr_peminta",
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
          alias: "peminta",
        },
        {
          text: "Menyetujui",
          value: "pr_menyetujui",
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
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "users/info",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {
              group_name: "pr_penyetuju",
              sub_group_name: "pr_penyetuju",
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
          alias: "menyetujui",
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
      ],
      //row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
      selected: false,
      //berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
      selectedAll: [],
      isInDom: false,
      isInViewport: false,
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
    disableApproval: function () {
      var self = this;
      if (self.selected == false) return true;
      if (self.selected.approved > self.type) return true;
      return false;
    },
  },
  methods: {
    onSelectedRow: function (val) {
      var self = this;
      if (val === undefined) {
        self.selected = false;
        self.dataid = {};
      } else {
        self.selected = val;
        self.dataid = {
          pr_id: val.id,
        };
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
    askApproval: async function () {
      var self = this;
      var c = confirm("Are you sure?");
      if (c) {
        var params = {
          approved: self.type + 1,
          pk: self.selected.id,
        };
        var r = await axios.put(App.url + "bom/pr", params);
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      }
    },
    reject: async function () {
      var self = this;
      var c = confirm("Are you sure?");
      if (c) {
        var params = {
          approved: -1,
          pk: self.selected.id,
        };
        var r = await axios.put(App.url + "bom/pr", params);
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      }
    },
    /* askApproval: async function() {
                var self = this
                var c = confirm('Are you sure?')
                if (c) {
                    var params = {
                        askapproval: 1,
                        pk: self.selected.id
                    }
                    var r = await axios.put(App.url + 'bom/pr', params)
                    if (!r.data.status)
                        App.errorMsg(r.data)
                    else {
                        self.$refs.template.getItems()
                        App.successMsg()
                    }
                }
            }, */
    print: function () {
      window.open(
        "https://main.buanamultiteknik.com/api/report/pr/index?pr_id=" +
          this.selected.id,
      );
    },
  },
  mounted: function () {},
};
</script>
