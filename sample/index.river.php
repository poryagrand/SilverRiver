<?php
    $src = [
        "num 1"=>64,
        "num 2"=>234,
        "num 3"=>566
    ];
?>

this is a shared data: @share("mytest")

<br/>

@if( @@basename == "Init.directives.php" )
    - it is init directive
@else 
    - it is not init directive. it is : @basename <br/>
    - the hash of address is {{ sha1(@@basename) }}
@endif

<br/>

@if (count( $src ) == 2)
    @foreach ($src as $key=>$val)
        {{$key}} : {{$val}}
    @endforeach
@elseif( count($src) < 2 )
    it is smaller than 2 
@else
    not acceptable
@endif

<br/>

<server:dropdown src="$src" id="myDrop" name="myDrop" server:data-hash="hello"/>

<br/>

<span id="myspan" server:data-hash="hello"></span>

<script>
console.log( document.getElementById("myDrop").value );
document.getElementById("myspan").innerHTML = document.getElementById("myspan").getAttribute("data-hash");
</script>

<server:php>
    var_dump([1,2,34]);
</server:php>