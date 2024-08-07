<form name="mariFrm" method="get" action="?" style="margin:0px">
<?=$hidden_db?>
<input type="hidden" name="no" value="<?=numberOnly($_GET['no'])?>">
<input type="hidden" name="mari_mode" value="">
<input type="hidden" name="cate" value="<?=strip_tags($cate)?>">
<input type="hidden" name="page" value="<?=numberOnly($page)?>">
<input type="hidden" name="search" value="<?=strip_tags($search)?>">
<input type="hidden" name="search_str" value="<?=strip_tags($old_search_str)?>">
<input type="hidden" name="temp" value="">
</form>
<script type='text/javascript' src="<?=$engine_url?>/board/common/common.js"></script>
