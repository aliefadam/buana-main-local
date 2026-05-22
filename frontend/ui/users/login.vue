<template>
	<v-dialog v-model="value" scrollable persistent overlay max-width="500px" transition="dialog-transition">
		<v-card>
			<v-card-title primary-title>
				Login
			</v-card-title>
			<v-divider></v-divider>
			<v-card-text>
				<v-autoform cols="12" sm="12" v-model="tableHeaders" :hide-details="true" :filled="true"
						:valid.sync="valid"></v-autoform>
			</v-card-text>
			<v-divider></v-divider>
			<v-card-actions>
				<v-spacer></v-spacer>
				<v-btn color="primary" outlined small @click="save" :loading="saveLoading" :disabled="!valid">
					<v-icon small left>mdi-login</v-icon>Login
				</v-btn>
				<v-btn color="error" outlined small @click="value = false">
					<v-icon small left>mdi-close</v-icon>Cancel
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<style>
</style>

<script>
	module.exports = {
		props: {
			value: {
				default: true
			},
			login: {
				default: false
			}
		},
		name: '',
		watch: {
			login: function () {
				this.$emit('update:login', this.login)
			}
		},
		data: function () {
			return {
				tableHeaders: [{
					"text": "Username",
					"value": "username",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": true,
					"filter": true
				}, {
					"text": "Password",
					"value": "password",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "password",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": true,
					"filter": true
				}],
				valid: false,
				saveLoading: false
			}
		},
		methods: {
			save: function(){
				var self = this
				var params = {};
				self.saveLoading = true
				self.tableHeaders.map(function (data) {
					if (data.data != undefined) {
						params[data.value] = data.data || ''
					}
				})
				
				axios
					.post(App.url + "auth/login", params)
					.then(function (res) {
						self.saveLoading = false
						if (res.data.status) {
							if (res.data.data.length < 1) {
								self.saveLoading = false
								self.login = false
								App.snack({
									color: "red",
									icon: "mdi-alert-circle-outline",
									message: "You are not allowed to view this page!"
								});
							} else {
								self.saveLoading = false
								App.username = params.username;
								App.userData = res.data;
								
								setCookie('uuid', res.data.uuid, false)
								
								self.value = false
								self.login = true
							}
						} else {
							self.saveLoading = false
							self.login = false
							App.snack({
								color: "red",
								icon: "mdi-alert-circle-outline",
								message: "Login failed!"
							});
						}
					})
					.catch(function (err) {
						self.login = false
						self.saveLoading = false
						App.snack({
							color: "red",
							icon: "mdi-alert-circle-outline",
							message: err.toString()
						});
					});
			}
		},
		mounted: function () {
			
		}
	}
</script>