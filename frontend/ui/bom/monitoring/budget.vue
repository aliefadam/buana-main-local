<template>
  <v-container
    v-if="ready"
    v-observe-visibility="onVisible"
    class="no-padding-margin"
    style="
      min-height: 100%;
      padding: 0 !important;
      height: auto;
      display: flex;
      flex-direction: column;
      margin: 0;
      width: 100%;
      max-width: 100%;
      background-color: #fff;
    "
  >
    <template v-if="allowView == false">
      <v-alert style="margin: 8px" dense outlined type="error">
        You are not allowed to view this RFQ!
      </v-alert>
    </template>
    <template v-else>
      <v-card outlined style="margin: 8px; background-color: #2b688c">
        <v-list-item two-line>
          <v-list-item-content>
            <div class="text-overline font-weight-bold text-white mb-4">
              {{ data.title }}
            </div>
            <v-list-item-subtitle>
              <v-row no-gutters dense>
                <v-col
                  cols="6"
                  md="3"
                  lg="1"
                  class="text-subtitle font-weight-bold text-white"
                  >Ragic No</v-col
                >
                <v-col
                  cols="6"
                  md="3"
                  lg="2"
                  class="text-subtitle font-weight-bold text-white"
                  >: {{ data.reference_no ?? "-" }}</v-col
                >
                <v-col
                  cols="6"
                  md="3"
                  lg="1"
                  class="text-subtitle font-weight-bold text-white"
                  >Assigned to</v-col
                >
                <v-col
                  cols="6"
                  md="3"
                  lg="2"
                  class="text-subtitle font-weight-bold text-white"
                  >: {{ data.assigned_to_name ?? "-" }}</v-col
                >
                <v-col
                  cols="6"
                  md="3"
                  lg="1"
                  class="text-subtitle font-weight-bold text-white"
                  >Created By</v-col
                >
                <v-col
                  cols="6"
                  md="3"
                  lg="2"
                  class="text-subtitle font-weight-bold text-white"
                  >: {{ data.created_by_name ?? "-" }}</v-col
                >
                <v-col cols="0" md="0" lg="3"></v-col>
                <v-col
                  cols="6"
                  md="3"
                  lg="1"
                  class="text-subtitle font-weight-bold text-white"
                ></v-col>
                <v-col
                  cols="6"
                  md="3"
                  lg="2"
                  class="text-subtitle font-weight-bold text-white"
                ></v-col>
                <v-col
                  cols="6"
                  md="3"
                  lg="1"
                  class="text-subtitle font-weight-bold text-white"
                  >Status</v-col
                >
                <v-col
                  cols="6"
                  md="3"
                  lg="2"
                  class="text-subtitle font-weight-bold text-white"
                  >: {{ data.status ?? "-" }}</v-col
                >
                <v-col
                  cols="6"
                  md="3"
                  lg="1"
                  class="text-subtitle font-weight-bold text-white"
                  >Created date</v-col
                >
                <v-col
                  cols="6"
                  md="3"
                  lg="2"
                  class="text-subtitle font-weight-bold text-white"
                  >:
                  {{
                    data.created_date ? datetimeFormat(data.created_date) : "-"
                  }}</v-col
                >
                <v-col cols="0" md="0" lg="3"></v-col>
                <v-col
                  cols="6"
                  md="3"
                  lg="1"
                  class="text-subtitle font-weight-bold text-white"
                  >Project No</v-col
                >
                <v-col
                  cols="6"
                  md="3"
                  lg="2"
                  class="text-subtitle font-weight-bold text-white"
                  >: {{ data.project_no ?? "-" }}</v-col
                >
                <v-col
                  cols="6"
                  md="3"
                  lg="1"
                  class="text-subtitle font-weight-bold text-white"
                  >Priority</v-col
                >
                <v-col
                  cols="6"
                  md="3"
                  lg="2"
                  class="text-subtitle font-weight-bold text-white"
                  >: {{ data.priority ?? "-" }}</v-col
                >
                <v-col
                  cols="6"
                  md="3"
                  lg="1"
                  class="text-subtitle font-weight-bold text-white"
                  >Updated by</v-col
                >
                <v-col
                  cols="6"
                  md="3"
                  lg="2"
                  class="text-subtitle font-weight-bold text-white"
                  >: {{ updatedBy }}</v-col
                >
                <v-col cols="0" md="0" lg="3"></v-col>
                <v-col
                  cols="6"
                  md="3"
                  lg="1"
                  class="text-subtitle font-weight-bold text-white"
                ></v-col>
                <v-col
                  cols="6"
                  md="3"
                  lg="2"
                  class="text-subtitle font-weight-bold text-white"
                ></v-col>
                <v-col
                  cols="6"
                  md="3"
                  lg="1"
                  class="text-subtitle font-weight-bold text-white"
                  >Group</v-col
                >
                <v-col
                  cols="6"
                  md="3"
                  lg="2"
                  class="text-subtitle font-weight-bold text-white"
                  >: {{ data.rfq_group ?? "-" }}</v-col
                >
                <v-col
                  cols="6"
                  md="3"
                  lg="1"
                  class="text-subtitle font-weight-bold text-white"
                  >Last updated</v-col
                >
                <v-col
                  cols="6"
                  md="3"
                  lg="2"
                  class="text-subtitle font-weight-bold text-white"
                  >: {{ datetimeFormat(lastUpdated) }}</v-col
                >
              </v-row>
            </v-list-item-subtitle>
          </v-list-item-content>
        </v-list-item>
      </v-card>
      <v-card flat style="margin: 8px; margin-top: 0">
        <v-list-item style="padding: 0">
          <v-list-item-content style="">
            <h2
              id=""
              style="
                display: flex;
                align-items: center;
                margin: 0 8px;
                color: #2b688c;
                font-weight: bold;
              "
            >
              ITEM LIST
              <div
                style="
                  flex: 1;
                  background: #2b688c;
                  height: 1px;
                  margin-left: 8px;
                "
              ></div>
            </h2>
          </v-list-item-content>
        </v-list-item>
        <!-- Tailwind CSS Table -->
        <table
          class="min-w-full table-auto border-collapse border border-gray-300 mt-4"
        >
          <thead>
            <tr class="bg-blue-100">
              <th
                class="px-4 py-2 text-left text-sm font-semibold text-gray-700"
              >
                Name
              </th>
              <th
                class="px-4 py-2 text-left text-sm font-semibold text-gray-700"
              >
                Type
              </th>
              <th
                class="px-4 py-2 text-left text-sm font-semibold text-gray-700"
              >
                Description
              </th>
              <th
                class="px-4 py-2 text-left text-sm font-semibold text-gray-700"
              >
                Datasheet
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(item, index) in list"
              :key="index"
              class="border-b border-gray-200"
            >
              <td class="px-4 py-2 text-sm text-gray-700">{{ item.name }}</td>
              <td class="px-4 py-2 text-sm text-gray-700">{{ item.type }}</td>
              <td class="px-4 py-2 text-sm text-gray-700">
                {{ item.description }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-700">
                <a
                  :href="
                    'https://main.buanamultiteknik.com/api/uploads/' +
                    item.datasheet
                  "
                  target="_blank"
                  v-if="item.datasheet.trim() != ''"
                  class="text-blue-600 hover:text-blue-800"
                  >Datasheet</a
                >
              </td>
            </tr>
          </tbody>
        </table>
      </v-card>
      <br />
      <h2
        id=""
        style="
          display: flex;
          align-items: center;
          margin: 0 8px;
          color: #2b688c;
          font-weight: bold;
        "
      >
        ATTACHMENTS
        <div
          style="flex: 1; background: #2b688c; height: 1px; margin-left: 8px"
        ></div>
      </h2>
      <v-row no-gutters style="margin: 8px; flex: 0" class="px-2">
        <template v-for="(item, index) in attachments">
          <v-col
            cols="12"
            class="no-icon-href mb-2 px-2 py-2"
            style="
              border-radius: 4px;
              align-items: center;
              overflow: hidden;
              display: flex;
              background-color: #eeeeee;
            "
            flat
            :key="index"
            round
          >
            <div
              class="mr-2"
              :style="'display: flex; flex: 1;' + attachmentStyle"
            >
              <a
                class="mr-2"
                style="overflow: hidden"
                :href="
                  'https://main.buanamultiteknik.com/api/uploads/rfq' +
                  hash[2] +
                  '/' +
                  item.file
                "
                target="_blank"
                >{{ item.name }}</a
              >
              <div
                class="clamp"
                style="cursor: pointer; flex: 1; width: 100%"
                @click="
                  itemName = item.name;
                  itemDescription = item.description;
                  dialogAttachmentDesc = true;
                "
              >
                {{ item.description }}
              </div>
            </div>
            <v-icon
              @click.stop="deleteFile(item.id)"
              color="error"
              v-if="!disableEdit"
              >mdi-delete</v-icon
            >
          </v-col>
        </template>
      </v-row>
      <br />
      <h2
        id="history"
        style="
          display: flex;
          align-items: center;
          margin: 0 8px;
          color: #2b688c;
          font-weight: bold;
        "
      >
        TRACKING
        <div
          style="flex: 1; background: #2b688c; height: 2px; margin-left: 8px"
        ></div>
      </h2>
      <template v-for="(item, index) in items" :key="item.id">
        <v-card
          outlined
          style="
            margin: 8px;
            background-color: rgb(254, 254, 254);
            border: 1px solid #2b688c;
          "
        >
          <v-list-item two-line>
            <v-list-item-content>
              <h2 class="mb-4">
                <b>{{ item.created_by_name }}</b>
                {{ new Date(item.created_date).formatDate("DD/MM/YYYY") }}
                <v-icon
                  @click.stop="
                    deleteComment(
                      item.id,
                      (item.filepath || '').split('+++')[0],
                    )
                  "
                  color="error"
                  v-if="!disableEdit"
                  >mdi-delete</v-icon
                >
              </h2>
              <v-list-item-subtitle
                v-if="check('assigned_to_name', item, index)"
                ><b>Assigned to</b> changed to
                <b>{{ item.hist.assigned_to_name }}</b></v-list-item-subtitle
              >
              <v-list-item-subtitle v-if="check('status', item, index)"
                ><b>Status</b> changed to
                <b>{{ item.hist.status }}</b></v-list-item-subtitle
              >
              <v-list-item-subtitle
                v-html="item.comment"
              ></v-list-item-subtitle>
              <v-list-item-subtitle v-if="item.filepath"
                ><b>Attachment</b>
                <a
                  class="mr-2"
                  style="overflow: hidden"
                  :download="(item.filepath || '').split('+++')[1]"
                  :href="
                    'https://main.buanamultiteknik.com/api/uploads/rfq' +
                    hash[2] +
                    '/' +
                    (item.filepath || '').split('+++')[0]
                  "
                  target="_blank"
                  >{{ (item.filepath || "").split("+++")[1] }}</a
                >
              </v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
        </v-card>
      </template>
      <!-- Comment Section (Add Tailwind for Buttons) -->
      <v-card flat v-if="!disableEdit && showComment == true">
        <v-card-actions style="flex: 0; align-items: end" class="comment">
          <v-autoform
            v-model="formFile2"
            :valid="valid"
            style="width: 100%"
          ></v-autoform>
        </v-card-actions>
        <v-card-actions style="flex: 0; align-items: end">
          <vue-editor-mobile
            v-model="comment"
            :height="200"
            style="flex: 1; z-index: inherit"
          ></vue-editor-mobile>
        </v-card-actions>
        <v-card-actions style="flex: 0; align-items: end; padding-top: 0">
          <v-spacer></v-spacer>
          <v-btn small color="primary" outlined @click="saveComment">
            <v-icon left>mdi-content-save</v-icon>Save
          </v-btn>
        </v-card-actions>
      </v-card>
    </template>
    <v-action-dialog
      :actions="false"
      :title="selectedList.name"
      v-model="listDetailDialog"
    >
      <v-card flat>
        <v-card-text>
          <div v-html="selectedList.description"></div>
          <a
            :href="
              'https://main.buanamultiteknik.com/api/uploads/' +
              selectedList.datasheet
            "
            target="_blank"
            >{{ selectedList.datasheet }}</a
          >
        </v-card-text>
      </v-card>
    </v-action-dialog>
    <v-dialog
      v-model="dialogAttachmentDesc"
      max-width="500px"
      transition="dialog-transition"
    >
      <v-card flat style="background-color: #eeeeee">
        <v-card-title>
          {{ itemName }}
        </v-card-title>
        <v-card-text>
          {{ itemDescription }}
        </v-card-text>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<style scoped>
.fab-header .v-speed-dial__list {
  width: auto;
  right: 38px;
}

.vue-editor-mobile > .toolbar > ul > li .icon {
  font-size: 16px;
}

.vue-editor-mobile .toolbar {
  z-index: inherit !important;
}

.no-icon-href::after {
  content: "" !important;
}

.comment .v-autoform > div > div {
  max-width: auto !important;
}
</style>
<script
  type="module"
  src="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css"
></script>
<script>
module.exports = {
  name: "",
  data: function () {
    return {
      name: "",
      comment: "",
      itemName: "",
      itemDescription: "",
      hash: location.hash.split("/").slice(1),
      formFile: [
        {
          text: "File",
          value: "file",
          type: "file",
          required: true,
        },
        {
          text: "Description",
          value: "description",
          type: "text",
          required: false,
        },
      ],
      formFile2: [
        {
          text: "File",
          value: "file",
          type: "file",
          required: false,
          xs: 12,
          width: "100%",
        },
      ],
      fab: false,
      showComment: false,
      dialogAttachmentDesc: false,
      allowView: true,
      ready: false,
      valid: false,
      isInDom: false,
      dialogFile: false,
      isInViewport: false,
      listDetailDialog: false,
      selectedList: false,
      data: {},
      list: [],
      attachments: [],
      items: [],
    };
  },
  watch: {},
  computed: {
    disableEdit: function () {
      var self = this;
      if (Object.values(self.data).length == 0) return true;
      if (self.data.status.toLowerCase() == "close") return true;
      return false;
    },
    attachmentStyle: function () {
      switch (App.$vuetify.breakpoint.name) {
        case "xs":
          return "align-items: start; flex-direction: column;";
        case "sm":
          return "align-items: start; flex-direction: column;";
        default:
          return "align-items: center; flex-direction: row;";
      }
    },
    lastUpdated: function () {
      var self = this;
      var updateRfq = self.data.modified_date,
        updateComment =
          self.items.length > 0 ? self.items[0].created_date : null;
      if (updateComment == null && updateRfq == null) return "-";
      if (updateComment == null) return updateRfq;
      if (new Date(updateComment).getTime() > new Date(updateRfq).getTime())
        return updateComment;
      else return updateRfq;
    },
    updatedBy: function () {
      var self = this;
      var updateRfq = self.data.modified_date,
        updateComment =
          self.items.length > 0 ? self.items[0].created_date : null;
      if (updateComment == null && updateRfq == null) return "-";
      if (updateComment == null) return self.data.modified_by_name;
      if (new Date(updateComment).getTime() > new Date(updateRfq).getTime())
        return self.items[0].created_by_name;
      else return self.data.modified_by_name;
    },
  },
  methods: {
    scrollToTop: async function () {
      document.querySelector(".layout.login-layout").scrollTo(0, 0);
    },
    openComment: function () {
      var self = this;
      self.showComment = true;
      self.$nextTick(() => {
        setTimeout(() => {
          document
            .querySelector(".layout.login-layout")
            .scrollTo(
              0,
              document.querySelector(".layout.login-layout").scrollHeight,
            );
        }, 100);
      });
    },
    openFile: function (item) {
      window.open(
        "https://main.buanamultiteknik.com/api/uploads/rfq" +
          this.hash[2] +
          "/" +
          item.file,
      );
    },
    deleteFile: async function (id) {
      var self = this;
      var c = confirm("Are you sure you want to delete this item?");
      var res = await axios.delete(App.url + "rfq/attachment", {
        params: {
          id: id,
        },
      });
      if (!res.data.status) App.errorMsg();
      else {
        App.successMsg();
        self.getAttachment();
      }
      return false;
    },
    deleteComment: async function (id, path) {
      var self = this;
      var c = confirm("Are you sure you want to delete this comment?");
      if (!c) return true;
      if (path.trim() != "") path = "./uploads/rfq" + self.hash[2] + "/" + path;
      var res = await axios.delete(App.url + "rfq/rfqcomment", {
        params: {
          id: id,
          path: path,
        },
      });
      if (!res.data.status) App.errorMsg();
      else {
        App.successMsg();
        self.getComment();
      }
      return false;
    },
    saveFile: async function () {
      var self = this;
      const formData = new FormData();
      //formData.append('file', files);
      self.formFile.map(function (val, i) {
        var key = val.value;
        formData.append(key, val.data);
      });
      formData.append("rfq_id", self.hash[2]);
      var res = await axios.post(App.url + "rfq/attachment", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (!res.data.status) {
        App.errorMsg();
      } else {
        App.successMsg();

        self.dialogFile = false;
        self.getAttachment();
      }
    },
    saveComment: async function () {
      var self = this;
      /* if (self.comment.trim() == '') {
                    App.errorMsg("Please fill comment field!")
                } */
      const formData = new FormData();
      //formData.append('file', files);
      self.formFile2.map(function (val, i) {
        var key = val.value;
        formData.append(key, val.data);
      });
      formData.append("rfq_id", self.hash[2]);
      formData.append("comment", self.comment);
      var res = await axios.post(
        App.url + "rfq/rfqcomment",
        /*{
                    rfq_id: self.hash[2],
                    comment: self.comment,
                }*/ formData,
        {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        },
      );
      if (!res.data.status) App.errorMsg();
      else {
        self.comment = "";
        self.formFile2[0].data = undefined;
        App.successMsg();
      }
      self.getComment();
      self.showComment = false;

      self.$nextTick(() => {
        setTimeout(() => {
          const element = document.getElementById("history");
          element.scrollIntoView();
        }, 100);
      });
    },
    listDetail: function (item) {
      var self = this;
      self.selectedList = item;
      self.listDetailDialog = true;
    },
    onVisible: function (isVisible, e) {
      var self = this;
      self.isInViewport = !!isVisible;
      self.isInDom = !!e.target.isConnected;
    },
    getData: async function () {
      var self = this;
      var res = await axios.get(App.url + "rfq/rfq", {
        params: {
          filter: {
            id: self.hash[2],
          },
        },
      });

      if (!res.data.status) App.errorMsg();
      else {
        self.data = res.data.data[0];
        App.title = "#" + self.data.rfq_no;
        self.allowView = true;
        if (self.data.rfq_group == "Overseas Germany")
          if (
            self.data.rfq_group == "Overseas Germany" &&
            !check_user("rfq_overseas_germany")
          ) {
            self.allowView = false;
          }
        if (self.data.rfq_group == "Overseas Beijing")
          if (
            self.data.rfq_group == "Overseas Beijing" &&
            !check_user("rfq_overseas_beijing")
          ) {
            self.allowView = false;
          }
        if (self.data.rfq_group == "Overseas Others")
          if (
            self.data.rfq_group == "Overseas Others" &&
            !check_user("rfq_overseas_others")
          ) {
            self.allowView = false;
          }
        if (self.data.rfq_group == "Local")
          if (self.data.rfq_group == "Local" && !check_user("rfq_local")) {
            self.allowView = false;
          }

        if (check_user("administrator")) {
          self.allowView = true;
        }
      }
    },
    getComment: async function () {
      var self = this;
      var res = await axios.get(App.url + "rfq/rfqcomment", {
        params: {
          filter: {
            rfq_id: self.hash[2],
          },
          limit: -1,
        },
      });
      if (!res.data.status) App.errorMsg();
      else {
        self.items = res.data.data;
      }
    },
    getAttachment: async function () {
      var self = this;
      var res = await axios.get(App.url + "rfq/attachment", {
        params: {
          filter: {
            rfq_id: self.hash[2],
          },
          limit: -1,
        },
      });
      if (!res.data.status) App.errorMsg();
      else {
        self.attachments = res.data.data;
        self.$nextTick(() => {
          setTimeout(() => {
            var els = document.querySelectorAll(".clamp");
            els.forEach((val) => {
              $clamp(val, {
                clamp: 2,
              });
            });
          }, 100);
        });
      }
    },
    getItems: async function () {
      var self = this;
      var res = await axios.get(App.url + "rfq/rfqlist", {
        params: {
          filter: {
            rfq_id: self.hash[2],
          },
          limit: -1,
        },
      });
      if (!res.data.status) App.errorMsg();
      else {
        self.list = res.data.data;
      }
    },
    check: function (key, item, index) {
      var self = this;
      var old = self.items[index + 1];
      item = item.hist;
      if (old != undefined && item[key] != undefined) {
        old = old.hist;
        if (old[key] != item[key]) return true;
        return false;
      }
      return false;
    },
  },
  mounted: async function () {
    var self = this;
    loadMultipleLibrary(
      "https://cdn.jsdelivr.net/npm/javascript-time-ago@2.5.9/bundle/javascript-time-ago.js",
    )
      .then(function (val) {
        if (TimeAgo.getDefaultLocale() == "")
          TimeAgo.addDefaultLocale({
            locale: "en",
            long: {
              year: {
                previous: "last year",
                current: "this year",
                next: "next year",
                past: {
                  one: "{0} year ago",
                  other: "{0} years ago",
                },
                future: {
                  one: "in {0} year",
                  other: "in {0} years",
                },
              },
              quarter: {
                previous: "last quarter",
                current: "this quarter",
                next: "next quarter",
                past: {
                  one: "{0} quarter ago",
                  other: "{0} quarters ago",
                },
                future: {
                  one: "in {0} quarter",
                  other: "in {0} quarters",
                },
              },
              month: {
                previous: "last month",
                current: "this month",
                next: "next month",
                past: {
                  one: "{0} month ago",
                  other: "{0} months ago",
                },
                future: {
                  one: "in {0} month",
                  other: "in {0} months",
                },
              },
              week: {
                previous: "last week",
                current: "this week",
                next: "next week",
                past: {
                  one: "{0} week ago",
                  other: "{0} weeks ago",
                },
                future: {
                  one: "in {0} week",
                  other: "in {0} weeks",
                },
              },
              day: {
                previous: "yesterday",
                current: "today",
                next: "tomorrow",
                past: {
                  one: "{0} day ago",
                  other: "{0} days ago",
                },
                future: {
                  one: "in {0} day",
                  other: "in {0} days",
                },
              },
              hour: {
                current: "this hour",
                past: {
                  one: "{0} hour ago",
                  other: "{0} hours ago",
                },
                future: {
                  one: "in {0} hour",
                  other: "in {0} hours",
                },
              },
              minute: {
                current: "this minute",
                past: {
                  one: "{0} minute ago",
                  other: "{0} minutes ago",
                },
                future: {
                  one: "in {0} minute",
                  other: "in {0} minutes",
                },
              },
              second: {
                current: "now",
                past: {
                  one: "{0} second ago",
                  other: "{0} seconds ago",
                },
                future: {
                  one: "in {0} second",
                  other: "in {0} seconds",
                },
              },
            },
            short: {
              year: {
                previous: "last yr.",
                current: "this yr.",
                next: "next yr.",
                past: "{0} yr. ago",
                future: "in {0} yr.",
              },
              quarter: {
                previous: "last qtr.",
                current: "this qtr.",
                next: "next qtr.",
                past: {
                  one: "{0} qtr. ago",
                  other: "{0} qtrs. ago",
                },
                future: {
                  one: "in {0} qtr.",
                  other: "in {0} qtrs.",
                },
              },
              month: {
                previous: "last mo.",
                current: "this mo.",
                next: "next mo.",
                past: "{0} mo. ago",
                future: "in {0} mo.",
              },
              week: {
                previous: "last wk.",
                current: "this wk.",
                next: "next wk.",
                past: "{0} wk. ago",
                future: "in {0} wk.",
              },
              day: {
                previous: "yesterday",
                current: "today",
                next: "tomorrow",
                past: {
                  one: "{0} day ago",
                  other: "{0} days ago",
                },
                future: {
                  one: "in {0} day",
                  other: "in {0} days",
                },
              },
              hour: {
                current: "this hour",
                past: "{0} hr. ago",
                future: "in {0} hr.",
              },
              minute: {
                current: "this minute",
                past: "{0} min. ago",
                future: "in {0} min.",
              },
              second: {
                current: "now",
                past: "{0} sec. ago",
                future: "in {0} sec.",
              },
            },
            narrow: {
              year: {
                previous: "last yr.",
                current: "this yr.",
                next: "next yr.",
                past: "{0} yr. ago",
                future: "in {0} yr.",
              },
              quarter: {
                previous: "last qtr.",
                current: "this qtr.",
                next: "next qtr.",
                past: {
                  one: "{0} qtr. ago",
                  other: "{0} qtrs. ago",
                },
                future: {
                  one: "in {0} qtr.",
                  other: "in {0} qtrs.",
                },
              },
              month: {
                previous: "last mo.",
                current: "this mo.",
                next: "next mo.",
                past: "{0} mo. ago",
                future: "in {0} mo.",
              },
              week: {
                previous: "last wk.",
                current: "this wk.",
                next: "next wk.",
                past: "{0} wk. ago",
                future: "in {0} wk.",
              },
              day: {
                previous: "yesterday",
                current: "today",
                next: "tomorrow",
                past: {
                  one: "{0} day ago",
                  other: "{0} days ago",
                },
                future: {
                  one: "in {0} day",
                  other: "in {0} days",
                },
              },
              hour: {
                current: "this hour",
                past: "{0} hr. ago",
                future: "in {0} hr.",
              },
              minute: {
                current: "this minute",
                past: "{0} min. ago",
                future: "in {0} min.",
              },
              second: {
                current: "now",
                past: "{0} sec. ago",
                future: "in {0} sec.",
              },
            },
            now: {
              now: {
                current: "now",
                future: "in a moment",
                past: "just now",
              },
            },
            mini: {
              year: "{0}yr",
              month: "{0}mo",
              week: "{0}wk",
              day: "{0}d",
              hour: "{0}h",
              minute: "{0}m",
              second: "{0}s",
              now: "now",
            },
            "short-time": {
              year: "{0} yr.",
              month: "{0} mo.",
              week: "{0} wk.",
              day: {
                one: "{0} day",
                other: "{0} days",
              },
              hour: "{0} hr.",
              minute: "{0} min.",
              second: "{0} sec.",
            },
            "long-time": {
              year: {
                one: "{0} year",
                other: "{0} years",
              },
              month: {
                one: "{0} month",
                other: "{0} months",
              },
              week: {
                one: "{0} week",
                other: "{0} weeks",
              },
              day: {
                one: "{0} day",
                other: "{0} days",
              },
              hour: {
                one: "{0} hour",
                other: "{0} hours",
              },
              minute: {
                one: "{0} minute",
                other: "{0} minutes",
              },
              second: {
                one: "{0} second",
                other: "{0} seconds",
              },
            },
          });
        self.ready = true;
      })
      .catch(function (err) {});
    //await self.getData();
    //await self.getComment();
    //await self.getItems();
    await Promise.all([
      Promise.resolve(self.getData()),
      Promise.resolve(self.getComment()),
      Promise.resolve(self.getItems()),
      Promise.resolve(self.getAttachment()),
    ]);
    if (self.hash[3] == "comment")
      self.$nextTick(() => {
        setTimeout(() => {
          document
            .querySelector(".layout.login-layout")
            .scrollTo(
              0,
              document.querySelector(".layout.login-layout").scrollHeight,
            );
        }, 100);
      });
  },
};
</script>
