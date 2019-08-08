<?php

namespace PoryaGrand\SilverRiver;

use System\Communicate\Debug\Console;
use System\Security\Crypt;

class RiverCompilerMatcher{
    protected $refrence;

    function __construct(&$ref)
    {
        $this->refrence = &$ref;
    }

    /**
     * replace function callback
     * @param array $matches
     * @return string
     */
    public function phpRawReplace($matches){
        if( $matches[1] == "@@" ){
            return "htmlentities({$matches[2]})";
        }
        if( $matches[1] == "@!" ){
            return $matches[2];
        }
        else if( $matches[1] == "@" ){
            return "<?php echo htmlentities({$matches[2]}); ?>";
        }
        else if( $matches[1] == "!" ){
            return "<?php {$matches[2]}; ?>";
        }
        else{
            return "<?php echo {$matches[2]}; ?>";
        }
    }


    /**
     * replace function callback of inline executable directives
     * @param array $matches
     * @return string
     * @throws RiverCompileHandleException
     */
    public function inlineExecDirectivesExecuter($matches){
        $temp = &RiverCompiler::getInlineStorage();

        if( isset( $temp[$matches[1]] ) ){
            return call_user_func_array($temp[$matches[1]],[&$this->refrence,isset($matches[3])?substr($matches[3],1,-1):"",1]);
        }
        return "<|".(Crypt::base64Encode($matches[0]))."|>";
    }

    /**
     * replace function callback of inline directives
     * @param array $matches
     * @return string
     * @throws RiverCompileHandleException
     */
    public function inlineDirectivesExecuter($matches){
        $temp = &RiverCompiler::getInlineStorage();
        if( isset( $temp[$matches[1]] ) ){
            $val = call_user_func_array($temp[$matches[1]],[&$this->refrence,isset($matches[3])?substr($matches[3],1,-1):"",0]);
            return $val;
        }
        return "<|".(Crypt::base64Encode($matches[0]))."|>";
    }

    /**
     * replace function callback of block directives
     * @param array $matches
     * @return string
     * @throws RiverCompileHandleException
     */
    public function blockDirectivesExecuter($matches){
        $temp = &RiverCompiler::getBlockStorage();
        if( isset( $temp[$matches[1]] ) ){
            $mat = "";
            if( isset($matches[3]) ){
                $mat = substr($matches[3],1,-1);                
            }
            return call_user_func_array($temp[$matches[1]],[&$this->refrence,$mat,$matches[4]==("@end".$matches[1])?"":$matches[4]]);
        }
        return "<|".(Crypt::base64Encode($matches[0]))."|>";
    }
}