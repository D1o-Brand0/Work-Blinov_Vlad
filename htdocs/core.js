;

if (this.Global === undefined) this.Global = this;
Global.APP = new Object();

function _Log() {
	if ((!console) || (!console.log)) return;
	var s = "";
	for (var i = 0; i < arguments.length; i++) {
		try { console.log(arguments[i]); } catch (e) { }
	}
}

Object.prototype.getName = function() {
	var fr = /function (.{1,})\(/;
	var r = (fr).exec((this).constructor.toString());
	return (r && r.length > 1) ? r[1] : "";
};

if (!String.prototype.trim) {
	String.prototype.trim = function() { return this.replace(/^\s+|\s+$/g, ''); }
}

if (!String.prototype.clear) {
	String.prototype.clear = function() { return this.replace(/ /g, ''); }
}

if (!String.prototype.upper) {
	String.prototype.upper = function() { return this.toUpperCase(); }
};

if (!String.prototype.lower) {
	String.prototype.lower = function() { return this.toLowerCase(); }
};

var dateFormat = function() {

	var	token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
		timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
		timezoneClip = /[^-+\dA-Z]/g,
		pad = (val, len) => {
			val = String(val);
			len = len || 2;
			while (val.length < len) val = "0" + val;
			return val;
		};

	// Regexes and supporting functions are cached through closure
	return (date, mask, utc) => {
		var dF = dateFormat;

		// You can't provide utc if you skip other args (use the "UTC:" mask prefix)
		if (arguments.length == 1 && Object.prototype.toString.call(date) == "[object String]" && !/\d/.test(date)) {
			mask = date;
			date = undefined;
		}

		// Passing date through Date applies Date.parse, if necessary
		date = date ? new Date(date) : new Date;
		if (isNaN(date)) throw SyntaxError("invalid date");

		mask = String(dF.masks[mask] || mask || dF.masks["default"]);

		// Allow setting the utc argument via the mask
		if (mask.slice(0, 4) == "UTC:") {
			mask = mask.slice(4);
			utc = true;
		}

		var	_ = utc ? "getUTC" : "get",
			d = date[_ + "Date"](),
			D = date[_ + "Day"](),
			m = date[_ + "Month"](),
			y = date[_ + "FullYear"](),
			H = date[_ + "Hours"](),
			M = date[_ + "Minutes"](),
			s = date[_ + "Seconds"](),
			L = date[_ + "Milliseconds"](),
			o = utc ? 0 : date.getTimezoneOffset(),
			flags = {
				d:    d,
				dd:   pad(d),
				ddd:  dF.i18n.dayNames[D],
				dddd: dF.i18n.dayNames[D + 7],
				m:    m + 1,
				mm:   pad(m + 1),
				mmm:  dF.i18n.monthNames[m],
				mmmm: dF.i18n.monthNames[m + 12],
				yy:   String(y).slice(2),
				yyyy: y,
				h:    H % 12 || 12,
				hh:   pad(H % 12 || 12),
				H:    H,
				HH:   pad(H),
				M:    M,
				MM:   pad(M),
				s:    s,
				ss:   pad(s),
				l:    pad(L, 3),
				L:    pad(L > 99 ? Math.round(L / 10) : L),
				t:    H < 12 ? "a"  : "p",
				tt:   H < 12 ? "am" : "pm",
				T:    H < 12 ? "A"  : "P",
				TT:   H < 12 ? "AM" : "PM",
				Z:    utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
				o:    (o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
				S:    ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
			};

		return mask.replace(token, ($0) => {
			return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
		});
	};
}();

// Some common format strings
dateFormat.masks = {
	"default":      "ddd mmm dd yyyy HH:MM:ss",
	shortDate:      "m/d/yy",
	mediumDate:     "mmm d, yyyy",
	longDate:       "mmmm d, yyyy",
	fullDate:       "dddd, mmmm d, yyyy",
	shortTime:      "h:MM TT",
	mediumTime:     "h:MM:ss TT",
	longTime:       "h:MM:ss TT Z",
	isoDate:        "yyyy-mm-dd",
	isoTime:        "HH:MM:ss",
	isoDateTime:    "yyyy-mm-dd'T'HH:MM:ss",
	isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
};

// Internationalization strings
dateFormat.i18n = {
	dayNames: [
		"ВС", "ПН", "ВТ", "СР", "ЧТ", "ПТ", "СБ",
		"Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота"
	],
	monthNames: [
		"Янв", "Фев", "Мрт", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Нбр", "Дек",
		"Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"
	]
};

if (!Date.prototype.format) {
	Date.prototype.format = function(mask, utc) {
		return dateFormat(this, mask, utc);
	};
}

if (!Date.prototype.ToUTC) {
	Date.prototype.ToUTC = function() {
		return new Date(this.valueOf() + this.getTimezoneOffset() * 60000);
	};
}

if (!Date.prototype.ToMSK) {
	Date.prototype.ToMSK = function() {
		return new Date(this.valueOf() + this.getTimezoneOffset() * 60000 + 3*60*60000);
	};
}

function is_def(z) {
	return (z !== undefined);
}

function is_ndef(z) {
	return (z === undefined);
}

function is_null(z) {
	return (z === null);
}

function is_nan(z) {
	return isNaN(z);
}

function is_is(z) {
	if (is_def(z)) if (!is_null(z)) if (!is_nan(z)) return true;
	return false;
}

function iof(_src, _dst) {
	try {
		if ((_src) && (_src instanceof _dst)) return true; else return false;
	} catch (e) {
		return false;
	}
}

function is_str(z) {
	return (typeof z == "string") || ((typeof z == "object") && (z instanceof String));
}

function isstr(z) {
	if (!is_str(z)) return false;
	return (z.trim().length > 0);
}

function is_num(z) {
	if ( (typeof z == "number") && (!isNaN(z)) && (z != Infinity) ) return true;
	return (typeof z == "object") && (z instanceof Number);
}

function is_bool(z) {
	return (typeof z == "boolean") || ((typeof z == "object") && (z instanceof Boolean));
}

function is_array(z) {
	return (typeof z == "object") && (z instanceof Array);
}

function is_date(z) {
	return (typeof z == "object") && (z instanceof Date);
}

function is_func(z) {
	return (typeof z == "function");
}

function is_obj(z) {
	return (typeof z == "object") && (z != null);
}

function is_func_name(_name) {
	return (is_func(window[_name]) || is_obj(window[_name]));
}

function str_to_num(s, def) {
	if (is_num(s)) return Math.floor(s);
    if (!is_str(s)) {
    	if (is_num(def)) return Math.floor(def); else
    		if (is_str(def)) return _Int(def, 0); else return 0;
    }
    try {
    	return parseInt(s);
    } catch (e) {
		if (is_num(def)) return Math.floor(def); else return 0;
	}
}

function JSON_to(z) {
	return JSON.stringify(z);
}

function JSON_from(z) {
	try { return JSON.parse(z); } catch (e) { return null; }
}

function guid() {
    return 'fxyxyxyx-0xyx-bxyx-0xyx-5xyxyxyxyxyx'.replace(/[xy]/g, function(c)
		{
			var r = Math.random()*16|0, v = c === 'x' ? r : (r&0x3|0x8);
			return v.toString(16);
		});
}

function _ID(id) {
    if (is_str(id)) {
		return document.getElementById(id);
	} else if (is_obj(id)) {
		return id;
	}
	return null;
}

function DateToUnix(dt, f) {
	if (is_date(dt)) return Math.trunc(dt.getTime() / 1000);
	if (is_bool(dt)) {
		if (dt) {
			return Math.trunc(Date.now() / 1000);
		} else {
			var z = new Date().ToUTC();
			return Math.trunc(z.getTime() / 1000);
		}
	} else if (is_num(dt)) {
		if (!is_bool(f)) f = false;
		if (f) {
			return ( Math.trunc(Date.now() / 1000) + dt*24*3600);
		} else {
			var z = new Date().ToUTC();
			return ( Math.trunc(z.getTime() / 1000) + dt*24*3600);
		}
	}
	return 0;
}

function UnixToDate(i) {
	if (is_num(i)) return new Date(i*1000); else new Date(0);
}

function DateToStr(dt) {
	if (is_num(dt)) dt = UnixToDate(dt); else if (!is_date(dt)) dt = new Date();
	return dt.format('yyyy.mm.dd HH:MM:ss');
}

function DateToStrShort(dt) {
	if (is_num(dt)) dt = UnixToDate(dt); else if (!is_date(dt)) dt = new Date();
	return dt.format('dd.mm.yyyy');
}

function openLink(url) {
	if (is_str(url)) window.open(url, "_self");
	return false;
}

function openURL(url) {
	if (is_str(url)) window.open(url, "_blank");
	return false;
}

function CSS_Include(_url) {
	if (!is_str(_url)) return false;
	var _head = document.getElementsByTagName("head");
	if (_head && _head[0])
	{
		var css = document.createElement('link');
		css.setAttribute('rel', 'stylesheet');
		css.setAttribute('type', 'text/css');
		css.setAttribute('href', _url);
		_head[0].appendChild(css);
		return true;
	}
	return false;
}

function JS_Include(_url) {
	if (!is_str(_url)) return false;
	var _head = document.getElementsByTagName("head");
	if (_head && _head[0])
	{
		var js = document.createElement('script');
		js.setAttribute('language', 'javascript');
		js.setAttribute('type', 'text/javascript');
		js.setAttribute('src', _url);
		_head[0].appendChild(js);
		return true;
	}
	return false;
}

function chartGetOpt(_colors) {
	
	var opt = {
		type: 'line',
		data: { labels: [ ], datasets: [ ] },
		options: {
			responsive: false,
			layout: { padding: { left: 16, right: 8, top: 8, bottom: 16 } },
			scales: {
				xAxes: [ { display: true, ticks: { fontSize: 12, fontColor: 'rgba(255,255,255,0.75)' } } ],
				yAxes: [ { display: true, ticks: { fontSize: 16, fontColor: 'rgba(255,255,255,0.75)' } } ]
			},
			events: [ ],
			legend: { display: false },
			title: { display: false, text: '', position: 'top', padding: 4, lineHeight: 1, fontColor: '#FFF', fontSize: 18 },
			tooltips: { enabled: false, position: 'top'  },
			elements: { point: { radius: 2 }, line: { borderWidth: 2 } },
			animation: { duration: 0 },
			hover: { animationDuration: 0 },
			responsiveAnimationDuration: 0
		}
	};
	
	var a = [ ];
	if (is_array(_colors)) a = _colors;

	var i = 0;
	while (i < a.length) {
		if (!isstr(a[i])) a.splice(i, 1); else i++;
	}

	if (a.length == 0) a.push('rgba(255, 99, 132, 0.5)');
	for(i = 0; i < a.length; i++) {
		opt.data.datasets.push( {
			label: '',
			backgroundColor: a[i],
			borderColor: a[i],
			data: [ ],
			fill: false
		} );
	}
	
	return opt;
}
