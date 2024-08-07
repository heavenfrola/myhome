<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  사원등록 - 위사아이디 찾기
	' +----------------------------------------------------------------------------------------------+*/

	$search = $_GET['search'];
	if($search) {
		$search = addslashes(trim($search));
		$data = $wec->get(150, 'search='.$search,1);
		foreach($data as $key => $val) {
			if(!$val->name) continue;
			$name = strMask($val->name[0], 2, '**');
			$search_result .= "
			<tr>
				<td><a href='javascript:;' onclick='useThisID(this)'><strong>{$val->member_id[0]}</strong></a></td>
				<td>{$name}</td>
			</tr>
			";
		}
	}

	//if(!$search_result) $search_result = "<div class='pcenter'>등록할 사원의 위사 아이디를 검색하세요</div>";

?>
<style type="text/css" title="">
body {background:#fff;}
</style>
<div class="popupContent" style="width: 700px; min-height: 500px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">사원등록 - 아이디 찾기</div>
	</div>
	<form id="search" method="get" action="?" onsubmit="return wics(this)">
		<input type="hidden" name="body" value="<?=$_GET['body']?>">
		<table class="tbl_row">
			<caption class="hidden">아이디 검색</caption>
			<colgroup>
				<col style="width:15%">
			</colgroup>
			<tr>
				<th scope="row">아이디 검색</th>
				<td>
					<input type="text" name="search" class="input" size="30" value="<?=inputText($search)?>" placeholder="위사아이디, 이메일, 휴대폰번호">
					<span class="box_btn_s blue"><input type="submit" value="검색"></span>
					<ul class="list_msg">
						<li>아이디/이메일/휴대폰 번호의 전체 내용을 입력해주세요.</li>
						<li>전화번호는 - 를 제외하고 입력해주세요.</li>
					</ul>
				</td>
			</tr>
		</table>
	</form>

	<table class="tbl_col">
		<caption class="hidden">검색 결과</caption>
		<thead>
			<tr>
				<th scope="col">아이디</th>
				<th scope="col">이름</th>
			</tr>
		</thead>
		<tbody>
			<?=$search_result?>
            <?php if (is_null($search_result) == true) { ?>
            <tr class="none">
                <td colspan="2"><p class="nodata">검색 결과가 없습니다. 정확한 아이디, 이메일, 휴대폰번호를 입력해주세요.</p></td>
            </tr>
            <?php } ?>
		</tbody>
	</table>
</div>
<script type="text/javascript">
	window.onload = function() {
		selfResize();
	}
	function wics(f) {
		if(f.search.value.length < 4) {
			window.alert('검색어를 4자이상 입력해 주십시오');
			return false;
		}
	}
	function useThisID(obj) {
		var member_id = obj.innerText;
		var	f = opener.document.getElementById('staffFrm');
		if(f) {
			f.admin_id.value = member_id;
			self.close();
		} else {
			window.alert('사원등록폼이 없습니다\n\n부모창을 닫았거나 다른페이지로 이동되어,\t\n아이디 선택을 할수 없습니다');
		}

	}
</script>
<?close(1)?>