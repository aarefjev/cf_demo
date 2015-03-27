<?php
/**
 * Basic mesage sender/loader for CF home project
 *
 *
 * @author Arte Arefjev <arte@artea.info>
 * @version 1.0
 */
 
set_time_limit(0); // no time limit - who knows how many messages we are going to send?


require('conf.php'); 

$ch = curl_init();

$currencies = array('EUR','USD','CAD','AUD','GBP'); // total 5
$countries = array('FR','IE','AU','US'); // total 4

$time_start0 = microtime(true);

for($i=0;$i<$number_of_messages;$i++){
	$curr1=$currencies[rand(0,4)];
	$curr2=$currencies[rand(0,4)]; // I know - it should not match $curr1 - but want to add error checking in processor
	$country = $countries[rand(0,3)];
	
	
	// time - random
	$hr = sprintf("%'.02d", rand(0,23));
	$mn = sprintf("%'.02d", rand(0,59));
	$sc = sprintf("%'.02d", rand(0,59));
	
	// rate 0.1000 -> 1.8000
	$rate = (rand(1000,18000))/10000;
	
	$json_to_send = '{"userId": "'.rand(1,1234124).'", "currencyFrom": "'.$curr1.'", "currencyTo": "'.$curr1.'", "amountSell": '.rand(10,10000).', "amountBuy": '.rand(10,10000).', "rate": '.$rate.', "timePlaced" : "25-MAR-15 '.$hr.':'.$mn.':'.$sc.'", "originatingCountry" : "'.$country.'"}';


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
	// echo ". ";
}
$time_end0 = microtime(true);
$time0 = $time_end0 - $time_start0;
echo "\n<br>time total: ".$time0."";

curl_close ($ch);