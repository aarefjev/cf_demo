<?php
/**
 * Main file for CF socket server project
 *
 * Well well well... we are creating web server here... in PHP
 *
 * @author Arte Arefjev <arte@artea.info>
 * @version 1.0
 */

require('conf.php'); // config file - thank you!
 

set_time_limit(0); // no time limit
ob_implicit_flush(); // don't hold anything - everything directly to the client

if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) < 0) {
	// AF_INET - inet protocol
	// SOCK_STREAM - socket type : sequenced, reliable, full-duplex, connection-based byte streams. 
	// SOL_TCP - protocol - UDP can also be fun ;)
	echo "Fail: Socket creation error.";
} else {
	echo "OK: Socket created\n";
}

// Binding socket with address & port
if (($ret = socket_bind($sock, $socket_server_address, $socket_port)) < 0) {
	echo "Fail: Binding socket with address & port";
} else {
	echo "OK: Binding socket & port successful\n";
}

// Start listening
if (($ret = socket_listen($sock, $ports_limit)) < 0) { // we are limiting number of connections opened
	echo "Fail: Socket connection error";
} else {
	echo "OK: Socket ready to accept messages\n";
}
	
do {
	// accepting socket connection
	if (($msgsock = socket_accept($sock)) === false) {
		echo "\nFail: starting connection with socket. reason: " . socket_strerror(socket_last_error($sock)) . "\n";
	} else {
		echo "\nOK: Socket is ready to accept messages";
	}
	
	$msg_length=0;

	// echo "Message from the client: ";
	// if (false === ($buf = socket_read($msgsock, 2048, PHP_NORMAL_READ))) { 
	if (false === ($buf = socket_read($msgsock, 4096))) {  // reading from the sockeet using buffer
		echo "Error: reading message from the client";        
	} else {
		//if( $buf!=='' ){
			//echo "Buffer: [".$buf."]\n"; // client message ok
			$incoming = explode("\r\n", $buf);
			// print_r($incoming);
			foreach($incoming AS $k=> $header_line){
				if( preg_match("%Content-Length: (.*)%",$header_line,$a) ){
					echo "\nLEN:".$a[1];
					$msg_length = $a[1];
				}
				echo "\n".$k."=".strlen($header_line);
				if(strlen($header_line)==$msg_length){
					// we have JSON
					// *********************************************************************************
					
					$obj = json_decode($json); // god bless json!
					
					// data from that obj can be
					// 1: send via another socket connection to socket.io and rendered on a frontend part of it
					// 2: or saved into SQL || NoSQL solution
					// 3: or saved as a file
					// 4: or something else... anything we want actually
					// I like one option here where we can accept both WebSocket connections from the clients (aka browsers with google map interfaces in them) and post data requests in one server.
					
					
					
					// *********************************************************************************
				}
			}
			
		//}
	}
	
	$message='ok';
	$output = "";
	$Header = "HTTP/1.1 200 OK \r\n" .
	"Date: Fri, 31 Dec 1999 23:59:59 GMT \r\n" .
	"Content-Type: text/html \r\n\r\n";
	$output = $Header . $message;

	socket_write($msgsock,$output,strlen($output));
	socket_close($msgsock);

	
} while (true);
	
	
// closing socket
if (isset($sock)) {
	socket_close($sock);
	echo "Socket closed.";
}





