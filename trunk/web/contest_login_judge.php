<?php
/**
* 修改人：王春祥
* 修改日期：2021/1/18
* 修改目的：判断是否已经确定参加比赛
* 主要修改内容：判断sql中是否有数据，如果没有，跳转cntest_login
*/
require_once('./include/cache_start.php');
require_once('./include/db_info.inc.php');
require_once('./include/memcache.php');
require_once('./include/my_func.inc.php');
require_once('./include/const.inc.php');
require_once('./include/setlang.php');

$login_ok=false;
$user_id=$_SESSION[$OJ_NAME.'_'.'user_id'];

$sql = "SELECT * FROM `contest_login_time` WHERE `contest_id`=? and `user_id`=?";
$resultsql_login= pdo_query($sql,$cid,$user_id);
$login_time=strtotime($resultsql_login[0]["login_time"]);

$sql = "SELECT * FROM `contest` WHERE `contest_id`=?";
$resultsql = pdo_query($sql,$cid);
$rowsql=$resultsql[0];
$now = time();
$training_length=$rowsql['training_length']; //训练时长
$ftraining_date=strtotime($rowsql['ftraining_date']); //补题赛时间
$start_time = strtotime($rowsql['start_time']);
$end_time = strtotime($rowsql['end_time']);
$view_description = $rowsql['description'];
$view_title = $rowsql['title'];
$view_start_time = $rowsql['start_time'];
$view_end_time = $rowsql['end_time'];
if(count($resultsql_login)!=0)
{
    $end_time1=(int)((intval(substr($training_length,0,2))*60*60+intval(substr($training_length,3,2))*60+$login_time));
    if($now>$end_time1&&$now<$ftraining_date&&!(isset($_SESSION[$OJ_NAME.'_'.'administrator']) || isset($_SESSION[$OJ_NAME.'_'.'contest_creator'])))
    {
        header("Location: ./contest_login.php?cid="."-".$cid);
    }
    $login_ok=true;
}
if (isset($_SESSION[$OJ_NAME.'_'.'administrator']) || isset($_SESSION[$OJ_NAME.'_'.'contest_creator'])) ///管理员无此限制
    $login_ok=true;
if($now>=$ftraining_date)
    $login_ok=true;
if(!$login_ok)
    header('Location: ./contest_login.php?cid='.$cid);
    
//结束
?>