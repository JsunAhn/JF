<?php
/**
 * Name: Log Class
 * Desc: 프레임웍의 각종 로그를 남기기 위한 기능
 * Date: 2016-12-14
 * Time: 오후 5:39
 */

namespace JF\CORE;


class Log{

    //로그 쓰기
    static public function write($message){

    }

    static public function errorLogWrite($message){


        $data = $message['err_type'] . "\n";
        $data .= $message['err_date'] . "\n";
        $data .= $message['err_uri'] . "\n";
        $data .= $message['err_file'] . " at ".$message["err_line"]. " Line\n";
        $data .= $message['err_msg'] . "\n";
        $data .= $message['err_remote'] . "\n\n";

        $log_file = TR_LOG."/Log_".date("ymd").".log";

        file_put_contents($log_file,$data,FILE_APPEND | LOCK_EX);
    }
}