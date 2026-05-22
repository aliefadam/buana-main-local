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
    <v-btn color="success" @click="sendPrint">Print</v-btn>
    <div id="report" style="padding: 8px">
      <table class="main">
        <tr>
          <td colspan="2" style="font-weight: bold; vertical-align: top">
            <img
              src="./img/logo.png"
              style="display: inline-block; margin-right: 75px"
            />PURCHASE ORDER
          </td>
          <td colspan="2" style="font-weight: bold"></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td style="width: 100%"></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td style="font-weight: bold">Kpd Yth.</td>
          <td style="width: 100%"></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td style="font-weight: bold">
            {{ supplier_name }}
          </td>
          <td style="width: 100%"></td>
          <td>PO</td>
          <td>&nbsp;: {{ po_no }}</td>
        </tr>
        <tr>
          <td>
            {{ (address || "").split("\n")[0] }}
          </td>
          <td style="width: 100%"></td>
          <td>Tanggal</td>
          <td>&nbsp;: {{ po_date }}</td>
        </tr>
        <tr>
          <td>
            {{ (address || "").split("\n")[1] }}
          </td>
          <td style="width: 100%"></td>
          <td>Perihal</td>
          <td>&nbsp;: {{ perihal }}</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td style="width: 100%"></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>
            Up : <span style="font-weight: bold">{{ pic_name }}</span>
          </td>
          <td style="width: 100%"></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td colspan="4">
            Sesuai dengan Penawaran yang kami terima, maka dengan ini kami
            ajukan PO sebagai berikut:
          </td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td style="width: 100%"></td>
          <td></td>
          <td></td>
        </tr>
      </table>
      <table class="tbl">
        <thead>
          <tr>
            <th>No</th>
            <th style="width: 100%">Nama Barang</th>
            <th>Qty</th>
            <th>Unit</th>
            <th>Harga Satuan</th>
            <th>Total</th>
          </tr>
        </thead>
        <template v-for="(item, index) in items">
          <tr>
            <td>{{ index + 1 }}</td>
            <td style="font-weight: bold">{{ item.item_name }}</td>
            <td>{{ item.order_qty }}</td>
            <td>{{ item.unit }}</td>
            <td>{{ item.price_per_item }}</td>
            <td>{{ item.total_price_per_item }}</td>
          </tr>
          <tr>
            <td></td>
            <td>{{ item.original_manufacture }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td>{{ item.manufacture_pn }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <template
            v-if="item.specification != null && item.specification != undefined"
          >
            <tr
              v-for="(item, index) in item.specification.split('\n')"
              :key="index"
            >
              <td></td>
              <td>{{ item }}</td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
          </template>
          <tr>
            <td></td>
            <td>&nbsp;</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
        </template>
        <tr class="withborder">
          <td colspan="6">&nbsp;</td>
        </tr>
        <tr class="withborder">
          <td colspan="5" style="text-align: center; font-weight: bold">
            Total =
          </td>
          <td style="font-weight: bold">{{ total }}</td>
        </tr>
        <tr class="withborder">
          <td colspan="6">Terbilang</td>
        </tr>
        <tr class="withborder">
          <td colspan="6" style="font-weight: bold">
            {{ terbilang(total) }} Rupiah
          </td>
        </tr>
      </table>

      <table class="main">
        <tr>
          <td style="width: 75px; display: block">&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td colspan="3" style="font-weight: bold; text-decoration: underline">
            Keterangan
          </td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td style="width: 75px"></td>
          <td style="font-weight: bold; text-align: center">
            PT. BMT BUANA MULTI TEKNIK
          </td>
          <td style="width: 100%"></td>
        </tr>
        <tr>
          <td style="width: 75px"></td>
          <td style="height: 75px; text-align: center"></td>
          <td style="width: 100%"></td>
        </tr>
        <tr>
          <td style="width: 75px"></td>
          <td
            style="
              font-weight: bold;
              text-decoration: underline;
              text-align: center;
            "
          >
            R. DARMAGIRI
          </td>
          <td style="width: 100%"></td>
        </tr>
        <tr>
          <td style="width: 75px"></td>
          <td style="font-weight: bold; text-align: center">Direktur</td>
          <td style="width: 100%"></td>
        </tr>
      </table>
    </div>
  </v-container>
</template>

<style scoped>
#report {
  size: A4;
  margin: 0;
  width: 210mm;
  /* height: 297mm; */
}
.main {
  table-layout: fixed;
  border-collapse: collapse;
  border: 0;
}
.main td,
.main th {
  white-space: nowrap;
}
.tbl {
  table-layout: fixed;
  border-collapse: collapse;
  border: 1px solid black;
}
.tbl td,
.tbl th {
  border: 1px solid black;
  padding: 0 4px;
}
.tbl td {
  border-bottom: 0px;
  border-top: 0px;
}
.tbl th {
  white-space: nowrap;
  font-weight: bold;
  background-color: #cccccc;
}
.tbl tr.withborder td {
  border: 1px solid black !important;
}
</style>

<script>
module.exports = {
  name: "",
  props: {
    value: undefined,
    data: {
      type: Object,
    },
    tableOnly: {
      type: Boolean,
      default: false,
    },
  },
  data: function () {
    return {
      address: "",
      supplier_name: "",
      pic_name: "",
      perihal: "",
      po_date: "",
      po_no: "",
      total: 0,
      items: [],
    };
  },
  methods: {
    terbilang: function (a) {
      if (!isNaN(Number(a))) {
        if (Number(a) == 0) return "0";
      } else {
      }

      var c =
        " Satu Dua Tiga Empat Lima Enam Tujuh Delapan Sembilan Sepuluh Sebelas".split(
          " ",
        );
      if (12 > a) var b = c[a];
      else
        20 > a
          ? (b = c[a - 10] + " Belas")
          : 100 > a
            ? ((b = parseInt(String(a / 10).substr(0, 1))),
              (b = c[b] + " Puluh " + c[a % 10]))
            : 200 > a
              ? (b = "Seratus " + terbilang(a - 100))
              : 1e3 > a
                ? ((b = parseInt(String(a / 100).substr(0, 1))),
                  (b = c[b] + " Ratus " + terbilang(a % 100)))
                : 2e3 > a
                  ? (b = "Seribu " + terbilang(a - 1e3))
                  : 1e4 > a
                    ? ((b = parseInt(String(a / 1e3).substr(0, 1))),
                      (b = c[b] + " Ribu " + terbilang(a % 1e3)))
                    : 1e5 > a
                      ? ((b = parseInt(String(a / 100).substr(0, 2))),
                        (a %= 1e3),
                        (b = terbilang(b) + " Ribu " + terbilang(a)))
                      : 1e6 > a
                        ? ((b = parseInt(String(a / 1e3).substr(0, 3))),
                          (a %= 1e3),
                          (b = terbilang(b) + " Ribu " + terbilang(a)))
                        : 1e8 > a
                          ? ((b = parseInt(String(a / 1e6).substr(0, 4))),
                            (a %= 1e6),
                            (b = terbilang(b) + " Juta " + terbilang(a)))
                          : 1e9 > a
                            ? ((b = parseInt(String(a / 1e6).substr(0, 4))),
                              (a %= 1e6),
                              (b = terbilang(b) + " Juta " + terbilang(a)))
                            : 1e10 > a
                              ? ((b = parseInt(String(a / 1e9).substr(0, 1))),
                                (a %= 1e9),
                                (b = terbilang(b) + " Milyar " + terbilang(a)))
                              : 1e11 > a
                                ? ((b = parseInt(String(a / 1e9).substr(0, 2))),
                                  (a %= 1e9),
                                  (b =
                                    terbilang(b) + " Milyar " + terbilang(a)))
                                : 1e12 > a
                                  ? ((b = parseInt(
                                      String(a / 1e9).substr(0, 3),
                                    )),
                                    (a %= 1e9),
                                    (b =
                                      terbilang(b) + " Milyar " + terbilang(a)))
                                  : 1e13 > a
                                    ? ((b = parseInt(
                                        String(a / 1e10).substr(0, 1),
                                      )),
                                      (a %= 1e10),
                                      (b =
                                        terbilang(b) +
                                        " Triliun " +
                                        terbilang(a)))
                                    : 1e14 > a
                                      ? ((b = parseInt(
                                          String(a / 1e12).substr(0, 2),
                                        )),
                                        (a %= 1e12),
                                        (b =
                                          terbilang(b) +
                                          " Triliun " +
                                          terbilang(a)))
                                      : 1e15 > a
                                        ? ((b = parseInt(
                                            String(a / 1e12).substr(0, 3),
                                          )),
                                          (a %= 1e12),
                                          (b =
                                            terbilang(b) +
                                            " Triliun " +
                                            terbilang(a)))
                                        : 1e16 > a &&
                                          ((b = parseInt(
                                            String(a / 1e15).substr(0, 1),
                                          )),
                                          (a %= 1e15),
                                          (b =
                                            terbilang(b) +
                                            " Kuadriliun " +
                                            terbilang(a)));
      a = b.split(" ");
      c = [];
      for (b = 0; b < a.length; b++) "" != a[b] && c.push(a[b]);
      return c.join(" ");
    },

    getData: async function () {
      var self = this;

      var result = await axios.get(App.url + "bom/purchase_order_item_group", {
        params: {
          purchase_order_id: self.data.purchase_order_id,
          filter: {},
          filterType: {},
          filterCondition: {},
        },
      });

      if (!result.data.status) App.errorMsg();

      var po = await axios.get(App.url + "bom/purchaseorder", {
        params: {
          id: self.data.purchase_order_id,
          filter: {},
          filterType: {},
          filterCondition: {},
        },
      });

      if (!po.data.status) App.errorMsg();
      po = po.data.data[0];
      self.supplier_name = po.supplier_name;
      self.pic_name = po.pic_name;
      self.address = po.address;
      self.po_no = po.po_no;
      self.po_date = po.po_date;
      self.perihal = po.title;
      var total = 0;
      result.data.data.map(function (val) {
        total += Number(val.total_price_per_item);
      });

      self.total = total;
      self.items = result.data.data.filter((val) => val.item_id != null);
    },
    sendPrint: async function () {
      var self = this;
      /* var res = await axios.post("https://main.buanamultiteknik.com/report.php", {
                    html: document.getElementById('report').serializeWithStyles()
                })

                window.open("https://main.buanamultiteknik.com/"+res.data) */
      var element = document.getElementById("element-to-print");
      html2pdf(element);
    },
  },
  mounted: function () {
    this.getData();
  },
};
</script>
