<?php
/*
 * @version 2021-09-03 10.8 Console
 */
class fw {
    static function sid(){ return 0; }
    static function param( $arg , $key , $ifKeyUndefined = false ){
        if ( isset($arg[$key]) )
            return $arg[$key];
        return $ifKeyUndefined;
    }
    static function value( ...$values ){ foreach ( $values as $v ) if ( $v ) return $v; return false; }
    static function isEmpty( $var ){ return '' === trim($var); }
    static function notEmpty( $var ){ return '' !== trim($var); }
    static function clockSQL(){ return date('Y-m-d H:i:s'); }
    static function i2( $n ){ $n = (int)$n; return ($n>9)?((string)$n):('0'.$n); }
    static function i3( $n ){ $n = (int)$n; return ($n<10)?('00'.$n):( ($n<100)?('0'.$n):((string)$n) ); }
    static function i4( $n ){ $n = (int)$n; return ($n<10)?('000'.$n):( ($n<100)?('00'.$n):( ($n<1000)?('0'.$n):((string)$n) ) ); }
    static function ik( $i , $k = 5 ){ $i = (int)$i; return trim(str_pad("$i ",(int)$k+1,'0',STR_PAD_LEFT)); }
    static function f1( $f ){ return sprintf('%2.1f', $f); }
    static function f2( $f ){ return sprintf('%2.2f', $f); }
    static function f3( $f ){ return sprintf('%2.3f', $f); }
    static function f4( $f ){ return sprintf('%2.4f', $f); }
    static function fk( $f , $k = 2 ){ return sprintf('%2.'.$k.'f', $f); }
	static function spacesL( $s , $divisor ){
        $re = '';
        while(true){
            $take = is_array($divisor) ? intval(current($divisor)) : intval($divisor);
            if ( is_array($divisor) )
                next($divisor);
            if ( !$take )
                return trim("$re $s");
            if ( mb_strlen($s,'utf-8') >= $take ) {
                $re .= ' '.mb_substr($s,0,$take,'utf-8');
                $s = mb_substr($s,$take,null,'utf-8');
            } else
                return trim("$re $s");
        }
    }
	static function spacesR( $s , $divisor ){
        $re = '';
        while(true){
            $take = is_array($divisor) ? intval(current($divisor)) : intval($divisor);
            if ( is_array($divisor) )
                next($divisor);
            if ( !$take )
                return trim("$s $re");
            if ( mb_strlen($s,'utf-8') >= $take ) {
                $re = ' '.mb_substr($s,mb_strlen($s,'utf-8')-$take,null,'utf-8').' '.$re;
                $s = mb_substr($s,0,mb_strlen($s,'utf-8')-$take,'utf-8');
            } else
                return trim("$s $re");
        }
    }
    static function nPages( $nRecords , $perPage = 10 ){
        $nPages = (int)(($nRecords-1)/$perPage+1) ;
        return (($nPages<1)?(1):($nPages));
    }
    static function cPage( $currentValue , $nPages ){
        return (($currentValue<1)?(1):( (($currentValue>$nPages)?($nPages):($currentValue)) ));
    }
    static function pagesCompat( $cPage , $nPages ,
                                 $iterCorner = 1 , $iterCenter = 2 ,
                                 $zeroAsSeparator = true , $addZeroCenters = false ){
        $a1 = 1 ;      // ++
        $a2 = $nPages; // --
        $a3 = $cPage;  // --
        $a4 = $cPage;  // ++
        $A = array ( $a1 , $a2 , $a3 , $a4 );
        for ( $i = 0 ; $i < $iterCorner ; $i++ ) {
            $a1++; if ( $a1 < $nPages ) $A[] = $a1;
            $a2--; if ( $a2 > 0 ) $A[] = $a2;
        }
        for ( $i = 0 ; $i < $iterCenter ; $i++ ) {
            $a3--; if ( $a3 > 0 ) $A[] = $a3;
            $a4++; if ( $a4 < $nPages ) $A[] = $a4;
        }
        $A = array_unique($A);
        sort($A);
        if ( count($A) == 0 )
            return $A;
        if ( $zeroAsSeparator ) {
            $B = array();
            $b = current($A) - 1 ;
            foreach ( $A as $a ) {
                if ( $a != $b + 1 )
                    $B[] = 0;
                $b = $B[] = $a;
            }
            if ( $addZeroCenters ) {
                $A = array();
                for ( $i = 0 ; $i < count($B) ; $i++ ) {
                    $A[] = $B[$i];
                    if ( ( $B[$i] == 0 ) && ( $B[$i+1]-$B[$i-1]>6 ) ) {
                        $A[] = (int)( ($B[$i-1]+$B[$i+1])/2 );
                        $A[] = 0;
                    }
                }
                return $A;
            }
            return $B;
        }
        return $A;
    }
    static function pages( $cPage , $nPages , $maxLength = 7 ){ // min length is 7 // recommended length 11 , 15 , ... //
        $RR = array_unique([ 1 , ( $cPage > 2 ) ? ( $cPage - 1 ) : 1 , $cPage , ( $cPage + 1 < $nPages ) ? ( $cPage + 1 ) : $cPage , $nPages ]);
        sort($RR);
        $addHighest = true;
        while ( true ) {
            sort($RR);
            $addRR = [];
            for ( $i = 0 ; $i < count($RR)-1 ; $i++ ) {
                if ( $RR[$i] + 1 != $RR[$i+1] ) {
                    $addRR[] = $addHighest ? ( $RR[$i+1] - 1 ) : ( $RR[$i] + 1 );
                    $addHighest = !$addHighest;
                }
            }
            if ( !count($addRR) )
                break;
            $done = false;
            foreach ( $addRR as $addV ) {
                $nSpaces = 0;
                for ( $i = 0 ; $i < count($RR)-1 ; $i++ )
                    if ( $RR[$i] + 1 != $RR[$i+1] )
                        $nSpaces++;
                if ( count($RR) + $nSpaces >= $maxLength ) {
                    $done = true;
                    break;
                }
                $RR[] = $addV;
                sort($RR);
            }
            if ( $done )
                break;
        }
        $addRR = [];
        for ( $i = 0 ; $i < count($RR)-1 ; $i++ )
            if ( $RR[$i] + 1 == $RR[$i+1] - 1 )
                $addRR[] = $RR[$i]+1;
        $RR = array_merge($RR,$addRR);
        sort($RR);
        //
        $Yield = [];
        $preVal = $RR[0] - 1 ;
        foreach ( $RR as $v ) {
            if ( $preVal + 1 != $v )
                $Yield[] = 0;
            $Yield[] = $preVal = $v;
        }
        return $Yield;
    }
    const lm_safe_count = 62;
    const lm_alpha =
        '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'. // +62
        '_~@#%$&=!?'. // +10 = 72
        '`^*:;,.|'; // +8 = 80
    static function lm_dec_base( $number , $base = self::lm_safe_count ){
        if ( $base == 10 )
            return $number;
        $r = (int)bcmod( $number , $base );
        $res = self::lm_alpha[$r];
        $q = bcdiv ( $number , $base );
        while ( $q ) {
            $r = (int)bcmod( $q , $base );
            $q = bcdiv( $q , $base );
            $res = self::lm_alpha[$r].$res;
        }
        return $res;
    }
    static function lm_base_dec( $number , $base = self::lm_safe_count ){
        if ( $base == 10 )
            return $number;
        $limit = strlen( $number );
        $res = strpos( self::lm_alpha , $number[0] );
        for ( $i = 1 ; $i < $limit ; $i++ )
            $res = bcadd( bcmul( $base , $res ) , strpos( self::lm_alpha , $number[$i] ) );
        return $res;
    }
    static function lm_base_convert( $number , $fromBase , $toBase = self::lm_safe_count ){
        return self::lm_dec_base( self::lm_base_dec( $number , $fromBase ) , $toBase );
    }
}
