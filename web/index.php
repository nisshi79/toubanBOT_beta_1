<?php

require('../vendor/autoload.php');

use Carbon\Carbon;

$dt = new Carbon();
const DAY = 1;
const WEEK = 7;

function getMJD() {
        global $dt;
        $D = $dt->day;//日
        $M = $dt->month;//月
        $Y = $dt->year;//年
        var_dump($Y);
        var_dump($M);
        var_dump($D);

        if ($M == 1 || $M == 2) {
            $Y = $Y - 1;
            $M = $M + 12;
        }
        $A = floor($Y / 100);
        var_dump($A);
        $B = 2 - $A + floor($A / 4);
        var_dump($B);
        $JD = floor(365.25 * $Y) + floor(30.6001 * ($M + 1)) + $D + $B + 1720994.5;
        var_dump($JD);
        $jD = floor($JD - 2400000.5);

        var_dump($jD);
        return $jD;
}

$cW = floor((getMJD()+3)/7);
$toubanNotfication = '今日の掃除は'."\n";


class toubanTable{
    public $itemNum;
    public $memberNum;
    public $rotateNum;
    public $perWhat;
    public $firstJD;
    public $firstW;
    public $rotation;

    function __construct($itemNum, $memberNum, $rotateNum, $perWhat, $firstJD){
        $this->itemNum = $itemNum;
        $this->memberNum = $memberNum;
        $this->rotateNum = $rotateNum;
        $this->perWhat = $perWhat;
        $this->firstJD = $firstJD;
        $this->firstW = floor(($firstJD + 3) / 7);
    }

    function getMID($iID){
        $this->rotation = $GLOBALS['cW'] - $this->firstW;
        $buffer =  (($this->rotation * $this->rotateNum)%max($this->memberNum,$this->itemNum));
        if($buffer < 0)$buffer + ($this->rotation * $this->rotateNum);
        return $iID - $buffer;
    }

    function output(){
        for($i = 1; $i <= $this -> itemNum; $i++){
            if($this->getMID($i) != 0 && $this->getMID($i) <= $this->memberNum) $GLOBALS['toubanNotfication'] .= "$i".'番目の役割は'.$this->getMID($i).'さんが当番です'."\n";
        }
    }
}

function time_diff($time_from, $time_to)
{
    // 日時差を秒数で取得
    $dif = $time_to - $time_from;
    // 時間単位の差
    $dif_time = date("H:i:s", $dif);
    // 日付単位の差
    $dif_days = (strtotime(date("Y-m-d", $dif)) - strtotime("1970-01-01")) / 86400;
    return "{$dif_days}days {$dif_time}";
}



$itemNums = [1, 3, 5];
$memberNums = [3, 2, 5];
$rotateNums = [1, 2, 3];
$perWhat = [WEEK, WEEK, WEEK];



for($i = 0; $i != count($itemNums); $i++){
    $toubanTable[$i] = new toubanTable($itemNums[$i],$memberNums[$i],$rotateNums[$i],$perWhat[$i],getMJD());
    $toubanTable[$i]->output();
}



$post_data = array(
    "value1" => "$toubanNotfication",
    "value2" => getMJD(),
    "value3" => "rr"
);
//IFTTT
$ch = curl_init('https://maker.ifttt.com/trigger/toubanbot1/with/key/rBrhvXD3WeFcdEEwJl6ht');

curl_setopt($ch,CURLOPT_POST, true);

//データの配列を設定する
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));

curl_exec($ch);
curl_close($ch);

