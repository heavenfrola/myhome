<div class="box_title first">
	<h2 class="title">광고스크립트 관리</h2>
</div>
<table class="tbl_col">
	<caption class="hidden">광고스크립트 관리</caption>
	<colgroup>
		<col>
		<col style="width:100px">
		<col style="width:60px">
		<col style="width:60px">
		<col style="width:60px">
		<col style="width:60px">
		<col style="width:60px">
		<col style="width:120px">
		<col style="width:140px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">스크립트명</th>
			<th scope="col">사용여부</th>
			<th scope="col">헤더</th>
			<th scope="col">공통상단</th>
			<th scope="col">공통하단</th>
			<th scope="col">가입완료</th>
			<th scope="col">주문완료</th>
			<th scope="col">등록일</th>
			<th scope="col">관리</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="left">adInsight 유입스크립트</td>
			<td><a href="#" class="p_color">사용함</a></td>
			<td>×</td>
			<td>×</td>
			<td>○</td>
			<td>○</td>
			<td>○</td>
			<td><?=date('Y-m-d')?></td>
			<td>
				<span class="box_btn_s blue"><input type="button" value="수정"></span>
				<span class="box_btn_s gray"><input type="button" value="삭제"></span>
			</td>
		</tr>
		<tr>
			<td class="left">링크프라이스</td>
			<td><a href="#" class="p_color">사용함</a></td>
			<td>○</td>
			<td>×</td>
			<td>×</td>
			<td>×</td>
			<td>○</td>
			<td><?=date('Y-m-d')?></td>
			<td>
				<span class="box_btn_s blue"><input type="button" value="수정"></span>
				<span class="box_btn_s  gray"><input type="button" value="삭제"></span>
			</td>
		</tr>
	</tbody>
</table>
<div class="box_bottom">
	<span class="box_btn blue"><input type="button" value="새 스크립트추가"></span>
</div>
<div class="box_title">
	<h2 class="title">광고스크립트 등록</h2>
</div>
<table class="tbl_row">
	<caption class="hidden">광고스크립트 등록</caption>
	<colgroup>
		<col style="width:15%">
	</colgroup>
	<tr>
		<th scope="row">스크립트명</th>
		<td><input type="text" name="name" value="" class="input" size="100"></td>
	</tr>
	<tr>
		<th scope="row">사용여부</th>
		<td>
			<label class="p_cursor"><input type="radio" name="use_script" checked> 사용함</label>
			<label class="p_cursor"><input type="radio" name="use_script"> 사용안함</label>
		</td>
	</tr>
	<tr>
		<th scope="row">가져오기</th>
		<td>
			<select>
				<option>다음클릭스 매출전송 스크립트</option>
			</select>
			<span class="box_btn_s"><input type="button" value="가져오기"></span>
			<div class="explain">목록에 있는 스크립트의 경우 수동 소스입력 없이 가져오기로 자동으로 스크립트를 설정하실수 있습니다.</div>
		</td>
	</tr>
	<tr>
		<th scope="row" rowspan="2">스크립트 소스 입력</th>
		<td>
			<div>
				<label class="p_cursor"><input type="radio" name=""> <strong class="p_color2">헤더</strong></label>
				<label class="p_cursor"><input type="radio" name=""> 공통상단</label>
				<label class="p_cursor"><input type="radio" name=""> 공통하단</label>
				<label class="p_cursor"><input type="radio" name=""> <strong class="p_color2">가입완료</strong></label>
				<label class="p_cursor"><input type="radio" name=""> <strong class="p_color2">상품상세</strong></label>
				<label class="p_cursor"><input type="radio" name=""> <strong class="p_color2">장바구니</strong></label>
				<label class="p_cursor"><input type="radio" name="" checked> <strong class="p_color2">주문완료</strong></label>
			</div>
			<h3>상단</h3>
			<textarea name="" cols="100" rows="5" class="txta"></textarea>

			<h3>상품별 반복실행</h3>
			<textarea name="" cols="100" rows="5" class="txta"><script type="text/javascript">ad_items += ',{i:"{{$시스템코드}}",	t:"{{$상품명}}"}';</script></textarea>

			<h3 class="section">하단</h3>
			<textarea name="" cols="100" rows="5" class="txta"><script type="text/javascript">
var wptg_tagscript_vars = wptg_tagscript_vars || [];
wptg_tagscript_vars.push(
(function() {
	return {ti:"21456",ty:"Cart",device:"web"
		,items:[
			ad_items
		]
	};
}));
</script>
<script type="text/javascript" async defer  src="//astg.widerplanet.com/js/wp_astg_3.0.js"></script></textarea>
		</td>
	</tr>
	<tr>
		<td>
			<p class="explain">광고스크립트에서 필요한 값을 다음 예약어로 자동 삽입되도록 할수 있습니다.</p>
			<ul class="list_msg">
				<li><strong>헤더/공통상단/하단</strong> : {{$성별남여}} {{$성별MF}} {{$성별12}} {{$아이디}} {{$나이}}</li>
				<li><strong>주문완료 페이지</strong> : {{$주문번호}} {{$상품금액}} {{$주문금액}} {{$결제금액}}</li>
				<li><strong>가입완료 페이지</strong> : {{$회원번호}} {{$회원아이디}} {{$나이}} {{$성별남여}} {{$성별MF}} {{$성별12}} </li>
			</ul>
		</td>
	</tr>
	<tr>
		<th scope="row">메모</th>
		<td>
			<textarea name="" cols="100" rows="5" class="txta">메이크샵에서 광고대행 진행. 14/10/23 위사로 이관</textarea>
		</td>
	</tr>
</table>