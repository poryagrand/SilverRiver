<?php

namespace PoryaGrand\SilverRiver;

class Walker{

    protected $__inlineStorage = array();
    protected $__blockStorage = array();

    protected $__tagBlockStorage = array();
    protected $__tagInlineStorage = array();

    protected $__attributesStorage = array();

    protected $textAsArray;
    protected $len;

    function __construct($content){
        $this->textAsArray = str_split($content);
        $this->len = count($this->textAsArray);
    }

    public function attachAttribute($attr){
        if( !is_array($attr) ){
            return;
        }
        $this->__attributesStorage = $attr;
    }


    public function attachInlineDirective($directives){
        if( !is_array($directives) ){
            return;
        }
        $this->__inlineStorage = $directives;
    }

    public function attachBlockDirective($directives){
        if( !is_array($directives) ){
            return;
        }
        $this->__blockStorage = $directives;
    }

    public function attachInlineTag($tags){
        if( !is_array($tags) ){
            return;
        }
        $this->__tagInlineStorage = $tags;
    }

    public function attachBlockTag($tags){
        if( !is_array($tags) ){
            return;
        }
        $this->__tagBlockStorage = $tags;
    }

    protected function isBlockTag( $name ){
        $block = &$this->__tagBlockStorage;
        return isset( $block[$name] );
    }

    protected function isInlineTag( $name ){
        $inline = &$this->__tagInlineStorage;
        return isset( $inline[$name] );
    }

    protected function isBlockDirective( $name ){
        $block = &$this->__blockStorage;
        return isset( $block[$name] );
    }

    protected function isInlineDirective( $name ){
        $inline = &$this->__inlineStorage;
        return isset( $inline[$name] );
    }

    protected function isAttribute($name){
        $inline = &$this->__attributesStorage;
        return isset( $inline[$name] );
    }

    protected function get($pos){
        if( $pos < $this->len ){
            return $this->textAsArray[$pos];
        }
        return null;
    }

    protected function getRange($pos,$len){
        if( $pos+$len <= $this->len ){
            $sliced = array_slice($this->textAsArray,$pos,$len);
            return implode("",$sliced);
        }
        return null;
    }

    public function parse(){
        $text = "";
        $output = [];

        for($i=0;$i<$this->len;$i++){
            switch( $this->get($i) ){
                case "s":
                    if( $this->getRange($i,7) == "server:" && $this->get($i-1) != "\\" ){
                        if(!empty($text)){
                            if( isset($output[count($output)-1]) && is_string($output[count($output)-1]) ){
                                $output[count($output)-1] = $output[count($output)-1] . $text;
                            }
                            else{
                                array_push($output,$text);
                            }
                            $text = "";
                        }
                        $temp = call_user_func_array(array($this,"tagAttributesParser"),array(&$i));

                        if( is_array($temp) ){
                            if(count($temp)>0){
                                $temp = $temp[0];
                                array_push($output,$temp);
                            }
                            $i--;
                        }
                        else if( is_string($temp) ){
                            $text .= $this->get($i) . $temp;
                        }
                        else{
                            $text.=$this->get($i);
                        }
                    }
                    else{
                        $text.=$this->get($i);
                    }
                    break;
                case "<":
                    if( $this->getRange($i,8) == "<server:" ){
                        if(!empty($text)){
                            if( isset($output[count($output)-1]) && is_string($output[count($output)-1]) ){
                                $output[count($output)-1] = $output[count($output)-1] . $text;
                            }
                            else{
                                array_push($output,$text);
                            }
                            $text = "";
                        }
                        $temp = call_user_func_array(array($this,"tagParser"),array(&$i));

                        if( is_array($temp) ){
                            array_push($output,$temp);
                        }
                        else if( is_string($temp) ){
                            $text .= $this->get($i) . $temp;
                        }
                        else{
                            $text.=$this->get($i);
                        }
                    }
                    else{
                        $text.=$this->get($i);
                    }
                    break;
                case "@":
                    if(!empty($text)){
                        if( isset($output[count($output)-1]) && is_string($output[count($output)-1]) ){
                            $output[count($output)-1] = $output[count($output)-1] . $text;
                        }
                        else{
                            array_push($output,$text);
                        }
                        $text = "";
                    }
                    $temp = call_user_func_array(array($this,"atSignParser"),array(&$i));

                    if( is_array($temp) ){
                        array_push($output,$temp);
                    }
                    else if( is_string($temp) ){
                        $text .= $this->get($i) . $temp;
                    }
                    else{
                        $text.=$this->get($i);
                    }
                    break;
                case "!":
                    if(!empty($text)){
                        if( isset($output[count($output)-1]) && is_string($output[count($output)-1]) ){
                            $output[count($output)-1] = $output[count($output)-1] . $text;
                        }
                        else{
                            array_push($output,$text);
                        }
                        $text = "";
                    }
                    $temp = call_user_func_array(array($this,"exMarkParser"),array(&$i));
                    if( is_array($temp) ){
                        array_push($output,$temp);
                    }
                    else if( is_string($temp) ){
                        $text .= $this->get($i) . $temp;
                    }
                    else{
                        $text.=$this->get($i);
                    }
                    break;
                case "{":
                    if(!empty($text)){
                        if( isset($output[count($output)-1]) && is_string($output[count($output)-1]) ){
                            $output[count($output)-1] = $output[count($output)-1] . $text;
                        }
                        else{
                            array_push($output,$text);
                        }
                        $text = "";
                    }
                    $temp = call_user_func_array(array($this,"bracketParser"),array(&$i));
                    if( is_array($temp) ){
                        array_push($output,$temp);
                    }
                    else if( is_string($temp) ){
                        $text .= $this->get($i) . $temp;
                    }
                    else{
                        $text.=$this->get($i);
                    }
                    break;
                default:  $text.=$this->get($i);
            }
        }

        if(!empty($text)){
            if( isset($output[count($output)-1]) && is_string($output[count($output)-1]) ){
                $output[count($output)-1] = $output[count($output)-1] . $text;
            }
            else{
                array_push($output,$text);
            }
            $text = "";
        }
        return $output;
    }

    protected function tagParser( &$pos ){
        $pos+=8;
        $output = null;
        $tagName = "";
        for($i=$pos;$i<$this->len;$i++){
            $ord = ord( $this->get($i) );
            if( 
                ( $ord >= ord('a') && $ord <= ord('z') ) ||
                ( $ord >= ord('A') && $ord <= ord('Z') ) || 
                ( $ord >= ord('0') && $ord <= ord('9') ) || 
                ( $ord == ord(':') )                     ||
                ( $ord == ord('_') )
             ){
                 $tagName .= $this->get($i);
            }
            else{
                break;
            }
        }

        $pos=$i;
        if( $this->isInlineTag($tagName) ){
            $output = call_user_func_array(array($this,"inlineTagParser"),array(&$pos,$tagName));
            if($output !== null){
                return $output;
            }
        }
        else if( $this->isBlockTag($tagName) ){
            $output = call_user_func_array(array($this,"blockTagParser"),array(&$pos,$tagName));
            if($output !== null){
                return $output;
            }
        }
        $pos-=$i;
        $pos-=8;
        return null;
    }

    protected function tagAttributesParser(&$pos){
        $open = null;
        $i = $pos;

        $attrs = [];

        for($i;$i<$this->len;$i++){
            while( $this->get($i)!==null && trim($this->get($i)) == "" ){
                $i++;
            }

            $ord = ord( $this->get($i) );

            if( 
                !(
                    ( $ord >= ord('a') && $ord <= ord('z') ) ||
                    ( $ord >= ord('A') && $ord <= ord('Z') ) || 
                    ( $ord >= ord('0') && $ord <= ord('9') ) || 
                    ( $ord == ord(':') )                     ||
                    ( $ord == ord('-') )                     ||
                    ( $ord == ord('_') )
                )
             ){
                 break;
            }

            $attrname = "";
            $attrValue = "";
            for($i;$i<$this->len;$i++){
                $ord = ord( $this->get($i) );
                if( 
                    ( $ord >= ord('a') && $ord <= ord('z') ) ||
                    ( $ord >= ord('A') && $ord <= ord('Z') ) || 
                    ( $ord >= ord('0') && $ord <= ord('9') ) || 
                    ( $ord == ord(':') )                     ||
                    ( $ord == ord('-') )                     ||
                    ( $ord == ord('_') )
                 ){
                     $attrname .= $this->get($i);
                }
                else{
                    break;
                }
            }

            while( $this->get($i)!==null && trim($this->get($i)) == "" ){
                $i++;
            }

            if( $this->get($i) == "=" ){
                $q = $i+1;
                while( $this->get($q)!==null && trim($this->get($q)) == "" ){
                    $q++;
                }

                if( $this->get($q) == "'" || $this->get($q) == "\"" ){
                    $i = $q;
                    $open = $this->get($i);
                    $i++;

                    for($i;$i<$this->len;$i++){
                        if( $this->get($i-1) != "\\" && $this->get($i) == $open ){
                            break;
                        }
                        $attrValue .= $this->get($i);
                    }

                    if( !empty($attrValue) ){
                        $wl = new Walker($attrValue);
                        $wl->attachBlockDirective($this->__blockStorage);
                        $wl->attachInlineDirective($this->__inlineStorage);
                
                        $wl->attachInlineTag($this->__tagInlineStorage);
                        $wl->attachBlockTag($this->__tagBlockStorage);

                        $wl->attachAttribute($this->__attributesStorage);
                        $attrValue = $wl->parse();
                    }
                    else{
                        $attrValue = null;
                    }
                }
                else{
                    return null;
                }
            }
            else{
                $attrValue = null;
                $i--;
            }

            $call = function($ref,$val){return $val;};
            if( strpos($attrname,"server:") >= 0 ){
                $newAttr = str_replace("server:","",$attrname);
                if( $this->isAttribute($newAttr) ){
                    $call = $this->__attributesStorage[$newAttr];
                    $attrname = $newAttr;
                }
            }

            $attrs[] = [
                "type"=>"attribute",
                "name"=>$attrname,
                "value"=>$attrValue,
                "callback"=>$call
            ];
            
            $open = null;
        }

        $pos = $i;
        return $attrs;
    }

    protected function blockContentParser(&$pos,$tag){
        $content = "";
        for($i=$pos;$i<$this->len;$i++){
            if( $this->getRange($i,strlen($tag)+10) == ("</server:" . $tag . ">") ){
                $i+=strlen($tag)+10;
                break;
            }
            else{
                $content .= $this->get($i);
            }
        }

        if( !empty($content) ){
            $wl = new Walker($content);
            $wl->attachBlockDirective($this->__blockStorage);
            $wl->attachInlineDirective($this->__inlineStorage);
    
            $wl->attachInlineTag($this->__tagInlineStorage);
            $wl->attachBlockTag($this->__tagBlockStorage);
            $wl->attachAttribute($this->__attributesStorage);
            $content = $wl->parse();
        }
        else{
            $content = null;
        }
        $pos = $i;

        return $content;
    }

    protected function blockTagParser(&$pos,$tag){
        $attrs = call_user_func_array(array($this,"tagAttributesParser"),array(&$pos));
        if( $attrs === null ){
            return null;
        }

        if( $this->get($pos) == ">" ){
            $pos++;

            $content = call_user_func_array(array($this,"blockContentParser"),array(&$pos,$tag));
            return [
                "type"=>"blockTag",
                "attributes"=>$attrs,
                "name"=>$tag,
                "content"=>$content,
                "callback"=>$this->__tagBlockStorage[$tag]
            ];
        }
        return null;
    }

    protected function inlineTagParser(&$pos,$tag){
        $attrs = call_user_func_array(array($this,"tagAttributesParser"),array(&$pos));
        if( $attrs === null ){
            return null;
        }

        if( $this->get($pos) == "/" && $this->get($pos+1) == ">" ){
            $pos+=2;
            return [
                "type"=>"inlineTag",
                "attributes"=>$attrs,
                "name"=>$tag,
                "callback"=>$this->__tagInlineStorage[$tag]
            ];
        }
        else if( $this->get($pos) == ">" ){
            $pos++;
            return [
                "type"=>"inlineTag",
                "attributes"=>$attrs,
                "name"=>$tag,
                "callback"=>$this->__tagInlineStorage[$tag]
            ];
        }
        return null;
    }

    protected function atSignParser( &$pos ){
        $pos++;
        $output = null;
        
        switch($this->get($pos)){
            case "!":
                $output = call_user_func_array(array($this,"exMarkParser"),array(&$pos));
                if($output !== null){
                    $output = [
                        "type"=>"@!",
                        "value"=>$output["value"]
                    ];
                }
                break;
            case "@":
                $pos++;
                
                if( $this->get($pos) == "{" ){
                    $output = call_user_func_array(array($this,"bracketParser"),array(&$pos));
                }
                else{
                    $output = call_user_func_array(array($this,"atSignIdentifierParser"),array(&$pos));
                }
                if( $output === null ){
                    $pos--;
                }
                else{
                    $output = [
                        "type"=>"@@",
                        "value"=>$output
                    ];
                }
                break;
            default:
                if( $this->get($pos) == "{" ){
                    $output = call_user_func_array(array($this,"bracketParser"),array(&$pos));
                    if($output !== null){
                        $output = [
                            "type"=>"@",
                            "value"=>$output
                        ];
                    }
                }
                else{
                    $output = call_user_func_array(array($this,"atSignIdentifierParser"),array(&$pos));
                }
                break;
        }
        if( $output === null ){
            $pos--;
            return null;
        }
        if( $output == "" ){
            return "";
        }

        return $output;
    }

    protected function atSignIdentifierParser( &$pos ){
        $tempPos = $pos;
        $name = "";
        $output = null;
        
        for($pos;$pos<$this->len;$pos++){
            $ord = ord($this->get($pos));

            if( 
                $pos == $tempPos &&  
                (
                    ( $ord >= ord('a') && $ord <= ord('z') ) ||
                    ( $ord >= ord('A') && $ord <= ord('Z') ) || 
                    ( $ord == ord('-') )                     ||
                    ( $ord == ord('_') )
                )
            ){
                $name .= $this->get($pos);
            }
            else if( 
                ( $ord >= ord('a') && $ord <= ord('z') ) ||
                ( $ord >= ord('A') && $ord <= ord('Z') ) || 
                ( $ord >= ord('0') && $ord <= ord('9') ) || 
                ( $ord == ord('-') )                     ||
                ( $ord == ord('.') )                     ||
                ( $ord == ord('_') )
            ){
                $name .= $this->get($pos);
            }
            else{
                break;
            }
        }

        if( 
            $this->isBlockDirective($name) 
        ){
            $output = call_user_func_array(array($this,"blockIdentifier"),array($name,&$pos));
        }
        else if( 
            $this->isInlineDirective($name) 
        ){
            $output = call_user_func_array(array($this,"inlineIdentifier"),array($name,&$pos));
            $pos--;
        }
        else{
            $output = null;
            $pos = $tempPos;
        }
        return $output;
    }


    protected function inlineIdentifier( $name , &$pos ){
        $paran = call_user_func_array(array($this,"parantesStatement"),array(&$pos));

        return [
            "type"=>"inline",
            "param"=>$paran,
            "name"=>$name,
            "callback"=>$this->__inlineStorage[$name]
        ];
    }

    protected function blockIdentifier( $name , &$pos ){
        $paran = call_user_func_array(array($this,"parantesStatement"),array(&$pos));
        $i=$pos;
        $isEnd = false;
        $content = "";

        $sames = 0;
        $sameMatcher = false;
        $sameMatchName= "";

        for( $i=$pos ; $i < $this->len ; $i++ ){
            $count = (4+strlen($name));
            if( ($this->len - $i) >= $count ){
                $end = $this->getRange($i,$count);
                $after = ord($this->get($i+$count));
                if( 
                    $end == ("@end".$name) && 
                    !(
                        ( $after >= ord('a') && $after <= ord('z') ) ||
                        ( $after >= ord('A') && $after <= ord('Z') ) || 
                        ( $after >= ord('0') && $after <= ord('9') ) || 
                        ( $after == ord('-') )                     ||
                        ( $after == ord('.') )                     ||
                        ( $after == ord('_') )
                    )
                ){
                    if( $sames == 0 ){
                        $isEnd = true;
                        $i+=$count;
                        break;
                    }
                    else{
                        $sames--;
                        $content .= "@";
                    }
                }
                else{
                    if( $this->get($i) == "@" ){
                        $sameMatcher = true;
                    }
                    else if($sameMatcher){
                        $ord = ord($this->get($i));
                        if( 
                            empty($sameMatchName) &&  
                            (
                                ( $ord >= ord('a') && $ord <= ord('z') ) ||
                                ( $ord >= ord('A') && $ord <= ord('Z') ) || 
                                ( $ord == ord('-') )                     ||
                                ( $ord == ord('_') )
                            )
                        ){
                            $sameMatchName .= $this->get($i);
                        }
                        else if( 
                            ( $ord >= ord('a') && $ord <= ord('z') ) ||
                            ( $ord >= ord('A') && $ord <= ord('Z') ) || 
                            ( $ord >= ord('0') && $ord <= ord('9') ) || 
                            ( $ord == ord('-') )                     ||
                            ( $ord == ord('.') )                     ||
                            ( $ord == ord('_') )
                        ){
                            $sameMatchName .= $this->get($i);
                        }
                        else{
                            if( $sameMatchName == $name ){
                                $sames++;
                            }
                            $sameMatchName = "";
                            $sameMatcher = false;
                        }
                        
                    }
                    $content .= $this->get($i);
                }
            }
            else{
                return null;
            }
        }

        if( !$isEnd ){
            return null;
        }

        $pos = $i-1;

        $wl = new Walker($content);
        $wl->attachBlockDirective($this->__blockStorage);
        $wl->attachInlineDirective($this->__inlineStorage);

        $wl->attachInlineTag($this->__tagInlineStorage);
        $wl->attachBlockTag($this->__tagBlockStorage);
        $wl->attachAttribute($this->__attributesStorage);

        return [
            "type"=>"block",
            "param"=>$paran,
            "name"=>$name,
            "content"=>$wl->parse(),
            "callback"=>$this->__blockStorage[$name]
        ];
    }

    protected function parantesStatement(&$pos){
        $open = 0;
        $i = $pos;

        while( $this->get($i) !== null && trim($this->get($i)) == "" ){
            $i++;
        }

        if( $this->get($i) != "(" ){
            return null;
        }

        $i++;
        $open++;
        $inner = "";

        for( $i;$i<$this->len && $open>0;$i++ ){
            if( $this->get($i) == ")" ){
                $open--;
                if( $open == 0 ){
                    break;
                }
            }
            else if( $this->get($i) == "(" ){
                $open++;
            }

            $inner .= $this->get($i);
        }

        if( $open != 0 ){
            return null;
        }

        $pos = $i+1;

        $wl = new Walker($inner);
        $wl->attachBlockDirective($this->__blockStorage);
        $wl->attachInlineDirective($this->__inlineStorage);
        $wl->attachInlineTag($this->__tagInlineStorage);
        $wl->attachBlockTag($this->__tagBlockStorage);
        $wl->attachAttribute($this->__attributesStorage);

        return $wl->parse();
    }

    protected function exMarkParser( &$pos ){
        $pos++;
        $output = null;
        if( $this->get($pos) == "{" ){
            $output = call_user_func_array(array($this,"bracketParser"),array(&$pos));
        }

        if( $output === null ){
            $pos--;
            return null;
        }

        if( $output == "" ){
            return "";
        }
        return [
            "type"=>"!",
            "value"=>$output
        ];
    }

    protected function bracketParser( &$pos ){
        $pos++;
        $output = null;
        $isComment = false;
        if( $this->get($pos) == "{" ){
            $pos++;
            $text = "";
            $isEnd = false;
            $i=$pos;

            if( $this->get($pos) == "-" && $this->get($pos+1) == "-" ){
                $isComment = true;
                $pos+=2;
            }

            for($i=$pos;$i<$this->len;$i++){
                if( $isComment && $this->get($i) == "-" && $this->get($i+1) == "-" && $this->get($i+2) == "}" && $this->get($i+3) == "}" ){
                    $isEnd = true;
                    $i+=4;
                    break;
                }
                else if( !$isComment && $this->get($i) == "}" && $this->get($i+1) == "}"){
                    $i+=1;
                    $isEnd = true;
                    break;
                }
                $text.=$this->get($i);
            }

            if( !$isEnd ){
                $pos--;
                $output = null;
            }
            else{
                $output = $text;
                $pos = $i;
            }
        }

        if( $output === null ){
            $pos--;
            return null;
        }

        if( $isComment ){
            return "";
        }

        $wl = new Walker($output);
        $wl->attachBlockDirective($this->__blockStorage);
        $wl->attachInlineDirective($this->__inlineStorage);
        $wl->attachInlineTag($this->__tagInlineStorage);
        $wl->attachBlockTag($this->__tagBlockStorage);
        $wl->attachAttribute($this->__attributesStorage);

        return [
            "type"=>"bracket",
            "value"=>$wl->parse()
        ];
    }
}