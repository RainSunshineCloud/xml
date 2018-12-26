<?php
namespace RainSunshineCloud;

class Xml
{
	protected static $xml_file = "php://input";
	protected static $format = 'UTF-8';
	protected static $xml = null;
	public static $encodeParser = 'encodeWechat';

	/**
	 * xml转为对象
	 * @param  string|null $string     [xml字符串]
	 * @param  string      $class_name [类名]
	 * @return 转为类名
	 */
	public static function toObj(string $string = null,$class_name = 'SimpleXMLElement')
	{
		if ($string === null) {
			$string = file_get_contents($xml_file);
		}


		if (self::$format !== 'UTF-8') {
			$string = mb_convert_encoding($string, 'UTF-8',$xml_file);
		}

		return simplexml_load_string($string,$class_name,LIBXML_NOCDATA);
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
	public static function encode($entry,$value = null)
	{
		if (is_array($entry)) {
			return self::encodeArray($entry);
		} else if (is_object($entry)) {
			return self::encodeObj($entry);
		} else if (is_string($entry) && json_decode($entry)) {
			return self::encodeJson($entry);
		} else if (is_string($entry) && $value != null) {
			return self::encodeString($entry,$value);
		} else {
			throw new XmlException('不支持该类型转化',1);
		}
	}

	/**
	 * 微信xml
	 * @param  string
	 * @return [type]
	 */
	protected static function encodeWechat(array $arr)
	{
		$arr = ['xml' => $arr];
		$str = self::encodeDefault($arr);
		return str_replace('<?xml version="1.0" encoding="UTF-8"?>','',$str);
	}

	/**
	 * 数组变为xml
	 * @param  array
	 * @return [type]
	 */
	public static function encodeArray(array $arr)
	{
		$parse = self::$encodeParser;
		return self::{$parse}($arr);
	}

	/**
	 * 默认xml
	 * @param  array
	 * @return [type]
	 */
	public static function encodeDefault(array $arr)
	{
		self::$xml = new DOMDocument('1.0', 'UTF-8');

		if (isset($arr['cdata'])) {
			throw new XmlException('xml根元素不能是cdata',2);
			return false;
		}
		self::appendElements($arr,self::$xml);
		$str = self::$xml->saveXML();
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
			if ($node == 'cdata') {
				if (!is_string($child)) {
					$child = json_encode($child);
				} 
				$cdata_obj = self::$xml->createCDATASection($child);
				$element_nodes_obj->appendChild($cdata_obj);
			}else if (is_array($child)) {
				$new_nodes_obj = self::$xml->createElement($node);
				self::appendElements($child,$new_nodes_obj);
				$element_nodes_obj->appendChild($new_nodes_obj);
				//删除引用，以便回收
				$new_nodes_obj = null;
			} else {
				$element = self::$xml->createElement($node,$child);
				$element_nodes_obj->appendChild($element);
				//删除引用以便回收
				$element = null;
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
	protected static function encodeObj(object $obj)
	{
		return self::encodeArray(self::objectToArray($obj));
	}

	/**
	 * json转为xml
	 * @param  string
	 * @return [type]
	 */
	protected static function encodeJson(string $string)
	{
		return self::encodeArray(json_decode($string,true));
	}

	/**
	 * 键值转化为xml
	 * @param  string
	 * @param  [type]
	 * @return [type]
	 */
	protected static function encodeString(string $key, $value)
	{
		return self::encodeArray([$key,$value]);
	}

	/**
	 * 对象转数组
	 * @param  object
	 * @return [type]
	 */
	protected static function objectToArray(object $obj)
	{
		return json_decode(json_encode($obj),true);
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