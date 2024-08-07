/* img hover */
/* example 
<img src="/_image/common/이미지.jpg" alt="" title="" onmouseover="imgOver(this)" onmouseout="imgOver(this,'out')"> 
*/
function imgOver(imgEl,opt) {
	if(imgEl.getAttribute('checked')) return;
	var src = imgEl.getAttribute('src');
	var ftype = src.substring(src.lastIndexOf('.'), src.length);
	if (opt == 'out') imgEl.src = imgEl.src.replace("_over"+ftype, ftype);
	else imgEl.src = imgEl.src.replace(ftype, "_over"+ftype);
}

/* 체크박스 전체선택 */
/* example 
<input type="checkbox" onclick="cartCheckAll(this.checked)">
*/
function cartCheckAll(checked) {
	$(':checkbox[name^="cno["], :checkbox[name^="wno["]').attrprop('checked', checked);
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

/* 스크롤 이동 */
$(window).scroll(function(){
	var y=$(this).scrollTop();
	if( y > 300 ){
		$('.btn_scroll').fadeIn();
	} else {
		$('.btn_scroll').fadeOut();
	}
	if ($('.gnb').hasClass('fixed')) {
		if( y > 230 ){
			$('.favorite .viewsub').fadeIn();
		} else {
			$('.favorite .viewsub').fadeOut();
		}
	}
});
function scrollup(){
	$('html, body').animate({scrollTop:0}, 'slow');
}
function scrolldown(){
	$('html, body').animate({scrollTop:$(document).height()}, 'slow');
}