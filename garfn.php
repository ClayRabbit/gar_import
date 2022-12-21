<?php
class GARFn {
    const MB = 1048576;
    const ReadSize = 262144;
    const bugFile = 'gar.bug';
    private const lockFile = 'gar.lock';
    static function ZIPFile(){
        if (GAR::isRemoteZip()) return GAR::ScanDir;
        if ( !is_dir(GAR::ScanDir) )
            return false;
        $l = [];
        foreach ( scandir(GAR::ScanDir) as $e )
            if ( preg_match('#\.zip$#i',$e) )
                $l[] = $e;
        if ( !count($l) )
            return false;
        rsort($l);
        return GAR::ScanDir.reset($l);
    }
    static function lockWrite( $worker , $ln = 0 , $error = false ){
        file_put_contents(self::lockFile,serialize([
            'ts' => \fw::clockSQL(),
            'worker' => intval($worker),
            'ln' => $ln ,
            'error' => $error
        ]));
        if ( $error )
            echo "\n\n!!!\nWorker = $worker\n$ln: $error\n!!!\n\n";
    }
    static function lockError(){
        if ( !file_exists(self::lockFile) )
            return 'Lock file required';
        $R = @unserialize(file_get_contents(self::lockFile));
        if ( !is_array($R) )
            return 'Wrong file format #/1';
        return $R['error'] ?? 'Wrong file format #/2';
    }
    static function lockClear(){
        if ( file_exists(self::lockFile) )
            @unlink(self::lockFile);
    }
    static function bug( $processing , $descr ){
        file_put_contents(self::bugFile,$processing."\n".$descr."\n\n",FILE_APPEND);
    }
    static function rawXML_extract( $raw , $k ){
        return ( preg_match('# '.$k.'="(?<v>[^<>"]*)"#',$raw,$M) ) ? $M['v'] : false;
    }
}
