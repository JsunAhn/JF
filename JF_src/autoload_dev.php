<?php
/**
 * Name: Autoload & Path
 * Desc: 쿠키 유틸리티
 * User: jusun
 * Date: 2017-05-14
 * Time: 오후 5:36
 */

//SITEROOT
define("JF_ROOT",$_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR."..");

//프레임웍의 위치
//define("JF_HOME",realpath(__DIR__)); phar안에서는 작동안함
define("JF_HOME",JF_ROOT . DIRECTORY_SEPARATOR . "JF_src");

//보조 리소스 파일등등
define("JF_MISC",JF_HOME . DIRECTORY_SEPARATOR . "MISC");


spl_autoload_register('JFAutoload');
function JFAutoload($className){
    $className = str_replace("JF\\","JF_src\\",$className); //테스트 시에만 사용함
    $className = str_replace("\\","/",$className);
    //echo $className;
    //echo JF_ROOT . DIRECTORY_SEPARATOR . $className . ".php";

    $searchClassName = JF_ROOT . DIRECTORY_SEPARATOR . $className . ".php";

    if(is_file($searchClassName)) {
        require_once $searchClassName;
        //echo $searchClassName . "<br>";
    }else{
        $searchClassName = "phar://" . JF_ROOT . DIRECTORY_SEPARATOR . "JF".DIRECTORY_SEPARATOR."JF.phar/" . $className . ".php";
        require_once  $searchClassName;
        //echo $searchClassName . "<br>";
    }
}