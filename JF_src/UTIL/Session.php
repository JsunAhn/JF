<?php
/**
 * Name: Session Class
 * Desc: 세션 생성/삭제를 위한 유틸리티
 * User: jusun
 * Date: 2016-12-14
 * Time: 오후 5:35
 */

namespace JF\UTIL;


use JF\CONF\JFConfig;

class Session{
    public $sessionPath;
    public $sessionCacheExpire;
    public $JFC;

    public function __construct(){
        $this->JFC = new JFConfig();

        $this->sessionPath = $this->JFC->sessionPath;
        $this->sessionCacheExpire = $this->JFC->sessionCacheExpire;

        //경로가 없다면
        if(!file_exists($this->sessionPath)){
            trigger_error("[JF Session]Can not access sessionPath.",E_USER_ERROR);
            exit;
        }

        session_save_path($this->sessionPath);

        ini_set("session.cache_expire", $this->sessionCacheExpire);
        ini_set("session.gc_maxlifetime", 10800);
        ini_set("session.gc_probability", 1);
        ini_set("session.gc_divisor", 100);

        //ini_set("session.cookie_domain", TR_COOKIE_DOMAIN);
        //session_set_cookie_params(0, "/", TR_COOKIE_DOMAIN);

        session_start();
    }

    /**
     * set
     * 세션을 저장함
     * @param $name
     * @param $value
     */
    static function set($name, $value) {
        $_SESSION[$name] = $value;
    }


    /**
     * get
     * 저장된 세션을 가져옴
     * @param $name
     * @return string
     */
    static function get($name) {
        return empty($_SESSION[$name]) ? "" : $_SESSION[$name];
    }


    /**
     * destory
     * 세션 종료
     */
    static function destory() {
        session_destroy();
    }

    public function __destruct(){
        unset($this->sessionPath);
        unset($this->sessionCacheExpire);
        unset($this->JFC);
    }

}