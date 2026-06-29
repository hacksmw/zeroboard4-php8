<?php
include "lib.php";

$connect = dbconn();

// 관리자가 1명이상 있을경우 바로 로그인 페이지로...
$temp = fetch_array(zb_query("select count(*) from $member_table where is_admin='1'",$connect));
if ($temp[0] > 0) {
	header("Location: admin.php"); 
	exit;
}

$user_id = req("user_id");
$password1 = req("password1");
$password2 = req("password2");
$name = req("name");

if ($password1 === null || $password2 === null) {
	error("비밀번호와 비밀번호 확인을 입력해야 합니다.");
}

// 빈문자열인지를 검사
if (isblank($user_id)) error("아이디를 입력하셔야 합니다","");
if (isblank($password1)) error("비밀번호를 입력하셔야 합니다","");
if (isblank($password2)) error("비밀번호 확인을 입력하셔야 합니다","");
if ($password1 !== $password2) error("비밀번호와 비밀번호 확인이 일치하지 않습니다","");
if (isblank($name)) error("이름을 입력하셔야 합니다","");

$user_id = addslashes($user_id);
$name = addslashes($name);

// 관리자 정보 입력
zb_query("INSERT INTO $member_table (user_id,password,name,is_admin,reg_date,level) VALUES ('$user_id', '" . get_password($password1, false, true) . "', '$name', '1', '".time()."','1')", $connect) or error(zb_error(), "");

header("Location: admin.php");
