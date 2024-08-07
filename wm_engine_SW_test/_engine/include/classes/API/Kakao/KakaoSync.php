<?php

namespace Wing\API\Kakao;

use Wing\HTTP\CurlConnection;

class KakaoSync
{
	const ADMIN_KEY = 'ce39ea8b7d1c45e0775f2750c907db26';
	const JS_KEY = '25fe462d0752121de16d991bad9024df';
    const SANDBOX = '';

	public function __construct($storeKey = null, $restapiKey = null)
	{
        $this->storeKey = $storeKey;
        $this->restapiKey = $restapiKey;
	}


    /**
     * getDialogUrl 간편가입창 오픈
     **/
	public function getDialogUrl()
    {
		$url  = 'https://'.self::SANDBOX.'sync4ecp.kakao.com/dialog/connect?'.
                'appKey='.self::JS_KEY.
                '&storeKey='.$this->storeKey;

        return $url;
	}


    /**
     * 쇼핑몰 설정 및 도메인 등의 정보 수정 후 갱신
     **/
    public function modification($action = 'edit')
    {
        $curl = new CurlConnection(
            'https://'.self::SANDBOX.'sync4ecp.kakao.com/api/v1/sync4ecp/sync/modification?appKey='.self::ADMIN_KEY,
            'POST',
            json_encode(array(
                'storeKey' => $this->storeKey,
                'appKey' => $this->restapiKey,
                'data' => array(
                    'type' => 'KAKAO_SYNC',
                    'action' => $action
                )
            ))
        );
        $curl->setHeader(array(
            'content-type: application/json;charset=UTF-8'
        ));
        $curl->exec();

        return $curl->getResult(true);
    }


    /**
     * 가입 약관 확인
     **/
    public function getTerms($access_token)
    {
        $curl = new CurlConnection(
            'https://'.self::SANDBOX.'kapi.kakao.com/v1/user/service/terms?extra=app_service_terms'
        );
        $curl->setHeader(array(
            'Authorization: Bearer '.$access_token,
        ));
        $curl->exec();

        return json_decode($curl->getResult());
    }


    /**
     * 카카오 싱크 가입 정보 조회
     **/
    public function getAppinfo()
    {
        $curl = new CurlConnection(
            'https://'.self::SANDBOX.'kapi.kakao.com/v1/ecp_app?target_app_key='.$this->restapiKey
        );
        $curl->setHeader(array(
            'Authorization: KakaoAK '.self::ADMIN_KEY
        ));
        $curl->exec();

        return json_decode($curl->getResult(true));
    }


    /**
     * 회원 연결 끊기
     **/
    public function unlink($user_id)
    {
        $curl = new CurlConnection(
            'https://'.self::SANDBOX.'kapi.kakao.com/v1/user/unlink',
            'POST',
            'target_id_type=user_id&target_id='.$user_id
        );
        $curl->setHeader(array(
            'Authorization: Bearer '.$_SESSION['sns_login']['bearer']
        ));
        $curl->exec();

        return json_decode($curl->getResult(true));
    }

    /**
     * 자동 로그인
     * https://developers.kakao.com/docs/latest/ko/kakaosync/auto-login#web
     **/
    public function autoLogin($rurl = null, $has_prompt = true)
    {
        global $p_root_url, $root_url;

        if (preg_match('/KAKAOTALK/', $_SERVER['HTTP_USER_AGENT']) == false) {
            return false;
        }

        $_SESSION['sns_login_state'] = md5(microtime().mt_rand());
        if (!$rurl) {
            $rurl = $root_url;
        }
        $_SESSION['sns_login']['rURL'] = $rurl;

        $param = array(
            'client_id' => $this->restapiKey,
            'redirect_uri' => $p_root_url.'/_data/compare/kakao/kakao_login_auth.php',
            'response_type' =>'code',
            'state' => $_SESSION['sns_login_state']
        );
        if ($has_prompt == true) { // none일 경우 자동 로그인. 최초 가입(가입 승인 전)인 경우 값이 없어야 함
            $param['prompt'] = 'none';
        }

        msg('',
            'https://kauth.kakao.com/oauth/authorize?'.http_build_query($param)
        );
    }

}