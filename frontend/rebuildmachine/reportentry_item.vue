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
    >
      <template v-slot:item.time="props">
        <span>Start Time: </span>{{ props.item.start_time }}<br />
        <span>End Time: </span>{{ props.item.end_time }}
      </template>
      <template v-slot:item.replacement="props">
        <span>NPB No: </span>{{ props.item.npb_id }}<br />
        <span>BMT ID No: </span>{{ props.item.item_replacement_id }}
      </template>
      <template v-slot:item.photo="props">
        <span>
          <!-- <a target="_blank" :href="'https://main.buanamultiteknik.com/api/uploads/rm' + props.item.parent_id+  '/' + props.item.attachment.slice(1).split('+++')[0]"
            >printed -->
          <!-- <img style="border: 1px solid #ddd; border-radius: 4px; padding: 5px; height: 150px;" :src="'https://main.buanamultiteknik.com/api/uploads/rm'+props.item.parent_id+'/'+props.item.attachment.trim().split('+++')[0]" /> -->
          <!-- </a> -->
        </span></template
      >
    </v-template>
  </v-container>
</template>

<style scoped></style>

<script>
module.exports = {
  name: "pcitem",
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
      url: "rebuildmachine/reportitem",
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
          text: "Time",
          value: "time",
          align: "start",
          sortable: false,
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
          data_value: [],
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
          visible: true,
          required: false,
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
            } else delete self.headersObj.partno_id.options.filter;
            delete self.headersObj.subassy_id.option.filter;

            self.headersObj.partno_id.update = null;
            self.headersObj.subassy_id.update = null;
          },
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
          disabled: false,
          visible: true,
          required: false,
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "maintenance/subassembly",
          searchby: ["part_info"],
          formatter: ["part_info", "part_info"],
          input: function (data) {
            var self = App.$get("Activity");
            if (data.data)
              self.headersObj.subassy_id.options.filter.part_info = data.data;
            else delete self.headersObj.subassy_id.options.filter.part_info;
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
          text: "Subassembly",
          value: "subassy_id",
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
          text: "Article No",
          value: "",
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
          type: "varchar",
          disabled: false,
          visible: true,
          required: false,
          form: true,
          filter: true,
          groupable: false,
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
          type: "varchar",
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
          type: "text",
          disabled: false,
          visible: true,
          required: false,
          form: true,
          filter: true,
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
          sj_id: self.data.sj_id,
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
          tmp.notes.form = true;
          tmp.notes.required = true;
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
          tmp.item_id.form = true;
          tmp.item_id.required = true;
          tmp.qty.form = true;
          tmp.qty.required = true;
          tmp.notes.form = true;
          tmp.notes.required = true;
        }, 100);
      });
    },
    onAfterSave: function () {
      this.$emit("after-save");
    },
  },
  mounted: function () {},
};
</script>
