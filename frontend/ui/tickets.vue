<template>
    <v-layout class="login-layout">
      <v-container style="margin: 0; padding: 0; max-width: 100%">
        <router-view
          style="flex: 1; padding: 0 !important"
          :class="'layout-' + $vuetify.breakpoint.name"
          :formatter="['_key', '_key']"
        ></router-view>
      </v-container>
    </v-layout>
  </template>
  
  <style></style>
  
  <script>
  module.exports = {
    name: "",
    data: function () {
      return {
        items: [
          {
            name: "Dashboard",
            disabled: !check_user("tickets_validasi"),
            loadWithBase: "/tickets/dashboard",
          },
          {
            name : "Entry",
            loadWithBase: "/tickets/tickets/entry"
          },
          {
            name : "Monitoring",
            loadWithBase: "/tickets/tickets/monitoring"
          },
           
        ],
      };
    },
    methods: {},
    mounted: function () {
      App.items = [];
      App.bottomItems = [];
      App.bottomBtn = undefined;
      App.drawer = false;
      App.toolbar = true;
      App.title = "Tickets";
      App.items = this.items.slice(0);
    },
  };
  </script>
  