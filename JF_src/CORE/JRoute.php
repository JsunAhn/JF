<?php
/**
 * Name: JRoute Class
 * Desc: 기능 설계/작업대기중...
 * Date: 2016-12-14
 * Time: 오후 5:34
 */

namespace JF\CORE;


class JRoute{
    var $routeTable;

    /*
     * 주소 체계
     * /control/view/$1/$2/$3/$4/$5/$6
     * 감지 후 control과 view를 지정, 나머지는 개발시 활용하도록 같이 전달
     * GET에만 해당함
     */

    public function __construct(){
        $this->routeTable = array();
    }

    public function setRoute($urlPattern,$JFTarget){
        $routeTablesItem = array();
        $routeTablesItem['urlPattern'] = $urlPattern;
        $routeTablesItem['JFTarget'] = $JFTarget;
        $this->routeTable[] = $routeTablesItem;
    }

    //public function

    public function useRoute(){

        $request_uri = $_SERVER['REQUEST_URI'];

        //request_uri가 /로 끝나지 않았다면
        if(substr($request_uri,-1) != "/"){
            $request_uri = $request_uri . "/";
        }

        //반환값
        $JFTarget = null;

        //패턴 검출
        foreach($this->routeTable AS $route) {
            $length = strlen($route['urlPattern']);
            if(substr($request_uri, 0, $length) === $route['urlPattern']){
                //uri에서 패턴 제거
                $request_uri = str_replace($route['urlPattern'],"",$request_uri);

                //파리미터 분석
                $request_val = explode("/",$request_uri);
                $JFTarget = $route['JFTarget'];
                foreach($request_val as $k => $val){
                    $JFTarget = str_replace("\$".$k,$val,$JFTarget);
                }
                break;
            }
        }


        return $JFTarget;
    }
}