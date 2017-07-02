<?php
//기본적으로 호출될 URL 지정 이 경로 이후에 붙는 것을 CONTROL/VIEW로 인식함.
//해당 폴더에 .htaccess가 있어야함.
define("JF_URL","/");

//모듈 로드! (테스트시)
include "../JF_src/autoload_dev.php";

//실제 사용
//include "../JF/JF.phar";

//$phar = new Phar("../JF/JF.phar");

$route = new \JF\CORE\JRoute();
$route->setRoute("/jusun/","/main/jusuntest?number=$0&test=$1");
$route->setRoute("/jusun2/","/main/rtest2?number=$0&test=$1");

//초기화
new JF\CORE\JGate($route);
