<?php
// 라이브러리 함수 파일 인크루드
require_once "lib.php";

if(strpos(strtolower($HTTP_REFERER),strtolower($HTTP_HOST)) === false) Error("정상적으로 작성하여 주시기 바랍니다.");
if(strpos(strtolower($HTTP_REFERER),"member_join.php") === false) Error("정상적으로 작성하여 주시기 바랍니다","");
if(getenv("REQUEST_METHOD") == 'GET' ) Error("정상적으로 글을 쓰시기 바랍니다","");


// DB 연결
$connect=dbConn();

$group_no = req("group_no");

if (!is_numeric($group_no)) {
	error("그룹 번호가 올바르지 않습니다.");
}

// 멤버 정보 구해오기;;; 멤버가 있을때
$member=member_info();

if ($mode == "admin" && ($member['is_admin'] == 1 || ($member['is_admin'] == 2 && $member['group_no'] == $group_no))) {
	$mode = "admin";
} else {
	$mode = "";
}

if(isset($member['no']) && !$mode) Error("이미 가입이 되어 있습니다.","window.close");

$id = req("id");

// 현재 게시판 설정 읽어 오기
if ($id) {
	if (!ctype_alnum(str_replace('_', '', $id))) {
		error("게시판 id가 올바르지 않습니다.");
	}
	
	$setup=get_table_attrib($id);

	// 설정되지 않은 게시판일때 에러 표시
	if (!$setup['name']) Error("생성되지 않은 게시판입니다.<br><br>게시판을 생성후 사용하십시요");

	// 현재 게시판의 그룹의 설정 읽어 오기
	$group_data=group_info($setup['group_no']);
	if(!$group_data['use_join']&&!$mode) Error("현재 지정된 그룹은 추가 회원을 모집하지 않습니다");

} else {
	$group_no = req("group_no");
	if(!$group_no) Error("회원그룹을 정해주셔야 합니다");
	if (!is_numeric($group_no)) {
		error("그룹 번호가 올바르지 않습니다.");
	}
	
	$group_data=mysql_fetch_array(zb_query("select * from $group_table where no='$group_no'"));
	if(!$group_data['no']) Error("지정된 그룹이 존재하지 않습니다");
	if(!$group_data['use_join'] && !$mode) Error("현재 지정된 그룹은 추가 회원을 모집하지 않습니다");
}

$user_id = req("user_id");
$name = req("name");

// 빈문자열인지를 검사
$user_id = str_replace("ㅤ","",$user_id);
$name = str_replace("ㅤ","",$name);

$user_id=trim($user_id);

if(isBlank($user_id)) Error("ID를 입력하셔야 합니다","");

$user_id = addslashes($user_id);
$name = addslashes($user_id);

$check=mysql_fetch_array(zb_query("select count(*) from $member_table where user_id='".$user_id."'",$connect));
if($check[0]>0) Error("이미 등록되어 있는 ID입니다","");

$email = req("email");

if (!ismail($email)) {
	error("올바른 메일주소 형식이 아닙니다.");
}

unset($check);
$check=mysql_fetch_array(zb_query("select count(*) from $member_table where email='".addslashes($email)."'",$connect));
if($check[0]>0) Error("이미 등록되어 있는 E-Mail입니다","");

$password = req("password");
$password1 = req("password1");

if(isBlank($password)) Error("비밀번호를 입력하셔야 합니다","");

if(isBlank($password1)) Error("비밀번호 확인을 입력하셔야 합니다","");

if($password!=$password1) Error("비밀번호와 비밀번호 확인이 일치하지 않습니다","");

$name = req("name");

if(isBlank($name)) Error("이름을 입력하셔야 합니다","");

if(preg_match("/(<|>)/",$name)) Error("이름을 영문, 한글, 숫자등으로 입력하여 주십시요");

unset($jumin);

if ($group_data['use_jumin'] && !$mode) {

	// 주민등록 번호 루틴
	if(isBlank($jumin1) || isBlank($jumin2) || strlen($jumin1) != 6 || strlen($jumin2) != 7) Error("주민등록번호를 올바르게 입력하여 주십시요","");

	if (!is_numeric($jumin1) || !is_numeric($jumin2) || !check_jumin($jumin1.$jumin2)) Error("잘못된 주민등록번호입니다","");

	$check = mysql_fetch_array(zb_query("select count(*) from $member_table where jumin='".get_password($jumin1 . $jumin2)."'", $connect));
	
	if($check[0] > 0) {
		Error("이미 등록되어 있는 주민등록번호입니다","");
	} else {
		$check = mysql_fetch_array(zb_query("select count(*) from $member_table where jumin='".get_password($jumin1 . $jumin2, true)."'", $connect));	
		if ($check[0] > 0) {
			Error("이미 등록되어 있는 주민등록번호입니다", "");
		}
	}
	
	$jumin = $jumin1 . $jumin2;
}

$home_address = req("home_address");
$home_tel = req("home_tel");
$office_address = req("office_address");
$office_tel = req("office_tel");
$handphone = req("handphone");
$comment = req("comment");

$job = req("job");
$homepage = req("homepage");
$hobby = req("hobby");
$icq = req("icq");
$msn = req("msn");
$aol = req("aol");

$mailing = req("mailing");
$openinfo = req("openinfo");

if ($mailing && $mailing != "1") {
	$mailing = "1";
}

if ($openinfo && $openinfo != "1") {
	$openinfo = "1";
}

$birth_1 = req("birth_1");
$birth_2 = req("birth_2");
$birth_3 = req("birth_3");

if($_zbDefaultSetup['check_email'] == "true" && !mail_mx_check($email)) Error("입력하신 $email 은 존재하지 않는 메일주소입니다.<br>다시 한번 확인하여 주시기 바랍니다.");

$name = addslashes($name);
$email = addslashes($email);

$home_address=addslashes($home_address);
$home_tel=addslashes($home_tel);
$office_address=addslashes($office_address);
$office_tel=addslashes($office_tel);
$handphone=addslashes($handphone);
$comment=addslashes($comment);

$birth = mktime(0,0,0,$birth_2,$birth_3,$birth_1);
$birth = addslashes($birth);

if($homepage && strpos($homepage,'http://') === false) $homepage="http://$homepage";
$reg_date=time();
$job = addslashes($job);
$homepage = addslashes($homepage);
$hobby = addslashes($hobby);
$icq = addslashes($icq);
$msn = addslashes($msn);
$aol = addslashes($aol);

$open_email = req("open_email");
$open_homepage = req("open_homepage");
$open_icq = req("open_icq");
$open_msn = req("open_msn");
$open_comment = req("open_comment");
$open_job = req("open_job");
$open_hobby = req("open_hobby");
$open_home_address = req("open_home_address");
$open_home_tel = req("open_home_tel");
$open_office_address = req("open_office_address");
$open_office_tel = req("open_office_tel");
$open_handphone = req("open_handphone");
$open_birth = req("open_birth");
$open_picture = req("open_picture");
$open_aol = req("open_aol");

if(($openinfo !== "1")) $openinfo = '';
if(($open_email !== "1")) $open_email = '';
if(($open_homepage !== "1")) $open_homepage = '';
if(($open_icq !== "1")) $open_icq = '';
if(($open_msn !== "1")) $open_msn = '';
if(($open_comment !== "1")) $open_comment = '';
if(($open_job !== "1")) $open_job = '';
if(($open_hobby !== "1")) $open_hobby = '';
if(($open_home_address !== "1")) $open_home_address = '';
if(($open_home_tel !== "1")) $open_home_tel = '';
if(($open_office_address !== "1")) $open_office_address = '';
if(($open_office_tel !== "1")) $open_office_tel = '';
if(($open_handphone !== "1")) $open_handphone = '';
if(($open_birth !== "1")) $open_birth = '';
if(($open_picture !== "1")) $open_picture = '';
if(($open_aol !== "1")) $open_aol = '';

unset($picture_name);

if(isset($_FILES['picture'])) {
	$picture = $_FILES['picture']['tmp_name'];
	$picture_name = $_FILES['picture']['name'];
	$picture_type = $_FILES['picture']['type'];
	$picture_size = $_FILES['picture']['size'];
}

if(($picture_name)) {
	if(!is_uploaded_file($picture)) Error("정상적인 방법으로 업로드 해주세요");
	if(!preg_match("/\.(gif|jpe?g|png)$/i",$picture_name)) Error("사진은 gif, png 또는 jpg 파일을 올려주세요");
	$picture_name_org = md5(uniqid(mt_rand(), true)).".".array_pop(explode(".",$picture_name));
	$path="icon/$picture_name_org";
	if(!@move_uploaded_file($picture,$path)) Error("사진 업로드가 제대로 되지 않았습니다");
	$picture_name=$path;
}


zb_query("insert into $member_table (level,group_no,user_id,password,name,email,homepage,icq,aol,msn,jumin,comment,job,hobby,home_address,home_tel,office_address,office_tel,handphone,mailing,birth,reg_date,openinfo,open_email,open_homepage,open_icq,open_msn,open_comment,open_job,open_hobby,open_home_address,open_home_tel,open_office_address,open_office_tel,open_handphone,open_birth,open_picture,picture,open_aol) values ('$group_data[join_level]','$group_data[no]','$user_id', '".get_password($password, false, true)."','$name','$email','$homepage','$icq','$aol','$msn', '".get_password($jumin)."','$comment','$job','$hobby','$home_address','$home_tel','$office_address','$office_tel','$handphone','$mailing','$birth','$reg_date','$openinfo','$open_email','$open_homepage','$open_icq','$open_msn','$open_comment','$open_job','$open_hobby','$open_home_address','$open_home_tel','$open_office_address','$open_office_tel','$open_handphone','$open_birth','$open_picture','$picture_name','$open_aol')") or error("회원 데이타 입력시 에러가 발생했습니다<br>".zb_error());

zb_query("update $group_table set member_num=member_num+1 where no='$group_data[no]'");

if(!$mode) {
	$member_data=mysql_fetch_array(zb_query("select * from $member_table where user_id='$user_id' and password='".get_password($password, false, true)."'"));

	// 4.0x 용 세션 처리
	$zb_logged_no = $member_data['no'];
	$zb_logged_time = time();
	$zb_logged_ip = $REMOTE_ADDR;
	$zb_last_connect_check = '0';
	
	$_SESSION['zb_logged_no']=$zb_logged_no;
	$_SESSION['zb_logged_time']=$zb_logged_time;
	$_SESSION['zb_logged_ip']=$zb_logged_ip;
	$_SESSION['zb_last_connect_check']=0;
}
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8" />
</head>
<body>
<script>
alert("회원가입이 정상적으로 처리 되었습니다\n\n회원이 되신것을 진심으로 축하드립니다.");
opener.window.history.go(0);
window.close();
</script>
</body>
</html>