<?php
use PoryaGrand\SilverRiver\River;
use PoryaGrand\SilverRiver\RiverHandleException;

River::directiveBlock("if",function($ref,$arg,$content){
    if( empty($arg) ){
        return new RiverHandleException("if directive needs arguments!");
    }

    return "<?php if ($arg) :  ?> $content <?php endif; ?>";
});

River::directiveInline("elseif",function($ref,$arg){
    if( empty($arg) ){
        return new RiverHandleException("else if directive needs arguments!");
    }

    return "<?php elseif ($arg) : ?>";
});

River::directiveInline("else",function($ref,$arg){
    return "<?php else : ?>";
});


River::directiveInline("share",function($ref,$arg,$exec){
    if( empty($arg) ){
        return Console::halt("share directive needs arguments!");
    }
    if(!$exec){
        
        return "<?php echo \$this->share($arg); ?>";
    }
    return "\$this->share($arg)";
});

River::directiveBlock("while",function($ref,$arg,$content){
    if( empty($arg) ){
        return new RiverHandleException("while directive needs arguments!");
    }

    return "<?php while ($arg) {  ?> $content <?php } ?>";
});

River::directiveBlock("for",function($ref,$arg,$content){
    if( empty($arg) ){
        return new RiverHandleException("for directive needs arguments!");
    }

    return "<?php for ($arg) {  ?> $content <?php } ?>";
});

River::directiveBlock("foreach",function($ref,$arg,$content){
    if( empty($arg) ){
        return new RiverHandleException("foreach directive needs arguments!");
    }

    return "<?php foreach ($arg) {  ?> $content <?php } ?>";
});


River::directiveInline("break",function($ref,$arg){
    if( !empty($arg) ){
        return "<?php if( $arg ){break;} ?>";
    }
    return "<?php break; ?>";
});

River::directiveInline("continue",function($ref,$arg){
    if( !empty($arg) ){
        return "<?php if( $arg ){continue;} ?>";
    }
    return "<?php continue; ?>";
});

River::directiveInline("require",function($ref,$arg){
    if( empty($arg) ){
        return new RiverHandleException("require directive needs arguments!");
    }

    $args = eval("return [".$arg."];");

    $clone = $ref->share();
    if( count($args) > 1 && is_array($arg[1]) ){
        foreach($args[1] as $key=>$val){
            $clone[$key] = $val;
        }
    }

    $riv = new River();
    $path = realpath($args[0]);
    $riv->render($path,$clone);
    return "<?php require_once ('".RiverCache::path($path)."'); ?>";
});

River::directiveInline("include",function($ref,$arg){
    if( empty($arg) ){
        return new RiverHandleException("include directive needs arguments!");
    }

    if( !is_array($arg) ){
        $arg = eval("return [".$arg."];");
    }

    $clone = $ref->share();
    if( count($arg) > 1 && is_array($arg[1]) ){
        foreach($arg[1] as $key=>$val){
            $clone[$key] = $val;
        }
    }

    $riv = new River();
    $path = realpath($arg[0]);
    return $riv->render($path,$clone,false);
});

River::directiveInline("basename",function($ref,$arg,$exec){
    if( $exec ){
        return " basename('".__FILE__."') ";
    }
    return "<?php echo basename('".__FILE__."'); ?>";
});


River::attribute("data-hash",function($ref,$val){
    return sha1($val);
});

River::inlineTag("dropdown",function($ref,$attrs){
    if( !isset($attrs["src"]) ){
        throw new RiverHandleException("dropdown element must have 'src' atrribute");
    }
    $src = $attrs["src"]->raw();

    $attrsStr = [];
    foreach( $attrs as $attr ){
        if( $attr->name() !== "src" ){
            $attrsStr[] = $attr->name()."=\"".str_replace("\"","\\'",addslashes($attr->val()))."\"";
        }
    }
    $attrsStr = implode(" ",$attrsStr);

    $content = "<option value='((VALUE))'>((TEXT))</option>";

    $temp = <<<PHP
    <select $attrsStr>
    <?php if( isset( {$src} ) ){
        \$src_content = "$content";
        foreach({$src} as \$src_key=>\$src_value){
            echo str_replace(
                "((VALUE))",\$src_value,
                str_replace(
                    "((TEXT))",\$src_key,
                    \$src_content
                )
            );
        }
    } ?>
    </select>
PHP;

    return $temp;
    
});


River::blockTag("php",function($ref,$attrs,$content){
    return River::eval($content);
});