<?php

namespace Wing\common;

/**
 * Smartwing order file log class
 **/

class OrderLog
{
    const __ENC_KEY__ = 'smart!@wing^';
    private $ono;
    private $changed = false;

    public function __construct($ono)
    {
        global $root_dir, $_site_key_file_info, $engine_dir;

        $this->ono = $ono;
        $this->enc_key = self::__ENC_KEY__.$_site_key_file_info[2];
        $this->log_dir = '_data/order_log/'.substr($ono, 0, 6).'/'.substr($ono, 6, 2);
        $this->log_file = $this->log_dir.'/'.$ono;

        // 로그 디렉토리 생성
        if (function_exists('makeFullDir') == false) {
            include_once __ENGINE_DIR__.'/_engine/include/file.lib.php';
        }
        if (file_exists($root_dir.'/'.$this->log_file) == false) {
            makeFullDir($this->log_dir);
        }
        $this->content = $this->decrypt();
    }


    /**
     * 로그의 주문번호를 리턴한다
     **/
    public function getOrderNo()
    {
        return $this->ono;
    }


    /**
     * 로그 본문 내용을 리턴한다.
     **/
    public function getContent()
    {
        return $this->content;
    }


    /**
     * 로그파일의 암호화를 해제한다.
     **/
    public function decrypt()
    {
        global $root_dir;

        $file = $root_dir.'/'.$this->log_file;
        if (file_exists($file) == false) {
            return null;
        }

        $fp = fopen($file,  'r');
        if (!$fp) return null;

        $content = fread($fp, filesize($file));
        fclose($fp);

        if (function_exists('openssl_encrypt') == true) {
            if (substr($content, 0, 3) === 'enc') {
                $content = @openssl_decrypt(substr($content, 3), 'AES128', $this->enc_key, OPENSSL_RAW_DATA);
            }
        }

        return $content;
    }


    /**
     * request parameter를 로그에 저장한다.
     **/
    public function getRequests()
    {
        if (count($_POST) > 0) {
            $this->writeln(print_r($_POST, true), 'POST');
        }
        if (count($_GET) > 0) {
            $this->writeln(print_r($_GET, true), 'GET');
        }
    }


    /**
     * 현재 시간 정보를 출력한다.
     **/
    function getMicrotime()
    {
        return date('Y-m-d H:i:s'.substr((string) microtime(), 1, 8));
    }

    /**
     * 주문로그에 텍스트를 추가한다.
     **/
    public function writeln($str, $title = '')
    {
        $str = "[".$this->getMicrotime()."]\n".$str;
        if ($title) {
            $str = "/* $title */\n".$str;
        }
        $this->content .= $str."\n\n";
        $this->changed = true;
    }


    /**
     * 로그파일을 암호화한다.
     **/
    public function __destruct()
    {
        if ($this->changed == false) { // 수정된 내역이 없을경우 종료
            return;
        }

        if (function_exists('openssl_encrypt') == true) {
            $this->content = @openssl_encrypt($this->content, 'AES128', $this->enc_key, OPENSSL_RAW_DATA);
            $this->content = 'enc'.$this->content;
        }
        fwriteTo($this->log_file, $this->content, 'w');
    }

}