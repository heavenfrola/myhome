<script type="text/javascript">
    function samsungpayAuth(auth,ua) {
        document.samsungpayFrm.action = auth;

        if (ua == 'pc') {
            let w = window.open("", "popUpWinv", "width=420px, height=500px, status=yes, scrollbars=no,resizable=yes, menubar=no");
            if (w) {
                var f = document.getElementById('samsungpayFrm');
                f.target = 'popUpWinv';
                f.submit();
            } else {
                window.alert('브라우저의 새창열기 설정이 차단되어있습니다. \n정상적인 결제를 위해서 새창열기를 허용해 주세요.');
                parent.layTgl3('order1', 'Y');
                parent.layTgl3('order2', 'N');
                parent.layTgl3('order3', 'Y');
            }
        } else {
            let f = document.getElementById('samsungpayFrm');
            $('#samsungpayFrm').remove();
            $('body').append(f);
            $('#samsungpayFrm').submit();
        }

        document.samsungpayFrm.action = "<?=$root_url?>/main/exec.php?exec_file=card.samsungpay/card_pay.exe.php";
    }
</script>

<form name="samsungpayFrm" id="samsungpayFrm" method="post" action="<?=$root_url?>/main/exec.php?exec_file=card.samsungpay/card_pay.exe.php">
    <input type="hidden" name="TID" value="">
    <input type="hidden" name="STARTPARAMS" value="">
</form>