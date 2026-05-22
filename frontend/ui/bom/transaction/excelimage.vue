<template>
	<div>
		<v-excel-reader v-model="file" ref="file"></v-excel-reader>
		<!-- <div style="display: flex; max-width: 100%; flex-wrap: wrap; gap: 10px">
			<img v-for="(item, index) in images" :key="index" :src="item[2]" style="max-width: 100px; max-height: 100px; object-fit: contain;" />
		</div> -->
		<table class="default-table simple-table table">
			<tr v-for="(item, index) in images" :key="index">
				<td v-for="(cell, cellindex) in item" :key="cellindex">
					<img v-if="cell.toString().match('data:image')!=null" :src="cell" style="max-width: 100px; max-height: 100px; object-fit: contain;" />
					<template v-else>{{cell}}</template>
				</td>
			</tr>
		</table>
	</div>
</template>

<style>
</style>

<script>
	module.exports = {
		name: '',
		data: function () {
			return {
				file: false,
				images: []
			}
		},
		watch: {
			async file(val){
				var self = this
				var wb = new ExcelJS.Workbook();
                var reader = new FileReader()
				reader.readAsArrayBuffer(val[0].info)
				var tmp = []
				reader.onload = () => {

					var buffer = reader.result;
					wb.xlsx.load(buffer).then(w => {
						var sheet = w.worksheets[0]
						for (const image of w.worksheets[0].getImages()) {
							// fetch the media item with the data (it seems the imageId matches up with m.index?)
							const img = workbook.model.media.find(m => m.index === image.imageId);
							var data = sheet.getRow(image.range.tl.nativeRow+1)._cells.map(val=>val.value ? (val.value.result ? val.value.result : val.value) : val.value)
							data[image.range.tl.nativeCol] = 'data:image/'+img.extension+';base64,'+img.buffer.toString('base64')
							tmp.push(data)
						}
						self.images = tmp
					})
				}
			}
		},
		methods: {
			
		},
		mounted: function () {
			
		}
	}
</script>