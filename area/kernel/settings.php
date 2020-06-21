<?php
namespace Area;
/*
 * @version 2020-04-25 1.0 V9
 */
class Settings {
    static function readString( $key , $ifNotSet = false ){
        if ( $Q = Kernel::$DB->mkRow('z_settings',[ 'key' => $key ]) )
            return $Q['v_string']; else
            return $ifNotSet;
    }
    static function readInt( $key , $ifNotSet = 0 ){
        if ( $Q = Kernel::$DB->mkRow('z_settings',[ 'key' => $key ]) )
            return (int)$Q['v_int']; else
            return $ifNotSet;
    }
    static function readBoolean( $key , $ifNotSet = false ){
        if ( $Q = Kernel::$DB->mkRow('z_settings',[ 'key' => $key ]) )
            return (($Q['v_boolean'])?true:false); else
            return $ifNotSet;
    }
    static function readObject( $key , $ifNotSet = NULL ){
        if ( $Q = Kernel::$DB->mkRow('z_settings',[ 'key' => $key ]) )
            return (($Q['v_object'])?(unserialize($Q['v_object'])):($ifNotSet)); else
            return $ifNotSet;
    }
    static function writeString( $key , $value = false ){
        if ( $Q = Kernel::$DB->mkRow('z_settings',[ 'key' => $key ]) )
            return (Kernel::$DB->mkUpdate('z_settings',array('v_string'=>$value),'id',$Q['id'])); else
            return ((Kernel::$DB->mkInsert('z_settings',array('key'=>$key,'v_string'=>$value)))?true:false);
    }
    static function writeInt( $key , $value = 0 ){
        if ( $Q = Kernel::$DB->mkRow('z_settings',[ 'key' => $key ]) )
            return (Kernel::$DB->mkUpdate('z_settings',array('v_int'=>$value),'id',$Q['id'])); else
            return ((Kernel::$DB->mkInsert('z_settings',array('key'=>$key,'v_int'=>$value)))?true:false);
    }
    static function writeBoolean( $key , $value = false ){
        if ( $Q = Kernel::$DB->mkRow('z_settings',[ 'key' => $key ]) )
            return (Kernel::$DB->mkUpdate('z_settings',array('v_boolean'=>($value?1:0)),'id',$Q['id'])); else
            return ((Kernel::$DB->mkInsert('z_settings',array('key'=>$key,'v_boolean'=>($value?1:0))))?true:false);
    }
    static function writeObject( $key , $object = NULL ){
        if ( is_null($object) )
            $store = false; else
            $store = serialize($object);
        if ( $Q = Kernel::$DB->mkRow('z_settings',[ 'key' => $key ]) )
            return (Kernel::$DB->mkUpdate('z_settings',array('v_object'=>$store),'id',$Q['id'])); else
            return ((Kernel::$DB->mkInsert('z_settings',array('key'=>$key,'v_object'=>$store)))?true:false);
    }
    static function erase( $key ){
        Kernel::$DB->mkDelete('z_settings','key',$key);
    }
}
