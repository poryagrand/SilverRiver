<?php

namespace PoryaGrand\SilverRiver;

class RiverHandleException extends \Exception{}

/**
 * river template engine renderer. simple view renderer
 */
class River{

    protected $__shareData = array();
    protected $__extention = "river.php";

    function __construct($extention=null)
    {
        if( is_string($extention) ){
            $this->__extention = $extention;
        }
    }


    /**
     * set or get shared values in view 
     * @param string $name
     * @param mixed $value
     * @return string|null
     */
    public function share($name=null,$value=null){
        if( $name===null ){
            return $this->__shareData;
        }
        else if( $value === null ){
            $temp = &$this->__shareData;
            if( isset($temp[$name]) ){
                return $temp[$name];
            }
            return null;
        }
        $this->__shareData[$name] = $value;
        return $this->__shareData[$name];
    }

    public function removeShare($name){
        $temp = &$this->__shareData;
        if( isset($temp[$name]) ){
            unset($this->__shareData[$name]);
            return true;
        }
        return false;
    }

    /**
     * set or get shared values in view  in bulk mode
     * @param array $arr
     * @return string|null
     */
    public function bulk_share($arr){
        if( is_array($arr) ){
            foreach( $arr as $key=>$val ){
                $this->share($key,$val);
            }
        }
    }

    /**
     * call inline directive to stack 
     * @param string $name
     * @param callback $callback
     */
    public function callInlineDirective($name,$args=[]){
        $args = array_merge([&$this],$args);
        return call_user_func_array(array(RiverCompiler::class,"callInlineDirective"),array_merge([$name],$args));
    }

    /**
     * call block directive to stack 
     * @param string $name
     * @param callback $callback
     */
    public function callBlockDirective($name,$args=[]){
        $args = array_merge([&$this],$args);
        return call_user_func_array(array(RiverCompiler::class,"callBlockDirective"),array_merge([$name],$args));
    }

    /**
     * call attribute to stack 
     * @param string $name
     * @param callback $callback
     */
    public function callAttribute($name,$args=[]){
        $args = array_merge([&$this],$args);
        return call_user_func_array(array(RiverCompiler::class,"callAttribute"),array_merge([$name],$args));
    }

    /**
     * call block tag to stack 
     * @param string $name
     * @param callback $callback
     */
    public function callInlineTag($name,$args=[]){
        $args = array_merge([&$this],$args);
        return call_user_func_array(array(RiverCompiler::class,"callTagInline"),array_merge([$name],$args));
    }

    /**
     * call block tag to stack 
     * @param string $name
     * @param callback $callback
     */
    public function callBlockTag($name,$args=[]){
        $args = array_merge([&$this],$args);
        return call_user_func_array(array(RiverCompiler::class,"callTagBlock"),array_merge([$name],$args));
    }

    /**
     * add inline directive to stack 
     * @param string $name
     * @param callback $callback
     */
    public static function directiveInline($name,$callback){
        RiverCompiler::inlineDirective($name,$callback);
    }

    /**
     * add block directive to stack 
     * @param string $name
     * @param callback $callback
     */
    public static function directiveBlock($name,$callback){
        RiverCompiler::blockDirective($name,$callback);
    }

    /**
     * add attribute to stack 
     * @param string $name
     * @param callback $callback
     */
    public static function attribute($name,$callback){
        RiverCompiler::attribute($name,$callback);
    }

    /**
     * add inline tag to stack 
     * @param string $name
     * @param callback $callback
     */
    public static function inlineTag($name,$callback){
        RiverCompiler::tagInline($name,$callback);
    }

    /**
     * add block tag to stack 
     * @param string $name
     * @param callback $callback
     */
    public static function blockTag($name,$callback){
        RiverCompiler::tagBlock($name,$callback);
    }


    public function render($path,$shareData=null,$evalData=true){
        $tpath = str_replace(".".$this->__extention,"",$path) . "." .  $this->__extention;
        if( is_array($shareData) ){
            $this->__shareData = $shareData;
        }

        if( !RiverCache::is($path) ){
            RiverCache::save(
                $path,
                call_user_func_array([RiverCompiler::class,"compile"],[$path, &$this ])
            );
        }
        
        $path = RiverCache::path($path);
 

        if( $evalData ){
            $content = $this->__getEvaluated($path);
        }
        else{
            $content = file_get_contents($path);
        }


        if( $content === null ){
            throw new RiverHandleException("there is an error in rendering file `$tpath`");
        }

        return $content;
    }

    /**
     * @brief get content of a php source after compiling php as html
     * @param[in] string $path the path of the desired file
     * @return string|null
     */
    protected function __getEvaluated($path){
        $output = "";
        if( is_file($path) && file_exists($path) ){
            ob_start();
            include $path;
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }
        return null;
    }

    public static function eval($code){
        $output = "";
        if( !empty($code) ){
            ob_start();
            eval($code);
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }
        return null;
    }
}