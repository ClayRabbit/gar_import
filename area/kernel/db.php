<?php
namespace Area;
/*
 * @version 2020-04-28 1.0 V9
 */
class DB {
    protected
        $connected = false ,
        $debug = false ,
        $debugID = 0 ,
        $debugRand;
    function __construct( $debug = false ){
        $this->debug = $debug;
        $this->debugRand = md5(microtime(true));
    }
    function set_debug( $debug ){
        $this->debug = $debug;
    }
    function err_debug( $query = '' ){
        if ( $this->debug )
            file_put_contents(
                'db_engine.'.date('Y-m-d').'.err' ,
                date('Y-m-d H:i:s ') . $this->debugRand.' id:' . \fw::sid() .
                "\n$query\n\n" ,
                FILE_APPEND );
    }
    /**
     * @param string $query
     * @return boolean | string
     */
    function fastQuery( $query = '' ){
        if ( !$this->connected )
            return false;
        if ( $this->debug )
            file_put_contents(
                'db_engine.'.date('Y-m-d').'.log' ,
                date('Y-m-d H:i:s ') . $this->debugRand.':'.($this->debugID++) . ' @fastQuery id:' . \fw::sid() .
                "\n$query\n\n" ,
                FILE_APPEND );
        return true;
    }
    /**
     * @param string $query
     * @return boolean | string
     */
    function getItem( $query = ''){
        if ( !$this->connected )
            return false;
        if ( $this->debug )
            file_put_contents(
                'db_engine.'.date('Y-m-d').'.log' ,
                date('Y-m-d H:i:s ') . $this->debugRand.':'.($this->debugID++) . ' @getItem id:' . \fw::sid() .
                "\n$query\n\n" ,
                FILE_APPEND );
        return true;
    }
    /**
     * @param string $query
     * @return array | boolean | string
     */
    function getRow( $query = '' ){
        if ( !$this->connected )
            return false;
        if ( $this->debug )
            file_put_contents(
                'db_engine.'.date('Y-m-d').'.log' ,
                date('Y-m-d H:i:s ') . $this->debugRand.':'.($this->debugID++) . ' @getRow id:' . \fw::sid() .
                "\n$query\n\n" ,
                FILE_APPEND );
        return true;
    }
    /**
     * @param string $query
     * @return array | boolean | string
     */
    function getRecords( $query = '' ){
        if ( !$this->connected )
            return false;
        if ( $this->debug )
            file_put_contents(
                'db_engine.'.date('Y-m-d').'.log' ,
                date('Y-m-d H:i:s ') . $this->debugRand.':'.($this->debugID++) . ' @getRecords id:' . \fw::sid() .
                "\n$query\n\n" ,
                FILE_APPEND );
        return true;
    }
    function query( $query  , $storage = 'Default' ){
        if ( !$this->connected )
            return false;
        if ( $this->debug )
            file_put_contents(
                'db_engine.'.date('Y-m-d').'.log' ,
                date('Y-m-d H:i:s ') . $this->debugRand.':'.($this->debugID++) . ' @query/'.$storage.' id:' . \fw::sid() .
                "\n$query\n\n" ,
                FILE_APPEND );
        return true;
    }
    function queryRows( $storage = 'Default' ){ die("Implementation required queryRows:[$storage]"); }
    function queryClose( $storage = 'Default' ){ die("Implementation required queryClose:[$storage]"); }
    function queryFetchRow($storage = 'Default'){ die("Implementation required queryFetchRow:[$storage]"); }
    function queryFetchAssoc($storage = 'Default'){ die("Implementation required queryFetchAssoc:[$storage]"); }
    //
    function rawFilter( $filter ){
        if ( !is_array($filter) )
            return false;
        $builder = [];
        foreach ( $filter as $column => $value ) {
            if ( is_array($value) ) {
                $builder[] = '(' . $this->p($column) . ' LIKE \'' . $this->l($value[0]) . '\')';
            } elseif ( ( false === $value ) || ( NULL === $value ) ) {
                $builder[] = '(' . $this->p($column) . ' IS NULL)';
            } else {
                $builder[] = '(' . $this->p($column) . ' = \'' . $this->s($value) . '\')';
            }
        }
        if ( count($builder) )
            return implode(' AND ',$builder);
        return false;
    }
    function rawOrder( $order ){
        if ( !$order )
            return false;
        if ( is_array($order) ) {
            $builder = array();
            foreach ( $order as $order_field ) {
                if ( !$order_field )
                    continue;
                if ( 2 == count( $order_pair = explode(' ',trim($order_field))) )
                    $builder[] = $this->p($order_pair[0]) . ' ' . $order_pair[1]; else
                    $builder[] = $this->p(trim($order_field));
            }
            if ( count($builder) )
                return implode(',', $builder);
        } else {
            if ( 2 == count( $order_pair = explode(' ',trim($order))) )
                return $this->p($order_pair[0]) . ' ' . $order_pair[1]; else
                return $this->p(trim($order));
        }
        return false;
    }
    //
    function mkCount( $table , $filter = [] ){
        $rawQuery = 'SELECT /* mkCount */ COUNT(*) FROM ' . $this->p($table);
        if ( is_array($filter) && count($filter) && ( $strFilter = $this->rawFilter($filter) ) )
            $rawQuery .= ' WHERE ' . $strFilter;
        //
        if ( false === ( $R = $this->getItem( $rawQuery ) ) )
            return false;
        return intval($R);
    }
    function mkItem( $table , $column , $filter = [] , $order = false , $offset = 0 ){
        $rawQuery = 'SELECT /* mkItem */ ' . $this->p($column) . ' FROM ' . $this->p($table) ;
        if ( is_array($filter) && count($filter) && ( $strFilter = $this->rawFilter($filter) ) )
            $rawQuery .= ' WHERE ' . $strFilter;
        if ( $order && ( $strOrder = $this->rawOrder($order)) )
            $rawQuery .= ' ORDER BY ' . $strOrder;
        $rawQuery .= ' LIMIT 1' . ( $offset ? ( ' OFFSET ' . intval($offset) ) : '' ) ;
        //
        if ( !$R = $this->getRow( $rawQuery ) )
            return false;
        return isset($R[$column]) ? $R[$column] : false;
    }
    function mkRow( $table , $filter = [] , $order = false , $offset = 0 ){
        $rawQuery = 'SELECT /* mkRow */ * FROM ' . $this->p($table);
        if ( is_array($filter) && count($filter) && ( $strFilter = $this->rawFilter($filter) ) )
            $rawQuery .= ' WHERE ' . $strFilter;
        if ( $order && ( $strOrder = $this->rawOrder($order)) )
            $rawQuery .= ' ORDER BY ' . $strOrder;
        $rawQuery .= ' LIMIT 1' . ( $offset ? ( ' OFFSET ' . intval($offset) ) : '' ) ;
        //
        return $this->getRow($rawQuery);
    }
    protected function mkInsert_baseQuery( $table , $data ){
        $rawQuery = 'INSERT /* mkInsert */ INTO ' . $this->p($table) ;
        $columns = [];
        $values = [];
        foreach ( $data as $column => $value ) {
            $columns[] = $this->p( $column );
            if ( ( $value !== false ) && ( $value !== NULL ) )
                $values[] = '\'' . $this->s( $value ) . '\''; else
                $values[] = 'NULL';
        }
        $rawQuery .= ' (' . implode(',',$columns) . ') VALUES ( ' . implode(',',$values) . ')';
        return $rawQuery;
    }
    /**
     * @param $table
     * @param $data
     * @param string $serialColumn
     * @return array | boolean | string
     */
    function mkInsert( $table , $data , $serialColumn = 'id' ){
        die("Implementation required mkInsert:[$table]:".serialize($data).':'.serialize($serialColumn));
    }
    function mkInsertExtra( $table , $data , $extraData = [] , $extraKeys = [] , $serialColumn = 'id' ){
        foreach ( $extraKeys as $key )
            $data[$key] = $extraData[$key];
        return $this->mkInsert($table,$data,$serialColumn);
    }
    function mkUpdate( $table , $data , $column = false , $value = false ){
        $rawQuery = 'UPDATE /* mkUpdate */ ' . $this->p($table) . ' SET ';
        $dataPair = [];
        foreach ( $data as $_column => $_value )
            if ( ( $_value !== false ) && ( $_value !== NULL ) )
                $dataPair[] = $this->p( $_column ) . ' = \'' . $this->s( $_value ) . '\''; else
                $dataPair[] = $this->p( $_column ) . ' = NULL';
        $rawQuery .= implode( ', ' , $dataPair );
        if ( $column ) {
            $rawQuery .= ' WHERE ' . $this->p($column);
            if ( false !== $value )
                $rawQuery .= ' = \'' . $this->s($value) . '\'' ; else
                $rawQuery .= ' IS NULL';
        }
        //
        return $this->fastQuery( $rawQuery );
    }
    function mkUpdateExtra( $table , $data , $extraData , $extraKeys , $column , $value ){
        foreach ( $extraKeys as $key )
            $data[$key] = $extraData[$key];
        return $this->mkUpdate($table,$data,$column,$value);
    }
    function mkUpdateFilter( $table , $data , $filter = [] ){
        $rawQuery = 'UPDATE /* mkUpdateFilter */ ' . $this->p($table) . ' SET ';
        $dataPair = [];
        foreach ( $data as $_column => $_value )
            if ( ( $_value !== false ) && ( $_value !== NULL ) )
                $dataPair[] = $this->p( $_column ) . ' = \'' . $this->s( $_value ) . '\''; else
                $dataPair[] = $this->p( $_column ) . ' = NULL';
        $rawQuery .= implode( ', ' , $dataPair );
        if ( is_array($filter) && count($filter) && ( $strFilter = $this->rawFilter($filter) ) )
            $rawQuery .= ' WHERE ' . $strFilter;
        //
        return $this->fastQuery( $rawQuery );
    }
    function mkDelete( $table , $column , $value = false ){
        $rawQuery = 'DELETE /* mkDelete */ FROM ' . $this->p($table);
        if ( $column ) {
            $rawQuery .= ' WHERE ' . $this->p($column);
            if ( false !== $value )
                $rawQuery .= ' = \'' . $this->s($value) . '\'' ; else
                $rawQuery .= ' IS NULL';
        }
        //
        return $this->fastQuery( $rawQuery );
    }
    function mkDeleteFilter( $table , $filter = [] ){
        $rawQuery = 'DELETE /* mkDeleteFilter */ FROM ' . $this->p($table);
        if ( is_array($filter) && count($filter) && ( $strFilter = $this->rawFilter($filter) ) )
            $rawQuery .= ' WHERE ' . $strFilter;
        //
        return $this->fastQuery( $rawQuery );
    }
    function mkSelect( $table , $column = false , $value = false , $order = false , $limit = false , $offset = 0 ){
        $rawQuery = 'SELECT /* mkSelect */ * FROM ' . $this->p($table);
        if ( $column ) {
            $rawQuery .= ' WHERE ' . $this->p($column);
            if ( false !== $value )
                $rawQuery .= ' = \'' . $this->s($value) . '\'' ; else
                $rawQuery .= ' IS NULL';
        }
        if ( $order && ( $strOrder = $this->rawOrder($order)) )
            $rawQuery .= ' ORDER BY ' . $strOrder;
        if ( $limit ) {
            $rawQuery .= ' LIMIT ' . intval($limit);
            if ( $offset )
                $rawQuery .= ' OFFSET ' . intval($offset);
        }
        //
        return $this->getRecords( $rawQuery );
    }
    function mkSelectFilter( $table , $filter = [] , $order = false , $limit = false , $offset = 0 ){
        $rawQuery = 'SELECT /* mkSelectFilter */ * FROM ' . $this->p($table);
        if ( is_array($filter) && count($filter) && ( $strFilter = $this->rawFilter($filter) ) )
            $rawQuery .= ' WHERE ' . $strFilter;
        if ( $order && ( $strOrder = $this->rawOrder($order)) )
            $rawQuery .= ' ORDER BY ' . $strOrder;
        if ( $limit ) {
            $rawQuery .= ' LIMIT ' . intval($limit);
            if ( $offset )
                $rawQuery .= ' OFFSET ' . intval($offset);
        }
        //
        return $this->getRecords( $rawQuery );
    }
    function mkSelectList( $table , $column , $filter = [] , $order = false ){
        $rawQuery =
            'SELECT /* mkSelectList */ ' .
            $this->p($table).'.'.$this->p($column) . ' AS v  ' .
            'FROM ' . $this->p($table);
        if ( is_array($filter) && count($filter) && ( $strFilter = $this->rawFilter($filter) ) )
            $rawQuery .= ' WHERE ' . $strFilter;
        if ( $order === true )
            $order = $column;
        if ( $order && ( $strOrder = $this->rawOrder($order)) )
            $rawQuery .= ' ORDER BY ' . $strOrder;
        $yield = [];
        foreach ( $this->getRecords($rawQuery) as $e )
            $yield[] = $e['v'];
        return $yield;
    }
    function mkSelectAssocPair( $table , $column_as_key , $column_as_value , $filter = [] , $order = false ){
        $rawQuery =
            'SELECT /* mkSelectAssocPair */ ' .
            $this->p($table).'.'.$this->p($column_as_key) . ' AS k , ' .
            $this->p($table).'.'.$this->p($column_as_value) . ' AS v  ' .
            'FROM ' . $this->p($table);
        if ( is_array($filter) && count($filter) && ( $strFilter = $this->rawFilter($filter) ) )
            $rawQuery .= ' WHERE ' . $strFilter;
        if ( $order === true )
            $order = $column_as_key;
        if ( $order && ( $strOrder = $this->rawOrder($order)) )
            $rawQuery .= ' ORDER BY ' . $strOrder;
        $yield = [];
        foreach ( $this->getRecords($rawQuery) as $e )
            $yield[$e['k']] = $e['v'];
        return $yield;
    }
    //
    function d( $s ){
        return date('Y-m-d',strtotime($s));
    }
    function t( $s ){
        return date('Y-m-d H:i:s',strtotime($s));
    }
    /**
     * @param $s
     * @return string
     */
    function p( $s ){ die("Implementation required p:[$s]"); }
    /**
     * @param $s
     * @return string
     */
    function s( $s ){ die("Implementation required s:[$s]"); }
    /**
     * @param $s
     * @return string
     */
    function l( $s ){
        if ( !$s )
            return '%';
        $pcntL = $pcntR = true;
        $l = mb_strlen($s,'utf-8');
        if ( mb_substr($s,0,1,'utf-8') == '^' ) {
            $pcntL = false;
            $s = mb_substr($s,1,$l-1,'utf-8');
            $l--;
        }
        if ( ($l>1) && ( mb_substr($s,$l-1,1,'utf-8') == '$' ) && ( mb_substr($s,$l-2,2,'utf-8') != '\$' ) ) {
            $pcntR = false;
            $s = mb_substr($s,0,$l-1,'utf-8');
        }
        $re = '';
        while ( $s != '' ) {
            $cc = mb_substr($s,0,2,'utf-8');
            if ( ( $cc == '\%' ) || ( $cc == '\_' ) ) {
                $re .= $cc;
                $s = mb_substr($s,2,mb_strlen($s,'utf-8')-2,'utf-8');
                continue;
            }
            if ( ( $cc == '\*' ) || ( $cc == '\?' ) ) {
                $re .= mb_substr($cc,1,1,'utf-8');
                $s = mb_substr($s,2,mb_strlen($s,'utf-8')-2,'utf-8');
                continue;
            }
            $cc = mb_substr($s,0,1,'utf-8');
            $s = mb_substr($s,1,mb_strlen($s,'utf-8')-1,'utf-8');
            if ( ( $cc == '%' ) || ( $cc == '*' ) ) {
                $re .= '%';
                continue;
            }
            if ( ( $cc == '_' ) || ( $cc == '?' ) ) {
                $re .= '_';
                continue;
            }
            $re .= $this->s($cc);
        }
        return ($pcntL?'%':'').$re.($pcntR?'%':'');
    }
}
