<?php

namespace PoryaGrand\SilverRiver;

use System\Communicate\Debug\Console;
use System\Security\Crypt;

class RiverCompileHandleException extends \Exception{}

/**
 * compile river template files to php executable files
 */
class RiverCompiler{

    /*const DIRECTIVE = '/(@end([A-Za-z_][\.A-Za-z_0-9]+)|(?<!\\\)(@@|@)(([A-Za-z_][\.A-Za-z_0-9]*)[ \t]*(\(((\\@|(?>[^\(\@\)])|(?4))*)\))|([A-Za-z_][\.A-Za-z_0-9]*))|(@@|@!|@|!)?(?<!\\\)({{)|(}}))/';
*/
    protected static $__inlineStorage = array();
    protected static $__blockStorage = array();

    protected static $__tagBlockStorage = [];
    protected static $__tagInlineStorage = [];

    protected static $__attributeStorage = [];


    public static function &getInlineStorage(){
        return self::$__inlineStorage;
    }

    public static function &getAttributeStorage(){
        return self::$__attributeStorage;
    }

    public static function &getBlockStorage(){
        return self::$__blockStorage;
    }

    public static function &getTagInlineStorage(){
        return self::$__tagInlineStorage;
    }

    public static function &getTagBlockStorage(){
        return self::$__tagBlockStorage;
    }

    /**
     * call an specefic tag attribute
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public static function callAttribute($name,...$args){
        if( $args === null ){
            $args = [];
        }

        $tmp = &self::$__attributeStorage;
        try{
            if( isset($tmp[$name]) && is_callable($tmp[$name]) ){
                return call_user_func_array($tmp[$name],$args);
            }
        }
        catch(\Exception $e){
            Console::halt($e);
        }
        return null;
    }

    /**
     * call an specefic inline tag
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public static function callTagInline($name,...$args){
        if( $args === null ){
            $args = [];
        }

        $tmp = &self::$__tagInlineStorage;
        try{
            if( isset($tmp[$name]) && is_callable($tmp[$name]) ){
                return call_user_func_array($tmp[$name],$args);
            }
        }
        catch(\Exception $e){
            Console::halt($e);
        }
        return null;
    }

    /**
     * call an specefic block tag
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public static function callTagBlock($name,...$args){
        if( $args === null ){
            $args = [];
        }

        $tmp = &self::$__tagBlockStorage;
        try{
            if( isset($tmp[$name]) && is_callable($tmp[$name]) ){
                return call_user_func_array($tmp[$name],$args);
            }
        }
        catch(\Exception $e){
            Console::halt($e);
        }
        return null;
    }

    /**
     * call an specefic block directive
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public static function callBlockDirective($name,...$args){
        if( $args === null ){
            $args = [];
        }

        $tmp = &self::$__blockStorage;
        try{
            if( isset($tmp[$name]) && is_callable($tmp[$name]) ){
                return call_user_func_array($tmp[$name],$args);
            }
        }
        catch(\Exception $e){
            Console::halt($e);
        }
        return null;
    }

    /**
     * call an specefic inline directive
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public static function callInlineDirective($name,...$args){
        if( $args === null ){
            $args = [];
        }

        $tmp = &self::$__inlineStorage;
        try{
            if( isset($tmp[$name]) && is_callable($tmp[$name]) ){
                return call_user_func_array($tmp[$name],$args);
            }
        }
        catch(\Exception $e){
            Console::halt($e);
        }
        return null;
    }

    /**
     * add inline directive to stack 
     * @param string $name
     * @param callback $callback
     * @throws RiverCompileHandleException
     */
    public static function inlineDirective($name,$callback){
        if( !is_string($name) || !is_callable($callback) ){
            throw new RiverCompileHandleException("arguments are not in correct type.");
        }

        $temp = &self::$__inlineStorage;

        if( isset( $temp[$name] ) ){
            throw new RiverCompileHandleException("the inline directive `$name` has been defined before!");
        }

        self::$__inlineStorage[$name] = $callback;
    }

    /**
     * add block directive to stack 
     * @param string $name
     * @param callback $callback
     * @throws RiverCompileHandleException
     */
    public static function blockDirective($name,$callback){
        if( !is_string($name) || !is_callable($callback) ){
            throw new RiverCompileHandleException("arguments are not in correct type.");
        }

        $temp = &self::$__blockStorage;

        if( isset( $temp[$name] ) ){
            throw new RiverCompileHandleException("the block directive `$name` has been defined before!");
        }

        self::$__blockStorage[$name] = $callback;
    }

     /**
     * add block tag to stack 
     * @param string $name
     * @param callback $callback
     * @throws RiverCompileHandleException
     */
    public static function tagBlock($name,$callback){
        if( !is_string($name) || !is_callable($callback) ){
            throw new RiverCompileHandleException("arguments are not in correct type.");
        }

        $temp = &self::$__tagBlockStorage;

        if( isset( $temp[$name] ) ){
            throw new RiverCompileHandleException("the block tag `$name` has been defined before!");
        }

        self::$__tagBlockStorage[$name] = $callback;
    }

    /**
     * add inline tag to stack 
     * @param string $name
     * @param callback $callback
     * @throws RiverCompileHandleException
     */
    public static function tagInline($name,$callback){
        if( !is_string($name) || !is_callable($callback) ){
            throw new RiverCompileHandleException("arguments are not in correct type.");
        }

        $temp = &self::$__tagInlineStorage;

        if( isset( $temp[$name] ) ){
            throw new RiverCompileHandleException("the inline tag `$name` has been defined before!");
        }

        self::$__tagInlineStorage[$name] = $callback;
    }

    /**
     * add tag attribute to stack 
     * @param string $name
     * @param callback $callback
     * @throws RiverCompileHandleException
     */
    public static function attribute($name,$callback){
        if( !is_string($name) || !is_callable($callback) ){
            throw new RiverCompileHandleException("arguments are not in correct type.");
        }

        $temp = &self::$__attributeStorage;

        if( isset( $temp[$name] ) ){
            throw new RiverCompileHandleException("the attribute `$name` has been defined before!");
        }

        self::$__attributeStorage[$name] = $callback;
    }

    /**
     * compile file/string template
     * @param string $path
     * @param River $ref
     * @return string
     */
    public static function compile( $file , &$ref ){
        if( file_exists($file) ){
            $content = file_get_contents($file);
        }
        else{
            $content = $file;
        }

        $wl = new Walker($content);
        $wl->attachBlockDirective(self::getBlockStorage());
        $wl->attachInlineDirective(self::getInlineStorage());

        $wl->attachInlineTag(self::getTagInlineStorage());
        $wl->attachBlockTag(self::getTagBlockStorage());

        $wl->attachAttribute(self::getAttributeStorage());

        $tree = $wl->parse();


        $ev = new Evaluator($tree,$ref);
        return $ev->eval();
    }

}