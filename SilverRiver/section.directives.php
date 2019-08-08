<?php

namespace PoryaGrand\SilverRiver;

class Section{
    private static $_sections = [];
    private static $_count = [];

    public static function inc($name){
        $tmp = &static::$_count;
        if( !isset( $tmp[$name] ) ){
            static::$_count[$name] = 0;
        }
        static::$_count[$name]++;
    }

    public static function count($name){
        $tmp = &static::$_count;
        if( !isset( $tmp[$name] ) ){
            static::$_count[$name] = 0;
        }
        return static::$_count[$name];
    }

    /**
     * @param string $name
     * @param string $viewPath
     */
    public static function add($name,$viewPath){
        if( is_string($name) && is_string($viewPath) ){
            self::$_sections[$name] = $viewPath;
        }
    }

    /**
     * @param string $name
     */
    public static function has($name){
        $tmp = &self::$_sections;
        return isset($tmp[$name]);
    }

    /**
     * @param River $ref
     * @param string $name
     * @param array $params
     * @return mixed
     */
    public static function import($ref,$name,$params=[]){
        if( self::has($name) ){
            $__section = self::$_sections[$name];
            if( !is_array($params) ){
                $params = [];
            }

            $pattern = "/\<\!\!\<\!\<\-\-\-\-\-(.+?)\-\-\-\-\-\>\!\>\!\!\>/";

            //array_splice($params,0,0,"\"".$__section."\"");
            
            $out = $ref->callInlineDirective("include",["\"".$__section."\""]);

            $out = preg_replace_callback($pattern,function($matches) use(&$params){
                if( isset($matches[1]) && isset($params[$matches[1]]) ){
                    return $params[$matches[1]];
                }
                else{
                    return "";
                }
            },$out);

            return $out;
        }
        return "";
    }
}

River::directiveBlock("section",function(&$ref,$arg,$content){
    $arg_ = explode(",",$arg,2);
    if( !is_array($arg_) || count($arg_) < 1 ){
        throw new RiverCompileHandleException("section directive name is not specified!");
    }

    
    $name = trim($arg_[0],"'\"");

    if( $arg_ > 1 ){
        $arg = $arg_[1];
    }
    else{
        $arg = "";
    }
    
    if( !Section::has($name) ){
        throw new RiverCompileHandleException("the section '$name' is not exists!");
    }

    $pattern = "/\<\!\!\!\-\-\-\-\-(.+?)\-\-\-\-\-\!\!\!\>/";

    preg_match_all($pattern ,$content,$contentDivs);
    $cdc = count($contentDivs[0]);

    $content = preg_split($pattern , $content);

    $shareInString = "";
    $params = [];

    if( !empty($arg) ){
        $shareInString .= "\$this->bulk_share( $arg );\n";
    }

    if( $cdc == (count($content)-1) && $cdc >= 1 ){
        for($i=1;$i<=$cdc;$i++){
            //$ref->share("SECTION_".$contentDivs[$i][0],$content[$i]);
            $params[$contentDivs[1][$i-1]] = $content[$i];
        }
    }

    return "<?php $shareInString ; \\PoryaGrand\\SilverRiver\\Section::inc('".$name."'); ?>".Section::import($ref,$name,$params);

});

River::directiveInline("block",function($ref,$arg){
    $arg = trim($arg);
    if( preg_match("/^(.+?)$/",$arg) ){
        return "<!!!-----$arg-----!!!>";
    }
    return "";
});


River::directiveInline("content",function($ref,$arg){
    $arg = trim($arg);
    if( preg_match("/^(.+?)$/",$arg) ){
        return "<!!<!<-----$arg----->!>!!>";
    }
    return "";
});