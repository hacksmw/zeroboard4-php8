<?php
/***************************************************************************
 * 공통 파일 include
 **************************************************************************/
require_once "_head.php";

if (strpos(strtolower($HTTP_REFERER),strtolower($HTTP_HOST)) === false) die();

/***************************************************************************
 * 게시판 설정 체크
 **************************************************************************/

$no = req("no") ?? '';
$filenum = req("filenum") ?? '';
$id = req("id") ?? '';

if (!is_numeric($no)) {
	error("no가 올바른 값이 아닙니다.");
}

if (!($filenum == 1 || $filenum == 2)) {
	error("filenum은 1 또는 2가 되어야 합니다.");
}

if (!ctype_alnum(str_replace('_', '', $id))) {
	error("id가 올바르지 않습니다.");
}


$page_num = req("page_num") ?? '';
$sn = req("sn") ?? '';
$ss = req("ss") ?? '';
$sc = req("sc") ?? '';
$keyword = req("keyword") ?? '';
$category = req("category") ?? '';
	

// 사용권한 체크
if ($setup['grant_view'] < $member['level'] && !$is_admin) {
	error("사용권한이 없습니다","login.php?id=".urlencode($id)."&page=".urlencode($page)."&page_num=".urlencode($page_num)."&category=".urlencode($category)."&sn=".urlencode($sn)."&ss=".urlencode($ss)."&sc=".urlencode($sc)."&keyword=".urlencode($keyword)."&no=".urlencode($no)."&file=zboard.php");
}

$data = mysql_fetch_array(zb_query("select * from  `$t_board"."_$id` where no='$no'"));

if ($data === false) {
	error("게시물이 없습니다.");
}

// 현재글의 Download 수를 올림;;
if($filenum == 1) {
	zb_query("update `$t_board"."_$id` set download1=download1+1 where no='$no'");
} else {
	zb_query("update `$t_board"."_$id` set download2=download2+1 where no='$no'");
}

// 다운로드;;
$filename = "file_name" . $filenum;
$sfilename = "s_file_name" . $filenum;

download_file($data[$filename], $data[$sfilename]);

