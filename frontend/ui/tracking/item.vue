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
      hide-edit-button
      hide-add-button
      :hide-delete-button="hideDeleteButton"
      @item-expanded="getChildren"
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
      <template v-slot:menu-after-filter>
        <v-btn
          small
          color="error"
          outlined
          @click="checkadmin"
          v-if="hideDeleteButton && adminMode"
          >Admin Mode</v-btn
        >
      </template>
      <template v-slot:expanded-item="props">
        <td :colspan="props.headers.length" style="height: auto">
          <v-card v-if="showBox" outlined style="padding: 8px">
            <b>Part Name:</b>{{ props.item.part_name }}<br />
            <b>Type:</b>{{ props.item.type }}<br />
            <b>Qty:</b>{{ props.item.qty }}<br />
            <b>UOM:</b>{{ props.item.uom }}<br />
            <b>Application:</b>{{ props.item.application }}
          </v-card>
          <template v-if="children.length > 0">
            <b>Part</b>
            <v-row dense style="flex: 0">
              <v-col cols="12" v-for="(item, index) in children" :key="index">
                <v-card outlined>
                  <v-list-item three-line>
                    <v-list-item-avatar tile size="80" color="grey">
                      <!-- <v-img max-height="150" max-width="150" :src="item.img"></v-img> -->
                      <v-img
                        max-height="150"
                        max-width="150"
                        alt="imgId"
                        :src="
                          'https://main.buanamultiteknik.com/api/barcode/item/image?id=' +
                          item.id
                        "
                      ></v-img>
                    </v-list-item-avatar>
                    <v-list-item-content>
                      Barcode: {{ item.barcode }}<br />
                      Latitude: {{ item.lt }}<br />
                      Longitude: {{ item.ln }}<br />
                      Group: {{ item.group_name }}
                    </v-list-item-content>
                    <v-list-item-content>
                      <b>Part Name:</b>{{ item.part_name }}<br />
                      <b>Type:</b>{{ item.type }}<br />
                      <b>Qty:</b>{{ item.qty }}<br />
                      <b>UOM:</b>{{ item.uom }}<br />
                      <b>Application:</b>{{ item.application }} <b>UOM:</b
                      >{{ item.uom }}<br />
                      <b>Group:</b> {{ item.group_name }}
                    </v-list-item-content>
                    <v-icon
                      style="position: absolute; top: 12px; right: 12px"
                      @click="remove('dismantle', item)"
                      >mdi-close</v-icon
                    >
                  </v-list-item>
                </v-card>
              </v-col>
            </v-row>
          </template>
        </td>
      </template>

      <!-- 
			<template v-slot:item.ll="props">
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
      <template v-slot:item.img="props">
        <v-list-item-avatar tile size="80" color="grey">
          <v-img
            max-height="150"
            max-width="150"
            alt="imgId"
            :src="
              'https://main.buanamultiteknik.com/api/barcode/item/image?id=' +
              props.item.id
            "
          ></v-img>
        </v-list-item-avatar>
      </template>
      <template v-slot:item.ll="props">
        <a
          :href="
            'https://www.google.com/maps/search/?api=1&query=' +
            props.item.lt +
            ',' +
            props.item.ln
          "
          >Latitude: {{ props.item.lt }}<br />
          Longitude: {{ props.item.ln }}
        </a>
      </template>
    </v-template>
  </v-container>
</template>

<style scoped></style>

<script>
module.exports = {
  name: "",
  creator: "",
  components: {
    /* 'document-form': 'url:ui/ewis/test/document_form.vue' */
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
    showBox: {
      type: Boolean,
      default: false,
    },
    showGroup: {
      type: Boolean,
      default: false,
    },
    showExpand: {
      type: Boolean,
      default: true,
    },
    hideDeleteButton: {
      type: Boolean,
      default: true,
    },
    adminMode: {
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
      },
    },
  },
  data: function () {
    return {
      name: "item",
      itemKey: "id",
      url: "barcode/item",
      loading: false,
      headers: [
        {
          text: "ID",
          value: "id",
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
          text: "Barcode",
          value: "barcode",
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
          text: "Lat-Long",
          value: "ll",
          type: "varchar",
          visible: true,
          form: false,
        },
        {
          text: "Image",
          value: "img",
          type: "varchar",
          visible: true,
          form: false,
        },
        {
          text: "Group",
          value: "group_name",
          type: "list",
          visible: true,
          filter: true,
          form: false,
          data_value: ["mech", "elect"],
        },
        /* , {
                    					"text": "Longitude",
                    					"value": "ln",
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
                    					"form": true,
                    					"filter": true,
                    					"groupable": false,
                    					"url": "",
                    					"searchby": [],
                    					"formatter": [],
                    					"options": {
                    						"filter": {},
                    						"filterType": {},
                    						"filterCondition": {}
                    					},
                    					"paging": true,
                    					"page": "1",
                    					"limit": "10"
                    				}, {
                    					"text": "Latitude",
                    					"value": "lt",
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
                    					"form": true,
                    					"filter": true,
                    					"groupable": false,
                    					"url": "",
                    					"searchby": [],
                    					"formatter": [],
                    					"options": {
                    						"filter": {},
                    						"filterType": {},
                    						"filterCondition": {}
                    					},
                    					"paging": true,
                    					"page": "1",
                    					"limit": "10"
                    				} */
        {
          text: "Type",
          value: "process",
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
        },
        {
          text: "Box",
          value: "box",
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
          text: "",
          value: "data-table-expand",
        },
      ],
      //row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
      selected: false,
      //berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
      selectedAll: [],
      children: [],
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
  },
  methods: {
    getChildren: async function (item) {
      var self = this;
      self.children = [];
      console.log(item);
      var res = await axios.get(App.url + "barcode/item", {
        params: {
          filter: {
            parent_id: item.item.id,
          },
          limit: -1,
        },
      });
      self.children = res.data.data;
    },
    onSelectedRow: function (val) {
      var self = this;
      if (val === undefined) {
        self.selected = false;
      } else {
        self.selected = val;
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
    checkadmin: function () {
      var c = prompt("Password");
      if ("-402137413" == hashCode(c)) {
        this.hideDeleteButton = false;
      } else {
        this.hideDeleteButton = true;
        alert("Wrong Password!");
      }
    },
  },
  mounted: function () {
    var self = this;
    //console.log(self.headersObj.group, self.headersObj.box)
    self.headersObj.group_name.visible = self.showGroup;
    self.headersObj.box.visible = self.showBox;
    /* 
            if(!self.showBox){
            	self.headersObj.barcode.text = 
            } */
  },
};
</script>
