<form method='post' enctype='multipart/form-data' action='./index.php' target='hidden<?=$now?>' onsubmit="return upXls();">
	<input type="hidden" name='exec' value='upload'>

	<table class="tbl_row">
		<caption>엑셀 일괄 업로드</caption>
		<colgroup>
			<col style="width: 150px;">
			<col>
		</colgroup>
		<tr>
			<th>업로드 파일 종류</th>
			<td>
				<label><input type="radio" name="body" value="product@product_upload.exe" checked> 상품 엑셀</label>
				<label><input type="radio" name="body" value="product@product_upload_field.exe"> 상품정보고시 엑셀</label>
			</td>
		</tr>
		<tr>
			<th>xls 파일</th>
			<td><input type="file" name="csv" class="input" size="20"></td>
		</tr>
		<tr>
			<td colspan="2">
				<ul class="list_msg">
					<li><a href="?body=product@product_download">상품관리 > 상품일괄등록 > 엑셀양식 다운로드</a> 메뉴에서 파일을 다운로드 받아 수정하신 후 <span class="p_color">'Excel 97 - 2003 통합 문서'</span>로 다른이름으로 저장한 후 업로드 해주세요.</li>
					<li>고유번호(시스템ID)가 있을 경우 수정, 없을 경우 신규등록으로 처리 됩니다.</li>
					<li>신규등록시 상품명 정보가 없으면 등록되지 않습니다.</li>
				</ul>

				<h3 class="p_color3">필드별 입력 주의사항</h3>
				<ul class="list_msg">
					<li>사입처명 : 사입처관리에 등록된 사입처명으로 정확히 입력해 주세요.</li>
					<li>상품상태 : 정상 / 품절 / 숨김 중 하나를 입력하세요.</li>
					<li>재고방식 : ERP / 무제한 중 하나를 입력하세요.</li>
					<li>SKU한정여부(L/N) : 재고방식이 ERP일 경우 한정/무제한/강제품절 여부를 L, N, Y 중 한가지 값으로 입력해 주세요.</li>
					<li>
						대분류, <?=$cfg['xbig_name']?>, <?=$cfg['ybig_name']?> : 실제카테고리 이름을 정확히 입력해 주세요. 카테고리 코드를 사용중일경우 코드도 입력가능합니다.<br>
					</li>
					<li>무이자/이벤트/회원혜택/무료배송/네이버페이 : Y 또는 N 으로 입력해 주세요. 빈값일 경우 N 으로 인정됩니다.</li>
					<li>이미지경로 : 상품이미지 업로드시 반드시 입력해 주세요. 없을 경우 모든 이미지 관련 작업은 처리되지 않습니다.</li>
					<li>
						원본이미지 : 대/중/소 이미지를 자동생성할때 입력합니다.<br>
						웹사이트 서버의 _data/auto_thumb 경로에 이미지를 업로드 해두신 후 해당 파일명만 원본이미지란에 입력해주세요.<br>
						대미미지~소이미지의 모든 정보는 무시됩니다.<br>
						신규등록일 경우 이미지경로도 필수값이 아니며 비워두셔도 됩니다.<br>
						<span class="p_color5">각각의 상품은 다른 이미지 파일명으로 등록하셔야 합니다. 두 상품이 상품 이미지가 같더라도 반드시 다른 이름으로 별도로 이미지를 업로드해야 합니다.</span>
					</li>
					<li>대이미지/중이미지/소이미지 : 미리 FTP로 업로드 된 이미지의 파일명만 입력해주세요. 가로, 세로 사이즈는 필수적으로 입력하셔야 합니다.</li>
					<li>
						원본이미지 : FTP로는 대이미지만 미리 업로드 하고 중이미지, 소이미지를 자동생성하실경우 대이미지의 이름을 입력해주세요.<br>
						대량처리할 경우 업로드 속도가 느려지거나 중간에 작업이 중단될수 있습니다.
					</li>
					<li>부가이미지 : 부가이미지를1~5 에 부가이미지의 경로를 입력해 주세요. (도메인을 제외한 경로 및 파일명 입력 _data/product/abc.png)</li>
					<li>추가이미지 : 대/중/소 이외의 추가이미지를 사용시 입력해 주세요. 2개 이상의 추가 이미지를 사용하실 경우 <strong>^</strong>기호로 로 구분해 주세요.</li>
					<li>
						상품옵션 1~5 : 다음과 같은 양식을 지켜 입력해 주세요. 첫번째에 옵션세트명을 입력해 주셔야 합니다.<br>
						두번째 항목에는 <span class="p_color5">콤보박스, 라디오버튼, 라디오버튼+줄바꿈, 컬러칩, 텍스트칩 중 한가지</span>로 옵션 종류를 선택합니다.<br>
						세번째부터 옵션 항목을 차례로 입력해주세요.<br>
						예) 색상::콤보박스::Red::Blue::Green::White<br><br>
						상품옵션별 추가 금액이 있을 경우 $$ 로 구분해 주세요.<br>
						예) 색상::콤보박스::Red::Blue$$1000::Green::White$$2500<br><br>
						재고방식이 ERP일 경우 입력한 옵션값들을 토대로 임시로 SKU 코드가 생성되며, 재고 일괄 업로드 기능을 이용해서 SKU 코드 및 재고를 수정하실수 있습니다.
					</li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" id="stpBtn" value="확인"></span>
	</div>
</form>
<script type="text/javascript">
	function upXls() {
		if(confirm('선택한 엑셀을 반영하시겠습니까?')) {
			$('#stpBtn').hide();
			return true;
		}
		return false;
	}
</script>