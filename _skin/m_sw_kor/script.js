////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
//  ▒   IE8이하 버전에서 HTML5사용하기
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
document.createElement('header');
document.createElement('footer');
document.createElement('hgroup');
document.createElement('nav');
document.createElement('section');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
//  ▒  convertible list - zardsama
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function chgListSkin(listname, type) {
	var list = $('.'+listname);
	if(list.length < 1) return;

	if(type == 1) {
		list.find('.prd_basic').addClass('col1').removeClass('col2').removeClass('col3').removeClass('col_img').removeClass('col_list');
		$('#btn_line').addClass('type1').removeClass('type2').removeClass('type3').removeClass('type4').removeClass('type5');
		$('#btn_line').attr('onclick','chgListSkin(\'prd_normal\', 2, this); return false;');
	} else if(type == 2) {
		list.find('.prd_basic').addClass('col2').removeClass('col1').removeClass('col3').removeClass('col_img').removeClass('col_list');
		$('#btn_line').addClass('type2').removeClass('type1').removeClass('type3').removeClass('type4').removeClass('type5');
		$('#btn_line').attr('onclick','chgListSkin(\'prd_normal\', 3, this); return false;');
	} else if(type == 3) {
		list.find('.prd_basic').addClass('col3').removeClass('col1').removeClass('col2').removeClass('col_img').removeClass('col_list');
		$('#btn_line').addClass('type3').removeClass('type1').removeClass('type2').removeClass('type4').removeClass('type5');
		$('#btn_line').attr('onclick','chgListSkin(\'prd_normal\', 4, this); return false;');
	} else if(type == 4) {
		list.find('.prd_basic').addClass('col_img').removeClass('col1').removeClass('col2').removeClass('col3').removeClass('col_list');
		$('#btn_line').addClass('type4').removeClass('type1').removeClass('type2').removeClass('type3').removeClass('type5');
		$('#btn_line').attr('onclick','chgListSkin(\'prd_normal\', 5, this); return false;');
	} else {
		list.find('.prd_basic').addClass('col_list').removeClass('col1').removeClass('col2').removeClass('col3').removeClass('col_img');
		$('#btn_line').addClass('type5').removeClass('type1').removeClass('type2').removeClass('type3').removeClass('type4');
		$('#btn_line').attr('onclick','chgListSkin(\'prd_normal\', 1, this); return false;');
	}
	setCookie(listname+'_config', 's'+type);
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
//  ▒  infinifyScroll - zardsama
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$.fn.IFScroll = function(speed, wait) {
	var $this = this;
	$this.IFScroll.speed = speed;
	$this.IFScroll.wait = wait;
	$this.IFScroll.len = $this.children().length;
	$this.IFScroll.direction = 1;
	$this.IFScroll.order = 0;
	$this.IFScroll.standby = true;
	$this.IFScroll.obj = $this[0];
	if(!$this.IFScroll.obj) return;
	$this.IFScroll.el = $this.IFScroll.obj.innerHTML;

	$this.css({"width":"100%", "overflow":"hidden", "white-space":"nowrap"});
	$this.IFScroll.orsize = $this.attr('scrollWidth');

	// window 사이즈 변경시 이벤트
	$(window).resize(function() {
		if($this.width() >= $this.IFScroll.orsize) { // 스크롤보다 큰 창크기
			$this.html($this.IFScroll.el);
			$this.IFScroll.status = false;
		} else if($this.IFScroll.status == false) { // 스크롤 동작가능한 창크기
			$this.IFScroll.obj.innerHTML += $this.IFScroll.el+$this.IFScroll.el;
			$this.IFScroll.point = $this.IFScroll.startpoint;
			$this.IFScroll.order = 0;
			$this.IFScroll.status = true;
		}
	});

	// 초기 위치 지정
	$this.IFScroll.setPoint = function(event) {
		if(!$this.IFScroll.point) {
			$this.IFScroll.startpoint = $this.attr('scrollWidth');
			$this.IFScroll.point = $this.IFScroll.startpoint

			if($this.width() >= $this.IFScroll.orsize) {
				$this.IFScroll.status = false;
				return;
			}

			$this.IFScroll.status = true;
			$this.IFScroll.obj.innerHTML += $this.IFScroll.el+$this.IFScroll.el;
			$this.attr('scrollLeft', $this.IFScroll.point);
		}
	}

	// 이동 처리
	$.fn.IFScrollMove = function(chgDirection) {
		if($this.width() >= $this.IFScroll.orsize) {
			return;
		}

		// 마우스오버 멈춤
		if(chgDirection) {
			$this.IFScroll.direction = chgDirection;
			if($this.IFScroll.standby != true) return;
			if($this.IFScroll.wait > 0) $this.IFScrollStop();
		}
		if($this.IFScroll.standby != true) return;
		$this.IFScroll.standby = false;

		// 초기위치 설정
		$this.IFScroll.setPoint();
		if($this.IFScroll.order == 0) {
			$this.attr('scrollLeft', $this.IFScroll.point);
		}

		// 태그사이 공백 보정
		var item1 = $this.IFScroll.obj.children[$this.IFScroll.order+$this.IFScroll.len];
		var item2 = $this.IFScroll.obj.children[$this.IFScroll.order+$this.IFScroll.len+chgDirection];
		var margin = (item1 && item2) ? Math.abs(($(item1).offset().left-$(item2).offset().left+(item1.offsetWidth*chgDirection))) : 0;

		// 이동
		var item = $this.children($this.IFScroll.order);
		var dir = $this.IFScroll.direction > 0 ? "+=" : "-=";
		$this.animate({"scrollLeft":dir+(item.width()+margin)}, {"duration":this.speed, "queue":false, "complete":function(){
			$this.IFScroll.standby = true;
		}});

		// 다음 패턴 정의
		$this.IFScroll.order += $this.IFScroll.direction;
		if($this.IFScroll.order < 0) $this.IFScroll.order = $this.IFScroll.len-1;
		if($this.IFScroll.order >= $this.IFScroll.len) $this.IFScroll.order = 0;

		if($this.IFScroll.wait > 0 && chgDirection) {
			$this.IFScrollStart();
		}
	}

	// 자동 이동
	$.fn.IFScrollStart = function() {
		$this.IFScroll.timer = setInterval(function() {
			$this.IFScrollMove($this.IFScroll.direction);
		}, $this.IFScroll.wait);
	}

	// 자동이동 정지
	$.fn.IFScrollStop = function() {
		clearInterval($this.IFScroll.timer);
		$this.IFScroll.timer = null;
	}

	$this.IFScroll.setPoint();
	if(wait > 0) $this.IFScrollStart();
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
//  ▒   etc
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* 사이드메뉴 토글 */
function toggle_nav(name) {
	var obj_name = $('nav.'+name);
	if(obj_name.hasClass('is_show')) {
		$('body').removeClass('view_nav');
		obj_name.removeClass('is_show');
	}
	else {
		$('body').addClass('view_nav');
		obj_name.addClass('is_show');
	}
}

/* 토글 */
function toggle_view(selector, obj){
	var search = $('#'+selector+'');
	var obj = $(obj);
	if (search.css('display') == 'none') {
		search.show();
		obj.addClass('active');
	} else {
		search.hide();
		obj.removeClass('active');
	}
}

/* 탭뷰 */
function tabover(name, no) {
	var tabs = $('.tab_'+name+'').find('li');
	tabs.each(function(idx) {
		var detail = $('.tabcnt_'+name+idx);
		var link = $(this).find('a');
		if(no == idx) {
			detail.show();
			link.addClass('active');
		} else {
			detail.hide();
			link.removeClass('active');
		}
	})
}

/* 전체체크 */
function cartCheckAll(checked) {
	$(':checkbox[name^="cno["], :checkbox[name^="wno["]').attrprop('checked', checked);
}

/* 검색결과 검색어순위 */
function searchrank() {
	if ($('#rank_search ol').css('display') == 'none') {
		$('#rank_search ol').slideDown('fast');
	} else {
		$('#rank_search ol').slideUp('fast');
	}
}

/* 주문조회상세, 주문서 토글 */
function toggle_next(obj){
	if ($(obj).next('div').css('display') == 'none'){
		$(obj).removeClass('active');
		$(obj).next('div').slideDown('fast');
	} else {
		$(obj).addClass('active');
		$(obj).next('div').slideUp('fast');
	}
}

/* 딤드클릭 */
$(window).ready(function(){
	$('#dimmed').click(function(){
		$('body').removeClass('view_nav');
		$('nav').removeClass('is_show')
	})
});

/* 스크롤 이동 */
$(window).scroll(function(){
	var y=$(this).scrollTop();
	if( y > 300 ){
		$('.btn_scroll').fadeIn();
	} else {
		$('.btn_scroll').fadeOut();
	}
});
function scrollup(){
	$('html, body').animate({scrollTop:0}, 'slow');
}