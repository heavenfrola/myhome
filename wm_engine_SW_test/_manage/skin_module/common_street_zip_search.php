<?PHP

	$_replace_code[$_file_name]['form_start']="";
	$_replace_hangul[$_file_name]['form_start']="폼시작";
	$_code_comment[$_file_name]['form_start']="우편번호 검색 폼 시작 선언";
	$_auto_replace[$_file_name]['form_start']="Y";

	$_replace_code[$_file_name]['find_zip_url']="";
	$_replace_hangul[$_file_name]['find_zip_url']="우편번호찾기";
	$_code_comment[$_file_name]['find_zip_url']="우편번호 찾기 링크 주소 출력";
	$_auto_replace[$_file_name]['find_zip_url']="Y";

	$_replace_code[$_file_name]['find_street_zip_url']="";
	$_replace_hangul[$_file_name]['find_street_zip_url']="도로명우편번호찾기";
	$_code_comment[$_file_name]['find_street_zip_url']="도로명 주소 우편번호 찾기 링크 주소 출력";
	$_auto_replace[$_file_name]['find_street_zip_url']="Y";

	$_replace_code[$_file_name]['search_word']="";
	$_replace_hangul[$_file_name]['search_word']="검색어";
	$_code_comment[$_file_name]['search_word']="검색중인 단어 출력";
	$_auto_replace[$_file_name]['search_word']="Y";

	$_replace_code[$_file_name]['common_street_zipcode_list']="";
	$_replace_hangul[$_file_name]['common_street_zipcode_list']="도로명우편번호리스트";
	$_code_comment[$_file_name]['common_street_zipcode_list']="검색된 도로명 주소 우편번호 리스트";
	$_replace_datavals[$_file_name]['common_street_zipcode_list']="우편번호선택:select_url:우편번호 선택 스크립트 주소 출력;우편번호:zipcode;주소:address;배송불가사유:impossible_reason;";

	$_replace_code[$_file_name]['form_end']="";
	$_replace_hangul[$_file_name]['form_end']="폼끝";
	$_code_comment[$_file_name]['form_end']="우편번호 검색 폼 끝 선언";
	$_auto_replace[$_file_name]['form_end']="Y";

	$_replace_code[$_file_name]['juso_api_use']= $cfg['juso_api_use'] == "Y" ? 'TRUE' : '';
	$_replace_hangul[$_file_name]['juso_api_use']="주소API사용여부";
	$_code_comment[$_file_name]['juso_api_use']="도로명 주소 API 사용유무";
	$_auto_replace[$_file_name]['juso_api_use']="Y";

	$_replace_code[$_file_name]['juso_api_script']= "
<script type=\"text/javascript\">
function getGugunList(sido) {
	if(sido) {
		$.ajax({
			type: 'get',
			url:  root_url+'/common/zip_search.php?mode=getGugun&sido='+encodeURIComponent(sido),
			dataType : 'json',
			async : false,
			success: function(result) {
				if(result.result == true) {
					var gugun = \"\";
					$.each(result.data, function(i, val) {
						gugun += \"<option value='\"+result.data[i].gugun+\"'>\"+result.data[i].gugun+\"</option>\"
					});
					$('#gugun').html(gugun);
				} else {
					alert(result.message);
				}
			}
		});
	} else {
		$('#gugun').html(\"<option value=''>전체</option>\");
	}
}
</script>";
	$_replace_hangul[$_file_name]['juso_api_script']="시도구군스크립트";
	$_code_comment[$_file_name]['juso_api_script']="시도구군스크립트 선언";
	$_auto_replace[$_file_name]['juso_api_script']="Y";

?>