
function update_graph() {

	APP.idGraph = new Vue({

		data: {
			statistic: [ ]
		},

		template: `
<canvas id="idGraphCanvas" style="width: 100%; height: 100%;"></canvas>
`,

		created: function() {
			var _this = this;
			
		},

		updated: function() {
			this.updateGraph();
		},

		methods: {
			updateGraph: function() {
				var ctx = document.getElementById(`idGraphCanvas`);
				var opt = chartGetOpt();
				opt.options.title.display = true;
				opt.options.title.text = 'График здоровья';
				opt.options.title.fontColor = '#5F5';
				opt.data.labels = [ ];
				opt.data.datasets[0].data = [ ];
				for (let i = 0; i < this.statistic.length; i++) {
					let z = this.statistic[i];
					let dt = UnixToDate(z.date);
					opt.data.labels.push(`${dt.getDate()}`);
					opt.data.datasets[0].data.push(z.value);
				}
				var myChart = new Chart(ctx.getContext('2d'), opt);
			}
		}

	});

	APP.idGraph.$mount();
	$('#idCanvasGraph').html("");
	$('#idCanvasGraph')[0].appendChild(APP.idGraph.$el);
	APP.idGraph.updateGraph();

	fetch("/input.php?m=graph&tick=" + Date.now()).then(function(response) {
		response.json().then(function(data) {
			if (is_obj(data)) {
				if (!data.ok) {
					APP.idGraph.statistic = [];
				} else {
					APP.idGraph.statistic = data.data;
				}
				APP.idGraph.updateGraph();
			}
		});
	}).catch(function(err) {
		_Log("Get graph data ERROR", err);
	});

}

$(function() {
	update_graph();
});
