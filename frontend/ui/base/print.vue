<template>
    <div :class="size+' section-to-print'" id="printdiv">
		<slot></slot>
	</div>
</template>

<style>
    @media print {
         #printdiv{
margin: 0 !important;
padding: 0 !important;
}
		canvas {
			display: block !important; 
		}
		@page {margin:0}
		html {margin:0}
        body > *{
            visibility: hidden;
        }

        .section-to-print {
            visibility: visible;
            position: absolute;
            left: 0;
            top: 0;
			background: white;
        }

		.v-card .section-to-print{
			visibility: hidden;
		}
    }

    .a0 {
        width: 2383pt;
        height: 3370pt;
    }

    .a0.landscape {
        height: 2383pt;
        width: 3370pt;
    }

    .a1 {
        width: 1683pt;
        height: 2383pt;
    }

    .a1.landscape {
        height: 1683pt;
        width: 2383pt;
    }

    .a2 {
        width: 1190pt;
        height: 1683pt;
    }

    .a2.landscape {
        height: 1190pt;
        width: 1683pt;
    }

    .a3 {
        width: 841pt;
        height: 1190pt;
    }

    .a3.landscape {
        height: 841pt;
        width: 1190pt;
    }

    .a4 {
        width: 595pt;
        /* height: 841pt; */
    }

    .a4.landscape {
        height: 595pt;
        width: 841pt;
    }

    .a5 {
        width: 419pt;
        height: 595pt;
    }

    .a5.landscape {
        height: 419pt;
        width: 595pt;
    }

    .a6 {
        width: 297pt;
        height: 419pt;
    }

    .a6.landscape {
        height: 297pt;
        width: 419pt;
    }

    .a7 {
        width: 209pt;
        height: 297pt;
    }

    .a7.landscape {
        height: 209pt;
        width: 297pt;
    }

    .a8 {
        width: 147pt;
        height: 209pt;
    }

    .a8.landscape {
        height: 147pt;
        width: 209pt;
    }

    .a9 {
        width: 104pt;
        height: 147pt;
    }

    .a9.landscape {
        height: 104pt;
        width: 147pt;
    }

    .a10 {
        width: 73pt;
        height: 104pt;
    }

    .a10.landscape {
        height: 73pt;
        width: 104pt;
    }

    .b0 {
        width: 2834pt;
        height: 4008pt;
    }

    .b0.landscape {
        height: 2834pt;
        width: 4008pt;
    }

    .b1 {
        width: 2004pt;
        height: 2834pt;
    }

    .b1.landscape {
        height: 2004pt;
        width: 2834pt;
    }

    .b2 {
        width: 1417pt;
        height: 2004pt;
    }

    .b2.landscape {
        height: 1417pt;
        width: 2004pt;
    }

    .b3 {
        width: 1000pt;
        height: 1417pt;
    }

    .b3.landscape {
        height: 1000pt;
        width: 1417pt;
    }

    .b4 {
        width: 708pt;
        height: 1000pt;
    }

    .b4.landscape {
        height: 708pt;
        width: 1000pt;
    }

    .b5 {
        width: 498pt;
        height: 708pt;
    }

    .b5.landscape {
        height: 498pt;
        width: 708pt;
    }

    .b6 {
        width: 354pt;
        height: 498pt;
    }

    .b6.landscape {
        height: 354pt;
        width: 498pt;
    }

    .b7 {
        width: 249pt;
        height: 354pt;
    }

    .b7.landscape {
        height: 249pt;
        width: 354pt;
    }

    .b8 {
        width: 175pt;
        height: 249pt;
    }

    .b8.landscape {
        height: 175pt;
        width: 249pt;
    }

    .b9 {
        width: 124pt;
        height: 175pt;
    }

    .b9.landscape {
        height: 124pt;
        width: 175pt;
    }

    .b10 {
        width: 87pt;
        height: 124pt;
    }

    .b10.landscape {
        height: 87pt;
        width: 124pt;
    }

    .c0 {
        width: 2599pt;
        height: 3676pt;
    }

    .c0.landscape {
        height: 2599pt;
        width: 3676pt;
    }

    .c1 {
        width: 1836pt;
        height: 2599pt;
    }

    .c1.landscape {
        height: 1836pt;
        width: 2599pt;
    }

    .c2 {
        width: 1298pt;
        height: 1836pt;
    }

    .c2.landscape {
        height: 1298pt;
        width: 1836pt;
    }

    .c3 {
        width: 918pt;
        height: 1298pt;
    }

    .c3.landscape {
        height: 918pt;
        width: 1298pt;
    }

    .c4 {
        width: 649pt;
        height: 918pt;
    }

    .c4.landscape {
        height: 649pt;
        width: 918pt;
    }

    .c5 {
        width: 459pt;
        height: 649pt;
    }

    .c5.landscape {
        height: 459pt;
        width: 649pt;
    }

    .c6 {
        width: 323pt;
        height: 459pt;
    }

    .c6.landscape {
        height: 323pt;
        width: 459pt;
    }

    .c7 {
        width: 229pt;
        height: 323pt;
    }

    .c7.landscape {
        height: 229pt;
        width: 323pt;
    }

    .c8 {
        width: 161pt;
        height: 229pt;
    }

    .c8.landscape {
        height: 161pt;
        width: 229pt;
    }

    .c9 {
        width: 113pt;
        height: 161pt;
    }

    .c9.landscape {
        height: 113pt;
        width: 161pt;
    }

    .c10 {
        width: 79pt;
        height: 113pt;
    }

    .c10.landscape {
        height: 79pt;
        width: 113pt;
    }

    .dl {
        width: 311pt;
        height: 623pt;
    }

    .dl.landscape {
        height: 311pt;
        width: 623pt;
    }

    .letter {
        width: 612pt;
        height: 792pt;
    }

    .letter.landscape {
        height: 612pt;
        width: 792pt;
    }

    .government-letter {
        width: 576pt;
        height: 756pt;
    }

    .government-letter.landscape {
        height: 576pt;
        width: 756pt;
    }

    .legal {
        width: 612pt;
        height: 1008pt;
    }

    .legal.landscape {
        height: 612pt;
        width: 1008pt;
    }

    .junior-legal {
        width: 576pt;
        height: 360pt;
    }

    .junior-legal.landscape {
        height: 576pt;
        width: 360pt;
    }

    .ledger {
        width: 1224pt;
        height: 792pt;
    }

    .ledger.landscape {
        height: 1224pt;
        width: 792pt;
    }

    .tabloid {
        width: 792pt;
        height: 1224pt;
    }

    .tabloid.landscape {
        height: 792pt;
        width: 1224pt;
    }

    .credit-card {
        width: 153pt;
        height: 243pt;
    }

    .credit-card.landscape {
        height: 153pt;
        width: 243pt;
    }
</style>

<script>
    module.exports = {
        name: '',
        props: {
            size: {
                default: 'a4'
            }
        },
        data: function() {
            return {

            }
        },
        methods: {
			print: function(){
				//printJS({ printable: 'printdiv', type: 'html', targetStyles: ['*']})
				//document.querySelector('body').style.height = document.getElementById('printdiv').clientHeight+'px'
				document.querySelectorAll('body > #printdiv').forEach(val=>{
					val.remove()
				})
				let new_element = document.getElementById('printdiv').cloneNode(true);
				document.body.appendChild(new_element)
				setTimeout(function(){
					print()
				}, 250)
			},
			printDebug: function(){
				//printJS({ printable: 'printdiv', type: 'html', targetStyles: ['*']})
				//document.querySelector('body').style.height = document.getElementById('printdiv').clientHeight+'px'
				document.querySelectorAll('body > #printdiv').forEach(val=>{
					val.remove()
				})
				let new_element = document.getElementById('printdiv').cloneNode(true);
				document.body.appendChild(new_element)
				/* setTimeout(function(){
					print()
				}, 250) */
			},
            printDiv(divName) {

                var printContents = document.getElementById(divName).innerHTML;
                w = window.open();

                w.document.write(printContents);
                w.document.write('<scr' + 'ipt type="text/javascript">' + 'window.onload = function() { window.print(); window.close(); };' + '</sc' + 'ript>');

                w.document.close(); // necessary for IE >= 10
                w.focus(); // necessary for IE >= 10

                return true;
            }
        },
        mounted: function() {
			
			loadMultipleLibrary('https://printjs-4de6.kxcdn.com/print.min.js;https://printjs-4de6.kxcdn.com/print.min.css').then(function(val) {
            }).catch(function(err) {

            })
        }
    }
</script>