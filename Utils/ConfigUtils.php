<?php

class ConfigUtils
{
    protected static $loaded = false;
    protected static $configs = array();
    protected static function init()
    {
        self::$configs = parse_ini_file(WEB_ROOT.'config/application.ini');
        self::$loaded = true;
    }
    public static function get($key)
    {
        if(!self::$loaded){
            self::init();
        }
        if(isset(self::$configs[$key])) {
            return self::$configs[$key];
        }
        else {
            return null;
        }
    }
    public static function isPlaformCreateEnabled()
    {
        return intval(ConfigUtils::get('enable.platformcreation')) === 1;
    }
    public static function isIpBlockerEnabled()
    {
        return intval(ConfigUtils::get('enable.ipblocker')) === 1;
    }
}
