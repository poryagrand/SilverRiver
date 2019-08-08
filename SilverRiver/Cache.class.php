<?php

namespace PoryaGrand\SilverRiver;

class RiverCacheHandleException extends \Exception{}

/**
 * cach files to access fast to data
 */
class RiverCache{
    const CACHE_FOLDER = __DIR__ . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR;
    const CACHE_EXT = ".cached";

    private static $_underDevelope = false;

    /**
     * check if cached file exist and is new . if not it will remove cached file if existed
     * @param string $file
     * @return bool|null
     * @throws RiverCacheHandleException
     */
    public static function is($file){

        if( self::$_underDevelope ){
            return null;
        }

        if( !is_string($file) ){
            throw new RiverCacheHandleException("arguments are not in correct format!");
        }

        $cachedFile = self::CACHE_FOLDER . sha1($file) . "_" . basename($file) . self::CACHE_EXT;
        if( is_file($file) &&  is_file($cachedFile) ){
            if( filemtime($cachedFile) >= filemtime($file) ){
                return true;
            }
            unlink($cachedFile);
        }
        return false;
    }

    /**
     * set state of caching to reload cache data on every request for develope purpose
     * @param bool $is
     */
    public static function underDevelope($is){
        self::$_underDevelope = !(!$is);
    }

    /**
     * check cash is on or not
     * @return bool
     */
    public static function isUnderDevelope(){
        return self::$_underDevelope;
    }

    /**
     * return the path to cached file if is cahched
     * @param string $file
     * @return string|null
     */
    public static function path($file){
        $is = self::is($file);
        if( $is || ($is === null && self::$_underDevelope ) ){
            return self::CACHE_FOLDER . sha1($file) . "_" . basename($file) . self::CACHE_EXT;
        }
        return null;
    }

    /**
     * cache content in file
     * @param string $file
     * @param string $content
     * @throws RiverCacheHandleException
     */
    public static function save($file,$content){
        if( !is_string($file) || !is_string($content) ){
            throw new RiverCacheHandleException("arguments are not in correct format!");
        }

        $cachedFile = self::CACHE_FOLDER . sha1($file) . "_" . basename($file) . self::CACHE_EXT;

        if( !is_dir(self::CACHE_FOLDER) ){
            mkdir(self::CACHE_FOLDER);
        }

        try{
            $file = fopen($cachedFile,"w");
            fwrite($file,$content);
            fclose($file);
        }
        catch(RiverCacheHandleException $e){
            throw $e;
        }
    }

    /**
     * delete all cached files from hard
     * @return void
     */
    public static function flush(){
        $files = glob(self::CACHE_FOLDER . "*");
        foreach( $files as $path ){
            if( is_file($path) ){
                unlink($path);
            }
        }
    }
}