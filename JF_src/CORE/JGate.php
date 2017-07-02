<?php
/**
 * Name: JGate Class
 * Desc: JFramework의 전체적인 처리를 위한 메인 클래스
 * User: jusun
 * Date: 2017-05-14
 * Time: 오후 5:32
 */

namespace JF\CORE;
use Exception;
use JF\CONF\JFConfig;
use JF\CORE\DataSet;
use JF\CORE\Error;
use JF\UTIL\FileTransfer;


class JGate{
    private $target_namespace;
    private $target_control;
    private $target_view;
    private $JFC;

    private $DataSet;
    private $route;
    private $routeVal = array();

    public function __construct(JRoute $jRoute = null) {

        //에러 핸들러러
        new Error();

        //사용할 APP의 namespace를 할당함
        //$this->target_namespace = $App_namespace;

        //DataSet 선언
        $this->DataSet = new DataSet();

        //Config for GateWay
        $this->JFC = new JFConfig();

        // Route 가져오기
        $this->route = $jRoute;

        //클래스 로딩
        $this->initialize()->uri_load()->request_load()->control_load()->view_load();
    }

    //0. 초기 구조 검사
    private function initialize(){

        //TimeZone 설정
        if($this->JFC->timeZone) {
            date_default_timezone_set($this->JFC->timeZone);
        }

        //폴더 권한 검사
        foreach($this->JFC->permissionCheckList as $k => $dir){
            if(!is_dir($dir['path'])){
                trigger_error($dir['name'] . " not existed. make path ".$dir['path']);
                if(mkdir($dir['path'],0777)){
                    trigger_error($dir['name'] . " do not have Permission. set path ".$dir['path']);
                    exit;
                }
            }else{
                $peram = decoct(fileperms($dir['path']) & 0777);
                if($peram != "777" && $peram != "707"){
                    trigger_error($dir['name'] . " do not have Permission. set path ".$dir['path']);
                    chmod($dir['path'],0777);
                    exit;
                }
            }

        }

        return $this;
    }

    // URI 분석 & route 검사
    private function uri_load(){

        //roete 활성화시
        if($this->route != null){
            $request_uri = $this->route->useRoute();
            if($request_uri != null){
                //$routeVal 파라미터 분리 및 처리
                $temp = explode("?",$request_uri);
                $temp = explode("&",$temp[1]);
                foreach($temp as $k => $val){
                    $temp2 = explode("=",$val);
                    $this->routeVal[$temp2[0]] = $temp2[1];
                }
            }else{
                //검출 되지 않았을 시 일반 호출
                $request_uri = $_SERVER['REQUEST_URI'];
            }
        }else{
            //비활성시 일반 호출
            $request_uri = $_SERVER['REQUEST_URI'];
        }


        $temp = explode("?",$request_uri);
        if(JF_URL == "/"){
            // 도메인으로 부터 시작하는 경우 /index.php
            $temp = explode(JF_URL, $temp[0]);
        }else{
            //index 경로가 다른 곳에 있을 수도 있음. ex) /admin/index.php
            $temp = explode(JF_URL, $temp[0],2);
            $temp = explode("/",$temp[1]);
        }

        if(!empty($temp[1]) or $temp[1] != ""){
            $this->target_control = $temp[1];
        }else{
            //기본 control 지정
            $this->target_control = $this->JFC->defaultControl;
        }

        if(!empty($temp[2]) or $temp[2] != ""){
            $this->target_view = $temp[2];
        }else{
            //기본 view 지정
            $this->target_view = $this->JFC->defaultView;
        }

        //페이지 컨트롤을 위해 저장해둠
        $this->DataSet->addDataRow("currentControl",$this->target_control,"JF");
        $this->DataSet->addDataRow("currentView",$this->target_view,"JF");

        //임시변수는 초기화해서 제거
        unset($temp);
        unset($temp2);
        return $this;
    }

    // 2. 각종 요청 값 수집
    private function request_load() {
        //추후 수집하는 과정에서 필터를 한번 걸어둘 필요는 있음.

        //route로 부터의 요청을 수집함.
        foreach($this->routeVal as $k => $v){
            $this->DataSet->addDataRow($k,$v);
        }

        //_GET요청을 수집함.
        foreach($_GET as $k => $v){
            $this->DataSet->addDataRow($k,$v);
        }

        //_POST요청을 수집함.
        foreach($_POST as $k => $v){
            if(preg_match("/:/",$k)){
                $tmp = explode(":",$k);
                // 테이블.필드 형태인 경우 name="tl_member:name"
                $this->DataSet->addDataRow($tmp[1],$v,$tmp[0]);
            }else{
                //그냥 넘어오는 값인 경우 ex) mode=write , mode=modify, mode=delete
                $this->DataSet->addDataRow($k,$v);
            }
        }

        //_FILES 요청을 수집함.
        foreach($_FILES as $k => $v) {
            $tmp = explode(":",$k);

            if(count($tmp) == 2){
                // 테이블.필드 형태인 경우 name="tl_member:photo"
                $this->DataSet->addDataRow($tmp[1], $v, $tmp[0]);
            }else{
                //그냥 넘어오는 값인 경우 name=uploadfile
                $this->DataSet->addDataRow($k, $v);
            }
        }

        //요청값을 초기화
        unset($this->routeVal);
        unset($_GET);
        unset($_POST);
        unset($_FILES);

        return $this;
    }

    // 3. CONTROL 로드 및 요청값 전달
    private function control_load(){

        $controlFile = $_SERVER['DOCUMENT_ROOT'];
        if(JF_URL != "/") $controlFile .= JF_URL;
        $controlFile .= DIRECTORY_SEPARATOR . "CONTROL" . DIRECTORY_SEPARATOR . $this->target_control . ".php";

        if(file_exists($controlFile)){
            //CONTROL이 있는 경우
            include_once $controlFile;

            //동적 호출
            $ref = new \ReflectionClass($this->target_control);
            $ref_class = $ref->newInstance();

            //모델클래스안에 뷰어메서드가 있는지 있는지 확인하기
            $target_view = $this->target_view;

            if($ref->hasMethod($target_view)) {
                //DataSet을 Model에 보내고 처리해서 받음
                try {
                    $this->DataSet = $ref_class->$target_view($this->DataSet);
                }catch(Exception $e){
                    trigger_error("Control do not return DataSet");
                }
            }
        } // if exist contorl
        return $this;
    }


    //4. VIEW 로드
    private function view_load(){


        //View에 표시할 데이터를 준비 하고 View를 실행함.
        if($this->DataSet != null) {
            $data = $this->DataSet->getDataSet();
        }

        $data = $this->DataSet->getDataSet();

        //$common = $data['common'];
        $JFcommon = $data['JF'];

        if($JFcommon['PrintStream'] == "none") {
            //출력없음 - 주로 프로세스를 처리하는 파일에 사용함 or 별도 템플릿 엔진 사용
        }else if($JFcommon['PrintStream'] == "json"){
            //json 출력 - 필요한 데이터만 받아서 출력할것
            echo json_encode($JFcommon['PrintData']);
        } else {
            //화면 출력인 경우 
            
            //파일 알아오기
            $controlFile = $_SERVER['DOCUMENT_ROOT'];
            if(JF_URL != "/") $controlFile .= JF_URL;
            $controlFile .= DIRECTORY_SEPARATOR . "CONTROL" . DIRECTORY_SEPARATOR . $this->target_control . ".php";
            
            $viewFile = $_SERVER['DOCUMENT_ROOT'];
            if(JF_URL != "/") $viewFile .= JF_URL;
            $viewFile .= DIRECTORY_SEPARATOR . "VIEW" . DIRECTORY_SEPARATOR . $this->target_control . DIRECTORY_SEPARATOR . $this->target_view . ".html";


            if(file_exists($viewFile)){
                include_once $viewFile;
            } else {
                //control도 없는지 검사
                if(file_exists($controlFile)){
                    // 404 screen
                    if($this->JFC->errorHTTP404Path) {
                        $data['err_msg'] = "[{$this->target_view}] View could not be found.";
                        include $this->JFC->errorHTTP404Path;
                    }else {
                        echo "<div style='color:red;'><strong>error 404 :</strong> [{$this->target_view}] View could not be found.</div>";
                    }
                }else{
                    // 404 screen
                    if($this->JFC->errorHTTP404Path) {
                        $data['err_msg'] = "[{$this->target_control}] Control, [{$this->target_view}] View could not be found.";
                        include $this->JFC->errorHTTP404Path;
                    }else {
                        echo "<div style='color:red;'><strong>error 404 :</strong> [{$this->target_control}] Control, [{$this->target_view}] View could not be found.</div>";
                    }
                }

            }
        }

        //JGate 끝.
    }

    //실행시간 측정을 위한 함수
    private function getTime(){
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    //클래스 종료시
    public function __destruct(){
        unset($this->target_contorl);
        unset($this->target_view);
        //unset($this->target_page);
        //unset($this->target_object);
        unset($this->DataSet);
    }
}