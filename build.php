<?php
/**
 * Title :
 * Author : jusun
 * Date: 2017-04-23
 * Time: 오후 2:56
 * build commend : php -dphar.readonly=0 build.php
 */



ini_set('phar.readonly',0);

$srcRoot = "./JF_src";
$buildRoot = "./JF";

//패키지 생성
$phar = new Phar($buildRoot . "/JF.phar",FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, "JF.phar");

//버져닝을 메타 태그로 추가(파일로 관리)
$buildVersion = intval(file_get_contents("./JF_temp/build_num.txt")) + 1;
$fp = fopen("./JF_temp/build_num.txt","w");
fwrite($fp,$buildVersion,4096);
fclose($fp);
$phar->setMetadata("JFramework 0.1 build {$buildVersion} - Develop by http://jusun.org / jusun(sun@jusun.org)");
echo "Build Version : " . $buildVersion . "\n";

//패키지할 파일 리스트
$phar["JF/CORE/JGate.php"] = file_get_contents($srcRoot . "/CORE/JGate.php");
$phar["JF/CORE/JRoute.php"] = file_get_contents($srcRoot . "/CORE/JRoute.php");
$phar["JF/CORE/DataSet.php"] = file_get_contents($srcRoot . "/CORE/DataSet.php");
$phar["JF/CORE/Error.php"] = file_get_contents($srcRoot . "/CORE/Error.php");
$phar["JF/CORE/Log.php"] = file_get_contents($srcRoot . "/CORE/Log.php");

$phar["JF/ORM/SqlMapper.php"] = file_get_contents($srcRoot . "/ORM/SqlMapper.php");

$phar["JF/UTIL/Cookie.php"] = file_get_contents($srcRoot . "/UTIL/Cookie.php");
$phar["JF/UTIL/Crypto.php"] = file_get_contents($srcRoot . "/UTIL/Crypto.php");
$phar["JF/UTIL/FileTransfer.php"] = file_get_contents($srcRoot . "/UTIL/FileTransfer.php");
$phar["JF/UTIL/Helper.php"] = file_get_contents($srcRoot . "/UTIL/Helper.php");
$phar["JF/UTIL/Http.php"] = file_get_contents($srcRoot . "/UTIL/Http.php");
$phar["JF/UTIL/ImageProcess.php"] = file_get_contents($srcRoot . "/UTIL/ImageProcess.php");
$phar["JF/UTIL/Session.php"] = file_get_contents($srcRoot . "/UTIL/Session.php");
$phar["JF/UTIL/Validate.php"] = file_get_contents($srcRoot . "/UTIL/Validate.php");

$phar["JF/UTIL/Excel.php"] = file_get_contents($srcRoot . "/UTIL/Excel.php");
$phar["JF/UTIL/Mailer.php"] = file_get_contents($srcRoot . "/UTIL/Mailer.php");
$phar["JF/UTIL/MQ.php"] = file_get_contents($srcRoot . "/UTIL/MQ.php");
//$phar["JF/UTIL/GeoIP.php"] = file_get_contents($srcRoot . "/UTIL/GeoIP.php");

$phar["autoload.php"] = file_get_contents($srcRoot ."/autoload.php");

$phar->setStub($phar->createDefaultStub("autoload.php"));

//파일 카피
$arr_pkg_file = array();
$arr_pkg_file[] = "/BASE/BaseAPIControl.php";
$arr_pkg_file[] = "/BASE/BaseWebControl.php";
$arr_pkg_file[] = "/CONF/JFConfig.php";
$arr_pkg_file[] = "/CONF/Config.php";
$arr_pkg_file[] = "/MISC/html/http404.html";
$arr_pkg_file[] = "/MISC/html/phpError.html";
$arr_pkg_file[] = "/MISC/sqlmap/sqlmap.xml";
$arr_pkg_file[] = "/MODL/ModUser.php";

foreach($arr_pkg_file as $k => $file){
    @mkdir(dirname($buildRoot . $file),0755,true);
    copy($srcRoot . $file,$buildRoot . $file);
    echo "Copying.... " . $buildRoot . $file . "\n";
    //echo dirname($buildRoot . $file) ."\n";
}

