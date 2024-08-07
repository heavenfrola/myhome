<?php

/**
 * request내 인잭션 감지
 */

namespace Wing\common;

class InjectionDetector {

    private $report_encode_key = 'aa$g34#@$%^%';
    private $ruleset_encode_key = '!345$^hf';
    private $ruleFile;
    private $ruleString = array();

    /**
     * 생성자
     */
    public function __construct()
    {
        global $root_dir;
        $this->server_ip = $_SERVER['SERVER_ADDR'];
        $this->ruleFile = $root_dir.'/_data/ruleset.txt';
        $this->refreshRuleFile(); //룰셋 최신화
    }

    /**
     * HTTP METHOD별 탐색시작
     */
    public function runDetection()
    {
        $this->__setRule(); //룰셋 확인
        if (!empty($_POST)) {
            $this->__recursiveDetect($_POST);
        }

        if (!empty($_GET)) {
            $this->__recursiveDetect($_GET);
        }
    }

    /**
     * 문자열내 패턴 포함 여부 확인
     * @param $value
     */
    private function __detect($value)
    {
        $res = $this->__detectStrings($value);
        if ($res['flag']) {
            $this->__handleInjectionAttempt($value, $res['matches']);
        } else {
            $value64 = base64_decode($value);
            $res = $this->__detectStrings($value64);
            if ($res['flag']) {
                $this->__handleInjectionAttempt($value, $res['matches']);
            }
        }
    }


    /**
     * 감지문자열 포함여부 체크
     * @param $value
     * @return array
     */
    private function __detectStrings($value)
    {
        $matches = array();
        $res = (preg_match('/('.implode("|", $this->ruleString[0]).')/i', $value, $matches[0]) &&
            preg_match('/('.implode("|", $this->ruleString[1]).')/i', $value, $matches[1]));
        $mergedMatches = array();
        foreach ($matches as $matchArray) {
            // 배열의 원소를 병합하기 전에 추출
            $mergedMatches = array_merge($mergedMatches, $matchArray);
        }
        return array(
            'flag' => $res,
            'matches' => array_unique($mergedMatches)
        );
    }

    /**
     * 탐지시 404리턴
     */
    private function __handleInjectionAttempt($value, $matches)
    {
        header("HTTP/1.1 404 Not Found");
        @$this->__setLog($value, $matches);
        exit();
    }

    /**
     * 배열값 재귀처리
     * @param $data
     */
    private function __recursiveDetect($data)
    {
        foreach ($data as $value) {
            if (is_array($value)) {
                $this->__recursiveDetect($value);
            } else {
                $this->__detect($value);
            }
        }
    }

    /**
     * 롤셋 파일 호출
     * @return false|mixed|string
     */
    private function __getRule()
    {
        if (file_exists($this->ruleFile)) {
            $fp = fopen($this->ruleFile, 'r');
            if ($fp) {
                $ruleSet_enc = fgets($fp);
                $ruleSet = json_decode(openssl_decrypt(base64_decode($ruleSet_enc), 'AES128', $this->ruleset_encode_key, OPENSSL_RAW_DATA));
                fclose($fp);
                return array('strings' => $ruleSet);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 탐지 룰셋 설정
     */
    private function __setRule()
    {
        global $tbl;

        //기본값 정의
        $this->ruleString = array(
            array(
                'union',
                'select',
                'insert',
                'update',
                'delete',
                'drop'
            ),
            array(
                $tbl['member'],
                $tbl['order'],
                $tbl['mng'],
                $tbl['order_product'],
                'BENCHMARK',
                'information_schema'
            )
        );

        $ruleSet = $this->__getRule();

        if (is_array($ruleSet['strings'])) {
            //룰셋이 수신된 경우, 수신된 문자열을 기본 문자열에 추가
            foreach ($ruleSet['strings'] as $seq => $string) {
                $this->ruleString[$seq] = array_merge($this->ruleString[$seq], $ruleSet['strings'][$seq]);
                $this->ruleString[$seq] = array_unique($this->ruleString[$seq]); //중복값 제거
            }
        }
    }

    /**
     * api에서 ruleset을 수신해서 ruleset파일을 최신화 한다.
     */
    public function refreshRuleFile()
    {
        if (!file_exists($this->ruleFile)) {
            //ruleset파일이 없다면 생성
            $fp = fopen($this->ruleFile, 'w');
            fclose($fp);
        }
        $fp = fopen($this->ruleFile, 'r');
        if ($fp) {
            $oldRuleSet = trim(fgets($fp));
            $ruleSetTime = fgets($fp);
            if ($ruleSetTime < (time() - 1800)) {
                //30분 이전 내용이라면 갱신 처리
                $param = array(
                    'rule_type' => 'requestCheck'
                );
                $res = $this->__sendCurl('https://watcher.wisa.co.kr/getruleset', 'POST', $param);
                $ruleSetArr = json_decode($res, true);
                if ($ruleSetArr['strings']) {
                    //ruleset 수신 성공시 갱신
                    $newRuleSet = $ruleSetArr['strings'];
                } else {
                    //실패시 기존 ruleset 유지
                    $newRuleSet = $oldRuleSet;
                }
                $fp = fopen($this->ruleFile, 'w');
                fwrite($fp, $newRuleSet."\n".time()); //성공, 실패와 무관하게 갱신시간은 변경
                fclose($fp);
            }
        }
    }

    /**
     * curl처리 메소드
     * @param $url
     * @param $method | GET, POST
     * @param array $param | 전송 파라미터
     * @return bool|string
     */
    private function __sendCurl($url, $method, $param = array())
    {
        return comm(
            $url,
            http_build_query($param),
            1,
            array('Server-Addr '.$this->server_ip) //헤더내 IP정보 추가
        );
    }

    /**
     * 탐지결과 API전송
     * @param $string | 감지된 문자열
     */
    private function __setLog($string, $matches)
    {
        $query = json_encode(
            array(
                'METHOD' => $_SERVER['REQUEST_METHOD'],
                'POST' => $_POST,
                'GET' => $_GET,
                'REQUEST' => $_REQUEST,
                'SESSION' => $_SESSION,
                'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'],
                'string' => $string
            ), JSON_UNESCAPED_UNICODE);
        $trace = array(
            '감지단어' => implode(',', $matches),
            '공격자IP' => $_SERVER['REMOTE_ADDR']
        );
        $host = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $code = rand(11111,99999);
        $key = $this->server_ip.$code.$this->report_encode_key;
        $query = base64_encode(openssl_encrypt($query, 'AES128', $key, OPENSSL_RAW_DATA));
        $param = array('host' => $host,
            'exception' => 'Injection',
            'message' => $string,
            'trace' => json_encode($trace),
            'code' => $code,
            'query' => $query,
            'server_ip' => $this->server_ip,
            'referer' => $_SERVER['HTTP_REFERER'],
            'client_ip' => $_SERVER['REMOTE_ADDR']
        );
        $this->__sendCurl('https://gcdg.wisa.co.kr/receive.php', 'POST', $param);
    }
}
