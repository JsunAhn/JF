<?php
/**
 * Name: Cookie class
 * Desc: 쿠키 유틸리티
 * User: jusun
 * Date: 2016-12-14
 * Time: 오후 5:36
 */

namespace JF\UTIL;


class Cookie{

    public function __construct(){
        if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){
            $_SERVER['HTTPS']='on';
        }
    }

    /**
     * 쿠키 굽기
     * @param $name
     * @param $value
     * @param int $period
     */
    public function set($name, $value, $period = 0){
        if($_SERVER['HTTPS'] == "on"){
            setcookie($name, $value, $period,"/",$_SERVER['HTTP_HOST'],true);
        }else {
            setcookie($name, $value, $period,"/",$_SERVER['HTTP_HOST']);
        }
    }


    /**
     * 쿠키를 가져옴
     * @param $name
     * @return mixed
     */
    public function get($name){
        return $_COOKIE[$name];
    }


    /**
     * 쿠키를 제거함
     * @param $name
     */
    public function remove($name){
        setcookie($name, "", time() - 3600);
    }


    /**
     * 모든 쿠키를 제거함
     */
    public function removeAll(){
        foreach($_COOKIE as $name => $value){
            $this->remove($name);
        }
    }


    /**
     * 모든 쿠키를 배열로 반환
     * @return mixed
     */
    public function getAll(){
        return $_COOKIE;
    }

}