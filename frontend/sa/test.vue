<template>
  <div></div>
</template>

<style></style>

<script>
module.exports = {
  name: "",
  data: function () {
    return {};
  },
  methods: {
    PDFtoIMG(url) {
      return new Promise(async (resolve, reject) => {
        const existingPdfBytes = await fetch(url).then((res) =>
          res.arrayBuffer(),
        );
        const fileArray = new Uint8Array(existingPdfBytes);
        const doc = await pdfjsLib.getDocument({
          data: fileArray,
          useSystemFonts: true,
        }).promise;

        console.log("PDFtoIMG: url, doc", url, doc);
        const pages = [];
        const count = 1;

        for (let i = 1; i < doc.numPages + 1; i++) {
          const page = await doc.getPage(i);
          const viewport = page.getViewport({
            scale: 1.5,
          });
          const canvas = document.createElement("canvas");
          const ctx = canvas.getContext("2d");
          canvas.width = viewport.width;
          canvas.height = viewport.height;
          const task = page.render({
            canvasContext: ctx,
            viewport: viewport,
          });
          task.promise.then(() => {
            pages.push(canvas.toDataURL());
            if (count == doc.numPages) {
              resolve(pages);
            }
          });
        }
      });
    },
  },
  mounted: function () {
    var self = this;
    loadMultipleLibrary(
      "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js;https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js",
    )
      .then(async function (val) {
        var dat = await self.PDFtoIMG(
          "https://main.buanamultiteknik.com/api/uploads/rfq1508/1715764994_eadacac40f877e1febf9.pdf",
        );
        console.log(dat);
      })
      .catch(function (err) {});
  },
};
</script>
