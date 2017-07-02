<?php
/**
 * Name: Crypto Class
 * Desc: 암호화/복호화 유틸리티 모음
 * User: jusun
 * Date: 2016-12-14
 * Time: 오후 6:00
 */

namespace JF\UTIL;


class Crypto{

    public function __construct() {

        //$arr = mcrypt_list_algorithms();
        //var_dump($arr);

    }

    /**
     * aesEncrypt
     * aes방식으로 암호화함 (mcrypt사용)
     * @param $val 암호화 할 데이터
     * @param $key 암호화 키
     * @return string 암호화된 데이터
     * 참조 : http://php.net/manual/kr/ref.mcrypt.php
     */
    public function aesEncrypt($val, $key){
        $key = str_pad($key, 16, chr(0));
        $mode=MCRYPT_MODE_ECB;
        $enc=MCRYPT_RIJNDAEL_128;
        $val=str_pad($val, (16*(floor(strlen($val) / 16)+(strlen($val) % 16==0?2:1))), chr(16-(strlen($val) % 16)));
        return mcrypt_encrypt($enc, $key, $val, $mode, mcrypt_create_iv( mcrypt_get_iv_size($enc, $mode), MCRYPT_DEV_URANDOM));
    }

    /**
     * aesDncrypt
     * aes방식으로 복호화함 (mcrypt사용)
     * @param $val 암호화 된 데이터
     * @param $key 암호화 키
     * @return string 복호화 된 데이터
     */
    public function aesDecrypt($val,$key){
        $key = str_pad($key, 16, chr(0));
        $mode = MCRYPT_MODE_ECB;
        $enc = MCRYPT_RIJNDAEL_128;
        $dec = @mcrypt_decrypt($enc, $key, $val, $mode, @mcrypt_create_iv( @mcrypt_get_iv_size($enc, $mode), MCRYPT_DEV_URANDOM ) );
        return rtrim($dec,(( ord(substr($dec,strlen($dec)-1,1))>=0 and ord(substr($dec, strlen($dec)-1,1))<=16)? chr(ord( substr($dec,strlen($dec)-1,1))):null));
    }


    /**
     * encrypt(base64)
     * 문자열로 암호화되고 복호 가능한 암호화
     * @param $string
     * @param $salt
     * @return string
     */
    public function encrypt($string, $salt){
        $result = '';
        for($i=0; $i<strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($salt, ($i % strlen($salt))-1, 1);
            $char = chr(ord($char)+ord($keychar));
            $result.=$char;
        }

        return base64_encode($result);
    }


    /**
     * decrypt(base64)
     * 암호화된 문자열을 복호화
     * @param $string
     * @param $salt
     * @return string
     */
    public function decrypt($string, $salt) {
        $string = str_replace(" ","+",$string); // URL 전송시 얘가 깨먹어서 그럼

        $result = '';
        $string = base64_decode($string);

        for($i=0; $i<strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($salt, ($i % strlen($salt))-1, 1);
            $char = chr(ord($char)-ord($keychar));
            $result.=$char;
        }

        return $result;
    }

}