<?php
/**
 * Name: SqlMapper Class
 * Desc: 데이터베이스 쿼리용 맵퍼,
 * 참고: FP BATIS ( https://github.com/adamldoyle/FPBatis )
 * User: jusun
 * Date: 2016-12-13
 * Time: 오전 4:54
 */

namespace JF\ORM;


use DOMDocument;
use PDO;

class SqlMapper{
    static private $PDOInstance; // Connection Instance

    private $sqlMap; // Filename for the main sqlMap file
    private $xmlDoc; // Loaded sqlMap file
    private $namespaces; // Associate array of namespace files
    private $debug = false; // Display all SQL statements
    //private $config; // config

    public function __construct($sqlMap,$createConn=true) {
        $this->sqlMap = $sqlMap;
        $this->conn = null;
        $this->xmlDoc = new DOMDocument();
        $this->xmlDoc->load($this->sqlMap);
        if ($createConn) {
            $this->createConnection();
        }
        $this->buildNamespaces();

        //$this->config = new Config("SqlMap");
        //$this->debug = $this->config->SqlMap["QueryDebug"];
    }

    function createConnection() {
        $propertyTags = $this->xmlDoc->getElementsByTagName('property');
        $properties = array();
        foreach ($propertyTags as $tag) {
            $properties[$tag->getAttribute('name')] = $tag->getAttribute('value');
            //echo $tag->getAttribute('value') . "<br />";
        }
        $serverSpecs = explode('/', $properties['JDBC.ConnectionURL']);
        $server = explode(':',$serverSpecs[2]);

        //이부분 커넥션 다른 DB를 사용하기 위해서 손봐야함
        if(!self::$PDOInstance) {
            try {
                self::$PDOInstance = new PDO('mysql:host=' . $server[0] . ";port=" . $server[1] . ";dbname=" . $serverSpecs[3], $properties['JDBC.Username'], $properties['JDBC.Password']);
            }catch(\PDOException $e){
                trigger_error("PDO Connection Error: ".$e->getMessage());
                exit();
            }
        }

        //인코딩 설정
        /*
        if($this->config->SqlMap["Charset"] != "") {
            $this->exec("SET NAMES {$this->config->SqlMap["Charset"]}");
        }

        //타임존 설정
        if($this->config->SqlMap["Timezone"] != ""){
            $this->exec("SET time_zone={$this->config->SqlMap["Timezone"]}");
        }
        */

    }

    public function setDebug($debug) {
        $this->debug = $debug;
    }

    //실행 쿼리
    public function exec($stmt){
        try{
            self::$PDOInstance->exec($stmt);
            $errorInfo = self::$PDOInstance->errorInfo();
            if($errorInfo[0] != "00000") die("Query: ".$stmt."<br>\nError: ".$errorInfo[2]);
        }catch(\PDOException $e){

            if($this->debug){
                echo "exec Query : " . $stmt . "<br />";
                echo "SqlMapper Error : " . $e->getMessage() . "<br/>";
            }
            die();
        }
    }

    //반환 쿼리
    public  function query($stmt){
        try{
            $result = self::$PDOInstance->query($stmt, PDO::FETCH_ASSOC);
            $errorInfo = self::$PDOInstance->errorInfo();
            if($errorInfo[0] != "00000") die("Query: ".$stmt."<br>\nError: ".$errorInfo[2]);
            return $result;
        }catch(\PDOException $e){
            if($this->debug){
                echo "return Query : " . $stmt . "<br />";
                echo "SqlMapper Error : " . $e->getMessage() . "<br/>";
            }
            die();
        }
    }

    function buildNamespaces() {
        $sqlMapConfig = $this->xmlDoc->getElementsByTagName('sqlMapConfig');
        $sqlMapConfig = $sqlMapConfig->item(0);

        if (strrpos($this->sqlMap, '/') !== false)
            $dir = substr($this->sqlMap, 0, strrpos($this->sqlMap, '/')+1);
        else
            $dir = '';
        $maps = $sqlMapConfig->getElementsByTagName('sqlMap');
        foreach($maps as $map) {
            $ext = $dir . $map->getAttribute('resource');
            $tempDoc = new DomDocument();
            $tempDoc->load($ext);
            $node = $tempDoc->getElementsByTagName('sqlMap')->item(0);
            $this->namespaces[$node->getAttribute('namespace')] = $node;
        }
    }

    function findMapElement($namespace, $tagName, $id) {
        $map = $this->namespaces[$namespace];
        if ($map != '') {
            foreach ($map->getElementsByTagName($tagName) as $elem) {
                if ($elem->getAttribute('id') == $id) {
                    return $elem;
                }
            }
        }
        return null;
    }

    function applyDynamicElement($item, $params, $dynamic) {
        $stmt = '';
        if ($item->getAttribute('open') != null)
            $stmt .= ' ' . $item->getAttribute('open');
        if (!$dynamic && $item->getAttribute('prepend') != null)
            $stmt .= $item->getAttribute('prepend') . ' ';
        if ($item->nodeName == 'dynamic')
            $dynamic = true;
        $stmt .= $this->buildUpStatement($item, $params, $dynamic);
        if ($item->getAttribute('close') != null)
            $stmt .= $item->getAttribute('close') . ' ';
        return $stmt;
    }

    function buildUpStatement($elm, $params, $dynamic=false) {
        $childTags = array('#text','include','dynamic','iterate','isParameterPresent',
            'isNotParameterPresent','isEmpty','isNotEmpty','isNull','isNotNull',
            'isEqual','isNotEqual','isGreaterThan','isGreaterEqual','isLessThan',
            'isLessEqual','isPropertyAvailable','isNotPropertyAvailable');

        $stmt = '';
        foreach ($elm->childNodes as $item) {
            switch($item->nodeName) {
                case '#text':
                    if(preg_replace('/\s\s+/', '', $item->nodeValue) != '')
                        $stmt .= preg_replace('/\s\s+/', ' ', $item->nodeValue);
                    break;
                case 'dynamic':
                    $subStmt = $this->buildUpStatement($item, $params,true);
                    if (preg_replace('/\s\s+/', '', $subStmt) != '') {
                        $stmt .= $this->applyDynamicElement($item, $params, $dynamic);
                        $dynamic = false;
                    }
                    break;
                case 'iterate':
                    if (!empty($params[$item->getAttribute('property')])) {
                        $subStmt = '';

                        $paramList = $params[$item->getAttribute('property')];
                        $size_list = sizeof($paramList);
                        for ($i = 0; $i < $size_list; $i++) {
                            $param = $paramList[$i];
                            $params[$item->getAttribute('property') . '[]'] = $param;
                            $sub = $this->buildUpStatement($item, $params, $dynamic);
                            $pieces = explode("#", $item->nodeValue);
                            if (sizeof($pieces)>1) {
                                $sub = $pieces[0];
                                for ($j = 1; $j < sizeof($pieces); $j+=2) {
                                    $sub .= "'" . $params[$pieces[$j]] . "'" . $pieces[$j+1];
                                }
                            }
                            if ($item->getAttribute('conjunction') != null && $i != 0)
                                $subStmt .= $item->getAttribute('conjunction');
                            $subStmt .= $sub;
                        }

                        if ($subStmt != '') {
                            if ($item->getAttribute('open') != null)
                                $subStmt = $item->getAttribute('open') . $subStmt;
                            if ($item->getAttribute('close') != null)
                                $subStmt .= $item->getAttribute('close');
                            if ($dynamic)
                                $dynamic = false;
                            else if ($item->getAttribute('prepend') != null)
                                $stmt .= $item->getAttribute('prepend');
                            $stmt .= $subStmt;
                            if ($item->getAttribute('append') != null)
                                $stmt .= $item->getAttribute('append');
                            $dynamic = false;
                        }
                    }
                    break;
                case 'isNotEmpty':
                case 'isParameterPresent':
                case 'isPropertyAvailable':
                    if (!empty($params[$item->getAttribute('property')])) {
                        $stmt .= $this->applyDynamicElement($item, $params, $dynamic);
                        $dynamic = false;

                    }
                    break;
                case 'isEmpty':
                case 'isNotParameterPresent':
                case 'isNotPropertyAvailable':
                    if (empty($params[$item->getAttribute('property')])) {
                        $stmt .= $this->applyDynamicElement($item, $params, $dynamic);
                        $dynamic = false;
                    }
                    break;
                case 'isNull':
                    if ($params[$item->getAttribute('property')] === null) {
                        $stmt .= $this->applyDynamicElement($item, $params, $dynamic);
                        $dynamic = false;
                    }
                    break;
                case 'isNotNull':
                    if ($params[$item->getAttribute('property')] !== null) {
                        $stmt .= $this->applyDynamicElement($item, $params, $dynamic);
                        $dynamic = false;
                    }
                    break;
                default:

                    break;
            }
        }
        return $stmt;
    }

    function doSelect($id, $params=null, $debug=false) {
        $ids = explode(".", $id);
        if ($elm = $this->findMapElement($ids[0], 'select', $ids[1])) {
            //$stmt = $elm->nodeValue;
            $class = $elm->getAttribute('parameterClass');

            $stmt = $this->buildUpStatement($elm, $params);

            $pieces = explode("$", $stmt);
            if (sizeof($pieces)>1) {
                $stmt = $pieces[0];
                switch($class) {
                    case '':
                    case 'array':
                        for ($i = 1; $i < sizeof($pieces); $i+=2) {
                            $stmt .= "" . $params[$pieces[$i]] . "" . $pieces[$i+1];
                        }
                        break;
                    default:
                        for ($i = 1; $i < sizeof($pieces); $i+=2) {
                            $stmt .= "" . $params . "" . $pieces[$i+1];
                        }
                        break;
                }
            }

            $pieces = explode("#", $stmt);
            if (sizeof($pieces)>1) {
                $stmt = $pieces[0];
                switch($class) {
                    case '':
                    case 'array':
                        for ($i = 1; $i < sizeof($pieces); $i+=2) {
                            $stmt .= "'" . $params[$pieces[$i]] . "'" . $pieces[$i+1];
                        }
                        break;
                    default:
                        for ($i = 1; $i < sizeof($pieces); $i+=2) {
                            $stmt .= "'" . $params . "'" . $pieces[$i+1];
                        }
                        break;
                }
            }

            $resultMap = $elm->getAttribute('resultMap');

            //hashMap 기능 추가
            if($resultMap == "hashMap"){

                $stmt = str_replace("\r\n"," ",$stmt);
                if ($debug || $this->debug)
                    echo 'DEBUG: ' . $stmt . '<br/>';

                $result = $this->query($stmt);
                foreach ($result as $row){
                    $results[] = $row;
                }
                return $results;
            }
            //hashmap 방식만 유지함.
            /*
            else if ($resultMap = $this->findMapElement($ids[0], 'resultMap', $resultMap)) {
                $resultTagsArry[] = $resultMap->getElementsByTagName('result');
                while ($resultMap->getAttribute('extends') != null) {
                    if ($resultMap = $this->findMapElement($ids[0], 'resultMap', $resultMap->getAttribute('extends'))) {
                        $resultTagsArry[] = $resultMap->getElementsByTagName('result');
                    }
                }
                $stmt = str_replace("\r\n"," ",$stmt);
                if ($debug || $this->debug)
                    echo 'DEBUG: ' . $stmt . '<br/>';
                $result = mysql_query($stmt, $this->conn) or die('There was an error running your SQL statement: ' . $stmt);
                $num_rows = mysql_numrows($result);
                $results = array();
                for($i=0; $i<$num_rows; $i++) {
                    $resultElm = array();
                    foreach ($resultTagsArry as $resultTags) {
                        foreach ($resultTags as $resultTag) {
                            if ($resultTag->getAttribute('select') == null) {
                                $resultElm[$resultTag->getAttribute('property')] = mysql_result($result,$i,$resultTag->getAttribute('column'));
                            } else {
                                $columns = array();
                                $column = rtrim(trim($resultTag->getAttribute('column'),'{'),'}');
                                if (strpos($column,'=') === false) {
                                    $resultElm[$resultTag->getAttribute('property')] = $this->doSelect($resultTag->getAttribute('select'), mysql_result($result,$i,$column));
                                } else {
                                    foreach (explode(',',$column) as $piece) {
                                        $colPieces = explode('=',$piece);
                                        $columns[$colPieces[0]] = mysql_result($result,$i,$colPieces[1]);
                                    }
                                    $resultElm[$resultTag->getAttribute('property')] = $this->doSelect($resultTag->getAttribute('select'), $columns);
                                }
                            }
                        }
                    }
                    $results[] = $resultElm;
                }
                return $results;
            }
            */
        }
        return null;
    }

    /**
     * Perform an insert given an array of variables and an insert id to
     * use, returns the object back (null if incorrect id).
     */
    function doInsert($id, $obj, $fromForm=false) {
        $ids = explode(".", $id);
        if ($elm = $this->findMapElement($ids[0], 'insert', $ids[1])) {
            $elm = $elm->cloneNode(true);
            if ($subStmt = $elm->getElementsByTagName('selectKey')->item(0)) {
                $elm->removeChild($subStmt);
            }
            //$stmt = $elm->nodeValue;
            $stmt = $this->buildUpStatement($elm, $obj);

            $pieces = explode("$", $stmt);
            $stmt = $pieces[0];
            for ($i = 1; $i < sizeof($pieces); $i+=2) {
                if ($fromForm)
                    $obj[$pieces[$i]] = $this->param($pieces[$i]);
                $stmt .= $obj[$pieces[$i]] . $pieces[$i+1];
            }

            $pieces = explode("#", $stmt);
            $stmt = $pieces[0];
            for ($i = 1; $i < sizeof($pieces); $i+=2) {
                if ($fromForm)
                    $obj[$pieces[$i]] = $this->param($pieces[$i]);
                //$stmt .= "'" . mysql_real_escape_string(htmlspecialchars($obj[$pieces[$i]], ENT_QUOTES)) . "'" . $pieces[$i+1];
                //$stmt .= "'" . htmlspecialchars($obj[$pieces[$i]], ENT_QUOTES) . "'" . $pieces[$i+1];
                //$stmt .= "'" . $obj[$pieces[$i]] . "'" . $pieces[$i+1];
                $stmt .= "'" . addslashes($obj[$pieces[$i]]) . "'" . $pieces[$i+1];
            }

            if ($this->debug) {
                echo 'DEBUG: ' . $stmt . '<br/>';
            }

            $this->exec($stmt);
            if ($subStmt != null) {
                if ($this->debug)
                    echo 'DEBUG: ' . $subStmt->nodeValue . '<br/>';

                $result = $this->query($subStmt->nodeValue);
                foreach($result AS $row) {
                    $results[] = $row;
                }

                $obj[$subStmt->getAttribute('keyProperty')] = $results[0][$subStmt->getAttribute('keyProperty')];
            }
            return $obj;
        }
        return null;
    }

    /**
     * Similar to insert, but for updates.
     */
    function doUpdate($id, $obj, $fromForm=false) {
        $ids = explode(".", $id);
        if ($elm = $this->findMapElement($ids[0], 'update', $ids[1])) {
            //$stmt = $elm->nodeValue;
            $stmt = $this->buildUpStatement($elm, $obj);

            $pieces = explode("$", $stmt);
            $stmt = $pieces[0];
            for ($i = 1; $i < sizeof($pieces); $i+=2) {
                if ($fromForm)
                    $obj[$pieces[$i]] = $this->param($pieces[$i]);
                $stmt .= $obj[$pieces[$i]] . $pieces[$i+1];
            }

            $pieces = explode("#", $stmt);
            $stmt = $pieces[0];
            for ($i = 1; $i < sizeof($pieces); $i+=2) {
                if ($fromForm)
                    $obj[$pieces[$i]] = $this->param($pieces[$i]);
                //$stmt .= "'" . mysql_real_escape_string(htmlspecialchars($obj[$pieces[$i]], ENT_QUOTES)) . "'" . $pieces[$i+1];
                //$stmt .= "'" . htmlspecialchars($obj[$pieces[$i]], ENT_QUOTES) . "'" . $pieces[$i+1];
                //$stmt .= "'" . $obj[$pieces[$i]] . "'" . $pieces[$i+1];
                $stmt .= "'" . addslashes($obj[$pieces[$i]]) . "'" . $pieces[$i+1];
                //$stmt .= "'" . $this->conn->quote($obj[$pieces[$i]]) . "'" . $pieces[$i+1];

            }
            if ($this->debug)
                echo 'DEBUG: ' . $stmt . '<br/>';
            $this->exec($stmt);
            return $obj;
        }
        return null;
    }

    /**
     * Similar to insert, but for deletes. Returns true if successful,
     * null if id not valid.
     */
    function doDelete($id, $obj) {
        $ids = explode(".", $id);
        if ($elm = $this->findMapElement($ids[0], 'delete', $ids[1])) {
            //$stmt = $elm->nodeValue;
            $stmt = $this->buildUpStatement($elm, $obj);

            $pieces = explode("$", $stmt);
            $stmt = $pieces[0];
            for ($i = 1; $i < sizeof($pieces); $i+=2) {
                $stmt .= $obj[$pieces[$i]] . $pieces[$i+1];
            }

            $pieces = explode("#", $stmt);
            $stmt = $pieces[0];
            for ($i = 1; $i < sizeof($pieces); $i+=2) {
                $stmt .= "'" . $obj[$pieces[$i]] . "'" . $pieces[$i+1];
            }
            if ($this->debug)
                echo 'DEBUG: ' . $stmt . '<br/>';

            $this->exec($stmt);
            return true;
        }
        return null;
    }

    /**
     * 쿼리를 직접 입력할 때 사용함.
     * @param $stmt
     * @return mixed
     */
    function &customQuery($stmt) {
        $result = $this->query($stmt);
        return $result;
    }

    function &customSelect($stmt, $type=MYSQL_ASSOC) {
        $result =& $this->customQuery($stmt);
        $results = array();
        $result = $this->query($stmt);
        foreach($result as $row){
            $results[] = $row;
        }
        return $results;
    }


}