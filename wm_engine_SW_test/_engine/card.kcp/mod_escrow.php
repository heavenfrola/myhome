<form method="post" action="<?=$root_url?>/main/exec.php">
<input type="hidden" name="exec_file" value="card.kcp/pp_ax_hub.php">
<input type='hidden' name='site_cd'  value='<?=$cfg['card_site_cd']?>'>
<input type='hidden' name='site_key' value='<?=$cfg['card_site_key']?>'>
<input type='hidden' name='req_tx'   value='mod_escrow'>
<input type='hidden' name='acnt_yn'  value='N'>
</form>