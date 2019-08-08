<?php

namespace PoryaGrand\SilverRiver;

use System\Communicate\Debug\Console;

class AttributeWrapper{
    private $__name;
    private $__value;
    private $__raw;

    public function __construct($name,$val,$raw)
    {
        $this->__name = $name;
        $this->__value = $val;
        if( is_array($raw) ){
            $raw = implode("",$raw);
        }
        $this->__raw = $raw;
    }

    public function name(){
        return $this->__name;
    }

    public function val($set=null){
        if( $set !== null ){
            $this->__value = $set;
        }
        return $this->__value;
    }

    public function raw(){
        return $this->__raw;
    }
}

class Evaluator{
    protected $tree;
    protected $len;
    protected $ref;
    function __construct($tree,&$ref){
        $this->tree = $tree;
        $this->len = count($tree);
        $this->ref = &$ref;
    }

    public function eval($tree=null,$parent=""){
        $len = 0;
        if( !is_array($tree) ){
            $tree = $this->tree;
            $len = $this->len;
        }
        else{
            $len = count($tree);
        }

        $output = "";

        for($i=0;$i<$len;$i++){
            if( is_string($tree[$i]) ){
                $output .= $tree[$i];
            }
            else{
                switch($tree[$i]["type"]){
                    case "@":
                        $output .= $this->eval([$tree[$i]["value"]],"@");
                        break;
                    case "@!":
                        $output .= $this->eval([$tree[$i]["value"]],"@!");
                        break;
                    case "@@":
                        $output .= $this->eval([$tree[$i]["value"]],"@@");
                        break;
                    case "!":
                        $output .= $this->eval([$tree[$i]["value"]],"!");
                        break;
                    case "bracket":
                        $tempOut = $this->eval($tree[$i]["value"],"");
                        if( $parent == "@@" ){
                            $output .= "htmlentities({$tempOut})";
                        }
                        else if( $parent == "@!" ){
                            $output .= $tempOut;
                        }
                        else if( $parent == "@" ){
                            $output .= "<?php echo htmlentities({$tempOut}); ?>";
                        }
                        else if( $parent == "!" ){
                            $output .= "<?php {$tempOut}; ?>";
                        }
                        else{
                            $output .= "<?php echo {$tempOut}; ?>";
                        }
                        break;
                    case "inline":

                        $param = (($tree[$i]["param"] !== null)?$this->eval($tree[$i]["param"],""):"");
                        if( $parent == "@@" ){
                            $output .= call_user_func_array($tree[$i]["callback"],[&$this->ref,$param,1]);
                        }
                        else{
                            $output .= call_user_func_array($tree[$i]["callback"],[&$this->ref,$param,0]);
                        }
                        break;
                    case "block":
                        $param = (($tree[$i]["param"] !== null)?$this->eval($tree[$i]["param"],""):"");
                        if( $parent == "@@" ){
                            $output .= call_user_func_array($tree[$i]["callback"],[&$this->ref,$param,$this->eval($tree[$i]["content"],""),1]);
                        }
                        else{
                            $output .= call_user_func_array($tree[$i]["callback"],[&$this->ref,$param,$this->eval($tree[$i]["content"],""),0]);
                        }
                        break;
                    case "attribute":
                        $val = (($tree[$i]["value"] !== null)?$this->eval($tree[$i]["value"],""):"");
                        $temp = call_user_func_array($tree[$i]["callback"],[&$this->ref,$val]);
                        if($parent == "tag"){
                            if( is_string($output) ){
                                $output = [];
                            }
                            $output[$tree[$i]["name"]] = new AttributeWrapper($tree[$i]["name"],$temp,$tree[$i]["value"]);
                        }
                        else{
                            $output .= $tree[$i]["name"]."=\"".str_replace("\"","\\'",addslashes($temp))."\"";
                        }
                        break;
                    case "inlineTag":
                        $attributes = (($tree[$i]["attributes"] !== null)?$this->eval($tree[$i]["attributes"],"tag"):"");
                        $output .= call_user_func_array($tree[$i]["callback"],[&$this->ref,$attributes]);
                        break;
                    case "blockTag":
                        $attributes = (($tree[$i]["attributes"] !== null)?$this->eval($tree[$i]["attributes"],"tag"):"");
                        $content = (($tree[$i]["content"] !== null)?$this->eval($tree[$i]["content"],""):"");
                        $output .= call_user_func_array($tree[$i]["callback"],[&$this->ref,$attributes,$content]);
                        break;
                }
            }
        }
        return $output;
    }
}