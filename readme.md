# xml 解析和包装器

## 支持cdata解析和包装 （cdata 不能是xml的顶级标签）

## 解析支持类的继承

## 支持微信的xml包装

## 解析时支持不同字符集转化为utf-8字符集，请自行修改 $format 属性

## 支持文件读取和字符串读取

## 注意： 
1. 转化为xml时必须为utf-8
2. false 转为0, true转为1 null不输出
3. number类型不加CDATA,string类型加CDATA
## 用法

```
    $json = ["from"=>2,"to"=>true,"message"=>["sdf" => "1sdf"]];
    $str = Xml::encode($json,'xml'); //转为xml格式,不带声明
    $str = Xml::encode($json,'xml',true); //转为xml格式,带声明
    $array = Xml::toArray($str);//转为Array格式
```

