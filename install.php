<?php
require_once "lib.php";

if(file_exists("config.php")) error("이미 config.php가 생성되어 있습니다.<br><br>재설치하려면 해당 파일을 지우세요");

head('bgcolor="#000000" text="#ffffff"');
?>
<script>
function check_submit() {
	if(!document.license.accept.checked) {
		alert("라이센스를 읽으시고 동의하시는 분만 제로보드를 사용하실수 있습니다.\n\n라이센스를 모두 읽으신후 라이센스에 동의하시면 체크를 하신후 설치시작하세요");
		return false;
	}
	return true;
}

function check_view() {
	if(document.license.accept.checked) {
		if(confirm("라이센스를 모두 읽으시고 동의를 하십니까?")) {
			return true;
		} else {
			return false;
		}
	}
}
</script>
<div>
<br><br><br>
<div align="center">
<form name="license" method="post" action="install1.php" onsubmit="return check_submit()">
<table cellpadding="3" cellspacing="0" width="600" border="0">
<tr>
<td height="30" colspan="3"><img src="images/inst_top.gif" alt="제로보드 4.0 설치"></td>
</tr>
<tr>
<td>
<br>
<img src="images/inst_step1.gif" alt="라이센스 정보">
<textarea cols="90" rows="15" readonly><?php include "license.txt"; ?></textarea>
<br>
<input type="checkbox" name="accept" value="1" onclick="return check_view()"> 위의 라이센스를 모두 읽었으며 동의합니다
</td>
</tr>

<tr>
<td>
<br>
<img src="images/inst_step1-2.gif"><br><br><br>

<?php
unset($check);

if (fileperms(".")==16839||fileperms(".")==16895) {
	$check="1";
} else {
	$check='';
}
if (!$check) {
?>
<div align="center">
<font color="red">현재 707로 퍼미션이 되어 있지 않습니다. 텔넷이나 FTP에서 퍼미션을 조정하세요.</font><br><br>
<div align="center">

<table border="0">
<tr>
<td align="center" height="30">
<form method="post" action="<?=htmlspecialchars($PHP_SELF)?>">
<input type="submit" value="퍼미션 조정하였습니다" style="height:20px;">
</form>
</td>
</tr>
</table>

</div>
</div>
<?php
} else {
?>
<br><br>
<div align="center">
<table border="0">
<tr>
<td align="center" height="30">
<input type="image" src="images/inst_b_1.gif" border="0" align="absmiddle" alt="설치 시작">
</td>
</tr>
</table>
</div>
<?php
}
?>
<br>
</td>
</tr>
</table>
</form>


</div>
<?php
foot();

