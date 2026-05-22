<template>
  <v-container
    style="
      padding: 0 !important;
      height: 100%;
      display: flex;
      flex-direction: column;
      margin: 0;
      width: 100%;
      max-width: 100%;
    "
  >
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
            <div
              style="text-align: center; font-weight: bold; margin-bottom: 20px"
            >
              PROGRESS REBUILD MACHINE PER SECTION: TOTAL SUBASSEMBLY
            </div>
            <div>
              <v-chart
                style="min-width: 350px; max-width: 100%; height: 350px"
                :default-options="chartOpt2"
                v-model="data2"
              ></v-chart>
            </div>

            <div>
              <v-row align="center" justify="center">
                <v-col>
                  <v-card
                    class="mx-auto"
                    max-width="200"
                    style="border: 1px solid #0099ff"
                  >
                    <v-card-text>
                      <p class="text-h4 text--primary text-center">VE</p>
                      <v-divider></v-divider>
                      <v-card-actions>
                        <v-btn
                          color="blue lighten-2"
                          text
                          @click="openSubassyVE"
                        >
                          Subassembly List
                        </v-btn>
                      </v-card-actions>
                    </v-card-text>
                  </v-card>
                </v-col>
                <v-col>
                  <v-card
                    class="mx-auto"
                    max-width="200"
                    style="border: 1px solid #00ff55"
                  >
                    <v-card-text>
                      <p class="text-h4 text--primary text-center">SE</p>
                      <v-divider></v-divider>
                      <v-card-actions>
                        <v-btn
                          color="blue lighten-2"
                          text
                          @click="openSubassySE"
                        >
                          Subassembly List
                        </v-btn>
                      </v-card-actions>
                    </v-card-text>
                  </v-card>
                </v-col>
                <v-col>
                  <v-card
                    class="mx-auto"
                    max-width="200"
                    style="border: 1px solid #ffff00"
                  >
                    <v-card-text>
                      <p class="text-h4 text--primary text-center">MAX</p>
                      <v-divider></v-divider>
                      <v-card-actions>
                        <v-btn
                          color="blue lighten-2"
                          text
                          @click="openSubassyMAX"
                        >
                          Subassembly List
                        </v-btn>
                      </v-card-actions>
                    </v-card-text>
                  </v-card>
                </v-col>
                <v-col>
                  <v-card
                    class="mx-auto"
                    max-width="200"
                    style="border: 1px solid #ff6600"
                  >
                    <v-card-text>
                      <p class="text-h4 text--primary text-center">HCF</p>
                      <v-divider></v-divider>
                      <v-card-actions
                        ><v-btn
                          color="blue lighten-2"
                          text
                          @click="openSubassyHCF"
                        >
                          Subassembly List
                        </v-btn>
                      </v-card-actions>
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>
            </div>
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
  data: function () {
    return {
      loading: true,
      headers: [{}],
      ctx: false,
      canvas: false,
      origin: null,
      words: [],
      words2: [],
      chartOpt: {
        tooltip: {
          trigger: "item",
        },
        legend: {
          top: "5%",
          left: "center",
        },
        yAxis: [],
        xAxis: [],
        series: [
          {
            name: "",
            type: "pie",
            radius: ["40%", "70%"],
            avoidLabelOverlap: false,
            itemStyle: {
              borderRadius: 10,
              borderColor: "#fff",
              borderWidth: 2,
            },
            label: {
              show: false,
              position: "center",
            },
            emphasis: {
              label: {
                show: true,
                fontSize: "40",
                fontWeight: "bold",
              },
            },
            labelLine: {
              show: false,
            },
          },
        ],
      },
      data: {
        0: [],
      },
      chartOpt2: {
        tooltip: {
          trigger: "item",
        },
        legend: {
          top: "5%",
          left: "center",
          show: false,
        },
        yAxis: [],
        xAxis: [],
        series: [
          {
            name: "",
            type: "pie",
            radius: ["60%", "70%"],
            labelLine: {
              length: 60,
            },
            label: {
              formatter: "{b}: {c}",
            },
            itemStyle: {
              borderRadius: 10,
              borderColor: "#fff",
              borderWidth: 2,
            },
          },
          {
            name: "",
            type: "pie",
            radius: ["48%", "58%"],
            labelLine: {
              length: 50,
            },
            label: {
              formatter: "{b}: {c}",
            },
            itemStyle: {
              borderRadius: 10,
              borderColor: "#fff",
              borderWidth: 2,
            },
          },
        ],
      },
      data2: {
        0: [],
      },
    };
  },
  methods: {
    getData: async function () {
      try {
        var self = this;
        var res = await axios.get(App.url + "rebuildmachine/report/chart", {
          params: {},
        });
        var data = res.data.data[0];
        var tmp = {
          0: [
            {
              name: "VE",
              value: Number((data.reported_ve / data.total_ve) * 100).toFixed(
                2,
              ),
            },
            {
              name: "SE",
              value: Number((data.reported_se / data.total_se) * 100).toFixed(
                2,
              ),
            },
            {
              name: "MAX",
              value: Number((data.reported_max / data.total_max) * 100).toFixed(
                2,
              ),
            },
            {
              name: "HCF",
              value: Number((data.reported_hcf / data.total_hcf) * 100).toFixed(
                2,
              ),
            },
          ],
        };
        self.data = tmp;
      } catch (error) {}
    },
    getData2: async function () {
      try {
        var self = this;
        var res = await axios.get(App.url + "rebuildmachine/report/chart2", {
          params: {},
        });
        var data = {};
        res.data.data.map((v) => {
          data[v.type_subassy] = v;
        });
        var tmp = {
          0: [
            {
              name: "VE Total",
              value: Number(data.VE.complete) + Number(data.VE.incomplete),
              itemStyle: {
                color: "#0099ff",
              },
            },
            {
              name: "SE Total",
              value: Number(data.SE.complete) + Number(data.SE.incomplete),
              itemStyle: {
                color: "#00ff55",
              },
            },
            {
              name: "MAX Total",
              value: Number(data.MAX.complete) + Number(data.MAX.incomplete),
              itemStyle: {
                color: "#ffff00",
              },
            },
            {
              name: "HCF Total",
              value: Number(data.HCF.complete) + Number(data.HCF.incomplete),
              itemStyle: {
                color: "#ff6600",
              },
            },
          ],
          1: [
            {
              name: "VE Complete",
              value: data.VE.complete,
              itemStyle: {
                color: "#b3e0ff",
              },
            },
            {
              name: "VE Incomplete",
              value: data.VE.incomplete,
              itemStyle: {
                color: "#007acc",
              },
            },
            {
              name: "SE Complete",
              value: data.SE.complete,
              itemStyle: {
                color: "#99ffbb",
              },
            },
            {
              name: "SE Incomplete",
              value: data.SE.incomplete,
              itemStyle: {
                color: "#00b33c",
              },
            },
            {
              name: "MAX Complete",
              value: data.MAX.complete,
              itemStyle: {
                color: "#ffff99",
              },
            },
            {
              name: "MAX Incomplete",
              value: data.MAX.incomplete,
              itemStyle: {
                color: "#cccc00",
              },
            },
            {
              name: "HCF Complete",
              value: data.HCF.complete,
              itemStyle: {
                color: "#ffa366",
              },
            },
            {
              name: "HCF Incomplete",
              value: data.HCF.incomplete,
              itemStyle: {
                color: "#b34700",
              },
            },
          ],
        };
        self.data2 = tmp;
      } catch (error) {}
    },
    getData3: async function () {
      try {
        var self = this;
        var res = await axios.get(App.url + "rebuildmachine/report/list", {
          params: {},
        });
        var data = {};
        res.data.data.map((v) => {
          data[v.type_subassy] = v;
        });
        var tmp = {
          0: [
            {
              name: "VE Total",
              value: Number(data.VE.complete) + Number(data.VE.incomplete),
            },
            {
              name: "SE Total",
              value: Number(data.SE.complete) + Number(data.SE.incomplete),
            },
            {
              name: "MAX Total",
              value: Number(data.MAX.complete) + Number(data.MAX.incomplete),
            },
            {
              name: "HCF Total",
              value: Number(data.HCF.complete) + Number(data.HCF.incomplete),
            },
          ],
          1: [
            {
              name: "VE Complete",
              value: data.VE.complete,
              itemStyle: {
                color: "#91cc75",
              },
            },
            {
              name: "VE Incomplete",
              value: data.VE.incomplete,
              itemStyle: {
                color: "#ee6666",
              },
            },
            {
              name: "SE Complete",
              value: data.SE.complete,
              itemStyle: {
                color: "#91cc75",
              },
            },
            {
              name: "SE Incomplete",
              value: data.SE.incomplete,
              itemStyle: {
                color: "#ee6666",
              },
            },
            {
              name: "MAX Complete",
              value: data.MAX.complete,
              itemStyle: {
                color: "#91cc75",
              },
            },
            {
              name: "MAX Incomplete",
              value: data.MAX.incomplete,
              itemStyle: {
                color: "#ee6666",
              },
            },
            {
              name: "HCF Complete",
              value: data.HCF.complete,
              itemStyle: {
                color: "#91cc75",
              },
            },
            {
              name: "HCF Incomplete",
              value: data.HCF.incomplete,
              itemStyle: {
                color: "#ee6666",
              },
            },
          ],
        };
        self.data2 = tmp;
      } catch (error) {}
    },
    openSubassyVE: function () {
      var self = this;
      var type_subassy = "VE";
      var w = screen.width * 0.75;
      var h = screen.height * 0.5;
      var left = screen.width / 2 - w / 2;
      var top = screen.height / 2 - h / 2;
      window.open(
        "https://main.buanamultiteknik.com/api/report/rebuild/Listsubassemlydashboard?type_subassy=" +
          type_subassy,
        `scrollbars=yes,resizable=yes,status=no,location=no,toolbar=no,menubar=no,width=${w},height=${h},left=${left},top=${top}`,
      );
    },
    openSubassySE: function () {
      var self = this;
      var type_subassy = "SE";
      var w = screen.width * 0.75;
      var h = screen.height * 0.5;
      var left = screen.width / 2 - w / 2;
      var top = screen.height / 2 - h / 2;
      window.open(
        "https://main.buanamultiteknik.com/api/report/rebuild/Listsubassemlydashboard?type_subassy=" +
          type_subassy,
        `scrollbars=yes,resizable=yes,status=no,location=no,toolbar=no,menubar=no,width=${w},height=${h},left=${left},top=${top}`,
      );
    },
    openSubassyMAX: function () {
      var self = this;
      var type_subassy = "MAX";
      var w = screen.width * 0.75;
      var h = screen.height * 0.5;
      var left = screen.width / 2 - w / 2;
      var top = screen.height / 2 - h / 2;
      window.open(
        "https://main.buanamultiteknik.com/api/report/rebuild/Listsubassemlydashboard?type_subassy=" +
          type_subassy,
        `scrollbars=yes,resizable=yes,status=no,location=no,toolbar=no,menubar=no,width=${w},height=${h},left=${left},top=${top}`,
      );
    },
    openSubassyHCF: function () {
      var self = this;
      var type_subassy = "HCF";
      var w = screen.width * 0.75;
      var h = screen.height * 0.5;
      var left = screen.width / 2 - w / 2;
      var top = screen.height / 2 - h / 2;
      window.open(
        "https://main.buanamultiteknik.com/api/report/rebuild/Listsubassemlydashboard?type_subassy=" +
          type_subassy,
        `scrollbars=yes,resizable=yes,status=no,location=no,toolbar=no,menubar=no,width=${w},height=${h},left=${left},top=${top}`,
      );
    },
  },
  mounted: function () {
    var self = this;
    this.getData();
    this.getData2();
    this.getData3();
  },
};
</script>
