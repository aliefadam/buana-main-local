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
      @open-add="onOpenAdd"
      @close-add="onCloseAdd"
      ref="template"
      :item-key="itemKey"
      :url="url"
      :headers="headers"
      :name="name"
      @update:selected-row="onSelectedRow"
      @update:selected-row-all="onSelectedRowAll"
      :table-only="tableOnly"
      @save="save"
      @update="update"
    >
      <template v-slot:menu-after-filter>
        <v-btn
          color="primary"
          outlined
          small
          @click="dialogNPB = true"
          v-if="!isDashboard"
        >
          <v-icon small left>mdi-plus</v-icon>Add From NPB
        </v-btn>
        <v-btn
          color="primary"
          outlined
          small
          @click="onAddItem"
          v-if="!isDashboard"
        >
          <v-icon small left>mdi-plus</v-icon>Add From Master
        </v-btn>
        <v-btn
          color="primary"
          outlined
          small
          @click="dialogManual = true"
          v-if="!isDashboard"
        >
          <v-icon small left>mdi-plus</v-icon>Add Manual
        </v-btn>
        <v-btn
          color="primary"
          outlined
          :disabled="!selected"
          small
          @click="dialogItem = true"
        >
          Show raw data
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
      <template v-slot:item.reject_detail="props">
        <v-btn
          color="error"
          outlined
          small
          @click="openRejectForm(props.item)"
          :disabled="!allowRejectInfo(props.item)"
          v-if="isDashboard"
        >
          Reject </v-btn
        ><br /><br />
        <b>Status:</b> {{ status(props.item.reject) }}<br /><br />
        <span>Notes:</span> {{ props.item.reject_notes }}
      </template>
      <template v-slot:item.item_detail="props">
        <b>BMT ID No: </b>{{ props.item.item_no }}<br />
        <b>ITEM NAME: </b>{{ props.item.item_name }}<br />
        <b>UNIT: </b>{{ props.item.unit }}<br />
        <b>ORIGINAL MANUFACTURE: </b>{{ props.item.original_manufacture }}<br />
        <b>MANUFACTURE PN: </b>{{ props.item.manufacture_pn }}<br />
        <b>SPECIFICATION: </b>{{ props.item.specification }}
      </template>

      <template v-slot:item.photo="props">
        <a
          v-if="props.item.attachment != null"
          target="_blank"
          :href="
            'https://main.buanamultiteknik.com/api/uploads/sj' +
            props.item.sj_id +
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
              'https://main.buanamultiteknik.com/api/uploads/sj' +
              props.item.sj_id +
              '/' +
              props.item.attachment.slice(0).split('+++')[0]
            "
          />
        </a>
      </template>
    </v-template>
    <v-action-dialog
      v-model="dialogNPB"
      title="Import From NPB"
      min-height="75%"
      @save="addNPB"
    >
      <v-autoform v-model="formNPB"></v-autoform>
    </v-action-dialog>
    <v-action-dialog
      v-model="dialogManual"
      title="Item Manual"
      min-height="75%"
      @save="addNPB"
    >
      <v-autoform v-model="formManual"></v-autoform>
    </v-action-dialog>
    <sj-item
      style="display: none"
      @after-save="onAfterSave"
      :sel="sel"
      :data="data"
      ref="sjItem"
    ></sj-item>
    <v-action-dialog
      :actions="false"
      v-model="dialogItem"
      title="Surat Jalan Item"
      min-height="75%"
      fullscreen
    >
      <sj-item
        :table-only="isDashboard"
        :key="selected.item_id + '_' + data.sj_id"
        :sel="processData"
        name=""
        :data="dataid"
      ></sj-item>
    </v-action-dialog>
    <v-action-dialog
      @save="saveReject"
      title="Reject Info"
      v-model="dialogReject"
    >
      <v-autoform
        v-model="formReject"
        :valid="valid"
        :index="selected.id"
      ></v-autoform>
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
    showRejectButton: {
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
    "sj-item": "url:ui/suratjalan/surat_jalan_item.vue",
  },
  data: function () {
    return {
      valid: false,
      url: "suratjalan/suratjalanitemgroup",
      formReject: [
        {
          text: "Reject Notes",
          value: "reject_notes",
          align: "start",
          type: "text",
        },
      ],
      name: "itemgroup",
      processData: {},
      itemKey: "item_no",
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
          text: "sj_id",
          value: "sj_id",
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
          text: "BMT Item Detail",
          value: "item_detail",
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
          form: true,
          filter: true,
          groupable: false,
          alias: "item_no",
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
          type: "text",
          disabled: false,
          visible: false,
          required: false,
          form: true,
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
          required: false,
          form: false,
          filter: true,
          groupable: false,
        },
        {
          text: "Short Description",
          value: "short_desc",
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
          filter: true,
          groupable: false,
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
          type: "trxt",
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: true,
          groupable: false,
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
        {
          text: "Reject Detail",
          value: "reject_detail",
          align: "center",
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
          text: "Reject Notes",
          value: "reject_notes",
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
      ],
      formManual: [
        {
          text: "Item",
          value: "item_id",
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
          required: true,
          form: false,
          filter: true,
          data: null,
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
          visible: false,
          required: true,
          form: true,
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
          text: "Short Description",
          value: "short_desc",
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
          filter: true,
          groupable: false,
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
          type: "int",
          disabled: false,
          visible: true,
          form: true,
          filter: true,
          groupable: false,
          required: true,
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
          form: true,
          filter: true,
          groupable: false,
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
      ],
      formNPB: [
        {
          text: "NPB",
          value: "npb_id",
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
          url: App.url + "transaction/npb",
          searchby: ["npb__no"],
          formatter: ["id", ["npb__no", "notes"]],
          options: {
            filter: {
              approved_date: null,
              approved3_date: null,
            },
            filterType: {
              approved_date: "notnull",
              approved3_date: "notnull",
            },
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          input: function (data) {
            App.$get("itemgroup").formNPB[1].form = true;
            App.$get("itemgroup").formNPB[1].options.filter = {
              npb_id: data.data,
            };

            App.$get("itemgroup").formNPB[1].data = null;
            App.$get("itemgroup").formNPB[1].update = null;
            App.$get("itemgroup").formNPB = App.updateObject(
              App.$get("itemgroup").formNPB,
            );
          },
        },
        {
          text: "Item",
          value: "npb_item_id",
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
          url: App.url + "transaction/npbitem",
          searchby: ["full"],
          formatter: function (val) {
            return {
              text: val.full,
              value: val.id,
              specification: val.specification,
              qty: val.qty,
              notes: val.notes,
            };
          },
          input: function (data) {
            var self = App.$get("itemgroup");
            if (data.data) {
              var val = data.data_value.filter(
                (val) => val.value == data.data,
              )[0];
              self.formNPB[2].form = true;
              self.formNPB[3].form = true;
              self.formNPB[4].form = true;
              self.formNPB[5].form = true;
              self.formNPB[6].form = true;
              self.formNPB[2].data = Number(val.qty);
              self.formNPB[3].update = val.specification;
              self.formNPB[4].data = val.notes;
            } else {
              self.formNPB[2].data = null;
              self.formNPB[2].update = null;
              self.formNPB[3].data = null;
              self.formNPB[3].update = null;
              self.formNPB[4].data = null;
              self.formNPB[4].update = null;
            }
            self.formNPB = App.updateObject(self.formNPB);
          },
          options: {
            filter: {
              flag: 1,
            },
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
          type: "int",
          disabled: false,
          visible: true,
          form: false,
          filter: true,
          groupable: false,
          readonly: true,
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
          type: "text",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: true,
          groupable: false,
          readonly: true,
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
          form: false,
          filter: true,
          groupable: false,
        },
        {
          text: "Short Description",
          value: "short_desc",
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
          form: false,
          filter: true,
          groupable: false,
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
          form: false,
          filter: false,
          groupable: false,
        },
      ],
      dialogNPB: false,
      dialogManual: false,
      dialogItem: false,
      dialogReject: false,
      selected: false,
      rejectFormSelected: false,
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
    formRejectObj: function () {
      var tmp = {},
        self = this;
      Object.keys(self.formReject).map((key) => {
        var val = self.formReject[key];
        tmp[val.value] = val;
      });
      return tmp;
    },
  },
  methods: {
    allowRejectInfo: function (item) {
      var self = this;
      if (item == undefined) return false;
      if (item.reject == 1) return false;
      return true;
    },
    addNPB: async function () {
      var self = this;
      try {
        const formData = new FormData();
        formData.append("npb_item_id", self.formNPB[1].data);
        formData.append("notes", self.formNPB[4].data);
        formData.append("short_desc", self.formNPB[5].data);
        formData.append("attachment", self.formNPB[6].data);
        formData.append("sj_id", self.sel.sj_id);

        var res = await axios.post(App.url + self.url + "/addnpb", formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        });

        // var res = await axios.post(App.url+self.url+"/addnpb",{
        //     npb_item_id: self.formNPB[1].data,
        //     notes: self.formNPB[4].data,
        //     short_desc: self.formNPB[5].data,
        //     sj_id: self.sel.sj_id,
        //     attachment: self.formNPB[6].data,

        // })

        if (!res.data.status) throw res.data;
        App.successMsg();
        self.$refs.template.getItems();
        self.$refs.getAttachment();
        self.dialogNPB = false;
      } catch (e) {
        App.errorMsg(e);
      }
    },
    // addManual: async function(){
    // 	var self = this
    //     try{
    //         const formData = new FormData();
    //         formData.append('item_name',self.formManual[1].data);
    //         formData.append('unit',self.formManual[2].data);
    //         formData.append('short_desc',self.formManual[3].data);
    //         formData.append('qty',self.formManual[4].data);
    //         formData.append('notes',self.formManual[5].data);
    //         formData.append('attachment',self.formManual[6].data);
    //         formData.append("sj_id", self.sel.sj_id);

    //         var res = await axios.post(App.url+self.url+"/addmanual", formData, {
    //             headers: {
    //             "Content-Type": "multipart/form-data",
    //         },

    //         });

    //         if(!res.data.status) throw res.data
    //         App.successMsg()
    //         self.$refs.template.getItems()
    //         self.$refs.getAttachment();
    //         self.dialogNPB = false
    //     }
    //     catch(e){
    //         App.errorMsg(e)
    //     }
    // },
    openRejectForm: function (item) {
      var self = this;
      self.rejectFormSelected = item;
      self.dialogReject = true;
    },
    status: function (f) {
      if (f == 1) {
        return "rejected";
      } else return "";
    },
    onSelectedRow: function (val) {
      var self = this;
      if (val === undefined) {
        self.selected = false;
        self.processData = {};
        self.dataid = {};
      } else {
        self.selected = val;
        self.formRejectObj.reject_notes.data = val.reject_notes;
        self.processData = {
          id: self.data.id,
          sj_id: self.data.sj_id,
          item_id: val.item_id,
          reject_notes: self.formRejectObj.reject_notes.data,
        };
        self.dataid = {
          id: self.data.id,
          sj_id: self.data.sj_id,
          item_id: val.item_id,
          reject_notes: self.formRejectObj.reject_notes.data,
        };
      }
    },
    onSelectedRowAll: function (val) {
      var self = this;
      self.selectedAll = val;
    },
    saveReject: async function () {
      var self = this,
        sel = self.rejectFormSelected;
      const formData = {};
      self.formReject.map(function (val, i) {
        var key = val.value;
        formData[key] = val.data;
      });
      formData["reject"] = 1;
      formData["pk"] = sel.id;
      var c = confirm("Are you sure?");
      if (!c) return true;
      if (c) {
        var r = await axios.put(
          App.url + "suratjalan/suratjalanitemgroup",
          formData,
        );
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      }
      self.dialogReject = false;
    },
    onOpenAdd: function () {
      var self = this;
      self.$nextTick(() => {
        setTimeout(() => {
          var tmp = {};
          self.$refs.template.formModelAdd.map((val) => {
            tmp[val.value] = val;
          });
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
          sj_id: self.data.sj_id,
        };
        self.$refs.template.defaultItemsOptions.filter = {
          sj_id: self.data.sj_id,
        };
        self.$refs.template.getItems();
      }
    },
    save: async function (params) {
      var self = this;
      self.overlay = true;
      try {
        const formData = new FormData();

        Object.keys(params).forEach(function (key) {
          var val = params[key];
          formData.append(key, val);
        });

        var res = await axios.post(
          App.url + "suratjalan/suratjalanitemgroup",
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
        formData.append("sj_id", self.selected.sj_id);
        var res = await axios.post(
          App.url + "suratjalan/suratjalanitemgroup",
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
  },
  mounted: function () {},
};
</script>
