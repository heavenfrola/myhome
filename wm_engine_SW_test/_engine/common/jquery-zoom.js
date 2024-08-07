// 2012-12-06 jquery-zoom by wisa
(function($, undefined) {
	$.fn.pzoom = function(bimg, json) {
		if(!bimg) return;
		if($(this).attr('jquery_pzoom')) return;
		if($('#zoom_img').length > 0) return;

		var parent = $(this).parent();
		var $this = this;
		var top = $this.offset().top;
		var left = $this.offset().left;

		// setup default value
		if(!json) json = {};
		var defaultvalue = {"width": $this.width(), "height": $this.height(), "position":"right", "margin": 10, "cursor":"pointer", "cursorc": "#000", "cursorb": "#fff", "cursora": ".5", "drag": false};
		for(var key in defaultvalue) {
			if(!json[key]) json[key] = defaultvalue[key];
		}

		switch(json.position) {
			case 'right' :
				ztop = top;
				zleft = left + $this.width() + json.margin;
			break;
			case 'left' :
				ztop = top;
				zleft = left - json.margin - json.width;
			break;
			case 'top' :
				ztop = top - json.margin - json.height;
				zleft = left;
			break;
			case 'bottom' :
				ztop = top + $this.height() + json.margin;
				zleft = left;
			break;
		}

		$(this).attr('jquery_pzoom', true)

		$this.move = function(event) {
			if(json.drag != true) {
				var sct = document.documentElement.scrollTop > document.body.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop;
				var scl = document.documentElement.scrollLeft > document.body.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft;

				var event = window.event ? window.event : event;
				var ct = (event.clientY+sct-top) - ($this.cursor.height()/2);
				var cl = (event.clientX+scl-left) - ($this.cursor.width()/2);
				if(cl < 0) cl = 0;
				if(ct < 0) ct = 0;
				if(cl > zf.width()-$this.cursor.width()-2) cl = (zf.width()-($this.cursor.width())-2);
				if(ct > zf.height()-$this.cursor.height()-2) ct = (zf.height()-($this.cursor.height())-2);

				$this.cursor.css({left:cl, top:ct});
			}

			if($.prop) {
				$this.zoomimg.prop('scrollLeft', (parseInt($this.cursor.css('left')) / $this.ratio_w));
				$this.zoomimg.prop('scrollTop', (parseInt($this.cursor.css('top')) / $this.ratio_h));
			} else {
				$this.zoomimg.attr('scrollLeft', (parseInt($this.cursor.css('left')) / $this.ratio_w));
				$this.zoomimg.attr('scrollTop', (parseInt($this.cursor.css('top')) / $this.ratio_h));
			}
		}

		$this.leave = function() {
			$this.attr('jquery_pzoom', '');
			$('#zoom_cursor').remove();
			zf.unbind('mouseleave');
			zf.unbind('mousemove');

			$('#zoom_img').fadeOut('fast', function() {
				$(this).remove();
			});
		}

		// 메인프레임
		var zf = $("<div id='zoom_frame'></div>").css({position:"relative"});
		$this.before(zf);
		zf.html($this);

		// 줌프레임
		$this.zoomimg = $("<div id='zoom_img'><img src='"+bimg+"' /></div>").css({position:"absolute", "overflow": "hidden", top:ztop, left: zleft, width:json.width, height:json.height, opacity:0});
		$('body').append($this.zoomimg);
		$this.zoomimg.animate({"opacity":1});

		// 메인프레임 커서
		$this.zoomimg.find('img').load(function(a) {
			var bigimg = $this.zoomimg.find('img');
			$this.ratio_w = $this.width() / bigimg.width();
			$this.ratio_h = $this.height() / bigimg.height();

			var h = (json.height*$this.ratio_h);
			var w = (json.width*$this.ratio_w);
			if(h > $this.height()) h = $this.height();
			if(w > $this.width()) w = $this.width();

			$this.cursor = $("<div id='zoom_cursor'></div>").css({border:"solid 1px "+json.cursorc, "position":"absolute", left:0, top:0, width:w, height:h, cursor:json.cursor, background:json.cursorb, "opacity":json.cursora});
			if(json.drag == true) $this.cursor.draggable({"cursor":"pointer", "containment":"#zoom_frame", "drag":$this.move});
			zf.append($this.cursor);

			zf.bind({
				mouseleave: $this.leave,
				mousemove: $this.move
			});
		});

	}
})(jQuery);