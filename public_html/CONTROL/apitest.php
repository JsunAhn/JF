<?php
use JF\BASE\BaseWebControl;
use JF\CORE\DataSet;

/**
 * Title :
 * Author : jusun
 * Date: 2017-07-03
 * Time: 오후 10:56
 */
class apitest extends BaseWebControl{

    var $db;
    var $config;

    public function __construct(){
        parent::__construct();
    }

    public function index(DataSet $dataSet){

        $dataSet->printStream("none");
        return $dataSet;
    }

    //쿠키 클래스 테스트
    public function cookie(DataSet $dataSet){

        $cookie = new \JF\UTIL\Cookie();

        $cookie->set("name","value");
        $cookie->set("count",$cookie->get("count") + 1);

        echo $cookie->get("name");
        echo $cookie->get("count");

        $dataSet->printStream("none");
        return $dataSet;
    }

    //암호화 테스트
    public function crypto(DataSet $dataSet){

        $crypto = new \JF\UTIL\Crypto();

        //기본 암호화
        $crypto_str = $crypto->encrypt("hello world JF!","11");
        echo $crypto_str . "<br>";
        $decrypto_str = $crypto->decrypt($crypto_str,"11");
        echo $decrypto_str . "<br>";

        //aes 암호화
        $crypto_str2 = $crypto->aesEncrypt("안녕안녕하세요","111111");
        echo $crypto_str2 . "<br>";
        $decrypto_str2 = $crypto->aesDecrypt($crypto_str2,"111111");
        echo $decrypto_str2 . "<br>";

        $dataSet->printStream("none");
        return $dataSet;
    }

    //엑셀 테스트
    public function excel(DataSet $dataSet){
        //아직 준비되지 않음...
        //composer 적용후 개발 예정

        $dataSet->printStream("none");
        return $dataSet;
    }

    //파일전송 업로드 폼
    public function file_transfer_form(DataSet $dataSet){

        return $dataSet;
    }


    //파일전송 업로드 테스트
    public function file_transfer_upload(DataSet $dataSet){

        //form data import
        $common = $dataSet->getDataTable("common");

        //업로드 폴더로 지정된 data폴더 이후의 폴더를 지정해줌 data/test/가 지정됨
        $ft = new \JF\UTIL\FileTransfer("test");
        if($file_result = $ft->uploadFile($common['upfile'])) {
            var_dump($file_result);
        }else{
            echo "oops! upload false";
        }

        $dataSet->printStream("none");
        return $dataSet;
    }

    //파일전송 다운로드 테스트
    public function file_transfer_down(DataSet $dataSet){

        //업로드 폴더로 지정된 data폴더 이후의 폴더를 지정해줌 data/test/가 지정됨
        $ft = new \JF\UTIL\FileTransfer("test");
        $fileSource = "1499092553_S2FrYW9UYWxrXzIwMTUxMjIyXzEyNTY0ODQ2MS5qcGc.jpg";
        $downloadFilename = "20151222_125648461.jpg";

        if(!$ft->downloadFile($fileSource,$downloadFilename)){
            echo "file not found";
        }

        //다운로드 기능은 더이상 출력 스트림이 있으면 안됨
        $dataSet->printStream("none");
        return $dataSet;
    }

    //헬퍼 클래스
    public function helper(DataSet $dataSet){

        echo "<xmp>";

        $search = "test";
        //보여질 페이지수,현재페이지,전체페이지,연결주소,추가할 변수들
        echo \JF\UTIL\Helper::getPaging(10,1,30,"/apitest/helper?page=","&search={$search}");

        //static으로 사용되며 이후 출력이 진행되지 않음.
        \JF\UTIL\Helper::go("/apitest/index");

        \JF\UTIL\Helper::alertMsg("나빠요 나빠");

        \JF\UTIL\Helper::alertMsg("나빠요 나빠","/apitest/index");

        \JF\UTIL\Helper::cutString("긴문자열 자르기 긴문자열",20,"...");

        echo "</xmp>";

        $dataSet->printStream("none");
        return $dataSet;
    }

    //http 클래스
    public function http(DataSet $dataSet){

        $http = new \JF\UTIL\Http();
        $postdata = array();
        $postdata["user_id"] = "jusun";
        $postdata["user_pass"] = "1234";
        $result = $http->simplePostRequest("http://evape.kr",$postdata);
        echo $result;

        $dataSet->printStream("none");
        return $dataSet;
    }

    //impageprocess 클래스 테스트
    public function impageprocess(DataSet $dataSet){
        $ir = new \JF\UTIL\ImageProcess();
        $result = $ir->setOrigin("test/111.jpg")->setSize(102,102)->setCrop()->exec();
        var_dump($result);
        echo "<img src='".$result['webPath']."'><br>";

        $result = $ir->setOrigin("test/111.jpg")->setSize(190,102)->exec();
        var_dump($result);
        echo "<img src='".$result['webPath']."'><br>";

        $dataSet->printStream("none");
        return $dataSet;
    }

    //메일(준비중)
    public function mailer(DataSet $dataSet){

        return $dataSet;
    }

    //Que 서비스(준비중)
    public function mq(DataSet $dataSet){

        return $dataSet;
    }

    //session 테스트
    public function session(DataSet $dataSet){
        //생성자에서 세션을 시작해야함(BaseWebControl에서 이미 시작됨)

        \JF\UTIL\session::set("name","jusun");

        echo \JF\UTIL\session::get("name");

        $dataSet->printStream("none");
        return $dataSet;
    }

    //validate
    public function validate(DataSet $dataSet){

        echo \JF\UTIL\Validate::isEmail("sun@aaaa.com");

        echo \JF\UTIL\Validate::isPhone("010-1234-5678");

        echo \JF\UTIL\Validate::is2ByteChar("☆☆☆☆☆");

        echo \JF\UTIL\Validate::isHangul("한글");

        echo \JF\UTIL\Validate::isNumber("1");

        echo \JF\UTIL\Validate::isComplexPass("abcd123!@#");

        echo \JF\UTIL\Validate::isComplexPass6("av123!");

        $dataSet->printStream("none");
        return $dataSet;
    }



}