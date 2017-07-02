<?php
/**
 * Name: BaseAPIControl class
 * Desc: 공통적인 컨트롤을 만들기 위한 상위 클래스(RestAPI용)
 * User: jusun
 * Date: 2017-05-14
 * Time: 오후 5:36
 */

namespace JF\BASE;
use JF\CONF\Config;
use JF\ORM\SqlMapper;

class BaseAPIControl {
    var $db;
    var $config;

    public function __construct(){

        //데이터베이스 시작
        $this->db = new SqlMapper(JF_MISC . DIRECTORY_SEPARATOR . "sqlmap" . DIRECTORY_SEPARATOR."sqlMap.xml");

        //사용자 config
        $this->config = new Config();

    }

    public function __toString(){
        return "JF::BaseAPIControl";
    }

    public function __destruct(){
        unset($this->session);
        unset($this->db);
        unset($this->config);
    }

}