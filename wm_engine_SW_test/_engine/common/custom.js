function ajaxPrd() {

	let param = {};

	param['obj_id'] = 'prd_basic';
	param['_tmp_file_name'] = 'shop/big_section.php'
	param['single_module'] = 'prd_basic'
	param['module_page'] = '1'
	param['striplayout'] = 'Y'
	param['cno1'] = 1009;

	$('#prd_basic').empty();
	$('.paging').remove();

	$.get(root_url+'/main/exec.php?exec_file=skin_module/skin_ajax.php', param, function(r) {
		$('#prd_basic').append(r.content);
		$('.prd_basic').after(r.pageRes);
	});
}

/*
===============================================================
- 사업자 등록번호 API 조회
skey : 사이트 키
num : 사업자등록번호
===============================================================
*/
function businessNumApi() {
	var biz_num = '';

	for(var i=0; i<$('[name = "biz_num[]"]').length; i++) {
		biz_num += $('[name = "biz_num[]"]')[i].value;
	}

	if(!biz_num) {
		window.alert(_lang_pack.member_input_biznum);
		return;
	}

	$.post( root_url+'/main/exec.php', {"exec_file":"ajax/custom_ajax.php", "exec":"biz_api", "biz_num":biz_num }, function(result) {
			window.alert(result);
		}
	);
}