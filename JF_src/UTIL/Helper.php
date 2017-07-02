<?php
/**
 * Name: Helper Class
 * Desc: 각종 복잡한 기능들을 대신 만들어주는 유틸리티(작업중)
 * User: jusun
 * Date: 2016-12-14
 * Time: 오후 6:00
 */

namespace JF\UTIL;


class Helper{

    //오류 표출
    public static function alertMsg($str, $url="") {
        $script = "<script>\n";
        $script .= "window.alert(\"{$str}\");\n";
        if(empty($url)) {
            $script .= "history.go(-1);\n";
        } else {
            $script .= "document.location.replace(\"{$url}\");\n";
        }
        $script .= "</script>\n";
        Helper::html_alert_utf8($script);
        exit;
    }

    //이동
    public static function go($url){
        $script = "
		<script>
		window.location.replace(\"{$url}\");
		</script>
		";
        Helper::html_alert_utf8($script);
        exit;
    }

    //html 출력용(각종용도)
    public static function html_alert_utf8($script){
        echo ("<!doctype html>
            <html>
            <head>
                <meta charset=\"UTF-8\">
                {$script}
            </head>
            <body></body>
            </html>");
    }

    //페이지네이션 만들기
    /*
     * 한페이지에 보여줄 페이지수, 현재페이지, 총페이지수, 기본 URL, 추가 URL
     * 기본 사용방법
     * Utils::getPaging(10, $page, $total_page, $_SERVER['PHP_SELF']."bbs_id=test&page=", "&add_method=add");
     * $write_pages -> 페이징숫자가 총 몇개 보여질지에 대한 정의
     * $cur_page -> 현재의 페이지
     * $total_page -> 총페이지의 수
     * $url -> 페이징 할때 들어가는 기본적인 URL 해당 변수에 값을 넣을때는 반드시 page변수명= 으로 끝나야함.
     * $add -> $url 에 페이징을 붙이고 그뒤 추가적으로 표현할 것이 있을 경우 넣는 부분
     *
     * 이를 응용하면 javascript 에서 페이징 처리가 가능함
     *
     * Utils::getPaging(10, $page, $total_page, "javascript:ajaxList('", "')");
     */
    static function getPaging($write_pages, $cur_page, $total_page, $url, $add="")
    {
        $str = "";
        if ($cur_page > 1) {
            $str .= "<a href=\"" . $url . "1". $add . "\" title='처음'>처음</a>\n";
        }

        $start_page = ( ( (int)( ($cur_page - 1 ) / $write_pages ) ) * $write_pages ) + 1;
        $end_page = $start_page + $write_pages - 1;

        if ($end_page >= $total_page) $end_page = $total_page;

        if ($start_page > 1) $str .= "<a href=\"" . $url . ($start_page-1) . $add."\" title='이전' class=\"arrow\">&lt;</a>\n";

        //if ($total_page > 1) {
        for ($k=$start_page;$k<=$end_page;$k++) {
            if ($cur_page != $k)
                $str .= "<a href=\"". $url . $k . $add . "\">" . $k . "</a>\n";
            else
                //$str .= "<span>" . $k . "</span>\n";
                $str .= "<a href=\"". $url . $k . $add . "\" class=\"on\">" . $k . "</a>\n";
        }
        //}

        if ($total_page > $end_page) $str .= "<a href=\"" . $url . ($end_page+1) . $add . "\" title='다음' class=\"arrow\">&gt;</a>\n";

        if ($cur_page < $total_page) {
            $str .= "<a href=\"" . $url . $total_page . $add . "\" title='맨끝'>맨끝</a>\n";
        }
        $str .= "";

        return $str;
    }

    //문자열 자르기
    public static function cutString($str, $len, $suffix = "...") {
        $c = substr(str_pad(decbin(ord($str{$len})),8,'0',STR_PAD_LEFT),0,2);
        if ($c == '10')
            for (;$c != '11' && $c{0} == 1;$c = substr(str_pad(decbin(ord($str{--$len})),8,'0',STR_PAD_LEFT),0,2));
        return substr($str,0,$len) . (strlen($str)-strlen($suffix) >= $len ? $suffix : '');
    }

    //필드 체크


    //레퍼러 체크

}