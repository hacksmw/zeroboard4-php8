<?php
/***************************************************************************
 * 여러번 호출시 에러 발생 금지
 **************************************************************************/
	//if($_head_php_excuted) return;
	//$_head_php_excuted = true;

/***************************************************************************
 * 기본 라이브러리 include 
 **************************************************************************/
if(realpath($_SERVER['SCRIPT_FILENAME']) == realpath(__FILE__)) exit;

// 라이브러리 함수 파일 include
//if(eregi(":\/\/",$_zb_path)||eregi("\.\.",$_zb_path)||eregi("^\/",$_zb_path)||eregi("data:;",$_zb_path)) $_zb_path ="./";
$_zb_path = str_replace('_head.php','',realpath(__FILE__));

require_once $_zb_path."lib.php";

// 내부에서 사용하는 변수 덮어씌우지 않게	
unset($use_division);
unset($prevdivpage);
unset($nextdivpage);
unset($hide_cart_start);
unset($hide_cart_end);
unset($result);
unset($result2);
unset($total);

unset($_zboardis);
unset($href);
unset($sort);
unset($is_admin);
unset($s_que);
unset($width);
unset($t_s_que);

/***************************************************************************
 * 현재 _head.php를 호출하는 파일이 게시판 관련 파일인지 검사
 **************************************************************************/
$_zb_file_list = array("apply_vote.php","comment_ok.php","del_comment.php","del_comment_ok.php","delete.php","download.php","list_all.php","view.php","vote.php","write.php","write_ok.php","zboard.php","image_box.php");
$_zb_c = count($_zb_file_list);
for($i=0;$i<$_zb_c;$i++) {
	if(strpos(strtolower($_SERVER["PHP_SELF"]), $_zb_file_list[$i]) !== false) { $_zboardis = TRUE; break; }
	else $_zboardis = FALSE;
}

// 리스트 체크 함수 파일 include
if($_zboardis) include "include/list_check.php";

// 명시적인 extract
if (isset($_REQUEST["id"])) {
	$id = req("id");
	if ($id && !ctype_alnum(str_replace('_', '', $id))) {
		error("id가 올바르지 않습니다.");
	}
} else {
	unset($id);
}

if (isset($_REQUEST["category"])) {
	$category = $_REQUEST["category"];
	if ($category && !is_numeric($category)) {
		error("값이 올바르지 않습니다.");
	}
} else {
	unset($category);
}

if (isset($_REQUEST["sn1"])) {
	$sn1 = $_REQUEST["sn1"];
	if ($sn1 && !ctype_alnum(str_replace('_', '', $sn1))) {
		error("값이 올바르지 않습니다.");
	}
} else {
	unset($sn1);
}
		
if (isset($_REQUEST["sn"])) {
	$sn = $_REQUEST["sn"];
	if ($sn && !ctype_alnum(str_replace('_', '', $sn))) {
		error("값이 올바르지 않습니다.");
	}
} else {
	unset($sn);
}
		
if (isset($_REQUEST["ss"])) {
	$ss = $_REQUEST["ss"];
	if ($ss && !ctype_alnum(str_replace('_', '', $ss))) {
		error("값이 올바르지 않습니다.");
	}
} else {
	unset($ss);
}
		
if (isset($_REQUEST["sc"])) {
	$sc = $_REQUEST["sc"];
	if ($sc && !ctype_alnum(str_replace('_', '', $sc))) {
		error("값이 올바르지 않습니다.");
	}
} else {
	unset($sc);
}
		
if (isset($_REQUEST["keyword"])) {
	$keyword = $_REQUEST["keyword"];
} else {
	unset($keyword);
}
		
if (isset($_REQUEST["prev_num"])) {
	$prev_num = $_REQUEST["prev_num"];
	if ($prev_num && !is_numeric($prev_num)) {
		error("값이 올바르지 않습니다.");
	}
} else {
	unset($prev_num);
}

if (isset($_REQUEST["divpage"])) {
	$divpage = $_REQUEST["divpage"];
	if ($divpage && !ctype_alnum(str_replace('_', '', $divpage))) {
		error("값이 올바르지 않습니다.");
	}
} else {
	unset($divpage);
}

/***************************************************************************
 * 기본 설정 체크
 **************************************************************************/

// 게시판 $id 체크
if ($_zboardis) {
	if (!isset($id) || !$id) {
		Error("게시판 이름을 지정해 주셔야 합니다.<br><br>예) zboard.php?id=이름",""); // 게시판 이름 체크
	} else {
		if (!ctype_alnum(str_replace('_', '', $id))) {
			error("id 값이 올바르지 않습니다.");
		}			
	}
}
	
/***************************************************************************
 * DB 연결하여 기본 데이타 추출
 **************************************************************************/
// DB 연결
$connect=dbConn();  

// 멤버 정보 구해오기;;; 멤버가 있을때
$_dbTimeStart = getmicrotime();
$member=member_info();
$_dbTime = isset($_dbTime) ? $_dbTime+(getmicrotime()-$_dbTimeStart) : getmicrotime()-$_dbTimeStart;

/***************************************************************************
 * 현재 _head.php를 불러오는 파일이 게시판일경우에 체크 하는 항목들
 **************************************************************************/
if($_zboardis) {

		// 게시판 설정 읽어 오기
	$_dbTimeStart = getmicrotime();
	$setup = get_table_attrib($id); 
	if(!$setup['name']) Error("생성되지 않은 게시판입니다.<br><br>게시판을 생성후 사용하십시요",""); // 설정되지 않은 게시판

	// 현재 게시판의 그룹의 설정 읽어 오기
	$group=group_info($setup['group_no']);
	$_dbTime += getmicrotime()-$_dbTimeStart;

	// 현재 로그인되어 있는 멤버가 전체, 그룹관리자, 게시판관리자인지 검사
	$is_admin='';
	if($member['level']!==10) if($member['is_admin']==1||($member['is_admin']==2&&$member['group_no']==$setup['group_no'])||check_board_master($member, $setup['no'])) $is_admin=1;

	// 현재 그룹이 폐쇄그룹이고 로그인한 멤버가 비멤버일때 에러표시
	if($group['is_open']==0&&!$is_admin&&$member['group_no']!=$setup['group_no']) Error("공개 되어 있지 않습니다");

	// 접근 금지 아이피인 경우 금지하기;;;
	if(!$is_admin) check_blockip();

	// 관리자일경우에는 무조건 바구니 기능 활성화 시킴 (게시물 정리를 위해서)
	if($is_admin) $setup['use_cart']=1; 

	// 스킨 디렉토리 : $dir 이라는 변수는 계속해서 스킨경로 파일로 
	$dir="skin/".$setup['skinname'];
	
	// 게시판의 가로크기 설정
	$width=$setup['table_width'];
		
	$category_num_c = array();
	$category_name_c = array();
	$category_n_c = array();
	$category_data = array();
	$_category_data = array();

	// 카테고리 읽어오기
	if($setup['use_category']) {
		$_dbTimeStart = getmicrotime();
		$result=zb_query("select * from $t_category"."_$id order by no");
		$_dbTime += getmicrotime()-$_dbTimeStart;
		$a_category="<select name=category onchange=category_change(this)><option value=''>Category</option>";
		if(!isset($category)) $category = '';
		while($data=mysql_fetch_array($result)) {
			$category_num_c[]=$data['no'];
			$category_name_c[]=$data['name'];
			$category_n_c[]=$data['num'];
			$category_data[$data['no']]=$data['name'];
			$_category_data[$data['no']]=$data['num'];
			if($category==$data['no']) $a_category.="<option value=$data[no] selected>".htmlspecialchars($data['name'])."</option>";
			else $a_category.="<option value=$data[no]>".htmlspecialchars($data['name'])."</option>";
		}
		$a_category.="</select>";
	} else {
		$category="";
	}
	
	/////////////////////////////////////////////
	// write.php가 아닐때 검색갯수 및 query 정리
	/////////////////////////////////////////////
	if(strpos(strtolower($_SERVER['PHP_SELF']),"write.php") === false) {

		// Division의 현황을 체크
		$_dbTimeStart = getmicrotime();
		$division_result=zb_query("select * from $t_division"."_$id where num>0 order by division desc");
		$_dbTime += getmicrotime()-$_dbTimeStart;
		$total_division=mysql_num_rows($division_result);
		$sum=0;
		$division=0;

		// division 페이지가 없으면 설정 (검색시 사용하는 단위페이지)
		if(empty($divpage)) $divpage = $total_division;
		if($divpage<$total_division) $prevdivpage = intval($divpage) +1;
		if($divpage>1) $nextdivpage = $divpage -1;

		// 정렬 기능 : $select_arrange 는 정렬 필드, $desc 는 정렬, 역정렬시
		if(!isset($select_arrange)) $select_arrange="headnum";
		if(!isset($desc)) $desc="asc";

		// 답글 목록에 나타나지 않게 설정하였을때 (게시판 설정시 use_showreply가 체크 되었을때)
		if(!$setup['use_showreply']) if(!isset($s_que)) $s_que=" arrangenum=0 "; else $s_que.=" and arrangenum=0 ";
	
		// 카테고리 : 카테고리가 있을때 category를 검색 조건에 넣음
		if($category) if(!isset($s_que)) $s_que=" category='$category' "; else $s_que.=" and category='$category'";
	
		// 검색 기능 체크, $sn 이름 $ss 제목 $sc 내용 검사, $keyword 내용;;
		$keyword=isset($keyword)?($keyword):'';
		$keyword=str_replace("`","",$keyword);
		$keyword=str_replace("\"","",$keyword);
		$keyword=str_replace("'","",$keyword);
		if(empty($sn)) $sn="off";
		if(empty($ss)) $ss="off";
		if(empty($sc)) $sc="off";
		if($sc=="off"&&$sn=="off"&&$ss=="off") {
			$sc="on";
			$ss="on";
		}
		if(!isblank($keyword)) {
			$keyword=addslashes($keyword);
			
			if(!isset($sn1)) {
				if($sn=="on"&&isset($t_s_que)) $t_s_que=isset($t_s_que) ? $t_s_que." or name like '%$keyword%' " : " or name like '%$keyword%' "; 
				elseif($sn=="on") $t_s_que=isset($t_s_que) ? $t_s_que." name like '%$keyword%' " : " name like '%$keyword%' ";
			} else {
				if($sn=="on"&&isset($t_s_que)) $t_s_que=isset($t_s_que) ? $t_s_que." or name = '$keyword' " : " or name = '$keyword' "; 
				elseif($sn=="on") $t_s_que=isset($t_s_que) ? $t_s_que." name = '$keyword' " : " name = '$keyword' ";
			}
			if($ss=="on"&&isset($t_s_que)) $t_s_que=isset($t_s_que) ? $t_s_que." or subject like '%$keyword%' ":" or subject like '%$keyword%' ";
			elseif($ss=="on") $t_s_que=isset($t_s_que) ? $t_s_que." subject like '%$keyword%' ": " subject like '%$keyword%' ";
			if($sc=="on"&&isset($t_s_que)) $t_s_que=isset($t_s_que) ? $t_s_que." or memo like '%$keyword%' ":" or memo like '%$keyword%' ";
			elseif($sc=="on") $t_s_que=isset($t_s_que) ? $t_s_que." memo like '%$keyword%' ": " memo like '%$keyword%' ";
			if(isset($s_que)) $s_que.=" and ( ".$t_s_que." ) ";
			else $s_que= " ( ".$t_s_que." ) ";
			
			$keyword=stripslashes($keyword);
		}
		
		// 검색 조건이 있을때 앞에 where 문 추가
		if(isset($s_que)) $s_que=" where ".$s_que;

		// 전체개수를 구함 : 검색어가 있을때는 따로 전체 갯수를 구함, 아니면 게시판에 있는것으로
		if(isset($s_que)) {
			if(!$keyword&&$setup['use_showreply']) {
				// 카테고리만 있을 경우
				$total=$_category_data[$category];
			} else {
				// 검색어나 답글없음이 체크되어 있을경우
				$use_division = true;
				$s_que = str_replace("where","where division='$divpage' and ", $s_que);
				$_dbTimeStart = getmicrotime();
				$temp=mysql_fetch_array(zb_query("select count(*) from $t_board"."_$id $s_que ",$connect));
				$_dbTime += getmicrotime()-$_dbTimeStart;
				$total=$temp[0];
			}
		} else {
			$total=$setup['total_article'];
		}

		// 페이지 관련 변수값 정함
		
		$page_num=$setup['memo_num'];
		
		if(!isset($page)) $page=1; // 만약 $page라는 변수에 값이 없으면 임의로 1 페이지 입력
		
		$total_page=(int)(($total-1)/$page_num)+1; // 전체 페이지 구함
		
		if($page>$total_page) $page=$total_page; // 페이지가 전체 페이지보다 크면 페이지 번호 바꿈
		

		$start_num=($page-1)*$page_num; // 페이지 수에 따른 출력시 첫번째가 될 글의 번호 구함
	}
		
	$href = "id=".urlencode($id)."&page=".urlencode($page)."&divpage=".urlencode($divpage);
		
	if(isset($category)) $href.="&category=".urlencode($category);
	if(isset($sn1)) $href.="&sn1=".urlencode($sn1);
	if(isset($sn)) $href.="&sn=".urlencode($sn);
	if(isset($ss)) $href.="&ss=".urlencode($ss);
	if(isset($sc)) $href.="&sc=".urlencode($sc);
	if(isset($keyword)) $href.="&keyword=".urlencode($keyword);
	if(isset($prev_num)) $href.="&prev_num=".urlencode($prev_num);
		
	if(isset($select_arrange)) $sort="&select_arrange=$select_arrange";
	if(isset($desc)) $sort.="&desc=$desc";

	// 카테고리를 나타나게 하는 변수
	if(!$setup['use_category']) {
		$hide_category_start='<!--';
		$hide_category_end='-->';
	} else {
		$hide_category_start='';
		$hide_category_end='';
	}

	// 바구니를 나타나게 하는 변수
	if($is_admin||$setup['use_cart']) {
		$a_cart="<a onfocus=blur() href='javascript:reverse()'>";
	} else {
		$hide_cart_start="<!--";
		$hide_cart_end="-->";
		$a_cart=""; 
	}

	// 모두삭제 버튼
	if($is_admin) $a_delete_all="<a onfocus=blur() href='javascript:delete_all()'>"; else $a_delete_all="<Zeroboard ";
	
	// 통계버튼
	if($setup['use_status']) {
		$a_status="<a onfocus=blur() href=javascript:void(window.open('stat.php?id=$id','status','width=400,height=400,statusbar=no,toolbar=no,resizable=no'))>"; 
	} else { 
		$a_status="<Zeroboard ";
	}
	
	$a_status="<Zeroboard ";

	// Setup 버튼
	if($is_admin) $a_setup="<a onfocus=blur() href='admin.php' target=_blank>"; else $a_setup="<Zeroboard ";

	// 현재 멤버의 새 쪽지가 있을때 아이콘 변경;;
	if(isset($member['no'])) {
		if(!empty($member['new_memo'])) {
			$member_memo_icon="<img name=memozzz src=$dir/member_memo_on.gif border=0 align=absmiddle>";
			$memo_on_sound="<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0' width='0' height='0'><param name=menu value=false><param name=wmode value=transparent><param name=movie value='$dir/memo_on.swf'><param name=quality value=low><param name='LOOP' value='false'><embed src='$dir/memo_on.swf' quality=low pluginspage='http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash' type='application/x-shockwave-flash' width='0' height='0' loop='false' wmode=transparent menu='false'></embed></object>";
			$memo_on_sound.="<audio src=\"$dir/memo_on.mp3\" controls autoplay hidden style=\"display:none;\"><embed src=\"$dir/memo_on.mp3\" width=\"0\" height=\"0\" loop=\"false\" hidden=\"false\"></audio>";
		} else {
			$member_memo_icon="<img src=$dir/member_memo_off.gif border=0 align=absmiddle>";
			unset($memo_on_sound);
		}
	} else {
		$member_memo_icon='';
		unset($memo_on_sound);
	}
}


/***************************************************************************
 * 각종 기본 버튼 설정
 **************************************************************************/

// 로그인, 아웃, 회원 정보 수정, 쪽지 메뉴 버튼

if(!isset($_zb_url)) $_zb_url='';

$s_url = $_SERVER['REQUEST_URI'];

if($id&&strpos(strtolower($s_url),strtolower($id)) === false) {
	if(strpos($s_url,'?') !== false) $s_url = $s_url . "&id=$id";
	else $s_url = $s_url . "?id=$id";
}

$s_url = urlencode($s_url);

if(!isset($member['no'])) {
	$a_login="<a onfocus=blur() href='".$_zb_url."login.php?$href$sort&s_url=$s_url'>";
	$a_logout="<Zeroboard ";
	$a_member_modify="<Zeroboard ";
	$a_member_memo="<Zeroboard ";
} else {
	$a_login="<Zeroboard ";
	$a_logout="<a onfocus=blur() href='".$_zb_url."logout.php?$href$sort&s_url=$s_url'>";
	$a_member_modify="<a onfocus=blur() href=# onclick=\"window.open('".$_zb_url."member_modify.php?group_no=$member[group_no]','zbMemberModify','width=560,height=590,toolbars=no,resizable=yes,scrollbars=yes')\">";
	$a_member_memo="<a onfocus=blur() href=\"javascript:void(window.open('".$_zb_url."member_memo.php','member_memo','width=450,height=500,status=no,toolbar=no,resizable=yes,scrollbars=yes'))\">";
}

// 회원가입버튼;;
if(!isset($member['no'])&&$group['use_join']) $a_member_join="<a onfocus=blur() href=# onclick=\"window.open('".$_zb_url."member_join.php?group_no=$setup[group_no]','zbMemberJoin','width=560,height=590,toolbars=no,resizable=yes,scrollbars=yes')\">"; else $a_member_join="<Zeroboard ";



