<template>
  <v-container
    class="apps-dashboard"
    style="
      padding: 1.5em !important;
      height: 100%;
      display: flex;
      flex-direction: column;
      margin: 0;
      width: 100%;
      max-width: 100%;
    "
  >
    <div class="welcome-header">
      <div class="welcome-company">Internal Buana Multi Teknik</div>
      <div class="text-h5 font-weight-bold welcome-text">
        Welcome, {{ App.userData.data[0].name }}
      </div>
      <div class="text-body-2 welcome-sub">Dashboard Aplikasi</div>
    </div>
    <div class="section-divider">
      <span class="divider-label">Modul Tersedia</span>
    </div>
    <v-row
      flex-col
      transition="slide-y-transition"
      style="padding: 0.25em; flex: 0"
    >
      <template v-for="(item, index) in items" :key="index">
        <v-col cols="12" md="4" lg="3" xl="2">
          <v-card
            flat
            :to="item.url"
            class="appbox"
            :style="`background: ${item.background}; color: ${item.color}`"
          >
            <div class="appbox-icon">
              <v-img
                :src="'./img/' + item.icon"
                width="52"
                height="52"
                contain
              ></v-img>
            </div>
            <div class="appbox-label">{{ item.name }}</div>
          </v-card>
        </v-col>
      </template>
      <v-col cols="12" md="4" lg="3" xl="2">
        <v-card
          flat
          href="https://panel.buanamultiteknik.com"
          class="appbox appbox-default"
        >
          <div class="appbox-icon">
            <v-img
              src="https://main.buanamultiteknik.com/img/moneycard.svg"
              width="52"
              height="52"
              contain
            ></v-img>
          </div>
          <div class="appbox-label">Sales Force</div>
        </v-card>
      </v-col>
      <v-col cols="12" md="4" lg="3" xl="2">
        <v-card
          flat
          href="https://logistic.buanamultiteknik.com"
          class="appbox appbox-default"
        >
          <div class="appbox-icon">
            <v-img
              src="https://main.buanamultiteknik.com/img/logistic.svg"
              width="52"
              height="52"
              contain
            ></v-img>
          </div>
          <div class="appbox-label">Logistic</div>
        </v-card>
      </v-col>
      <v-col cols="12" md="4" lg="3" xl="2">
        <v-card
          flat
          href="https://panel.buanamultiteknik.com/ticket/ticket"
          class="appbox"
          style="background: #d66360; color: #fff"
        >
          <div class="appbox-icon">
            <v-img
              src="https://main.buanamultiteknik.com/img/ticket.svg"
              width="52"
              height="52"
              contain
            ></v-img>
          </div>
          <div class="appbox-label">Ticketing</div>
        </v-card>
      </v-col>
      <v-col cols="12" md="4" lg="3" xl="2">
        <v-card
          flat
          href="https://finances.buanamultiteknik.com"
          class="appbox"
          style="background: rgb(95 154 87); color: #fff"
        >
          <div class="appbox-icon">
            <v-img
              src="https://main.buanamultiteknik.com/img/finance-2.svg"
              width="52"
              height="52"
              contain
            ></v-img>
          </div>
          <div class="appbox-label">Finance</div>
        </v-card>
      </v-col>
      <v-col cols="12" md="4" lg="3" xl="2">
        <v-card
          flat
          href="https://panel.buanamultiteknik.com/ticket/ticket-warehouse"
          class="appbox"
          style="background: rgb(120 179 240); color: #fff"
        >
          <div class="appbox-icon">
            <v-img
              src="https://main.buanamultiteknik.com/img/ticket-workshop.png"
              width="52"
              height="52"
              contain
            ></v-img>
          </div>
          <div class="appbox-label">Ticket Workshop</div>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<!-- <style>
/* @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"); */
@import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap");

* {
  /* font-family: "Inter", sans-serif !important; */
  font-family: "Plus Jakarta Sans", sans-serif;
}
</style> -->

<style scoped>
/* @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"); */
@import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap");

* {
  /* font-family: "Inter", sans-serif !important; */
  font-family: "Plus Jakarta Sans", sans-serif;
}

.welcome-header {
  padding: 1.5em 0.5em 1em 0.5em;
  margin-top: 0.75em;
  margin-bottom: 0;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.welcome-company {
  font-size: 0.75em;
  font-weight: 600;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  opacity: 0.45;
  margin-bottom: 6px;
  font-family: "Plus Jakarta Sans", sans-serif;
}

.welcome-text {
  line-height: 1.2;
  letter-spacing: -0.3px;
  font-family: "Plus Jakarta Sans", sans-serif !important;
}

.welcome-sub {
  opacity: 0.5;
  margin-top: 4px;
  font-size: 0.8em;
  font-family: "Plus Jakarta Sans", sans-serif !important;
}

.section-divider {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 1.25em 0.75em 0.5em 0.75em;
}

.section-divider::before,
.section-divider::after {
  content: "";
  flex: 1;
  height: 1px;
  background: rgba(0, 0, 0, 0.1);
}

.divider-label {
  font-size: 0.7em;
  font-weight: 600;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  opacity: 0.4;
  white-space: nowrap;
  font-family: "Plus Jakarta Sans", sans-serif;
}

.appbox {
  border-radius: 12px;
  border: 1px solid rgba(0, 0, 0, 0.08);
  height: 100px;
  display: flex;
  align-items: center;
  font-weight: 800;
  margin: 0.25em;
  margin-left: 0;
  margin-top: 0;
  cursor: pointer;
  font-size: 1.1em;
  padding: 0 16px;
  gap: 14px;
  transition:
    transform 0.15s ease,
    box-shadow 0.15s ease,
    filter 0.15s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  text-decoration: none !important;
}

.appbox:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
  filter: brightness(1.04);
}

.appbox:active {
  transform: translateY(0px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.appbox-default {
  background: #f5f5f5;
  color: #333;
}

.appbox-icon {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
  padding: 4px;
}

.appbox-default .appbox-icon {
  background: rgba(0, 0, 0, 0.06);
}

.appbox-label {
  flex: 1;
  line-height: 1.2;
}
</style>

<script>
module.exports = {
  name: "",
  props: {},
  data: function () {
    return {
      name: "users",
      items: [],
    };
  },
  methods: {
    getData: async function () {
      var self = this;
      var res = await axios.get(App.url + "admin/modules/available", {
        params: {},
      });
      self.items = res.data.data;
    },
  },
  mounted: function () {
    this.getData();
  },
};
</script>
