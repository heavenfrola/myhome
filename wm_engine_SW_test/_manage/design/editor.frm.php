<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  페이지 편집
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";
	$_skin_name=editSkinName();

	$design_edit_key = addslashes($_GET['design_edit_key']);
	$design_edit_code = addslashes($_GET['design_edit_code']);

    // 퀵카트 장바구니 리스트 코드
    if (
        $design_edit_code == 'cart_list'
        && preg_match('/shop_cart_layer([0-9])/', $design_edit_key, $_layer_no) == true
    ) {
        $design_edit_code = 'cart_quick'.$_layer_no[1].'_list';
    }

	if(!$design_edit_key || !$design_edit_code) msg("필수값이 없습니다", "close");
	$design_edit_key=@str_replace(".tmp", ".php", $design_edit_key);

	$_edt_mode="module";
	$file_dir=$root_dir."/_skin/".$_skin_name."/MODULE/";
	$file_name=$design_edit_code.".".$_skin_ext['m'];

	$_edit_pg=$design_edit_key;
	include_once $engine_dir."/_manage/skin_module/_skin_module.php";
	$_edit_pg=$edit_pg=$file_name;

	$_pg_title=$_replace_hangul[$design_edit_key][$design_edit_code];

?>
<div class="box_title first">
	<h2><?=$_pg_title?> 편집</h2>
</div>
<div><?include $engine_dir."/_manage/design/editor.php";?></div>