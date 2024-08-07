// 본문 내용 갱신하기
var d_interval = null;
function d_previewReload()
{
	var _content_id = parent.content_id;
	if (_content_id == 'm_content' && $(':checked[name=use_m_content]', parent.opener.document).length == 0) {
		_content_id = 'content2';
	}

	// 에디터 오픈 체크
	if (top.opener.oEditors.length == 0 || !top.opener.oEditors.getById[_content_id]) {
		if (_content_id == 'content2' || $(':checked[name=use_m_content]', top.opener.document).length == 0) {
			top.opener.tabover(0);
			$('#content22', top.opener.document).click();
		} else {
			top.opener.tabover(1);
			$('#mcontent_22', top.opener.document).click();
		}

		d_interval = setInterval(function() {
			if (top.opener.oEditors.length == 0 ||top.opener.oEditors.getById[_content_id]) {
				d_getContent(_content_id);
				clearInterval(d_interval);
			}
		}, 500);
		return false;
	}
	d_getContent(_content_id);
}

// 본문 내용 가져오기
function d_getContent(_content_id) {
	var editor = top.opener.oEditors.getById[_content_id];
	var content = editor.getIR();
 
	$('.__wing_d_preview_area').html(content);

	// 이미지 로딩 체크
	var imgs = $('.__wing_d_preview_area').find('img');
	window.img_cnt = imgs.length;
	window.img_load = 0;
	if (window.img_cnt == 0) {
		d_resize();
	} else {
		imgs.on('load', function() {
			window.img_load++;
			if (window.img_cnt == window.img_load) {
				d_resize();
			}
		});
	}
}

function d_resize()
{
	var content_area = $('.__wing_d_preview_area');
	var pos_s = content_area.offset().top;
	var pos_e = content_area.offset().top+content_area.height();
	var height = $('body').innerHeight();

	// 본문 외 영역 가리기
	$('.dimmed').remove();
	$('body').append('<div id="dimmed_top" class="dimmed"></div>');
	$('body').append('<div id="dimmed_bottom" class="dimmed"></div>');
	$('.dimmed').css({
		'z-index': 9999,
		'position': 'absolute',
		'left': 0,
		'width': '100%',
		'background-color': '#000',
		'opacity': '.3'
	});
	$('#dimmed_top').css({'top': '0', 'height': pos_s-15});
	$('#dimmed_bottom').css({'top': pos_e+15, 'height': $('body').prop('scrollHeight')-pos_e});
	$('.wing-detail-more-contents').height('auto');

	// 본문 위치로 스크롤
	$('html, body').animate({'scrollTop': content_area.offset().top-120}, {'queue':false, 'complete': function() {
		if (height != $('body').innerHeight()) { // 렌더링 속도에 따른 위치 정보 보정
			d_resize();
		}
	}});

	parent.removeLoading();
}

$(function() {
	d_previewReload();
});