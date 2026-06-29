<?php
/**********************************************************************************
 * 공통 파일 include
 *********************************************************************************/
require_once "_head.php";

if(strpos(strtolower($HTTP_REFERER),strtolower($HTTP_HOST)) === false) Error("비정상적인 접근입니다..");
if(getenv("REQUEST_METHOD") !== 'POST' ) Error("정상적인 접근이 아닙니다.");

/**********************************************************************************
 * 설정 체크
 *********************************************************************************/
$id = req("id") ?? '';
if (!$id || !ctype_alnum(str_replace('_', '', $id))) {
	error("게시판 아이디가 잘못되었습니다.");
}
	
// 그룹 정보 구해오기
$setup=get_table_attrib($id);

if (!$setup) {
	error("게시판 정보를 구할 수 없습니다.");
}

$exec = req("exec") ?? '';

$board_name = req("board_name") ?? '';
if ($board_name && !ctype_alnum(str_replace('_', '', $board_name))) {
	error("대상 게시판 아이디가 잘못되었습니다.");
}

// 사용권한 체크
if($exec=="view_all"&&$setup['grant_view']<$member['level']&&!$is_admin) Error("사용권한이 없습니다","login.php?id=".urlencode($id)."&page=".urlencode($page)."&page_num=".urlencode($page_num)."&category=".urlencode($category)."&keykind=".urlencode($keykind)."&keyword=".urlencode($keyword)."&no=".urlencode($no)."&file=zboard.php");

if($exec!="view_all") unset($setup);

if(!$is_admin&&$exec!="view_all") Error("사용권한이 없습니다","login.php?id=".urlencode($id)."&page=".urlencode($page)."&page_num=".urlencode($page_num)."&category=".urlencode($category)."&keykind=".urlencode($keykind)."&keyword=".urlencode($keyword)."&no=".urlencode($no)."&file=zboard.php");

$selected = req("selected") ?? '';

$select_list=$selected; 
$selected=explode(";",$selected);
$j = count($selected);
for ($i = 0; $i < $j-1; $i++) {
	if (!is_numeric($selected[$i])) {
		error("selected가 올바르지 않습니다.");
		
	}
}

if($exec=="copy_all") $_kind = "복사";
elseif($exec=="move_all") $_kind = "이동";
elseif($exec=="delete_all") $_kind = "삭제";
elseif($exec=="view_all") $_kind = "보기";
else {
	error("exec가 올바르지 않습니다.");
}

/**********************************************************************************
 * 쪽지 보내는 함수 간단하게 사용할수 있는 것
 *********************************************************************************/
function _send_message($to, $from, $subject, $memo) {

	global $get_memo_table, $send_memo_table, $member_table;
	
	$to = addslashes($to);
	$from = addslashes($from);
	$subject = addslashes($subject);
	$memo = addslashes($memo);

	$reg_date = time();

	zb_query("insert into $get_memo_table (member_no,member_from,subject,memo,readed,reg_date) 
					values ('$to','$from','$subject','$memo',1,'$reg_date')") or error(zb_error());

	zb_query("insert into $send_memo_table (member_to,member_no,subject,memo,readed,reg_date) 
					values ('$to','$from','$subject','$memo',1,'$reg_date')") or error(zb_error());

	zb_query("update $member_table set new_memo=1 where no='$to'") or error(zb_error());
}


/**********************************************************************************
 * View_All 일때 (선택된 게시물 보기)
 *********************************************************************************/

if($exec=="view_all") {

	$_view_included = true;
	$_view_included2 = true;
	
	$href.="&selected=$select_list";

	head();

	// 상단 현황 부분 출력
	include "$dir/setup.php";

	for($i=count($selected)-2;$i>=0;$i--) {
		$no = $selected[$i];
		include "view.php";
	}

	// layer 출력
	if (req("zbLayer")) {
		$zbLayer = '';
	}
		
	if($zbLayer) {
		echo "\n<script>".$zbLayer."\n</script>";
		unset($zbLayer);
	}

	foot();

}


/**********************************************************************************
 * Delete_All 일때 (선택된 게시물 삭제)
 *********************************************************************************/

else if($exec=="delete_all") {

	for ($i=0;$i<count($selected)-1;$i++) {

		$temp=mysql_fetch_array(zb_query("select * from $t_board"."_$id where no='$selected[$i]'"));

		// 답글이 없을때 
		if(!$temp['child']) {

			// 글삭제
			zb_query("delete from $t_board"."_$id where no='$selected[$i]'") or Error(zb_error()); 

			// 카테고리에서 숫자 하나 뺌
			zb_query("update $t_category"."_$id set num=num-1 where no='$temp[category]'",$connect);

			// 파일삭제
			@z_unlink("./".$temp["file_name1"]);
			@z_unlink("./".$temp["file_name2"]);

			// Divison 정리
			minus_division($temp['division']);

			// 이전, 다음글에 대한 정리
			if($temp['depth']==0) {
				// 이전글이 있으면 빈자리 메꿈;;;
				if($temp['prev_no']) zb_query("update $t_board"."_$id set next_no='$temp[next_no]' where next_no='$temp[no]'"); 
				// 다음글이 있으면 빈자리 메꿈;;;
				if($temp['next_no']) zb_query("update $t_board"."_$id set prev_no='$temp[prev_no]' where prev_no='$temp[no]'"); 
			} else {
				$temp2=mysql_fetch_array(zb_query("select count(*) from $t_board"."_$id where father='$temp[father]'"));
				// 원본글이 있으면 원본글의 자식 글을 없앰;;;
				if(!$temp2[0]) zb_query("update $t_board"."_$id set child='0' where no='$temp[father]'"); 
			}
			zb_query("delete from $t_comment"."_$id where parent='$selected[$i]'") or Error(zb_error()); // 코멘트삭제

			// 메시지 보내는 부분
			if($notice_user) {
				if($temp['ismember']) {
					$_to = $temp['ismember'];
					$_from = $member['no'];	
					$_subject = ($temp['name'])." 님의 게시물이 ".$_kind."되었습니다";
					$_memo = ($temp['name'])." 님께서 쓰신 \"".($temp['subject'])."\" 글이 $member[name]님에 의해서 게시판 성격에 적합하지 않아서 ".$_kind." 되었습니다\n";
					_send_message($_to,$_from,$_subject,$_memo);
				}
			}
		}  
	}
	$temp = mysql_fetch_array(zb_query("select count(*) from  $t_board"."_$id",$connect));
	zb_query("update $admin_table set total_article='$temp[0]' where name='$id'") or Error(zb_error());
	echo"<script>opener.window.history.go(0);window.close();</script>";
}

/**********************************************************************************
 * Copy All 일때 (선택된 게시물 이동)
 *********************************************************************************/

else if ($exec=="copy_all"||$exec=="move_all") {
	$sitelink1 = req("sitelink1") ?? '';
	$sitelink2 = req("sitelink2") ?? '';
	$email = req("email") ?? '';
	$homepage = req("homepage") ?? '';

	for($i=0;$i<count($selected)-1;$i++) {
		$s_data=mysql_fetch_array(zb_query("select * from $t_board"."_$id where no='$selected[$i]'"));

			
		// 답글이 없을때;; 
		if($s_data && $s_data['arrangenum']==0) {

			// 원본글을 모두 구함
			$result=zb_query("select * from $t_board"."_$id where headnum='$s_data[headnum]' order by arrangenum",$connect) or error(zb_error());
				
			$temp=mysql_fetch_array(zb_query("select max(division) from $t_division"."_$board_name",$connect));
			$max_division=$temp[0];
			$temp=mysql_fetch_array(zb_query("select max(division) from $t_division"."_$board_name where num>0 and division!='$max_division'",$connect));
			if(!$temp[0]) $second_division=0; else $second_division=$temp[0];

   			// 이동할 게시판의 최고 headnum을 구함
			$max_headnum=mysql_fetch_array(zb_query("select min(headnum) from $t_board"."_$board_name where (division='$max_division' or division='$second_division') and headnum>-2000000000",$connect));
			if(!$max_headnum[0]) $max_headnum[0]=0;
			$headnum=$max_headnum[0]-1;
			
			
			
				
			// 이동할 게시판의 이전, 이후글을 구함
			$next_data=mysql_fetch_array(zb_query("select division,headnum,arrangenum from $t_board"."_$board_name where (division='$max_division' or division='$second_division') and headnum>-2000000000 order by headnum limit 1"));
			
			
			
			
			if(!$next_data[0]) $next_data[0]="0";
			else $next_data=mysql_fetch_array(zb_query("select no,headnum,division from $t_board"."_$board_name where division='$next_data[division]' and headnum='$next_data[headnum]' and arrangenum='$next_data[arrangenum]'"));

			$a_category=mysql_fetch_array(zb_query("select min(no) from $t_category"."_$board_name",$connect));
			$category=$a_category[0];

			$next_no=$next_data['no'];
			
			
			$father=0;
			$term_father=0;
			$root_no=0;

			// looping 하면서 데이타 입력
			while($data=mysql_fetch_array($result)) {

				if(!is_dir("./data/$board_name")) {
					@mkdir("./data/$board_name",0777);
				}

				// 업로드된 파일이 있을경우 처리 #1		
				if($data["s_file_name1"]) {
					$temp_ext=time();
					@mkdir("./data/$board_name/".$temp_ext,0777);
					@copy($data["file_name1"] , "./data/$board_name/".$temp_ext."/".$data["s_file_name1"]);
					$data["file_name1"]="data/$board_name/".$temp_ext."/".$data["s_file_name1"];
					@chmod("./".$data["file_name1"],0706);
					@chmod("./data/$board_name/".$temp_ext,0707);
					}
				// 업로드된 파일이 있을경우 처리 #2	
				if($data["s_file_name2"]) {
					$temp_ext=time();
					@mkdir("./data/$board_name/".$temp_ext,0777);
					@copy($data["file_name2"] , "./data/$board_name/".$temp_ext."/".$data["s_file_name2"]);
					$data["file_name2"]="data/$board_name/".$temp_ext."/".$data["s_file_name2"];
					@chmod("./".$data["file_name2"],0706);
					@chmod("./data/$board_name/".$temp_ext,0707);
				}

				$data['name']=addslashes($data['name']);
				$data['subject']=addslashes($data['subject']);
				$sitelink1=addslashes($sitelink1);
				$sitelink2=addslashes($sitelink2);
				$email=addslashes($email);
				$homepage=addslashes($homepage);
				$division=add_division($board_name);
				$data['headnum']=$headnum;
				$data['division']=$division;
				$data['next_no']=$next_no;
				$data['prev_no']=0;
				$data['category']=$category;
				$data['father']=$data['father']+$term_father;
				$data['child']=$data['child']+$term_child;
				
				$data['homepage'] = addslashes($data['homepage']);
				$data['email'] = addslashes($data['email']);
				$data['use_html'] = addslashes($data['use_html']);
				$data['reply_mail'] = addslashes($data['reply_mail']);
				$data['category'] = addslashes($data['category']);
				$data['is_secret'] = addslashes($data['is_secret']);
				$data['sitelink1'] = addslashes($data['sitelink1']);
				$data['sitelink2'] = addslashes($data['sitelink2']);
				$data['file_name1'] = addslashes($data['file_name1']);
				$data['file_name2'] = addslashes($data['file_name2']);
				$data['s_file_name1'] = addslashes($data['s_file_name1']);
				$data['s_file_name2'] = addslashes($data['s_file_name2']);
				$data['x'] = addslashes($data['y']);
				$data['y'] = addslashes($data['x']);
				$data['islevel'] = addslashes($data['islevel']);
				$data['hit'] = addslashes($data['hit']);
				$data['vote'] = addslashes($data['vote']);
				$data['download1'] = addslashes($data['download1']);
				$data['download2'] = addslashes($data['download2']);
				$data['total_comment'] = addslashes($data['total_comment']);
				
				// 게시물 삭제시 기록 남길 경우
				if($notice_bbs) {
					$data['memo'] .= "\n* $member[name]님에 의해서 게시물 ".$_kind."되었습니다 (".date("Y-m-d H:i").")";
				}
					
				$data['memo']=addslashes($data['memo']);

				$query = "insert into $t_board"."_$board_name (division,headnum,arrangenum,depth,prev_no,next_no,father,child,ismember,memo,ip,password,name,homepage,email,subject,use_html,reply_mail,category,is_secret,sitelink1,sitelink2,file_name1,file_name2,s_file_name1,s_file_name2,x,y,reg_date,islevel,hit,vote,download1,download2,total_comment) values ('$data[division]','$data[headnum]','$data[arrangenum]','$data[depth]','$data[prev_no]','$data[next_no]','$data[father]','$data[child]','$data[ismember]','$data[memo]','$data[ip]','$data[password]','$data[name]','$data[homepage]','$data[email]','$data[subject]','$data[use_html]','$data[reply_mail]','$data[category]','$data[is_secret]','$data[sitelink1]','$data[sitelink2]','$data[file_name1]','$data[file_name2]','$data[s_file_name1]','$data[s_file_name2]','$data[x]','$data[y]','$data[reg_date]','$data[islevel]','$data[hit]','$data[vote]','$data[download1]','$data[download2]','$data[total_comment]')";
				
				//echo($query);

				zb_query($query) or error(zb_error());

				$no=mysql_insert_id();
				if(!$father) {
					$root_no=$no;
					$father=$no;
					$term_father=$data['no']-$no;
				}

				// Comment 정리
				$comment_result=zb_query("select * from $t_comment"."_$id where parent='$data[no]' order by reg_date",$connect) or error(zb_error());
				while($comment_data=mysql_fetch_array($comment_result)) {
					$comment_data['memo']=addslashes($comment_data['memo']);
					$comment_data['name']=addslashes($comment_data['name']);
					zb_query("insert into $t_comment"."_$board_name (parent,ismember,name,password,memo,reg_date,ip) values ('$no','$comment_data[ismember]','$comment_data[name]','$comment_data[password]','$comment_data[memo]','$comment_data[reg_date]','$comment_data[ip]')") or error(zb_error());
				}

				zb_query("update $t_category"."_$board_name set num=num+1 where no='$category'",$connect);
			}
			
			$prev_data=mysql_fetch_array(zb_query("select headnum from $t_board"."_$board_name where headnum>'$headnum' order by headnum limit 1"));
			zb_query("update $t_board"."_$board_name set prev_no='$root_no' where headnum='$prev_data[0]'",$connect) or Error(zb_error());


			// 메시지 보내는 부분
			if($notice_user) {
				if($s_data['ismember']) {
					$_to = $s_data['ismember'];
					$_from = $member['no'];	
					$_subject = ($s_data['name'])." 님의 게시물이 ".$_kind."되었습니다";
					$_memo = ($s_data['name'])." 님께서 쓰신 \"".($s_data['subject'])."\" 글이 $member[name]님에 의해서 ".$_kind." 되었습니다\n";
					$_memo .= " 옮겨진 위치 : zboard.php?id=".$board_name."&no=".$no;
					_send_message($_to,$_from,$_subject,$_memo);
				}
			}
		}
	}
	
	$total=mysql_fetch_array(zb_query("select count(*) from $t_board"."_$board_name" ,$connect));
	zb_query("update $admin_table set total_article='$total[0]' where name='$board_name'");


	if($exec=="copy_all") {
		echo"<script> opener.window.history.go(0); window.close(); </script>";
	} else if ($exec=="move_all") {
?>
<html>
<head>
<meta charset="utf-8" />
<script>
window.onload = function () {
	document.frm.submit();
};
</script>
</head>
<body>
<form name="frm" method="post" action="list_all.php">
<input type="hidden" name="id" value="<?=htmlspecialchars($id)?>">
<input type="hidden" name="exec" value="delete_all">
<input type="hidden" name="selected" value="<?=htmlspecialchars($select_list)?>">
</form>
</body>
</html>
<?php
			exit;
	}
}


