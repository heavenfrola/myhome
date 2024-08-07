// 2013-11-28 jquery-wingscroll by wisa
if(typeof $.prop == 'undefined') {
	$.prototype.prop = $.attr;
}

(function($, undefined) {

	var frame_style = {
		'position': 'relative',
		'overflow': 'hidden',
		'list-style-type': 'none',
		'margin': 0,
		'padding' : 0
	}

	var list_style = {
		'position':'absolute', 
		'list-style-type': 'none'
	}

	$.fn.wingscroll = function(option) {
		if(this.length < 1) return;
		if(!option.oid) return;

		if(!option.pause_time) option.pause_time = 0;
		if(!option.direction) option.direction = 3;
		if(!option.speed) option.speed = 5;
		if(!option.pause_time) option.pause_time = 0;
		option.width = parseInt(option.width);
		option.height = parseInt(option.height);
		option.direction = parseInt(option.direction);
		option.speed = parseInt(option.speed);
		option.pause_time = parseInt(option.pause_time);
		option.pause_type = parseInt(option.pause_type);
		if(!option.pause_type) option.pause_type = 0;
		if(option.pause_type >= this.length) option.pause_type = 0;
		this.option = option;

		var $this = this;
		this.twidth = this.theight = 0;

		this.frame = $('#'+option.oid);
		this.paging = $('#'+option.oid+'_2');
		this.dir = option.direction;
		this.default_direction = option.direction;
		this.isover = false;
		if(option.pause_type == 0) this.paging.remove();

		if(this.frame.length != 1) {
			window.alert('object not exists ['+option.oid+']');
			return false;
		}

		// hidden layer
		var parent = this.frame;
		while(parent.length > 0) {
			if(parent.css('display') == 'none') {
				$this.hideparent = parent;
				this.livecheck = setInterval(function() {
					if($this.hideparent.css('display') != 'none') {
						$this.init();
					}
				}, 10);
			}
			parent = parent.parent();
			if(parent.length < 1) break;
			if(parent[0].tagName == 'HTML') break;
		}

		if(option.auto_start == 'Y') {
			this.frame.bind({
				'mouseover' : function() {
					$this.isover = true;
				},
				'mouseleave' : function() {
					if($this.isover == true) {
						setTimeout(function() { 
							$this.isover = false;
							$this.next();
						}, (1000*option.pause_time));
					}
				}
			});
		}

		$this.init = function() {
			$this.twidth = $this.theight = 0;

			frame_style.width = $this.option.width;
			frame_style.height = $this.option.height;
			$this.frame.css(frame_style);
			if(!$this.elements) $this.elements = new Array();
			var x = y = 0;
			for(var loop = 0; loop <= 2; loop++) {
				for(var i = 0; i < $this.length; i++) {
					var en = (loop*$this.length)+i;
					if($this.elements[en]) {
						var _li = $this.elements[en];
					} else {
						var _li = $(document.createElement('li'));
						_li.html($this[i]);
					}

					if(option.direction == 1 || option.direction == 2) {
						list_style.top = y;
						list_style.left = 0;
					} else {
						list_style.top = 0;
						list_style.left = x;
					}

					_li.css(list_style);
					$this.frame.append(_li);
					x += _li.width();
					y += _li.height();

					if(option.pause_type == 0) {
						if(i == 0) $this.elements[loop] = _li;
					} else {
						$this.elements[(loop*$this.length)+i] = _li;
					}
				}
				if(loop == 0) {
					$this.twidth = x;
					$this.theight = y;
				}
			}
			$this.frame.prop('scrollLeft', $this.twidth);
			$this.frame.prop('scrollTop', $this.theight);

			if(x > 0 && $this.livecheck) clearInterval($this.livecheck);
		}
		this.init();

		this.cnt = $this.elements.length / 3;
		this.morder =(this.dir == 2 || this.dir == 4) ? this.cnt : 0;
		this.pmconst = ($this.dir == 2 || $this.dir == 4) ? -1 : 1;

		$this.setAuto = function(val) {
			var old = option.auto_start;
			if(val == 0) val = 'N';
			else if(val == 1) val = 'Y';
			else {
				val = (option.auto_start == 'Y') ? 'N' : 'Y';
			}

			option.auto_start = val;
			if(option.auto_start == 'Y' && old != val) {
				$this.Break = false;
				setTimeout(function(){$this.next()}, (1000*option.pause_time));
			}
			if(option.auto_start == 'N' && old != val) {
				$this.Break = true;
			}
		}
 
		$this.next = function(force) {
			if($this.isover == true && force != true) return;
			if($this.Break == true) {
				if(force != true) {
					//$this.Break = false;
					return;
				}
			}
			if($this.ing == true) return;
			$this.ing = true;

			if($this.frame.prop('scrollLeft') == 0) $this.frame.prop('scrollLeft', $this.twidth);
			if($this.frame.prop('scrollTop') == 0) $this.frame.prop('scrollTop', $this.theight);

			var pos = $this.getPosition();
			if($this.direction == 1 || $this.direction == 2) {
				param = {'scrollTop': pos.top}
				$this.frame.prop('scrollLeft', 0);
			} else {
				param = {'scrollLeft': pos.left}
				$this.frame.prop('scrollTop', 0);
			}
 
			if($this.frame.prop('scrollLeft') ==  param.scrollLeft) {
				$this.frame.prop('scrollLeft', 0);
			}
			$this.frame.animate(param, {'queue':false, 'duration':(option.speed*300), 'complete':function() {
				$this.ing = false;

				if($this.morder > $this.cnt || $this.morder < 0 || option.pause_type == 0) {
					$this.frame.prop('scrollLeft', $this.frame.prop('scrollLeft')-($this.twidth*$this.pmconst));
					$this.frame.prop('scrollTop', $this.frame.prop('scrollTop')-($this.theight*$this.pmconst));
					$this.morder -= ($this.cnt * $this.pmconst); 
					if(option.pause_type == 0) {
						$this.morder = 0;
					}
				}

				if($this.bullet && $this.bullet.length > 0) {
					$this.bullet.filter('.selected').removeClass('selected');
					if($this.dir == 2 || $this.dir == 4) {
						$this.bullet.eq($this.morder).addClass('selected');
					} else {
						var tmp = $this.morder;
						if(tmp >= $this.cnt) tmp = 0;
						$this.bullet.eq(tmp).addClass('selected');
					}
				}

				if(option.auto_start == 'Y' && force != true) {
					setTimeout(function(){$this.next()}, (1000*option.pause_time));
				}
			}});
		}

		$this.getPosition = function() {
			$this.pmconst = ($this.dir == 2 || $this.dir == 4) ? -1 : 1;
			$this.morder += option.pause_type*$this.pmconst;
			if(option.pause_type == 0) $this.morder = 0;

			var obj = $this.elements[$this.cnt+$this.morder];

			var left = 0;
			var top = 0;
			switch($this.dir) {
				case 1 :
				case 2 :
					top = parseInt(obj.css('top'));
				break;
				case 3 :
				case 4 :
					left = parseInt(obj.css('left'));
				break;
			}

			return {'left':left, 'top':top};
		}

		$this.chdir = function(dir) {
			if($this.default_direction == 1 || $this.default_direction == 2) {
				dir = dir == 1 ? 1 : 2;
			} else {
				dir = dir == 1 ? 3 : 4;
			}
			$this.dir = dir;

			if(option.auto_start != 'Y') {
				$this.next(true);
			}
		}

		setTimeout(function() {
			//$this.init();
		}, 10);

		if(option.auto_start == 'Y') {
			setTimeout(function(){$this.next()}, (1000*option.pause_time));
		}

		// paging
		if(this.paging.length == 1) {
			if(option.auto_start == 'Y') {
				this.paging.bind({
					'mouseover' : function() {
						$this.isover = true;
					},
					'mouseleave' : function() {
						if($this.isover == true) {
							setTimeout(function() {
								$this.isover = false;
								$this.next();
							}, (1000*option.pause_time));
						}
					}
				});
			}
			this.bullet = this.paging.find('ul>li');
			this.bullet.css('cursor', 'pointer');
			this.bullet.click(function() {
				$this.morder = $(this).index()-(option.pause_type*$this.pmconst);
				$this.next(true);
			});

			$this.bullet.eq(0).addClass('selected');
		}

		return this;
	}

})(jQuery);