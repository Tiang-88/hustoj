<?php
    /**
     * 修改人：王春祥
    * 修改日期：2021/1/18
    * 修改目的：Ajax发送post接收用
    */
	require_once('./include/cache_start.php');
    require_once('./include/db_info.inc.php');
	require_once('./include/setlang.php');
	header('content-type:text/html;charset="utf-8"');
    $time=$_POST['time'];
    $cid=(int)$_POST['cid'];
    $now=date('Y-m-d H:i:s', time());  
    $time=$now;                     ///防止恶意提交错误时间到数据库中
    $user_id=$_SESSION[$OJ_NAME.'_'.'user_id'];
    $sql = "insert into contest_login_time(`user_id`,`contest_id`,`login_time`) values (?,?,?)";
	$resultsql1 = pdo_query($sql,$user_id,$cid,$time);  
?>