<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 등록
	' +----------------------------------------------------------------------------------------------+*/

	// 타입 별 게시판 스킨 구하기
	$_board_type=array("basic"=>"일반", "gallery"=>"갤러리", "blog"=>"블로그", "bank"=>"입금확인");
	$board_type=$_board_type[$_GET[board_type]] ? $_GET[board_type] : "basic";

	// 보유스킨
	$_skin_type=array();
	$_bsrc=$root_dir."/board/_skin";
	$odir=opendir($_bsrc);
	while($arr=readdir($odir)){
		if(!is_dir($_bsrc."/".$arr) || $arr == "." || $arr == "..") continue;
		$_type="";
		foreach($_board_type as $key=>$val){
			if(strchr($arr, $key)){
				$_type=$key;
				break;
			}
		}
		$_type=$_type ? $_type : "basic";
		$_skin_type[$_type][]=$arr;
	}

	$no = numberOnly($_GET['no']);
	if($no){
		$data=get_info("mari_config", "no", $no); // 게시판 정보
		if(!$data[no]) msg("해당 게시판이 더 이상 존재하지 않습니다", "back");
	}

	$group=getGroupName();
	$group[10]="비회원(누구나)";

	$_configFields['use_editor'] = array(1 => '텍스트', 2 => 'HTML', 3 => '에디터 사용');
	$_configFields['use_sort'] = array('N' => '기본', 'Y' => '작성일순');
	if(!$data['use_editor']) $data['use_editor'] = 3;
	function printSelect($fd, $type=1, $value = null) {
		global $data,$group;
		$ii=10;
		$w=($type == 1) ? 150 : 100;
		$str="<select name=\"".$fd."\" style=\"font-size:9pt; width:".$w."px\">\n";

		switch($type){
			case 1 :
			while($ii>0) {

				if($group[$ii]) {
					$sel=checked($data[$fd],$ii,1);
					$str.="<option value=\"$ii\" $sel>($ii)".$group[$ii]."</option>\n";
				}

				$ii--;
			}
			break;
			case 2 :
			if(!$data[$fd]) $data[$fd]="N";

			$sel=checked($data[$fd],"Y",1);
			$str.="<option value=\"Y\" $sel>사용</option>\n";

			$sel=checked($data[$fd],"N",1);
			$str.="<option value=\"N\" $sel>사용안함</option>\n";
			break;
			case 3 :
				$field = $GLOBALS['_configFields'][$fd];
				if(!is_array($field)) return;
				foreach($field as $key => $val) {
					$sel = $key == $value ? 'selected' : '';
					$str .= "<option value='$key' $sel>$val</option>\n";
				}
			break;
		}

		$str.="</select>";
		return $str;
	}

	if(!$data['date_type_list']) $data['date_type_list'] = "Y@-@m@-@d";
	if(!$data['date_type_view']) $data['date_type_view'] = "Y@-@m@-@d@ @H@:@i@:@s";
	if(!$data['date_type_user']) $data['date_type_user'] = "Y@-@m@-@d";
	$date_type_list = explode('@', $data['date_type_list']);
	$date_type_view = explode('@', $data['date_type_view']);
	$date_type_user = explode('@', $data['date_type_user']);
	if(!$data['load_url']) $data['load_url'] = 1;
    if (is_null($data['hit_type']) == true) $data['hit_type'] = '2';

?>
<form name="listFrm" method="post" action="<?=$PHP_SELF?>" target="hidden<?=$now?>" onsubmit="return boardChk(this);">
	<input type="hidden" name="body" value="board@board_edit.exe">
	<input type="hidden" name="no" value="<?=$no?>">
	<input type="hidden" name="new_edit" value="1">
	<div class="box_title first">
		<h2 class="title">게시판 종류 선택</h2>
	</div>
	<div class="box_middle">
		<ul class="list_msg left">
			<li>신규글,댓글 제한은 게시판 관리자를 제외한 회원에게 적용되며 <u>0 또는 미지정시 작동하지 않습니다</u>.</li>
			<li>신규글,댓글 제한은 삭제한 글을 모두 포함하므로, 지웠다 쓰는 편법을 방지합니다.</li>
		</ul>
	</div>
	<table class="tbl_row">
		<caption class="hidden">게시판 종류 선택</caption>
		<colgroup>
			<col style="width:15%;">
			<col style="width:85%;">
		<colgroup>
		<tr>
			<th scope="row">게시판 종류</th>
			<td>
				<select name="board_type" onchange="location.href = './?body=<?=$body?>&<?=$no ? "no=$no&chg=1&" : "";?>board_type='+this.value;">
					<?foreach($_board_type as $key=>$val) {?>
					<option value="<?=$key?>" <?=checked($board_type, $key, 1)?>><?=$val?> 게시판</option>
					<?}?>
				</select>
			</td>
		</tr>
	</table>
	<div class="box_title">
		<h2 class="title">게시판 디자인 선택</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">게시판 디자인 선택</caption>
		<colgroup>
			<col style="width:15%;">
			<col style="width:85%;">
		<colgroup>
		<tr>
			<td colspan="2" class="p_color2">스킨 선택</td>
		</tr>
		<tr>
			<th scope="row">스킨 설정</th>
			<td>
				<ul class="list_quarter">
				<?
					if(is_array($_skin_type[$board_type])) {
						$_default_skin=(!$chg) ? $data[skin] : "";
						foreach($_skin_type[$board_type] as $key=>$val){
							$_default_skin=$_default_skin ? $_default_skin : $val;
							?>
							<li><label class="p_cursor"><input type="radio" name="skin" value="<?=$val?>" <?=checked($_default_skin, $val)?>><?=$val?></label></li>
							<?
						}
					} else {
						echo '등록된 스킨이 없습니다.';
					}
				?>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">모바일 스킨</th>
			<td>
				<ul class="list_quarter">
				<?
					if(is_array($_skin_type[$board_type])) {
						$_default_skin=(!$chg) ? $data['mskin'] : "";
						foreach($_skin_type[$board_type] as $key=>$val){
							$_default_skin=$_default_skin ? $_default_skin : $val;
							?>
							<li><label class="p_cursor"><input type="radio" name="mskin" value="<?=$val?>" <?=checked($_default_skin, $val)?>><?=$val?></label></li>
							<?
						}
					} else {
						echo '등록된 스킨이 없습니다.';
					}
				?>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_title">
		<h2 class="title">게시판 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">게시판 설정</caption>
		<colgroup>
			<col style="width:15%;">
			<col style="width:85%;">
		<colgroup>
		<tr>
			<td colspan="2" class="p_color2">기본 설정</td>
		</tr>
		<tr>
			<th scope="row">게시판명</th>
			<td>
				<input type="text" name="title" size="40" maxlength="25" value="<?=$data[title]?>" class="input">
			</td>
		</tr>
		<tr>
			<td colspan="2" class="p_color2">디자인 설정</td>
		</tr>
		<tr>
			<th scope="row">제목 줄임</th>
			<td>
				<input type="text" name="cut_title" size="4" maxlength="4" value="<?=$data[cut_title] ? $data[cut_title] : "100"?>" class="input"> byte
			</td>
		</tr>
		<tr>
			<th scope="row">한 페이지 글수</th>
			<td>
				<input type="text" name="page_row" size="4" maxlength="4" value="<?=$data[page_row] ? $data[page_row] : "10"?>" class="input">
			</td>
		</tr>
		<tr>
			<th scope="row">페이지 블럭</th>
			<td>
				<input type="text" name="page_block" size="4" maxlength="4" value="<?=$data[page_block] ? $data[page_block] : "10"?>" class="input">
			</td>
		</tr>
		<tr>
			<th scope="row">시작페이지</th>
			<td>
					<select name="start_mode">
					<option value="1" <?=checked($data[start_mode],1,1)?>>리스트</option>
					<option value="2" <?=checked($data[start_mode],2,1)?>>글 본문</option>
					<option value="3" <?=checked($data[start_mode],3,1)?>>글 쓰기</option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row">본문,목록 표현 방식</th>
			<td>
				<select name="list_mode">
					<option value="2" <?=checked($data['list_mode'],2,1)?>>본문만</option>
					<option value="1" <?=checked($data['list_mode'],1,1)?>>본문 아래 목록</option>
					<option value="3" <?=checked($data['list_mode'],3,1)?>>본문 아래 답글</option>
				</select>
				<select name="auth_member">
					<option value="1" <?=checked($data[auth_member],1,1)?>>전체 게시글</option>
					<option value="2" <?=checked($data[auth_member],2,1)?>>본인 작성 게시글</option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row">댓글 정렬</th>
			<td>
				<input type="radio" id="r1" name="board_comment_sort" value="1" <?=checked($data['board_comment_sort'],1).checked($data['board_comment_sort'],false)?>> <label for="r1" class="p_cursor">최신글이 아래로</label><br>
				<input type="radio" id="r2" name="board_comment_sort" value="2" <?=checked($data['board_comment_sort'],2)?>> <label for="r2" class="p_cursor">최신글이 위로</label>
			</td>
		</tr>
		<?if($data[no]) {?>
		<tr>
			<th scope="row">상단 디자인</th>
			<td>
				<span class="box_btn_s"><input type="button" value="편집하기" onClick="boardTop(<?=$data[no]?>);"></span>
			</td>
		</tr>
		<?} if($board_type == "gallery") {?>
		<tr>
			<th scope="row">갤러리형식 한줄 글수</th>
			<td>
				<input type="text" name="gallery_cols" size="4" maxlength="4" value="<?=numberOnly($data['gallery_cols'])?>" class="input">
			</td>
		</tr>
		<?}?>
		<tr>
			<td colspan="2" class="p_color2">권한 설정</td>
		</tr>
		<tr>
			<th scope="row">목록 보기</th>
			<td>
				<?=printSelect("auth_list")?>
			</td>
		</tr>
		<tr>
			<th scope="row">글 보기</th>
			<td>
				<?=printSelect("auth_view")?>
				<?if ($board_type == "blog"){?>
				<span class="explain">게시판 종류가 <strong>블로그 게시판</strong>일는 글보기가 사용되지 않습니다.</span>
				<?}?>
			</td>
		</tr>
		<tr>
			<th scope="row">글 쓰기</th>
			<td>
				<?=printSelect("auth_write")?>
			</td>
		</tr>
		<tr>
			<th scope="row">답글 쓰기</th>
			<td>
				<?=printSelect("auth_reply")?>
			</td>
		</tr>
		<tr>
			<th scope="row">댓글 쓰기</th>
			<td>
				<?=printSelect("auth_comment")?>
			</td>
		</tr>
		<tr>
			<th scope="row">파일 업로드</th>
			<td>
				<?=printSelect("auth_upload")?>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="p_color2">사용 여부 설정</td>
		</tr>
		<tr>
			<th scope="row">글수정</th>
			<td>
				<?=printSelect("use_edit", 2)?>
			</td>
		</tr>
		<tr>
			<th scope="row">글삭제</th>
			<td>
				<?=printSelect("use_del", 2)?>
			</td>
		</tr>
		<tr>
			<th scope="row">글보기</th>
			<td>
				<?=printSelect("use_view", 2)?>
				<?if($board_type == "blog"){?>
				<span class="explain">게시판 종류가 <strong>블로그 게시판</strong>일는 글보기가 사용되지 않습니다.</span>
				<?}?>
			</td>
		</tr>
		<tr>
			<th scope="row">답글</th>
			<td>
				<?=printSelect("use_reply", 2)?>
				<span class="explain">정렬방법이 기본일때만 사용하실수 있습니다.</span>
			</td>
		</tr>
		<tr>
			<th scope="row">댓글</th>
			<td>
				<?=printSelect("use_comment", 2)?>
			</td>
		</tr>
		<tr>
			<th scope="row">분류</th>
			<td>
				<?=printSelect("use_cate", 2)?>
				<? if($data[no] && $data[use_cate] == "Y"){?>
				<span class="box_btn_s"><input type="button" value="분류 설정" onClick="wisaOpen('./pop.php?body=board@board_cate&no=<?=$data[no]?>', 'boardCate','Y',500,400);"></span>
				<?}?>
			</td>
		</tr>
		<tr>
			<th scope="row">추가항목</th>
			<td>
				<span class="box_btn_s"><input type="button" value="추가항목 설정" onClick="wisaOpen('./pop.php?body=board@board_temp&no=<?=$data[no]?>', 'boardtemp','Y',580,400);"></span>
			</td>
		</tr>
		<tr>
			<th scope="row">에디터</th>
			<td>
				<?=printSelect("use_editor", 3, $data['use_editor'])?>
			</td>
		</tr>
		<tr>
			<th scope="row">정렬방법</th>
			<td>
				<?=printSelect("use_sort", 3, $data['use_sort'])?>
				<ul class="list_msg">
					<li>정렬순서가 기본이 아닐 경우 답글 기능을 사용할 수 없습니다.</li>
					<li>작성일순 정렬 사용시 관리자에서 게시물별 작성일시를 수정할 수 있습니다.</li>
					<li>지정된 정렬순서는 관리자에서 해당 게시판만 검색했을때 또는 쇼핑몰 화면에서 반영됩니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">신규게시글 작성 통보</th>
			<td>
				<div>
					* <span class="desc4">문자알림 :</span>
					<?=printSelect("use_scallback", 2)?>
					<span class="box_btn_s"><a href="?body=config@sms_config&sadmin=Y" target="_blank">설정</a></span>
				</div>
				<div>
					* <span class="desc4">이메일알림 :</span>
					<?=printSelect("use_mcallback", 2)?>
					<span class="box_btn_s"><a href="?body=member@email_config" target="_blank">설정</a></span>
				</div>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="p_color2">기타 설정</td>
		</tr>
		<tr>
			<th scope="row">게시물 작성 후 이동페이지</th>
			<td>
				<label class="p_cursor"><input type="radio" name="load_url" value="1" <?=checked($data['load_url'],"1")?>>본문으로 이동</label><br>
				<label class="p_cursor"><input type="radio" name="load_url" value="2" <?=checked($data['load_url'],"2")?>>리스트로 이동</label><br>
				<label class="p_cursor"><input type="radio" name="load_url" value="3" <?=checked($data['load_url'],"3")?>>주소 지정</label>
				<input type="text" name="loading_url" size="20" value="<?=$data['loading_url']?>" class="input"> <span class="explain"><br>
			</td>
		</tr>
		<?
			for($i = 0; $i <= 2; $i++) {
			switch($i) {
				case '0' :
					$_title = '목록 날짜형식';
					$_type = 'list';
					$_date_type_array = $date_type_list;
				break;
				case '1' :
					$_title = '보기 날짜형식';
					$_type = 'view';
					$_date_type_array = $date_type_view;
				break;
				case '2' :
					$_title = '사용자코드 날짜형식';
					$_type = 'user';
					$_date_type_array = $date_type_user;
				break;
			}
		?>
		<tr>
			<th scope="row"><?=$_title?></th>
			<td>
				현재 설정 : <?=parseDateType($data['date_type_'.$_type])?>
				<input type="hidden" id="date_type_<?=$i?>" name="date_type_<?=$_type?>" value="<?=$data['date_type_'.$_type]?>">
				<div class="add_fld">
					<div class="fld_list">
						<select id="date_item_<?=$i?>" class="select_n" size="10">
							<?foreach($date_type_items as $key => $val) {?>
							<option value="<?=$key?>"><?=$val?></option>
							<?}?>
						</select>
					</div>
					<div class="add small">
						<span class="box_btn_s blue"><input type="button" value="추가 ▶" onclick="select<?=$i?>.addFromSelect(item<?=$i?>, true);"></span><br><br>
						<span class="box_btn_s gray"><input type="button" value="제거 ◀" onclick="select<?=$i?>.remove()"></span>
					</div>
					<div class="add_list">
						<select id="date_select_<?=$i?>" class="select_n" size="10">
							<?foreach($_date_type_array as $val) {?>
							<option value="<?=$val?>"><?=$date_type_items[$val]?></option>
							<?}?>
						</select>
						<span class="box_btn_s blue"><input type="button" value="▲" onclick="select<?=$i?>.move(-1);"></span>
						<span class="box_btn_s blue"><input type="button" value="▼" onclick="select<?=$i?>.move(1);"></span>
					</div>
				</div>
			</td>
		</tr>
		<?}?>
		<tr>
			<th scope="row">조회수 증가 형태</th>
			<td>
				<select name="hit_type">
					<option value="1" <?=checked($data[hit_type],1,1)?>>조회마다</option>
					<option value="2" <?=checked($data[hit_type],2,1)?>>중복방지</option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row">사용 가능 태그</th>
			<td>
				<input type="text" name="tag" maxlength="100" value="<?=$data[tag] ? $data[tag] : "a,img,embed,font,b,div,center,p,br,strong";?>" size="70" class="input">
			</td>
		</tr>
		<tr>
			<th scope="row">글제한 (1인1일)</th>
			<td>
				<input type="text" name="day_write" maxlength="4" value="<?=$data[day_write]?>" size="4" class="input">
			</td>
		</tr>
		<tr>
			<th scope="row">댓글제한 (1인1일)</th>
			<td>
				<input type="text" name="day_comment" maxlength="4" value="<?=$data[day_comment]?>" size="4" class="input">
			</td>
		</tr>
		<tr>
			<th scope="row">업로드 가능 확장자</th>
			<td>
				<input type="text" name="upfile_ext" maxlength="50" value="<?=$data[upfile_ext] ? $data[upfile_ext] : "jpg|gif|bmp|png"?>" size="20" class="input"> <span class="explain">('|' 로 구분)</span>
			</td>
		</tr>
		<tr>
			<th scope="row">업로드 크기 (KB)</th>
			<td>
				<input type="text" name="upfile_size" maxlength="10" value="<?=$data[upfile_size] ? $data[upfile_size] : 500;?>" size="5" class="input"> KB
			</td>
		</tr>
		<tr>
			<th scope="row" rowspan="2">제목입력 제한</th>
			<td>
				<label class="p_cursor"><input type="radio" name="use_fsubject" value="N" checked> 사용안함</label>
				<label class="p_cursor"><input type="radio" name="use_fsubject" value="Y" <?=checked($data['use_fsubject'], 'Y')?>> 사용함</label>
			</td>
		</tr>
		<tr>
			<td>
				<textarea name="fsubject" class="txta" rows="5" cols="80"><?=stripslashes($data['fsubject'])?></textarea>
				<ul class="list_msg">
					<li>게시물 제목을 입력한 항목 중에서만 선택할 수 있습니다.</li>
					<li>제목은 1개이상 입력할 수 있으며, 각 제목 입력은 엔터로 구분해 주세요.</li>
					<li>설정한 내용을 적용 받으려면 <a href="?body=design@board">디자인관리>HTML 편집>게시판 스킨 편집</a> 메뉴에서 '글 입력/수정 폼'의 글제목 입력 input 태그를 <span class="p_color2">{{$제한제목목록}}</span> 으로 대체해 주세요.</li>
					<li><span class="p_color2">{{$제한제목목록}}</span> 모듈에 제거한 input 태그를 그대로 붙여넣으시면 사용하지 않거나 '게시판 관리자' 로그인 시 수동으로 제목을 입력하실 수 있습니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">자동 비밀글</th>
			<td>
				<label class="p_cursor"><input type="checkbox" name="auto_secret" value="Y" <?=checked($data['auto_secret'], 'Y')?>> 비밀글 전용 게시판으로 사용</label>
				<ul class="list_msg">
					<li>게시물작성시 무조건 비밀글로 저장됩니다.</li>
					<li>게시판 스킨(글 입력/수정 폼) 의 비밀글 체크부분을 {{$비밀글필드숨김시작}} 과 {{$비밀글필드숨김끝}}으로 감싸주시면 본설정에 따라 비밀글 선택버튼이 자동으로 숨김됩니다.</li>
					<li>본설정은 <u>게시판관리자에게는 적용되지 않습니다.</u> 게시판관리자에 접속했을때는 비밀글 설정 체크박스가 출력됩니다.</li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="설정완료"></span>
		<?if($listURL) {?>
		<span class="box_btn gray"><input type="button" value="취소" onclick="location.href='<?=$listURL?>';"></span>
		<?}?>
	</div>
</form>

<script type="text/javascript" src="<?=$engine_url?>/_engine/common/R2Select.js"></script>
<script type="text/javascript">
	function boardChk(f){
		if(!checkBlank(f.title, '게시판명을 입력해주세요.')) return false;
		if(!checkBlank(f.cut_title, '제목줄임 글자수를 입력해주세요.')) return false;
		if(!checkBlank(f.page_row, '한 페이지 글수를 입력해주세요.')) return false;
		if(!checkBlank(f.page_block, '페이지 블럭을 입력해주세요.')) return false;
		if(!checkBlank(f.tag, '사용 가능 태그를 입력해주세요.')) return false;
		if(!checkBlank(f.upfile_ext, '업로드 가능 확장자를 입력해주세요.')) return false;
		if(!checkBlank(f.upfile_size, '업로드 크기를 입력해주세요.')) return false;

		for(var i = 0; i <= 2; i++) {
			var temp = '';
			var sel = document.getElementById('date_select_'+i);
			for(var x = 0; x < sel.options.length; x++) {
				if(temp) temp += '@';
				temp += sel.options[x].value;
			}
			document.getElementById('date_type_'+i).value = temp;
		}
	}

	function boardTop(n){
		nurl='./pop.php?body=board@board_top.frm&no='+n;
		window.open(nurl,'board_top','top=10,left=10,width=950,status=no,toolbars=no,scrollbars=yes');
	}

	var select0 = new R2Select('date_select_0');
	var select1 = new R2Select('date_select_1');
	var select2 = new R2Select('date_select_2');
	var item0 = new R2Select('date_item_0');
	var item1 = new R2Select('date_item_1');
	var item2 = new R2Select('date_item_2');

	function ifrmResize(frm) {
		var win = $(frm.contentWindow.document.body);
		win.css('margin','0');
		win.css('overflow', 'hidden');

		$(frm).css({'height':win.prop('scrollHeight')});
	}

	var o_use_sort = $(':input[name=use_sort]');
	var useSort = function() {
		if(o_use_sort.val() == 'Y') {
			$(':input[name=use_reply]').val('N').prop('disabled', true).css('background', '#eee');
		} else {
			$(':input[name=use_reply]').prop('disabled', false).css('background', '');
		}
	}
	o_use_sort.change(useSort);
	$(function() {
		useSort();
	});
</script>