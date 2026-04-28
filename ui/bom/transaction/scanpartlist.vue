<template>
    <div v-observe-visibility="onVisible" :id="randomId()">
        <v-row>
            <v-col v-for="(val, i) in data" style="height: 250px; display: flex; flex-direction: column;" cols="12" md="4" lg="3" xl="2">
				<div style="flex: 1; overflow: hidden;">
					<span style="font-weight: bolder;">Item</span> : 
					{{ val.part_number }}<br/>
					<span style="font-weight: bolder;">BOM qty</span> : 
					{{ Number(val.qty) }}<br/>
					<span style="font-weight: bolder;">Description</span> : 
					{{ val.description }}
				</div>
                <div>
                    <v-btn color="primary" @click="complete(val)">All</v-btn>
                    <v-btn color="primary" @click="partial(val)">Partial</v-btn>
                    <v-btn color="primary" @click="out(val)">Out</v-btn>
                </div>
            </v-col>
        </v-row>
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
					var res = await axios.get(App.url+'transaction/bomitem', {
						params: {
							filter: {
								bom_receive_id: barcode.split('.')[0]
							},
							limit: -1
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