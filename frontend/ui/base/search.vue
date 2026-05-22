<template>
    <v-container class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-card-title2 primary-title style="padding-top: 8px; padding-bottom: 4px;">
            <v-spacer></v-spacer>
            <v-multilevel-menu :items="App.searchCategoryMenu"></v-multilevel-menu>
            <v-multilevel-menu :items="App.searchSortByMenu"></v-multilevel-menu>
        </v-card-title2>
        <v-data-table v-model="tableSelectedRow" @click:row="tableSelectRow" :headers="tableHeaders" :items="tableData" item-key="id" :loading="tableLoading" mobile-breakpoint="0" :options.sync="tableOptions" :server-items-length="tableDataTotal" hide-default-header>
            <template v-slot:body="props">
                <tr v-for="(item, index) in props.items" :key="index">
                    <td class="app-search" :style="'background-color: #EDEDED; padding: 8px !important;'+(index>0 ? 'padding-top: 0px !important;' : '')">
                        <v-card class="rounded-lg search-result-box" elevation="0">
                            <a :href="item[dataMapping.url]">
                                <v-card-title2 small class="redmine-search-title">
                                    <!-- :href="'#/redmine/issues/'+item.id" -->
                                    <div style="overflow: hidden;
									text-overflow: ellipsis;
									flex: 1;
									max-height: 17px;">{{item[dataMapping.url]}}</div>
                                    <v-chip small text-color="#22A65D" color="#E9F7EF"><span style="color: #57BC84; font-weight: bold">{{datetimeFormat(item[dataMapping.last_updated]).substr(0, 16)}}</span></v-chip>
                                </v-card-title2>
                                <v-card-title2 small class="redmine-search-title2" style="max-height: 34px; overflow: hidden;">
                                    <template v-if="item.$highlighting[dataMapping.subject]">
                                        <div v-html="item.$highlighting[dataMapping.subject][0]"></div>
                                    </template>
                                    <template v-else>{{item[dataMapping.subject]}}</template>
                                </v-card-title2>
                            </a>
                            <v-card-text v-if="hasPrependTextSlot" style="padding-top: 0 !important; padding-bottom: 0 !important;">
                                <slot name="prepend-text" v-bind:item="item"></slot>
                            </v-card-text>
                            <v-card-text style="max-height: 2.7rem; padding-top: 0; padding-bottom: 0; overflow: hidden;">
                                <template v-if="item.$highlighting[dataMapping.description]">
                                    <div v-html="item.$highlighting[dataMapping.description][0]"></div>
                                </template>
                                <template v-else>{{item[dataMapping.description]}}</template>
                            </v-card-text>
                            <v-card-text v-if="hasAppendTextSlot" style="padding-top: 0 !important; padding-bottom: 0 !important;">
                                <slot name="append-text" v-bind:item="item"></slot>
                            </v-card-text>
                        </v-card>
                    </td>
                </tr>
            </template>
        </v-data-table>
    </v-container>
</template>

<style scoped>
    .search-result-box {
        padding-bottom: 16px;
    }

    .app-search * {
        font-family: arial, sans-serif;
    }

    .redmine-search-title * {
        font-size: 14px !important;
        color: #575757;
    }

    .redmine-search-title {
        padding-bottom: 0 !important;
        cursor: pointer;
        font-size: 14px !important;
    }

    .redmine-search-title2 * {
        font-size: 14px !important;
        color: #1155CC;
    }

    .redmine-search-title2 {
        cursor: pointer;
        padding-top: 0 !important;
        font-size: 14px !important;
    }

    a::after {
        /* top: 3px;
		right: 5px;
		position: absolute; */
        display: none;
    }

    em {
        font-weight: bold;
        color: #5f6368 !important;
        font-style: normal;
        background-color: #E0E0E0;
        border-radius: 4px;
    }
</style>

<script>
    module.exports = {
        name: 'app-search',
        props: {
            category: ''
        },
        data: function() {
            return {
                value: {},
                tableData: [],
                tableSelectedRow: [],
                tableHeaders: [{
                    text: "ID",
                    value: "id",
                    align: 'start',
                    sortable: true,
                    filterable: false,
                    divider: false,
                    class: "",
                    width: 180
                }],
                tableLoading: false,
                tableOptions: {
                    sortBy: ['id'],
                    sortDesc: [true],
                    filter: {}
                },
                tableDataTotal: 0,
                dataMapping: {
                    url: 'url',
                    subject: 'subject',
                    description: 'description',
                    last_updated: 'last_updated'
                }
            }
        },
        computed: {
            hasPrependTextSlot: function() {
                return this.$scopedSlots["prepend-text"] !== undefined
            },
            hasAppendTextSlot: function() {
                return this.$scopedSlots["append-text"] !== undefined
            }
        },
        watch: {
            tableOptions: function(val) {
                this.tableGetData()
            }
        },
        methods: {
            tableGetData: function() {
                var self = this
                self.tableLoading = true
                /*  self.tableOptions.filter.page = self.tableOptions.page
                 self.tableOptions.filter.offset = self.tableOptions.page * self.tableOptions.itemsPerPage - 10
                 self.tableOptions.filter.limit = self.tableOptions.itemsPerPage */
                var params = {
                    page: self.tableOptions.page,
                    offset: self.tableOptions.page * self.tableOptions.itemsPerPage - 10,
                    limit: self.tableOptions.itemsPerPage,
                    q: self.tableOptions.filter.q,
                    category: self.category,
                    sortBy: App.searchSortBySelected,
                    sortDesc: App.searchSortBySelectedDesc
                }
                if (App.searchCategorySelected != null)
                    params.search_type = App.searchCategorySelected
                axios.get(App.url + 'search', {
                        params: params
                    })
                    .then(function(response) {
                        var data = response.data
                        self.tableData = data.data
                        self.tableDataTotal = data.total
                        self.dataMapping = data.default
                        self.tableLoading = false
                        self.$emit('search-completed', self.tableData, data.q)
                    })
                    .catch(function(error) {
                        self.tableLoading = false
                    })
            },
            tableSelectRow: function(val) {
                if (val.id == (this.tableSelectedRow[0] || {
                        id: false
                    }).id)
                    this.tableSelectedRow = []
                else
                    this.tableSelectedRow = [val]
            }
        },
        mounted: function() {
            var self = this
			var hash = location.hash.split('#')[1]
			hash = hash.split('/__')[0]
			hash = hash.split('/')
			if(hash.length>=4){
				App.search = unescape(hash[3]).replace(/\%2F/g, '/')
				App.showSearch = true
			}
            var search = App.search;
            self.tableOptions.filter = Object.assign({
                limit: 10,
                offset: 0,
                q: $get('q') || search || '*',
                category: self.category,
                page: 1
            }, App.tmp)
            App.tmp = false
            self.tableGetData()
        }
    }
</script>