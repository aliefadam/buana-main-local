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
    <pr :item-options="historyOptions" table-only history></pr>
    <!--<v-tabs style="flex: 0" v-model="tab">
			<v-tab :key="0">PR</v-tab>
		</v-tabs>
        <v-tabs-items v-model="tab" style="flex: 1;" class="tabs">
			<v-tab-item :key="0" style="height: 100%;">
				
			</v-tab-item>
			
		</v-tabs-items>-->
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
          peminta_setuju: null,
          penyetuju_setuju: null,
        },
        filterType: {
          peminta_setuju: "notnull",
          penyetuju_setuju: "notnull",
        },
      },
    };
  },
  methods: {},
  mounted: function () {},
};
</script>
