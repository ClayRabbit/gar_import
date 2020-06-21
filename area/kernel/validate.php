<?php
/*
 * @version 2020-05-19 5.0
 */
class validate {
    static function isDigits( $v ){
        return 1 === preg_match('/^[0-9]+$/', (string)$v );
    }
    static function isNatural( $v ){
        return 1 === preg_match('/^[1-9]+[0-9]*$/', (string)$v );
    }
    static function isNumeric( $v ){
        return 1 === preg_match('/^[-+]?[0-9]+$/', (string)$v );
    }
    static function isNumericPositive( $v ){
        return 1 === preg_match('/^[+]?[0]*[1-9]+[0-9]*$/', (string)$v );
    }
    static function isNumericPositiveWithZero( $v ){
        return 1 === preg_match('/^[+]?[0-9]+$/', (string)$v );
    }
    static function isFloat( $v ){
        return 1 === preg_match('/^[-+]?[0-9]*[.]?[0-9]+$/', (string)$v );
    }
    static function isLatin( $v ){
        return 1 === preg_match('/^[a-zA-Z]*$/', (string)$v );
    }
    static function isLatinWithNumeric( $v ){
        return 1 === preg_match('/^[0-9a-zA-Z]*$/', (string)$v );
    }
    static function isLatinWithSpace( $v ){
        return 1 === preg_match('/^[ a-zA-Z]*$/', (string)$v );
    }
    static function isLatinWithNumericAndSpace( $v ){
        return 1 === preg_match('/^[ 0-9a-zA-Z]*$/', (string)$v );
    }
    static function isLatinWithNumericAndDash( $v ){
        return 1 === preg_match('/^[_0-9a-zA-Z]*$/', (string)$v );
    }
    static function isLatinLowercase( $v ){
        return 1 === preg_match('/^[a-z]*$/', (string)$v );
    }
    static function isLatinLowercaseWithSpace( $v ){
        return 1 === preg_match('/^[ a-z]*$/', (string)$v );
    }
    static function isLatinUppercase( $v ){
        return 1 === preg_match('/^[A-Z]*$/', (string)$v );
    }
    static function isLatinUppercaseWithSpace( $v ){
        return 1 === preg_match('/^[ A-Z]*$/', (string)$v );
    }
    static function isURIPart( $v ){
        return 1 === preg_match('/^(([0-9]+[\-\_a-z]+[\-_0-9a-z]*)|([\_a-z]+[\-\_0-9a-z]*))$/i', (string)$v );
      //return 1 === preg_match('/^(([0-9]+[\-\_a-zA-Z]+[\-_0-9a-zA-Z]*)|([\_a-zA-Z]+[\-\_0-9a-zA-Z]*))$/', (string)$v );
    }
    static function isMD5( $v ){
        return 1 === preg_match('/^[0-9a-fA-F]{32}$/', (string)$v );
    }
    static function isLocalURI( $v ){
        return 1 === preg_match('/^(([0-9]+[\-\_a-zA-Z]+[\-_0-9a-zA-Z]*)|([\_a-zA-Z]+[\-\_0-9a-zA-Z]*))$/', (string)$v );
    }
    static function isGlobalURI( $v ){ /* slash or /[local]/.../[local]/ */
        $local_uri = '[0-9]+[\-\_a-zA-Z]+[\-\_a-zA-Z0-9]*'.'|'.'[\_a-zA-Z]+[\-\_a-zA-Z0-9]*' ;
        return 1 === preg_match('/^\/(('.$local_uri.')\/)*$/' , (string)$v );
    }
    static function isPhoneNumber( $v ){
        return 1 === preg_match('/^[+]?[0-9]{11,13}$/', (string)$v );
    }
    static function isEmail( $v ){
        if (strlen($v) > 127)
            return false;
		$p = explode('@',$v);
		if ( 2 != count($p) )
			return false;
		if ( !self::isDomain($p[1]) )
			return false;
		$l = strtolower($p[0]);
		if ( strlen($l) < 1 )
			return false;
		$abc = 'qwertyuiopasdfghjklzxcvbnm0123456789_';
		if ( strpos($abc,$l[0]) === false )
			return false;
		$abc .= '-.';
		for ( $i = 1 ; $i < strlen($l) ; $i++ ) {
			if ( stripos($abc,$l[$i]) === false )
				return false;
		}
		return true;
    }
    static function isDomain( $v ){
        return
            preg_match('/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))+$/i', $v) &&
            preg_match('/^.{1,253}$/', $v) &&
            preg_match('/^[^\.]{1,63}(\.[^\.]{1,63})*$/', $v);
    }
	static function isDate( $s ){
		if ( !preg_match('/^[\d]{1,2}\.[\d]{1,2}\.[\d]{1,5}$/',$s) )
		    return false;
        $s = explode('.',$s);
		return checkdate( (int)$s[1] , (int)$s[0] , (int)$s[2] );
	}
    static function isDateTime( $v ){
        if ( ! (1 === preg_match(
            '/^(0[1-9]{1}|[1-2]{1}[0-9]{1}|3[0-1]{1})\.(0[1-9]{1}|[1-2]{1}[0-9]{1}|30|31)\.(18|19|20|21)[0-9]{2}'.
            '( )+([0-1]{0,1}[0-9]{1}|2[0-3]{1}):([0-9]{1}|[0-5]{1}[0-9]{1})(:[0-9]{1}|:[0-5]{1}[0-9]{1}){0,1}$/'
            , (string)$v )) )
            return false;
        if ( !checkdate(mb_substr($v,3,2,'utf-8'),mb_substr($v,0,2,'utf-8'),mb_substr($v,6,4,'utf-8')) )
            return false;
        return true;
    }
    static function isSqlDate( $v ){
        if ( ! (1 === preg_match('/^(18|19|20|21)[0-9]{2}-(0[1-9]{1}|11|12)-(0[1-9]{1}|1[0-9]{1}|2[0-9]{1}|30|31)$/' , (string)$v )) )
            return false;
        if ( !checkdate(mb_substr($v,5,2,'utf-8'),mb_substr($v,8,2,'utf-8'),mb_substr($v,0,4,'utf-8')) )
            return false;
        return true;
    }
    static function isSqlDateTime( $s ){
        $s = explode(' ',$s);
        $d = explode('-',$s[0]);
        if ( 3 != count($d) )
            return false;
        $yy = (int)$d[0];
        $mm = (int)$d[1];
        $dd = (int)$d[2];
        if ( !checkdate($mm,$dd,$yy) )
            return false;
        $s = explode(':',$s[1]);
        if ( 3 != count($s) )
            return false;
        $hh = (int)$s[0];
        $ii = (int)$s[1];
        $ss = (int)$s[2];
        if ( ( $hh < 0 ) || ( $hh > 23 ) || ( $ii < 0 ) || ( $ii > 59 ) || ( $ss < 0 ) || ( $ss > 59 ) )
            return false;
        return true;
    }
    static function isLogin( $v ){
        return 1 === preg_match('/^[a-zA-Z]+((\.|-)[0-9a-zA-Z]+|[0-9a-zA-Z]+)+$/', (string)$v );
    }
    static function isVariableName( $v ){
        return 1 === preg_match('/^[a-zA-Z_]+[a-zA-Z_0-9]*$/' , (string)$v ) ;
    }
    static function isIPv4( $s ){
        if ( 4 != count( $S = explode('.',$s) ) )
            return false;
        for ( $i = 0 ; $i < 4 ; $i++ ) {
            if ( !is_numeric($S[$i]) )
                return false;
            if ( ( $S[$i] < 0 ) || ( $S[$i] > 255 ) )
                return false;
        }
        return true;
    }
    static function isIPv4Mask( $s ){
        if ( 2 != count( $S = explode('/',$s) ) )
            return false;
        if ( !self::isNumeric($S[1]) )
            return false;
        if ( ( (int)$S[1] < 0 ) || ( (int)$S[1] > 32 ) )
            return false;
        if ( !self::isIPv4($S[0]) )
            return false;
        return true;
    }
    static function isIPv6( $s ){
        if ( strlen($s) < 2 )
            return false;
        if ( ( false !== strpos($s,'@') ) || ( false !== strpos($s,'#') ) )
            return false;
        $s = str_replace(
            array(
                '0','1','2','3','4','5','6','7','8','9',
                'a','b','c','d','e','f',
                'A','B','C','D','E','F'),
            '@' , $s
        );
        $s = str_replace('@','#',str_replace('@@','#',str_replace('@@@','#',str_replace('@@@@','#',$s))));
        if ( ( $p = strpos($s,'::') ) != strrpos($s,'::') )
            return false;
        if ( false !== strpos($s,'@') )
            return false;
        if ( ( $p == 0 ) || ( $p == strlen($s)-2 ) )
            $s = str_replace('::',':',$s);
        $s = explode(':',$s);
        if ( ( $n = count($s) ) > 8 )
            return false;
        $multiple = false;
        for ( $i = 0 ; $i < $n ; $i++ ) {
            if ( $s[$i] == '' ) {
                $multiple = true;
                continue;
            }
            if ( $s[$i] != '#' )
                return false;
        }
        if ( ( !$multiple ) && ( $n != 8 ) )
            return false;
        return true;
    }
}
