<?php
require_once "lib.php";

if (strpos(strtolower($HTTP_REFERER),strtolower($HTTP_HOST)) === false) error("잘못된 접근입니다.");

// DB 연결
$connect = dbConn();

// 멤버 정보 구해오기
$member = member_info();

if (!isset($member['no'])) Error("로그인 상태가 아닙니다");

/*
$group_no = $_REQUEST["group_no"];

if ($group_no === null) {
	$group_no = $member['group_no'];
} else {
	if (!is_numeric($group_no)) {
		error("그룹 번호가 올바르지 않습니다.");
	}
}
*/

/*
$id = $_REQUEST["id"];

if ($id !== null) {
	if (!ctype_alnum(str_replace('_', '', $id))) {
		error("id 형식이 올바르지 않습니다.");
	}
	$setup = get_table_attrib($id);
}
*/

//if(isset($setup['group_no'])&&!isset($group_no)) $group_no=$setup['group_no'];

destroyZBSessionID($member['no']);

// 4.0x 용 세션 처리
$_SESSION['zb_logged_no']='';
$_SESSION['zb_logged_time']='';
$_SESSION['zb_logged_ip']='';
$_SESSION['zb_secret']='';
$_SESSION['zb_last_connect_check']=0;
unset($_SESSION['zb_logged_no'],$_SESSION['zb_logged_time'],$_SESSION['zb_logged_ip'],$_SESSION['zb_secret']);
session_destroy(); 

$id = req("id") ?? '';
$page_num = req("page_num") ?? '';
$des = req("des") ?? '';
$sn = req("sn") ?? '';
$ss = req("ss") ?? '';
$sc = req("sc") ?? '';
$keyword = req("keyword") ?? '';
$category = req("category") ?? '';
$no = req("no") ?? '';
$s_url = req("s_url") ?? '';

$referer = $HTTP_REFERER ?? '';

if($s_url) movepage(htmlspecialchars($s_url));

if($id) {
	header("Location: zboard.php?id=".urlencode($id)."&page=".urlencode($page)."&page_num=".urlencode($page_num)."&select_arrange=".urlencode($select_arrange)."&desc=".urlencode($des)."&sn=".urlencode($sn)."&ss=".urlencode($ss)."&sc=".urlencode($sc)."&keyword=".urlencode($keyword)."&category=".urlencode($category)."&no=".urlencode($no)."");
}
elseif($group['join_return_url']) {
	header("Location: " . $group['join_return_url']);
}
elseif($referer) {
	header("Location: $referer");
}
else {
	echo"<script>history.go(-2);</script>";
}

