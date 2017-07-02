<?php
use JF\BASE\BaseWebControl;
use JF\CORE\DataSet;
use JF\UTIL\Cookie;
use JF\UTIL\Crypto;

class main extends BaseWebControl {

    public function index(DataSet $dataSet){


        return $dataSet;
    }

    public function test01(DataSet $dataSet){
        $common = $dataSet->getDataTable("common");

        $cookie = new Cookie();
        $cookie->set("a1",$cookie->get("a1") + 11);
        $cookie->set("a2","232");
        $cookie->set("a3","12321");
        $cookie->set("a4","afafafasfads안주선");

        var_dump($cookie->getAll());

        var_dump($common);


        return $dataSet;
    }


    public function test02(DataSet $dataSet){

        $crypt = new Crypto();
        $enc_str = $crypt->aesEncrypt("안주선입니다.안주선","12");
        echo $enc_str;

        echo $crypt->aesDecrypt($enc_str,"12");

        echo sha1("test");


        return $dataSet;
    }

    public function imagetest(DataSet $dataSet){
        echo "<pre>";

        $JFC = new \JF\CONF\JFConfig();

        $ir = new \JF\UTIL\ImageProcess();
        $result = $ir->setOrigin("test/111.jpg")->setSize(102,102)->setCrop()->exec();
        var_dump($result);
        echo "<img src='".$result['webPath']."'><br>";

        $ir = new \JF\UTIL\ImageProcess();
        $result = $ir->setOrigin("test/111.jpg")->setSize(101,101)->setResize()->exec();
        var_dump($result);
        echo "<img src='".$result['webPath']."'><br>";

        $result = $ir->setOrigin("test/111.jpg")->setSize(320,220)->exec();
        var_dump($result);
        echo "<img src='".$result['webPath']."'><br>";

        $result = $ir->setOrigin("test/111.jpg")->setSize(150,202)->setResize()->exec();
        var_dump($result);
        echo "<img src='".$result['webPath']."'><br>";

        $result = $ir->setOrigin("test/111.jpg")->setSize(900,203)->setResize()->exec();
        var_dump($result);
        echo "<img src='".$result['webPath']."'><br>";

        $result = $ir->setOrigin("test/111.jpg")->setSize(960,540)->exec();
        var_dump($result);
        echo "<img src='".$result['webPath']."'><br>";

        echo "</pre>";



        return $dataSet;
    }

    public function rtest(DataSet $dataSet){

        $needle = "/jusun/test";
        $haystack = "/jusun/test/test/test";

        $length = strlen($needle);
        echo (substr($haystack, 0, $length) === $needle);

        return $dataSet;
    }

    public function jusuntest(DataSet $dataSet){
        $common = $dataSet->getDataTable("common");
        $JF = $dataSet->getDataTable("JF");
        echo "common value : ";
        var_dump($common);

        var_dump($JF);


        exit;
        return $dataSet;
    }

}