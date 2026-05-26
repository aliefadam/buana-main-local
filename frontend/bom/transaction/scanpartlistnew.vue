<template>
    <div v-observe-visibility="onVisible" :id="randomId()">
        <table class="">
                    <thead>
                        <tr>
                            <th>Machine No</th>
                            <th>Part Number</th>
                            <th>Qty</th>
                            <th>Description</th>
                            <th colspan=3 style="text-align:center;">Action Receive</th>
                        </tr>
                    </thead>
                    <tbody v-for="(val, i) in data" :key="i" >
                        <tr>
                             <td>{{ val.machine_no }}</td>
                             <td>{{ val.part_number }}</td>
                             <td>{{ Number(val.qty) }}</td>
                              <td>{{val.description}}</td>
                            <td> <v-btn color="primary" @click="complete(val)">All</v-btn> </td>
                           <td><v-btn color="primary" @click="partial(val)">Partial</v-btn></td>
                           <td><v-btn color="primary" @click="out(val)">Out</v-btn></td>
                        </tr>
                        
                    </tbody>
                </table>
                </div>
</template>

<style>
</style>

<script>

    module.exports = {
        name: '',
        data: function() {
            return {
                data: [],
				location: '0'
            }
        },
        computed: {

        },
        methods: {
            async complete(item) {
                try {
					var code = `${item.id}.${item.item_id}.${item.bom_header_id}.1`
                    var self = this
                    var l = prompt("Input Location", '')
					var c = confirm("Are you sure to complete "+item.part_number+"?")
                    if (l === null || !c) {} else {
                        var n = Number(item.qty-item.received_qty)
                        if (isNaN(n)) {
                            App.errorMessage('Please input valid number!')
                        } else {
                            var res = await axios.post(App.url + 'transaction/receivescan', {
                                latlong: self.location,
                                code: code,
                                qty: Math.abs(n),
                                location: l
                            })
                            if (!res.data.status) throw res.data
                            App.successMsg()
                        }
                    }
                } catch (err) {
                    App.errorMsg()
                }
            },
            async receive(item) {
                try {
					var code = `${item.id}.${item.item_id}.${item.bom_header_id}.1`
                    var self = this
                    var c = prompt("Input QTY", 0)
                    var l = prompt("Input Location", '')
                    if (c === null || l === null) {} else {
                        var n = Number(c)
                        if (isNaN(n)) {
                            App.errorMessage('Please input valid number!')
                        } else {
                            var res = await axios.post(App.url + 'transaction/receivescan', {
                                latlong: self.location,
                                code: code,
                                qty: Math.abs(n),
                                location: l

                            })
                            if (!res.data.status) throw res.data
                            App.successMsg()
                        }
                    }
                } catch (err) {
                    App.errorMsg()
                }
            },
            async outgoing(item) {
                try {
					var code = `${item.id}.${item.item_id}.${item.bom_header_id}.1`
                    var self = this
                    var c = prompt("Input QTY", 0)
                    var l = prompt("Input Location", '')
                    if (c === null || l === null) {} else {
                        var n = Number(c)
                        if (isNaN(n)) {
                            App.errorMessage('Please input valid number!')
                        } else {
                            var res = await axios.post(App.url + 'transaction/receivescan', {
                                latlong: self.location,
                                code: code,
                                qty: -Math.abs(n),
                                location: l

                            })
                            if (!res.data.status) throw res.data
                            self.headers[0].data = ''
                            App.successMsg()
                        }
                    }
                } catch (err) {
                    App.errorMsg()
                }
            },
			async getData(barcode){
				try {
						var self = this
					var res = await axios.get(App.url+'transaction/bomheader/getItemList', {
						params: {
								bom_receive_id: barcode.split('.')[0]

						}
					})
					self.data = res.data.data
				} catch (err) {
					console.log(err)
					App.errorMsg()
				}
			},
			onVisible: function(isVisible, e) {
				var self = this
				isInViewport = !!isVisible
				isInDom = !!e.target.isConnected
                if (isInViewport) {
					self.data = []
                    self.getData(App.page().barcode)
                }
			},
        },
		async mounted() {
			var self = this
			self.location = await self.getLocation()
			setNonPermanentInterval(async ()=>{
				self.location = await self.getLocation()
			}, 60000, self.$el.id, self.$el)
		},
    }
</script>