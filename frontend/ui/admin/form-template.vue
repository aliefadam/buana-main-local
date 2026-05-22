<template>
	<v-form ref="form" v-model="valid" v-if="ready" v-observe-visibility="onVisible">
		<div class="flex" style="flex-wrap: wrap; gap: 16px;">
			<template v-for="(item, index) in headers">
				<div @click="emitFocus(item)" :style="style(item)" :key="'form-'+index">
					<slot :name="item.value+'.prepend'" v-bind="{
						items: headers
					}"></slot>
					<div :style="checkIfAuto(item)">
						<v-subheader :style="labelStyle" v-if="leftLabel">{{item.text}}<span class="red--text" v-if="item.required"><strong>* </strong></span></v-subheader>

						<template v-if="item.type == 'textnode'">
							<span>{{item.text}}</span>
						</template>

						<template v-else-if="item.type == 'header'">
							<span>{{item.text}}</span>
							<v-divider style="max-width: 100% !important;"></v-divider>
						</template>

						<template v-else-if="item.type == '-'">
							<v-divider style="max-width: 100% !important;"></v-divider>
						</template>

						<template v-else-if="item.type == 'spacer'">
							<v-spacer></v-spacer>
						</template>

						<template v-else-if="item.type == 'ckeditor'">
							<ckeditor :editor="ckver" v-model="item.data" :config="item.config || ckconfig"></ckeditor>
						</template>

						<template v-else-if="item.type == 'button'">
							<v-btn :color="item.color" @click="item.click(item)" :outlined="item.outlined" :small="(item.small || false)" :ref="item.value"><template v-if="item.icon!=undefined">
									<v-icon small left>{{item.icon}}</v-icon>&nbsp;
								</template>{{item.text}}</v-btn>
						</template>

						<template v-else-if="item.type == 'icon'">
							<v-icon small @click="item.click(item)">{{item.icon}}</v-icon>
						</template>

						<template v-else-if="item.type == 'varchar'">
							<v-text-field :hint="item.hint ? item.hint : false" :persistent-hint=" item.hint ? true : false" :suffix="item.suffix ? item.suffix : ''" :prefix="item.prefix ? item.prefix : ''" :label="leftLabel ? '' : item.text" v-model="item.data" :hide-details="hideDetails" :readonly="item.readonly||false" :rules=" item.required ? [].concat(requiredRules, getRules(item.rules)) : getRules(item.rules)" :filled="filled" :dense="dense" :required="item.required || false" @input="emitValue(item, index)" @click="emitFocus(item)" @click:clear="emitValueClear(item)" :clearable="item.clearable||false" @keyup.enter="function(e){emitEnter(item, e)}" :ref="item.value">
								<template #label v-if="leftLabel !== true">
									<span class="red--text" v-if="item.required"><strong>* </strong></span>{{item.text}}
								</template>
							</v-text-field>
						</template>

						<template v-else-if="item.type == 'text'">
							<v-textarea :hint="item.hint ? item.hint : false" :persistent-hint=" item.hint ? true : false" :label="leftLabel ? '' : item.text" v-model="item.data" :hide-details="hideDetails" :readonly="item.readonly||false" :rules=" item.required ? [].concat(requiredRules, getRules(item.rules)) : getRules(item.rules)" :filled="filled" :dense="dense" :required="item.required || false" @input="emitValue(item, index)" :rows="item.rows || 5" @click="emitFocus(item)" @click:clear="emitValueClear(item)" :clearable="item.clearable||false" :ref="item.value">
								<template #label v-if="leftLabel !== true">
									<span class="red--text" v-if="item.required"><strong>* </strong></span>{{item.text}}
								</template>
							</v-textarea>
						</template>

						<template v-else-if="item.type == 'markdown'">
							<v-textarea :hint="item.hint ? item.hint : false" :persistent-hint=" item.hint ? true : false" :label="leftLabel ? '' : item.text" v-model="item.data" :hide-details="hideDetails" :readonly="item.readonly||false" :rules=" item.required ? [].concat(requiredRules, getRules(item.rules)) : getRules(item.rules)" :filled="filled" :dense="dense" :required="item.required || false" @input="emitValue(item, index)" :rows="item.rows || 5" @click="emitFocus(item)" @click:clear="emitValueClear(item)" :clearable="item.clearable||false" :ref="item.value">
								<template #label v-if="leftLabel !== true">
									<span class="red--text" v-if="item.required"><strong>* </strong></span>{{item.text}}
								</template>
							</v-textarea>
						</template>

						<template v-else-if="item.type == 'int'">
							<v-text-field :hint="item.hint ? item.hint : false" :persistent-hint=" item.hint ? true : false" :suffix="item.suffix ? item.suffix : ''" :prefix="item.prefix ? item.prefix : ''" type="text" :label="leftLabel ? '' : item.text" v-model="item.data" :hide-details="hideDetails" :readonly="item.readonly||false" :rules=" item.required ? [].concat(requiredRules, getRules(item.rules)) : getRules(item.rules)" :filled="filled" :dense="dense" :required="item.required || false" @input="emitValue(item, index)" @keypress="intKeypress" @click="emitFocus(item)" @click:clear="emitValueClear(item)" @keyup.enter="function(e){emitEnter(item, e)}" :clearable="item.clearable||false" :ref="item.value">
								<template #label v-if="leftLabel !== true">
									<span class="red--text" v-if="item.required"><strong>* </strong></span>{{item.text}}
								</template>
							</v-text-field>
						</template>

						<template v-else-if="item.type == 'float'">
							<v-text-field :hint="item.hint ? item.hint : false" :persistent-hint=" item.hint ? true : false" :suffix="item.suffix ? item.suffix : ''" :prefix="item.prefix ? item.prefix : ''" type="text" :label="leftLabel ? '' : item.text" v-model="item.data" :hide-details="hideDetails" :readonly="item.readonly||false" :rules=" item.required ? [].concat(requiredRules, getRules(item.rules)) : getRules(item.rules)" :filled="filled" :dense="dense" :required="item.required || false" :precision="item.precision||2" @input="emitValue(item, index)" @keypress="floatKeypress(item, $event)" @click="emitFocus(item)" @click:clear="emitValueClear(item)" @keyup.enter="function(e){emitEnter(item, e)}" :clearable="item.clearable||false" :ref="item.value">
								<template #label v-if="leftLabel !== true">
									<span class="red--text" v-if="item.required"><strong>* </strong></span>{{item.text}}
								</template>
							</v-text-field>
						</template>

						<template v-else-if="item.type == 'list' && typeof item.url == 'string' && item.paging && !item.combo">
							<v-select-paging :hint="item.hint ? item.hint : false" :persistent-hint=" item.hint ? true : false" @focus="selected = index; selectedObj = item.value" :label="leftLabel ? '' : item.text" :items="item.data_value" v-model="item.data" :hide-details="hideDetails" :readonly="item.readonly||false" :rules=" item.required ? [].concat(requiredRules, getRules(item.rules)) : getRules(item.rules)" :filled="filled" :dense="dense" :required="item.required || false" :search-input.sync="searches[item.value]" @prev="prev" @next="next" :total="item.total" :page="item.page" @input="emitValue(item, index)" @click="emitFocus(item)" @click:clear="emitValueClear(item)" :clearable="item.clearable||false" :ref="item.value" :url="true">
								<template #label v-if="leftLabel !== true">
									<span class="red--text" v-if="item.required"><strong>* </strong></span>{{item.text}}
								</template>
							</v-select-paging>
						</template>

						<template v-else-if="item.type == 'list' && typeof item.url != 'string'">
							<v-autocomplete :hint="item.hint ? item.hint : false" :persistent-hint=" item.hint ? true : false" :menu-props="getAutocompleteMenuProps(item)"
								@focus="selected = index; selectedObj = item.value" :label="leftLabel ? '' : item.text"
								:items="item.data_value" v-model="item.data" :hide-details="hideDetails"
								:readonly="item.readonly||false" :rules=" item.required ? [].concat(requiredRules, getRules(item.rules)) : getRules(item.rules)" :filled="filled"
								:dense="dense" :required="item.required || false" 
								@input="emitValue(item, index)" @click="emitFocus(item)" @click:clear="emitValueClear(item)" :clearable="item.clearable||false" :total="item.data_value.length"
								:ref="item.value">
								<template #label v-if="leftLabel !== true">
									<span class="red--text" v-if="item.required"><strong>* </strong></span>{{item.text}}
								</template>
							</v-autocomplete>
						</template>

						<template v-else-if="['date', 'date2'].includes(item.type)">
							<v-date-input :allowed-dates="item.alloweddates" :hint="item.hint ? item.hint : false" :persistent-hint=" item.hint ? true : false" :label="leftLabel ? '' : item.text" v-model="item.data" :hide-details="hideDetails" :readonly="item.readonly||false" :rules=" item.required ? [].concat(requiredRules, getRules(item.rules)) : getRules(item.rules)" :filled="filled" :dense="dense" :required="item.required || false" @input="emitValue(item, index)" @click="emitFocus(item)" @click:clear="emitValueClear(item)" :clearable="item.clearable||false" :ref="item.value">
								<template #label v-if="leftLabel !== true">
									<span class="red--text" v-if="item.required"><strong>* </strong></span>{{item.text}}
								</template>
							</v-date-input>
						</template>

						<template v-else-if="['datemonth'].includes(item.type)">
							<v-date-input :allowed-dates="item.alloweddates" :hint="item.hint ? item.hint : false" :persistent-hint=" item.hint ? true : false" :label="leftLabel ? '' : item.text" v-model="item.data" :hide-details="hideDetails" :readonly="item.readonly||false" :rules=" item.required ? [].concat(requiredRules, getRules(item.rules)) : getRules(item.rules)" :filled="filled" :dense="dense" :required="item.required || false" type="month" @input="emitValue(item, index)" @click="emitFocus(item)" @click:clear="emitValueClear(item)" :clearable="item.clearable||false" :ref="item.value">
								<template #label v-if="leftLabel !== true">
									<span class="red--text" v-if="item.required"><strong>* </strong></span>{{item.text}}
								</template>
							</v-date-input>
						</template>

						<template v-else-if="item.type == 'time'">
							<v-time-input :simple="item.simple !== undefined ? item.simple : false" :hint="item.hint ? item.hint : false" :persistent-hint=" item.hint ? true : false" :label="leftLabel ? '' : item.text" v-model="item.data" :hide-details="hideDetails" :readonly="item.readonly||false" :rules=" item.required ? [].concat(requiredRules, getRules(item.rules)) : getRules(item.rules)" :filled="filled" :dense="dense" :required="item.required || false" :format="item.format || 'ampm'" :allowed-minutes="item.allowedMinutes" :allowed-hours="item.allowedHours" @input="emitValue(item, index)" @click="emitFocus(item)" @click:clear="emitValueClear(item)" :clearable="item.clearable||false" :ref="item.value">
								<template #label v-if="leftLabel !== true">
									<span class="red--text" v-if="item.required"><strong>* </strong></span>{{item.text}}
								</template>
							</v-time-input>
						</template>

						<template v-else-if="item.type == 'color'">
							<v-color-input :hint="item.hint ? item.hint : false" :persistent-hint=" item.hint ? true : false" :label="leftLabel ? '' : item.text" v-model="item.data" :hide-details="hideDetails" :readonly="item.readonly||false" :rules=" item.required ? [].concat(requiredRules, getRules(item.rules)) : getRules(item.rules)" :filled="filled" :dense="dense" :required="item.required || false" @input="emitValue(item, index)" flat :ref="item.value" @click="emitFocus(item)">
								<template #label v-if="leftLabel !== true">
									<span class="red--text" v-if="item.required"><strong>* </strong></span>{{item.text}}
								</template>
							</v-color-input>
						</template>

						<template v-else-if="item.type == 'datetime'">
							<v-datetime-input :hint="item.hint ? item.hint : false" :persistent-hint=" item.hint ? true : false" :label="leftLabel ? '' : item.text" v-model="item.data" :hide-details="hideDetails" :readonly="item.readonly == null ? false : item.readonly" :rules=" item.required ? [].concat(requiredRules, getRules(item.rules)) : getRules(item.rules)" :filled="filled" :dense="dense" :required="item.required || false" :format="item.format || 'ampm'" @input="emitValue(item, index)" @click="emitFocus(item)" @click:clear="emitValueClear(item)" :clearable="item.clearable||false" :ref="item.value">
								<template #label v-if="leftLabel !== true">
									<span class="red--text" v-if="item.required"><strong>* </strong></span>{{item.text}}
								</template>
							</v-datetime-input>
						</template>

						<template v-else-if="item.type == 'datetimesimple'">
							<v-datetime-input :simple="true" :hint="item.hint ? item.hint : false" :persistent-hint=" item.hint ? true : false" :label="leftLabel ? '' : item.text" v-model="item.data" :hide-details="hideDetails" :readonly="item.readonly||false" :rules=" item.required ? [].concat(requiredRules, getRules(item.rules)) : getRules(item.rules)" :filled="filled" :dense="dense" :required="item.required || false" :format="item.format || 'ampm'" @input="emitValue(item, index)" @click="emitFocus(item)" @click:clear="emitValueClear(item)" :clearable="item.clearable||false" :ref="item.value">
								<template #label v-if="leftLabel !== true">
									<span class="red--text" v-if="item.required"><strong>* </strong></span>{{item.text}}
								</template>
							</v-datetime-input>
						</template>
						
						<template v-else-if="item.type == 'switch'">
							<v-switch :hint="item.hint ? item.hint : false" :persistent-hint=" item.hint ? true : false" :label="leftLabel ? '' : item.text" v-model="item.tmp_data" :hide-details="hideDetails"
								:readonly="item.readonly||false" :rules=" item.required ? [].concat(requiredRules, getRules(item.rules)) : getRules(item.rules)" :filled="filled"
								:dense="item.dense != undefined ? item.dense : dense" :required="item.required || false" :style="'max-width: 300px; align-items: center; margin: 0; min-height: 52px;'+item.style"
								@change="cbChange(item, index)" @input="emitValue()" @click="emitFocus(item)" @click:clear="emitValueClear(item)" :clearable="item.clearable||false" :ref="item.value">
								<template #label v-if="leftLabel !== true">
									<span class="red--text" v-if="item.required"><strong>* </strong></span>{{item.text}}
								</template>
							</v-switch>
						</template>

						<template v-else>
							{{item.type}}
						</template>
					</div>
					<slot :name="item.value+'.append'" v-bind="{
						items: headers
					}"></slot>
				</div>
			</template>
		</div>
	</v-form>
</template>

<style>
	.ck-editor__editable {
        min-height: 100px;
    }
	.ck-sticky-panel__content,
	.ck-sticky-panel__content > .ck-toolbar{
		min-height: 40px;
	}
</style>

<script>
    module.exports = {
        name: '',
        props: {
            value: {
                default: {}
            },
            headers: {
                default: []
            },
            pk: {
                default: null
            },
            leftLabel: {
                type: Boolean,
                default: false
            },
            leftLabelWidth: {
                default: '100px'
            },
            hideDetails: {
                type: Boolean,
                default: true
            },
            dense: {
                type: Boolean,
                default: false
            },
            filled: {
                type: Boolean,
                default: true
            },
			valid: {
				default: false
			},
        },
        components: {
            /* ,
            			'v-code-editor': 'url:component/v-code-editor.vue' */
        },
        data: function() {
            return {
                requiredRules: [function(v) {
                    return ((![null, undefined].includes(v) ? v : '').toString().trim().length > 0 && ![null,
                        undefined
                    ].includes(v)) || 'Required!'
                }],
                defaultStyle: {
                    "width": "auto"
                },
				ckver: ClassicEditor,
                ckconfig: {
					//plugins: [ Bold, Italic, Underline, Strikethrough, Code, Subscript, Superscript ],
					toolbar: {
						items: ['undo', 'redo', 'heading', 'bold', 'italic', 'underline', 'strikethrough', 'subscript', 'superscript',  '-', 
						'fontfamily', 'fontsize', 'fontColor', 'fontBackgroundColor',
						'alignment', 'bulletedList', 'numberedList', 'todoList', 'outdent', 'indent', 'blockQuote', '-',
						'specialCharacters', 'horizontalLine',
						'link', 'codeBlock', 'insertTable', 'mediaEmbed', 'sourceEditing'
						],
    					shouldNotGroupWhenFull: true
					},
					fontColor: {
						colorPicker: {
							format: 'hex'
						},
						colors: [{
							color: '#000000',
                    		label: 'Black'
						},
						{
							color: '#4d4d4d',
							label: 'Dim grey'
						},
						{
							color: '#999999',
							label: 'Grey'
						},
						{
							color: '#e6e6e6',
							label: 'Light grey'
						},
						{
							color: '#ffffff',
							label: 'White',
							hasBorder: true
						},
						{
							color: '#e64c4c',
							label: 'Red'
						},
						{
							color: '#e6994c',
							label: 'Orange'
						},
						{
							color: '#e6e64c',
							label: 'Yellow'
						},
						{
							color: '#99e64c',
							label: 'Light green'
						},
						{
							color: '#4ce64c',
							label: 'Green'
						},
						{
							color: '#4ce699',
							label: 'Aquamarine'
						},
						{
							color: '#4ce6e6',
							label: 'Turquoise'
						},
						{
							color: '#4c99e6',
							label: 'Light blue'
						},
						{
							color: '#4c4ce6',
							label: 'Blue'
						},
						{
							color: '#994ce6',
							label: 'Purple'
						}]
					},
					fontBackgroundColor: {
						colorPicker: {
							format: 'hex'
						},
						colors: [{
							color: '#000000',
                    		label: 'Black'
						},
						{
							color: '#4d4d4d',
							label: 'Dim grey'
						},
						{
							color: '#999999',
							label: 'Grey'
						},
						{
							color: '#e6e6e6',
							label: 'Light grey'
						},
						{
							color: '#ffffff',
							label: 'White',
							hasBorder: true
						},
						{
							color: '#e64c4c',
							label: 'Red'
						},
						{
							color: '#e6994c',
							label: 'Orange'
						},
						{
							color: '#e6e64c',
							label: 'Yellow'
						},
						{
							color: '#99e64c',
							label: 'Light green'
						},
						{
							color: '#4ce64c',
							label: 'Green'
						},
						{
							color: '#4ce699',
							label: 'Aquamarine'
						},
						{
							color: '#4ce6e6',
							label: 'Turquoise'
						},
						{
							color: '#4c99e6',
							label: 'Light blue'
						},
						{
							color: '#4c4ce6',
							label: 'Blue'
						},
						{
							color: '#994ce6',
							label: 'Purple'
						}]
					}
				},
                searches: {},
                cancelToken: {},
                total: 0,
                page: 1,
                selectedItem: false,
                ready: false,
                isInViewport: false,
                isInDom: false,
				valueChange: false,
				preparing: false,
				idx: 0,
				headersObj: {}
            }
        },
        computed: {
            computedSearches: function() {
                return Object.assign({}, this.searches)
            },
            /*headersObj: function() {
                var self = this,
                    obj = {}
                self.headers.map(function(val) {
                    if (val.value)
                        obj[val.value] = val
                })
                return obj
            },*/
			computedValues: function(){
                var self = this,
                    obj = {}
                self.headers.map(function(val) {
                    if (val.value)
                        obj[val.value] = val.data
                })
                return obj
			}
        },
        watch: {
            computedSearches: {
                deep: true,
                handler: function(n, o) {
                    var self = this
                    Object.keys(o).forEach(function(key) {
                        var val = o[key]
                        var val2 = n[key]

                        if (val != undefined && val2 != undefined)
                            if (val != val2) {
                                self.listDataGetter(self.headersObj[key])
                            }
                    });
                    //this.getData()
                }
            },
			computedValues: function(n, o){
				if(this.ready){
					if(JSON.stringify(n)!=JSON.stringify(o))
						this.$emit('input', n)
				}
			},
			valid: function () {
				this.$emit('update:valid', this.valid)
			},
			headers: {
				immediate: true,
				deep: true,
				handler: function(){
					var self = this,
                    obj = {}
					self.headers.map(function(val) {
						if (val.value)
							obj[val.value] = val
					})
					self.headersObj = obj
					if(!self.valueChange && !self.preparing){
						//self.prepareForm()
					}else{
						self.valueChange = false
					}
				}
			},
			headersObj: {
				deep: true,
				handler: function(n, o){
					var self = this
					self.headers.map(function(val, i){
						if(o[val.value] == undefined && n[val.value] != undefined){
							if(val.type == 'switch'){
								val.tmp_data = val.data
							}
						}
						else if(n[val.value].data != o[val.value].data)
							if(val.type == 'switch'){
								val.tmp_data = val.data
							}
					})
				}
			}
        },
        methods: {
            getAutocompleteMenuProps: function(item) {
                var defaults = {
                    auto: false,
                    overflowY: true,
                    maxHeight: 320
                }
                if (item && item.menuProps && typeof item.menuProps == 'object') {
                    return Object.assign({}, defaults, item.menuProps)
                }
                return defaults
            },
            emitValue: function(item, index) {
                var self = this
				self.valueChange = true
                if (item.type == 'list' && typeof item.url == 'string' && item.paging && !item.combo) {
                    self.listValueSelect(item)
                }

				if(item.input){
					item.input(item)
				}
            },
            emitEnter: function(item, e) {

            },
            emitFocus: function(item) {
                this.selectedItem = item
            },
            emitValueClear: function(item) {

            },
			cbChange: function (item, index) {
				item.data = item.data_value[(item.tmp_data == true ? 1 : 0)]
				this.emitValue(item, index)
			},
            checkIfAuto: function(item) {
                var self = this
                var data = App.updateObject(self.defaultStyle)
                var applied = ["width"]
				var tmp = {
					flex: '1'
				}
                applied.map(function(val) {
					var key = val
					if(val == 'width')
						key = 'formWidth'
                    if (item[key])
                        data[val] = item[key]
                })
                if (data.width == 'auto')
                    tmp.width= 'auto'
					
				
				return tmp
            },
            style: function(item) {
                var self = this
                var data = App.updateObject(self.defaultStyle)
                var applied = ["width"]
                applied.map(function(val) {
					var key = val
					if(val == 'width')
						key = 'formWidth'
                    if (item[key])
                        data[val] = item[key]
                })
                if (item.inline) {} else if (data.width == 'auto') {
                    data.width = '100%'
                    data.display = 'flex'
                }
				data.display = 'flex'
				data = Object.assign(data, item.containerStyle)
                return data
            },
            getRules: function(rules) {
                var self = this
                if (rules)
                    return rules.map(function(fn) {
                        return function(val) {
                            return fn(val, self.value)
                        }
                    })
                else
                    return []
            },
            intKeypress: function($event) {
                var keyCode = $event.keyCode ? $event.keyCode : $event.which;
                // if ((keyCode < 48 || keyCode > 57) && keyCode !== 46) {
                if ((keyCode < 48 || keyCode > 57) && keyCode != 8) {
                    // 46 is dot
                    $event.preventDefault();
                }
            },
            //data getter
            prev: function(page) {
                this.selectedItem.page = page
                this.listDataGetter(this.selectedItem)
            },
            next: function(page) {
                this.selectedItem.page = page
                this.listDataGetter(this.selectedItem)
            },
            listValueSelect: function(item) {
                if (item.data_selected)
                    if (item.data != item.data_selected.value) {
                        item.data_value.shift()
                        //item.data_value = App.updateObject(item.data_value)
                        item.data_selected = null
                    }
            },
            listDataFormater: function(datas, item) {
                var data_value = []
                if (item.formatter) {
                    var formatter = item.formatter
                    if (Array.isArray(item.formatter)) {
                        //typeof data.formatter[1] == 'string' ? 
                        var text = function(val) {
                            return val[item.formatter[1]]
                        }

                        if (Array.isArray(item.formatter[1])) {
                            text = function(val) {
                                return item.formatter[1].map(function(key) {
                                    return val[key]
                                }).join()

                                //return tmp.join()
                            }
                        }

                        formatter = function(val) {
                            return {
                                text: text(val),
                                value: val[item.formatter[0]]
                            }
                        }
                    }
                    if (datas) {
                        if (Array.isArray(datas)) {
                            data_value = data_value.concat(
                                datas.map(function(val) {
                                    return formatter(val)
                                })
                            )
                        } else
                            throw datas
                    }
                } else {
                    data_value = data_value.concat(datas.data)
                }
                return data_value
            },
            listValueChecker: async function(item) {
                try {
                    var self = this,
                        found = -1,
                        tokenValue = item.value + item.url + 'value'
                    var options = {
                        filter: {},
                        filterType: {},
                        filterCondition: {}
                    }

                    if (item.options) {
                        options = JSON.parse(JSON.stringify(item.options))
                    }

                    if (item.pk !== undefined) {
                        options.filter[item.pk] = item.data
                    } else if (Array.isArray(item.formatter)) {
                        options.filter[item.formatter[0]] = item.data
                    } else if (Array.isArray(item.searchby)) {
                        options.filter[item.searchby[0]] = item.data
                    } else {
                        options.filter[item.pk || item.value] = item.data
                    }

                    for (var i = 0; i < item.data_value.length; i++) {
                        if (item.data_value[i].value == item.data) {
                            found = i
                            break
                        }
                    }
                    if (found == -1) {
                        if (self.cancelToken[tokenValue] != null) {
                            self.cancelToken[tokenValue].cancel('canceled')
                            self.cancelToken[tokenValue] = null
                        }
                        self.cancelToken[tokenValue] = axios.CancelToken.source();
                        var val = await axios.get(item.url, {
                            params: vTableParam(options),
                            cancelToken: self.cancelToken[tokenValue].token
                        })
                        if (!val.data.status) throw val.data
                        var tmp = self.listDataFormater(val.data.data, item)
                        item.data_selected = tmp[0]
                        //item.data_value = [item.data_selected]
                    }
                } catch (err) {
                    console.log(err)
                }
            },
            listDataGetter: async function(item) {
                try {
                    var self = this,
                        found = -1,
                        tokenValue = item.value + item.url + 'data'
						
                    var options = {
                        filter: {},
                        filterType: {},
                        filterCondition: {}
                    }

                    if (item.options) {
                        options = JSON.parse(JSON.stringify(item.options))
                    }

                    if (item.paging) {
                        options.itemsPerPage = item.limit || 10
                        options.page = item.page
                    }

                    if (item.loading) {
                        item.loading = true
                    }

                    if (self.searches[item.value].toString().trim().length != 0) {
                        /*if (item.pk !== undefined) {
                            options.filter[item.pk] = self.searches[item.value]
                            if (options.filterType[item.pk] == undefined) {
                                options.filterType[item.pk] = '%'
                            }
                        } else */
						if (Array.isArray(item.searchby)) {
                            item.searchby.map(function(val) {
                                options.filter[val] = self.searches[item.value]
                                if (options.filterType[val] == undefined) {
                                    options.filterType[val] = '%'
                                }
                                if (options.filterCondition[val] == undefined) {
                                    options.filterCondition[val] = 'OR'
                                }
                            })
                        }
						else if (Array.isArray(item.formatter)) {
                            options.filter[item.formatter[1]] = self.searches[item.value]
                            if (options.filterType[item.formatter[1]] == undefined) {
                                options.filterType[item.formatter[1]] = '%'
                            }
                        } else {
                            options.filter[item.pk || item.value] = self.searches[item.value]
                            if (options.filterType[item.pk || item.value] == undefined) {
                                options.filterType[item.pk || item.value] = '%'
                            }
                        }
                    } else {
                        if (item.pk !== undefined) {
                            delete options.filter[item.pk]
                        } else if (Array.isArray(item.formatter)) {
                            delete options.filter[item.formatter[0]]
                        } else if (Array.isArray(item.searchby)) {
                            item.searchby.map(function(val) {
                                delete options.filter[val]
                            })
                        } else {
                            delete options.filter[item.pk || item.value]
                        }
                    }

                    if (item.data_value == undefined)
                        item.data_value = []

                    if (self.cancelToken[tokenValue] != null) {
                        self.cancelToken[tokenValue].cancel('canceled')
                        self.cancelToken[tokenValue] = null
                    }

                    self.cancelToken[tokenValue] = axios.CancelToken.source();

                    var val = await axios.get(item.url, {
                        params: vTableParam(options),
                        cancelToken: self.cancelToken[tokenValue].token
                    })

                    if (!val.data.status) throw val.data
                    var tmp = self.listDataFormater(val.data.data, item)
					self.$nextTick(()=>{
						if (item.data_selected)
							item.data_value = [item.data_selected].concat(tmp)
						else{
							item.data_value = tmp
						}
					})
                    item.real_data = val.data.data

                    if (item.paging)
                        item.total = Math.ceil(parseInt(val.data.total) / parseInt(item.limit))

                } catch (err) {
                    console.log(err)
                }
                if (item.loading) {
                    item.loading = false
                }
            },
			prepareForm: async function(){
				var self = this,
					proms = []

				self.preparing = true
				self.headers.map(function(item, i) {
					self.$set(self.searches, self.headers[i].value, '')
					self.$set(self.headers[i], 'data_value', self.headers[i].data_value || [])
					if(item.default!=undefined){
						//console.log(item.data, item.value, 'default')
						self.$set(self.headers[i], 'data', item.data == undefined ? item.default : item.data)
						if(item.type == 'switch'){
							self.$set(self.headers[i], 'tmp_data', self.headers[i].data_value[1] == item.default)
						}
					}
					else if(item.update!=undefined){
						//console.log(item.data, item.value, 'update')
						if(item.valueType == 'int')
							self.$set(self.headers[i], 'data', Number(item.update))
						else
							self.$set(self.headers[i], 'data', item.update)
						if(item.type == 'switch'){
							self.$set(self.headers[i], 'tmp_data', self.headers[i].data_value[1] == item.update)
						}
					}
					else{
						//console.log(item.data, item.value, 'else')
						self.$set(self.headers[i], 'data', item.data == undefined ? null : item.data)
						if(item.type == 'switch'){
							self.$set(self.headers[i], 'tmp_data', self.headers[i].data_value[1] == item.data)
						}
					}
					
					if (item.type == 'list' && typeof item.url == 'string' && item.paging && !item.combo) {
						self.$set(self.headers[i], 'total', 0)
						self.headers[i].page = 1
						proms.push(new Promise(async (resolve, reject) => {
							try {
								await self.listValueChecker(item)
								await self.listDataGetter(item)
							} catch (err) {
								console.log(err)
							}
							resolve(true)
						}))
					}
				})
				await Promise.all(proms)
				self.preparing = false
				
			},
            onVisible: function(isVisible, e) {
                var self = this
                self.isInViewport = !!isVisible
                self.isInDom = !!e.target.isConnected
				if(e.isIntersecting){
					self.valueChange = false
					self.prepareForm()
				}
            },
			reset: function(){
				var self = this
				self.valueChange = false
				self.$refs.form.reset()
			}
        },
        mounted: function() {
            var self = this,
                proms = []
            //self.prepareForm()

			self.$nextTick(()=>{
				self.ready = true
			})
        }
    }
</script>
