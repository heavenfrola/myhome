function chartMake(bind, option) {
	let resultChart = new Chart(document.getElementById(bind), option);
	if ((option.type !== 'treemap' && option.type !== 'pie') && (!resultChart.options.indexAxis || resultChart.options.indexAxis != 'y')) {
		resultChart.options.scales.y.grid.borderDash = [3, 7];
		resultChart.options.scales.y.grid.borderDashOffset = 5;
		resultChart.options.scales.y.grid.drawTicks = false;
		resultChart.options.scales.y.grid.drawBorder = false;
		resultChart.options.scales.y.ticks.stepSize = 2;
		resultChart.options.scales.y.ticks.maxTicksLimit = 6;
		resultChart.options.scales.y.afterDataLimits = function(scale) {
			if(scale.max < 5) {
				scale.max = 5;
			}
		};
	}
	resultChart.update();
	return resultChart;
}

Chart.Tooltip.positioners.custom = function(elements, position) {
	if (!elements.length) {
		return false;
	}
	let i, len;
	let x = 0;
	let y = 0;
	let count = 0;
	for (i = 0, len = elements.length; i < len; ++i) {
		const el = elements[i].element;
		if (el && el.hasValue()) {
			const pos = el.tooltipPosition();
			x += pos.x;
			y += pos.y;
			++count;
		}
	}

	return {
		x: x / count,
		y: y / count
	};
}

Chart.Tooltip.positioners.custom2 = function (elements, position) {
	if (!elements.length) {
		return false;
	}
	let i, len;
	let x = 0;
	let y = 0;
	let count = 0;
	for (i = 0, len = elements.length; i < len; ++i) {
		const el = elements[i].element;
		if (el && el.hasValue()) {
			const pos = el.tooltipPosition();
			x += pos.x;
			y += pos.y;
			++count;
		}
	}

	return {
		x: 0,
		y: y / count
	};
}

function externaltooltip(context) {

	const {chart, tooltip} = context;

	let tooltipEl = document.getElementById('chartjs-tooltip');

	if (!tooltipEl) {
		tooltipEl = document.createElement('div');
		tooltipEl.id = 'chartjs-tooltip';
		tooltipEl.innerHTML = '<table></table>';
		document.body.appendChild(tooltipEl);
	}

	const tooltipModel = context.tooltip;
	if (tooltipModel.opacity === 0) {
		tooltipEl.style.opacity = 0;
		return;
	}

	tooltipEl.style.background = '#FFF';
	tooltipEl.style.color = '#E8E8E8';
	tooltipEl.style.border = '1px solid #303742';
	tooltipEl.style.opacity = 1;

	tooltipEl.classList.remove('above', 'below', 'no-transform');
	if (tooltipModel.yAlign) {
		tooltipEl.classList.add(tooltipModel.yAlign);
	} else {
		tooltipEl.classList.add('no-transform');
	}

	function getBody(bodyItem) {
		return bodyItem.lines;
	}

	if (tooltipModel.body) {
		const titleLines = tooltipModel.title || [];
		const bodyLines = tooltipModel.body.map(getBody);
		let innerHtml = '<thead>';

		titleLines.forEach(function(title) {
			innerHtml += '<tr><th colspan="2" style="background:#303742;color:#fff;padding:5px 10px;text-align:left;">' + title + '</th></tr>';
		});
		innerHtml += '</thead><tbody>';

        let tbody_footer = '';
        let tbody_footer_value = 0;
        let tbody_footer_unit = '';
        let tbody_footer_average = '';
        let regex = /[^0-9]/g;
		bodyLines.forEach(function(body, i) {
			const colors = tooltipModel.labelColors[i];
			let style = 'background:' + colors.backgroundColor;
			style += '; border-color:' + colors.borderColor;
			style += '; border-width: 2px';
			style += '; border-radius: 50px';
			style += '; width: 8px';
			style += '; height: 8px';
			style += '; margin-right: 5px';
			style += '; display: inline-block;';
			const span = '<span style="' + style + '"></span>';

            tbody_footer = body?.[2]?.[0];
            tbody_footer_unit = body?.[2]?.[1];

            // footer에서 sum 할지 여부 + footer에 삽입 여부
            if (body?.[2]?.[2] === 'Y' || body?.[2]?.[2] === undefined) { // Y or undefined이면 푸터삽입 X,sum O
                innerHtml += '<tr><td style="padding:5px 10px;text-align:left;color:#303742;">' + span + body[0] + '</td><td style="padding:5px 10px;text-align:right;font-weight:bold;color:#000;">' + body[1] + '</td></tr>'; // body 삽입
                tbody_footer_value = tbody_footer_value + Number(body[1].replace(regex, ''));
            } else { // N이면 푸터 맨 하단 삽입, sum X
                tbody_footer_average = '<tr><td style="padding:5px 10px;text-align:left;color:#303742;">' + body[0] + '</td><td style="padding:5px 10px;text-align:right;font-weight:bold;color:#000;">' + body[1] + '</td></tr>';
            }
        });

        if (tbody_footer) {
            innerHtml += '<tr><td colspan="2" style="padding: 0px 10px;"><div style="border-top: 1px solid #e8e8e8;"></div></td></tr>';
            innerHtml += '<tr><td style="padding:0px 10px 5px 10px;text-align:left;color:#303742;">' + tbody_footer + '</td><td style="padding:5px 10px;text-align:right;font-weight:bold;color:#000;">' + tbody_footer_value.toLocaleString() + tbody_footer_unit + '</td></tr>';
            innerHtml += tbody_footer_average;
        }
		innerHtml += '</tbody>';

		let tableRoot = tooltipEl.querySelector('table');
		tableRoot.innerHTML = innerHtml;
	}

	const position = context.chart.canvas.getBoundingClientRect(); //차트 캔버스의 좌표
	const bodyFont = Chart.helpers.toFont(tooltipModel.options.bodyFont);

	tooltipEl.style.opacity = 1;
	tooltipEl.style.font = tooltip.options.bodyFont.string;
	tooltipEl.style.pointerEvents = 'none';
	tooltipEl.style.position = 'absolute';

	const {offsetLeft: positionX, offsetTop: positionY} = context.chart.canvas;

	var customPosition = Chart.Tooltip.positioners.custom.call(context.tooltip, context.tooltip._active, context.tooltip._eventPosition);
	tooltipEl.style.top = position.top + window.pageYOffset + tooltipModel.caretY - ($(tooltipEl).height() / 2)  + 'px';

	var linearWidth = 0;
	for(var s = 0; s < context.chart.boxes.length; s++) {
		if(context.chart.boxes[s].type == 'linear') {
			linearWidth = context.chart.boxes[s].width;
			break;
		}
	}

	var canvaspadding = 10;	
	if(document.getElementById(context.chart.canvas.id).style.paddingLeft) {
		canvaspadding = document.getElementById(context.chart.canvas.id).style.paddingLeft.replace('px', '');
	}
	if(customPosition.x <= (context.chart.chartArea.left + context.chart.chartArea.right) / 2) {
		tooltipEl.style.left = position.left + customPosition.x + Number(canvaspadding) + 'px';
	} else {
		tooltipEl.style.left = position.left + customPosition.x - $(tooltipEl).width() - Number(canvaspadding) + 'px';
	}
	if(chart.config._config.options.plugins.tooltip.position === 'custom2') {
		tooltipEl.style.left = position.left + context.chart.chartArea.left + 10 + 'px';
	}
}

const findLabel = (labels, evt) => {
	let found = false;
	let res = null;

	labels.forEach(l => {
		l.labels.forEach((label, index) => {
			if (evt.x > label.x && evt.x < label.x2 && evt.y > label.y && evt.y < label.y2) {
				res = {
					label: label.label,
					index
				};
				found = true;
			}
		});
	});

	return [found, res];
};

function getLabelHitBoxes(scales) {
	return Object.values(scales).map(function (s) {
		return {
			scaleId: s.id,
			labels: s._labelItems.map(function (e, i) {
				return {
					x: e.translation[0] - s._labelSizes.widths[i],
					x2: e.translation[0] + s._labelSizes.widths[i] / 2,
					y: e.translation[1] - s._labelSizes.heights[i] / 2,
					y2: e.translation[1] + s._labelSizes.heights[i] / 2,
					label: e.label,
					index: i
				};
			})
		}
	});
}

function showTooltipOnLabelsHover(axis = '') {
	return {
		afterEvent: (chart, event, opts) => {
			let evt = event.event;
			if (evt.type === 'mousemove' && event.inChartArea === false) {
				let s = {x: chart.scales.x, y: chart.scales.y};
				if (axis == 'y') {
					s = {y: chart.scales.y};
				}
				if (axis == 'x') {
					s = {x: chart.scales.x};
				}
				const [found, labelInfo] = findLabel(getLabelHitBoxes(s), evt);
				if (found) {
					$(this).css('cursor','pointer');
					chart.tooltip.setActiveElements([
						{datasetIndex: 0, index: labelInfo.index}
					]);

					chart.setActiveElements([
						{datasetIndex: 0, index: labelInfo.index}
					]);

					chart.update();
				}
			}
		}
	};
}

function goLinkOnLabelClick(axis = '') {
	return {
		afterEvent: (chart, event, opts) => {
			let evt = event.event;
			if (evt.type === 'click' && event.inChartArea === false) {
				let s = {x: chart.scales.x, y: chart.scales.y};
				if (axis == 'y') {
					s = {y: chart.scales.y};
				}
				if (axis == 'x') {
					s = {x: chart.scales.x};
				}
				const [found, labelInfo] = findLabel(getLabelHitBoxes(s), evt);
				if (labelInfo == null || chart.config._config.data.labels[labelInfo.index] === '즐겨찾기, 주소창에 직접입력') {
					return false;
				}
				if (found) {
					window.open(chart.config._config.data.labels[labelInfo.index], '_blank');
				}
			}
		}
	};
}