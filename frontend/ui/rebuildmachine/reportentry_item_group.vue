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
      @open-edit="onOpenEdit"
      hide-edit-button
      hide-delete-button
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
      @save="save"
      @update="update"
    >
      <template v-slot:title-body v-if="$refs.template">
        <b>Count Rows: </b>{{ $refs.template.itemsTotal }}
      </template>

      <template v-slot:menu-after-filter>
        <!-- <v-btn color="primary" outlined small @click="addPIC" :disabled="!selected">
                    <v-icon small left>mdi-account-group</v-icon>Add PIC
            </v-btn> -->
      </template>
      <template v-slot:item.actions="props">
        <template v-if="selected">
          <template v-if="selected.id == props.item.id">
            <v-btn
              color="error"
              small
              outlined
              @click="$refs.template.deleteData()"
              >Delete</v-btn
            >
            <v-btn
              color="warning"
              style="margin-top: 8px"
              small
              outlined
              @click.stop="$refs.template.openUpdate()"
              >Edit</v-btn
            >
          </template>
        </template>
      </template>
      <template v-slot:item.time="props">
        <span>Start Time: </span>{{ props.item.start_time }}<br />
        <span>End Time: </span>{{ props.item.end_time }}
      </template>
      <template v-slot:item.type="props">
        {{ typestatus(props.item.type) }}<br /><br />
      </template>
      <template v-slot:item.replacement="props">
        <span>NPB No: </span>{{ props.item.npb_no }}<br />
        <span>PN: </span>{{ props.item.manufacture_pn }}<br />
        <span>QTY: </span>{{ props.item.qty }}<br />
        <span v-if="props.item.ismatch == 1">
          <v-icon small>mdi-flag</v-icon>
        </span>
      </template>
      <template v-slot:item.photo="props">
        <a
          v-if="props.item.attachment != null"
          target="_blank"
          :href="
            'https://main.buanamultiteknik.com/api/uploads/rm' +
            props.item.parent_id +
            '/' +
            props.item.attachment.slice(0).split('+++')[0]
          "
        >
          <img
            style="
              border: 1px solid #ddd;
              border-radius: 4px;
              padding: 5px;
              height: 150px;
            "
            :src="
              'https://main.buanamultiteknik.com/api/uploads/rm' +
              props.item.parent_id +
              '/' +
              props.item.attachment.slice(0).split('+++')[0]
            "
          />
        </a>
      </template>
      <!-- <report-item style="display: none;" @after-save="onAfterSave" :sel="sel" :data="data" ref="sjItem"></report-item>
        <v-action-dialog :actions="false" v-model="dialogItem" title="Detail Activity" min-height="75%" fullscreen>
            <report-item :key="selected.item_id+'_'+data.parent_id" :sel="processData" name="" :data="dataid"></report-item>
        </v-action-dialog> -->
    </v-template>

    <v-action-dialog @save="savePIC" title="Add PIC" v-model="dialogPIC">
      <v-autoform v-model="formPIC" :valid="valid"></v-autoform>
    </v-action-dialog>
  </v-container>
</template>

<style scoped></style>

<script>
module.exports = {
  name: "Activity",
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
      },
    },
  },
  components: {
    "report-item": "url:ui/rebuildmachine/reportentry_item.vue",
  },
  data: function () {
    return {
      valid: false,
      name: "Activity",
      processData: {},
      itemKey: "id",
      url: "rebuildmachine/reportitemgroup",
      formPIC: [
        {
          text: "PIC",
          value: "pic",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          data: null,
          disabled: false,
          visible: false,
          required: true,
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "users",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {
              groups: "rebuild_report_page",
            },
            filterType: {
              groups: "%",
            },
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "pic_name",
        },
      ],
      headers: [
        {
          text: "Actions",
          value: "actions",
          type: "varchar",
          form: false,
          filter: false,
        },
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
          filter: true,
          groupable: false,
          paging: true,
          page: "0",
          limit: "10",
        },
        {
          text: "parent_id",
          value: "parent_id",
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
          paging: true,
          page: "0",
          limit: "10",
        },
        {
          text: "Time",
          value: "time",
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
          text: "Start Time",
          value: "start_time",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "time",
          disabled: false,
          visible: false,
          required: false,
          form: true,
          filter: true,
          groupable: false,
        },
        {
          text: "End Time",
          value: "end_time",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "time",
          disabled: false,
          visible: false,
          required: false,
          form: true,
          filter: true,
          groupable: false,
        },
        {
          text: "Type Activity",
          value: "type",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          clearable: true,
          disabled: false,
          visible: true,
          required: true,
          form: true,
          default: App.page().selected.type,
          filter: true,
          groupable: false,
          data_value: [
            {
              text: "Replacement",
              value: "1",
            },
            {
              text: "Repair",
              value: "2",
            },
            {
              text: "Installation",
              value: "3",
            },
          ],
          input: function (data) {
            var self = App.$get("Activity");
            if (data.data.toString() == "1") {
              self.headersObj.npb_id.form = true;
              self.headersObj.npb_id.required = true;
              self.headersObj.item_replacement_id.form = true;
              self.headersObj.item_replacement_id.required = true;
              self.headersObj.qty.form = true;
              self.headersObj.qty.required = true;
            } else {
              self.headersObj.npb_id.form = false;
              self.headersObj.npb_id.required = false;
              self.headersObj.item_replacement_id.form = false;
              self.headersObj.item_replacement_id.required = false;
              self.headersObj.qty.form = false;
              self.headersObj.qty.required = false;
            }
          },
        },
        {
          text: "Section",
          value: "section_id",
          align: "start",
          sortable: false,
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
          data_value: ["SE", "VE", "MAX", "HCF"],
          input: function (data) {
            var self = App.$get("Activity");
            if (data.data) {
              self.headersObj.partno_id.options.filter = {
                type_subassy: data.data,
              };
              self.headersObj.subassy_id.options.filter = {
                type_subassy: data.data,
              };
            } else {
              delete self.headersObj.partno_id.options.filter;
              delete self.headersObj.subassy_id.option.filter;
            }
            self.headersObj.partno_id.update = null;
            self.headersObj.subassy_id.update = null;
            self.headers = App.updateObject(self.headers);
          },
        },
        {
          text: "Subassembly",
          value: "subassy_id",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          clearable: true,
          disabled: false,
          visible: true,
          required: true,
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "maintenance/subassembly",
          searchby: ["subassy_info"],
          formatter: ["sub_assembly", "subassy_info"],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
            subassy: true,
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Part No",
          value: "partno_id",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          clearable: true,
          disabled: false,
          visible: true,
          required: true,
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "maintenance/subassembly",
          searchby: ["part_info"],
          formatter: function (val) {
            return {
              text: val.part_info,
              value: val.part_info,
              article_number: val.art_num,
            };
          }, //['part_info', 'part_info'],
          input: function (data) {
            var self = App.$get("Activity");
            if (data.data) {
              var val = data.data_value.filter(
                (val) => val.value == data.data,
              )[0];

              self.headersObj.subassy_id.options.filter.part_info = data.data;
              self.headersObj.art_no.update = val.article_number;
            } else {
              delete self.headersObj.subassy_id.options.filter.part_info;
              self.headersObj.art_no.update = null;
            }
            self.headersObj.subassy_id.data = null;
            self.headersObj.subassy_id.update = null;
            self.headers = App.updateObject(self.headers);
          },
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
            part_info: true,
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Article No",
          value: "art_no",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          clearable: true,
          disabled: false,
          visible: true,
          required: false,
          form: true,
          filter: true,
          groupable: false,
        },
        {
          text: "Notes",
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
        },
        {
          text: "Replacement",
          value: "replacement",
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
          text: "NPB No",
          value: "npb_id",
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
          filter: true,
          groupable: false,
          url: App.url + "transaction/npb",
          searchby: ["npb__no"],
          formatter: ["id", "npb__no"],
          options: {
            filter: {
              approved: 4,
            },
            filterType: {},
            filterCondition: {},
          },
          input: function (data) {
            var self = App.$get("Activity");
            if (data.data) {
              self.headersObj.item_replacement_id.options.filter = {
                npb_id: data.data,
              };
            } else delete self.headersObj.item_replacement_id.options.filter;
            //self.headersObj.item_replacement_id.data = null
            self.headersObj.item_replacement_id.update = null;
            self.headers = App.updateObject(self.headers);
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "npb_no",
        },
        {
          text: "Part Replacement",
          value: "item_replacement_id",
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
          filter: true,
          groupable: false,
          url: App.url + "transaction/npbitem",
          // "searchby": ['full'],
          formatter: ["item_id", "full"],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "manufacture_pn",
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
          // "input": function(data){
          //     var self = App.$get('npb_item')
          // 	if(Number(data.data) > self.maxQty){
          // 		self.headersObj.qty.data = self.maxQty
          //     	self.headers = App.updateObject(self.headers)
          // 	}
          // }
        },
        {
          text: "Is Match",
          value: "ismatch",
          readonly: false,
          align: "center",
          type: "switch",
          form: true,
          data_value: [1, 0],
          visible: false,
        },
        {
          text: "Photo",
          value: "attachment",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "file",
          disabled: false,
          visible: false,
          required: false,
          form: true,
          filter: false,
          groupable: false,
        },
        {
          text: "Photo",
          value: "photo",
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
      ],
      dialogItem: false,
      selected: false,
      dialogPIC: false,
      dataid: {},
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
    addPIC: function () {
      this.formPIC[0].data = 0;
      this.dialogPIC = true;
      this.$nextTick(() => {
        this.formPIC[0].data = null;
      });
    },
    savePIC: async function () {
      var self = this;
      try {
        var r = await axios.get(App.url + "rebuildmachine/pic", {
          params: {
            parent_id: self.selected.id,
            user_id: self.formPIC[0].data,
          },
        });
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
          self.dialogPIC = false;
        }
      } catch (err) {
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
        self.processData = {
          id: self.data.id,
          parent_id: self.data.parent_id,
        };
        self.dataid = {
          id: self.data.id,
          parent_id: self.data.parent_id,
        };
      }
    },
    typestatus: function (f) {
      if (f == 1) {
        return "Replacement";
      }
      if (f == 2) {
        return "Repair";
      }
      if (f == 3) {
        return "Installation";
      }
    },
    onSelectedRowAll: function (val) {
      var self = this;
      self.selectedAll = val;
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
          tmp.qty.form = true;
          tmp.qty.required = true;
          tmp.notes.form = true;
          tmp.notes.required = true;
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
          tmp.item_id.form = true;
          tmp.item_id.required = true;
          tmp.qty.form = true;
          tmp.qty.required = true;
          tmp.notes.form = true;
          tmp.notes.required = true;
        }, 100);
      });
    },
    onAddItem: function () {
      var self = this;
      self.$refs.sjItem.$refs.template.openAdd();
    },
    onAfterSave: function () {
      var self = this;
      setTimeout(() => {
        self.$refs.template.getItems();
        self.$refs.sjItem.$refs.template.getItems();
      }, 100);
    },
    onVisible: function (isVisible) {
      var self = this;
      if (isVisible) {
        self.itemsOptions.filter = {
          parent_id: self.data.parent_id,
        };
        self.$refs.template.defaultItemsOptions.filter = {
          parent_id: self.data.parent_id,
        };
        self.$refs.template.getItems();
      }
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
        formData.append("parent_id", self.data.parent_id);
        var res = await axios.post(
          App.url + "rebuildmachine/reportitemgroup",
          formData,
          {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          },
        );

        if (!res.data.status) App.errorMsg();
        else {
          App.successMsg();
          self.$refs.template.dialogAdd = false;
          self.$refs.template.getItems();
        }
      } catch (err) {
        self.overlay = false;
      }
    },
    onBeforeUpdate: function () {
      var self = this;
      if (self.selected.type.toString() == "1") {
        self.headersObj.npb_id.form = true;
        self.headersObj.npb_id.required = true;
        self.headersObj.item_replacement_id.form = true;
        self.headersObj.item_replacement_id.required = true;
        self.headersObj.qty.form = true;
        self.headersObj.qty.required = true;
      } else {
        self.headersObj.npb_id.form = false;
        self.headersObj.npb_id.required = false;
        self.headersObj.item_replacement_id.form = false;
        self.headersObj.item_replacement_id.required = false;
        self.headersObj.qty.form = false;
        self.headersObj.qty.required = false;
      }
      self.headersObj = App.updateObject(self.headersObj);
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
        formData.append("parent_id", self.data.parent_id);
        var res = await axios.post(
          App.url + "rebuildmachine/reportitemgroup",
          formData,
          {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          },
        );

        if (!res.data.status) App.errorMsg();
        else {
          App.successMsg();
          self.$refs.template.dialogUpdate = false;
          self.$refs.template.getItems();
        }
      } catch (error) {
        self.overlay = false;
      }
    },
    onOpenEdit: function () {
      var self = this;
      //console.log(self.selected.type, self.defaultForm)
      if (self.selected.type == 1) {
        self.headers.map(function (val) {
          val.form = false;
        });
        self.headersObj.npb_id.form = true;
        self.headersObj.npb_id.required = true;
        self.headersObj.item_replacement_id.form = true;
        self.headersObj.item_replacement_id.required = true;
        self.headersObj.qty.form = true;
        self.headersObj.qty.required = true;
      } else {
        //self.headers = JSONfn.parse(JSONfn.stringify(self.defaultForm))
        self.headersObj.npb_id.form = false;
        self.headersObj.npb_id.required = false;
        self.headersObj.item_replacement_id.form = false;
        self.headersObj.item_replacement_id.required = false;
        self.headersObj.qty.form = false;
        self.headersObj.qty.required = false;
      }
      setTimeout(() => {
        self.headersObj.attachment.data = null;
      }, 300);
    },
  },
  mounted: function () {},
};
</script>
