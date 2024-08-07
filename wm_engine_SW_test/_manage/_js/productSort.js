/**
 * 상품 진열순서, 기획전 상품진열순서
 **/

var lastSelect = null;
var timestamp = new Date().getTime();
$(function() {
	// ctrl 및 shift 키로 다중 선택
	$('.movable').on('click', function(e) {
		document.getSelection().removeAllRanges(); // 텍스트 선택 안되도록

		if (e.shiftKey == true) { // 셀렉트 연속 선택
			if(lastSelect == null) return false;

			$('.sel_'+timestamp).removeClass('sel_'+timestamp).removeClass('soldout');

			var direction = (lastSelect > $(this).index()) ? -1 : +1;
			var cursor = lastSelect;
			while(cursor != $(this).index()) {
				cursor += direction;
				$('.movable').eq(cursor).addClass('soldout').addClass('sel_'+timestamp);
			}
		} else if (e.ctrlKey == true) { // 컨트롤 키 다중 선택
			$(this).toggleClass('soldout');
		} else {
			$('.movable.soldout').removeClass('soldout');
			$(this).addClass('soldout');
		}

		if(e.shiftKey == false) {
			lastSelect = $(this).index();
			timestamp = new Date().getTime();
		}
	});

	// 상, 하 키보드 이벤트
	$(document).on('keydown', function(e) {
		switch(e.keyCode) {
			case 38 :
				selectedMove(-1);
				return false;
				break;
			case 40 :
				selectedMove(1);
				return false;
				break;
		}
	});

	// 리모콘 버튼 이벤트
	$('.move_btn_up').on('click', function() {
		var step = parseInt($('#step').val());
		if (isNaN(step)) step = 1;
		selectedMove(-(step))
	});

	$('.move_btn_dn').on('click', function() {
		var step = parseInt($('#step').val());
		if (isNaN(step)) step = 1;
		selectedMove(step)
	});

	$('.move_btn_top').on('click', function() {
		for (var i = ($('.soldout').length-1); i >= 0; i--) {
			$('.soldout').last().insertBefore($('.movable').first());
		}
	});

	$('.move_btn_bottom').on('click', function() {
		for (var i = 0; i <= ($('.soldout').length-1); i++) {
			$('.soldout').first().insertAfter($('.movable').last());
		}
	});
});

// 아이템 상하 이동
function selectedMove(step) {
	if ($('.soldout').length == 0) return false;

	if (step > 0 ) { // Down
		for (var i = $('.soldout').length-1; i >= 0; i--) {
			sopt = $('.soldout').eq(i);
			next = sopt.index()+step;

			if (next >= $('.movable').length) {
				step -= (sopt.index()+step)-($('.movable').length-1);
				next = $('.movable').length-1;
			}

			nopt = $('.movable').eq(next);
			if (nopt.hasClass('soldout') == false) {
				sopt.insertAfter(nopt);
			}
		}
	} else {
		for (var i = 0; i < $('.soldout').length; i++) {
			sopt = $('.soldout').eq(i);
			next = sopt.index()+step;

			if (next < 0) {
				step -= next;
				next = 0;
			}

			nopt = $('.movable').eq(next);
			if (nopt.hasClass('soldout') == false) {
				sopt.insertBefore(nopt);
			}
		}
	}

	// 이동 후 선택한 내용이 화면에 안보일 경우 스크롤 강제 이동
	var clientHeight = $(document).scrollTop()+$(window).height();
	var box_top = $('.soldout').eq(0).offset().top;
	var box_bottom = $('.soldout').last().offset().top + $('.soldout').outerHeight();
	if(box_bottom > clientHeight) {
		$(document).scrollTop($(document).scrollTop()+(box_bottom-clientHeight+10));
	}
	if(box_top < $(document).scrollTop()) {
		$(document).scrollTop($(document).scrollTop()-($(document).scrollTop()-box_top+20));
		console.log('test');
	}

}