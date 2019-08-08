<?php
use PoryaGrand\SilverRiver\River;
use PoryaGrand\SilverRiver\RiverCache;

include_once "./SilverRiver/loader.php";

RiverCache::underDevelope(true);


$r = new River();
$res = $r->render("./sample/index.river.php",[
    "mytest" => 123
],true);

echo $res;