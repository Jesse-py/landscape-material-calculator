/* Landscape Material Calculator: math and form wiring. Runs only in the visitor's browser; sends nothing anywhere.
 * Imperial: lengths in feet, depth in inches, volume in cubic yards (27 cu ft), weight in short tons.
 * Metric: lengths in meters, depth in centimeters, volume in cubic meters, weight in tonnes.
 * Concrete bag yields: QUIKRETE Concrete Mix (No. 1101) data sheet (40 lb 0.30, 60 lb 0.45, 80 lb 0.60 cu ft). */
(function (root) {
	'use strict';

	var FT3_PER_YD3 = 27;
	var BAGS = [ { lb: 40, ft3: 0.30 }, { lb: 60, ft3: 0.45 }, { lb: 80, ft3: 0.60 } ];

	function pos(x) {
		x = typeof x === 'string' ? parseFloat(x) : x;
		return isFinite(x) && x > 0 ? x : 0;
	}

	// Area in square feet (imperial) or square meters (metric).
	function area(shape, d) {
		switch (shape) {
			case 'circle': return Math.PI * Math.pow(pos(d.diameter) / 2, 2);
			case 'triangle': return pos(d.base) * pos(d.height) / 2;
			case 'area': return pos(d.area);
			default: return pos(d.length) * pos(d.width);
		}
	}

	// Rounds up to a multiple of step; a small tolerance keeps exact multiples from rounding up on float error.
	function roundUp(q, step) {
		if (!(step > 0)) return q;
		return Math.ceil(q / step - 1e-9) * step;
	}

	/* One result. m = { soldBy, density, price, step, min, bags }, d = dimensions (strings or numbers).
	 * Returns volume (yd3 or m3, with waste), weight (tons or tonnes, if density is set), qty (in the unit sold),
	 * order (qty rounded up to the step and raised to the minimum), cost (order x price, or 0), and bags (concrete). */
	function calc(opts) {
		var metric = !!opts.metric, m = opts.material, d = opts.dims;
		var a = area(opts.shape, d);
		var depth = pos(d.depth);
		var waste = 1 + pos(opts.waste) / 100;
		var raw = metric ? a * depth / 100 : a * depth / 12 / FT3_PER_YD3;
		var volume = raw * waste;
		var weight = pos(m.density) ? volume * m.density : 0;
		var byWeight = m.soldBy === 'weight' && weight > 0;
		var qty = byWeight ? weight : volume;
		var order = 0;
		if (qty > 0) {
			order = Math.max(roundUp(qty, pos(m.step)), pos(m.min));
		}
		var ft3 = metric ? 0 : volume * FT3_PER_YD3;
		var bags = m.bags && ft3 > 0 ? BAGS.map(function (b) {
			return { lb: b.lb, count: Math.ceil(ft3 / b.ft3 - 1e-9) };
		}) : [];
		return {
			area: a, volume: volume, weight: weight, byWeight: byWeight, qty: qty, order: order,
			cost: pos(m.price) && order ? order * m.price : 0, bags: bags,
		};
	}

	// Plain number for display: up to 2 decimals, trailing zeros dropped (3.10 -> 3.1, 4.00 -> 4).
	function num(x) {
		var r = Math.round(x * 100) / 100;
		return (r.toFixed(2)).replace(/\.?0+$/, '');
	}
	function money(cur, x) {
		return cur + (Math.round(x * 100) / 100).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
	}
	function fmt(s) {
		var args = Array.prototype.slice.call(arguments, 1), i = 0;
		return s.replace(/%(\d)\$s|%s|%%/g, function (all, n) {
			if (all === '%%') return '%';
			return String(n ? args[n - 1] : args[i++]);
		});
	}

	// "1 cubic yard" / "2.5 cubic yards": the singular string is used when the amount shows as exactly 1.
	function amount(t, unit, x) {
		var n = num(x);
		return fmt(n === '1' && t[unit + 'One'] ? t[unit + 'One'] : t[unit], n);
	}

	// Result text lines for one calculation (used by the page and by the tests).
	function describe(cfg, key, r) {
		var t = cfg.i18n, m = cfg.materials[key], metric = cfg.metric;
		if (!(r.volume > 0)) return [ t.empty ];
		var vol = amount(t, metric ? 'm3' : 'yd3', r.volume);
		var wt = r.weight ? amount(t, metric ? 'tonnes' : 'tons', r.weight) : '';
		var lines = [];
		if (r.byWeight) {
			lines.push(fmt(t.needBoth, wt, vol));
		} else {
			lines.push(fmt(t.need, vol));
			if (wt) lines.push(fmt(t.weighs, wt));
		}
		if (pos(cfg.waste)) lines.push(fmt(t.waste, num(cfg.waste)));
		var unit = r.byWeight ? (metric ? 'tonnes' : 'tons') : (metric ? 'm3' : 'yd3');
		if (r.order !== r.qty) {
			var o = fmt(t.order, amount(t, unit, r.order));
			if (pos(m.min) && r.order === m.min && roundUp(r.qty, pos(m.step)) < m.min) {
				o += ' ' + fmt(t.minNote, amount(t, unit, m.min));
			} else if (pos(m.step)) {
				o += ' ' + fmt(t.stepNote, num(m.step));
			}
			lines.push(o);
		}
		if (r.cost) {
			var unitName = r.byWeight ? (metric ? t.unitT : t.unitTon) : (metric ? t.unitM3 : t.unitYd3);
			lines.push(fmt(t.cost, money(cfg.currency, r.cost), money(cfg.currency, m.price), unitName));
		}
		if (r.bags.length) {
			lines.push(t.bagsHead + ' ' + r.bags.map(function (b) { return fmt(t.bag, b.count, b.lb); }).join(', ') + '.');
		}
		return lines;
	}

	var FIELDS = { rect: [ 'length', 'width' ], circle: [ 'diameter' ], triangle: [ 'base', 'height' ], area: [ 'area' ] };

	function setup(el) {
		var cfg;
		try { cfg = JSON.parse(el.getAttribute('data-lmcalc')); } catch (e) { return; }
		var form = el.querySelector('form'), out = el.querySelector('.lmcalc-result');
		// Look inputs up by name: form.elements.length is the element count, not the "length" field.
		var input = function (name) { return form.querySelector('[name="' + name + '"]'); };
		var lastKey = input('material').value;

		function update(ev) {
			var key = input('material').value, shape = input('shape').value;
			var depthInput = input('depth');
			// A new material brings its usual depth, unless the visitor typed their own.
			if (ev && ev.target === input('material') && key !== lastKey) {
				if (!depthInput.value || pos(depthInput.value) === cfg.materials[lastKey].depth) {
					depthInput.value = cfg.materials[key].depth ? String(cfg.materials[key].depth) : '';
				}
				lastKey = key;
			}
			Object.keys(FIELDS).forEach(function (s) {
				FIELDS[s].forEach(function (f) {
					var p = el.querySelector('[data-field="' + f + '"]');
					if (p) p.hidden = FIELDS[shape].indexOf(f) < 0;
				});
			});
			var dims = { depth: depthInput.value };
			FIELDS[shape].forEach(function (f) { dims[f] = input(f).value; });
			var r = calc({ metric: cfg.metric, material: cfg.materials[key], shape: shape, dims: dims, waste: cfg.waste });
			out.textContent = '';
			describe(cfg, key, r).forEach(function (line) {
				var p = document.createElement('p');
				p.textContent = line;
				out.appendChild(p);
			});
		}
		form.addEventListener('input', update);
		form.addEventListener('change', update);
		form.addEventListener('submit', function (e) { e.preventDefault(); update(); });
		update();
	}

	var api = { area: area, calc: calc, describe: describe, num: num, money: money, fmt: fmt, roundUp: roundUp, BAGS: BAGS, setup: setup };
	if (typeof module !== 'undefined' && module.exports) {
		module.exports = api;
	} else {
		root.LandscapeMaterialCalculator = api;
		var start = function () {
			var els = document.querySelectorAll('[data-lmcalc]');
			for (var i = 0; i < els.length; i++) setup(els[i]);
		};
		if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
	}
})(this);
