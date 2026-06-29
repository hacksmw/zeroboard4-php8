<?php
require_once "lib.php";

$user_id = req("user_id") ?? '';

if (!$user_id) {
	error("아이디를 입력해야 합니다.", "window.close()");
}

$connect = dbconn();

$check = mysql_fetch_array(zb_query("select count(*) from $member_table where user_id='" . addslashes($user_id) . "'"));

head();
?>
<form>
<table width="100%" height="100%">
<tr>
<td align="center">
<?php
if ($check !== false && $check[0] != "0") {
	echo htmlspecialchars($user_id) . "는 이미 등록된 아이디입니다";
} else {
	echo htmlspecialchars($user_id) . "는 사용하실 수 있습니다";
}
?>
</td>
</tr>
<tr>
<td align="center"><input type="button" value="Close window" onclick="window.close();" class="submit"></td>
</tr>
</table>
</form>
<?php 
foot(); 
