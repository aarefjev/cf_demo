<?php
/**
 * Master Socket server for CF home project
 *
 *
 * @author Arte Arefjev <arte@artea.info>
 * @version 1.0
 */
 
error_reporting(E_ALL); // Hello errors!
set_time_limit(0);	// Should be set to 0 to run forever
ob_implicit_flush();	// No buffers

$starttime = round(microtime(true),2);

echo "try to start...\n";
$socket = stream_socket_server("tcp://127.0.0.1:8889", $errno, $errstr);

if (!$socket) {
	echo "socket unavailable\n";
    die($errstr. "(" .$errno. ")\n");
}


$connects = array();

while (true) {
	echo "main while...\n";
	
    // ok - we need an array of sockets that we have
    $read = $connects;
    $read[] = $socket;
    $write = $except = null;
	
	
	echo "A1\n";
    if (!stream_select($read, $write, $except, null)) { // no timeout is needed. Awaiting new sockets awailable for reading
        break;
    }else{
		echo "A1.1\n";
		echo "\nAAAAAA -> E".print_r($read,1).' EConn:'.print_r($connects,1)." \n";
	}
	echo "A2\n";

    if (in_array($socket, $read)) { // looks like we have a new connection -> hello handshake!

        if (($connect = stream_socket_accept($socket, -1)) && $info = handshake($connect)) {
			echo "new connection...\n";            
			echo "connect=".$connect.", info=".print_r($info,1)."\nOK\n";          
			//echo "info\n";     
			//var_dump($info); 

			$connects[] = $connect; // add connection to the master list
            on_open($connect, $info); // If we need something to do on Open
        }
        unset($read[ array_search($socket, $read) ]);
    }

    foreach($read as $connect) { // Reading data from connection
        $data = fread($connect, 100000);

        if (!$data) { // looks like connection was closed
			echo "connection closed... \n";    
			fclose($connect);
            unset($connects[ array_search($connect, $connects) ]);
            on_close($connect); // Call on close actions
            continue;
        }
		sleep(4); // testing frame limter - but have to check microtime there first
        on_message($connect, $data); // on message event
		send_to_all($data);
    }

	if( ( round(microtime(true),2) - $starttime) > 100) { 
		echo "time = ".(round(microtime(true),2) - $starttime); 
		echo "exit \n\r\n"; 
		fclose($socket);
		echo "connection closed OK\n\r\n"; 
		exit();
	}
}

fclose($socket);



/**
 * Stole that function from somewhere - needed for WebSockets handshake
 * 
 * @param <type> $connect 
 * 
 * @return <type>
 */
function handshake($connect) { // handshake function
    $info = array();

    $line = fgets($connect);
    $header = explode(' ', $line);
    $info['method'] = $header[0];
    $info['uri'] = $header[1];

    while ($line = rtrim(fgets($connect))) {
        if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
            $info[$matches[1]] = $matches[2];
        } else {
            break;
        }
    }

    $address = explode(':', stream_socket_get_name($connect, true)); 
    $info['ip'] = $address[0];
    $info['port'] = $address[1];

    if (empty($info['Sec-WebSocket-Key'])) {
        return false;
    }

    $SecWebSocketAccept = base64_encode(pack('H*', sha1($info['Sec-WebSocket-Key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
    $upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
        "Upgrade: websocket\r\n" .
        "Connection: Upgrade\r\n" .
        "Sec-WebSocket-Accept:".$SecWebSocketAccept."\r\n\r\n";
    fwrite($connect, $upgrade);

    return $info;
}



/**
 * Stole from somewhere as well - really usefull for WebSockets
 * 
 * @param <type> $payload 
 * @param <type> $type  
 * @param <type> $masked  
 * 
 * @return <type>
 */
function encode($payload, $type = 'text', $masked = false) {
    $frameHead = array();
    $payloadLength = strlen($payload);

    switch ($type) {
        case 'text':
            // first byte indicates FIN, Text-Frame (10000001):
            $frameHead[0] = 129;
            break;

        case 'close':
            // first byte indicates FIN, Close Frame(10001000):
            $frameHead[0] = 136;
            break;

        case 'ping':
            // first byte indicates FIN, Ping frame (10001001):
            $frameHead[0] = 137;
            break;

        case 'pong':
            // first byte indicates FIN, Pong frame (10001010):
            $frameHead[0] = 138;
            break;
    }

    // set mask and payload length (using 1, 3 or 9 bytes)
    if ($payloadLength > 65535) {
        $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
        $frameHead[1] = ($masked === true) ? 255 : 127;
        for ($i = 0; $i < 8; $i++) {
            $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
        }
        // most significant bit MUST be 0
        if ($frameHead[2] > 127) {
            return array('type' => '', 'payload' => '', 'error' => 'frame too large (1004)');
        }
    } elseif ($payloadLength > 125) {
        $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
        $frameHead[1] = ($masked === true) ? 254 : 126;
        $frameHead[2] = bindec($payloadLengthBin[0]);
        $frameHead[3] = bindec($payloadLengthBin[1]);
    } else {
        $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
    }

    // convert frame-head to string:
    foreach (array_keys($frameHead) as $i) {
        $frameHead[$i] = chr($frameHead[$i]);
    }
    if ($masked === true) {
        // generate a random mask:
        $mask = array();
        for ($i = 0; $i < 4; $i++) {
            $mask[$i] = chr(rand(0, 255));
        }

        $frameHead = array_merge($frameHead, $mask);
    }
    $frame = implode('', $frameHead);

    // append payload to frame:
    for ($i = 0; $i < $payloadLength; $i++) {
        $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
    }

    return $frame;
}


/**
 * Stole from somewhere as well - really usefull for WebSockets
 * 
 * @param <type> $data 
 * 
 * @return <type>
 */
function decode($data) {
    $unmaskedPayload = '';
    $decodedData = array();

    // estimate frame type:
    $firstByteBinary = sprintf('%08b', ord($data[0]));
    $secondByteBinary = sprintf('%08b', ord($data[1]));
    $opcode = bindec(substr($firstByteBinary, 4, 4));
    $isMasked = ($secondByteBinary[0] == '1') ? true : false;
    $payloadLength = ord($data[1]) & 127;

    // unmasked frame is received:
    if (!$isMasked) {
        return array('type' => '', 'payload' => '', 'error' => 'protocol error (1002)');
    }

    switch ($opcode) {
        // text frame:
        case 1:
            $decodedData['type'] = 'text';
            break;

        case 2:
            $decodedData['type'] = 'binary';
            break;

        // connection close frame:
        case 8:
            $decodedData['type'] = 'close';
            break;

        // ping frame:
        case 9:
            $decodedData['type'] = 'ping';
            break;

        // pong frame:
        case 10:
            $decodedData['type'] = 'pong';
            break;

        default:
            return array('type' => '', 'payload' => '', 'error' => 'unknown opcode (1003)');
    }

    if ($payloadLength === 126) {
        $mask = substr($data, 4, 4);
        $payloadOffset = 8;
        $dataLength = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset;
    } elseif ($payloadLength === 127) {
        $mask = substr($data, 10, 4);
        $payloadOffset = 14;
        $tmp = '';
        for ($i = 0; $i < 8; $i++) {
            $tmp .= sprintf('%08b', ord($data[$i + 2]));
        }
        $dataLength = bindec($tmp) + $payloadOffset;
        unset($tmp);
    } else {
        $mask = substr($data, 2, 4);
        $payloadOffset = 6;
        $dataLength = $payloadLength + $payloadOffset;
    }

    /**
     * We have to check for large frames here. socket_recv cuts at 1024 bytes
     * so if websocket-frame is > 1024 bytes we have to wait until whole
     * data is transferd.
     */
    if (strlen($data) < $dataLength) {
        return false;
    }

    if ($isMasked) {
        for ($i = $payloadOffset; $i < $dataLength; $i++) {
            $j = $i - $payloadOffset;
            if (isset($data[$i])) {
                $unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
            }
        }
        $decodedData['payload'] = $unmaskedPayload;
    } else {
        $payloadOffset = $payloadOffset - 4;
        $decodedData['payload'] = substr($data, $payloadOffset);
    }

    return $decodedData;
}

/**
 * 
 * 
 * @param <type> $connect 
 * @param <type> $info 
 * 
 * @return <type>
 */
function on_open($connect, $info) {
    echo "open OK\n\n";
    //fwrite($connect, encode('Good - we are connected'));
}

.
/**
 * 
 * 
 * @param <type> $connect 
 * 
 * @return <type>
 */
function on_close($connect) {
    echo "close OK [".print_r($connect)."]\n\n";
}

/**
 * 
 * 
 * @param <type> $data 
 * 
 * @return <type>
 */
function send_to_all( $data){
	global $connects;
	$f = decode($data);
	foreach($connects AS $conn){
		fwrite($conn, encode($f['payload']));
	}
}


/**
 * 
 * 
 * @param <type> $connect 
 * @param <type> $data 
 * 
 * @return <type>
 */
function on_message($connect, $data) {
	global $read, $all_connections;
    $f = decode($data);
	echo "Message:";
	echo $f['payload'] . "[".print_r($all_connections,1)."]\n\n";
    fwrite($connect, encode($f['payload']));
}

