<template>
  <v-container
    style="
      padding: 1em !important;
      height: 100%;
      display: flex;
      flex-direction: column;
      margin: 0;
      width: 100%;
      max-width: 100%;
      padding: 1em;
    "
  >
    <v-template v-model="value" ref="template" :url="url">
      <!--<div class="text-h5 font-weight-bold" style="padding: 0.25em 0.25em 0px 0.75em; margin: 0"></div>-->
      <template>
        <v-row
          align="center"
          justify="center"
          flex-col
          transition="slide-y-transition"
          style="padding: 0.25em; flex: 0"
        >
          <v-col md="4" lg="3" xl="2">
            <v-card flat class="appbox" disabled>
              <v-list-item-avatar size="80" tile>
                <v-img
                  :src="'https://main.buanamultiteknik.com/img/departments.svg'"
                ></v-img>
              </v-list-item-avatar>
              <v-card-item>
                <div>
                  <div>Department</div>
                  <div class="text-h6 mb-1">Total:</div>
                </div>
              </v-card-item>
            </v-card>
          </v-col>
          <v-col md="4" lg="3" xl="2">
            <v-card flat class="appbox" disabled>
              <v-list-item-avatar size="80" tile>
                <v-img
                  :src="'https://main.buanamultiteknik.com/img/box.svg'"
                ></v-img>
              </v-list-item-avatar>
              <v-card-item>
                <div>
                  <div>Items {{ $refs.template.itemsTotal }}</div>
                  <div class="text-h6 mb-1">Total:</div>
                </div>
              </v-card-item>
            </v-card>
          </v-col>
          <v-col md="4" lg="3" xl="2">
            <v-card flat class="appbox" disabled>
              <v-list-item-avatar size="80" tile>
                <v-img
                  :src="'https://main.buanamultiteknik.com/img/human.svg'"
                ></v-img>
              </v-list-item-avatar>
              <v-card-item>
                <div>
                  <div>Supplier</div>
                  <div class="text-h6 mb-1">Total:</div>
                </div>
              </v-card-item>
            </v-card>
          </v-col>
        </v-row>
      </template>
    </v-template>
  </v-container>
</template>

<style scoped>
.appbox {
  border-radius: 0.5em;
  border: 1px solid rgba(0, 0, 0, 0.1);
  height: 100px;
  display: flex;
  justify-content: left;
  align-items: center;
  font-weight: 800;
  margin: 0.25em;
  margin-left: 0;
  margin-top: 0;
  cursor: pointer;
  font-size: 1.5em;
  padding: 0 8px;
  background-color: #0f4083;
  color: #ffffff;
}
</style>

<script>
module.exports = {
  name: "",
  data: function () {
    return {
      url: "bom/item",
    };
  },
  methods: {},
  mounted: function () {},
};
</script>
