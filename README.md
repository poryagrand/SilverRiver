<p align="center"><img src="./phpte.png"/></p>

## About SilverRiver

**silver river** is a simple **php template engine** with internal simple **caching system** that supports to defined **html** tags , elements , **attributes** and inline and block **directives**. it has no internal predefined directives and tags so it needs to be defined by the developer.

## First Step
to access library just include the ``loader.php`` and use functions.
```php
include_once("./SilverRiver/loader.php");

/**
  * @param string $extention (option => "river.php")
  */
$r = new River( "river.php" );
/**
  * @param string filename
  * @param array shareData (option => [])
  * @param bool $execOutPut (option => true)
  */
$res = $r->render("./SilverRiver/sample/index",[
  "myTest"=>123
],true);

echo $res;
```

## Syntax
the engine has 4 kind of syntax. directives , inline evaluators , tags and attributes

### Directives
directives can be separated into two parts.

- Inline Directives
- Block Directives

both of them has same general syntax but the **block directive** has an end part. 

general syntax: 
```php
@[DIRECTIVE NAME]( [ARGUMENTS] ) ... @end[DIRECTIVE NAME]
```
the above example is a block example. the inline one has no **end** part.

**note:** the __arguments__ is optional.

block example:
```php
<select>
@foreach( $obj as $key=>$val )
  <option value="{{ $val }}">{{ $key }}</option>
@endforeach
</select>
```

inline example:
```php
<p>user name is : @auth.name </p>
<p>now : @now.format("Y-M-D")</p>
```
there is a special note that is , the both directives have two phase of evaluation. one of them is **InText** evaluation and the other is **InPHP** .

both of phases can be handled by the directive callback handler.

example on inline:
```php
hello 
@if( @@auth.gender == "man" )
  Mr. @auth.name
@else
  Miss @auth.name 
@endif
```
in here the **@@auth.name** with double **@** is an **InPHP** evaluation. the logic of this is to return a php string with out  **\<?php ... ?>** to be parsable by php interpreter.

the directive name must be like this RegExp ``[A-Za-z_-][A-Za-z0-9_-.]*`` .

#### Extend Functions
**note**: the ``$ref`` is pointing to the current **River** instance . you can access current instance share data by calling ``$this->share(...)``

define a block directive:
```php
use PoryaGrand\SilverRiver\River;

River::directiveBlock("if",function($ref,$arg,$content,$exec){
    if( empty($arg) ){
        return new RiverHandleException("if directive needs arguments!");
    }

    return "<?php if ($arg) :  ?> $content <?php endif; ?>";
});

#--- in template.river.php
@if( true )
  it is true
@endif
```
define an inline directive:
```php
use PoryaGrand\SilverRiver\River;

River::directiveInline("basename",function($ref,$arg,$exec){
    if( $exec ){
        return " basename(__FILE__) ";
    }
    return "<?php echo basename(__FILE__); ?>";
});

#--- in template.river.php
current file name is @basename that @if( @@basename == "template.river.php" ) is @else is not @endif equal to template.river.php
```

### Inline Evaluators
this syntax is an internal syntax and is not extensible.

it has 5 kind of syntax.
- InPHP HtmlEntities Executer ( ``@@{{ ... }}`` ) equals ``htmlentities(...)`` in php
- InPHP Executer ( ``@!{{ ... }}`` ) equals ``...`` in php
- InText HtmlEntities Print ( `@{{ ... }}` ) equals ``<?php echo htmlentities(...); ?>`` in text
- InText Executer ( ``!{{ ... }}``  ) equals ``<?php ... ?>`` in text
- InText Print ( ``{{ ... }}`` ) equals ``<?php echo ... ; ?>`` in text


### HTML Tags
these tags are like html tags with difference in tag name. the tag name must be started with ``server:`` , like ``<server:input />`` or ``<server:dropdown src="..."> ... </server:dropdown>``

the block tags have content that can be nested template too like directives.

in server tags definition no directive is allowed.
example (it is wrong):
```php
<server:dropdown {{@auth.name}}="..."/>
```
the correct:
```php
<server:element an-attr="{{ @@auth.name }}">
  {{ @@auth.name }} = @auth.name
</server:element>
```

define an inline tag (html select element) :
```php
use PoryaGrand\SilverRiver\River;

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

#--- in template.river.php

<?php
    $src = [
        "num 1"=>64,
        "num 2"=>234,
        "num 3"=>566
    ];
?>
<server:dropdown src="$src" id="myDrop" name="myDrop"/>
```

define a block tag (php executer) :
```php
use PoryaGrand\SilverRiver\River;

River::blockTag("php",function($ref,$attrs,$content){
    return River::eval($content);
});

#--- in template.river.php
<server:php>
  echo "this will be printed statically";
</server:php>
```

### Attributes
like tags , this is like the html one too. just must be started with ``server:`` .
attributes can be used in server tags and html tags in same way.

**note:** an important note is that attributes formats can be written in all over the html code. the engine doesn't create tree from non server tags. just the server tag and its attributes will be read together by the engine.


define:
```php
use PoryaGrand\SilverRiver\River;

$counter = 0;
River::attribute("id",function($ref,$val) use(&$counter){
    $counter++;
    return $val."_".$counter."_".mt_rand(100,500);
});

#--- in template.river.php
<span server:id="ID"></span> {{-- <span id="ID_1_254"></span> --}}
<server:dropdown src="..." server:id="ID"/>
```

**note:** the ``{{-- ... --}}`` is the comment syntax

## Cache System
the caching system is so simple. it will cache files until any modification on main file with the same path.

```php
use PoryaGrand\SilverRiver\RiverCache;

RiverCahce::is( $path ) // dose the file is in cache?

RiverCache::save( $path , $content ) // save content in cache file for the $path

RiverCache::underDevelope( $is ) // tell to system to cache files and read from caches or ignore cache files for developing purposes

RiverCache::path( $path ) // returns the cached file path is exist

RiverCache::flush() // remove all cached files

RiverCache::isUnderDevelope() // is under developer? 
```

**updating on progress...**

## Licence
The SilverRiver is open-source software licensed under the MIT license.
