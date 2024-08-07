<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  추가 페이지 편집
	' +----------------------------------------------------------------------------------------------+*/

	$type = $_GET['type'];
	$content_add = $_GET['content_add'];
	$cont_no = addslashes($_GET['cont_no']);
	if($cont_no=="") {
		$cont_no=0;
	}

	if($content_add){ // 2007-11-13 - Han
		$cont_edit_file=$root_dir."/_config/content_add.php";
		if(is_file($cont_edit_file)) include_once $cont_edit_file;
		$_cont_page[$cont_no]=$cont_no;
		$_cont_page_name[$cont_no]=(!$_content_add_info[$cont_no]['name']) ? "기타페이지" : $_content_add_info[$cont_no]['name'];
		$ext=getExt($_content_add_info[$cont_no]['pg_name']);
	}
	if(!$ext) $ext="php";

	$cont_page=$_cont_page[$cont_no];
	if(!$cont_page) {
		msg("잘못된 접속입니다","back");
	}
	$cont_file1 = $root_dir.'/_template/content/'.$cont_page.'.'.$ext;
	$cont_file2 = $root_dir.'/_template/content/'.$cont_page.'_m.'.$ext;

    $write_error = false;
    if (is_writeable($root_dir.'/_template/content') == false) {
        $write_error = $root_dir.'/_template/content';
    } else {
        for ($i = 1; $i <= 2; $i++) {
            if(is_file(${'cont_file'.$i}) == true) {
                if(is_writable(${'cont_file'.$i}) == true) {
                    $file_str = file_get_contents(${'cont_file'.$i});
                    $file_str = str_replace("<?php ?>", "", $file_str);
                    $file_str = str_replace("<?php", "[WMCODE]", $file_str);
                    $file_str = str_replace("?>", "[/WMCODE]", $file_str);
                    ${'content'.$i} = $file_str;
                } else {
                    $write_error = ${'cont_file'.$i};
                }
            }
        }
    }

?>
<div class="box_title first">
    <h2 class="title"><?=$_cont_page_name[$cont_no]?></h2>
</div>
<div class="box_middle">
    <ul class="tab_pr" >
        <li class="on">
            <a onclick="tabover(0); return false;" class="box">PC 추가 페이지</a>
        </li>
        <li>
            <a onclick="tabover(1); return false;" class="box">모바일 추가 페이지</a>
        </li>
    </ul>
    <form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="checkFrm(this)">
        <input type="hidden" name="body" value="design@content.exe">
        <input type="hidden" name="cont_no" value="<?=$cont_no?>">
        <input type="hidden" name="content_add" value="<?=$content_add?>">
        <?php if ($write_error != false) {?>
        <div class='box_middle2'>
            <ul>
                <li><span class='p_color2'><?=basename($write_error)?></span> - 파일을 수정할 수 없습니다</li>
                <li>파일의 모드를 쓰기 가능하게 수정하셔야합니다</li>
            </ul>
        </div>
        <div class='box_bottom'>
            <span class='box_btn gray'><input type='button' onclick="goMywisa('?body=customer@list');" value='1:1 고객 센터 문의'></span>
        </div>
        <?php } else {?>
        <div class="box_middle2 board_content">
            <textarea id="content1" name="content1" class="txta" style="width:100%; height:400px;"><?=htmlspecialchars($content1)?></textarea>
        </div>
        <div class="box_middle2 board_content">
            <textarea id="content2" name="content2" class="txta" style="width:100%; height:400px;"><?=htmlspecialchars($content2)?></textarea>
        </div>
        <div class="box_bottom top_line">
            <span class="box_btn blue"><input type="submit" value="확인"></span>
            <span class="box_btn gray"><input type="button" value="보기" onclick="window.open('<?=$root_url?>/content/content.php?cont=<?=$cont_page?>')"></span>
            <?php if($content_add){ ?>
            <span class="box_btn gray"><input type="button" value="취소" onclick="location.href='./?body=<? if($_GET['type'] == 'mobile') { ?>wmb<? } else { ?>design<? } ?>@content_add&type=<?=$_GET['type']?>'"></span>
            <?php } ?>
        </div>
        <?php }?>
    </form>
</div>
<script type="text/javascript" src="<?=$engine_url?>/_engine/R2Na/R2Na.js"></script>
<script type="text/javascript">
	var editor1 = new R2Na('content1', {
		'editor_gr': 'content',
		'editor_code': 'content_<?=$cont_no?>'
	});
    editor1.initNeko('content_<?=$cont_no?>', 'content', 'img');

	var editor2 = new R2Na('content2', {
		'editor_gr': 'content',
		'editor_code': 'content_<?=$cont_no?>_m'
	});
    editor2.initNeko('content_<?=$cont_no?>_m', 'content', 'img');

	function checkFrm(f) {
        printLoading();

		oEditors.getById['content1'].exec("UPDATE_CONTENTS_FIELD", []);
		oEditors.getById['content2'].exec("UPDATE_CONTENTS_FIELD", []);
	}

    function tabover(no) {
        if(window.editorCheck) return false;

        $('.board_content').not(':eq('+no+')').hide();
        $('.board_content').eq(no).show();
        $('.tab_pr>li').not(':eq('+no+')').removeClass('on');
        $('.tab_pr>li').eq(no).addClass('on');
    }

    window.editorCheck = setInterval(function() {
        if(oEditors && oEditors.getById && oEditors.getById['content2']) {
            if(oEditors.getById['content2'].getEditingAreaHeight) {
                $('.board_content').eq(1).hide();
                clearInterval(window.editorCheck);
                window.editorCheck = null;
            }
        }
    }, 200);
</script>