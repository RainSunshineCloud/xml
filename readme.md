# xml 解析和包装器

- 支持文件读取和字符串读取

### 注意事项： 
1. 转化为xml时必须为utf-8
2. false 转为0, true转为1 null不输出
3. number类型不加CDATA,string类型加CDATA

### 用法
1. 转为xml格式

```
    $array = ["from"=>2,"to"=>true,"message"=>["sdf" => "1sdf"]];
    $json = "{"2":"1"}";
    $object = new Object();
    $str = "skdjfk";
    $str = Xml::encode($json,'xml'); //转为xml格式,不带声明
    $str = Xml::encode($json,'xml',true); //转为xml格式,带声明
```

2. Xml文件转其他类型
```

Xml::setFile("php://input"); 
$array = Xml::toArray();//转为Array格式
$object = Xml::toObject();//转为Object格式
$object = Xml::toObject(null,"xml");//转为Object并添加至xml对象内
$Josn = Xml::toJson('');//转为Object并添加至xml对象内

```

3. Xml字符串转其他类型
```
    $array = Xml::toArray($str);//转为Array格式
    $object = Xml::toObject($str);//转为Object格式
    $object = Xml::toObject($str,"xml");//转为Object并添加至xml对象内
    $Josn = Xml::toJson($str);//转为Object并添加至xml对象内
```

4. 设置来源的字符集类型
```
 Xml::setCharset("gb2312");

```