<?php
	if(!isset($_REQUEST['address'])) {
?>
<html lang="ko">
<head>
<meta charset="utf-8" />
</head>
<body>
<script>
alert("우편번호를 입력하셔야 합니다");
history.back();
</script>
</body>
</html>
<?php
		exit;
	}

	$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER']: '';
	$num = isset($_REQUEST['num']) ? $_REQUEST['num']: '';
	$address = isset($_REQUEST['address']) ? $_REQUEST['address']: '';
	
	$url=preg_replace("/search_zipcode.php\?/i","search_zipcode3.php", $referer);
	$url=preg_replace("/num=1/i","",$url);
	$url=preg_replace("/num=2/i","",$url);
	header("Location: http://zeroboard.com/zipcode/search_zipcode2.html?num=".urlencode($num)."&url=".urlencode($url)."&address=".urlencode($address));
