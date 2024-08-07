<?php

	use Wing\API\Kakao\KakaoTalkStore;
    use Wing\API\Naver\CommerceAPI;

    header('Content-type: text/html; charset='._BASE_CHARSET_);

    $pno = numberOnly($_GET['pno']);
    $_REQUEST['from_ajax'] = 'Y';

    if ($_GET['service'] == 'talkstore') {
        $kts = new KakaoTalkStore();
        $ret = $kts->productRegister($pno);
        $ret = json_decode($ret);

        // 에러메시지 출력
        $kko_errmsg = '';
        if (count($ret->extras->validation)) {
            foreach ($ret->extras->validation as $val) {
                $kko_errmsg .= $val[0]."\n";
            }
        }
        if (empty($ret->extras->error_message) == false) {
            $kko_errmsg .= $ret->extras->error_message."\n";
        }
        if ($kko_errmsg) {
            $pdo->query("update {$tbl['product_talkstore']} set useYn='N', edt_date=now() where no='$kko[no]'");
            exit($kko_errmsg);
        } else {
            $pdo->query("update {$tbl['product_talkstore']} set edt_date=now() where no='$kko[no]'");
            exit('success');
        }
    }

    if ($_GET['service'] == 'smartstore') {
        $commerceAPI = new CommerceAPI();
        try {
            $commerceAPI->products($pno); // 상품 등록 요청
            exit('success');
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

?>