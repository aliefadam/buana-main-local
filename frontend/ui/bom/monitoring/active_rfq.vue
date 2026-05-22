<template>
    <v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template :show-select="false" :data="data" :items-options="itemsOptions" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" table-only :table-fixed-header="tableFixedHeader">

            <template v-slot:after-header>
				<v-row>
					<v-col cols="3">
						<v-card class="mx-auto" max-width="344" outlined>
							<v-list-item three-line>
								<v-list-item-content>
									<div class="text-overline mb-4" style="font-weight: bold">
										New RFQ
									</div>
									<v-list-item-title class="text-h5 mb-1" style="font-weight: bold">
										{{total_new_rfq}}
									</v-list-item-title>
								</v-list-item-content>
							</v-list-item>
						</v-card>
					</v-col>
					<v-col cols="3">
						<v-card class="mx-auto" max-width="344" outlined>
							<v-list-item three-line>
								<v-list-item-content>
									<div class="text-overline mb-4" style="font-weight: bold">
										RFQ Submitted
									</div>
									<v-list-item-title class="text-h5 mb-1" style="font-weight: bold">
										{{total_submitted}}
									</v-list-item-title>
								</v-list-item-content>
							</v-list-item>
						</v-card>
					</v-col>
					<v-col cols="3">
						<v-card class="mx-auto" max-width="344" outlined>
							<v-list-item three-line>
								<v-list-item-content>
									<div class="text-overline mb-4" style="font-weight: bold">
										Clarification Needed
									</div>
									<v-list-item-title class="text-h5 mb-1" style="font-weight: bold">
										{{total_need_clarification}}
									</v-list-item-title>
								</v-list-item-content>
							</v-list-item>
						</v-card>
					</v-col>
					<v-col cols="3">
						<v-card class="mx-auto" max-width="344" outlined>
							<v-list-item three-line>
								<v-list-item-content>
									<div class="text-overline mb-4" style="font-weight: bold">
										Quotation Received
									</div>
									<v-list-item-title class="text-h5 mb-1" style="font-weight: bold">
										{{total_quotation_received}}
									</v-list-item-title>
								</v-list-item-content>
							</v-list-item>
						</v-card>
					</v-col>
				</v-row>
            </template>

            <template v-slot:item.rfq_no="props">
                <a :href="'#/rfq/rfq/'+props.item.id">{{props.item.rfq_no}}</a>
            </template>

            <template v-slot:item.priority="props">
                <div style="justify-content: center; display: flex;">
                    <v-alert dense color="#ffcc99" v-if="props.item.priority=='Medium'" style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold;">
                        {{props.item.priority}}
                    </v-alert>
                    <v-alert dense color="#f88686" v-if="props.item.priority=='High'" style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold;">
                        {{props.item.priority}}
                    </v-alert>
                    <v-alert dense color="#bfdda8" v-if="props.item.priority=='Low'" style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold;">
                        {{props.item.priority}}
                    </v-alert>
                </div>
            </template>
        </v-template>
    </v-container>
</template>

<style scoped>
</style>

<script>
    module.exports = {
        name: '',
        creator: '',
        components: {},
        props: {
            value: undefined,
            data: {
                type: Object
            },
            tableOnly: {
                type: Boolean,
                default: false
            },
            tableFixedHeader: {
                type: Boolean,
                default: true
            },
            disableTable: {
                type: Boolean,
                default: false
            },
            activeColumn: {
                type: Boolean,
                default: false
            },
            showExpand: {
                type: Boolean,
                default: false
            },
            singleExpand: {
                type: Boolean,
                default: true
            },
            history: {
                type: Boolean,
                default: false
            },
            itemsOptions: {
                type: Object,
                default: {
                    filter: {},
                    filterType: {},
                    sortBy: ['rfq_no'],
                    sortDesc: [false]
                }
            }
        },
        data: function() {
            return {
                name: '',
                itemKey: 'id',
                total_new_rfq: 0,
                total_submitted: 0,
                total_need_clarification: 0,
                total_quotation_received: 0,
                url: 'monitoring/listrfqclarification',
                loading: false,
                dataId: {},
                headers: [{
                    "text": "#",
                    "value": "id",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "int",
                    "disabled": false,
                    "visible": false,
                    "required": true,
                    "form": false,
                    "filter": false,
                    "groupable": false,
                    "url": "",
                    "search": [],
                    "formatter": [],
                    "options": {
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {}
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10"
                }, {
                    "text": "Rfq No",
                    "value": "rfq_no",
                    "align": "start",
                    "sortable": false,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "varchar",
                    "disabled": false,
                    "visible": true,
                    "required": true,
                    "form": false,
                    "filter": true,
                    "groupable": false,
                    "url": "",
                    "search": [],
                    "formatter": [],
                    "options": {
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {}
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10"
                }, {
                    "text": "Title",
                    "value": "title",
                    "align": "start",
                    "sortable": false,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "varchar",
                    "disabled": false,
                    "visible": true,
                    "required": true,
                    "form": false,
                    "filter": true,
                    "groupable": false,
                    "url": "",
                    "search": [],
                    "formatter": [],
                    "options": {
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {}
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10"
                }, {
                    "text": "Priority",
                    "value": "priority",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "list",
                    "disabled": false,
                    "visible": true,
                    "required": true,
                    "form": true,
                    "filter": true,
                    "groupable": false,
                    "data_value": ["High", "Medium", "Low"]
                }],
                //row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
                selected: false,
                //berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
                selectedAll: [],
                isInDom: false,
                isInViewport: false,
            }
        },
        watch: {

        },
        computed: {
            headersObj: function() {
                var tmp = {},
                    self = this
                Object.keys(self.headers).map(key => {
                    var val = self.headers[key]
                    tmp[val.value] = val
                })
                return tmp;
            },
        },
        methods: {},
        mounted: function() {
            var self = this;

        }
    }
</script>