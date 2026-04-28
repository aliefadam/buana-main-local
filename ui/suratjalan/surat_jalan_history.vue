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
    <sj :items-options="historyOptions" table-only history></sj>
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
    sj: "url:ui/suratjalan/surat_jalan.vue",
  },
  data: function () {
    return {
      tab: 0,
      historyOptions: {
        filter: {
          approved:"-1, 3",
        },
        filterType: {
          approved:'in',
        },
        filterCondition:{
        }
      },
    };
  },
  methods: {},
  mounted: function () {},
};
</script>
