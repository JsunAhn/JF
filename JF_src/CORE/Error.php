<?php
/**
 * Name: Error Class
 * Desc: 디버깅을 위한 오류를 발생
 * User: jusun
 * Date: 2016-12-14
 * Time: 오후 5:38
 */

namespace JF\CORE;


use JF\CONF\JFConfig;

class Error
{
    public $JFC;

    public function __construct(){
        //header('Content-Type:application/json;charset=utf-8');

        //PHP 에러를 출력하지 않는다.
        ini_set('display_errors', 0);
        error_reporting(E_ALL | E_STRICT);

        set_exception_handler(array(__CLASS__, '__exception_handler'));
        set_error_handler(array(__CLASS__, '__error_handler'));
        register_shutdown_function(array(__CLASS__, '__shutdown_handler'));


    }

    //
    static public function __shutdown_handler(){
        $lastError = error_get_last();

        if($lastError['type']) {

            //에러 포맷
            $data = array();
            $data['err_date'] = date("y-m-d H:i:s");
            $data['err_type'] = "<font color=red>JF_Fatal_Error</font>";
            $data['err_uri'] = $_SERVER['REQUEST_URI'];
            $data['err_file'] = $lastError['file'];
            $data['err_line'] = $lastError['line'];
            $data['err_msg'] = $lastError['message'];
            $data['err_remote'] = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

            $JFC = new JFConfig();
            if ($JFC->errorReportHTML) {
                include $JFC->errorReportHTMLPath;
            }
        }

    }

    //try catch exception이 발생하는 경우의 헨들링
    static public function __exception_handler($e)
    {
        //오류를 표시할 경우 여러가지 일이 많아서 -_- 추후 예정
        //echo $e->getMessage();
    }

    //일반 오류
    static public function __error_handler($errno, $str, $file, $line){
        $typestr = "COMMON";

        switch ($errno) {
            case E_ERROR: // 1
                $typestr = 'JF_ERROR';
                break;
            case E_WARNING: // 2
                $typestr = 'JF_WARNING';
                return; // 처리안함.
                break;
            case E_PARSE: // 4
                $typestr = 'JF_PARSE';
                break;
            case E_NOTICE: // 8
                $typestr = 'JF_NOTICE';
                return; // 처리안함.
                break;
            case E_CORE_ERROR: // 16
                $typestr = 'JF_CORE_ERROR';
                break;
            case E_CORE_WARNING: // 32
                $typestr = 'JF_CORE_WARNING';
                return; // 처리안함.
                break;
            case E_COMPILE_ERROR: // 64
                $typestr = 'JF_COMPILE_ERROR';
                break;
            case E_CORE_WARNING: // 128
                $typestr = 'JF_COMPILE_WARNING';
                break;
            case E_USER_ERROR: // 256
                $typestr = 'JF_USER_ERROR';
                break;
            case E_USER_WARNING: // 512
                $typestr = 'JF_USER_WARNING';
                break;
            case E_USER_NOTICE: // 1024
                $typestr = 'JF_USER_NOTICE';
                break;
            case E_STRICT: // 2048
                $typestr = 'JF_STRICT';
                break;
            case E_RECOVERABLE_ERROR: // 4096
                $typestr = 'JF_RECOVERABLE_ERROR';
                break;
            case E_DEPRECATED: // 8192
                $typestr = 'JF_DEPRECATED';
                break;
            case E_USER_DEPRECATED: // 16384
                $typestr = 'JF_USER_DEPRECATED';
                break;
        }



        //에러 포맷
        $data = array();
        $data['err_date'] = date("y-m-d H:i:s");
        $data['err_type'] = $typestr;
        $data['err_uri'] = $_SERVER['REQUEST_URI'];
        $data['err_file'] = $file;
        $data['err_line'] = $line;
        $data['err_msg'] = $str;
        $data['err_remote'] = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

        $JFC = new JFConfig();
        if($JFC->errorReportHTML){
            include $JFC->errorReportHTMLPath;
        }

        //파일에 로그를 기록함
        if($JFC->errorReportLogFile){
            // todo://로그를 파일에 기록하기 만들기
            //Log::framework_log($data);
        }

    }



}