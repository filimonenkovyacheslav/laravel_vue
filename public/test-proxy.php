<?php
$ch = curl_init();  

curl_setopt($ch, CURLOPT_URL, 'https://www.google.com');  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_PROXY, '117.242.152.98:80');  

$output = curl_exec($ch);  
$info = curl_getinfo($ch);
var_dump($info);
//var_dump($output);

curl_close($ch);  

echo '<br><br><br>';

$ch = curl_init();  

//eddie
$useragent="Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";

curl_setopt($ch, CURLOPT_URL, 'https://www.google.com');  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  

//eddie
curl_setopt($ch, CURLOPT_USERAGENT,$useragent);

curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_PROXY, '1.179.180.17:53575');  

$output = curl_exec($ch);  
$info = curl_getinfo($ch);
var_dump($info);

curl_close($ch);  

echo '<br><br><br>';

$ch = curl_init();  

curl_setopt($ch, CURLOPT_URL, 'https://www.google.com');  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_PROXY, '95.143.8.42:32045');  

$output = curl_exec($ch);  
$info = curl_getinfo($ch);
var_dump($info);

curl_close($ch);  


