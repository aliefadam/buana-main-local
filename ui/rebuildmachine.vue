<template>
    <v-layout class="login-layout">
        <v-container style="margin: 0; padding: 0; max-width: 100%">
            <router-view style="flex: 1; padding: 0 !important" :class="'layout-' + $vuetify.breakpoint.name" :formatter="['_key', '_key']"></router-view>
        </v-container>
    </v-layout>
</template>

<style></style>

<script>
    module.exports = {
        name: "",
        data: function() {
            return {
            //     items: [{
            //         name: "Daily Report",
            //         children: [{
            //             name: "Entry",
            //             loadWithBase: "/rebuildmachine/rebuildmachine/reportentry",
            //         }],
            //     }, {
            //         name: "List Part",
            //         children: [{
            //                 name: "Entry CYDH",
            //                 loadWithBase: "/rebuildmachine/rebuildmachine/listentry"
            //             },
            //             {
            //                 name: "Entry Koerber",
            //                 loadWithBase: "/rebuildmachine/rebuildmachine/listkoerber"
            //             }
            //         ],
            //     }, {
            //         name: "Subassembly",
            //         children: [{
            //             name: "Entry",
            //             loadWithBase: "/rebuildmachine/rebuildmachine/subassyentry"
            //         }],
            //     }, {
			// 		name: "Dashboard",
			// 		loadWithBase: "/rebuildmachine/rebuildmachine/dashboard"
			// 	}
            // ],
            };
        },
        methods: {},
        mounted: function() {
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