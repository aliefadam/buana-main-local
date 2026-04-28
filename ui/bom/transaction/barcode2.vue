<template>
    <table class="barcode-container" v-observe-visibility="createQr" style="border-collapse: separate !important; width:50%; padding : 0 !important;">
        <template v-for="(tr, trindex) in tmp" :key="index">
            <tr :key="trindex+randomId()">
                <td v-for="(item, index) in tr" :key="trindex+'-'+index">
                    <div style="display: flex; border: 1px solid black; width: 100mm; height: 35mm; clear:both;  page-break-after:always; align-self: center;" v-if="item.print.match(/\.a$/) != null">
                        <div style="display: inline-block; padding: 4px; display: inline-block; width: 15mm; height: 15mm; flex: 1 15mm; align-self: center;">
                            <!-- <div :id="'qrcode'+item.elid" :key="item+index"></div> -->
                            <img :id="'qrcode'+item.elid" :key="item+index" style="width: 15mm; height: 15mm;" />
                            <!-- <div style="font-weight: bold; text-align: center; padding-top: 2px;">{{item.id+'.'+item.item_id+'.'+item.bom_header_id+'.'+item.source}}</div> -->
                        </div>
                        <div style="display: flex; padding: 2px; flex: 0 290px; align-self: center;">
                            <table cellspacing="0" cellpadding="0" style="border: 0;">
                                <!--<tr>-->
                                <!--	<td style="text-wrap: nowrap; vertical-align: baseline;">Machine No </td>-->
                                <!--	<td>: {{item.machine_no}}</td>-->
                                <!--</tr>-->
                                <tr>
                                    <td style="text-wrap: nowrap; vertical-align: baseline;">Colly No </td>
                                    <td :style="'font-size: '+size(item.description)">: {{item.koli_no}}<br><br></td>
                                </tr>
                                <tr>
                                    <td style="text-wrap: nowrap; vertical-align: baseline;">Description </td>
                                    <td :style="'font-size: '+size(item.description)">: {{item.description}}<br><br></td>
                                    <!--</tr>-->
                                    <!--<tr>-->
                                    <!--	<td style="text-wrap: nowrap; vertical-align: baseline;">Qty </td>-->
                                    <!--	<td>: {{Number(item.qty)}}<br><br></td>-->
                                </tr>
                                <!--<tr>-->
                                <!--	<td style="text-wrap: nowrap; vertical-align: baseline;">Container </td>-->
                                <!--	<td :style="'font-size: '+size(item.description)">: </td>-->
                                <!--</tr>-->

                            </table>
                        </div>
                    </div>
                    <div style="display: flex; border: 1px solid black; width: 100mm; height: 35mm; clear:both;  page-break-after:always; align-self: center;">
                        <div style="display: inline-block; padding: 4px; display: inline-block; width: 15mm; height: 15mm; flex: 1 15mm; align-self: center;">
                            <!-- <div :id="'qrcode'+item.elid" :key="item+index"></div> -->
                            <img :id="'qrcode'+item.elid" :key="item+index" style="width: 15mm; height: 15mm;" />
                            <!-- <div style="font-weight: bold; text-align: center; padding-top: 2px;">{{item.id+'.'+item.item_id+'.'+item.bom_header_id+'.'+item.source}}</div> -->
                        </div>
                        <div style="display: flex; padding: 2px; flex: 0 290px; align-self: center;">
                            <table cellspacing="0" cellpadding="0" style="border: 0;">
                                <!-- <tr  >
										<td  style="text-wrap: nowrap; vertical-align: baseline;">Subcolly No </td>
										<td :style="'font-size: '+size(item.description)">: {{item.subkoli_no}}<br></td>
									</tr> -->
                                <tr>
                                    <td style="text-wrap: nowrap; vertical-align: baseline;">No</td>
                                    <td>: {{item.print}}<br></td>
                                </tr>
                                <!--<tr  >-->
                                <!--	<td  style="text-wrap: nowrap; vertical-align: baseline;">Colly No </td>-->
                                <!--	<td :style="'font-size: '+size(item.description)">: {{item.koli_no}}<br></td>-->
                                <!--</tr>-->
                                <!--<tr>-->
                                <!--	<td style="text-wrap: nowrap; vertical-align: baseline;">Container </td>-->
                                <!--	<td :style="'font-size: '+size(item.description)">: </td>-->
                                <!--</tr>-->
                                <!-- <tr>
										<td style="text-wrap: nowrap; vertical-align: baseline;">Description </td>
										<td :style="'font-size: '+size(item.description)">: {{item.description}}</td>
									</tr> -->
                            </table>
                        </div>
                    </div>
                    <div style="display: flex; border: 1px solid black; width: 100mm; height: 35mm; clear:both;  page-break-after:always; align-self: center;" v-if="item.print.match(/\.c$/) != null">
                        <div style="display: inline-block; padding: 4px; display: inline-block; width: 15mm; height: 15mm; flex: 1 15mm; align-self: center;">
                            <!-- <div :id="'qrcode'+item.elid" :key="item+index"></div> -->
                            <img :id="'qrcode'+item.elid" :key="item+index" style="width: 15mm; height: 15mm;" />
                            <!-- <div style="font-weight: bold; text-align: center; padding-top: 2px;">{{item.id+'.'+item.item_id+'.'+item.bom_header_id+'.'+item.source}}</div> -->
                        </div>
                        <div style="display: flex; padding: 2px; flex: 0 290px; align-self: center;">
                            <table cellspacing="0" cellpadding="0" style="border: 0;">
                                <!--<tr>-->
                                <!--	<td style="text-wrap: nowrap; vertical-align: baseline;">Machine No </td>-->
                                <!--	<td>: {{item.machine_no}}</td>-->
                                <!--</tr>-->
                                <tr>
                                    <td style="text-wrap: nowrap; vertical-align: baseline;">No Part </td>
                                    <td :style="'font-size: '+size(item.description)">: {{item.item_name}}<br><br></td>
                                </tr>
                                <tr>
                                    <td style="text-wrap: nowrap; vertical-align: baseline;">Description </td>
                                    <td :style="'font-size: '+size(item.description)">: {{item.description}}<br><br></td>
                                    <!--</tr>-->
                                    <!--<tr>-->
                                    <!--	<td style="text-wrap: nowrap; vertical-align: baseline;">Qty </td>-->
                                    <!--	<td>: {{Number(item.qty)}}<br><br></td>-->
                                </tr>
                                <tr>
                                    <td style="text-wrap: nowrap; vertical-align: baseline;">Application </td>
                                    <td :style="'font-size: '+size(item.description)">: {{item.application}}</td>
                                </tr>

                            </table>
                        </div>
                    </div>
                </td>
            </tr>
        </template>
    </table>
</template>

<style scoped>
    .barcode-container canvas {
        display: none !important;
    }

    .barcode-container img {
        display: block !important;
    }

    table td {
        font-weight: bold;
    }
</style>

<script>
    module.exports = {
        name: '',
        props: {
            value: []
        },
        data: function() {
            return {
                tmp: []
            }
        },
        methods: {
            size(desc) {
                if (desc) {
                    if (desc.length > 85)
                        return '10px !important'
                    else
                        return '12.25px !important'
                } else
                    return '12.25px !important'

            },
            chunk(arr) {
                console.log({
                    arr
                })
                const chunkSize = 1;
                var tmp = []
                for (let i = 0; i < arr.length; i += chunkSize) {
                    const chunk = arr.slice(i, i + chunkSize);
                    chunk.map((val, tdi) => {
                        val.elid = i + '-' + tdi
                        val.done = false
                    })
                    tmp.push(chunk)
                }
                return tmp
            },
            createQr(vis, e) {
                var self = this
                var isInViewport = !!isVisible
                var isInDom = !!e.target.isConnected
                console.log({
                    isInDom,
                    show: App.tmp.target.showBarcodeDialog,
                    go: App.tmp.target.go
                })
                if (isInDom && App.tmp.target.showBarcodeDialog && App.tmp.target.go) {
                    self.tmp = self.chunk(self.value)
                    self.$nextTick(() => {
                        console.log(self.value.filter(val => val.done == false || val.done == undefined))
                        self.value.filter(val => val.done == false || val.done == undefined).map((val, i) => {
                            val.done = true
                            //self.generate('qrcode'+val.elid, val.id+'.'+val.item_id+'.'+val.bom_header_id+'.'+val.source)
                            self.generate('qrcode' + val.elid, val.print)
                            if (self.value.filter(val => val.done == false || val.done == undefined).length == 0)
                                App.tmp.target.go = false
                        })
                    })
                }
            },
            generate(id, data) {
                /* new QRCode(id, {
                	text: data,
                	width: 128,
                	height: 128,
                	colorDark : "#000000",
                	colorLight : "#ffffff",
                	correctLevel : QRCode.CorrectLevel.H
                }); */
                this.matrix(data, id)
            },
            matrix(data, img) {
                try {
                    var canvas = document.createElement('canvas');
                    document.body.appendChild(canvas)
                    bwipjs.toCanvas(canvas, {
                        bcid: 'datamatrix', // Barcode type
                        text: data, // Text to encode
                        scale: 3, // 3x scaling factor
                        height: 15, // Bar height, in millimeters
                        width: 15, // Bar height, in millimeters
                        includetext: true, // Show human-readable text
                        textxalign: 'center', // Always good to set this
                    });
                    document.querySelectorAll('#' + img).forEach(val => {
                        val.src = canvas.toDataURL('image/png')
                    });
                    setTimeout(() => {
                        document.body.removeChild(canvas)
                    }, 50)
                } catch (err) {
                    console.log(err)
                    App.errorMsg()
                }

            }
        },
        mounted: function() {}
    }
</script>