<?php
/**
 * 修改人：王春祥
 * 修改时间：2021/1/18
 * 修改目的：修改oi排行榜
 */
$OJ_CACHE_SHARE = false;
$cache_time = 10;
require_once './include/cache_start.php';
require_once './include/db_info.inc.php';
require_once './include/setlang.php';
$view_title = $MSG_CONTEST . $MSG_RANKLIST;
$title = "";
require_once "./include/const.inc.php";
require_once "./include/my_func.inc.php";

class TM
{
    public $solved = 0; ///训练赛ac题目
    public $fsolved = 0; ///补题赛ac题目
    public $time = 0;
    public $p_wa_num;
    public $p_ac_sec; ///训练赛ac题目
    public $p_pass_rate; ///训练赛的ac率
    public $fp_ac_sec; ///补题赛ac题目
    public $fp_pass_rate; ///补题赛的ac率
    public $user_id;
    public $nick;
    ///改
    public $total; //训练赛成绩
    public $all_total; //训练赛+补题赛成绩
    public $login_date;
    public function TM()
    {
        $this->solved = 0;
        $this->fsolved = 0;
        $this->time = 0;
        $this->p_wa_num = array();
        $this->p_ac_sec = array();
        $this->p_pass_rate = array();
        $this->fp_ac_sec = array();
        $this->fp_pass_rate = array();
        $this->total = 0;
        $this->all_total = 0;
    }
    public function Add($pid, $sec, $res, $ftraining_date)
    {
        //echo $ftraining_date;
        //              echo "Add $pid $sec $res<br>";
        if (isset($this->p_ac_sec[$pid]) && $this->p_ac_sec[$pid] > 0) {
            return;
        }

        if (isset($this->fp_ac_sec[$pid]) && $this->fp_ac_sec[$pid] > 0) {
            return;
        }

        if ($sec <= $ftraining_date) ///在训练赛时期
        {
            if ($res * 100 < 99) {
                if (isset($this->p_pass_rate[$pid])) {
                    if ($res > $this->p_pass_rate[$pid]) {
                        $this->total -= $this->p_pass_rate[$pid] * 100;
                        $this->p_pass_rate[$pid] = $res;
                        $this->fp_pass_rate[$pid] = $res;
                        $this->total += $this->p_pass_rate[$pid] * 100;
                        $this->all_total = $this->total;
                    }
                } else {
                    $this->p_pass_rate[$pid] = $res;
                    $this->fp_pass_rate[$pid] = $res;
                    $this->total += $res * 100;
                    $this->all_total = $this->total;
                }
                if (isset($this->p_wa_num[$pid])) {
                    $this->p_wa_num[$pid]++;
                } else {
                    $this->p_wa_num[$pid] = 1;
                }

            } else {
                $this->p_ac_sec[$pid] = 1;
                $this->solved++;
                $this->fsolved = $this->solved;
                if (!isset($this->p_wa_num[$pid])) {
                    $this->p_wa_num[$pid] = 0;
                }

                if (isset($this->p_pass_rate[$pid])) {
                    $this->total -= $this->p_pass_rate[$pid] * 100;
                    $this->all_total = $this->total;
                } else {
                    $this->fp_pass_rate[$pid] = $this->p_pass_rate[$pid] = $res;
                }
                $this->p_pass_rate[$pid] = $this->fp_pass_rate[$pid] = 1;
                $this->total += 100;
                $this->all_total = $this->total;
                $this->time += $sec - $this->login_date + $this->p_wa_num[$pid] * 1200;

                // echo "Time:".$this->login_date."<br>";
                //                      echo "Solved:".$this->solved."<br>";
            }
        } else ///在补题赛时间
        {
            if ($res * 100 < 99) {
                if (isset($this->fp_pass_rate[$pid])) {
                    if ($res > $this->fp_pass_rate[$pid]) {
                        $this->all_total -= $this->fp_pass_rate[$pid] * 100;
                        $this->fp_pass_rate[$pid] = $res;
                        $this->all_total += $this->fp_pass_rate[$pid] * 100;
                    }
                } else {
                    $this->fp_pass_rate[$pid] = $res;
                    $this->all_total += $res * 100;
                }
                if (isset($this->p_wa_num[$pid])) {
                    $this->p_wa_num[$pid]++;
                } else {
                    $this->p_wa_num[$pid] = 1;
                }
            } else {
                $this->fp_ac_sec[$pid] = 1;
                $this->fsolved++;
                if (!isset($this->p_wa_num[$pid])) {
                    $this->p_wa_num[$pid] = 0;
                }

                if (isset($this->fp_pass_rate[$pid])) {
                    $this->all_total -= $this->fp_pass_rate[$pid] * 100;
                } else {
                    $this->fp_pass_rate[$pid] = $res;
                }
                $this->fp_pass_rate[$pid] = 1;
                $this->all_total += 100;
                //echo "Time:".$this->time."<br>";
            }
            //echo "Solved:".$this->all_total."<br>";
        }
    }
}

function s_cmp($A, $B)
{
//      echo "Cmp....<br>";
    if ($A->total != $B->total) {
        return $A->total < $B->total;
    } else {
        if ($A->solved != $B->solved) {
            return $A->solved < $B->solved;
        } else {
            return $A->time > $B->time;
        }

    }
}

// contest start time
if (!isset($_GET['cid'])) {
    die("No Such Contest!");
}

$cid = intval($_GET['cid']);

if ($OJ_MEMCACHE) {
    $sql = "SELECT `start_time`,`title`,`end_time`,`ftraining_date`,`training_length` FROM `contest` WHERE `contest_id`='$cid'";
    require "./include/memcache.php";
    $result = mysql_query_cache($sql);
    if ($result) {
        $rows_cnt = count($result);
    } else {
        $rows_cnt = 0;
    }

} else {
    $sql = "SELECT `start_time`,`title`,`end_time`,`ftraining_date`,`training_length` FROM `contest` WHERE `contest_id`=?";
    $result = pdo_query($sql, $cid);
    if ($result) {
        $rows_cnt = count($result);
    } else {
        $rows_cnt = 0;
    }

}

$start_time = 0;
$end_time = 0;
if ($rows_cnt > 0) {
//       $row=$result[0];

    if ($OJ_MEMCACHE) {
        $row = $result[0];
    } else {
        $row = $result[0];
    }

    $start_time = strtotime($row['start_time']);
    $end_time = strtotime($row['end_time']);
    $ftraining_date = strtotime($row['ftraining_date']);
    $training_length = strtotime($row['training_length']);
    $title = $row['title'];
}
if ($start_time == 0) {
    $view_errors = "No Such Contest";
    require "template/" . $OJ_TEMPLATE . "/error.php";
    exit(0);
}

if ($start_time > time()) {
    $view_errors = "Contest Not Started!";
    require "template/" . $OJ_TEMPLATE . "/error.php";
    exit(0);
}
$noip = (time() < $end_time) && (stripos($title, $OJ_NOIP_KEYWORD) !== false);
if (isset($_SESSION[$OJ_NAME . '_' . "administrator"]) ||
    isset($_SESSION[$OJ_NAME . '_' . "m$cid"]) ||
    isset($_SESSION[$OJ_NAME . '_' . "source_browser"]) ||
    isset($_SESSION[$OJ_NAME . '_' . "contest_creator"])
) {
    $noip = false;
}

if ($noip) {
    $view_errors = "<h2>$MSG_NOIP_WARNING</h2>";
    require "template/" . $OJ_TEMPLATE . "/error.php";
    exit(0);
}
if (!isset($OJ_RANK_LOCK_PERCENT)) {
    $OJ_RANK_LOCK_PERCENT = 1;
}

$lock = $end_time - ($end_time - $start_time) * $OJ_RANK_LOCK_PERCENT;

//echo $lock.'-'.date("Y-m-d H:i:s",$lock);
$view_lock_time = $start_time + ($end_time - $start_time) * (1 - $OJ_RANK_LOCK_PERCENT);
$locked_msg = "";
if (time() > $view_lock_time && time() < $end_time + $OJ_RANK_LOCK_DELAY) {
    $locked_msg = "The board has been locked.";
}

if ($OJ_MEMCACHE) {
//        require("./include/memcache.php");
    $sql = "SELECT count(1) as pbc FROM `contest_problem` WHERE `contest_id`='$cid'";
    $result = mysql_query_cache($sql);
    if ($result) {
        $rows_cnt = count($result);
    } else {
        $rows_cnt = 0;
    }

} else {
    $sql = "SELECT count(1) as pbc FROM `contest_problem` WHERE `contest_id`=?";
    $result = pdo_query($sql, $cid);
    if ($result) {
        $rows_cnt = count($result);
    } else {
        $rows_cnt = 0;
    }

}

if ($OJ_MEMCACHE) {
    $row = $result[0];
} else {
    $row = $result[0];
}

// $row=$result[0];
$pid_cnt = intval($row['pbc']);
/**
 * 修改人：王春祥
 * 修改时间：2021/1/28
 * 修改目的：封榜运行
 * 实现方法：修改sql命令，使训练赛期间只能查到自己的提交
 */
$user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'];
$now = time();
if ($now > $ftraining_date || isset($_SESSION[$OJ_NAME . '_' . 'administrator']) || isset($_SESSION[$OJ_NAME . '_' . 'contest_creator'])) {
    require "./include/contest_solutions.php"; ///查询所有提交！！！！
} else {
    $sql = "SELECT
        user_id,nick,solution.result,solution.num,solution.in_date,solution.pass_rate
                FROM
                        solution where solution.contest_id='$cid' and solution.user_id='$user_id' and num>=0 and problem_id>0
        ORDER BY user_id,solution_id";

    $result = pdo_query($sql);
    $rows_cnt = count($result);
}
//结束
//echo $sql;
//$result=pdo_query($sql);
$user_cnt = 0;
$user_name = '';
$U = array();

for ($i = 0; $i < $rows_cnt; $i++) {

    $row = $result[$i];

    $n_user = $row['user_id'];
    if (strcmp($user_name, $n_user)) {
        $user_cnt++;
        $U[$user_cnt] = new TM();

        /**
         * 查询登陆时间
         */
        $sql = "select login_time from contest_login_time where user_id=? and contest_id=?";
        $result_sql = pdo_query($sql, $row['user_id'], $cid);
        if (count($result_sql) != 0) {
            $U[$user_cnt]->login_date = strtotime($result_sql[0]['login_time']);
        } else {
            $U[$user_cnt]->login_date = $start_time;
        }

        //结束

        $U[$user_cnt]->user_id = $row['user_id'];
        $U[$user_cnt]->nick = $row['nick'];

        $user_name = $n_user;
    }
    if ($row['result'] != 4 && $row['pass_rate'] >= 0.99) {
        $row['pass_rate'] = 0;
    }

    if (time() < $end_time + $OJ_RANK_LOCK_DELAY && $lock < strtotime($row['in_date'])) {
        $U[$user_cnt]->Add($row['num'], strtotime($row['in_date']), 0, $ftraining_date);
    } else {
        $U[$user_cnt]->Add($row['num'], strtotime($row['in_date']), $row['pass_rate'], $ftraining_date);
    }

}
usort($U, "s_cmp");
////firstblood

$first_blood = array();
for ($i = 0; $i < $pid_cnt; $i++) {
    $first_blood[$i] = "";
}

if ($OJ_MEMCACHE) {
    $sql = "select s.num,s.user_id from solution s ,
        (select num,min(solution_id) minId from solution where contest_id=$cid and result=4 GROUP BY num ) c where s.solution_id = c.minId";
    $fb = mysql_query_cache($sql);
    if ($fb) {
        $rows_cnt = count($fb);
    } else {
        $rows_cnt = 0;
    }

} else {
    $sql = "select s.num,s.user_id from solution s ,
        (select num,min(solution_id) minId from solution where contest_id=? and result=4 GROUP BY num ) c where s.solution_id = c.minId";
    $fb = pdo_query($sql, $cid);
    if ($fb) {
        $rows_cnt = count($fb);
    } else {
        $rows_cnt = 0;
    }

}

foreach ($fb as $row) {
    if ($row["in_date"] > $ftraining_date) ///补题赛第一次做出来不在算入
    {
        continue;
    }

    $first_blood[$row['num']] = $row['user_id'];
}

/////////////////////////Template
require "template/" . $OJ_TEMPLATE . "/contestrank-ioi.php";
/////////////////////////Common foot
if (file_exists('./include/cache_end.php')) {
    require_once './include/cache_end.php';
}
