<?PHP

/**
 * 게시판 스킨 편집
 **/

include_once __ENGINE_DIR__.'/_engine/include/img_ftp.lib.php';
$page_mode = 'board';

if (isset($_GET['filename']) == true) {
    $ext = getExt($_GET['filename']);
    $syntax = ($ext == 'css') ? 'css' : 'html';
}

?>
<style>
.context > li { padding: 5px 8px; border-bottom: solid 1px #f8f8f8; cursor: pointer; }
.context > li:hover { background-color: #26ace2; }
.context > li .description { color: #bfbfbf; }
</style>

<div class="box_title first">
	<h2 class="title">게시판 스킨 편집</h2>
</div>
<div class="box_middle left">
	<p class="p_color2">잘못된 코드 삽입으로 발생한 문제에 대해서는 책임지지 않습니다.</p>
</div>
<div id="controlTab" class="none_margin">
	<ul class="tabs square">
		<li onclick="location.href='./?body=design@board'" class="selected">스킨 목록</li>
		<li onclick="location.href='./?body=design@board_skin';">스킨 관리</li>
	</ul>
</div>
<form name="editFrm" action="<?=$PHP_SELF?>" method="post" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="design@template.exe">
	<input type="hidden" name="exec" value="modify">
	<div class="box_bottom left">
        <ul id="edit_list" class="context">
            <li onclick="location.href='{{ link }}'">
                <img src="<?=$engine_url?>/_manage/image/icon/{{ icon }}"> {{ name }}
                <span>{{ use }}</span>
                <span class="description">{{ title }}</span>
            </li>
        </ul>
	</div>
</form>

<?php if ($_GET['skinname'] && $_GET['filename']) { ?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/edit_area/edit_area_full.js"></script>
<script>
$.post('./index.php', {'body': 'design@template.exe', 'page_mode': 'board', 'view_mode': 'dir', 'skinname': '<?=$_GET['skinname']?>', 'filename': '<?=$_GET['filename']?>'}, function(r) {
    $('.context').html(r);

    editAreaLoader.init({
        id: "edt_content"
        ,start_highlight: true
        ,allow_resize: "both"
        ,allow_toggle: false
        ,word_wrap: true
        ,replace_tab_by_spaces: false
        ,language: "kr"
        ,syntax: "<?=$syntax?>"
        ,font_family: 'dotum'
    })
});
</script>
<?php } else { ?>
<script>
$(window).on('load hashchange', function() {
    scanskin();
});

let loop = '';
function scanskin()
{
    if (loop == '') {
        loop = $('.context').html();
    }
    let folder = location.href.split('#');
    if (!folder[1]) folder[1] = '';

    $('.context').html('');
    $.post('./index.php', {'body': 'design@board_skin_explorer.exe', 'folder': folder[1]}, function(r) {
        r.forEach(function(data) {
            let tmp = loop;
            for (key in data) {
                if (!data[key]) data[key] = '';
                tmp = tmp.replace('{{ '+key+' }}', data[key]);
            };
            $('.context').append(tmp);
        });
    });
}
</script>
<?php } ?>