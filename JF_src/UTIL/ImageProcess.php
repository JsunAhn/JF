<?php
/**
 * Name: ImageProcess Class
 * Desc: 각종 이미지를 가공해주는 유틸리티
 * User: jusun
 * Date: 2016-12-14
 * Time: 오후 6:01
 */

namespace JF\UTIL;


use JF\CONF\JFConfig;

class ImageProcess{
    var $JFC;
    var $defaultDataPath;
    var $imageFIlePath;

    var $thumbSize;

    var $is_crop = false;
    var $is_resize = false;

    public function __construct(){
        //업로드 폴더
        $this->JFC = new JFConfig();
        $this->defaultDataPath = $this->JFC->defaultDataPath;

        //기본 리사이즈 사이즈
        $this->thumbSize = array("x" => 300, "y" => 300);

    }

    //이미지 경로
    public function setOrigin($imageFilePath){
        $this->imageFIlePath = $this->defaultDataPath . DIRECTORY_SEPARATOR. $imageFilePath;
        $this->is_crop = false;
        $this->is_resize = false;
        return $this;
    }

    //특정 사이즈로 크롭
    public function setCrop(){
        $this->is_crop = true;
        return $this;
    }

    //원래 사이즈를 줄임
    public function setResize(){
        $this->is_resize = true;
        return $this;
    }


    //출력사이즈 설정
    public function setSize($width,$height){
        $this->thumbSize = array("x" => $width, "y" => $height);
        return $this;
    }

    //실행
    public function exec(){

        //echo $this->imageFIlePath;

        //파일 존재 여부 검사
        if(!file_exists($this->imageFIlePath)){
            trigger_error("ImageProcess::exec() : File not found or permission error.");
            return false;
        }


        //파일 타입 검사
        $image_attr = getimagesize($this->imageFIlePath);
        if (($image_attr[2] < 1 || $image_attr[2] > 3) ||
            ($image_attr[2] == 1 && ! function_exists('imagegif')) ||
            ($image_attr[2] == 2 && ! function_exists('imagejpeg')) ||
            ($image_attr[2] == 3 && ! function_exists('imagepng'))){
                trigger_error("ImageProcess::exec() : File type not supported. ");
        }

        //원본의 크기
        $image_width = $image_attr[0];
        $image_height = $image_attr[1];

        $thumb_x = 0;
        $thumb_y = 0;

        if($this->is_crop){
            //크롭일 때 계산
            if($image_width / $this->thumbSize['x'] <= $image_height / $this->thumbSize['y']){
                $ratio = $image_width / $this->thumbSize['x'];
                $thumb_width = $this->thumbSize['x'];
                $thumb_height = floor($image_height / $ratio);
                $thumb_y = round(($this->thumbSize['y'] - $thumb_height) / 2);
            }else{
                $ratio = $image_height / $this->thumbSize['y'];
                $thumb_width = floor($image_width / $ratio);
                $thumb_height = $this->thumbSize['y'];
                $thumb_x = round(($this->thumbSize['x'] - $thumb_width) / 2);
            }

        }else if($this->is_resize){
            //리사이즈만 할 때 계산
            if($image_width > $image_height){
                //가로사진
                $thumb_width = $this->thumbSize['y'];
                $thumb_height = intval($image_height / ($image_width / $this->thumbSize['x']));
            }else{
                //세로사진
                $thumb_width = intval($image_width / ($image_height / $this->thumbSize['y']));
                $thumb_height = $this->thumbSize['y'];
            }

            if($thumb_width < $this->thumbSize['x']){
                //사진이 리사이즈 할려는 크기보다 가로가 작을 때
                $thumb_x = ($this->thumbSize['x'] - $thumb_width) /2;
                echo $thumb_x;
            }

            if($thumb_height < $this->thumbSize['y']){
                //사진이 리사이즈 할려는 크기보다 세로가 작을 때
                $thumb_y = ($this->thumbSize['y'] - $thumb_height) /2;
            }
        }else{
            $thumb_width = $this->thumbSize['x'];
            $thumb_height = $this->thumbSize['y'];
        }


        //저장위치에 파일명
        preg_match('@^(.+/)?([^/]+)\.([^.]+)?$@', $this->imageFIlePath, $p);
        $savePath = $p[1] . "thumb" . DIRECTORY_SEPARATOR . $this->thumbSize['x'] . "x" . $this->thumbSize['y'];
        $savePath = $savePath . DIRECTORY_SEPARATOR . $p[2] . "." . $p[3] ;

        //경로가 없으면 만들어야함
        $tmp = explode(DIRECTORY_SEPARATOR, $savePath);
        $tmp_path = "";
        //foreach($tmp AS $dir) {
        for($i = 1;$i < count($tmp) - 1;$i++){
            $tmp_path .= DIRECTORY_SEPARATOR . $tmp[$i];
            if(!is_dir($tmp_path)) {
                mkdir($tmp_path, 0707);
                chmod($tmp_path, 0707);
                if(!is_dir($tmp_path)){
                    //폴더를 생성하지 못할 때가 있음.
                    trigger_error("ImageProcess::exec() : Check Permission of directory  at " . $tmp_path);
                    exit;
                }
            }
        }

        //반환 형식
        //----------------------------------------------------------------------------
        $returnPath = array();
        // 원본파일 절대경로
        $returnPath['originPath'] = $this->imageFIlePath;
        // 절대경로
        $returnPath['savePath'] = $savePath;
        // data/로 시작하는 경로(웹에서 호출시 사용)
        $returnPath['webPath'] = str_replace($_SERVER['DOCUMENT_ROOT'],"",$savePath);
        // data를 이후의 경로(파일을 추적할때 사용)
        $returnPath['dataPath'] = str_replace($this->defaultDataPath.DIRECTORY_SEPARATOR,"",$savePath);
        //----------------------------------------------------------------------------

        /*
        if(file_exists($savePath)){
            //이미 파일이 있으면 그대로 리턴
            return $returnPath;
        }
        */


        $image = null;
        // 원본 이미지로부터 Image 객체 생성
        switch ($image_attr[2]){
            case 1: $image = imagecreatefromgif($this->imageFIlePath); break;
            case 2: $image = imagecreatefromjpeg($this->imageFIlePath); break;
            case 3: $image = imagecreatefrompng($this->imageFIlePath); break;
        }

        //안티얼러아어스
        if (function_exists('imageantialias')) imageantialias($image, TRUE);

        //섬네일 객체
        $thumbnail = imagecreatetruecolor($this->thumbSize['x'], $this->thumbSize['y']);
        imagealphablending($thumbnail, false); //반투명 안함
        imagecopyresampled($thumbnail, $image, $thumb_x, $thumb_y, 0, 0, $thumb_width, $thumb_height, $image_width, $image_height);
        imagesavealpha($thumbnail, true);

        switch ($image_attr[2]){
            case 1: imagegif($thumbnail, $savePath); break;
            case 2: imagepng($thumbnail, $savePath); break;
            case 3: imagejpeg($thumbnail, $savePath); break;
        }

        return $returnPath;
    }

}