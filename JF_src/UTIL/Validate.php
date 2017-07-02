<?php
/**
 * Name: Validate Class
 * Desc: 각종 데이터 형식을 검사하기 위한 유틸리티
 * Author : jusun
 * Date: 2017-04-20
 * Time: 오전 12:42
 */

namespace JF\UTIL;


class Validate{

    //이메일 검사
    public static function isEmail($eMail){
        if(preg_match('/([\w\-]+\@[\w\-]+\.[\w\-]+)/i', $eMail)) {
            return true;
        }else{
            return false;
        }
    }

    //휴대폰검사
    public static function isPhone($phone) {
        if(preg_match("/^([0-9]{2,4})-([0-9]{3,4})-([0-9]{4})$/", $phone)) {
            return true;
        }else{
            return false;
        }
    }

    //숫자검사
    public static function isNumber($number, $msg, $msg_type = "er_msg") {
        if(preg_match("/^\d+$/", $number)) {
            return true;
        }else{
            return false;
        }
    }

    //비밀번호 복잡도 검사(8자리 이상)
    public static function isComplexPass($number) {
        //비밀번호 복잡도 체크 8자 이상 20자 이하 영문 숫자 혼합
        if(preg_match("/^.*(?=.{8,20})(?=.*[0-9])(?=.*[a-zA-Z]).*$/i", $number)) {
            return true;
        }else{
            return false;
        }
    }

    //비밀번호 복잡도 검사(6자리 이상)
    public static function isComplexPass6($number) {
        //비밀번호 복잡도 체크 6자 이상 20자 이하 영문 숫자 혼합
        if(preg_match("/^.*(?=.{6,20})(?=.*[0-9])(?=.*[a-zA-Z]).*$/i", $number)) {
            return true;
        }else{
            return false;
        }
    }

    //2바이트 특수문자가 들어있는지 검사
    public static function is2ByteChar($String) {
        $pattern = '/[\x{1100}-\x{11FF}\x{3130}-\x{318F}\x{AC00}-\x{D7AF}a-zA-Z0-9~`!@#$%^&*()+\-.:;,_<>?\[\]\{\}\/"\' ]+/u';

        if(strlen(preg_replace($pattern, "", $String,-1)) > 1) {
            return true;
        }else{
            return false;
        }
    }

    //한글인지 검사
    public static function isHangul($String) {
        $pattern = '/[\x{1100}-\x{11FF}\x{3130}-\x{318F}\x{AC00}-\x{D7AF}]+/u';
        if(strlen(preg_replace($pattern, "", $String,-1)) > 1) {
            //제거한 글자보다 더 많으면 한글 이외의 문자가 있음..
            return false;
        }else{
            return true;
        }
    }

}