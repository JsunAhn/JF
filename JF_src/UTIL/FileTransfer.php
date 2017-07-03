<?php
/**
 * Name: FileTransfer Class
 * Desc: 파일업로드/다운로드용 유틸리티
 * User: jusun
 * Date: 2016-12-14
 * Time: 오후 5:37
 */

namespace JF\UTIL;


use JF\CONF\JFConfig;

class FileTransfer{
    var $JFC;
    var $defaultUploadPath;
    var $defaultDataPath;


    /**
     * FileTransfer constructor.
     * @param $targetFolder 업로드 할 폴더를 지정  ex) bbs/test01, upfolder ...
     */
    public function __construct($targetFolder){
        $this->JFC = new JFConfig();
        $this->defaultUploadPath = $this->JFC->defaultDataPath . DIRECTORY_SEPARATOR . $targetFolder;
        $this->defaultDataPath = $this->JFC->defaultDataPath;

    }

    //업로드용으로 사용할 폴더 생성
    public function makePath($upload_dir) {
        $tmp = explode(DIRECTORY_SEPARATOR, $upload_dir);
        $tmp_path = $this->defaultDataPath;
        foreach($tmp AS $dir) {
            $tmp_path .= DS . $dir;
            if(!file_exists($tmp_path)) {
                mkdir($tmp_path, 0707);
                chmod($tmp_path, 0707);
            }else{
                chmod($tmp_path, 0707);
            }
        }
    }

    //서버 저장 파일명 만들기
    public function makeFilename($file_source, $i=null) {
        $nTime = time();
        $extension = $this->getExtension($file_source);
        $idx = ($i==null) ? "" : $i."_";
        $file_name = substr(preg_replace("/[^\d\w]/", "", base64_encode($file_source)), 0, 50);
        $file_name = $nTime."_".$idx.$file_name.".".$extension;
        return $file_name;
    }

    //확장자 가져오기 실행파일일 경우에는 확장자명 뒤에 __ 를 붙이도록 한다.(실행방지)
    public function getExtension($file_source) {
        preg_match("/.([a-z0-9_]+)$/i", $file_source, $match);
        $extension = $match[1];
        if(preg_match("/(php|exe|html|htm|ini|jsp|asp|js)/i", $extension, $match)) {
            $extension = $match[1]."__";
        }
        return strtolower($extension);
    }


    // JGate에서 DateSet 를 이용하여 파일 업로드(단일 파일 업로드)
    public function uploadFile($fList) {
        $this->makePath($this->defaultDataPath);

        $newfList = array();
        $tmp_name = $fList['tmp_name'];
        $file_size = $fList['size'];
        $file_source = $fList['name'];
        $file_type = $fList['type'];
        $file_error = $fList['error'];

        if(is_uploaded_file($tmp_name)) {
            $file_name = $this->makeFilename($file_source);
            $dest_path = $this->defaultUploadPath."/".$file_name;

            move_uploaded_file($tmp_name, $dest_path);
            chmod($dest_path, 0606);

            if($file_name && file_exists($dest_path)) {
                $newfList['name'] = $file_name;
                $newfList['source'] = $file_source;
                $newfList['destPath'] = $dest_path;
                $newfList['webPath'] = str_replace($_SERVER['DOCUMENT_ROOT'],"",$dest_path);
                $newfList['size'] = $file_size;
                $newfList['type'] = $file_type;
                $newfList['error'] = $file_error;
                //web_path 필요함.
            }
        }

        return $newfList;
    }

    //파일 다운로드 개선 jusun
    //이미지 테그도 됨.
    function downloadFile($fileSource, $downloadFileName) {

        ini_set('zlib.output_compression', 'Off');

        $dest_path = $this->defaultUploadPath. DIRECTORY_SEPARATOR .$fileSource;

        if($fileSource && file_exists($dest_path)) {


            $fsize = filesize($dest_path);
            $path_parts = pathinfo($dest_path);
            $ext = strtolower($path_parts["extension"]);

            switch ($ext){
                case "pdf": $ctype="application/pdf"; break;
                case "exe": $ctype="application/octet-stream"; break;
                case "zip": $ctype="application/zip"; break;
                case "doc": $ctype="application/msword"; break;
                case "xls": $ctype="application/vnd.ms-excel"; break;
                case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
                case "gif": $ctype="image/gif"; break;
                case "png": $ctype="image/png"; break;
                case "jpeg":
                case "jpg": $ctype="image/jpg"; break;
                default: $ctype="application/force-download";
            }

            header("Pragma: public"); // required
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false); // required for certain browsers
            header("Content-Type: $ctype");
            header("Content-Disposition: attachment; filename=\"".$downloadFileName."\";" );
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: ".$fsize);
            ob_clean();
            flush();

            $handle = fopen($dest_path, 'rb');
            fpassthru($handle);
            fclose($handle);

        } else {
            return false;
        }
    }

    //ftp ???

}