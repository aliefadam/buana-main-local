<template>
    <div class="main-container-planner" :style="`width: ${parseInt(mainWidth)+300}px; min-height: 100%; max-width: 100%; margin-left: auto; margin-right: auto; display: flex; background: #FFF; flex-wrap: wrap; align-content: baseline;`">
        <div class="flex flex-column planner-container" :style="`flex: 1 ${mainWidth}; overflow: auto; background: #FFF !important; margin-bottom: 60px;`">
            <v-card elevation="0" class="card-yellow card-planner">
                <!-- <v-card-title class="card-header"><a :href="location.href.split('/').slice(0,5).concat(['planner', 'planner', 'project', project.id]).join('/')">PROJ-{{(project.id||0).toString().padStart(4, 0)}}-{{project.name}}</a><v-spacer></v-spacer><v-btn small color="warning" small text @click="toggleWatch"><v-icon left v-if="watcher_username.includes(App.username)">mdi-star</v-icon><v-icon left v-else>mdi-star-outline</v-icon> Watch</v-btn></v-card-title> -->
                <div class="card-header" style="flex-wrap: wrap;">
                    <v-card-title style="flex: 1 100%; font-weight: bold; padding-top: 0; padding-bottom: 8px; font-size: 10px; height: auto; line-height: 2; padding-left: 0;" v-if="parent !== null"><a :href="location.href.split('/').slice(0,5).concat(['planner', 'planner', 'task', parent.id]).join('/')">{{parent.category}} #{{(parent.id||0).toString().padStart(4, 0)}} - {{parent.subject}}</a></v-card-title>
                    <v-card-subtitle :style="'flex: 1 100%; margin-top: -8px !important; font-weight: bold; padding-bottom: 0; font-size: 14px; color: #000;' + (data.parent_id == null ? 'padding-top: 8px; padding-left: 0;' : 'padding-top: 0; padding-left: 32px;') ">{{data.category}} #{{(data.id||0).toString().padStart(4, 0)}} - {{data.subject}}</v-card-subtitle>
                </div>
                <!-- <v-card-title :style="'font-weight: bold; padding-bottom: 8px;' + (data.parent_id == null ? 'padding-top: 0px;' : 'padding-top: 0; padding-left: 32px;') ">{{data.subject}}</v-card-title> -->
                <v-card-text style="padding-bottom: 0" class="markdown-body" v-html="App.md.render(data.description)" v-if="data.description">
                </v-card-text>
                <v-row>
                    <v-col style="padding: 8px;" xs="12" md="3" lg="2">
                        <b>% Done: {{data.percent_done}}%</b>
                    </v-col>
                    <template v-for="(f, fidx) in fieldColumns">
                        <v-col style="padding: 8px;" xs="12" md="3" lg="2" :key="fidx" v-if="data[fidx] != null">
                            <b>{{fidx.slice(6)}}: {{data[fidx]}}</b>
                        </v-col>
                    </template>
                </v-row>
                <div class="flex flex-column" style="margin-top: 0 !important; margin-left: 8px; margin-right: 8px;" v-if="children.length>0">
                    <b>Sub Task</b>
                    <template v-for="(item, index) in children">
                        <a style="padding-left: 24px" :href="location.href.split('/').slice(0,5).concat(['planner', 'planner', 'task', item.id]).join('/')" :key="index">#{{item.id}} {{item.category}} {{item.subject}}</a>
                    </template>
                </div>
                <v-menu v-model="showMenu" :position-x="x" :position-y="y" absolute offset-y>
                    <v-list>
                        <v-list-item v-if="previewurl!=null">
                            <v-list-item-title @click="window.open(previewurl)">Preview</v-list-item-title>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-title @click="window.open(downloadurl)">Download</v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-menu>
            </v-card>
            <div class="card-yellow card-planner">
                <div style="flex: 0; border-bottom: 1px solid #d6d7d8" class="card-header">
                    <b>Document</b>
                    <v-spacer></v-spacer>
                    <v-btn small text color="error" class="btn-bold" @click="openReference">
                        <v-icon small left>mdi-text-box-plus-outline</v-icon>Add
                    </v-btn>
                </div>
                <!-- <div style="flex: 0; border-bottom: 1px solid #A9C6F4" class="text-bold flex">
					<b>Members</b>
					<v-spacer></v-spacer>
					<v-btn small text color="error" class="btn-bold" @click="openAddMember">
						<v-icon small left>mdi-account-plus-outline</v-icon>Add
					</v-btn>
				</div>
				<div style="flex: 0; display: flex; flex-wrap: wrap; gap: 8px; justify-content: left; margin-top: 0 !important;">
					<v-chip class="member-chip" close @click:close="deleteMember(item2.id, item2.name)" href v-for="(item2, index2) in members" :key="index2" outlined>{{item2.name}}</v-chip>
				</div> -->
                <div class="flex flex-column" style="margin-top: 0 !important; padding-bottom: 8px;">
                    <template v-for="(ref, refIdx) in reference">
                        <div style="font-size: 12px;"><b>{{refIdx}}</b></div>
                        <div @contextmenu="show" v-for="(item, index) in ref" class="link-hover" style="display: flex; align-items: center; padding-bottom: 3px; padding-left: 8px; padding-right: 8px;">
                            <a :href="item.url" target="_blank" :key="index" :key="index">{{item.alias||item.url}}</a>
                            <v-spacer></v-spacer>
                            <v-btn style=" height: 18px; width: 18px; margin-right: 8px;" @click.stop="editReference(item)" small icon color="warning">
                                <v-icon style="font-size: 16px; height: 18px;">mdi-file-document-edit-outline</v-icon>
                            </v-btn>
                            <v-btn style=" height: 18px; width: 18px;" @click.stop="removeReference(item)" small icon color="error">
                                <v-icon style="font-size: 16px; height: 18px;">mdi-close-circle</v-icon>
                            </v-btn>
                        </div>
                    </template>
                </div>
                <div v-if="Object.keys(reference).length == 0" class="info" style="height: 30px; align-items: center; display: flex; justify-content: center;">
                    <div class="subtitle">No Document added!</div>
                </div>
            </div>
            <div class="card-yellow card-planner">
                <div class="card-header">
                    <b>Notes</b>
                    <v-spacer></v-spacer>
                    <!-- <v-btn small text color="warning" class="btn-bold" @click="updateTask" v-if="allow(2) || allow(7)">
                        <v-icon small left>mdi-pencil</v-icon>Update
                    </v-btn> -->
                </div>
                <div v-for="(item, index) in notes" :key="index" :class="'history-card task-note-'+(index+1)" style="padding: 8px; margin: 0">
                    <div style="font-weight: bold; align-items: center; padding-bottom: 4px; font-size: 12px; display: flex;" :title="datetimeFormat(item.created_date)">{{humandate.relativeTime(item.created_date.split('.')[0])}} by {{item.created}}
                        <v-spacer></v-spacer>
                        <v-btn small text color="warning" class="btn-bold" @click="editNote(item)" v-if="item.created_by == App.username">
                            <v-icon small>mdi-comment-edit-outline</v-icon>
                        </v-btn>
                        #{{index+1}}
                    </div>
                    <v-row v-if="item.f_task_id!==null" style="font-size: 12px;">
                        <template v-for="(f, fidx) in fieldColumns">
                            <v-col style="padding: 8px; padding-top: 0;" xs="12" md="4" lg="3" :key="fidx" v-if="item[fidx] != null">
                                <b>{{fidx.slice(6)}}</b> changed to <b>{{item[fidx]}}</b>
                            </v-col>
                        </template>
                    </v-row>
                    <div class="markdown-body" v-html="App.md.render(item.notes||'')" v-if="item.notes" style="padding-top: 8px"></div>
                </div>
                <div v-if="notes.length == 0" class="info" style="height: 30px; align-items: center; display: flex; justify-content: center;">
                    <div class="subtitle">No updates yet!</div>
                </div>
            </div>
            <v-fab-transition>
                <v-btn color="orange" dark absolute bottom right small @click="updateTask" fab :style="'bottom: 5px; left: '+(left+fabLeft)+'px'">
                    <v-icon>mdi-pencil</v-icon>
                </v-btn>
            </v-fab-transition>
        </div>
        <div :style="'flex: 0 '+(['sm', 'xs'].includes($breakpoint) ? '100%; padding-left: 8px;' : '300px;')+' display: flex; flex-direction: column; overflow: hidden; padding-right: 8px;'" class="side-content">
            <v-card class="section-card section-card-12" elevation="0" outlined :style="'max-width: 100%; padding: 0 !important; box-sizing: border-box;'+(['sm', 'xs'].includes($breakpoint) ? 'margin-right: 0 !important;' : '')">
                <div class="flex" style="background: #f9f9fa; width: 100%; font-weight: bold; text-align: left; margin-bottom: 4px; align-items: center; padding: 4px 8px 4px 16px; border-bottom: 1px solid #d6d7d8">
                    <span style="font-size: 14px !important;">Members</span>
                    <v-spacer></v-spacer>
                    <v-btn v-if="allow(10)" small text color="error" class="btn-bold" @click="openAddMember">
                        <v-icon small left>mdi-account-plus-outline</v-icon>Add
                    </v-btn>
                </div>

                <div style="padding: 8px;">
                    <div style="flex: 0; display: flex; flex-wrap: wrap; gap: 8px; justify-content: left; margin-top: 0 !important;">
                        <v-chip class="member-chip" close @click:close="deleteMember(item2.id, item2.name)" href v-for="(item2, index2) in members" :key="index2" outlined>{{shortName(item2.name)}}</v-chip>
                    </div>
                    <div v-if="members.length == 0" class="info" style="height: 30px; align-items: center; display: flex; justify-content: center;">
                        <div class="subtitle">No Members added!</div>
                    </div>
                </div>
            </v-card>
            <v-card class="section-card section-card-12" elevation="0" outlined :style="'max-width: 100%; padding: 0 !important; box-sizing: border-box;'+(['sm', 'xs'].includes($breakpoint) ? 'margin-right: 0 !important;' : '')">
                <div class="flex" style="background: #f9f9fa; width: 100%; font-weight: bold; text-align: left; margin-bottom: 4px; align-items: center; padding: 4px 8px 4px 16px; border-bottom: 1px solid #d6d7d8">
                    <span style="font-size: 14px !important;">Watchers</span>
                    <v-spacer></v-spacer>
                    <v-btn small text color="error" class="btn-bold" @click="openAddWatcher">
                        <v-icon small left>mdi-star-plus</v-icon>Add
                    </v-btn>
                </div>

                <div style="padding: 8px;">
                    <div style="flex: 0; display: flex; flex-wrap: wrap; gap: 8px; justify-content: left; margin-top: 0 !important;">
                        <v-chip class="member-chip" close @click:close="deleteWatcher(item2.id, item2.name)" href v-for="(item2, index2) in watcher" :key="index2" outlined>{{shortName(item2.name)}}</v-chip>
                    </div>
                    <div v-if="watcher.length == 0" class="info" style="height: 30px; align-items: center; display: flex; justify-content: center;">
                        <div class="subtitle">No Watchers added!</div>
                    </div>
                </div>
            </v-card>
            <v-card class="section-card section-card-12" elevation="0" outlined :style="'max-width: 100%; padding: 0 !important; box-sizing: border-box;'+(['sm', 'xs'].includes($breakpoint) ? 'margin-right: 0 !important;' : '')">
                <div class="flex" style="background: #f9f9fa; width: 100%; font-weight: bold; text-align: left; margin-bottom: 4px; align-items: center; padding: 4px 8px 4px 16px; border-bottom: 1px solid #d6d7d8">
                    <span style="font-size: 14px !important;">Related Task</span>
                    <v-spacer></v-spacer>
                    <v-btn small text color="error" class="btn-bold" @click="openAddRelated">
                        <v-icon small left>mdi-plus</v-icon>Add
                    </v-btn>
                </div>
                <div style="padding: 8px;" class="related-content">
                    <div class="info" v-for="(item, index) in related" :key="index">
						<a :href="'#'+['', 'planner', 'planner', 'task', item.id].join('/')">
						<!-- @click="App.loadWithBase(['', 'planner', 'planner', 'task', item.id].join('/'))" -->
                        <div class="title" class="flex" style="flex-wrap: nowrap; display: flex; align-items: center;"> 
                            <div style="cursor: pointer; flex: 1" >{{item.category}} #{{item.id}} - {{item.subject}}</div>
                            <v-btn @click.stop="removeRelated(item.id, data.id)" icon color="error">
                                <v-icon>mdi-close-circle</v-icon>
                            </v-btn>
                        </div></a>
                        <div class="subtitle" v-html="item.description"></div>
                        <!-- <div class="subtitle" style="margin-top: 8px; flex-wrap: nowrap; display: flex; align-items: center;">
                            <div style="flex: 1" class="text-vertical-centered">
                                <v-icon left>mdi-account</v-icon>{{nameFormat(item['alias_Assigned to']||'-')}}
                            </div>
                            <div class="text-vertical-centered">
                                <v-chip small flat class="immediate" v-if="item.alias_Priority == 'Immediate'">
                                    <v-icon left>mdi-alert-decagram-outline</v-icon>{{item.alias_Priority}}
                                </v-chip>
                                <v-chip small flat class="urgent" v-else-if="item.alias_Priority == 'Urgent'">
                                    <v-icon left>mdi-alert-circle-outline</v-icon>{{item.alias_Priority}}
                                </v-chip>
                                <v-chip small flat class="high" v-else-if="item.alias_Priority == 'High'">
                                    <v-icon left>mdi-chevron-up-circle-outline</v-icon>{{item.alias_Priority}}
                                </v-chip>
                                <v-chip small flat class="normal" v-else-if="item.alias_Priority == 'Normal'">
                                    <v-icon left>mdi-circle-outline</v-icon>{{item.alias_Priority}}
                                </v-chip>
                                <v-chip small flat class="low" v-else-if="item.alias_Priority == 'Low'">
                                    <v-icon left>mdi-chevron-down-circle-outline</v-icon>{{item.alias_Priority}}
                                </v-chip>
                            </div>
                        </div> -->
                    </div>
                    <!-- <a class="info" href>
					<div class="title" class="flex" style="flex-wrap: nowrap; display: flex; align-items: center;">
						<div style="flex: 1">#123 Installation - Mesin a</div>
						<v-chip small flat class="important"><v-icon left>mdi-alert-decagram-outline</v-icon>Important</v-chip>
					</div>
					<div class="subtitle">Installasi mesin a sudah selesai pada hari sabtu</div>
					<div class="subtitle" style="margin-top: 8px; flex-wrap: nowrap; display: flex; align-items: center;">
						<div style="flex: 1" class="text-vertical-centered">
							<v-icon left>mdi-account</v-icon>{{nameFormat('Muchibuddin Abbas')}}
						</div>
						<div class="text-vertical-centered">
							<v-icon left>mdi-calendar-month</v-icon>2022-01-01
						</div>
					</div>
				</a> -->
                    <div v-if="related.length == 0" class="info" style="height: 30px; align-items: center; display: flex; justify-content: center;">
                        <div class="subtitle">No Related Task added!</div>
                    </div>
                </div>
            </v-card>
        </div>

        <v-action-dialog v-model="dialogAddMember" title="Add Member" @save="addMember" :disable-save="!validAddMember" :save-loading="loading">
            <form-template :headers="formAddMember" :valid.sync="validAddMember" ref="formAddMember"></form-template>
        </v-action-dialog>

        <v-action-dialog v-model="dialogAddWatcher" title="Add Watcher" @save="addWatcher" :disable-save="!validAddWatcher" :save-loading="loading">
            <form-template :headers="formAddWatcher" :valid.sync="validAddWatcher" ref="formAddWatcher"></form-template>
        </v-action-dialog>

        <v-action-dialog v-model="dialogRelated" title="Add Related Task" @save="addRelated" :disable-save="!validRelated" :save-loading="loading">
            <form-template :headers="formRelated" :valid.sync="validRelated" ref="formRelated"></form-template>
        </v-action-dialog>

        <v-action-dialog v-model="dialogReference" :title="referenceMode+' Reference'" @save="addReference" :disable-save="!validReference" :save-loading="loading">
            <form-template :headers="formReference" :valid.sync="validReference" ref="formReference"></form-template>
        </v-action-dialog>

        <v-action-dialog v-model="dialogUpdate" title="Add Notes" @save="addNotes" :disable-save="!valid" :save-loading="loading" key="22" fullscreen>
			<template v-if="notes.length>0">
				<b>Previous Notes:</b>
				<div class="markdown-body" v-html="App.md.render(notes.slice(-1)[0].notes||'')" style="padding-top: 8px; margin-bottom: 8px; border-bottom: 1px solid"></div>
			</template>
            <form-template :headers="headers" :valid.sync="valid" ref="headers">
                <!-- <template slot="notes.append">
                    <div style="flex: 0; align-items: center; display: flex;">
                        <v-icon @click="App.openWindow('http://'+location.host+'/apps/#/sa/md', '_blank', 400, 500)" color="error" style=" margin-right: 8px;">mdi-help-circle-outline</v-icon>
                    </div>
                </template> -->
            </form-template>
        </v-action-dialog>

        <v-bottom-sheet v-model="bottomSheet" inset>
            <div style="background-color: #fff;">
                <ckeditor :editor="ckver" v-model="noteEdit" :config="ckconfig"></ckeditor>
                <!-- <v-textarea hide-details dense filled label="Notes" auto-grow v-model="noteEdit"></v-textarea> -->
                <div class="flex" style="padding: 8px;">
                    <v-spacer></v-spacer>
                    <v-btn small color="primary" outlined @click="updateNote" style="margin-right: 8px;">Save</v-btn>
                    <v-btn small color="error" outlined @click="bottomSheet=false">Cancel</v-btn>
                </div>
            </div>
        </v-bottom-sheet>

        <v-overlay v-model="loading" style="z-index: 1000"><b>Loading...</b></v-overlay>

    </div>
</template>

<style scoped>
	.markdown-body ol li{
		list-style: decimal !important;
	}
    a[target*="_blank"]:after {
        color: red !important;
    }

    .member-chip {
        border: 0 !important;
        height: auto;
        padding: 0 4px !important;
    }

    .member-chip .v-icon *,
    .member-chip .v-icon {
        color: #ff5252 !important;
        font-size: 20px !important;
    }

    .history-card {
        padding-bottom: 4px;
        margin: 0 8px !important;
        margin-bottom: 0 !important;
    }

    div>.history-card:not(:last-child) {
        border-bottom: 1px solid #dedede;
    }

    div>.history-card:last-child {
        margin-bottom: 0 !important;
    }

    .text-vertical-centered {
        display: flex;
        align-items: center;
    }

    .side-content>* {
        width: 100%;
        margin-right: 8px;
        margin-top: 8px;
    }

    .btn-bold {
        font-weight: bold
    }

    .btn-bold *:before {
        font-weight: bold
    }

    .memberlist>a {
        margin-right: 8px;
    }

    .memberlist>a:last-child {
        margin-right: 0 !important;
    }

    .memberlist .v-chip {
        border: 0;
        line-height: 1;
    }

    .memberlist .v-chip .mdi {
        font-size: 18px !important;
        color: #ff5252 !important;
    }

    .section-card * {
        font-size: 14px !important;
    }

    .section-card-12 *:not(.v-icon) {
        font-size: 12px !important;
    }

    .section-card {
        padding: 8px 12px;
        border-radius: 4px;
        margin-bottom: 8px;
        font-size: 14px !important;
    }

    .section-header {
        background: #f9f9fa;
        padding-top: 4px;
        padding-left: 16px;
        padding-right: 8px;
        align-items: center;
        padding-bottom: 4px;
        border-radius: 4px 4px 0 0;
    }

    div.info {
        background-color: #fff !important;
        background: #fff !important;
    }

    .move-to-note {
        color: rgb(9, 105, 218);
        cursor: pointer;
    }

    .card-planner>div.history-card:nth-child(odd) {
        background-color: rgb(246, 250, 246) !important;
    }

    .link-hover:hover {
        background-color: rgb(246, 250, 246) !important;
    }
</style>

<script>
    module.exports = {
        name: '',
        components: {
            'form-template': 'url:ui/admin/form-template.vue',
            'v-contextmenu': 'url:ui/base/Contextmenu.vue',
            'v-contextmenu-group': 'url:ui/base/ContextmenuGroup.vue',
            'v-contextmenu-submenu': 'url:ui/base/ContextmenuSubmenu.vue',
            'v-action-dialog': 'url:v-apps/src/lib-components/v-action-dialog.vue',
        },
        /*directives: {
            contextmenu: VContextmenu.directive,
        },*/
        /*directives: {
            contextmenu: {
                bind(el, binding, vnode) {
                    const node = vnode.context.$refs[binding.arg] || vnode.context.$refs[binding.value]
                    const contextmenu = Object.prototype.toString.call(node) === '[object Array]' ? node[0] : node
                    if (contextmenu) {
                        contextmenu.$contextmenuId = el.id || contextmenu._uid // eslint-disable-line no-underscore-dangle
                    }
                },
                // 之所以用 inserted 而不是 bind，是需要确保 contentmenu mounted 后才进行 addRef 操作
                inserted(el, binding, vnode) {
                    const node = vnode.context.$refs[binding.arg] || vnode.context.$refs[binding.value]
                    const contextmenu = Object.prototype.toString.call(node) === '[object Array]' ? node[0] : node
                    if (contextmenu) {
                        contextmenu.addRef({
                            el,
                            vnode
                        })
                    }
                }
            },
        },*/
        data: function() {
            return {
                taskId: -1,
                left: 0,
                showMenu: false,
				x: 0,
      			y: 0,
                referenceId: -1,
                ckver: ClassicEditor,
                ckconfig: {
                    //plugins: [ Bold, Italic, Underline, Strikethrough, Code, Subscript, Superscript ],
                    toolbar: {
                        items: ['undo', 'redo', 'heading', 'bold', 'italic', 'underline', 'strikethrough', 'subscript', 'superscript',
                            'fontfamily', 'fontsize', 'fontColor', 'fontBackgroundColor',
                            'alignment', 'bulletedList', 'numberedList', 'todoList', 'outdent', 'indent', 'blockQuote',
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
                            }
                        ]
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
                            }
                        ]
                    }

                },
                referenceMode: 'Add',
                loading: false,
                bottomSheet: false,
                dialogTask: false,
                noteEdit: '',
                noteId: '',
                dialogAddMember: false,
                dialogAddWatcher: false,
                validAddMember: false,
                validAddWatcher: false,
                validReference: false,
                validRelated: false,
                dialogReference: false,
                dialogUpdate: false,
                dialogReference: false,
                dialogRelated: false,
                valid: false,
                formReference: [{
                    "text": "Category",
                    "value": "category",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "list",
                    "default": "Part Number",
                    "disabled": false,
                    "visible": true,
                    "required": true,
                    "form": true,
                    "filter": true,
                    "groupable": false,
                    "paging": true,
                    "page": 0,
                    "limit": 10,
                    "data_value": ['Part Number', 'Documentation', 'Datasheet', 'Software', 'Others'],
                }, {
                    "text": "Alias",
                    "value": "alias",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "formWidth": "100%",
                    "type": "varchar",
                    "disabled": false,
                    "visible": true,
                    "required": false,
                    "form": true,
                    "filter": true,
                    "groupable": false,
                }, {
                    "text": "URL",
                    "value": "url",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "formWidth": "100%",
                    "type": "text",
                    "disabled": false,
                    "visible": true,
                    "required": false,
                    "form": true,
                    "filter": true,
                    "groupable": false,
                }],
                formAddMember: [{
                    "text": "User",
                    "value": "username",
                    "align": "start",
                    "sortable": true,
                    "filter": true,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "list",
                    "data_value": [],
                    "url": App.url + "planner/project/member",
                    'searchby': ['username', 'email'],
                    'formatter': function(val) {
                        return {
                            text: val.name,
                            value: val.username
                        }
                    },
                    "options": {
                        filter: {
                            //groups: ';dept-seg;'
                        },
                        filterType: {
                            //groups: '%'
                        },
                        filterCondition: {
                            email: 'OR',
                            username: 'OR',
                            //groups: 'and'
                        },
                        project_id: -1
                    },
                    paging: true,
                    page: 0,
                    limit: 10
                }],
                formAddWatcher: [{
                    "text": "User",
                    "value": "username",
                    "align": "start",
                    "sortable": true,
                    "filter": true,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "list",
                    "data_value": [],
                    "url": App.url + "admin/users",
                    'searchby': ['username', 'email'],
                    'formatter': function(val) {
                        return {
                            text: val.name,
                            value: val.username
                        }
                    },
                    "options": {
                        filter: {
                            //groups: ';dept-seg;'
                        },
                        filterType: {
                           // groups: '%'
                        },
                        filterCondition: {
                            email: 'OR',
                            username: 'OR',
                            //groups: 'and'
                        }
                    },
                    paging: true,
                    page: 0,
                    limit: 10
                }],
                formRelated: [{
                    "text": "Task ID",
                    "value": "task_id",
                    "align": "start",
                    "sortable": true,
                    "filter": true,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "int",
                }],
                data: {},
                related: [],
                watcher: [],
                project: {},
                members: {},
                taskOptions: {
                    task_id: -1
                },
                headersDef: [{
                        "text": "Subject",
                        "value": "subject",
                        "align": "start",
                        "sortable": true,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "formWidth": "100%",
                        "type": "varchar",
                        "disabled": false,
                        "visible": true,
                        "required": true,
                        "form": true,
                        "filter": true,
                        "groupable": false,
                        "paging": true,
                        "page": 0,
                        "limit": 10,
                        "data_value": [],
                        "url": "",
                        "searchby": [],
                        "formatter": [null, null],
                        "pk": null,
                        "options": {
                            "filter": {},
                            "filterType": {},
                            "filterCondition": {}
                        }
                    }, {
                        "text": "description",
                        "value": "description",
                        "align": "start",
                        "sortable": true,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "formWidth": "100%",
                        "type": "ckeditor",
                        "disabled": false,
                        "visible": true,
                        "required": false,
                        "form": true,
                        "filter": true,
                        "groupable": false,
                        "paging": true,
                        "page": 0,
                        "limit": 10,
                        "data_value": [],
                        "url": "",
                        "searchby": [],
                        "formatter": [null, null],
                        "pk": null,
                        "options": {
                            "filter": {},
                            "filterType": {},
                            "filterCondition": {}
                        }
                    },
                    /*{
						"text": "Project",
						"value": "project_id",
						"align": "start",
						"sortable": true,
						"filterable": false,
						"divider": false,
						"class": "",
						"width": "auto",
						"type": "int",
						"disabled": false,
						"visible": true,
						"required": true,
						"form": false,
						"filter": true,
						"groupable": false,
						"paging": true,
						"page": 0,
						"limit": 10,
						"data_value": [],
						"url": "",
						"searchby": [],
						"formatter": [null, null],
						"pk": null,
						"options": {
							"filter": {},
							"filterType": {},
							"filterCondition": {}
						},
						"alias": "project"
					}, */
                    {
                        "text": "Tracker",
                        "value": "category_id",
                        "align": "start",
                        "sortable": true,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "formWidth": "calc(50% - 8px)",
                        "type": "list",
                        "disabled": false,
                        "visible": true,
                        "required": true,
                        "form": true,
                        "filter": true,
                        "groupable": false,
                        "paging": true,
                        "page": 0,
                        "default": 0,
                        "limit": 10,
                        "data_value": [],
                        "url": App.url + "planner/category",
                        "searchby": ['name'],
                        "formatter": ['id', 'name'],
                        "options": {
                            "filter": {
                                flag: 1,
                            },
                            "filterType": {
                                name: '%'
                            },
                            "filterCondition": {}
                        },
                        "alias": "category"
                    },
                    {
                        "text": "Parent Task",
                        "value": "parent_id",
                        "align": "start",
                        "sortable": true,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "formWidth": "calc(50% - 8px)",
                        "type": "int",
                        "disabled": false,
                        "visible": true,
                        "required": false,
                        "form": true,
                        "filter": true,
                        "groupable": false,
                    }, {
                        "text": "% Done",
                        "value": "percent_done",
                        "align": "start",
                        "sortable": true,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "formWidth": "calc(50% - 8px)",
                        "type": "list",
                        "disabled": false,
                        "visible": true,
                        "required": true,
                        "form": true,
                        "filter": true,
                        "groupable": false,
                        "paging": true,
                        "page": 0,
                        "default": 0,
                        "limit": 10,
                        "data_value": [0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100],
                    }
                    /*, {
                    					"text": "Created By",
                    					"value": "created_by",
                    					"align": "start",
                    					"sortable": true,
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
                    					"paging": true,
                    					"page": 0,
                    					"limit": 10,
                    					"data_value": [],
                    					"url": "",
                    					"searchby": [],
                    					"formatter": [null, null],
                    					"pk": null,
                    					"options": {
                    						"filter": {},
                    						"filterType": {},
                    						"filterCondition": {}
                    					}
                    				}, {
                    					"text": "Created Date",
                    					"value": "created_date",
                    					"align": "start",
                    					"sortable": true,
                    					"filterable": false,
                    					"divider": false,
                    					"class": "",
                    					"width": "auto",
                    					"type": "datetime",
                    					"disabled": false,
                    					"visible": true,
                    					"required": true,
                    					"form": false,
                    					"filter": true,
                    					"groupable": false
                    				}, {
                    					"text": "Parent Task",
                    					"value": "parent_id",
                    					"align": "start",
                    					"sortable": true,
                    					"filterable": false,
                    					"divider": false,
                    					"class": "",
                    					"width": "auto",
                    					"type": "int",
                    					"disabled": false,
                    					"visible": true,
                    					"required": false,
                    					"form": false,
                    					"filter": true,
                    					"groupable": false,
                    					"paging": true,
                    					"page": 0,
                    					"limit": 10,
                    					"data_value": [],
                    					"url": "",
                    					"searchby": [],
                    					"formatter": [null, null],
                    					"pk": null,
                    					"options": {
                    						"filter": {},
                    						"filterType": {},
                    						"filterCondition": {}
                    					}
                    				}*/
                ],
                headersTask: [],
                notes: [],
                reference: {},
                fieldColumns: {},
                parentTaskId: null,
                parent: null,
                children: [],
                watcher_username: [],
                headers: [],
                access: {
                    project: {},
                    task: {}
                },
				previewurl: null,
				downloadurl: null,
            }
        },
        computed: {
            fabLeft: function() {
                var self = this
                var left = -70
                if (self.$breakpoint == 'xl') {
                    return left + 1264
                }
                if (self.$breakpoint == 'lg') {
                    return left + 960
                }
                if (self.$breakpoint == 'md') {
                    return left + 600
                }
                if (self.$breakpoint == 'sm') {
                    return window.innerWidth - 70
                } else if (self.$breakpoint == 'xs') {
                    return window.innerWidth - 70
                } else
                    return window.innerWidth - 70
            },
            mainWidth: function() {
                var self = this
                if (self.$breakpoint == 'xl') {
                    return '1264px'
                }
                if (self.$breakpoint == 'lg') {
                    return '960px'
                }
                if (self.$breakpoint == 'md') {
                    return '600px'
                }
                if (self.$breakpoint == 'sm') {
                    return '100%'
                } else if (self.$breakpoint == 'xs') {
                    return '100%'
                } else
                    return
            },
            headersObj: function() {
                var self = this,
                    obj = {}
                self.headers.map(function(val) {
                    if (val.value)
                        obj[val.value] = val
                })
                return obj
            },
        },
        watch: {
            dialogAddMember: function(val) {
                var self = this
                if (val) {
                    self.$nextTick(() => {
                        setTimeout(() => {
                            self.$refs.formAddMember.$refs.form.reset()
                        }, 100)
                    })
                }
            },
            $breakpoint: function() {
                var self = this
                var fw = "calc(50% - 8px)"
                if (['xs', 'sm'].includes(App.breakpoint))
                    fw = "100%"
                else if (['md'].includes(App.breakpoint))
                    fw = "calc(50% - 8px)"
                else if (['lg', 'xl'].includes(App.breakpoint))
                    fw = "calc(25% - 8px)"
                self.headersTask.map(val => {
                    val.formWidth = fw
                })
                self.headersDef.map(val => {
                    if (!['subject', 'description'].includes(val.value))
                        val.formWidth = fw
                })
            }
        },
        methods: {
            show: function(e) {
                e.preventDefault()
				var self = this
                this.showMenu = false
                this.x = e.clientX
                this.y = e.clientY
				var el = e.srcElement.tagName == 'A' ? e.srcElement : e.srcElement.querySelector('a')
				if(el)
					self.previewurl = App.url+'file/dms/excel?file='+encodeURIComponent(el.href)
				else
					self.previewurl = null
				if(el.href.match(/(.xlsx|.xls|.doc|.docx|.pdf|.jpg|.ppt|.pptx)$/ig)==null)
					self.previewurl = null
				self.downloadurl = el.href
                this.$nextTick(() => {
                    self.showMenu = true
                })
            },
            updateNote: async function() {
                var self = this
                try {
                    self.loading = true
                    var res = await axios.put(App.url + 'planner/notes', {
                        notes: self.noteEdit,
                        pk: self.noteId
                    })
                    if (!res.data.status) throw res.data
                    App.successMsg()
                    self.getData()
                } catch (err) {
                    App.errorMsg(err)
                }
                self.bottomSheet = false
                self.loading = false
            },
            editNote: function(item) {
                this.noteEdit = item.defaultNotes;
                this.noteId = item.id;
                this.bottomSheet = true;
            },
            nameFormat: function(name) {
                if (name) {
                    var name = name.split(/\s+/)
                    return name.map((val, i) => {
                        if (i != name.length - 1)
                            return val.trim()[0]
                        return val
                    }).join('. ')
                }
                return name
            },
            openReference: function() {
                var self = this
                self.formReference[0].update = null
                self.formReference[0].data = null
                self.formReference[1].update = null
                self.formReference[1].data = null
                self.referenceMode = 'Add'
                self.dialogReference = true
            },
            openAddMember: function() {
                var self = this
                self.formAddMember[0].update = null
                self.formAddMember[0].data = null
                self.dialogAddMember = true
            },
            openAddWatcher: function() {
                var self = this
                self.formAddWatcher[0].update = null
                self.formAddWatcher[0].data = null
                self.dialogAddWatcher = true
            },
            openAddRelated: function() {
                var self = this
                self.formRelated[0].update = null
                self.formRelated[0].data = null
                self.dialogRelated = true
            },
            addReference: function() {
                var self = this
                return new Promise(async (resolve, reject) => {
                    try {
                        self.loading = true
                        if (self.referenceMode == 'Add')
                            var res = await axios.post(App.url + 'planner/reference', {
                                url: self.formReference[2].data,
                                alias: self.formReference[1].data,
                                category: self.formReference[0].data,
                                task_id: self.taskId
                            })
                        else
                            var res = await axios.put(App.url + 'planner/reference', {
                                url: self.formReference[2].data,
                                alias: self.formReference[1].data,
                                category: self.formReference[0].data,
                                task_id: self.taskId,
                                pk: self.referenceId
                            })
                        if (!res.data.status) throw res.data
                        App.successMsg()
                    } catch (err) {
                        App.errorMsg(err)
                    }
                    self.loading = false
                    self.getReference()
                    self.dialogReference = false
                    resolve(true)
                });
            },
            editReference: function(item) {
                var self = this
                self.formReference[2].data = item.url
                self.formReference[1].data = item.alias
                self.formReference[0].data = item.category
                var tmp = App.updateObject(self.formReference)
                self.$nextTick(() => {
                    self.formReference = tmp
                    self.referenceId = item.id
                    self.referenceMode = 'Edit'
                    self.dialogReference = true
                })
            },
            removeReference: function(item) {
                var self = this
                var c = confirm("Are you sure to delete document " + (item.alias || item.url) + "?")
                if (c)
                    return new Promise(async (resolve, reject) => {
                        try {
                            self.loading = true
                            var res = await axios.delete(App.url + 'planner/reference', {
                                params: {
                                    id: item.id
                                }
                            })
                            if (!res.data.status) throw res.data
                            App.successMsg()
                        } catch (err) {
                            App.errorMsg(err)
                        }
                        self.loading = false
                        self.getReference()
                        resolve(true)
                    });
            },
            addMember: function() {
                var self = this
                return new Promise(async (resolve, reject) => {
                    try {
                        self.loading = true
                        var res = await axios.post(App.url + 'planner/task_member', {
                            username: self.formAddMember[0].data,
                            task_id: self.taskId
                        })
                        if (!res.data.status) throw res.data
                        App.successMsg()
                    } catch (err) {
                        App.errorMsg(err)
                    }
                    self.loading = false
                    self.getMember()
                    resolve(true)
                });
            },
            toggleWatch: function() {
                var self = this
                if (!self.watcher_username.includes(App.username)) {
                    return new Promise(async (resolve, reject) => {
                        try {
                            self.loading = true
                            var res = await axios.post(App.url + 'planner/watcher', {
                                username: App.username,
                                task_id: self.taskId
                            })
                            if (!res.data.status) throw res.data
                            App.successMsg()
                        } catch (err) {
                            App.errorMsg(err)
                        }
                        self.loading = false
                        self.getWatcher()
                        resolve(true)
                    });
                } else {
                    return new Promise(async (resolve, reject) => {
                        try {
                            self.loading = true
                            var res = await axios.delete(App.url + 'planner/watcher', {
                                params: {
                                    id: self.watcher.filter(val => val.username == App.username)[0].id
                                }
                            })
                            if (!res.data.status) throw res.data
                            App.successMsg()
                        } catch (err) {
                            App.errorMsg(err)
                        }
                        self.loading = false
                        self.getWatcher()
                        resolve(true)
                    });
                }
            },
            addWatcher: function() {
                var self = this
                return new Promise(async (resolve, reject) => {
                    try {
                        self.loading = true
                        var res = await axios.post(App.url + 'planner/watcher', {
                            username: self.formAddWatcher[0].data,
                            task_id: self.taskId
                        })
                        if (!res.data.status) throw res.data
                        App.successMsg()
                    } catch (err) {
                        App.errorMsg(err)
                    }
                    self.loading = false
                    self.getWatcher()
                    resolve(true)
                });
            },
            updateTask: function() {
                var self = this

                self.headers = [].concat(self.headersDef, self.headersTask, [{
                    "text": "Notes",
                    "value": "notes",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "formWidth": "100%",
                    "type": "ckeditor",
                    "disabled": false,
                    "config": {
                        //plugins: [ Bold, Italic, Underline, Strikethrough, Code, Subscript, Superscript ],
                        toolbar: {
                            items: ['undo', 'redo', 'heading', 'bold', 'italic', 'underline', 'strikethrough', 'subscript', 'superscript',
                                'fontfamily', 'fontsize', 'fontColor', 'fontBackgroundColor',
                                'alignment', 'bulletedList', 'numberedList', 'todoList', 'outdent', 'indent', 'blockQuote',
                                'specialCharacters', 'horizontalLine',
                                'link', 'codeBlock', 'insertTable', 'mediaEmbed', 'sourceEditing'
                            ],
                            shouldNotGroupWhenFull: true
                        }
                    },
                    "visible": true,
                    "required": false,
                    "form": true,
                    "filter": true,
                    "groupable": false,
                    "paging": true,
                    "page": 0,
                    "limit": 10,
                    "data_value": [],
                    "url": "",
                    "searchby": [],
                    "formatter": [null, null],
                    "pk": null,
                    "options": {
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {}
                    }
                }]).filter(val => {
                    if (!self.allow(2))
                        return val.value == 'notes'
                    else if (!self.allow(7))
                        return val.value != 'notes'
                    else
                        return true
                })

                self.headers.map(function(val) {
                    var key = val.value
                    var v = self.data[val.value]
                    var value = self.data[val.value]
                    if (key)
                        if (key.match('__')) {
                            var keys = key.split('__')
                            var value = v != undefined ? v.split('=__=')[1] : null
                        }
                    val.data = self.data[val.value]
                    //console.log(val.text, value, self.data[val.value], val.value)
                })
                //self.headers = App.updateObject(self.headers)
                self.dialogUpdate = true
            },
            addNotes: async function() {
                var self = this
                try {
                    var params = {}
                    Object.keys(self.headersObj).forEach(function(key) {
                        var val = self.headersObj[key]
                        if (val.data !== undefined)
                            params[key] = val.data
                    });
                    params.pk = self.taskId
                    var res = await axios.post(App.url + 'planner/notes/add', params)
                    if (!res.data.status) throw res.data
                    App.successMsg()
                    self.getData()
                } catch (err) {
                    App.errorMsg(err)
                }
                self.dialogUpdate = false
            },
            addRelated: async function() {
                var self = this
                try {
                    var params = {}
                    var res = await axios.post(App.url + 'planner/related', {
                        task_id_related: self.formRelated[0].data,
                        task_id: self.taskId,
                    })
                    if (!res.data.status) throw res.data
                    App.successMsg()
                    self.getData()
                } catch (err) {
                    App.errorMsg(err)
                }
                self.dialogRelated = false
            },
            removeRelated: function(relatedid, taskid) {
                var self = this
                var c = confirm("Are you sure to delete related task #" + relatedid + "?")
                if (c)
                    return new Promise(async (resolve, reject) => {
                        try {
                            self.loading = true
                            var res = await axios.delete(App.url + 'planner/related', {
                                params: {
                                    task_id: relatedid > taskid ? taskid : relatedid,
                                    task_id_related: relatedid < taskid ? taskid : relatedid
                                }
                            })
                            if (!res.data.status) throw res.data
                            App.successMsg()
                        } catch (err) {
                            App.errorMsg(err)
                        }
                        self.loading = false
                        self.getRelated()
                        resolve(true)
                    });
            },
            getProject: function() {
                var self = this
                return new Promise(async (resolve, reject) => {
                    try {
                        var res = await axios.get(App.url + 'planner/project', {
                            params: vTableParam({
                                filter: {
                                    id: self.projectId
                                },
                                limit: -1
                            })
                        })
                        if (!res.data.status) throw res.data
                        if (res.data.data.length == 0) throw 'Project not found!'
                        self.project = res.data.data[0]
                        App.title = 'B'+self.project.created_date.substr(2,2)+'-' + (self.project.id || 0).toString().padStart(4, 0) + '-' + self.project.name
                        document.querySelector('.v-application--wrap .v-toolbar__title').onclick = function() {
                            App.loadWithBase(['', 'planner', 'planner', 'project', self.project.id].join('/'))
                        }
                    } catch (err) {
                        App.errorMsg(err)
                    }
                    resolve(true)
                });
            },
            getRelated: function() {
                var self = this
                return new Promise(async (resolve, reject) => {
                    try {
                        var res = await axios.get(App.url + 'planner/task/related', {
                            params: {
                                id: self.taskId
                            }
                        })
                        if (!res.data.status) throw res.data
                        res.data.data.map(val => {
                            Object.keys(val).forEach(function(key) {
                                var v = val[key]
                                if (key)
                                    if (key.match('__')) {
                                        var keys = key.split('__')
                                        var value = v != undefined ? v.split('=__=')[1] : null
                                        var alias = v != undefined ? v.split('=__=')[0] : null
                                        val['real_' + keys[0]] = v
                                        val['alias_' + keys[0]] = alias
                                        val[key] = value
                                        self.fieldColumns['alias_' + keys[0]] = true
                                    }
                            });
                        })
                        self.related = res.data.data

                    } catch (err) {
                        App.errorMsg(err)
                    }
                    resolve(true)
                });
            },
            getNotes: async function() {
                var self = this
                var taskIds = [-1]
                try {
                    var res = await axios.get(App.url + 'planner/notes', {
                        params: {
                            filter: {
                                task_id: self.taskId,
                            },
                            limit: -1
                        }
                    })
                    if (!res.data.status) throw res.data
                    res.data.data.map(val => {
                        Object.keys(val).forEach(function(key) {
                            var v = val[key]
                            if (key)
                                if (key.match('__')) {
                                    var keys = key.split('__')
                                    var value = v != undefined ? v.split('=__=')[1] : null
                                    var alias = v != undefined ? v.split('=__=')[0] : null
                                    val['real_' + keys[0]] = v
                                    val['alias_' + keys[0]] = alias
                                    val[key] = value
                                    self.fieldColumns['alias_' + keys[0]] = true
                                }
                        });
                        if (val.notes) {
                            val.defaultNotes = val.notes.slice(0)
                            var m = val.notes.match(/note#\d+/ig)
                            if (m)
                                m.map(v => {
                                    val.notes = val.notes.replace(v, `<span class="move-to-note" onclick="document.querySelector('.task-note-${v.split('#')[1]}').scrollIntoView()">#${v.split('#')[1]}</span>`)
                                })
                            var m = val.notes.match(/task#\d+/ig)
                            if (m)
                                m.map(v => {
                                    var taskid = Number(v.split('#')[1])
                                    taskIds.push(taskid)
                                    val.notes = val.notes.replace(v, `<a class="task-${taskid}" href="${location.href.split('/').slice(0,5).concat(['planner', 'planner', 'task', taskid]).join('/')}">#${taskid}</a>`)
                                })

                        }
                    })
                    self.notes = res.data.data
                } catch (err) {
                    self.notes = []
                    App.errorMsg(err)
                }
                self.$nextTick(() => {
                    self.updateNoteTask(taskIds)
                })
            },
            updateNoteTask: async function(taskIds) {
                var self = this
                try {
                    var res = await axios.get(App.url + 'planner/task', {
                        params: {
                            filter: {
                                id: taskIds
                            },
                            filterType: {
                                id: 'in'
                            },
                            filterCondition: {

                            },
                            limit: -1,
                            fields: ['Status__2', 'id']
                        }
                    })
                    setTimeout(() => {
                        res.data.data.map(function(val) {
                            if (val.Status__2)
                                if (['Completed', 'Rejected'].includes(val.Status__2.split('=__=')[0])) {
                                    //console.log(document.querySelectorAll('.task-'+val.id), '.task-'+val.id)
                                    document.querySelectorAll('.task-' + val.id).forEach(val => {
                                        val.style.textDecoration = 'line-through'
                                    })
                                }
                        })
                    }, 250)
                } catch (err) {

                }
            },
            deleteMember: function(id, name) {
                var self = this
                var c = confirm("Are you sure to delete " + name + " as member")
                if (c)
                    return new Promise(async (resolve, reject) => {
                        try {
                            self.loading = true
                            var res = await axios.delete(App.url + 'planner/task_member', {
                                params: {
                                    id: id
                                }
                            })
                            if (!res.data.status) throw res.data
                            App.successMsg()
                        } catch (err) {
                            App.errorMsg(err)
                        }
                        self.loading = false
                        self.getMember()
                        resolve(true)
                    });
            },
            deleteWatcher: function(id, name) {
                var self = this
                var c = confirm("Are you sure to delete " + name + " as watcher")
                if (c)
                    return new Promise(async (resolve, reject) => {
                        try {
                            self.loading = true
                            var res = await axios.delete(App.url + 'planner/watcher', {
                                params: {
                                    id: id
                                }
                            })
                            if (!res.data.status) throw res.data
                            App.successMsg()
                        } catch (err) {
                            App.errorMsg(err)
                        }
                        self.loading = false
                        self.getWatcher()
                        resolve(true)
                    });
            },
            getTask: function() {
                var self = this
                return new Promise(async (resolve, reject) => {
                    try {
                        var res = await axios.get(App.url + 'planner/task', {
                            params: vTableParam({
                                filter: {
                                    id: self.taskId
                                },
                                limit: -1
                            })
                        })
                        if (!res.data.status) throw res.data
                        if (res.data.data.length == 0) throw 'Task not found!'
                        res.data.data.map(val => {
                            Object.keys(val).forEach(function(key) {
                                var v = val[key]
                                if (key)
                                    if (key.match('__')) {
                                        var keys = key.split('__')
                                        var value = v != undefined ? v.split('=__=')[1] : null
                                        var alias = v != undefined ? v.split('=__=')[0] : null
                                        val['real_' + keys[0]] = v
                                        val['alias_' + keys[0]] = alias
                                        val[key] = value
                                        self.fieldColumns['alias_' + keys[0]] = true
                                    }
                            });
                        })
                        self.data = res.data.data[0]
                        self.projectId = self.data.project_id
                        self.formAddMember[0].options.project_id = self.projectId
                        self.formAddMember = App.updateObject(self.formAddMember)
                        self.getAccess()
                        await self.getProject()
                        if (self.data.parent_id !== null) {
                            self.parentTaskId = self.data.parent_id
                            await self.getParentTask()
                        } else {
                            self.parentTaskId = null
                            self.parent = null
                        }
                        await self.getTaskField(self.data.category_id)
                    } catch (err) {
                        App.errorMsg(err)
                    }
                    resolve(true)
                });
            },
            getParentTask: function() {
                var self = this
                return new Promise(async (resolve, reject) => {
                    try {
                        var res = await axios.get(App.url + 'planner/task', {
                            params: vTableParam({
                                filter: {
                                    id: self.parentTaskId
                                },
                                limit: -1
                            })
                        })
                        if (!res.data.status) throw res.data
                        if (res.data.data.length == 0) throw 'Parent Task not found!'
                        self.parent = res.data.data[0]
                    } catch (err) {
                        App.errorMsg(err)
                    }
                    resolve(true)
                });
            },
            getChildren: function() {
                var self = this
                return new Promise(async (resolve, reject) => {
                    try {
                        var res = await axios.get(App.url + 'planner/task', {
                            params: vTableParam({
                                filter: {
                                    parent_id: self.taskId
                                },
                                limit: -1
                            })
                        })
                        if (!res.data.status) throw res.data
                        self.children = res.data.data
                    } catch (err) {
                        App.errorMsg()
                    }
                    resolve(true)
                });
            },
            getReference: function() {
                var self = this
                return new Promise(async (resolve, reject) => {
                    try {
                        var res = await axios.get(App.url + 'planner/reference', {
                            params: vTableParam({
                                filter: {
                                    task_id: self.taskId
                                },
                                limit: -1
                            })
                        })
                        if (!res.data.status) throw res.data
                        var tmp = {}
                        res.data.data.map(val => {
                            if (tmp[val.category] == undefined)
                                tmp[val.category] = []
                            tmp[val.category].push(val)
                        })
                        self.reference = tmp
                    } catch (err) {
                        App.errorMsg(err)
                    }
                    resolve(true)
                });
            },
            getMember: function() {
                var self = this
                return new Promise(async (resolve, reject) => {
                    try {
                        var res = await axios.get(App.url + 'planner/task_member', {
                            params: vTableParam({
                                filter: {
                                    task_id: self.taskId
                                },
                                limit: -1
                            })
                        })
                        if (!res.data.status) throw res.data

                        self.members = res.data.data
                    } catch (err) {
                        App.errorMsg(err)
                    }
                    resolve(true)
                });
            },
            getWatcher: function() {
                var self = this
                return new Promise(async (resolve, reject) => {
                    try {
                        var res = await axios.get(App.url + 'planner/watcher', {
                            params: vTableParam({
                                filter: {
                                    task_id: self.taskId
                                },
                                limit: -1
                            })
                        })
                        if (!res.data.status) throw res.data
                        var w = []
                        res.data.data.map(val => {
                            w.push(val.username)
                        })
                        self.watcher_username = w
                        self.watcher = res.data.data
                    } catch (err) {
                        App.errorMsg(err)
                    }
                    resolve(true)
                });
            },
            getData: async function() {
                var self = this
                try {
                    self.loading = true
                    var self = this,
                        proms = []
                    proms.push(self.getTask())
                    proms.push(self.getRelated())
                    proms.push(self.getMember())
                    proms.push(self.getWatcher())
                    proms.push(self.getNotes())
                    proms.push(self.getReference())
                    proms.push(self.getReference())
                    proms.push(self.getChildren())
                    await Promise.all(proms)
                } catch (err) {
					console.log(err)
                }
                self.loading = false
            },
            getTaskField: async function(id) {
                try {
                    var self = this
                    self.loading = true
                    var res = await axios.get(App.url + 'planner/field/task_category', {
                        params: {
                            category_id: id
                        }
                    })
                    var tmp = []
                    var fw = "calc(50% - 16px)"
                    if (['xs', 'sm'].includes(App.breakpoint))
                        fw = "100%"
                    else if (['md'].includes(App.breakpoint))
                        fw = "calc(50% - 16px)"
                    else if (['lg', 'xl'].includes(App.breakpoint))
                        fw = "calc(25% - 16px)"

                    res.data.data.map(function(val) {
                        try {
                            var values = val.task_value/*.slice(2)*/.split(';;').map((val) => {
                                var s = val.split('$$')
								//console.log(s, val)
                                return {
                                    text: s[1],
                                    value: val.field_type == 'int' ? Number(s[0]) : s[0].toString()
                                }
                            })
                        } catch (err) {
                            var values = []
                        }

                        var def = null;
                        if (val.default_value != null) {
                            if (val.default_value.trim() != '') {
                                if (val.field_type == 'int')
                                    def = Number(val.default_value)
                                else
                                    def = val.default_value.toString()
                            }
                        }

                        var url = (val.url || '').trim()

                        var params = JSONfn.parse((val.params || `
							{
								"options": {
									"filter": {},
									"filterType": {},
									"filterCondition": {}
								},
								"searchby": [],
								"formatter": [null, null]
							}
						`).trim())

                        var field = {
                            "text": val.name,
                            "value": val.name + '__' + val.id,
                            "align": "start",
                            "sortable": true,
                            "filterable": false,
                            "divider": false,
                            "class": "",
                            "width": "auto",
                            "formWidth": fw,
                            "type": val.field_type,
                            "disabled": false,
                            "visible": true,
                            "required": false,
                            "form": true,
                            "filter": true,
                            "groupable": false,
                            "default": def,
                            "paging": true,
                            "page": 0,
                            "limit": 10,
                            "data_value": values,
                            "alias": "alias_" + val.name,
                            "url": url == '' ? false : App.url + url,
                            "options": params.options,
                            "searchby": params.searchby,
                            "formatter": params.formatter
                        }
                        tmp.push(field)
                        if (field.value == "Assigned to__4") {
                            field.options.project_id = self.projectId
                        }
                    })

                    self.headersDef.map(val => {
                        if (!['subject', 'description'].includes(val.value))
                            val.formWidth = fw
                    })
                    self.headersTask = tmp
                } catch (err) {
                    console.log(err)
                }
                self.loading = false
            },
            getAccess: async function() {
                var self = this
                return new Promise(async (resolve, reject) => {
                    try {
                        var res = await axios.get(App.url + 'planner/access/access', {
                            params: {
                                project_id: self.projectId
                            }
                        })
                        self.access = res.data
                    } catch (err) {
                        App.errorMsg(err)
                    }
                    resolve(true)
                });
            },
            allow: function(check) {
                var self = this,
                    ok = false
                if (self.access.project.access) {
                    if (self.access.project.access.includes('$author$')) {
                        if ([1, 5, 6, 7, 8, 9, 10].includes(Number(check)) || self.access.project.access.includes(check.toString())) {
                            ok = true
                        }
                    } else {
                        if (self.access.project.access.includes(check.toString())) {
                            ok = true
                        }
                    }
                }
                return ok
            },
            shortName: function(name) {
                if (!name)
                    return name
                var s = name.split(/\s+/)
                if (s[0][0].toLowerCase() == 'f' && s[1][0].toLowerCase() == 'x') {
                    s = s.slice(2)
                }
                var n = [s[0]] //, s[s.length-1]]
                if (s.length > 2) {
                    n.push(s[1][0] + '.')
                }
                n.push(s[s.length - 1])
                return n.join(' ')
            }
        },
        mounted: function() {
            var self = this
            self.taskId = location.hash.split('/')[4]
            self.taskOptions = {
                task_id: Number(self.taskId)
            }
            self.getData()
            self.left = parseFloat(window.getComputedStyle(document.querySelector('.main-container-planner')).marginLeft)
        }
    }
</script>