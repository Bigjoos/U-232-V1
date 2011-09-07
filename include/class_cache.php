<?php
/**
 *   https://09source.kicks-ass.net:8443/svn/installer09/
 *   Licence Info: GPL
 *   Copyright (C) 2010 Installer09 v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn,kidvision.
 **/

if (!extension_loaded('memcache')) {
    die('Memcache Extension not loaded.');
}

class CACHE extends Memcache {
    public $CacheHits = array();
    public $MemcacheDBArray = array();
    public $MemcacheDBKey = '';
    protected $InTransaction = false;
    public $Time = 0;
    protected $Page = array();
    protected $Row = 1;
    protected $Part = 0;
    
    function __construct() {
        $this->connect('127.0.0.1', 11211);
    }
    //---------- Caching functions ----------//
    // Wrapper for Memcache::set, with the zlib option removed and default duration of 1 hour
    public function cache_value($Key, $Value, $Duration=2592000) {
        $StartTime=microtime(true);
        if (empty($Key)) {
            trigger_error("Cache insert failed for empty key");
        }
        if (!$this->set($Key, $Value, 0, $Duration)) {
            trigger_error("Cache insert failed for key $Key", E_USER_ERROR);
        }
        $this->Time+=(microtime(true)-$StartTime)*1000;
    }
    public function get_value($Key, $NoCache=false) {
        $StartTime=microtime(true);
        if (empty($Key)) {
            trigger_error("Cache retrieval failed for empty key");
        }
        $Return = $this->get($Key);
        $this->Time+=(microtime(true)-$StartTime)*1000;
        return $Return;
    }
    // Wrapper for Memcache::delete. For a reason, see above.
    public function delete_value($Key) {
        $StartTime=microtime(true);
        if (empty($Key)) {
            trigger_error("Cache retrieval failed for empty key");
        }
        if (!$this->delete($Key,0)) {
        }
        $this->Time+=(microtime(true)-$StartTime)*1000;
    }
}//end class
?>