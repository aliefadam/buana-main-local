<template>
    <v-container style="
      padding: 0 !important;
      height: 100%;
      display: flex;
      flex-direction: column;
      margin: 0;
      width: 100%;
      max-width: 100%;
    ">
        <v-autoform v-model="headers"></v-autoform>

        <!-- <div>
            <v-app>
                <div>
                    <v-container>
                        <div style="text-align: center;  font-weight: bold; margin-bottom: 20px;">PROGRESS PER SECTION</div>
                    </v-container>
                </div>
            </v-app>
        </div> -->

        <div>
            <v-app>
                <div>
                    <v-container>
                        <!-- <div style="text-align: center; font-weight: bold; margin-bottom: 20px">
                            PERSENTASE PROGRESS REBUILD PER SECTION
                        </div>
                        <div>
                            <v-chart style="min-width: 350px; max-width: 100%; height: 350px" :default-options="chartOpt" v-model="data"></v-chart>
                        </div> -->
                        
                        <div style="text-align: center; font-weight: bold; margin-bottom: 30px">
                           REBUILD MACHINE 
                        </div>
                        <v-card
                            class="mx-auto"
                            max-width="300"
                        >
                            <v-img
                            class="white--text align-end"
                            height="200px"
                            src="https://internaldev.buanamultiteknik.com/api/uploads/assets/machine.jpeg"
                            >
                            <!-- <v-card-title>Protos 80C</v-card-title> -->
                            </v-img>

                            <!-- <v-card-subtitle class="pb-0">
                            Number 10
                            </v-card-subtitle> -->

                            <v-card-text class="text--primary">
                            <div>Protos 80C Machine</div>
                            </v-card-text>

                            <v-card-actions>
                            <v-btn
                                color="orange"
                                text
                                outlined
                                @click="showData1=true"
                                small
                            >
                            Add Activity
                            </v-btn>

                            <v-btn
                                color="orange"
                                text
                                outlined
                                small
                                @click="showData4=true"
                            >
                                Dashboard
                            </v-btn>
                            <v-btn
                                color="orange"
                                text
                                outlined
                                small
                                @click="showData5=true" v-if="check_user(['rossi', 'administrator'])"
                            >
                                Master
                            </v-btn>
                            </v-card-actions>
                        </v-card>
                        <br>
                        <v-card
                            class="mx-auto"
                            max-width="300"
                        >
                            <v-img
                            class="white--text align-end"
                            height="200px"
                            src="https://internaldev.buanamultiteknik.com/api/uploads/assets/logo450x2bmt.png"
                            >
                            <!-- <v-card-title>Protos 80 ROM</v-card-title> -->
                            </v-img>

                            <!-- <v-card-subtitle class="pb-0">
                            Number 10
                            </v-card-subtitle> -->

                            <v-card-text class="text--primary">
                            <div>Protos 80 ROM</div>
                            </v-card-text>

                            <v-card-actions>
                            <v-btn
                             color="orange"
                             text
                            outlined
                               @click="showData2=true"
                               small>
                            Add Activity
                            </v-btn>

                            <!--<v-btn-->
                            <!--    color="orange"-->
                            <!--    text-->
                            <!--    outlined-->
                            <!--    small-->
                            <!--    @click="" >-->
                            <!--    Dashboard-->
                            <!--</v-btn>-->
                            
                            <v-btn
                                color="orange"
                                text
                                outlined
                                small
                                @click="showData3=true" v-if="check_user(['rossi', 'administrator'])"
                            >
                                Master
                            </v-btn>
                            </v-card-actions>
                        </v-card>

                    <v-action-dialog :actions="false" v-model="showData1" title="Daily Report" min-height="75%" fullscreen>
                        <v-protos80c :key="selected.id" :data="dataid" :sel="processData" table-only :hide-default-footer="true"></v-protos80c>
                    </v-action-dialog>
                    <v-action-dialog :actions="false" v-model="showData2" title="Daily Report" min-height="75%" fullscreen>
                        <v-protos80rom></v-protos80rom>
                    </v-action-dialog>
                     <v-action-dialog :actions="false" v-model="showData3" title="List Protos 80C Reg 1" min-height="75%" fullscreen>
                        <v-partlistrebuild></v-partlistrebuild>
                    </v-action-dialog>
                    <v-action-dialog :actions="false" v-model="showData4" title="List Protos 80C Reg 1" min-height="75%" fullscreen>
                        <v-protos80cdashboard></v-protos80cdashboard>
                    </v-action-dialog>
                    <v-action-dialog :actions="false" v-model="showData5" title="List Protos 80C Reg 1" min-height="75%" fullscreen>
                        <v-protos80cmaster></v-protos80cmaster>
                    </v-action-dialog>
                    </v-container>
                </div>
            </v-app>
        </div>
    </v-container>
</template>

<style></style>

<script>
    module.exports = {
        name: "",
        components: {
            'v-protos80c': 'url:ui/rebuildmachine/reportentry.vue',
            'v-protos80rom': 'url:ui/rebuildmachine/report_rebuild_2_partlist.vue',
            'v-partlistrebuild': 'url:ui/rebuildmachine/partlistrebuild.vue',
            'v-action-dialog': 'url:ui/base/v-action-dialog.vue',
            'v-protos80cdashboard': 'url:ui/rebuildmachine/dashboard.vue',
            'v-protos80cmaster': 'url:ui/rebuildmachine/subassyentry.vue',
        },
        data: function() {
            return {
                showData1: false,
                showData2: false,
                showData3: false,
                showData4: false,
                showData5: false,
                processData: {},
                selected: false,
                dataid:{},
                loading: true,
                headers: [{}],
                ctx: false,
                canvas: false,
                origin: null,
                words: [],
                words2: [],
                data: {
                    0: [],
                },

            };
        },
        methods: {
			closeActionDialog: function(){
				var self = this
				self.value = false
				self.$emit('close')
                if (self.showData1 == true && self.showData2 == true) {
                    for (let index = 1; index < array.length; index++) {
                        self.showData[index] = false
                        
                    }
                }
			}
        },
        mounted: function() {
            // var self = this;
            // this.getData();
            // this.getData2();
            // this.getData3();
        },
    };
</script>