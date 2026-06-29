<?php
// 라이브러리 함수 파일 인크루드
require_once "lib.php";

if(strpos(strtolower($HTTP_REFERER),strtolower($HTTP_HOST)) === false) Error("정상적으로 글을 작성하여 주시기 바랍니다.");
if(getenv("REQUEST_METHOD") == 'GET' ) Error("정상적으로 글을 쓰시기 바랍니다","");
if(!eregi("member_modify.php",$HTTP_REFERER)) Error("정상적으로 글을 쓰시기 바랍니다");

// DB 연결
$connect=dbConn();

// 멤버 정보 구해오기;;; 멤버가 있을때
$member=member_info();

if(!$member['no']) Error("회원정보가 존재하지 않습니다");

$group=group_info($member['group_no']);

$name = req("name");

if (isblank(trim(str_replace("ㅤ","",$name)))) Error("이름을 입력하셔야 합니다");

if(preg_match("/(<|>)/",$name)) Error("이름에는 태그를 사용하실수 없습니다.");

$password = req("password");
$password1 = req("password1");

if($password&&$password1&&$password!=$password1) Error("비밀번호가 일치하지 않습니다");

$birth_1 = req("birth_1");
$birth_2 = req("birth_2");
$birth_3 = req("birth_3");

if (is_numeric($birth_1) && is_numeric($birth_2) && is_numeric($birth_3)) {
	$birth = mktime(0,0,0,$birth_2,$birth_3,$birth_1);
} else {
	$birth = false;
}

$email = req("email");

$check=mysql_fetch_array(zb_query("select count(*) from $member_table where email='".addslashes($email)."' and no <> ".$member['no'],$connect));
if($check[0]>0) Error("이미 등록되어 있는 E-Mail입니다");

$job = req("job");
$homepage = req("homepage");
$hobby = req("hobby");
$icq = req("icq");
$msn = req("msn");
$home_address = req("home_address");
$home_tel = req("home_tel");
$office_address = req("office_address");
$office_tel = req("office_tel");
$handphone = req("handphone");
$comment = req("comment");

$mailing = req("mailing");
$openinfo = req("openinfo");

if ($mailing && $mailing != "1") {
	$mailing = "1";
}

if ($openinfo && $openinfo != "1") {
	$openinfo = "1";
}

$name = addslashes($name);
$job = addslashes($job);
$email = addslashes($email);

if ($_zbDefaultSetup['check_email'] == "true" && !mail_mx_check($email)) Error("입력하신 $email 은 존재하지 않는 메일주소입니다.<br>다시 한번 확인하여 주시기 바랍니다.");

if ($homepage && strpos($homepage, 'http') === false) $homepage = "http://$homepage";
$homepage = addslashes($homepage);
$birth = addslashes($birth);
$hobby = addslashes($hobby);
$icq = addslashes($icq);
$msn = addslashes($msn);
$home_address = addslashes($home_address);
$home_tel = addslashes($home_tel);
$office_address = addslashes($office_address);
$office_tel = addslashes($office_tel);
$handphone = addslashes($handphone);
$comment = addslashes($comment);

$que="update $member_table set name='$name'";
if($password&&$password1&&$password==$password) $que.=" ,password='".get_password($password, false, true)."' ";

if (($birth_1)&&($birth_2)&&($birth_3)&&$group['use_birth']) $que.=", birth='$birth'";

if ($email) $que.=",email='$email'";

$que.=",homepage='$homepage'";
if(($group['use_job'])) $que.=",job='$job'";
if(($group['use_hobby'])) $que.=",hobby='$hobby'";
if(($group['use_icq'])) $que.=",icq='$icq'";
if(($group['use_aol'])) $que.=",aol='$aol'";
if(($group['use_msn'])) $que.=",msn='$msn'";
if(($group['use_home_address'])) $que.=",home_address='$home_address'";
if(($group['use_home_tel'])) $que.=",home_tel='$home_tel'";
if(($group['use_office_address'])) $que.=",office_address='$office_address'";
if(($group['use_office_tel'])) $que.=",office_tel='$office_tel'";
if(($group['use_handphone'])) $que.=",handphone='$handphone'";
if(($group['use_comment'])) $que.=",comment='$comment'";
if(($group['use_mailing'])) $que.=",mailing='$mailing'";
$que.=",openinfo='$openinfo'";

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


$que.=",openinfo='$openinfo',open_email='$open_email',open_homepage='$open_homepage',open_icq='$open_icq',open_msn='$open_msn',open_comment='$open_comment',open_job='$open_job',open_hobby='$open_hobby',open_home_address='$open_home_address',open_home_tel='$open_home_tel',open_office_address='$open_office_address',open_office_tel='$open_office_tel',open_handphone='$open_handphone',open_birth='$open_birth',open_picture='$open_picture',open_aol='$open_aol' ";

$que.=" where no='$member[no]'";

zb_query($que) or Error("회원정보 수정시에 에러가 발생하였습니다 ".zb_error());

$del_picture = req("del_picture");

if(($del_picture)) {
	z_unlink($member['picture']);
	zb_query("update $member_table set picture='' where no='$member[no]'") or Error("사진 자료 업로드시 에러가 발생하였습니다");
}

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
	if(!move_uploaded_file($picture,$path)) Error("사진 업로드가 제대로 되지 않았습니다");
	@z_unlink($member['picture']);
	zb_query("update $member_table set picture='$path' where no='$member[no]'") or Error("사진 자료 업로드시 에러가 발생하였습니다");
}
?>
<html lang="ko">
<head>
<meta charset="utf-8">
<script>
alert("회원님의 정보 수정이 제대로 처리되었습니다.");
opener.window.history.go(0);
window.close();
</script>
</head>
<body>
</body>
</html>