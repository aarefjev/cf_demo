<?php
/**
 * Main config file for CF home project
 *
 * Will put all of the variables for easy reconfig into one file
 *
 * @author Arte Arefjev <arte@artea.info>
 * @version 1.0
 */
 
require('../conf.php');
// print_r($HTTP_RAW_POST_DATA);

$json = $HTTP_RAW_POST_DATA;
$obj = json_decode($json); // god bless json!
// print_r($obj); // debug


/*
 * we should probably put all the checks in there: if format is valid, came from a correct source, data is fine 
 * ->> no time for this now. Not too hard to implement.
 */
 
// info -> timePlaced: "25-MAR-15 06:03:49"

$hr_dirname = substr($obj->timePlaced,10,2);
$min_dirname = substr($obj->timePlaced,13,2);

// $filename = $files_main_dir.'/'.md5($json).'.json'; // md5 seems like a good option for most cases

/* i'll use multifolder structure with filename being DATE-TIME-USERID. 
 * why? just because it looks better and also ordered nicely.
 * 
 */
$filename = $files_main_dir.'/'.$hr_dirname.'/'.$min_dirname.'/'.str_replace(':','_',substr($obj->timePlaced,10,8)).'-'.$obj->userId.'.json'; 


// if main file directory is none-existant - create
if(!is_dir($files_main_dir)){ // was it the fastest way of checking if directory does exist? Another questions here - does it really matter in that sample? Where is the bottleneck - that is the question.
	mkdir($files_main_dir);
}
// if sub directory is none-existant - create
if(!is_dir($files_main_dir.'/'.$hr_dirname)){
	mkdir($files_main_dir.'/'.$hr_dirname);
}
// and so on and so forth - depends on amount of requests / per minute we are going to have remember 64k files in one directory 
if(!is_dir($files_main_dir.'/'.$hr_dirname.'/'.$min_dirname)){
	mkdir($files_main_dir.'/'.$hr_dirname.'/'.$min_dirname);
}

// creating/appening more content to a filelist
$json_prefix = ( is_file($files_main_dir.'/'.$filelist_name) )?',':''; // is it a new file?
$filelist_content = $json_prefix.'"'.$filename.'"';

// writing actual json message file
$ret = file_put_contents($files_main_dir.'/'.$filelist_name,$filelist_content,FILE_APPEND); // we should probably check if writing was a success



file_put_contents($filename,$json);
 