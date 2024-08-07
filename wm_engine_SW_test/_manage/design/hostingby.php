<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  호스팅업체정보 게시
	' +----------------------------------------------------------------------------------------------+*/

?>
<div class="box_title first">
	<h2 class="title">호스팅업체정보 게시</h2>
</div>
<table class="tbl_col">
	<caption class="hidden">호스팅업체정보 게시</caption>
	<colgroup>
		<col style="width:250px">
		<col>
		<col style="width:150px">
		<col style="width:200px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">코드명</th>
			<th scope="col">로고</th>
			<th scope="col">사이즈</th>
			<th scope="col">코드</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th>호스팅사업자로고1</th>
			<td class="left"><img src="<?=$engine_url?>/_engine/common/hostingby/hostingby_01.gif"></td>
			<td>135x18</td>
			<td>{{$호스팅사업자로고1}}</td>
			</tr>
		<tr>
			<th>호스팅사업자로고2</th>
			<td class="left"><img src="<?=$engine_url?>/_engine/common/hostingby/hostingby_02.gif"></td>
			<td>135x18</td>
			<td>{{$호스팅사업자로고2}}</td>
		</tr>
		<tr>
			<th>호스팅사업자로고3</th>
			<td class="left"><img src="<?=$engine_url?>/_engine/common/hostingby/hostingby_03.gif"></td>
			<td>135x18</td>
			<td>{{$호스팅사업자로고3}}</td>
		</tr>
		<tr>
			<th>호스팅사업자로고4</th>
			<td class="left"><img src="<?=$engine_url?>/_engine/common/hostingby/hostingby_04.gif"></td>
			<td>80x32</td>
			<td>{{$호스팅사업자로고4}}</td>
		</tr>
		<tr>
			<th>호스팅사업자로고5</th>
			<td class="left"><img src="<?=$engine_url?>/_engine/common/hostingby/hostingby_05.gif"></td>
			<td>80x32</td>
			<td>{{$호스팅사업자로고5}}</td>
		</tr>
		<tr>
			<th>호스팅사업자로고6</th>
			<td class="left"><img src="<?=$engine_url?>/_engine/common/hostingby/hostingby_06.gif"></td>
			<td>80x32</td>
			<td>{{$호스팅사업자로고6}}</td>
		</tr>
		<tr>
			<th>호스팅사업자로고7</th>
			<td class="left"><img src="<?=$engine_url?>/_engine/common/hostingby/hostingby_07.gif"></td>
			<td>46x31</td>
			<td>{{$호스팅사업자로고7}}</td>
		</tr>
		<tr>
			<th>호스팅사업자로고8</th>
			<td class="left"><img src="<?=$engine_url?>/_engine/common/hostingby/hostingby_08.gif"></td>
			<td>78x81</td>
			<td>{{$호스팅사업자로고8}}</td>
		</tr>
		<tr>
			<th>호스팅사업자게시1</th>
			<td class="left">Hosting by WISA</td>
			<td>Text</td>
			<td>{{$호스팅사업자게시1}}</td>
		</tr>
		<tr>
			<th>호스팅사업자게시2</th>
			<td class="left">Hosting by (주)위사</td>
			<td>Text</td>
			<td>{{$호스팅사업자게시2}}</td>
		</tr>
		<tr>
			<th>호스팅사업자게시3</th>
			<td class="left">(주)위사</td>
			<td>Text</td>
			<td>{{$호스팅사업자게시3}}</td>
		</tr>
	</tbody>
</table>
<div class="box_bottom">
	<ul class="list_msg left">
		<li>2012년 8월 18일 시행된 <u>"전자상거래법 개정안" 제10조에 '호스팅사업자 정보노출'</u>에 따라 쇼핑몰 사이트 내에 호스팅 업체의 정보를 노출하셔야합니다.</li>
		<li>8가지의 기본 이미지 중 선택하여 코드를 복사하신 후 <a href="#" onclick="layoutEdit('%7B%7BB%7D%7D'); return false;" class="p_color2">레이아웃 하단</a>등 필요한 페이지에 삽입 해 주시기 바랍니다.</li>
	</ul>
</div>

<script type="text/javascript">
	function layoutEdit(part) {
		nurl='./pop.php?body=design@layout.frm&part='+part;
		window.open(nurl,'layout_edit','top=10,left=200,width=900,height=900,status=no,toolbars=no,scrollbars=yes');
	}
</script>