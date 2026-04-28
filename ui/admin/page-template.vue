<template>
    <v-card elevation="0" tile v-observe-visibility="onVisible" v-resize="onResize" :id="'v-template-'+_uid" :class="'v-template-new v-template '+cardClass">
        <v-overflow-menu :small="true" v-if="!disableMenu">
            <slot name="title-body"></slot>
            <v-spacer></v-spacer>
            <template v-slot:overflow-content>
                <slot name="prepend-menu"></slot>
                <v-menu offset-y v-if="!hideFilterButton && presets.length > 0">
                    <template v-slot:activator="props">
                        <v-btn-toggle active-class="none" :color="Object.keys(filterPills).length == 0 ? 'primary' : 'warning'" small class="v-btn--outlined" borderless>
                            <v-btn color="primary" outlined small @click="dialogFilter = !dialogFilter" style=" margin-right: 0; margin-left: 0; height: 26px;">
                                <v-icon small left color="primary">mdi-filter</v-icon>Filter
                                <v-icon small right color="primary">{{'mdi-menu-'+(dialogFilter ? 'up' : 'down')}}
                                </v-icon>
                            </v-btn>
                            <v-btn color="primary" outlined small v-bind="props.attrs" v-on="props.on" style="margin-right: 0; margin-left: 0; border-left: 1px solid !important; height: 26px;" v-if="selectedPreset == false" icon>
                                <v-icon small>{{'mdi-menu-'+(props.value ? 'up' : 'down')}}</v-icon>
                            </v-btn>
                            <v-btn color="primary" outlined small v-bind="props.attrs" v-on="props.on" style="margin-right: 0; margin-left: 0; border-left: 1px solid !important; height: 26px;" v-else>
                                <template> {{selectedPreset.name}}</template>
                                <v-icon small right>{{'mdi-menu-'+(props.value ? 'up' : 'down')}}</v-icon>
                            </v-btn>
                            <v-btn v-if="selectedPreset != false || Object.keys(filterPills).length > 0" @click="clearFilter(true)" color="primary" outlined small style="margin-left: 0; margin-right: 0; border-left: 1px solid !important; height: 26px;" icon>
                                <v-icon small color="error">mdi-close-thick</v-icon>
                            </v-btn>
                        </v-btn-toggle>
                    </template>
                    <v-list>
                        <v-list-item v-for="(item, index) in presets" :key="index" @click="selectedPreset = item">
                            <v-list-item-title>{{ item.name }}</v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-menu>
                <v-btn-toggle v-if="!hideFilterButton && presets.length == 0" active-class="none" :color="Object.keys(filterPills).length == 0 ? 'primary' : 'warning'" small class="v-btn--outlined" borderless style="margin-left: 2px;">
                    <v-btn color="primary" outlined small @click="dialogFilter = !dialogFilter" style=" margin-right: 0; margin-left: 0; height: 26px;">
                        <v-icon small left color="primary">mdi-filter</v-icon>Filter
                        <v-icon small right color="primary">{{'mdi-menu-'+(dialogFilter ? 'up' : 'down')}}</v-icon>
                    </v-btn>
                    <v-btn v-if="selectedPreset != false" @click="clearFilter(true)" color="primary" outlined small style="margin-left: 0; margin-right: 0; border-left: 1px solid !important; height: 26px;" icon>
                        <v-icon small color="error">mdi-close-thick</v-icon>
                    </v-btn>
                </v-btn-toggle>
                <!-- Use this slot to add element after filter button -->
                <slot name="menu-after-filter"></slot>
                <template v-if="tableOnly==false">
                    <v-btn :loading="loading" color="warning" outlined small @click="openEdit" :disabled="selectedRowTmp.length == 0 || disableEditButton" v-if="!hideEditButton">
                        <v-icon small left>mdi-pencil</v-icon>Edit
                    </v-btn>
                </template>
                <!-- Use this slot to add element after edit button -->
                <slot name="menu-after-edit"></slot>
                <template v-if="tableOnly==false && alwaysShowForm == false">
                    <v-btn :loading="loading" color="primary" outlined small @click="openAdd" v-if="!hideAddButton" :disabled="disableAddButton">
                        <v-icon small left>mdi-plus</v-icon>{{labelAdd}}
                    </v-btn>
                </template>
                <!-- Use this slot to add element after add button -->
                <slot name="menu-after-add"></slot>
                <template v-if="tableOnly==false">
                    <v-btn :loading="loading" :color="deleteMode == 'delete' ? 'error' : (lastSelectedRow === undefined ? 'error' : (lastSelectedRow[activeColumn]==1 ? 'error' : 'success'))" outlined small @click="deleteData" :disabled="selectedRowTmp.length == 0 || disableDeleteButton" v-if="!hideDeleteButton">
                        <v-icon small left>{{deleteMode == 'delete' ? 'mdi-delete' : (lastSelectedRow === undefined ? '' : (lastSelectedRow[activeColumn]==1 ? 'mdi-close-thick' : 'mdi-check-bold'))}}</v-icon>{{deleteMode == 'delete' ? labelDelete : (lastSelectedRow === undefined ? 'Active/Inactive' : (lastSelectedRow[activeColumn]==1 ? 'Set Inactive' : 'Set Active'))}}
                    </v-btn>
                </template>
                <!-- Use this slot to add element after delete button -->
                <slot name="menu-after-delete"></slot>
                <!-- Use this slot to add element after menu buttons -->
                <slot name="append-menu"></slot>
            </template>
        </v-overflow-menu>

        <v-action-dialog save-icon="mdi-filter-plus" label-save="Apply" :class="formClass" :max-width="formMaxWidth" :width="formWidth" v-model="dialogFilter" :disableAnimation="alwaysShowForm" :title="'Filter '+name" @save="applyFilter">
            <!-- Use this slot to add before `add` form -->
            <slot name="prepend-dialog-filter"></slot>
			<v-select single-line hide-details dense v-model="selectedAvailableFilter" :items="availableFilter" label="Filter" style=" max-width: 300px !important;" @change="onFilterSelect"></v-select>
			<!-- <table class="table" style="border: 0; width: 100%">
				<tr v-for="(item, index) in selectedField" :key="index">
					<td style="min-width: 10%"><v-icon small @click="">mdi-close</v-icon></td>
					<td style="min-width: 10%">a</td>
					<td style="width: 100%">a</td>
				</tr>
			</table> -->
            <form-template :headers="formFilter" ref="formFilter" dense>
				<template v-for="(item, index) in formFilter" :key="index" :slot="item.value+'.prepend'">
					<div style="flex: 0; align-items: center; display: flex;"><v-icon @click="removeFilter(item.value)" color="error" style=" margin-right: 8px;">mdi-close</v-icon></div>
					<v-select v-if="['ckeditor', 'varchar'].includes(item.type)" value="contains" style="flex: 0; min-width: 180px; margin-right: 8px; flex-basis: min-content;" label="rule" hide-details filled dense :items="['is', 'not', 'contains', `doesn't contains`]" @change="function(val){onFilterType(val, item)}"></v-select>
					<v-select v-else-if="item.type=='int' || item.type=='float' || item.type=='date'" value="is" style="flex: 0; min-width: 180px; margin-right: 8px;" label="rule" hide-details filled dense :items="['is', 'not', 'more than', 'less than']" @change="function(val){onFilterType(val, item)}"></v-select>
					<v-select v-else-if="item.type=='list'" value="is" style="flex: 0; min-width: 180px; margin-right: 8px;" label="rule" hide-details filled dense :items="['is', 'not']" @change="function(val){onFilterType(val, item)}"></v-select>
				</template>	
				<template v-for="(item, index) in formFilter" :key="index" :slot="item.value+'.append'">
					<div style="flex: 0; align-items: center; display: flex;"><v-icon @click="addFilter(item.value, item.data, item)" color="success" style=" margin-left: 8px;">mdi-plus</v-icon></div>
					<div style="flex: 1; flex-basis: 100%; margin-top: 4px; margin-bottom: 4px;" v-if="filterValue[item.value]">
						<v-chip @click:close="removeFilterValue(item.value, val)" close small v-for="(val, idx) in filterValue[item.value]" :key="idx">{{filterType[item.value][idx]}}: {{val}}</v-chip>
					</div>
				</template>
			</form-template>
            <!-- Use this slot to add after `add` form -->
            <slot name="append-dialog-filter"></slot>
			<template v-slot:left-actions>
				<v-btn small color="primary" outlined @click="dialogSavePreset=true"><v-icon small left>mdi-content-save</v-icon>Save</v-btn>
				<v-btn small color="error" outlined @click="deletePreset" v-if="selectedPreset"><v-icon small left>mdi-close</v-icon>Delete</v-btn>
				<v-spacer></v-spacer>
			</template>
        </v-action-dialog>
		
		<v-action-dialog v-model="dialogSavePreset" title="Save Preset" @save="saveFilter">
			<v-autoform cols="12" sm="12" v-model="filterSaveForm" hide-details="auto" :filled="true"
				:valid.sync="validFilter">
			</v-autoform>
		</v-action-dialog>

        <v-action-dialog :class="formClass" :max-width="formMaxWidth" :width="formWidth" v-model="dialogAdd" :disableAnimation="alwaysShowForm" :title="formAddMode ? false : 'Add '+name" @save="save" :disable-save="!valid" :save-loading="loading" :fullscreen="dialogAddFullscreen || fullscreenDialog" :mode="formAddMode || alwaysShowForm ? 'expand':'dialog'">
            <!-- Use this slot to add before `add` form -->
            <slot name="prepend-dialog-add"></slot>
			<template v-for="(index, name) in formAppendSlot">
				{{name.split('.')[2]+'.append'}}
			</template>
            <form-template :headers="formModelAdd" :valid.sync="valid" ref="formAdd">
				<template v-for="(index, name) in formPrependSlot" :slot="name.split('.')[2]+'.prepend'" slot-scope="formModelAdd">
					<slot :name="name" v-bind="formModelAdd">
					</slot>
				</template>
				<template v-for="(index, name) in formAppendSlot" :slot="name.split('.')[2]+'.append'" slot-scope="formModelAdd">
					<slot :name="name" v-bind="formModelAdd">
					</slot>
				</template>
			</form-template>
            <!-- Use this slot to add after `add` form -->
            <slot name="append-dialog-add"></slot>
        </v-action-dialog>

        <v-action-dialog :class="formClass" :max-width="formMaxWidth" :width="formWidth" v-model="dialogEdit" :disableAnimation="alwaysShowForm" :title="'Edit '+name" @save="saveEdit" :disable-save="!validEdit" :save-loading="loading" :fullscreen="fullscreenDialog" :mode="alwaysShowForm ? 'expand':'dialog'">
            <!-- Use this slot to add before `edit` form -->
            <slot name="prepend-dialog-edit"></slot>
            <form-template :headers="formModel" :valid.sync="validEdit" ref="formEdit"></form-template>
            <template v-slot:append-actions v-if="alwaysShowForm">
                <v-btn color="error" outlined small @click="dialogEdit=false">
                    <v-icon small left>mdi-close</v-icon>Cancel
                </v-btn>
            </template>
            <!-- Use this slot to add after `edit` form -->
            <slot name="append-dialog-edit"></slot>
        </v-action-dialog>
        <!-- <v-divider></v-divider> -->
        <template v-if="!disableTable">
            <div class="table-container" ref="tableContainer" :style="tableContainerStyle">
                <v-data-table class="template-table" @toggle-select-all="onToggleSelectAll" @item-selected="onSelected" :height="tableHeightReal" :hide-default-header="hideDefaultHeader" ref="table" :fixed-header="tableFixedHeader" v-model="selectedRowTmp" :headers="headersFilter" :items="items" :item-key="itemKey" :loading="loading" mobile-breakpoint="0" :options.sync="defaultItemsOptions" :server-items-length="itemsTotal" :show-select="showSelect" :single-select="singleSelect" :disable-pagination="disablePagination" :hide-default-footer="hideDefaultFooter" :disable-sort="disableSort" :item-class="fnItemClass" :footer-props="footerProps" :show-expand="showExpand" :single-expand="singleExpand" :dense="tableDense" :multi-sort="multiSort" @click:row="onRowClick" @update:items-per-page="onItemsPerPage" @update:page="onPage" @update:group-by="autoRowspan" v-on="tableListeners" :items-per-page="itemsPerPage">
                    <template v-slot:no-data>
                        No data available
                        <v-btn text icon @click="getItems">
                            <v-icon>mdi-refresh</v-icon>
                        </v-btn>
                    </template>
                    <template v-slot:no-results>
                        No data available
                        <v-btn text icon @click="getItems">
                            <v-icon>mdi-refresh</v-icon>
                        </v-btn>
                    </template>
                    <template v-slot:group.header="props">
                        <td :colspan="props.headers.length" class="custom-group-header" v-observe-visibility="setRemoveGroup(props)" @click.stop="groupSort(groupByItem)">
                            <v-btn text icon small @click.stop="props.toggle">
                                <v-icon>{{'mdi-'+(props.isOpen ? 'minus' : 'plus')}}</v-icon>
                            </v-btn>
                            <div class="sort-toggle-parent" style="display: inline-block;">{{groupByItem.text}}:
                                <template v-if="$scopedSlots['item.'+groupByItem.value]">
                                    <slot :name="'item.'+groupByItem.value" :item="props.items[0]" :header="props.headers" :value="props.items[0][props.groupBy[0]]" :is-mobile="$refs.table.isMobile"></slot>
                                </template>
                                <template v-else-if="groupByItem.alias !== undefined">
                                    {{props.items[0][groupByItem.alias]}}
                                </template>
                                <template v-else-if="groupByItem.type == 'datetime'">
                                    {{datetimeFormat(props.items[0][groupByItem.value])}}
                                </template>
                                <template v-else-if="groupByItem.type == 'time'">
                                    {{timeFormat(props.items[0][groupByItem.value])}}
                                </template>
                                <template v-else-if="groupByItem.type == 'date'">
                                    {{dateFormat(props.items[0][groupByItem.value])}}
                                </template>
                                <template v-else-if="groupByItem.type == 'float'">
                                    {{numberFormat(props.items[0][groupByItem.value], !isNaN(parseInt(groupByItem.decimalPlace)) ? groupByItem.decimalPlace : 2)}}
                                </template>
                                <template v-else>
                                    {{props.items[0][groupByItem.value]}}
                                </template>
                                <v-icon class="sort-toggle" small style="font-size: 18px !important;">{{'mdi-arrow-up'}}
                                </v-icon>
                            </div>
                        </td>
                    </template>
                    <template v-slot:footer.page-text="props">
                        <v-table-footer :props="props" :footer-props="footerProps" :options="defaultItemsOptions" @refresh="getItems"></v-table-footer>
                    </template>
                    <template v-for="(index, name) in Object.assign(headersSlot, $scopedSlots)" :slot="name" slot-scope="data">
                        <slot :name="name" v-bind="data" v-if="index.alias !== undefined">
                            {{data.item[index.alias]}}
                        </slot>
                        <slot :name="name" v-bind="data" v-else-if="index.type == 'datetime' && [true, undefined].includes(index.autoformat)">
                            {{datetimeFormat(data.item[index.value])}}
                        </slot>
                        <template v-else-if="['text', 'ckeditor'].includes(index.type) && [true, undefined].includes(index.autoformat)">
                            <v-truncate :text="data.item[index.value]"></v-truncate>
                        </template>
                        <slot :name="name" v-bind="data" v-else-if="index.type == 'date' && [true, undefined].includes(index.autoformat)">
                            {{dateFormat(data.item[index.value])}}
                        </slot>
                        <slot :name="name" v-bind="data" v-else-if="index.type == 'time' && [true, undefined].includes(index.autoformat)">
                            {{timeFormat(data.item[index.value])}}
                        </slot>
                        <slot :name="name" v-bind="data" v-else-if="index.type == 'float' && [true, undefined].includes(index.autoformat)">
                            {{numberFormat(data.item[index.value], !isNaN(parseInt(index.decimalPlace)) ? index.decimalPlace : 2)}}
                        </slot>
                        <slot :name="name" v-bind="data" v-else-if="index.type == 'switch'">
							<v-icon v-if="checkDataSwitch(index, data) == 1" color="success">mdi-check-bold</v-icon>
							<v-icon v-else color="error">mdi-close-thick</v-icon>
                        </slot>
                        <slot :name="name" v-bind="data" v-else-if="index.type!==undefined">
                            {{data.item[index.value]}}
                        </slot>
                        <slot :name="name" v-bind="data" v-else></slot>
                    </template>
                </v-data-table>
            </div>
            <!-- Use this slot to add element after body -->
            <slot name="append-body" v-bind="{
				items: items
			}"></slot>
        </template>

		<slot></slot>

        <v-overlay v-model="loading" style="z-index: 1000"><b>Loading...</b></v-overlay>
    </v-card>
</template>

<style scoped>
    .template-table>.v-data-table__wrapper {
        border-top-left-radius: 3px;
        border-top-right-radius: 3px;
    }

    .v-template {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

	.v-template-new{
		border: 0 !important;
	}

	.table-container{
		border-radius: 6px;
	}
</style>

<script>
    module.exports = {
        name: '',
        props: {
			/** 
			 * set this to define primary key data 
			 */
			value: {
				default: undefined
			},
            name: {
                default: ''
            },
            /**
             * Page url to model
             */
            url: {
                default: ''
            },
            cardClass: {
                type: String,
                default: ''
            },
            formClass: {
                type: String,
                default: ''
            },
            formMaxWidth: {
                type: String,
                default: '500px'
            },
            formWidth: {
                type: String,
                default: '500px'
            },
            headers: {
                default: []
            },
            /**
             * Form selalus tampil
             */
            alwaysShowForm: {
                type: Boolean,
                default: false
            },
            /**
             * view data iterator
             */
            dataIterator: {
                type: Boolean,
                default: false
            },
            /**
             * Untuk hanya menampilkan add form
             */
            formAddMode: {
                type: Boolean,
                default: false
            },
            /**
             * Fullscreen mode for add dialog
             */
            dialogAddFullscreen: {
                type: Boolean,
                default: false
            },
            /**
             * Fullscreen dialog
             */
            fullscreenDialog: {
                type: Boolean,
                default: false
            },
            /**
             * Disable or enable menu
             */
            disableMenu: {
                type: Boolean,
                default: false
            },
            /**
             * hide filter button
             */
            hideFilterButton: {
                type: [Boolean, Function],
                default: false
            },
            /**
             * hide edit button
             */
            hideEditButton: {
                type: [Boolean, Function],
                default: false
            },
            /**
             * hide add button
             */
            hideAddButton: {
                type: [Boolean, Function],
                default: false
            },
            /**
             * hide delete button
             */
            hideDeleteButton: {
                type: [Boolean, Function],
                default: false
            },
            /**
             * Show only table (no crud functionality)
             */
            tableOnly: {
                type: Boolean,
                default: false
            },
            /**
             * Set delete mode
             */
            deleteMode: {
                type: String,
                default: 'delete'
            },
            /**
             * label-add
             */
            labelAdd: {
                type: String,
                default: 'Add'
            },
            /**
             * label-delete
             */
            labelDelete: {
                type: String,
                default: 'Delete'
            },
            /**
             * label-edit
             */
            labelEdit: {
                type: String,
                default: 'Edit'
            },
            disableEditButton: {
                type: Boolean,
                default: false
            },
            disableAddButton: {
                type: Boolean,
                default: false
            },
            disableDeleteButton: {
                type: Boolean,
                default: false
            },
            /**
             * disable table
             */
            disableTable: {
                type: Boolean,
                default: false
            },
            /**
             * active-column
             */
            activeColumn: {
                type: String,
                default: ''
            },
            /**
             * Hide header
             */
            hideDefaultHeader: {
                type: Boolean,
                default: false
            },
            /**
             * fixed header for table
             */
            tableFixedHeader: {
                type: Boolean,
                default: true
            },
            /**
             * Set table height
             */
            tableHeight: {
                default: 'auto'
            },
            /**
             * Page primary key
             */
            itemKey: {
                default: 'id'
            },
            /**
             * Show select checkbox
             */
            showSelect: {
                type: Boolean,
                default: true
            },
            /**
             * Single select
             */
            singleSelect: {
                type: Boolean,
                default: true
            },
            /**
             * disable table pagination
             */
            disablePagination: {
                type: Boolean,
                default: false
            },
			/**
			* table selected data index
			*/
		   selectedRowIndex: {
			   default: []
		   },
            /**
             * hide table default footer
             */
            hideDefaultFooter: {
                type: Boolean,
                default: false
            },
            /**
             * Disable table sort
             */
            disableSort: {
                type: Boolean,
                default: false
            },
            /**
             * Allow multi sort
             */
            multiSort: {
                type: Boolean,
                default: false
            },
            /**
             * Table footer props, [v-data-footer](#/admin/admin/docs/index/vuetify/api/v-data-footer)
             */
            footerProps: {
                type: Object,
                default: {
                    'items-per-page-options': [5, 10, 25, 50, 100, 200]
                }
            },
            /**
             * Show expandable button on table
             */
            showExpand: {
                type: Boolean,
                default: false
            },
            /**
             * Allow multiple expand or single expand on table expandable
             */
            singleExpand: {
                type: Boolean,
                default: false
            },
            /**
             * Set table to dense
             */
            tableDense: {
                type: Boolean,
                default: false
            },
            /**
             * Table items per page
             */
            itemsPerPage: {
                default: 10
            },
			/**
			 * set Items options
			 */
			itemsOptions: {
				type: Object,
				default: {}
			},
        },
        components: {
            'form-template': 'url:ui/admin/form-template.vue',
            'v-action-dialog': 'url:v-apps/src/lib-components/v-action-dialog.vue',
        },
        data: function() {
            return {
                //utility
                ready: false,
                loading: false,
                isInViewport: false,
                isInDom: false,
                //dialog
                dialogAdd: false,
                dialogEdit: false,
                dialogFilter: false,
                //form
                valid: false,
                formModel: [],
                validEdit: false,
                validFilter: false,
                //filter
                formFilter: [],
                presets: [],
                selectedPreset: false,
                selectedAvailableFilter: false,
                dialogSavePreset: false,
                filterPills: {},
                filterTypeSelected: {},
                filterType: {},
                filterValue: {},
				selectedField: {},
                //table
                lastInput: {},
                getItemsToken: false,
                lastSelectedRow: undefined,
                selectedRowTmp: [],
                tableHeightReal: 'auto',
                items: [],
                itemsTotal: 0,
                defaultItemsOptions: {
                    groupBy: [],
                    sortBy: [],
                    sortDesc: [true],
                    filter: {},
                    filterType: {}
                },
                //resize
                debounceTimeout: 0,
                resizeTimestamp: 0,
				filterSaveForm: [{
					"text": "Name",
					"value": "name",
					"type": "varchar",
					"required": true
				}, {
					"text": "Public",
					"value": "status",
					"type": "switch",
					"data_value": [0, 1],
					"default": 0
				}],
            }
        },
        computed: {
            headersObj: function() {
                var self = this,
                    obj = {}
                self.headers.map(function(val) {
                    if (val.value)
                        obj[val.value] = val
                })
                return obj
            },
            formFilterObj: function() {
                var self = this,
                    obj = {}
                self.formFilter.map(function(val) {
                    if (val.value)
                        obj[val.value] = val
                })
                return obj
            },
            headersFilter: function() {
                return this.headers.filter(function(val) {
                    return [undefined, false].includes(val.disabled) && [undefined, true].includes(val
                        .visible)
                })
            },
			availableFilter: function (){
				var self = this, tmp = []

				self.headers.map((val, i)=>{
					if(val.filter==undefined){
						tmp.push({
							text: val.text,
							value: [i, val.value].join(),
							//i: i
						})
					}else if(val.filter==true){
						tmp.push({
							text: val.text,
							value: [i, val.value].join(),
							//i: i
						})
					}
				})
				return tmp
			},
            headersSlot: function() {
                var self = this
                var scopedSlots = Object.keys(self.$scopedSlots)
                scopedSlots.push('item.data-table-expand')
                scopedSlots.push('item.data-table-select')
                scopedSlots.push('expanded-item')
                var tmp = {}
                self.headers.map(function(val) {
                    if (!scopedSlots.includes('item.' + val.value)) {
                        var obj = {
                            type: val.type,
                            value: val.value
                        }

                        if (val.decimalPlace !== undefined)
                            obj.decimalPlace = val.decimalPlace
                        if (val.alias !== undefined)
                            obj.alias = val.alias

                        tmp['item.' + val.value] = obj
                    }
                })
                return tmp
            },
            formAppendSlot: function() {
                var self = this
                var scopedSlots = Object.keys(self.$scopedSlots)
                var tmp = {}
                scopedSlots.map(function(val) {
                    if(val.match('form.append.')!=null)
						tmp[val] = self.$scopedSlots[val]
                })
                return tmp
            },
            formPrependSlot: function() {
                var self = this
                var scopedSlots = Object.keys(self.$scopedSlots)
                var tmp = {}
                scopedSlots.map(function(val) {
                    if(val.match('form.prepend.')!=null)
						tmp[val] = self.$scopedSlots[val]
                })
                return tmp
            },
            tableContainerStyle: function() {
                var cls = {
                    'padding': '4px',
                    'background': '#999999',
                    'overflow': 'hidden',
                    'flex': 1
                }
                return cls
            }
        },
        watch: {
			selectedPreset: function(val){
				var self = this
				if(val){
					self.clearFilter(false, false)
					self.filterValue = JSONfn.parse(val.filter)
					self.filterType = JSONfn.parse(val.filter_type)
					self.filterSaveForm[0].data = val.name
					self.filterSaveForm[1].data = val.status
					Object.keys(self.filterValue).forEach(function (key) {
						var val = self.filterValue[key]
						self.onFilterSelect(key)
					});
					self.applyFilter()
				}
			},
            headersObj: {
                immediate: true,
                handler: function() {
                    var self = this
                    self.formModelAdd = JSONfn.parse(JSONfn.stringify(self.headers)).filter(function(val) {
                        return [undefined, true].includes(val.form)
                    }).map(val=>{
						if(self.value)
							if(self.value[val.value] != undefined){
								val.default = self.value[val.value]
								val.data = self.value[val.value]
							}
						return val
					})
                }
            },
            dialogAdd: function(val) {
                var self = this
                if (val == false) {
                    self.formModelAdd = JSONfn.parse(JSONfn.stringify(self.headers)).filter(function(val) {
                        return [undefined, true].includes(val.form)
                    })
                    self.$refs.formAdd.reset()
                }
            },
			value: {
				immediate: true,
				handler: function(val){
					var self = this
					/*if(val)
						Object.keys(val).forEach(function (key) {
							var val = val[key]
							self.headersObj[key].default = val
						});*/
				}
			},
			formFilter: {
				immediate: true,
				handler: function(val){
					var self = this
				}
			},
			filter: {
				immediate: true,
				handler: function(val){
					var self = this
				}
			}
        },
        methods: {
            //event
			onResize: function(e) {
                var self = this
                clearTimeout(self.debounceTimeout)
                self.debounceTimeout = setTimeout(() => {
                    var height = 'auto'
					var el = self.$refs.tableContainer ? self.$refs.tableContainer : null
					var parentHeight = self.$el.parentElement.clientHeight;
					var $el = App.page().$el
					var bound = self.$el.getBoundingClientRect()
					if(self.tableHeight == 'fit-content'){
						self.tableHeightReal = 'auto'
					}
                    else{ 
						if (self.tableFixedHeader && self.tableHeight == 'auto') {
							var top = 0
							if (self.$refs.table)
								top = (self.$refs.table.$el
									.children[1] ? getElmHeight(self.$refs.table.$el
										.children[1]) : getElmHeight(self.$refs.table.$el.querySelectorAll(
										'thead.v-data-table-header')[0]))
						} else {
							height = self.tableHeight
						}

						if($el==self.$el){
							var hmin = 0;
							for(var i = 0; i < $el.children.length; i++){
								var child = $el.children[i]
								var style = window.getComputedStyle(child)
								
								if(child != $el.querySelector('.table-container') && !child.className.contains('v-overlay') && style.position != 'fixed' && style.position != 'absolute' && style.display != 'none'){
									hmin += child.clientHeight
								}
							}
							self.tableHeightReal = window.innerHeight-64-hmin-49-18
						}else
							self.tableHeightReal = el.clientHeight-top-8-49
					}
                }, 150)
            },
            /*onResize: function(e) {
                var self = this
                try {
                    if (e) {
                        var timeStamp = e.timeStamp
                        self.resizeTimestamp = timeStamp
                    } else {
                        var timeStamp = 0
                        self.resizeTimestamp = 0
                    }
                    //clearTimeout(self.debounceTimeout)
                    if (self.debounceTimeout == false) {
                        self.debounceTimeout = setTimeout(() => {
                            self.debounceTimeout = false
                            if (self.resizeTimestamp == timeStamp) {
                                var height = 'auto'
                                var el = self.$refs.tableContainer ? self.$refs.tableContainer : null
                                if (self.tableFixedHeader && self.tableHeight == 'auto') {
                                    var top = 0
                                    if (self.$refs.table)
                                        top = (self.$refs.table.$el
                                            .children[1] ? getElmHeight(self.$refs.table.$el
                                                .children[1]) : getElmHeight(self.$refs.table.$el.querySelectorAll(
                                                'thead.v-data-table-header')[0]))
                                } else {
                                    height = self.tableHeight
                                }

                                self.tableHeightReal = el.clientHeight - top - 8
                            }
							else{
								self.onResize({timeStamp})
							}
                        }, 250)
                    }
                } catch (err) {
                    console.log(err)
                }
            },*/
            onVisible: function(isVisible, e) {
                var self = this
                self.isInViewport = !!isVisible
                self.isInDom = !!e.target.isConnected
                if (self.isInDom) {
                    if (!self.ready && self.defaultItemsOptions.sortBy.length == 0)
                        self.defaultItemsOptions.sortBy = [self.itemKey]
                    self.getItems()
                }
            },
			get$url: function(){
				var self = this, url = false
				while(self && url === false){
					if(self.$url && self.$url != './ui/admin/page-template.vue')
						url = self.$url
					self = self.$parent
				}
				return url.length > 100 ? location.hash.split('/__')[0] : url
			},
			getFilterPresets: function () {
				var self = this
				return new Promise((resolve, reject) => {
					var target = self.get$url()
					
					axios.get(App.url + 'admin/filters/available', {
							params: {
								target: target,
								created_by: App.userData ? App.userData.data[0].username : null
							}
						})
						.then(function (response) {
							var data = response.data
							if (data.status) {
								self.presets = data.data
								resolve(self.presets)
							} else {
								throw data
							}
						})
						.catch(function (error) {
							App.errorMsg()
							reject(axios.errorParser(error))
						})
				});
			},
			clearFilter: function (getItem, clearPreset) {
				var self = this
				if (self.selectedPreset !== false && clearPreset)
					self.selectedPreset = false
				self.filterValue = {}
				self.filterType = {}
				self.filterSaveForm[0].data = null
				self.filterSaveForm[1].data = null
				if(getItem)
					self.applyFilter()
			},
			deletePreset: function () {
				var self = this

				if (confirm('Are you sure to delete this preset?')) {
					axios.delete(App.url + 'admin/filters', {
						params: {
							filter_id: self.selectedPreset.filter_id
						}
					}).then(function (val) {
						if (val.data.status) {
							self.clearFilter()
							self.selectedPreset = false
							self.dialogFilter = false
							App.successMsg()
							self.getFilterPresets()
							self.applyFilter()
						} else
							throw val.data
					}).catch(function (err) {
						App.errorMsg()
					})
				}
			},
			saveFilter: async function(){
				var self = this
				try {
					self.applyFilter()
					var target = self.get$url()
					var name = self.filterSaveForm[0].data
					if(self.selectedPreset!=false && self.selectedPreset.name.trim() == self.filterSaveForm[0].data.trim()){
						var res = await axios.put(App.url + 'admin/filters', {
							filter: JSONfn.stringify(self.filterValue),
							filter_type: JSONfn.stringify(self.filterType),
							created_by: App.userData ? App.userData.data[0].username : null,
							target: target,
							name: self.filterSaveForm[0].data,
							status: self.filterSaveForm[1].data,
							pk: self.selectedPreset.filter_id
						})
					}
					else{
						var res = await axios.post(App.url + 'admin/filters', {
							filter: JSONfn.stringify(self.filterValue),
							filter_type: JSONfn.stringify(self.filterType),
							created_by: App.userData ? App.userData.data[0].username : null,
							target: target,
							name: self.filterSaveForm[0].data,
							status: self.filterSaveForm[1].data
						})
					}
					if(!res.data.status) throw res.data
					await self.getFilterPresets()
					self.clearFilter(false, false)
					var sel = false
					self.presets.map(val=>{
						if(val.name == name)
							sel = val
					})
					App.successMsg()
					self.dialogSavePreset = false
					self.$nextTick(()=>{
						self.selectedPreset = sel
					})
				} catch (error) {
					console.log(error)
					App.errorMsg()
				}
			},
			applyFilter: function(){
				var self = this
				var filterType = {}
				var condition = {}

				//add value to filter
				Object.keys(self.selectedField).forEach(function (key) {
					var val = self.selectedField[key]
					var item = self.formFilterObj[key]
					console.log(item)
					if(item.data != undefined){
						if(item.data.toString().trim() != ''){
							self.addFilter(item.value, item.data, item)
						}
					}
				});

				Object.keys(self.filterValue).forEach(function (key) {
					var val = self.filterType[key].slice(0)
					var cond = []
					var tmp = val.map(v=>{
						var type = '='
						if(v=='is')
							type = '='
						if(v=='not')
							type = '!='
						if(v=='contains')
							type = '%'
						if(v=="doesn't contains")
							type = '!%'
						if(v=='more than')
							type = '>'
						if(v=='less than')
							type = '<'
						if(v=='between')
							type = 'btw'
						if(cond.length == 0)
							cond.push('and')
						else
							cond.push('or')
						return type
					})
					filterType[key] = tmp
					condition[key] = cond
				});
				self.defaultItemsOptions.filter = Object.assign({}, self.itemsOptions.filter, self.filterValue)
				self.defaultItemsOptions.filterType = Object.assign({}, self.itemsOptions.filterType, filterType)
				self.defaultItemsOptions.filterCondition = Object.assign({}, self.itemsOptions.filterCondition, condition)
				self.getItems()
				self.dialogFilter = false
				//self.defaultItemsOptions.filterCondition = Object.assign({}, self.itemsOptions.filterCondition, opt.filterCondition)
			},
			onFilterSelect: function(val){
				var self = this
				
				if(val!=false){
					var val = val.split(',')
					var item = self.headers[val[0]]//self.headersObj[val]
					console.log(self.availableFilter, val)
					if(self.formFilterObj[item.value]==undefined){
						self.selectedField[item.value] = true
						if(['text', 'varchar', 'ckeditor', 'auto'].includes(item.type)){
							self.formFilter.push({
								text: item.text,
								value: item.value,
								type: 'varchar',
								containerStyle: {
									"flex-wrap": "wrap"
								}
							})
							self.filterTypeSelected[item.value] = 'contains'
						}
						else if(['int', 'float'].includes(item.type)){
							self.formFilter.push({
								text: item.text,
								value: item.value,
								type: item.type,
								containerStyle: {
									"flex-wrap": "wrap"
								}
							})
							self.filterTypeSelected[item.value] = 'is'
						}
						else if(['list'].includes(item.type)){
							var t = App.updateObject(item)
							t.containerStyle = {
								"flex-wrap": "wrap"
							}
							self.formFilter.push(t)
							self.filterTypeSelected[item.value] = 'is'
						}
						//self.formFilter = App.updateObject(self.formFilter)
						//console.log(self.formFilter)
						self.$nextTick(()=>{
							self.selectedAvailableFilter = false
						})
					}
				}
			},
			onFilterType: function(val, item){
				var self = this
				self.filterTypeSelected[item.value] = val
			},
			removeFilter: function(item){
				var self = this
				self.formFilter.splice(Object.keys(self.formFilterObj).indexOf(item), 1)
				delete self.filterTypeSelected[item]
				delete self.selectedField[item]
			},
			addFilter: function(key, data, item){
				var self = this
				if(self.filterValue[key]==undefined)
					self.filterValue[key] = []
				if(self.filterType[key]==undefined)
					self.filterType[key] = []
				self.filterValue[key].push(data)
				var val = self.filterTypeSelected[key]
				self.filterType[key].push(val)
				item.data = null
				self.filterValue = App.updateObject(self.filterValue)
			},
			removeFilterValue: function(key, data){
				var self = this
				var idx = self.filterValue[key].indexOf(data)
				self.filterValue[key].splice(idx, 1)
				self.filterType[key].splice(idx, 1)
				self.filterValue = App.updateObject(self.filterValue)
				if(self.filterValue[key].length == 0){
					delete self.filterValue[key]
				}
			},
            //table event
            onToggleSelectAll: function(val) {
				var self = this
				self.selectedRowIndex = Array.from(new Set(self.selectedRowIndex))
				var row = val.items.slice(-1)[0]

				if (!val.value) {
					val.items.map(function (val) {
						var key = val[self.itemKey]
						var idx = self.selectedRowIndex.indexOf(key)
						if (idx > -1)
							self.selectedRowIndex.splice(idx, 1)
					})
					var lastPk = self.selectedRowIndex.slice(-1)
					self.lastSelectedRow = self.selectedRowTmp.find(function (row) {
						return row[self.itemKey] == lastPk[0]
					})
				} else {
					val.items.map(function (val) {
						var key = val[self.itemKey]
						if (!self.selectedRowIndex.includes(key))
							self.selectedRowIndex.push(key)
					})
					self.selectedRowIndex = Array.from(new Set(self.selectedRowIndex))
					self.lastSelectedRow = row
				}
				self.$emit('toggle-select-all', val)
			},
            onSelected: function(val) {
				var self = this
				/*self.selectedRowIndex = Array.from(new Set(self.selectedRowIndex))
				var idx = self.selectedRowIndex.indexOf(val.item[self.itemKey])*/

				/*if (idx > -1) {
					self.selectedRowIndex.splice(idx, 1)
					var lastPk = self.selectedRowIndex.slice(-1)
					self.lastSelectedRow = self.selectedRowTmp.find(function (row) {
						return row[self.itemKey] == lastPk[0]
					})
				} else {
					self.selectedRowIndex.push(val.item[self.itemKey])
					self.lastSelectedRow = val.item
				}*/

                if (self.singleSelect) {
					if(val.value == false)
						self.selectedRowTmp = []
                    else if (self.lastSelectedRow === undefined)
                        self.selectedRowTmp = [val.item]
                    else if (val.item[self.itemKey] == self.lastSelectedRow[self.itemKey])
                        self.selectedRowTmp = []
                    else
                        self.selectedRowTmp = [val.item]
                } else {
                    var f = self.selectedRowTmp.findIndex(function(row) {
                        return row[self.itemKey] == val.item[self.itemKey]
                    })
                    if (f > -1) {
                        self.selectedRowTmp.splice(f, 1)
                    } else {
                        self.selectedRowTmp.push(val.item)
                    }
                }

                self.selectedRowIndex = Array.from(new Set(self.selectedRowIndex))
                var idx = self.selectedRowIndex.indexOf(val.item[self.itemKey])

                if (idx > -1) {
                    self.selectedRowIndex.splice(idx, 1)
                    var lastPk = self.selectedRowIndex.slice(-1)
                    self.lastSelectedRow = self.selectedRowTmp.find(function(row) {
                        return row[self.itemKey] == lastPk[0]
                    })
                } else {
                    self.selectedRowIndex.push(val.item[self.itemKey])
                    self.lastSelectedRow = val.item
                }

                if (self.singleSelect) {
                    self.selectedRowIndex = self.selectedRowIndex.slice(-1)
                }

				self.$emit('item-selected', val)
				if(val.value)
					self.lastSelectedRow = val.item
				else
					self.lastSelectedRow = undefined
				if(self.selectedRowTmp[0])
                	self.$emit('update:selected-row', self.selectedRowTmp[0])
				else
                	self.$emit('update:selected-row')
			},
            onRowClick: function(val) {
                var self = this

                if (self.singleSelect) {
                    if (self.lastSelectedRow === undefined)
                        self.selectedRowTmp = [val]
                    else if (val[self.itemKey] == self.lastSelectedRow[self.itemKey])
                        self.selectedRowTmp = []
                    else
                        self.selectedRowTmp = [val]
                } else {
                    var f = self.selectedRowTmp.findIndex(function(row) {
                        return row[self.itemKey] == val[self.itemKey]
                    })
                    if (f > -1) {
                        self.selectedRowTmp.splice(f, 1)
                    } else {
                        self.selectedRowTmp.push(val)
                    }
                }

                self.selectedRowIndex = Array.from(new Set(self.selectedRowIndex))
                var idx = self.selectedRowIndex.indexOf(val[self.itemKey])

                if (idx > -1) {
                    self.selectedRowIndex.splice(idx, 1)
                    var lastPk = self.selectedRowIndex.slice(-1)
                    self.lastSelectedRow = self.selectedRowTmp.find(function(row) {
                        return row[self.itemKey] == lastPk[0]
                    })
                } else {
                    self.selectedRowIndex.push(val[self.itemKey])
                    self.lastSelectedRow = val
                }

                if (self.singleSelect) {
                    self.selectedRowIndex = self.selectedRowIndex.slice(-1)
                }

                self.$emit('click:row', val)
				if(self.selectedRowTmp[0])
                	self.$emit('update:selected-row', self.selectedRowTmp[0])
				else
                	self.$emit('update:selected-row')
            },
            onItemsPerPage: function(val) {
                this.$emit('update:items-per-page', val)
                this.getItems()
            },
            onPage: function(val) {
                this.$emit('update:page', val)
                this.getItems()
            },
            //table fn
            fnItemClass: function(val) {},
            getItems: async function() {
                var self = this
                if (self.url == false)
                    return
                if (self.defaultItemsOptions.sortBy === undefined)
                    self.defaultItemsOptions.sortBy = []

                if (self.defaultItemsOptions.sortBy.length == 0 && !self.ready)
                    return
                try {
                    try {
                        if (self.getItemsToken)
                            self.getItemsToken.cancel('canceled');
                    } catch (error) {

                    }

                    self.tableLoading = true;
                    if (self.disablePagination)
                        self.defaultItemsOptions.limit = -1

                    var params = vTableParam(deepAssign({}, self.itemsOptions, self.defaultItemsOptions))
                    var opt = {
                        url: self.url,
                        params: params
                    }

                    if (self.$listeners['before-get-items']) {
                        await Promise.resolve(self.$listeners['before-get-items'](opt))
                    }

					if(self.value)
						Object.keys(self.headersObj).forEach(function (key) {
							var val = self.headersObj[key]
							if(self.value[key]!==undefined){
								params.filter[key] = self.value[key]
							}
						});

                    self.getItemsToken = axios.CancelToken.source();

                    var response = await axios.get(App.url + opt.url, {
                        params: opt.params,
                        cancelToken: self.getItemsToken.token
                    })

                    var data = response.data
                    if (data.status == false)
                        throw data.data

                    if (Object.keys(self.lastInput).length == 0 && data.data.length > 0) {
                        self.lastInput = data.data[data.data.length - 1]
                    }

                    /**
                     * check apakah yg terselect ada di data, jika tidak ada, jika ada diganti dengan data terbaru
                     */
                    if (self.selectedRowTmp.length > 0) {
                        var selected = []
                        // mendapatkan data pk dari semua selected row
                        var itemKeys = self.selectedRowTmp.map(function(val) {
                            return val[self.itemKey]
                        })
                        var tmpLastSelectedRow = undefined
                        data.data.map(function(val, i) {
                            //jika menemukan data dengan pk yg sama, ganti dengan data baru
                            if (itemKeys.includes(val[self.itemKey])) {
                                self.selectedRowTmp[itemKeys.indexOf(val[self.itemKey])] = val
                                if (self.lastSelectedRow)
                                    if (self.lastSelectedRow[self.itemKey] == val[self.itemKey])
                                        tmpLastSelectedRow = val
                            }
                        })
                        self.lastSelectedRow = tmpLastSelectedRow
                    }
                    if (self.lastSelectedRow !== undefined) {
                        var tmpLastSelectedRow = undefined
                        data.data.map(function(val, i) {
                            if (self.lastSelectedRow[self.itemKey] == val[self.itemKey])
                                tmpLastSelectedRow = val
                        })
                        if (self.singleSelect == false) {
                            if (tmpLastSelectedRow !== undefined)
                                self.lastSelectedRow = tmpLastSelectedRow
                        } else
                            self.lastSelectedRow = tmpLastSelectedRow
                        if (tmpLastSelectedRow !== undefined && self.selectedRowTmp.length == 0)
                            self.selectedRowTmp = [tmpLastSelectedRow]
                    }

                    if (self.formAddMode && data.data.length > 0) {
                        self.lastSelectedRow = data.data[0]
                        self.selectedRowTmp = [data.data[0]]
                        self.tmpAdd = self.selectedRowTmp.slice()
                        var pkHeader = arraySearchByKey(self.headers, 'value', self.itemKey)
                        if (self.value !== undefined) {
                            pkHeader.data = self.value
                        } else {
                            pkHeader.data = undefined
                        }

                        self.headers.map(function(val) {

                            val.data = val.data = self.tmpAdd[0][val.value] || undefined
                        })
                        self.$nextTick(function() {
                            self.headers = App.updateObject(self.headers)
                        })
                    }
                    /**
                     * set items dan total
                     */
                    self.items = data.data
                    self.itemsTotal = data.total
                    self.tableLoading = false
                    self.ready = true
                    /* self.$nextTick(function(){
                    	var tbody = self.$refs.table.$el.querySelectorAll('tbody')[0]
                    	self.headersFilter.map(function(header){
                    		if(header.cell)
                    	})
                    	console.log(tbody, )
                    }) */
                    self.$emit('after-get-items', data)
                } catch (err) {
                    var canceled = false
                    if (err.message)
                        if (err.message == 'canceled')
                            canceled = true
                    if (!canceled) {
                        App.errorMsg()
                        self.tableLoading = false
                        self.$emit('after-get-items', err)
                    }
                }
            },
            autoRowspan: async function(val) {},
            tableListeners: function() {},
            //fn
			checkDataSwitch: function(index, data){
				var self = this
				if(self.headersObj[index.value].data_value == undefined){
					var value = data.item[index.value]
				}
				else {
					if(self.headersObj[index.value].data_value.length > 0)
						var value = self.headersObj[index.value].data_value.indexOf(data.item[index.value])
					else
						var value = data.item[index.value]
				}
				return value
			},
            save: async function() {
                var self = this
                var params = {};
                try {
					if(self.value){
						var fields = Object.keys(self.headersObj)
						Object.keys(self.value).forEach(function (key) {
							if(fields.includes(key)){
								var val = self.value[key]
								params[key] = val
							}
						});
					}
					
                    self.formModelAdd.map(function(data) {
                        if (data.data !== undefined) {
                            params[data.value] = data.data
                        }
                    })
                    if (self.haveCreatedBy)
                        params.created_by = App.userData ? App.userData.data[0].username : null

                    if (self.$listeners.save) {
                        self.$emit('save', params)
                    } else {
                        self.tableLoading = true
                        self.saveLoading = true
                        var opt = {
                            url: self.url,
                            params: params
                        }
                        if (self.$listeners['before-save']) {
                            await Promise.resolve(self.$listeners['before-save'](opt))
                        }
                        self.lastInput = opt.params
                        var response = await axios.post(App.url + opt.url, opt.params)
                        var data = response.data
                        if (data.status) {
                            if (!self.$listeners['save-notification'])
                                App.successMsg()
                            else
                                self.$emit('save-notification', null, response.data)

                            if (!self.formAddMode && !self.alwaysShowForm)
                                self.dialogAdd = false
                            if (self.alwaysShowForm)
                                self.clearAddForm()
                            self.getItems()
                        } else {
                            throw data
                        }
                        self.tableLoading = false
                        self.saveLoading = false
                        self.$emit('after-save', null, response.data)
                    }
                } catch (err) {
                    if (!self.$listeners['save-notification'])
                        App.errorMsg(err)
                    else
                        self.$emit('save-notification', axios.errorParser(err), err.data || null)
                    self.tableLoading = false
                    self.saveLoading = false
                    self.$emit('after-save', axios.errorParser(err), err.data || null)
                }
            },
            saveEdit: async function() {
                const c = confirm("Apakah anda yakin?");
                if (!c) return;
      
                var self = this
                try {
                    var params = {};
                    self.formModel.map(function(data) {
                        if (data.data !== undefined && data.form !== false) {
                            if (data.value == self.itemKey) {
                                //params['pk'] = data.data !== undefined ? data.data : ''
                                var pkValue = data.data !== undefined ? data.data : ''
                                if (self.pkValue != pkValue)
                                    params[data.value] = data.data !== undefined ? data.data : ''
                                params['pk'] = self.pkValue
                            } else {
                                params[data.value] = data.data !== undefined ? data.data : ''
                            }
                        }
                    })

                    try {
                        if (params['pk'] == undefined)
                            params['pk'] = self.selectedRowTmp[0][self.itemKey]
                    } catch (err) {

                    }

                    if (self.haveModifiedBy)
                        params.modified_by = App.userData ? App.userData.data[0].username : null

                    if (self.$listeners.edit) {
                        self.$emit('edit', params)
                    } else {
                        self.loading = true
                        var opt = {
                            url: self.url,
                            params: params
                        }
                        if (self.$listeners['before-edit']) {
                            await Promise.resolve(self.$listeners['before-edit'](opt))
                        }
                        self.lastInput = opt.params
                        var response = await axios.put(App.url + opt.url, opt.params)
                        var data = response.data
                        if (data.status) {
                            if (!self.$listeners['edit-notification'])
                                App.successMsg()
                            else
                                self.$emit('edit-notification', null, response.data)
                            self.dialogEdit = false
                            self.getItems()
                        } else {
                            throw data
                        }
                        self.loading = false
                        self.$emit('after-edit', null, response.data)
                    }
                } catch (err) {
                    if (!self.$listeners['edit-notification'])
                        App.errorMsg()
                    else
                        self.$emit('edit-notification', axios.errorParser(err), err.data || null)
                    self.loading = false
                    self.$emit('after-edit', axios.errorParser(err), err.data || null)
                }
            },
            openEdit: function() {
                var self = this,
                    selected = self.lastSelectedRow
					
                self.$emit('open-edit')
				
                self.pkValue = selected[self.itemKey] || ''
                var tmp = self.headers.slice().filter(function(val) {
                    return [undefined, true].includes(val.form)
                }).map(function(val) {
                    if (self.lastSelectedRow !== undefined) {
                        if (selected[val.value] !== undefined)
                            val.data = selected[val.value]
                        else
                            val.data = undefined
                    }
                    return val;
                })

				self.formModel = tmp

				//console.log(self.$refs)
				//self.$refs.formEdit.reset()

                self.dialogEdit = true
            },
            openAdd: function() {
                var self = this
                self.$emit('open-add')
                self.dialogAdd = true
            },
            deleteData: async function () {
				var self = this
				try {
					var params = {}
					params[self.itemKey] = self.selectedRowTmp[0][self.itemKey] //[0][self.itemKey]
					if(self.deleteMode == 'active')
						params.flag = self.lastSelectedRow[self.activeColumn] == 1 ? 0 : 1
					if (self.haveModifiedBy)
						params.modified_by = App.userData ? App.userData.data[0].username : null

					if (self.$listeners.delete) {
						self.$emit('delete', params)
					} else {
						var c = confirm(lang.areYouSureDeleteData)
						if (c) {
							self.tableLoading = true
							var opt = {
								url: self.url,
								params: params
							}
							if (self.$listeners['before-delete']) {
								await Promise.resolve(self.$listeners['before-delete'](opt))
							}
							var response = await axios.delete(App.url + opt.url, {
								params: opt.params
							})
							var data = response.data
							if (data.status) {
								if (!self.$listeners['delete-notification'])
									App.successMsg(self.deleteMode == 'active' ? lang.dataSaved : lang.dataDeleted)
								else
									self.$emit('delete-notification', null, response.data)
								/** delete data dari selectedRow */
								var f = self.selectedRowTmp.findIndex(function (val) {
									return val[self.itemKey] == params[self.itemKey]
								})
								if (f > -1 && self.deleteMode == 'delete'){
									self.selectedRowTmp.splice(f, 1)
									self.selectedRowTmp = [].concat(self.selectedRowTmp)
								}
								
								self.getItems()
							} else {
								throw data
							}
							self.tableLoading = false
							self.$emit('after-delete', null, response.data)
						}
					}
				} catch (err) {
					if (!self.$listeners['delete-notification'])
						App.errorMsg()
					else
						self.$emit('delete-notification', axios.errorParser(err), err.data || null)
					self.tableLoading = false
					self.$emit('after-delete', axios.errorParser(err), err.data || null)
				}
			},

        },
        mounted: function() {
            var self = this
			self.getFilterPresets()
        }
    }
</script>