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
          disabled: !check_user("po_validasi"),
          loadWithBase: "/purchasing/bom/dashboard",
        } /* ,
					{
						name: 'Project',
						children: [{
							name: 'List Project',
							loadWithBase: '/purchasing/bom/project/list'
						}, {
							name: 'Bom',
							loadWithBase: '/purchasing/bom/project/bom'
						}]
					}, */,
        {
          name: "Purchase Order",
          children: [
            {
              name: "Active",
              loadWithBase: "/purchasing/bom/purchasing/purchaseorder",
              disabled: !check_user(['administrator', 'po_admin', 'entry_po_page'])
            },
            {
              name: "History",
              loadWithBase: "/purchasing/bom/purchasing/history",
              disabled: !check_user(['administrator', 'po_admin', 'history_po_page'])
            },
            {
              name: "Monitoring",
              loadWithBase: "/purchasing/bom/purchasing/monitoring",
              disabled: !check_user(['administrator', 'po_admin', 'monitoring_po_page'])
            },{
              name: "Outstanding PO",
              loadWithBase: "/purchasing/bom/purchasing/monitorprpo",
              disabled: !check_user(['administrator', 'po_admin', 'outstanding_po_page'])
            },{
              name: "Proforma PO",
              loadWithBase: "/purchasing/bom/fake/purchaseorder",
            },
            
            // {
            //   name: "PR",
            //   loadWithBase: "/prdev/bomdev/pr/monitoring",
            //   disabled: !check_user(['administrator', 'po_admin'])
            // },
            
          ],
          disabled: !check_user(['administrator', 'po_admin','purchaseorder_po_page'])
        },
        {
          name: "Manufacturing Order",
          loadWithBase: "/purchasing/bom/manufacturing/order",
          disabled: !check_user(["administrator"]),
        },
        
// 		{
// 			name: "Fake Purchase Order",
// 			loadWithBase: "/purchasing/bom/fake/purchaseorder",
// 		},

        {
          name: "Report",
          children: [
              
            // {
            //   name: "PO Payment",
            //   loadWithBase: "/purchasing/bom/report/popayment",
            //   disabled: !check_user(['administrator', 'po_admin', 'payment_po_page'])
            // },
            
            {
              name: "PO Budget",
              loadWithBase: "/purchasing/bom/report/pobudget",
              disabled: !check_user(['administrator', 'po_admin', 'budget_po_page'])
            },
            {
              name: "Request Arival",
              loadWithBase: "/purchasing/bom/purchasing/requestarival",
              disabled: !check_user(['administrator', 'po_admin', 'budget_po_page'])
            },  
            
            // {
            //   name: "PO Budget 2",
            //   loadWithBase: "/purchasing/bom/report/pobudget2",
            //   disabled: !check_user(['administrator'])
            // },
            
          ],
          disabled: !check_user(['administrator', 'po_admin','report_po_page'])
        },
        
        // {
        //   name: "Home",
        //   loadWithBase: "/purchasing/bom/purchasing/purchaseorder_view_only",
        //   disabled: !check_user("view_only_po"),
        // },
        
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
    App.title = "";
    App.items = this.items.slice(0);
  },
};
</script>
