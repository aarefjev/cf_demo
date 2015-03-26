<?php
/**
 * Basic mesage sender/loader for CF home project
 *
 *
 * @author Arte Arefjev <arte@artea.info>
 * @version 1.0
 */

require('conf.php'); 

$ch = curl_init();



for($i=0;$i<$number_of_messages;$i++){

	$json_to_send = '{"userId": "'.rand(1,1234124).'", "currencyFrom": "EUR", "currencyTo": "GBP", "amountSell": '.rand(10,10000).', "amountBuy": 747.10, "rate": 0.7471, "timePlaced" : "24-JAN-15 10:27:44", "originatingCountry" : "FR"}';


	$time_start = microtime(true); // well well... let's see
	
	// nothing too exciting
	curl_setopt($ch, CURLOPT_PORT, $simple_send_port);
	curl_setopt($ch, CURLOPT_URL, $simple_send_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json_to_send);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json') ); // need this to populate $HTTP_RAW_POST_DATA
	
	

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$server_output = curl_exec ($ch);
	
	$time_end = microtime(true);
	$time = $time_end - $time_start;
	
	echo "\n<br>time:".$time."[".$server_output."]\n__________";
}
curl_close ($ch);