<?php
/**
 * Name: DataSet Class
 * Desc: 데이터 교환 및 패킹을 위해서 사용되는 객체 클래스
 * User: jusun
 * Date: 2016-12-14
 * Time: 오후 5:33
 */

namespace JF\CORE;

class DataSet{
    private $DataSet;

    public function __construct(){
        $this->DataSet = Array();
    }


    /**
     * addDataTable
     * Table 단위의 데이터를 세팅함.
     * @param $dataTable 데이터
     * @param string $dataTableName 테이블명
     */
    public function addDataTable($dataTable,$dataTableName = "common"){
        $this->DataSet[$dataTableName] = $dataTable;
    }

    /**
     * getDataTable
     * 테이블 단위로 데이터를 리턴 받음
     * @param $dataTableName
     * @return mixed
     */
    public function getDataTable($dataTableName){
        if($dataTableName != "") {
            return $this->DataSet[$dataTableName];
        }else{
            return null;
        }
    }

    /**
     * addDataRow
     * 간단한 변수/배열 형태의 데이터를 특정 테이블에 추가함
     * 열(row)형식
     * @param $rowName 열 이름 또는 인덱스
     * @param $value
     * @param string $dataTableName (생략시 common)
     */
    public function addDataRow($rowName,$value,$dataTableName = "common"){
        $this->DataSet[$dataTableName][$rowName] = $value;
    }

    /**
     * getDataRow
     * 설정된 값을 가져옴
     * @param $rowName
     * @param string $dataTableName
     * @return mixed
     */
    public function getDataRow($rowName, $dataTableName = "common") {
        return $this->DataSet[$dataTableName][$rowName];
    }

    /**
     * getDataSet
     * DataSet 전체를 반환함
     * (VIEW에 표시하기전에 사용됨)
     * @return array
     */
    public function getDataSet(){
        return $this->DataSet;
    }


    /**
     * printStream Setting
     * JGate에서 출력을 설정함 (View를 참조할때는 사용안함,json 출력시 data를 추가)
     * @param $mode
     * @param array $data
     */
    public function printStream($mode, array $data = null){
        $this->DataSet['JF']['PrintStream'] = $mode;
        $this->DataSet['JF']['PrintData'] = $data;
    }

    //객체가 글자로 텍스트로 반환시에 DataSet Object라고 표기 해줌
    public function __toString(){
        return "[::JF DataSet Object::]";
    }

    public function __destruct(){
        //소멸
        unset($this->DataSet);
    }
}