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
      hide-add-button
      @after-save="onAfterSave"
      @open-add="onOpenAdd"
      @close-add="onCloseAdd"
      :data="data"
      :items-options="itemsOptions"
      v-model="value"
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

      <template v-slot:item.item_detail="props">
        <b>BMT ID No: </b>{{ props.item.item_no }}<br />
        <b>ITEM NAME: </b>{{ props.item.item_name }}<br />
        <b>UNIT: </b>{{ props.item.unit }}<br />
        <b>ORIGINAL MANUFACTURE: </b>{{ props.item.original_manufacture }}<br />
        <b>MANUFACTURE PN: </b>{{ props.item.manufacture_pn }}<br />
        <b>SPECIFICATION: </b>{{ props.item.specification }}
      </template>
    </v-template>
  </v-container>
</template>

<style scoped></style>

<script>
module.exports = {
  name: "Items",
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
      default: false,
    },
    itemsOptions: {
      type: Object,
      default: {
        filter: {},
        filterType: {},
      },
    },
  },
  components: {},
  data: function () {
    return {
      name: "",
      processData: {},
      itemKey: "id",
      url: "suratjalan/suratjalanitem",
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
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: true,
          groupable: false,
          alias: "item_no",
          url: App.url + "bom/item",
          searchby: ["full"],
          formatter: function (val) {
            return {
              text: val.full,
              value: val.id,
              specification: val.specification,
            };
          },
          input: function (data) {
            var self = App.$get("Items");
            if (data.data) {
              var val = data.data_value.filter(
                (val) => val.value == data.data,
              )[0];

              self.headersObj.specification.update = val.specification;
            } else {
              self.headersObj.specification.update = null;
            }
            self.headers = App.updateObject(self.headers);
          },
          options: {
            filter: {
              is_active: 1,
            },
            filterType: {},
            filterCondition: {},
            full: true,
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
          readonly: true,
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
          form: true,
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
  },
  methods: {
    onSelectedRow: function (val) {
      var self = this;
      if (val === undefined) {
        self.selected = false;
        self.processData = {};
        self.dataid = {};
      } else {
        self.selected = val;
        self.processData = {
          sj_id: self.data.id,
        };
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
        }, 100);
      });
    },
    onCloseAdd: function () {
      var self = this;
      self.$nextTick(() => {
        self.$emit("close-add");
        setTimeout(() => {
          var tmp = {};
          self.$refs.template.formModelAdd.map((val) => {
            tmp[val.value] = val;
          });
        }, 100);
      });
    },
    onAfterSave: function () {
      this.$emit("after-save");
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
          App.url + "suratjalan/suratjalanitem",
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
          App.url + "suratjalan/suratjalanitem",
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
