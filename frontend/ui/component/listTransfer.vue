<template>
    <div style="display: flex;">
        <v-list dense style="flex: 1">
            <v-list-item-group v-model="selected" active-class="pink--text" multiple>
				<template v-for="(item, i) in value">
					<v-list-item v-if="item.excel && item.excelField!=true" :key="i">
						<v-list-item-content>
							<v-list-item-title v-text="item.text"></v-list-item-title>
						</v-list-item-content>
					</v-list-item>
				</template>
            </v-list-item-group>
        </v-list>
		<div style="flex: 1 30px; display: flex; flex-direction: column;">
			<v-spacer></v-spacer>
			<v-btn small color="primary" outlined>
				<v-icon dark>
					mdi-arrow-right-bold
			  	</v-icon>
			</v-btn>
			<v-btn small color="primary" outlined>
				<v-icon dark>
					mdi-arrow-left-bold
			  	</v-icon>
			</v-btn>
			<v-spacer></v-spacer>
		</div>
		<v-list dense style="flex: 1">
            <v-list-item-group v-model="selected" active-class="pink--text" multiple>
				<template v-for="(item, i) in value">
					<v-list-item v-if="item.excel && item.excelField==true" :key="i">
						<v-list-item-content>
							<v-list-item-title v-text="item.text"></v-list-item-title>
						</v-list-item-content>
					</v-list-item>
				</template>
            </v-list-item-group>
        </v-list>
    </div>
</template>

<style>
</style>

<script>
    module.exports = {
        name: 'list-transfer',
        props: {
            value: {
                default: []
            }
        },
        data: function() {
            return {
                selected: []
            }
        },
        methods: {

        },
        mounted: function() {

        }
    }
</script>