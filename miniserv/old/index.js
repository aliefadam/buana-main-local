/**
 * @typedef {Object} index
 */
/**
 * index class
 *
 * @param { import("db").App } App
 * @returns {Object} index class
 */
//import axios from 'axios';
module.exports = function (App) {
	
	var axios = require('axios');
	Number.prototype.format = function(n, x, dec, delim) {
		var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\.' : '$') + ')';
		var dec = dec || ',', delim = delim || '.'
		var ret = this.toFixed(Math.max(0, ~~n)).replace(new RegExp(re, 'g'), '$&{,}');
		var num = ret.split('.')
		num[0] = num[0].replace(/\{,\}/g, delim)
		ret = num.join(dec)
		return ret
	}

	var NUMBER_MAP = {
		'.': 'point',
		'-': 'negative',
		0: 'Zero',
		1: 'One',
		2: 'Two',
		3: 'Three',
		4: 'Four',
		5: 'Five',
		6: 'Six',
		7: 'Seven',
		8: 'Eight',
		9: 'Nine',
		10: 'Ten',
		11: 'Eleven',
		12: 'Twelve',
		13: 'Thirteen',
		14: 'Fourteen',
		15: 'Fifteen',
		16: 'Sixteen',
		17: 'Seventeen',
		18: 'Eighteen',
		19: 'Nineteen',
		20: 'Twenty',
		30: 'Thirty',
		40: 'Forty',
		50: 'Fifty',
		60: 'Sixty',
		70: 'Seventy',
		80: 'Eighty',
		90: 'Ninety'
	  };
	
	  // http://en.wikipedia.org/wiki/English_numerals#Cardinal_numbers
	  var CARDINAL_MAP = {
		2: 'Hundred',
		3: 'Thousand',
		6: 'Million',
		9: 'Billion',
		12: 'Trillion',
		15: 'Quadrillion',
		18: 'Quintillion',
		21: 'Sextillion',
		24: 'Septillion',
		27: 'Octillion',
		30: 'Nonillion',
		33: 'Decillion',
		36: 'Undecillion',
		39: 'Duodecillion',
		42: 'Tredecillion',
		45: 'Quattuordecillion',
		48: 'Quindecillion',
		51: 'Sexdecillion',
		54: 'Septendecillion',
		57: 'Octodecillion',
		60: 'Novemdecillion',
		63: 'Vigintillion',
		100: 'Googol',
		303: 'Centillion'
	  };
	
	  // Make a hash of words back to their numeric value.
	  var WORD_MAP = {
		nil: 0,
		naught: 0,
		period: '.',
		decimal: '.'
	  };
	
	  Object.keys(NUMBER_MAP).forEach(function (num) {
		WORD_MAP[NUMBER_MAP[num]] = isNaN(+num) ? num : +num;
	  });
	
	  Object.keys(CARDINAL_MAP).forEach(function (num) {
		WORD_MAP[CARDINAL_MAP[num]] = isNaN(+num) ? num : Math.pow(10, +num);
	  });
	
	  /**
	   * Returns the number of significant figures for the number.
	   *
	   * @param  {number} num
	   * @return {number}
	   */
	  function intervals (num) {
		var match = String(num).match(/e\+(\d+)/);
	
		if (match) return match[1];
	
		return String(num).length - 1;
	  }
	
	  /**
	   * Calculate the value of the current stack.
	   *
	   * @param {Array}  stack
	   * @param {number} largest
	   */
	  function totalStack (stack, largest) {
		var total = stack.reduceRight(function (prev, num, index) {
		  if (num > stack[index + 1]) {
			return prev * num;
		  }
	
		  return prev + num;
		}, 0);
	
		return total * largest;
	  }
	
	  /**
	   * Accepts both a string and number type, and return the opposite.
	   *
	   * @param  {string|number} num
	   * @return {string|number}
	   */
	  function numbered (num) {
		if (typeof num === 'string') return numbered.parse(num);
		if (typeof num === 'number') return numbered.stringify(num);
	
		throw new Error('Numbered can only parse strings or stringify numbers');
	  }
	
	  /**
	   * Turn a number into a string representation.
	   *
	   * @param  {number} num
	   * @return {string}
	   */
	  numbered.stringify = function (value) {
		var num = Number(value);
		var floor = Math.floor(num);
	
		// If the number is in the numbers object, we quickly return.
		if (NUMBER_MAP[num]) return NUMBER_MAP[num];
	
		// If the number is a negative value.
		if (num < 0) return NUMBER_MAP['-'] + ' ' + numbered.stringify(-num);
	
		// Check if we have decimals.
		if (floor !== num) {
		  var words = [numbered.stringify(floor), NUMBER_MAP['.']];
		  var chars = String(num).split('.').pop();
	
		  for (var i = 0; i < chars.length; i++) {
			words.push(numbered.stringify(+chars[i]));
		  }
	
		  return words.join(' ');
		}
	
		var interval = intervals(num);
	
		// It's below one hundred, but greater than nine.
		if (interval === 1) {
		  return NUMBER_MAP[Math.floor(num / 10) * 10] + '-' + numbered.stringify(Math.floor(num % 10));
		}
	
		var sentence = [];
	
		// Simple check to find the closest full number helper.
		while (!CARDINAL_MAP[interval]) interval -= 1;
	
		if (CARDINAL_MAP[interval]) {
		  var remaining = Math.floor(num % Math.pow(10, interval));
	
		  sentence.push(numbered.stringify(Math.floor(num / Math.pow(10, interval))));
		  sentence.push(CARDINAL_MAP[interval] + (remaining > 99 ? ',' : ''));
	
		  if (remaining) {
			if (remaining < 100) sentence.push('and');
	
			sentence.push(numbered.stringify(remaining));
		  }
		}
	
		return sentence.join(' ');
	  };
	
	  /**
	   * Turns a string representation of a number into a number type
	   * @param  {string} num
	   * @return {number}
	   */
	  numbered.parse = function (num) {
		var modifier = 1;
		var largest = 0;
		var largestInterval = 0;
		var zeros = 0; // Track leading zeros in a decimal.
		var stack = [];
	
		var total = num.split(/\W+/g)
		  .map(function (word) {
			var num = word.toLowerCase();
	
			return WORD_MAP[num] !== undefined ? WORD_MAP[num] : num;
		  })
		  .filter(function (num) {
			if (num === '-') modifier = -1;
			if (num === '.') return true; // Decimal points are a special case.
	
			return typeof num === 'number';
		  })
		  .reduceRight(function (memo, num) {
			var interval = intervals(num);
	
			// Check the interval is smaller than the largest one, then create a stack.
			if (typeof num === 'number' && interval < largestInterval) {
			  stack.push(num);
			  if (stack.length === 1) return memo - largest;
			  return memo;
			}
	
			memo += totalStack(stack, largest);
			stack = []; // Reset the stack for more computations.
	
			// If the number is a decimal, transform everything we have worked with.
			if (num === '.') {
			  var decimals = zeros + String(memo).length;
	
			  zeros = 0;
			  largest = 0;
			  largestInterval = 0;
	
			  return memo * Math.pow(10, -decimals);
			}
	
			// Buffer encountered zeros.
			if (num === 0) {
			  zeros += 1;
			  return memo;
			}
	
			// Shove the number on the front if the intervals match and the number whole.
			if (memo >= 1 && interval === largestInterval) {
			  var output = '';
	
			  while (zeros > 0) {
				zeros -= 1;
				output += '0';
			  }
	
			  return Number(String(num) + output + String(memo));
			}
	
			largest = num;
			largestInterval = intervals(largest);
	
			return (memo + num) * Math.pow(10, zeros);
		  }, 0);
	
		return modifier * (total + totalStack(stack, largest));
	  };

	return class index extends App.module {
		constructor() {
			super()
			this.barcode = new App.model['./models/barcode']()
		}

		async init(opt) {
			var self = this
			var urlDomain = self.$get.dev != undefined ? 'internaldev' : 'main'
			var data = await axios.post('https://'+urlDomain+'.buanamultiteknik.com/api/data/get', {
						q: `SELECT approved from purchase_order p 
						where p.id = ${self.$get.po_id}`
					})
			//opt.data = data.data
			if(data.data.data[0].approved!=3)
				opt.templateName = 'template_preview.docx'
			if(self.$get.noprice!=undefined)
				opt.templateName = 'templatenoprice.docx'
			return opt
		}


		terbilang(a){
			var self = this
			var c=" Satu Dua Tiga Empat Lima Enam Tujuh Delapan Sembilan Sepuluh Sebelas".split(" ");if(12>a)var b=c[a];else 20>a?b=c[a-10]+" Belas":100>a?(b=parseInt(String(a/10).substr(0,1)),b=c[b]+" Puluh "+c[a%10]):200>a?b="Seratus "+self.terbilang(a-100):1E3>a?(b=parseInt(String(a/100).substr(0,1)),b=c[b]+" Ratus "+self.terbilang(a%100)):2E3>a?b="Seribu "+self.terbilang(a-1E3):1E4>a?(b=parseInt(String(a/1E3).substr(0,1)),b=c[b]+" Ribu "+self.terbilang(a%1E3)):1E5>a?(b=parseInt(String(a/100).substr(0,2)),
			a%=1E3,b=self.terbilang(b)+" Ribu "+self.terbilang(a)):1E6>a?(b=parseInt(String(a/1E3).substr(0,3)),a%=1E3,b=self.terbilang(b)+" Ribu "+self.terbilang(a)):1E8>a?(b=parseInt(String(a/1E6).substr(0,4)),a%=1E6,b=self.terbilang(b)+" Juta "+self.terbilang(a)):1E9>a?(b=parseInt(String(a/1E6).substr(0,4)),a%=1E6,b=self.terbilang(b)+" Juta "+self.terbilang(a)):1E10>a?(b=parseInt(String(a/1E9).substr(0,1)),a%=1E9,b=self.terbilang(b)+" Milyar "+self.terbilang(a)):1E11>a?(b=parseInt(String(a/1E9).substr(0,2)),a%=1E9,b=self.terbilang(b)+" Milyar "+self.terbilang(a)):
			1E12>a?(b=parseInt(String(a/1E9).substr(0,3)),a%=1E9,b=self.terbilang(b)+" Milyar "+self.terbilang(a)):1E13>a?(b=parseInt(String(a/1E10).substr(0,1)),a%=1E10,b=self.terbilang(b)+" Triliun "+self.terbilang(a)):1E14>a?(b=parseInt(String(a/1E12).substr(0,2)),a%=1E12,b=self.terbilang(b)+" Triliun "+self.terbilang(a)):1E15>a?(b=parseInt(String(a/1E12).substr(0,3)),a%=1E12,b=self.terbilang(b)+" Triliun "+self.terbilang(a)):1E16>a&&(b=parseInt(String(a/1E15).substr(0,1)),a%=1E15,b=self.terbilang(b)+" Kuadriliun "+self.terbilang(a));a=b.split(" ");c=[];for(b=0;b<a.length;b++)""!=a[b]&&c.push(a[b]);return c.join(" ")
		}

		
		get(){
			var self = this
			var urlDomain = self.$get.dev != undefined ? 'internaldev' : 'main'
			return new Promise(async (resolve, reject) => {
				try {
					var bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
					'September', 'Oktober', 'November', 'December'
				]
					var po_id = self.$get.po_id
					if(self.$get.fake == 1)
						var data = await axios.post('https://'+urlDomain+'.buanamultiteknik.com/api/data/get', {
							q: `SELECT p.charge1, p.charge2, p.other_charge, p.charge1_desc, p.charge2_desc, p.discount, p.discount_desc,p.ref_offer_no, 
							p.payment_term, p.despatch, p.shipping_address, p.miscellaneous, p.promised_delivery_date,
							u.name as dir, ms.pic_name, p.currency, p.note as keterangan, s.*, order_qty*price_per_item as total_price_per_item, i.specification from (
								select s.id, coalesce(s.order_no, s.id) as order_no, s.flag, s.purchase_order_id, s.item_id, i.item_no, i.item_name, i.unit, i.original_manufacture, i.manufacture_pn, i.article_no, i.item_type, '' as description, sum(s.order_qty) as order_qty, 
								(select price_per_item from fake_purchase_order_item where flag=1 and purchase_order_id = s.purchase_order_id and item_id = s.item_id limit 1) as price_per_item 
								from fake_purchase_order_item s left join m_item i on i.id = s.item_id
								where i.id is not null and s.flag = 1
								group by s.item_id, i.original_manufacture, i.manufacture_pn, i.specification, i.unit, s.purchase_order_id
							)s 
							left join m_item i on i.id = s.item_id
							left join fake_purchase_order p on p.id = s.purchase_order_id
							left join m_supplier ms on ms.id = p.supplier_id
							left join m_department d on d.id = p.dept_id
							left join users u on u.id = d.approval_1
							where p.id = ${po_id} order by s.order_no`
						})
					else
						var data = await axios.post('https://'+urlDomain+'.buanamultiteknik.com/api/data/get', {
							q: `SELECT p.charge1, p.charge2, p.other_charge, p.charge1_desc, p.charge2_desc, p.discount, p.discount_desc,p.ref_offer_no, 
							p.payment_term, p.despatch, p.shipping_address, p.miscellaneous, p.promised_delivery_date,
							u.name as dir, ms.pic_name, p.currency, p.note as keterangan, s.*, order_qty*price_per_item as total_price_per_item, i.specification from (
								select s.id, coalesce(s.order_no, s.id) as order_no, s.flag, s.purchase_order_id, s.item_id, i.item_no, i.item_name, i.unit, i.original_manufacture, i.manufacture_pn, i.article_no, i.item_type, '' as description, sum(s.order_qty) as order_qty, 
								(select price_per_item from purchase_order_item where flag=1 and purchase_order_id = s.purchase_order_id and item_id = s.item_id limit 1) as price_per_item 
								from purchase_order_item s left join m_item i on i.id = s.item_id
								where i.id is not null and s.flag = 1
								group by s.item_id, i.original_manufacture, i.manufacture_pn, i.specification, i.unit, s.purchase_order_id
							)s 
							left join m_item i on i.id = s.item_id
							left join purchase_order p on p.id = s.purchase_order_id
							left join m_supplier ms on ms.id = p.supplier_id
							left join m_department d on d.id = p.dept_id
							left join users u on u.id = d.approval_1
							where p.id = ${po_id} order by s.order_no`
						})

					if(self.$get.fake == 1)
						var po = await axios.post('https://'+urlDomain+'.buanamultiteknik.com/api/data/get', {
							q: `select coalesce((select sum(payment_pct) from invoice where po_id = s.id), 0) as total_payment_pct, ms.pic_name, ms.address, s.*, date(s.approval_2_date) as approved_date, d.dept_code, d.dept_name, ms.name as supplier_name from fake_purchase_order s left join m_supplier ms on ms.id = s.supplier_id left join m_department d on d.id = s.dept_id where s.id = ${po_id}`
						})
					else
						var po = await axios.post('https://'+urlDomain+'.buanamultiteknik.com/api/data/get', {
							q: `select coalesce((select sum(payment_pct) from invoice where po_id = s.id), 0) as total_payment_pct, ms.pic_name, ms.address, s.*, date(s.approval_2_date) as approved_date, d.dept_code, d.dept_name, ms.name as supplier_name from purchase_order s left join m_supplier ms on ms.id = s.supplier_id left join m_department d on d.id = s.dept_id where s.id = ${po_id}`
						})
					var total = 0, charge1 = 0, charge2 = 0, other_charge = 0, discount = 0, discount_desc = "", charge1_desc = "", charge2_desc = "";
					var ref_offer_no = '';
					data.data.data.map((val, i)=>{
						val.no = i+1
						total+=Number(val.total_price_per_item)
						ref_offer_no = val.ref_offer_no
						charge1 = Number(val.charge1)
						charge2 = Number(val.charge2)
						other_charge = Number(val.other_charge)
						discount = Number(val.discount)
						discount_desc=val.discount_desc || ""
						charge1_desc = val.charge1_desc || ""
						charge2_desc = val.charge2_desc || ""
						val.article_no=val.article_no || ""

						if(val.promised_delivery_date != null){
							var promised_delivery_date = val.promised_delivery_date.split(' ')[0].split('-').reverse()
							promised_delivery_date[1] = bulan[Number(promised_delivery_date[1])]
							val.promised_delivery_date = promised_delivery_date.join(' ')

						}

						if(val.item_type.trim()=='Jasa'){
							var item_no1= 'BMT ID No: '+val.item_no
							var item_name1= 'Service Name: '+val.item_name
							var original_manufacture1= 'Vendor: '+val.original_manufacture
							var description1 ='Service Description'
							val.item_no=item_no1
							val.item_name=item_name1
							val.original_manufacture=original_manufacture1
							val.description=description1
						} else{
							var item_no1= 'BMT ID No: '+val.item_no
							var item_name1= 'Device Name: '+val.item_name
							var original_manufacture1= 'Original Manufacture: '+val.original_manufacture
							var manufacture_pn1= '\nManufacture PN/Type: '+val.manufacture_pn
							var article_no1="\nArticle Number: "+val.article_no
							var description1 ='Description'
							val.item_no=item_no1
							val.item_name=item_name1
							val.original_manufacture=original_manufacture1
							val.manufacture_pn=manufacture_pn1
							val.article_no=article_no1
							val.description=description1
						}
					})

					var isvis_charge2_desc=false;
					if(discount_desc.trim()!=''){
						discount_desc = "\n- Discount description: \n"+discount_desc
					}

					if(charge1_desc.trim()!=''){
						charge1_desc = "- Charge description 1: \n"+charge1_desc
					}

					if(charge2_desc.trim()!=''){
						charge2_desc = "\n- Charge description 2: \n"+charge2_desc
						isvis_charge2_desc=true
					}else{
						isvis_charge2_desc=false
					}

					var charge = charge1+charge2+other_charge
					var subtotal = Number(total).format(2,3)
					total = total + charge 

					var cur = {
						'IDR': 'Rupiah',
						'EUR': 'Euro',
						'CNY': 'Chinese Yuan',
						'SGD': 'SGD',
						'USD': 'USD',
						'GBP': 'Pound Sterling',
						'BDT': 'Bangladesh Taka',
					}

					var c = {
						'IDR': 'Rp',
						'EUR': 'EUR',
						'CNY': 'CNY',
						'USD': 'USD',
						'SGD': 'SGD',
						'GBP': 'GBP',
						'BDT': 'BDT',

					}
					
					var qr = ''

					if(po.data.data[0].approved == 3){
						var barcode = await self.barcode.get({
							bcid: 'qrcode',
							text: "https://"+urlDomain+".buanamultiteknik.com/#/sa/info/po_approved_"+po_id,//'https://internal.buanamultiteknik.com/#/sa/info?id='+po_id
							showtext: false
						})

						qr = {
							_type: "image",
							source: barcode.data,
							format: "image/png",
							width: 100,
							height: 100
						}
					}

					var date = po.data.data[0].po_date.split('-').reverse()
					date[1] = bulan[Number(date[1])]

					if(po.data.data[0].approved_date != null){
						var approval_2_date = po.data.data[0].approved_date.split('-').reverse()
						approval_2_date[1] = bulan[Number(approval_2_date[1])]
						approval_2_date = approval_2_date.join(' ')

					}

					var items = data.data.data, keterangan = '', name = '', tmp='', text=[], text1='', text2=''
					items.map(function(val){
						val.price_per_item = Number(val.price_per_item).format(2,3)
						keterangan = val.keterangan
						name = val.dir
						val.total_price_per_item = Number(val.total_price_per_item).format(2,3)
						val.order_qty = Number(val.order_qty).format(2,3)
						val.specification=val.specification||''
						tmp=val.specification.split(/\n/)
						text = [tmp.slice(0, tmp.length/2).join('\n'), tmp.slice(tmp.length/2).join('\n')]
						text1=text[0]
						text2=text[1]
						val.text1=text[0]
						val.text2=text[1]

					})
					items.sort((a,b)=>{
						return a.order_no - b.order_no
					})
					items.map((val, i)=>{
						val.no = i+1
					})

					keterangan = keterangan.replace(/\n\n/, '\n')
					var cr = c[po.data.data[0].currency]
					if(charge == 0){
						charge = '-'
					}
					else{
						charge = cr+' '+Number(charge).format(2,3)
					}
					if(discount == 0){
						discount = '-'
					}
					else{
						total = total - discount;
						discount = cr+' '+Number(discount).format(2,3)
					}
					var res = {
						status: true,
						data: {
							total_payment_pct: po.data.data[0].total_payment_pct,
							payment_term: po.data.data[0].payment_term,
							despatch: po.data.data[0].despatch,
							shipping_address: po.data.data[0].shipping_address,
							miscellaneous: po.data.data[0].miscellaneous,
							title: po.data.data[0].title,
							pic_name: po.data.data[0].pic_name,
							approved: po.data.data[0].approved,
							address: po.data.data[0].address||'',
							po_no: po.data.data[0].po_no,
							preview: 'DRAFT',
							po_date: date.join(' '),
							approval_2_date:approval_2_date,
							order_type: po.data.data[0].order_type,
							currency: cur[po.data.data[0].currency],
							c: c[po.data.data[0].currency],
							supplier_name: po.data.data[0].supplier_name,
							pt: 'PT. BMT BUANA MULTI TEKNIK',
							name: name,
							jabatan: 'Director',
							total: Number(total).format(2,3),
							subtotal: subtotal,
							discount: discount,
							charge: charge,
							totalret: Number(Number(total||0).toFixed(3)),
							terbilang: numbered(Number(Number(total||0).toFixed(3))),
							items: items,
							barcode: qr,
							keterangan: keterangan,
							ref_offer_no,
							charge1_desc: charge1_desc,
							charge2_desc: charge2_desc,
							discount_desc:discount_desc,
							text1:text1,
							text2: text2, isvis_charge2_desc
							
						},
					}
					resolve(res)
				} catch (err) {
					reject(App.format(err))
				}
			});
		}
	}
}