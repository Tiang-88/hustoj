 <?php
 /**  
  * 修改人：王春祥
  * 修改日期：2021/1/18
  * 修改目的：增加比赛确认界面
  */
if(isset($_POST['keyword']))
  $cache_time = 1;
else
  $cache_time = 10;

$OJ_CACHE_SHARE = false;//!(isset($_GET['cid'])||isset($_GET['my']));
require_once('./include/cache_start.php');
require_once('./include/db_info.inc.php');
require_once('./include/memcache.php');
require_once('./include/my_func.inc.php');
require_once('./include/const.inc.php');
require_once('./include/setlang.php');
$view_title= $MSG_CONTEST;

require("template/".$OJ_TEMPLATE."/error.php");
if(isset($_GET['cid']))
{
	$cid = $_GET['cid'];
	$view_cid=intval($cid);
	if($view_cid>0)
		$sql = "SELECT * FROM `contest` WHERE `contest_id`='".$cid."'";
	else
		$sql = "SELECT * FROM `contest` WHERE `contest_id`='".substr($cid,1)."'";
	$resultsql = pdo_query($sql,$cid)[0];
}
if (file_exists('./include/cache_end.php'))
	require_once('./include/cache_end.php');
?>
<html> 
<head> 
<meta charset="utf-8" /> 
<style> 
body{ text-align:left} 
.div{ margin:0 auto; width:850px; height:200px; border:1px solid #F00} 
/* css注释：为了观察效果设置宽度 边框 高度等样式 */ 
</style> 
</head> 
<body> 
<h3  style="text-align:center;">
<?php
if($view_cid<=0)
	echo "训练赛结束界面";
else
	echo "训练赛确认界面";
?>
</h3>	
<div class="div" style="<?php if($view_cid<=0) echo "display:none" ?>"> 
您还没确认参加本次比赛！<br>
在确认之前，请您仔细阅读下列要求：<br>
1.本次比赛从<?php echo $resultsql["start_time"]; ?>开始，于<?php echo $resultsql["end_time"];?>结束。<br>
2.本次比赛分为训练赛和补题赛两部分，并将于<?php echo $resultsql["ftraining_date"]; ?>开始补题赛，在此之前，您可以任选择<?php echo substr($resultsql["training_length"],0,2)."小时".substr($resultsql["training_length"],3,2)."分钟" ;?>来参加训练赛。<br>
3.计分方式：本场比赛采用ioi赛制，采用oi排行榜；补题赛期间封锁排行榜，排名不再改变，且补题赛所ac的题目均采用黄色标记。<br>
4.训练赛期间封榜运行，在榜单界面只能看到自己的分数，补题赛开始后，榜单重新开放。<br>
5.为保证公平，在比赛结束之前严禁泄露题目。<br>
6.点击确定，将代表您开始参加训练赛，计时开始。点击取消，则返回比赛列表界面。<br>
7.本文本框文字待修改完善，欢迎提出意见。
</div> 
<div class="div" style="<?php if($view_cid>0) echo "display:none" ?>"> 
您的训练赛已经结束，请等待补题赛开始！<br>
在确认之前，请您仔细阅读下列要求：<br>
1.本次比赛从<?php echo $resultsql["start_time"]; ?>开始，于<?php echo $resultsql["end_time"];?>结束。<br>
2.本次比赛分为训练赛和补题赛两部分，并将于<?php echo $resultsql["ftraining_date"]; ?>开始补题赛。<br>
3.计分方式：本场比赛采用ioi赛制，采用oi排行榜；补题赛期间封锁排行榜，排名不再改变，且补题赛所ac的题目均采用黄色标记。<br>
4.为保证公平，在比赛结束之前严禁泄露题目。<br>
5.点击确定，将跳转排行榜界面。点击取消，则返回比赛列表界面。<br>
6.本文本框文字待修改完善，欢迎提出意见。
</div> 
<br>
<div align="center">
<?php if($view_cid>0)
		echo '<button name="btn" type="submit" id="btn" style="width:100px;  ">确定</button>';
	else
		echo '<button onclick="window.location.href=\'./contestrank-oi.php?cid='.substr($cid,1).'\'" name="submit" type="submit" style="width:100px;">确定</button>';
?>
<?php for($i=0;$i<=5;$i++) 
	echo "&nbsp"; 
?>
<button onclick="window.location.href='./contest.php'" name="submit" type="submit" style="width:100px;">取消</button>
</div>
<br>
</body> 
<script src="https://s3.pstatp.com/cdn/expire-1-M/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript">
		function current() {
				var d = new Date(),
					str = '';
				str += d.getFullYear() + '-';
				str += d.getMonth() + 1 + '-';
				str += d.getDate() + ' ';
				str += d.getHours() + ':';
				str += d.getMinutes() + ':';
				str += d.getSeconds() + '';
				return str;
		}
		var oBtn=document.getElementById("btn");
		oBtn.onclick=function()
		{				//给按钮设置点击事件
			var jsonObj = {"time":current(),"cid":<?php echo $cid; ?> };
			myajax=$.ajax({
				type:"post",						//提交方式
				url:"contest_login_post.php",		//执行的url(控制器/操作方法)
				async:true,							//是否异步
				data:jsonObj,						//获取form表单的数据
				datatype:'json',					//数据格式
				success:function(data){
					console.log(data);				//打印
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					alert(XMLHttpRequest.status);
					alert(XMLHttpRequest.readyState);
					alert(textStatus);
				}
			});
			$.when(myajax).done(function () {
				window.location.href = './contest.php?cid=<?php echo $cid; ?>';
        	});
			return false;
		}
	</script>
</html> 