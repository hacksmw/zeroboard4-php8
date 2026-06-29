<?php
if(realpath($_SERVER['SCRIPT_FILENAME']) == realpath(__FILE__)) exit;

if(!defined("_zb_lib_included")) return;
if(eregi(":\/\/",$dir)||eregi("^\.",$dir)) $dir ="./";

if($setup['use_category']) {

	$c_href="&id=".(isset($id)?urlencode($id):'')."&page=".(isset($page)?urlencode($page):'')."&page_num=".(isset($page_num)?urlencode($page_num):'')."&sn=".(isset($sn)?urlencode($sn):'')."&ss=".(isset($ss)?urlencode($ss):'')."&sc=".(isset($sc)?urlencode($sc):'')."&keyword=".(isset($keyword)?urlencode($keyword):'');
	$c_sort="&select_arrange=".(isset($select_arrange)?urlencode($select_arrange):'')."&desc=".(isset($desc)?urlencode($desc):'');
 
	$a_c_list="<a href=zboard.php?&id=".(isset($id)?urlencode($id):'').">";

	include "$dir/category_head.php";

	for($i=0;$i<count($category_num_c);$i++) {
		if($category==$category_num_c[$i]) $b="<b>"; else $b="";
		$print_category_data="<a href='zboard.php?category=$category_num_c[$i]$c_href$c_sort'>$b$category_name_c[$i] ($category_n_c[$i])</a>";
		include "$dir/category_main.php";
	}  
		
	include "$dir/category_foot.php";
}

