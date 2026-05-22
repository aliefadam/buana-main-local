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
    <pr :items-options="historyOptions" table-only history show-column ></pr>
  </v-container>
</template>

<style>
.tabs > .v-window__container {
  height: 100%;
}
</style>

<script>
module.exports = {
  name: "",
  components: {
    pr: "url:ui/pr/pr.vue",
  },
  data: function () {
    return {
      tab: 0,
      historyOptions: {
        filter: {
          status:"-1, 3, -4",
        //   created_by: App.userData.data[0].userid
        },
        filterType: {
          status:'in',
        },
        filterCondition:{
        }
      },
    };
  },
  methods: {},
  mounted: function () {
    var self = this
    loadMultipleLibrary('https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js')
    if(check_user(['administrator'])){
        delete self.itemsOptions.filter.created_by
    }
  },
};
</script>
