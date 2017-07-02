<?php

/**
 * 환경설정 파일
 * User: jusun
 * Date: 2017-04-10
 * Time: 오후 9:55
 */

namespace JF\CONF;

class JFConfig{
    public $__debug;

    public $errorReportHTML;
    public $errorReportHTMLPath;
    public $errorReportLogFile;
    public $errorReportLogFilePath;
    public $errorHTTP404Path;

    public $defaultControl = "main";
    public $defaultView = "index";

    public $defaultTimeZone = "GMT";

    public $defaultDataPath;
    public $dataPathPermission;
    public $permissionCheckList;


    public function __construct(){

        //에러 디버그 여부 결정
        $this->__debug = true;

        //에러 리포팅
        $this->errorReportHTML = true;
        $this->errorReportHTMLPath = JF_MISC .DIRECTORY_SEPARATOR. "html" . DIRECTORY_SEPARATOR . "phpError.html";
        $this->errorReportLogFile = false;
        $this->errorReportLogFilePath = "";

        //404 페이지
        $this->errorHTTP404Path =  JF_MISC .DIRECTORY_SEPARATOR. "html" . DIRECTORY_SEPARATOR . "http404.html";


        //인덱스 Control/View 설정
        $this->defaultControl = "main";
        $this->defaultView = "index";

        //타임존 설정
        $this->timeZone = "Asia/Seoul";

        //업로드&파일 폴더 설정
        $this->defaultDataPath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "data";

        //파일 처리를 위한 구동 환경을 위한 폴더 권한 검사
        $this->dataPathPermission = "0707";
        $this->permissionCheckList = array(
            array("name" => "Data Path","path" => $this->defaultDataPath),
            array("name" => "Session Path","path" => $this->defaultDataPath .DIRECTORY_SEPARATOR. "_session"),
            array("name" => "Board Path","path" => $this->defaultDataPath .DIRECTORY_SEPARATOR. "bbs")
        );

        //세션폴더 설정
        $this->sessionPath = $this->defaultDataPath .DIRECTORY_SEPARATOR. "_session";
        $this->sessionCacheExpire = "300";






    }


}