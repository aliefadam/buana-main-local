<template>
    <v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-autoform v-model="headers"></v-autoform>
		<div style="display: flex">
			<div style="position: relative; flex: 1">
				<img id="img" style="width: 927px; height: 448px">
				<canvas id="canvas" style="width: 927px; height: 448px; position: absolute; top: 0; left: 0;"></canvas>
				<canvas id="canvas2" style="width: 927px; height: 448px; position: absolute; top: 0; left: 0;"></canvas>
			</div>
			<textarea id="text" style="position: relative; flex: 1; height: 100%"></textarea>
		</div>
    </v-container>
</template>

<style>
</style>

<script>
    module.exports = {
        name: '',
        data: function() {
            return {
                loading: true,
                headers: [{
                    "text": "Photo",
                    "value": "attachment",
                    "align": "start",
                    "sortable": false,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "file",
                    "disabled": false,
                    "visible": false,
                    "required": false,
                    "form": true,
                    "filter": false,
                    "groupable": false,
                    "input": function(a) {
						var self = App.page()
                        if (FileReader && a.data) {
							var canvas = document.getElementById('canvas');
							var ctx = canvas.getContext("2d");
							var canvas2 = document.getElementById('canvas2');
							var ctx2 = canvas2.getContext("2d");
                            var text = document.getElementById('text');
                            var img = document.getElementById('img');
                            var fr = new FileReader();
                            fr.onload = function() {
                                img.src = fr.result;
                                //canvas.width = img.width;
                                //canvas.height = img.height;
                                img.onload = async () => {
									var div = img.naturalWidth / img.width
									//console.log(img.width, img.naturalWidth, div, img.naturalHeight / div)
									img.style.height = (img.naturalHeight / div) +'px'
									//console.log(img.style, img)
									canvas.width = img.naturalWidth
									canvas.height = img.naturalHeight
									canvas.style.height = (img.naturalHeight / div) +'px'

									canvas2.width = img.naturalWidth
									canvas2.height = img.naturalHeight
									canvas2.style.height = (img.naturalHeight / div) +'px'
									
									canvas2.width = parseFloat(canvas2.style.width)
									canvas2.height = parseFloat(canvas2.style.height)

									const worker = await Tesseract.createWorker('eng');
									await worker.setParameters({
										tessedit_pageseg_mode: Tesseract.PSM.SPARSE_TEXT_OSD,
									})
									const ret = await worker.recognize(img.src);
									var tmp2 = []
									ret.data.lines.map((val)=>{
										tmp2 = tmp2.concat(val.words)
									})
									self.words = tmp2
									self.renderBoxes()
									await worker.terminate();
								}

                            }
                            fr.readAsDataURL(a.data);
                        }
                    }
                }],
				ctx: false,
				canvas: false,
				origin: null,
				words: [],
				words2: [],
            }
        },
        methods: {
			renderBoxes: function(){
				var self = this, tmp = []
				var canvas = document.getElementById('canvas');
				var ctx = canvas.getContext("2d");
				self.words.map(w=>{
					//console.log(w)
					if(w){
						tmp.push(['text: ', w.text, JSON.stringify(w.bbox)].join(' '))
						var b = w.bbox;

						ctx.strokeWidth = 2

						ctx.strokeStyle = 'red'
						ctx.strokeRect(b.x0, b.y0, b.x1-b.x0, b.y1-b.y0)
						/*ctx.beginPath()
						ctx.moveTo(w.baseline.x0, w.baseline.y0)
						ctx.lineTo(w.baseline.x1, w.baseline.y1)*/
						//ctx.strokeStyle = 'green'
						ctx.stroke()
					}
				})
				
				var canvas2 = document.getElementById('canvas2');
				var ctx2 = canvas2.getContext("2d");
				self.words2.map(w=>{
					//console.log(w)
					if(w){
						tmp.push(['text: ', w.text, JSON.stringify(w.bbox)].join(' '))
						var b = w.bbox;

						ctx2.strokeWidth = 2

						ctx2.strokeStyle = 'red'
						//ctx2.strokeRect(b.x0, b.y0, b.x1-b.x0, b.y1-b.y0)
						ctx2.beginPath();
						ctx2.rect(b.x0, b.y0, b.x1, b.y1); 
						/*ctx.beginPath()
						ctx.moveTo(w.baseline.x0, w.baseline.y0)
						ctx.lineTo(w.baseline.x1, w.baseline.y1)*/
						//ctx.strokeStyle = 'green'
						ctx2.stroke()
					}
				})
				text.value = tmp.join('\n')
			},
			renderSelection: function(e){
				var self = this
				if(self.ctx == false){
					self.canvas = document.getElementById('canvas2');
					self.ctx = self.canvas.getContext("2d");
				}

				self.ctx.strokeStyle = 'red';
				self.ctx.beginPath();
				self.ctx.rect(self.origin.x, self.origin.y, e.offsetX - self.origin.x, e.offsetY - self.origin.y); 
				self.last = {x0: self.origin.x, y0: self.origin.y, x1: e.offsetX - self.origin.x, y1:e.offsetY - self.origin.y}
				self.ctx.stroke();
			},
			clear: function(){
				var self = this
				if(self.ctx == false){
					self.canvas = document.getElementById('canvas2');
					self.ctx = self.canvas.getContext("2d");
				}

				self.ctx.clearRect(0, 0, self.canvas.width, self.canvas.height);
			},
			render: function(e){
				var self = this
					
				if (self.origin) {
					self.clear(); 
					self.renderSelection(e);
				}
			},
			askNumber: function(){
				var self = this
				var c = prompt("Input Number")
				if(c){
					self.words2.push({text: c, bbox: self.last})
					self.renderBoxes()
				}
			}
        },
        mounted: function() {
            var self = this
            loadMultipleLibrary('https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js;https://unpkg.com/konva@9/konva.min.js').then(function(val) {
                self.loading = false
				if(self.ctx == false){
					self.canvas = document.getElementById('canvas2');
					self.ctx = self.canvas.getContext("2d");
				}
				self.canvas.onmousedown = e => { self.origin = {x: e.offsetX, y: e.offsetY}; };
				self.canvas.onmouseup = e => { self.origin = null; self.askNumber()};
				self.canvas.onmousemove = self.render
				
				self.canvas.width = parseFloat(self.canvas.style.width)
				self.canvas.height = parseFloat(self.canvas.style.height)
            }).catch(function(err) {
                self.loading = false
            })
        }
    }
</script>