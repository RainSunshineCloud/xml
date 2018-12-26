<?php
namespace RainSunshineCloud;

use \DOMDocument;
use \DOMNode;

class Xml
{
	protected static $xml_file = "php://input";
	protected static $format = 'UTF-8';
	protected static $xml = null;

	/**
	 * xml转为对象
	 * @param  string|null $string     [xml字符串]
	 * @param  string      $class_name [类名]
	 * @return 转为类名
	 */
	public static function toObj(string $string = null,$class_name = 'SimpleXMLElement')
	{
		libxml_use_internal_errors(true);

		if ($string === null) {
			$string = file_get_contents($xml_file);
		}

		if (self::$format !== 'UTF-8') {
			$string = mb_convert_encoding($string, 'UTF-8',$xml_file);
		}
		
		libxml_disable_entity_loader(true);
		$res = simplexml_load_string($string,$class_name,LIBXML_NOCDATA);

		if ($res == false) {
			if (isset(libxml_get_errors()[0])) {
				$message = libxml_get_errors()[0]->message;
			} else {
				$message = "转化失败";
			}
			
			throw new XmlException($message,3);
		}

		return $res;
	}

	/**
	 * xml转为json
	 * @param  string|null $string [xml字符串]
	 * @return [type]              [description]
	 */
	public static function toJson(string $string = null)
	{
		return json_encode(self::toObj($string));
	}

	/**
	 * xml转数组
	 * @param  string|null $string [description]
	 * @return [type]              [description]
	 */
	public static function toArray(string $string = null)
	{
		return json_decode(self::toJson($string),true);
	}

	/**
	 * 转化为xml
	 * @param  [type]
	 * @return [type]
	 */
	public static function encode($entry,string $root,$declaration = false)
	{
		if (is_array($entry)) {
			$res = self::encodeArray($entry,$root);
		} else if (is_object($entry)) {
			$res = self::encodeObj($entry,$root);
		} else if (is_string($entry) && json_decode($entry)) {
			$res = self::encodeJson($entry,$root);
		} else if (is_string($entry)) {
			$res = self::encodeString($entry,$root);
		} else {
			throw new XmlException('不支持该类型转化',1);
		}

		if ($declaration) {
			return $res;
		}

		return trim(str_replace('<?xml version="1.0" encoding="UTF-8"?>', "", $res),"\n");
	}

	/**
	 * 默认xml
	 * @param  array
	 * @return [type]
	 */
	public static function encodeArray(array $arr,string $root)
	{
		self::$xml = new DOMDocument('1.0', 'UTF-8');
		$all = [$root=>$arr];
		self::appendElements($all,self::$xml);
		$str = self::$xml->saveXML(null,LIBXML_NOEMPTYTAG);
		self::$xml = null;
		return $str;
	}


	/**
	 * 	添加elements
	 * @param  [array]
	 * @param  [DOMNode]
	 * @return [type]
	 */
	protected static function appendElements(array $arr,DOMNode $element_nodes_obj)
	{
		foreach ($arr as $node => $child) {
			if (is_array($child)) {
				$new_nodes_obj = self::$xml->createElement($node);
				self::appendElements($child,$new_nodes_obj);
				$element_nodes_obj->appendChild($new_nodes_obj);
				//删除引用，以便回收
				$new_nodes_obj = null;
			} else if (is_numeric($child)|| is_bool($child) || is_null($child)) {
				if (is_bool($child)) { //处理布尔值问题
					$child = $child ? 1 : 0;
				}
				if (is_null($child)) {
					continue;
				}
				
				$element = self::$xml->createElement($node,$child);
				$element_nodes_obj->appendChild($element);
				//删除引用以便回收
				$element = null;
			} else if (is_string($child)) {
				$cdata_obj = self::$xml->createCDATASection($child);
				$element = self::$xml->createElement($node);
				$element->appendChild($cdata_obj);
				$element_nodes_obj->appendChild($element);
				//删除引用以便回收
				$element = null;
			} else {
				throw new XmlException('数据源格式错误，内部不能包含对象',4);
			}
		}

		//删除引用以便回收
		$element_nodes_obj = null;
	}

	/**
	 * 对象转化为xml
	 * @param  object
	 * @return [type]
	 */
	protected static function encodeObj(object $obj,$root)
	{
		return self::encodeArray(self::objectToArray($obj),$root);
	}

	/**
	 * json转为xml
	 * @param  string
	 * @return [type]
	 */
	protected static function encodeJson(string $string,string $root)
	{
		if (!$array = json_decode($string,true)) {
			throw new XmlException('json转数组失败',6);
		}
		return self::encodeArray($array,$root);
	}

	/**
	 * 键值转化为xml
	 * @param  string
	 * @param  [type]
	 * @return [type]
	 */
	protected static function encodeString(string $key, string $root)
	{
		return self::encodeArray($key,$root);
	}

	/**
	 * 对象转数组
	 * @param  object
	 * @return [type]
	 */
	protected static function objectToArray(object $obj)
	{
		if (!$str = json_encode($obj)) {
			throw new XmlException('对象转为数组失败',5);
		}

		if (!$array = json_decode($str,true)) {
			throw new XmlException('对象转为数组失败',5);
		}

		return $array;
	}

	/**
	 * 设置来源字符集
	 * @param string
	 */
	public static function setCharset(string $format)
	{
		self::$format = strtoupper($format);
	}

	/**
	 * 设置来源文件
	 * @param string
	 */
	public static function setFile(string $file)
	{
		self::$xml_file = $file;
	}

}

class XmlException extends \Exception {}