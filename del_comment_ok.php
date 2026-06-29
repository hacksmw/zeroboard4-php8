<?php

/***************************************************************************
 * 공통 파일 include
 **************************************************************************/
require_once "_head.php";

if (!eregi($HTTP_HOST,$HTTP_REFERER)) Error("정상적으로 글을 삭제하여 주시기 바랍니다.");
if (!eregi("del_comment.php",$HTTP_REFERER)) Error("제대로 된 접근을 하여 주시기 바랍니다");

/***************************************************************************
* 코멘트 삭제 진행
**************************************************************************/

$password = req("password") ?? '';
$c_no = req("c_no") ?? '';
$no = req("no") ?? '';
$id = req("id") ?? '';

if (!$id || !ctype_alnum(str_replace('_', '', $id))) {
	error("게시판 id가 올바르지 않습니다.");
}

if (!is_numeric($c_no)) {
	error("코멘트 번호가 숫자가 아닙니다.");
}

if (!is_numeric($no)) {
	error("게시물 번호가 숫자가 아닙니다.");
}

if (!isset($_REQUEST['password'])) $_REQUEST['password']='';

if (!isset($des)) $des='';
	
// 원본글을 가져옴
$s_data = mysql_fetch_array(zb_query("select * from $t_comment"."_$id where no='$c_no'"));

if (!$s_data) {
	error("코멘트가 없습니다.");
}

// 패스워드를 암호화
if ($password) {
	$password = get_password($password, false, true);   
	if (strlen($s_data['password']) <= 16 && strlen(get_password("a")) >= 41) 
		$password = get_password($_REQUEST["password"], true, true);
}

// 회원일때를 확인;;
if (!$is_admin && $member['level'] > $setup['grant_delete']) {
	if (!$s_data['ismember']) {
		if ($s_data['password'] != $password) Error("비밀번호가 올바르지 않습니다");
	} else {
		if ($s_data['ismember'] != $member['no']) Error("비밀번호를 입력하여 주십시요");
	}
}

// 코멘트 삭제
zb_query("delete from $t_comment"."_$id where no='$c_no'") or error(zb_error());

// 코멘트 갯수 정리 (동시성 문제로 삑사리 날 수 있음)
$total = mysql_fetch_array(zb_query("select count(*) from $t_comment"."_$id where parent='$no'"));
zb_query("update $t_board"."_$id set total_comment='$total[0]' where no='$no'") or error(zb_error()); 

// 회원일 경우 해당 해원의 점수 주기
if ($member['no'] == $s_data['ismember']) zb_query("update $member_table set point2=point2-1 where no='$member[no]'", $connect) or error(zb_error());

// 페이지 이동
if ($setup['use_alllist']) movepage("zboard.php?id=$id&page=$page&page_num=".urlencode($page_num)."&select_arrange=".urlencode($select_arrange)."&desc=".urlencode($des)."&sn=".urlencode($sn)."&ss=".urlencode($ss)."&sc=".urlencode($sc)."&keyword=".urlencode($keyword)."&no=$no");
else movepage("view.php?id=".urlencode($id)."&page=".urlencode($page)."&page_num=".urlencode($page_num)."&select_arrange=".urlencode($select_arrange)."&desc=".urlencode($des)."&sn=".urlencode($sn)."&ss=".urlencode($ss)."&sc=".urlencode($sc)."&keyword=".urlencode($keyword)."&no=".urlencode($no));
