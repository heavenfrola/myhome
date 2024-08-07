<?php

namespace Wing\Session;

/**
 * DB 세션 핸들러 클래스
 **/

abstract class WingSession
{
    /**
     * 세션 핸들러 등록
     */
	protected function init()
    {
        if (ini_get('session.auto_start') == 1) {
            session_write_close();
        }

        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );
	}

    /**
     * 세션 내용 분석
     */
    public function unserialize($session_data)
    {
         $return_data = array();
         $offset = 0;
         while ($offset < strlen($session_data)) {
             if (!strstr(substr($session_data, $offset), "|"))
             {
                 return;
             }
             $pos = strpos($session_data, "|", $offset);
             $num = $pos - $offset;
             $varname = substr($session_data, $offset, $num);
             $offset += $num + 1;
             $data = unserialize(substr($session_data, $offset));
             $return_data[$varname] = $data;
             $offset += strlen(serialize($data));
         }
         return $return_data;
    }

    /**
     * 세션 열기
     */
    abstract public function open($savePath, $sessionName);

    /**
     * 세션 닫기
     */
    abstract public function close();

    /**
     * 세션 읽기
     * PHP7부터 값이 null 로 리턴되면 세션이 동작하지 않습니다.
     * 값이 없더라도 빈 스트링으로 전송해야 합니다.
     */
    abstract public function read($id);


    /**
     * 세션 쓰기
     */
    abstract public function write($id, $data);

    /**
     * 세션 삭제
     */
    abstract public function destroy($id);

    /**
     * 만료 된 세션 삭제
     */
    abstract public function gc($maxlifetime);

    /**
     * 지정된 세션 아이디가 존재하는지 리턴
     */
    abstract public function exists($id);

    /**
     * 세션의 내용을 배열로 리턴
     */
    abstract public function parse($id);

    /**
     * 중복 로그인 체크
     **/
    abstract public function setDuplicate($var);
    abstract public function checkDuplicate($var);

}


?>