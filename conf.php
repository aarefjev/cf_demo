<?php
/**
 * Main config file for CF home project
 *
 * Will put all of the variables for easy reconfig into one file
 *
 * @author Arte Arefjev <arte@artea.info>
 * @version 1.0
 */

$files_main_dir = 'tmpdir';
$filelist_name = 'allfiles.html'; // we need html - so server will return it correctly


$simple_send_url  = 'http://arte.test/simple/consumption.php';
$simple_send_port = 80;


$number_of_messages = 100;

$socket_server_address = '127.0.0.1';
$socket_port = 8889; // which one was not used yet on my dev.ubuntu...?
$ports_limit = 5;
$requests_limit = 100; // requests per second - I am guessing if our procerror is limited by something - this might be really useful
