<template>
	<div style="padding: 1em">
		<v-card style="margin: 1em">
			<v-card-title primary-title v-if="title">
				{{title}}
			</v-card-title>
			<v-card-text v-html="info">
				
			</v-card-text>
		</v-card>
	</div>
</template>

<style scoped>
	table.tpl {
        table-layout: auto;
        margin-top: 1em;
        min-width: 100%;
    }

    table.tpl th,
    table.tpl td {
        border: 1px solid #333;
        padding: 4px;
    }

    table.tpl th{
        background: #FFFF00;
    }

    table.tpl tr.first td {
        font-weight: bold;
    }

	table.tpl .top-header{
		border-top: 2px solid #333;
	}

	table.tpl .no-border{
		border-top: 0;
	}
</style>

<script>
	module.exports = {
		name: '',
		data: function () {
			return {
				title: false,
				info: '',
				hash: location.hash.split('/').slice(1),
			}
		},
		methods: {
            getdata: async function() {
                var self = this
                var res = await axios.get(App.url + 'info', {
                    params: {
                        filter: {
                            uid: self.hash[2]
                        },
                        limit: -1
                    }
                })
                if (!res.data.status)
                    App.errorMsg()
                else {
					if(res.data.data[0].url != null){
						if(res.data.data[0].url.trim()!=''){
							var res = await axios.get(res.data.data[0].url, {
								params: {uid: self.hash[2]}
							})
							if (!res.data.status)
								App.errorMsg()
							else {
								self.info = res.data.data
								if(res.data.title)
									self.title = res.data.title
							}
						}
					}
					else
                    	self.info = res.data.data[0].info
                }
            },
		},
		mounted: function () {
			this.getdata()
		}
	}
</script>