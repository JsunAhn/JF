<?php
/**
 * Name: Http Class
 * Desc: HTTP 프로토콜 호출을 도와주는 유틸리티
 * User: jusun
 * Date: 2016-12-14
 * Time: 오후 6:04
 */

namespace JF\UTIL;


class Http{

    public function __construct(){

    }

    //Simple Connection!
    public function simplePostRequest($url,$postData = array()){

        $str_postData = "";
        foreach($postData as $key => $value){
            $str_postData .= $key."=".$value."&";
        }

        $curl = curl_init($url);
        curl_setopt_array($curl,array(
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => array(
                    'Accept: text/plain',
                    'Content-Type: application/x-www-form-urlencoded'
                ),
                CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)',
                CURLOPT_POSTFIELDS => $str_postData
            )
        );
        $response = curl_exec($curl);
        if($response == false){
            trigger_error(curl_error($curl));
        }

        curl_close($curl);

        return $response;
    }


}