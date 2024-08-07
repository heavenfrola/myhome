<?
	/* +----------------------------------------------------------------------------------------------+
	' |  사입처 일괄관리
	' +----------------------------------------------------------------------------------------------+*/
?>
<form method="post" enctype="multipart/form-data" action="" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="product@provider_in.exe">
	<div class="box_title first">
		<h2 class="title">일괄 사입처 엑셀파일 정보</h2>
	</div>
	<div class="box_middle left">
		<ul class="list_msg">
			<li>엑셀파일을 통해 사입처를 한번에 등록/수정 하실수 있습니다.</li>
			<li>첫번째 항목인 <u>시스템코드가 있을 경우 기존의 사입처를 수정</u>하며, <u>없을 경우 신규로 새로운 사입처가 추가</u>됩니다.</li>
			<li>시스템코드를 마음대로 바꾸실 경우 다른 사업처에 정보가 잘못 저장될 수 있습니다.</li>
			<li><span class="p_color2">'사입처명' 항목은 필수 항목</span>입니다. 입력되어 있지 않을 경우 처리하지 않고 무시됩니다.</li>
			<li>반드시 사입처 관리 메뉴에서 다운로드 한 양식을 이용하시거나 그와 동일한 순서양식으로 작성 해 주십시오.</li>
			<li>엑셀 저장시 반드시 <span class="p_color2">'csv(쉼표로 구분)'</span> 형식으로 저장 해 주십시오.</li>
            <li>‘은행명' 항목은 '은행코드' 항목에 따라 자동으로 변경됩니다. </li>
		</ul>
	</div>
	<table class="tbl_row">
		<caption>일괄 사입처 수정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">파일 업로드</th>
			<td>
				<input type="file" name="csv" class="input input_full"> <span class="box_btn_s blue"></span>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="일괄 사입처 수정파일 업로드"></span>
	</div>
</form>