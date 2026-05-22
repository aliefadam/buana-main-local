<template>
	<div v-if="value" style="position: fixed; top: 0; left: 0; height: 100%; width: 100%; display: flex; flex-direction: column; z-index: 1000">
		<template v-for="index in 10">
			<div :style="'width: 100%; display: flex; height: '+(100/10)+'%;'">
				<template v-for="index2 in 25">
					<div :key="index2" :ref="index+'-'+index2" :style="'opacity: 0; width: '+(100/25)+'%; height: 100%; background-color: '+getColor2()">                        
                    </div>
				</template>
			</div>
		</template>
	</div>
</template>

<style>
</style>

<script>
	module.exports = {
		name: '',
		props:{
			value: false,
		},
		data: function () {
			return {
				lastColor: ''
			}
		},
		watch: {
			value(val){
			    console.log(val)
				if(val) this.start()
			}
		},
		methods: {
			getColor2(){
				var self = this
				var c = ['1b296b', '162d92', '7888bf', '3a50a4', '0c2490', 'c4a755', '987c43', 'cead58', 'f6bd3a', 'f0bc42'].filter(val=>val!=self.lastColor)
				return '#'+c[Math.floor(Math.random()*c.length)]
			},
			getColor(){ 
				return `hsla(${~~(360 * Math.random())}, 70%,  72%, 1)`
			},
			start(){
			    console.log('screen start')
				var self = this;
				//var mini = ['sm', 'md', 'xs'].includes(App.breakpoint)
				setTimeout(()=>{
					var el = Object.values(self.$refs).map(val=>{return val[0]})
					el.map((val, i)=>{
						setTimeout(()=>{
							val.style.opacity = 1
						}, 10*(i+1))
						setTimeout(()=>{
							val.style.opacity = 0
							if(i==el.length-1){
								self.value = false
								self.$emit('input', false)
							}
						}, 10*(i+1)+(10*el.length)+200)
					})
					setTimeout(()=>{
					    var elementToRotate = document.getElementById("dashboard-procurement"); 
						elementToRotate.classList.toggle("rotated90");
					}, 10*(el.length+1)+(10*el.length)+200)
				}, 500)
			}
		},
		mounted: function () {
			var self = this
		}
	}
</script>