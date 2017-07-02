<?php
/**
 * Name: BaseWebControl class
 * Desc: 공통적인 컨트롤을 만들기 위한 상위 클래스(웹용)
 * User: jusun
 * Date: 2017-05-14
 * Time: 오후 5:36
 */

namespace JF\BASE;
use JF\CONF\Config;
use JF\ORM\SqlMapper;
use JF\UTIL\Session;

/**
 * Title : 웹용 기본 Control
 * Author : jusun
 * Date: 2017-04-20
 * Time: 오후 10:26
 */
class BaseWebControl {
    var $session;
    var $db;
    var $config;

    public function __construct(){
        //세션 시작
        $this->session = new Session();

        //데이터베이스 시작
        //$this->db = new SqlMapper(JF_MISC . DIRECTORY_SEPARATOR . "sqlmap" . DIRECTORY_SEPARATOR."sqlMap.xml");

        //사용자 config
        $this->config = new Config();

    }

    public function __toString(){
        return "JF::BaseWebControl";
    }

    public function __destruct(){
        unset($this->session);
        unset($this->db);
        unset($this->config);
    }

}