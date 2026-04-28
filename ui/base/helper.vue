<template>
    <v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-toolbar color="white" class="flex-initial" style="flex: 0">
            <div style="width: 300px;">
                <v-text-field label="Table" v-model="table" hide-details dense></v-text-field>
            </div>
            <div style="width: 300px;">
                <v-text-field label="Url" v-model="url" hide-details dense></v-text-field>
            </div>
            <div style="width: 300px;">
                <v-text-field label="name" v-model="name" hide-details dense></v-text-field>
            </div>
            <v-btn small color="primary" outlined @click="create">Create</v-btn>
            <v-spacer></v-spacer>
        </v-toolbar>
		<div style="display: flex;">
			<div style="flex: 1">
				<b>UI</b>
				<v-code-editor v-model="uiTemplate" language="html" label=""></v-code-editor>
			</div>
			<div style="flex: 1">
				<b>Models</b>
				<v-code-editor v-model="modelTemplate" label=""></v-code-editor>
			</div>
			<div style="flex: 1">
				<b>Controllers</b>
				<v-code-editor v-model="controllerTemplate" label=""></v-code-editor>
			</div>
		</div>
    </v-container>
</template>

<style>
</style>

<script>
    module.exports = {
        name: '',
        data: function() {
            return {
                table: '',
                url: '',
                name: '',
                uiTemplate: '',
                modelTemplate: '',
                controllerTemplate: '',
            }
        },
        methods: {
            type: function(t) {
                switch (t) {
                    case 'int':
                        return 'int'
                        break;
                    case 'varchar':
                        return 'varchar'
                        break;
                    case 'text':
                        return 'text'
                        break;
                    case 'datetime':
                        return 'datetime'
                        break;
                    case 'date':
                        return 'date'
                        break;
                    case 'time':
                        return 'time'
                        break;
                    case 'decimal':
                        return 'float'
                        break;

                    default:
                        return 'varchar'
                        break;
                }
            },
            create: async function() {
                var self = this,
                    beautifierConfig = {
                        "indent_size": 1,
                        "indent_with_tabs": true,
                        "html": {
                            "end_with_newline": true,
                            "js": {
                                "indent_size": 1
                            },
                            "css": {
                                "indent_size": 1
                            }
                        },
                        "css": {
                            "indent_size": 1
                        },
                        "js": {
                            "preserve-newlines": true
                        }
                    }
                var tpl = await axios.get(App.url.replace('api/', 'ui/base/template.vue'), {
                })
                var tplModel = await axios.get(App.url.replace('api/', 'ui/base/model.vue'), {
                })
                var tplController = await axios.get(App.url.replace('api/', 'ui/base/controller.vue'), {
                })
                var res = await axios.get(App.url + 'helper', {
                    params: {
                        table: self.table
                    }
                })
                var headers = [],
                    itemKey = '',
					nameUnderscore = self.name.unCamelCase().slugify("_").properCase(),
					url = self.url + '/' + nameUnderscore.toLowerCase()

                res.data.ddl.map(val => {
                    if (val.COLUMN_KEY == 'PRI')
                        itemKey = val.COLUMN_NAME.toLowerCase()
					var invi = ["modified_by", "modified_date", "created_by", "created_date"];
                    var tmp = {
                        "text": val.COLUMN_NAME.unUnderscore().unhyphenate().unCamelCase().properCase(),
                        "value": val.COLUMN_NAME.toLowerCase(),
                        "align": "start",
                        "sortable": true,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "type": self.type(val.DATA_TYPE.toLowerCase()),
                        "disabled": false,
                        "visible": val.COLUMN_KEY != 'PRI',
                        "required": val.IS_NULLABLE == 'NO',
                        "form": val.COLUMN_KEY != 'PRI' && !invi.includes(val.COLUMN_NAME.toLowerCase()),
                        "filter": true,
                        "groupable": false,
                        "url": "",
                        "searchby": [],
                        "formatter": [],
                        "options": {
                            "filter": {},
                            "filterType": {},
                            "filterCondition": {}
                        },
                        "paging": true,
                        "page": "1",
                        "limit": "10"
                    }
					if("created_by" == val.COLUMN_NAME.toLowerCase()){
						tmp.alias = "created_by_name"
					}
					if("modified_by" == val.COLUMN_NAME.toLowerCase()){
						tmp.alias = "modified_by_name"
					}
                    headers.push(tmp)
                })
                tpl.data = tpl.data.replaceAll('{{headers}}', JSON.stringify(headers))
                tpl.data = tpl.data.replaceAll('{{itemKey}}', itemKey)
                tpl.data = tpl.data.replaceAll('{{name}}', self.name)
                tpl.data = tpl.data.replaceAll('{{url}}', url)

                tplModel.data = tplModel.data.replaceAll('{{name}}', self.name.pascalCase())
                tplModel.data = tplModel.data.replaceAll('{{table}}', self.table)
                tplModel.data = tplModel.data.replaceAll('{{itemKey}}', itemKey)
                tplModel.data = tplModel.data.replaceAll('{{fields}}', JSON.stringify(res.data.fields))
                tplModel.data = tplModel.data.replaceAll('{{url}}', self.url.pascalCase())

				tplController.data = tplController.data.replaceAll('{{nameUnderscore}}', nameUnderscore)
				tplController.data = tplController.data.replaceAll('{{name}}', self.name.pascalCase())
				tplController.data = tplController.data.replaceAll('{{url}}', self.url.pascalCase())

                self.uiTemplate = html_beautify(tpl.data, beautifierConfig)
				self.modelTemplate = tplModel.data
				self.controllerTemplate = tplController.data

            }
        },
        mounted: function() {
            var lib = [
                'library/highlight/highlight.min.css',
                'library/highlight/highlight.min.js',
                'library/beautify/beautify.min.js',
                'library/beautify/beautify-css.min.js',
                'library/beautify/beautify-html.min.js'
            ]
            loadMultipleLibrary(lib.join(';'))
        }
    }
</script>