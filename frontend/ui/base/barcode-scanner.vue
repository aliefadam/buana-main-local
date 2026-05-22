<template>
    <div v-observe-visibility="visibilityChanged">
        <div id="loadingMessage">{{loadingMessage}}</div>
        <div style="width: 100%; height: 100%; display: flex; justify-content: center;">
            <canvas :id="'canvas-scanner'+_.uid" hidden></canvas>
        </div>
    </div>
</template>

<style>
</style>

<script>
    module.exports = {
        name: '',
        data: function() {
            return {
                loadingMessage: '🎥 Unable to access video stream (please make sure you have a camera enabled)',
                canvasId: 'canvas',
                ready: false,
                loadOnReady: false,
                outputData: '',
                video: false,
                canvasElement: false,
                canvas: false,
                codeReader: false,
				last: ''
            }
        },
        watch: {
            ready: function(val) {
                if (val && this.loadOnReady) {
                    this.loadOnReady = false
                    this.init()
                }
            }
        },
        methods: {
            visibilityChanged: function(isVisible, entry) {
                if (isVisible) {
                    if (!this.ready)
                        this.loadOnReady = true
                    else
                        this.init()
                }
            },
            init: function() {
                var self = this
                if (document.getElementById(self.canvasId)) {
                    var el = document.getElementById(self.canvasId)
                    self.$nextTick(function(val) {
                        if (self.canvasElement === false)
                            self.canvasElement = el;
                        if (self.canvas === false)
                            self.canvas = self.canvasElement.getContext("2d");

                        if (self.video === false) {
                            self.video = document.createElement("video");
                            navigator.mediaDevices.getUserMedia({
                                video: {
                                    facingMode: "environment"
                                }
                            }).then(function(stream) {
                                self.video.srcObject = stream;
                                self.video.setAttribute("playsinline",
                                    true); // required to tell iOS safari we don't want fullscreen
                                self.video.play();
                                requestAnimationFrame(self.tick);
                            });
                        } else {
                            //self.video.play();
                            requestAnimationFrame(self.tick);
                        }
                    })
                }
            },
            tick: function() {
                var self = this
                self.loadingMessage = "⌛ Loading video..."
                if (self.outputData == '') {
                    if (self.video.readyState === self.video.HAVE_ENOUGH_DATA) {
                        self.loadingMessage = ''
                        self.canvasElement.hidden = false;
                        //outputContainer.hidden = false;

                        self.canvasElement.height = self.video.videoHeight;
                        self.canvasElement.width = self.video.videoWidth;
                        self.canvas.drawImage(self.video, 0, 0, self.canvasElement.width, self.canvasElement.height);
                        //var imageData = self.canvas.getImageData(0, 0, self.canvasElement.width, self.canvasElement.height);
                        codeReader.decodeFromCanvas(self.canvas).then((result) => {
                            console.log(result)
                            self.drawLine(code.location.topLeftCorner, code.location.topRightCorner, "#FF3B58");
							self.drawLine(code.location.topRightCorner, code.location.bottomRightCorner, "#FF3B58");
							self.drawLine(code.location.bottomRightCorner, code.location.bottomLeftCorner, "#FF3B58");
							self.drawLine(code.location.bottomLeftCorner, code.location.topLeftCorner, "#FF3B58");
							if(self.last!=result.text){
								self.$emit('success', result.text)
								self.last = result.text
							}
							//self.outputData = code.data;
                        }).catch((err) => {
                            console.error(err)
                        })
                        /* var code = jsQR(imageData.data, imageData.width, imageData.height, {
                        	inversionAttempts: "dontInvert",
                        });
                        if (code) {
                        	if(code.data.trim().length > 4){
                        		self.drawLine(code.location.topLeftCorner, code.location.topRightCorner, "#FF3B58");
                        		self.drawLine(code.location.topRightCorner, code.location.bottomRightCorner, "#FF3B58");
                        		self.drawLine(code.location.bottomRightCorner, code.location.bottomLeftCorner, "#FF3B58");
                        		self.drawLine(code.location.bottomLeftCorner, code.location.topLeftCorner, "#FF3B58");
                        		//self.outputMessage.hidden = true;
                        		self.outputData = code.data;
                        		//self.onClose()
                        		self.$emit('success', self.outputData)
                        	}
                        } else {
                        	//outputMessage.hidden = false;
                        	//self.outputData = ''
                        } */
                    }
                    requestAnimationFrame(self.tick);
                }
            },
            drawLine: function(begin, end, color) {
                var self = this
                self.canvas.beginPath();
                self.canvas.moveTo(begin.x, begin.y);
                self.canvas.lineTo(end.x, end.y);
                self.canvas.lineWidth = 4;
                self.canvas.strokeStyle = color;
                self.canvas.stroke();
            },
        },
        mounted: function() {
            var self = this
            console.log(self)
            loadMultipleLibrary('./library/zxing-browser.min.js').then(function(val) {
                self.codeReader = new ZXing.BrowserQRCodeReader()
                self.ready = true
            }).catch(function(err) {
                console.log(err)
                self.ready = true
            })
            this.canvasId = "canvas-scanner" + this._.uid
        }
    }
</script>