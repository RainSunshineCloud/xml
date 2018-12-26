<?php
require_once '../vendor/autoload.php';
use RainSunshineCloud\Xml;
use RainSunshineCloud\XmlException;
try{
	$json = ["from"=>2,"to"=>true,"message"=>["sdf" => "1sdf"]];
	$str = Xml::encode($json,'xml'); //转为xml格式
	$array = Xml::toArray($str);
} catch (XmlException $e) {
    echo $e->getMessage();
}