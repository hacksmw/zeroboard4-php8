<?php
require_once "lib.php";

if ($_SERVER['REQUEST_METHOD'] != 'POST')  Error("잘못된 접근입니다.");
if (strpos(strtolower($HTTP_REFERER),strtolower($HTTP_HOST)) === false) error("잘못된 접근입니다.");

$connect = dbconn();

$user_id = req("user_id") ?? '';
$password = req("password") ?? '';

if (empty(trim($user_id))) error("아이디를 입력하여 주십시요");
if (empty(trim($password))) error("비밀번호를 입력하여 주십시요");

$id = req("id") ?? '';

unset($group);

if ($id) {
	if (!ctype_alnum(str_replace('_', '', $id))) {
		error("id 형식이 올바르지 않습니다.");
	}
	$setup = get_table_attrib($id);
	if (!$setup) {
		error("게시판이 존재하지 않습니다.");
	}
	$group = group_info($setup['group_no']);
}

// 회원 로그인 체크
$result = zb_query("select * from $member_table where user_id='".addslashes($user_id)."' and password='".get_password($password, false, true)."'") or error(zb_error());
$member_data = fetch_array($result);
	
if (!isset($member_data['no']) && strlen(get_password("a")) >= 41) {
	$result = zb_query("select * from $member_table where user_id='".addslashes($user_id)."' and password='".get_password($password, true, true)."'") or error(zb_error());
	$member_data = fetch_array($result);
}

// 회원로그인이 성공하였을 경우 세션을 생성하고 페이지를 이동함
if (isset($member_data['no'])) {
	$query = fetch_array(zb_query("show columns from $member_table like 'reg_m_date'"));
	
	if (!isset($query[0])) {
		zb_query("alter table $member_table add reg_m_date int(13)");
	}
	
	$dbqry="UPDATE $member_table SET reg_m_date = '$now_time' WHERE no='$member_data[no]'";
	zb_query($dbqry);
	
	$auto_login = req("auto_login");

	if($auto_login !== null) {
		makeZBSessionID($member_data['no']);
	}

	// 4.0x 용 세션 처리
	$zb_logged_no = $member_data['no'];
	$zb_logged_time = time();
	$zb_logged_ip = $REMOTE_ADDR;
	$zb_last_connect_check = '0';
	$_SESSION['zb_logged_no']=$zb_logged_no;
	$_SESSION['zb_logged_time']=$zb_logged_time;
	$_SESSION['zb_logged_ip']=$zb_logged_ip;
	$_SESSION['zb_last_connect_check']=0;
	$_SESSION['zb_hash'] = md5($now_time.$member_data['user_id'].$member_data['no'].$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
	
	$page_num = req("page_num") ?? '';
	$des = req("des") ?? '';
	$sn = req("sn") ?? '';
	$ss = req("ss") ?? '';
	$sc = req("sc") ?? '';
	$keyword = req("keyword") ?? '';
	$category = req("category") ?? '';
	$no = req("no") ?? '';
	
	$referer = $HTTP_REFERER ?? '';
		
	// 로그인 후 페이지 이동
	$s_url = req("s_url") ?? '';
	
	if(!$s_url && $id) {
		$s_url="zboard.php?id=$id";
	}
	
	if ($s_url) {
		movepage(htmlspecialchars($s_url));
	} else if ($id) {  movepage("zboard.php?id=".$id."&page=".$page."&page_num=".urlencode($page_num)."&select_arrange=".urlencode($select_arrange)."&desc=".urlencode($des)."&sn=".urlencode($sn)."&ss=".urlencode($ss)."&sc=".urlencode($sc)."&keyword=".urlencode($keyword)."&category=".urlencode($category)."&no=".urlencode($no)."");
	} else if (isset($group) && $group['join_return_url']) {
		movepage($group['join_return_url']);
	} else if ($referer) {
		movepage($referer);
	} else {
		echo"<script>history.go(-2);</script>";
	}

// 회원로그인이 실패하였을 경우 에러 표시
} else {
	Error("로그인을 실패하였습니다");
}

