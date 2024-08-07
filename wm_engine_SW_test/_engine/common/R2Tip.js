R2Tip = function(obj, msg, cName, ev) {
	if (typeof(obj) == "string" ) obj = document.getElementById (obj);

	if (!obj) return;
	if (obj.tullTipObject) return;

	obj.style.cursor = "pointer";
	if(!obj.id) {
		obj.id = 'R2Tip_'+new Date().getTime().toString().substring(0, 10)+Math.random(0,10000);
	}

	this.tullTip = document.createElement ("DIV");
	this.tullTip.id = "tullTip_"+obj.id;
	this.tullTip.style.position = "absolute";
	this.tullTip.style.display = "none";
	this.tullTip.className = cName;
	this.tullTip.innerHTML = msg;
	document.body.appendChild (this.tullTip);

	if (!cName) { // 클래스를 지정하지 않은경우 기본값
		this.tullTip.style.border = "solid 1px #ff8400";
		this.tullTip.style.backgroundColor = "#ffffcc";
		this.tullTip.style.padding = "5px";
		this.tullTip.style.opacity = ".9";
		this.tullTip.style.filter = "alpha(opacity=90)";
		this.tullTip.style.fontSize = "11px";
		this.tullTip.style.letterSpacing = "-1px";
		this.tullTip.style.whiteSpace = "nowrap";
		this.tullTip.style.zIndex = 2;
	}

	var id = obj.id;

	function addEvent(object, event ,listener) {
		if (object.addEventListener) object.addEventListener (event, listener, false); 
		else if (object.attachEvent) object.attachEvent ('on' + event, listener); 
	}

	over = function (event) {
		var ev = (window.event) ? window.event : event;
		var tobj = document.getElementById ("tullTip_"+id);

		tobj.style.display = "block";
		var pos = R2TipPos(tobj, ev);
		tobj.style.top = (pos[0]-tobj.offsetHeight-10)+'px';
		tobj.style.left = pos[1]+'px';
	}

	out = function () {
		var tobj = document.getElementById ("tullTip_"+id);
		tobj.style.display = "none";
	}

	move = function (event) {
		var ev = (window.event) ? window.event : event;
		var tobj = document.getElementById ("tullTip_"+id);

		var pos = R2TipPos(tobj, ev);
		tobj.style.top = (pos[0]-tobj.offsetHeight-5)+'px';
		tobj.style.left = pos[1]+'px';
	}

	if (ev) {
		var ev = (window.event) ? window.event : ev;
		over (ev);
	}

	addEvent(obj, "mouseover", over);
	addEvent(obj, "mouseout", out);
	addEvent(obj, "mousemove", move);

	obj.tullTipObject = this;
}

R2Tip.prototype.Msg = function(msg) {
	this.tullTip.innerHTML = msg;
}

function R2TipPos(obj, ev) {
	var bElement = (document.documentElement && document.documentElement.scrollTop > 0) ? document.documentElement : document.body;
	var top = (bElement.scrollTop + ev.clientY);
	var left = (bElement.scrollLeft + ev.clientX+10);

	obj.style.display = "block";

	if (left+obj.offsetWidth > bElement.scrollWidth) {
		left = bElement.scrollWidth - obj.offsetWidth - 30;
	}

	return new Array(top, left);
}