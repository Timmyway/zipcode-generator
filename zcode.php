<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Zipcode utility</title>
	<link rel="icon" href="./favicon.ico" sizes="16x16" />
	<link rel="stylesheet" href="./static/css/bulma.min.css">
	<!-- development version, includes helpful console warnings -->	
	<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
	 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</head>
<body>	
	<div id="app" class="container">
		<div class="field">
			<!-- <span class="tag is-info is-light">Search: {{ regexPattern }}</span> -->
			<span class="tag is-info is-light">Total: {{ zipcodes.length }}</span>
			<span :class="['tag', filteredFormated.length > 0 ? 'is-success' : 'is-warning', shake ? 'animate__animated animate__wobble' : '']">Found: {{ filteredFormated.length }}</span>			
		</div>
		<div style="max-width: 90%">
			<div class="mt-2 is-flex">
				<div class="sidebar--left" style="position: fixed; top: 0; left: 0; width: 10%; max-height: 95vh; overflow: auto">
					<textarea name="" id="" cols="5" class="textarea" v-model="rangeNumber.generated" style="height: 100vh;" placeholder="Generated numbers"></textarea>
					<button class="button" @click.prevent="quickSearch">Filter</button>
				</div>

				<div class="mr-2">
					<input class="input" type="number" placeholder="From" v-model="rangeNumber.from" min="0" max="99">
				</div>
				<div class="mr-2">
					<input class="input" type="number" placeholder="To" v-model="rangeNumber.to"  min="0" max="99">
				</div>				
				<div class="mr-2 is-flex mb-2">
					<label class="checkbox mr-2">
				  		<input type="checkbox" v-model="rangeNumber.appendMode">
				  		Append
					</label>					
					<label class="checkbox mr-2">
				  		<input type="checkbox" v-model="rangeNumber.isStrict">
				  		Strict
					</label>
					<button class="button" @click.prevent="generateZcode">Generate</button>
				</div>
			</div>
			<div class="field">
				<input class="input" type="text" placeholder="Comma separated zipcodes" v-model="q" @keyup.enter="filterZcode">
			</div>
			<div class="field">
				<textarea class="textarea" ref="result-area" name="" id="" cols="30" rows="10" v-model="filteredFormated"></textarea>
			</div>
		</div>
		<div class="sidebar" v-if="filteredFormated" style="position: fixed; top: 0; right: 0; width: 5%; max-height: 95vh; overflow: auto">
			<ul class="">
				<li class="tag mt-1 has-text-white" v-for="cp in filteredFormated" :key="cp" :style="{background: colors[String(cp).slice(0, 1)]}">{{ cp }}</li>
			</ul>
		</div>
		<div class="buttons mt-2">
			<button class="button is-secondary" @click.prevent="filterZcode">Filter</button>
			<button class="button is-info" @click.prevent="copyToClipboard('result-area')">Copy</button>			
		</div>
	</div>

	<script src="zipcode.js"></script>
	<script>
		var vm = new Vue({
			el: '#app',
			data() {
				return {
					zipcodes: window.zipcodes,
					q: '',
					colors: {"0": '#ff5252',
						"1": '#ff4081',
						"2": '#e040fb',
						"3": '#7c4dff',
						"4": '#00acc1',
						"5": '#00897b',
						"6": '#f57f17',
						"7": '#546e7a',
						"8": '#880e4f',
						"9": '#6d4c41',
					},
					rangeNumber: {
						from: 10,
						to: 99,
						appendMode: true,
						isStrict: true,
						generated: []
					},
					shake: false,
					filtered: [],
					regexPattern: null
				}
			},
			mounted() {
				console.log('Zcodes: ', this.zipcodes);
			},
			computed: {
				filteredFormated: {
					get() {
						const f = this.filtered.map((item) => {
							return item.code_postal
						});
						return f;
					},
					set() {

					}
				}
			},
			methods: {
				generateZcode() {
					if (this.rangeNumber.generated === '') {
						this.rangeNumber.generated = [];
					}
					if (!this.rangeNumber.appendMode) {
						this.rangeNumber.generated = [];
					}
					this.range(this.rangeNumber.from, this.rangeNumber.to).map(item => {
						this.rangeNumber.generated.push(item)
					})					
				},
				quickSearch() {
					this.q = [...this.rangeNumber.generated].join(',');
					this.filterZcode();
				},
				filterZcode() {					
					if (this.q.length < 2 || !this.q) {
						return
					}
					const temp = this.q.split(',');
					this.regexPattern = '^' + temp.join('|^').replace(/\s/g, '') + '\\d+';

					this.filtered = this.zipcodes.filter((item) => {
						const regex = new RegExp(this.regexPattern, 'g');
						if (this.rangeNumber.isStrict) {
							return String(item.code_postal).match(regex) && String(item.code_postal).length === 5	
						} else {
							return String(item.code_postal).match(regex)
						}
						
					});
					this.shake = true;
					setTimeout(() => {
						this.shake = false;
					}, 1000)
				},
				range(start, end) {
				    var ans = [];
				    for (let i = start; i <= end; i++) {
				    	console.log('----------------->', String(i).length)
				    	if (String(i).length === 1) {
				    		i = '0' + String(i);
				    		console.log('==================>')
				    	}
				        ans.push(i);
				    }
				    return ans;
				},
				copyToClipboard(element) {
					/* Select the text field */
					console.log(element)
					const copyText = this.$refs[element];					
					copyText.select();
					copyText.setSelectionRange(0, 99999); /*For mobile devices*/

					/* Copy the text inside the text field */
					document.execCommand("copy");
				},
				fetchEncrypt() {
					if (!this.form.input) {
						console.log('Nothing to encrypt...save api call');
						return
					}
					axios.post('http://localhost:5000/pwd/encrypt', {
						toEncrypt: this.form.input					
					})
					.then((response) => {						
						this.form.output = response.data.encrypted;
						this.form.key = response.data.key;
					})
					.catch((error) => {
						console.log(error);
					});
				}
			}
		})
	</script>
</body>
</html>