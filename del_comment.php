<?php
/***************************************************************************
 * 공통 파일 include
 **************************************************************************/
require_once "_head.php";

if(!eregi($HTTP_HOST,$HTTP_REFERER)) Error("정상적으로 글을 삭제하여 주시기 바랍니다.");

/***************************************************************************
 * 코멘트 삭제 페이지 처리
 **************************************************************************/

$c_no = req("c_no");
$no = req("no") ?? '';
$mode = req("mode") ?? '';

if (!is_numeric($c_no)) {
	error('잘못된 댓글 번호입니다.');
}

// 원본글을 가져옴
$s_data = mysql_fetch_array(zb_query("select * from $t_comment"."_$id where no='$c_no'"));

if ($s_data === false) {
	error("원본 글이 없습니다.");
}

if ($s_data['ismember'] || $is_admin || $member['level'] <= $setup['grant_delete']) {
	if (!$is_admin && $s_data['ismember'] != $member['no']) {
		error("삭제할 권한이 없습니다");
	}
	$title = "글을 삭제하시겠습니까?";
} else {
	$title = "글을 삭제합니다.<br>비밀번호를 입력하여 주십시요";
	
	$input_password = "<input type=password name=password size=20 class=input>";
}

$target = "del_comment_ok.php";

$a_list="<a href=zboard.php?$href$sort>";
  
$a_view="<a href=view.php?$href$sort&no=".urlencode($no);
	
if ($mode) {
	$mode = htmlspecialchars($mode);
} else {
	$mode = '';
}

head();

include $dir . "/ask_password.php";

foot();

include "_foot.php";
