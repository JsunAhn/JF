<?php
use JF\BASE\BaseWebControl;
use JF\CORE\DataSet;

/**
 * Title :
 * Author : jusun
 * Date: 2017-06-13
 * Time: 오후 4:30
 */

class ee extends BaseWebControl{

    var $db;
    var $JFC;

    public function index(DataSet $dataSet){

        $param = array();
        $param['mb_idx'] = "2";
        $tr_member = $this->db->doSelect("tr_member.select_one",$param);
        var_dump($tr_member);

        $dataSet->printStream("none");
        return $dataSet;
    }

}