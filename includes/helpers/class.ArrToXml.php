<?php
class ArrToXml{
    
    static function parse($arr){
        $dom = new DOMDocument('1.0');
        self::recursiveParser($dom,$arr,$dom);
        return $dom->saveXML();
    }
    
    private static function recursiveParser(&$root, $arr, &$dom){
        foreach($arr as $key => $item){
            if(is_array($item) && !is_numeric($key)){
                $node = $dom->createElement($key);
                self::recursiveParser($node,$item,$dom);
                $root->appendChild($node);
            }elseif(is_array($item) && is_numeric($key)){
                self::recursiveParser($root,$item,$dom);
            }else{
                $node = $dom->createElement($key, $item);
                $root->appendChild($node);
            }
        }
    }
    
}