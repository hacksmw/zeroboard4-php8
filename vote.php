<?php
/***************************************************************************
 * 공통파일 include
 **************************************************************************/
require_once "_head.php";

if (strpos(strtolower($_SERVER["HTTP_REFERER"]), strtolower($_SERVER["HTTP_HOST"])) === false) {
	error("잘못된 접근입니다.");
}

$no = req("no") ?? '';

if (!is_numeric($no)) {
	error("게시물 번호가 올바르지 않습니다.");
}

$page_num = req("page_num") ?? '';
$category = req("category") ?? '';
$des = req("des") ?? '';
$sn = req("sn") ?? '';
$ss = req("ss") ?? '';
$sc = req("sc") ?? '';
$keyword = req("keyword") ?? '';

/***************************************************************************
 * 설정 체크
 **************************************************************************/
// 사용권한 체크
if ($setup['grant_view'] < $member['level'] && !$is_admin) {
	error("사용권한이 없습니다","login.php?id=".urlencode($id)."&page=".urlencode($page)."&page_num=".urlencode($page_num)."&category=".urlencode($category)."&sn=".urlencode($sn)."&ss=".urlencode($ss)."&sc=".urlencode($sc)."&keyword=".urlencode($keyword)."&no=".urlencode($no)."&file=zboard.php");
}

// 현재글의 Vote수 올림;;
if(!isset($_SESSION['zb_vote']) || strpos($_SESSION['zb_vote'], $setup['no'] . "_" . $no) === false) {
	zb_query("update $t_board"."_$id set vote=vote+1 where no='$no'");
	$vote_str =  "," . $setup['no'] . "_" . $no;
	
	if (!isset($_SESSION['zb_vote'])) {
		$_SESSION['zb_vote'] = '';
	}

	// 기존 세션 처리 (4.0x용 세션 처리로 인하여 주석 처리)	
	//$_SESSION['zb_vote'] = $_SESSION['zb_vote'] . $vote_str;

	// 4.0x 용 세션 처리
	$zb_vote = $_SESSION['zb_vote'] . $vote_str;
	session_register("zb_vote");
}

// 페이지 이동

if ($setup['use_alllist']) {
	$temp_href="zboard.php";
} else { 
	$temp_href="view.php";
}

movepage("$temp_href?id=".urlencode($id)."&page=".urlencode($page)."&page_num=".urlencode($page_num)."&select_arrange=".urlencode($select_arrange)."&desc=".urlencode($des)."&sn=".urlencode($sn)."&ss=".urlencode($ss)."&sc=".urlencode($sc)."&keyword=".urlencode($keyword)."&category=".urlencode($category)."&no=".urlencode($no)); 

