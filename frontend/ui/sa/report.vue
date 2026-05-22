<template>
  <div>
    <table class="table-theme1 default-table">
      <thead>
        <tr>
          <th>No</th>
          <th>PIC</th>
          <th>Section</th>
          <th>Subassembly</th>
          <th>Part Number</th>
          <th>Article Number</th>
          <th>NPB No</th>
          <th>Item Replacement No</th>
          <th>Notes</th>
          <th>Image</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(val, i) in info">
          <td>{{ i + 1 }}</td>
          <td>{{ val.created_by_name }}</td>
          <td>{{ val.field_list_18 }}</td>
          <td>{{ val.field_list_19 }}</td>
          <td>{{ val.field_list_26 }}</td>
          <td>{{ val.field_varchar_27 }}</td>
          <td>{{ val.field_list_29 }}</td>
          <td>{{ val.field_list_28 }}</td>
          <td>{{ val.field_text_20 }}</td>
          <td>
            <img
              style="height: 150px; max-width; 150px;"
              :src="
                'https://main.buanamultiteknik.com/api' +
                val.field_attachment_24.slice(1).split('+++')[0]
              "
            />
            <img />
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.table-theme1 {
  border: 1px solid black !important;
  font-family: "lucida grande", Tahoma, verdana, arial, sans-serif;
  font-size: 11px;
  font-weight: bolder;
}

.table-theme1 thead tr th {
  background-color: #de847b !important;
  border: 1px solid black !important;
  color: #fff !important;
  font-weight: bolder !important;
}

.table-theme1 thead tr:first-child th {
  background-color: rgb(15, 71, 92) !important;
  border: 1px solid black !important;
  color: #fff !important;
  font-weight: bolder !important;
}

.table-theme1.stripe tr:nth-child(even) td {
  background-color: #eed6d3 /* #E6B8B7 */ !important;
  border: 1px solid black !important;
}

.table-theme1.stripe tr:nth-child(odd) td {
  background-color: #fff !important;
  border: 1px solid black !important;
}
</style>

<script>
module.exports = {
  name: "",
  data: function () {
    return {
      title: false,
      info: [],
      url: "document/notes/maintenance_maker",
      hash: location.hash.split("/").slice(1),
    };
  },
  methods: {
    getdata: async function () {
      var self = this;
      console.log(App.url + self.url);
      var res = await axios.get(App.url + self.url, {
        params: vTableParam({
          filter: {
            note_id: self.hash[2],
          },
          limit: -1,
        }),
      });
      if (!res.data.status) App.errorMsg();
      else {
        self.info = res.data.data;
      }
    },
  },
  mounted: function () {
    this.getdata();
  },
};
</script>
