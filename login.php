<?php
require_once "lib.php";

$id = req("id") ?? '';

$group_no = req("group_no") ?? '';

if (!$id && !$group_no) Error("게시판 이름이나 그룹번호를 지정하여 주셔야 합니다.<br><br>(login.php?id=게시판이름   또는  login.php?group_no=번호)","");

$connect = dbConn();

// 현재 게시판 설정 읽어 오기
if ($id) {
	if (!ctype_alnum(str_replace('_', '', $id))) {
		error("게시판 id가 올바르지 않습니다.");
	}
	
	$setup = get_table_attrib($id);
	
	if (!$setup) {
		error("해당하는 게시판이 없습니다.");
	}

	// 설정되지 않은 게시판일때 에러 표시
  	if (!isset($setup['name']) || !$setup['name']) {
		Error("생성되지 않은 게시판입니다.<br><br>게시판을 생성후 사용하십시요","");
	}

	// 현재 게시판의 그룹의 설정 읽어 오기
  	$group = group_info($setup['group_no']);
	
	if (!$group) {
		error("그룹의 설정을 읽는데 실패했습니다.");
	}
	
  	$dir = "skin/" . $setup['skinname'];
  	$file = "skin/" . $setup['skinname'] . "/login.php";

} else {
	if (!is_numeric($group_no)) {
		error("그룹 번호가 숫자가 아닙니다.");
	}

	$group = mysql_fetch_array(zb_query("select * from $group_table where no='$group_no'"));
	
	if ($group === false || !$group['no']) {
		error("지정된 그룹이 존재하지 않습니다");
	}
}

head();
?>

<script>
 function check_submit()
 {
  if(!login.user_id.value)
  {
   alert("아이디를 입력하여 주세요");
   login.user_id.focus();
   return false;
  }
  if(!login.password.value)
  {
   alert("비밀번호를 입력하여 주세요");
   login.password.focus();
   return false;
  }
  check=confirm("자동 로그인 기능을 사용하시겠습니까?\n\n자동 로그인 사용시 다음 접속부터는 로그인을 하실필요가 없습니다.\n\n단, 게임방, 학교등 공공장소에서 이용시 개인정보가 유출될수 있으니 조심하여 주십시요");
  if(check) {login.auto_login.value=1;}
  return true;
 }
</script>
<?php
	$sn = req("sn") ?? '';
	$ss = req("ss") ?? '';
	$sc = req("sc") ?? '';
	$keyword = req("keyword") ?? '';
	$page_num = req("page_num") ?? '';
	$category = req("category") ?? '';
	$mode = req("mode") ?? '';
	$desc = req("desc") ?? '';
	$select_arrange = req("select_arrange") ?? '';
	$referer = req("referer") ?? '';
	$s_url = req("s_url") ?? '';
	$page = req("page") ?? '';
	$id = req("id") ?? '';
	$no = req("no") ?? '';
	$autologin = req("autologin") ?? '';
?>

<form method="post" action="login_check.php" onsubmit="return check_submit();" name="login">

<input type="hidden" name="auto_login" value="<?php if($autologin === null || !is_array($autologin) || !isset($autologin['ok']))echo"0";else echo"1";?>">

<input type="hidden" name="page" value="<?=($page !== null)?htmlspecialchars($page):''?>">
<input type="hidden" name="id" value="<?=($id !== null)?htmlspecialchars($id):''?>">
<input type="hidden" name="no" value="<?=($no !== null)?htmlspecialchars($no):''?>">
<input type="hidden" name="select_arrange" value="<?=($select_arrange !== null)?htmlspecialchars($select_arrange):''?>">
<input type="hidden" name="desc" value="<?=($desc !== null)?htmlspecialchars($desc):''?>">
<input type="hidden" name="page_num" value="<?=($page_num !== null)?htmlspecialchars($page_num):''?>">
<input type="hidden" name="keyword" value="<?=($keyword !== null)?htmlspecialchars($keyword):''?>">
<input type="hidden" name="category" value="<?=($category !== null)?htmlspecialchars($category):''?>">
<input type="hidden" name="sn" value="<?=($sn !== null)?htmlspecialchars($sn):''?>">
<input type="hidden" name="ss" value="<?=($ss !== null)?htmlspecialchars($ss):''?>">
<input type="hidden" name="sc" value="<?=($sc !== null)?htmlspecialchars($sc):''?>">
<input type="hidden" name="mode" value="<?=($mode !== null)?htmlspecialchars($mode):''?>">
<input type="hidden" name="s_url" value="<?=($s_url !== null)?htmlspecialchars($s_url):''?>">
<input type="hidden" name="referer" value="<?=($referer !== null)?htmlspecialchars($referer):''?>">

<?php
if ($id) include $file;
?>

</form>

<?php
foot();
