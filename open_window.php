<?php
require_once "lib.php";

$mode = req("mode") ?? '';
$str = req("str") ?? '';

if(!$mode || !$str) die("<script>window.close()</script>");

if($mode != "m" && $mode != "i" && $mode != "t" && $mode != "tn") die("<script>window.close()</script>");

$connect = dbconn();

// 멤버 정보 구해오기;;; 멤버가 있을때
$member = member_info();

// 현재 로그인되어 있는 멤버가 전체, 또는 그룹관리자인지 검사
if ($member['is_admin'] == 1 || $member['is_admin'] == 2 && $member['group_no'] == $setup['group_no']) {
	$is_admin=1;
} else {
	$is_admin="";
}

if ($is_admin && ($mode == "i" || $mode == "t")) {
	$data = mysql_fetch_array(zb_query("select * from $member_table where no='".addslashes($str)."'"));
	if ($data === false) {
		error("데이터가 없습니다.");
	}
}

unset($href);

if (($mode == "i" || $mode == "t") && $is_admin && $data['user_id']) {
	if ($mode=="i") {
		$href = "admin_setup.php?exec=view_member&group_no=$data[group_no]&exec2=modify&no=$data[no]";
	} else {
		$href = "admin/trace.php?keykind[5]=ismember&keyword=" . urlencode($data['user_id']);
	}
} else if ($mode == "tn" && $is_admin && $str) {
	$href = "admin/trace.php?keykind[0]=name&keyword=" . urlencode($str);
}

if ($mode=="m") {
	$mail = base64_decode($str);
	if (ismail($mail)) {
		$href = "mailto:$mail";	
	}
}

if ($href) {
	header("Location: $href");
}
