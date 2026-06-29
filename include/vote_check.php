<?php
	if(realpath($_SERVER['SCRIPT_FILENAME']) == realpath(__FILE__)) exit;
	
	if(eregi(":\/\/",$dir)||eregi("\.\.",$dir)) $dir ="./";

	if(!$data['vote']) $data['vote']=1;

	$reply_result=zb_query("select * from $t_board"."_$id where headnum='$data[headnum]' and depth>0 order by arrangenum");

	while($reply_data=mysql_fetch_array($reply_result)) {
		include "include/reply_check.php";
		$subject=$reply_data['subject'];
		$a_vote="<a href=apply_vote.php?id=".urlencode($id)."&no=".urlencode($data['no'])."&sub_no=".urlencode($reply_data['no'])."&page=".urlencode($page)."&page_num=".urlencode($page_num)."&select_arrange=".urlencode($select_arrange)."&desc=".urlencode($des)."&cn=".urlencode($sn)."&ss=".urlencode($ss)."&sc=".urlencode($sc)."&keyword=".urlencode($keyword)."&category=".urlencode($category).">";
		$bar_size=(int)(($reply_data['vote']/$data['vote'])*100);
		$vote=$reply_data['vote'];
		include "$dir/vote_list.php";
	}



