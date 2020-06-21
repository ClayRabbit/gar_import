<?php
namespace Area;
/*
 * @version 2020-04-25 1.0 V9
 */
require_once 'area/kernel/db.php';
class DB_MySQLi extends DB {
    private $db_host, $db_port = 3306, $db_name, $db_user, $db_pass, $db_pause = 1113, $db_attempts = 1;
    private $db, $re;
    function __construct( $host = 'localhost' , $user = 'root' , $password = '' , $base = '' , $debug = false ){
        $this->db = new \mysqli();
        $this->db_host = $host;
        $this->db_user = $user;
        $this->db_pass = $password;
        $this->db_name = $base;
        $this->re = array();
        parent::__construct( $debug );
    }
    function set_port( $Port = 3306 ){ $this->db_port = $Port; }
    function set_connect_attempts( $n ){ $this->db_attempts = intval($n); }
    function set_connect_pause( $n ){ $this->db_pause = intval($n); }
    function install(){
        if ( $this->connected )
            return false;
        $Attempt = 0;
        while (true) {
            $this->db = @new \mysqli( $this->db_host , $this->db_user , $this->db_pass , $this->db_name , $this->db_port );
            if ( $this->db->connect_error ) { // php > 5.3.0
                $Attempt++;
                if ($Attempt >= $this->db_attempts)
                    return false;
                usleep((int) (1000 * $this->db_pause));
                continue;
            }
            break;
        }
        $this->connected = true;
        return true;
    }
    /**
     * @param string $query
     * @return bool
     */
    function fastQuery( $query = '' ){
        if ( !parent::fastQuery($query) )
            return false;
        if ( false === ( $R = $this->db->query( $query ) ) ) {
            $this->err_debug($query);
            return false;
        }
        if ( is_object($R) )
            $R->close();
        return true;
    }
    /**
     * @param string $query
     * @return bool|mixed
     */
    function getItem( $query = '' ){
        if ( !parent::getItem($query) )
            return false;
        if ( !$R = $this->db->query( $query ) ) {
            $this->err_debug($query);
            return false;
        }
        if ( !$Row = $R->fetch_array( MYSQLI_NUM ) )
            return false;
        if ( isset($Row[0]) )
            $yield = $Row[0]; else
            return false;
        $R->close();
        return $yield;
    }
    /**
     * @deprecated
     * @param string $query
     * @return int
     */
    function getItemInt( $query = '' ){
        $r = $this->getItem( $query );
        if ( false === $r )
            return false;
        return (int)$r;
    }
    /**
     * @param string $query
     * @return bool
     */
    function getRow( $query = '' ){
        if ( !parent::getRow($query) )
            return false;
        if ( !$R = $this->db->query( $query ) ) {
            $this->err_debug($query);
            return false;
        }
        if ( !$Row = $R->fetch_array( MYSQLI_ASSOC ) )
            return false;
        $R->close();
        return $Row;
    }
    function getRecords( $query = '' ){
        if ( !parent::getRecords($query) )
            return false;
        if ( $R = $this->db->query($query) ) {
            $Return = array();
            while ( $row = $R->fetch_assoc() )
                $Return[] = $row;
        } else {
            $this->err_debug($query);
            return false;
        }
        $R->close();
        return $Return;
    }
    /**
     * @return bool
     */
    function system_ping(){
        if (!$this->connected)
            return false;
        return $this->db->ping();
    }
    /**
     * @param $query
     * @param string $storage
     * @return bool
     */
    function query( $query , $storage = 'Default' ){
        if ( !parent::query($query,$storage) )
            return false;
        if ( false === ( $this->re[ $storage ] = $this->db->query( $query ) ) ) {
            $this->err_debug($query);
            unset( $this->re[ $storage ] );
            return false;
        }
        return true;
    }
    /**
     * @param string $storage
     * @return bool|int
     */
    function queryRows( $storage = 'Default' ){
        if ( !isset($this->re[$storage]) )
            return false;
        return $this->re[$storage]->num_rows;
    }
    /**
     * @param string $storage
     * @return bool
     */
    function queryClose( $storage = 'Default' ){
        if ( !isset($this->re[$storage]) )
            return false;
        $this->re[$storage]->close();
        unset( $this->re[$storage] );
        return true;
    }
    /**
     * @param string $storage
     * @return bool|array
     */
    function queryFetchRow( $storage = 'Default' ){
        if (!isset( $this->re[$storage] ))
            return false;
        return $this->re[$storage]->fetch_array( MYSQLI_NUM );
    }
    /**
     * @param string $storage
     * @return bool|array
     */
    function queryFetchAssoc( $storage = 'Default' ){
        if (!isset( $this->re[$storage] ))
            return false;
        return $this->re[$storage]->fetch_array( MYSQLI_ASSOC );
    }
    /**
     * @param $table
     * @param $data
     * @param $serialColumn_not_used
     * @return bool|mixed
     */
    function mkInsert( $table , $data , $serialColumn_not_used = 'id' ){
        if ( !is_array($data) )
            return false;
        if ( !$this->fastQuery( $this->mkInsert_baseQuery($table,$data) ) )
            return false;
        return $this->db->insert_id;
    }
    function insertID(){
        if ( !$this->connected )
            return false;
        return $this->db->insert_id;
    }
    function p( $s ){
        return '`'.$this->db->real_escape_string( $s ).'`';
    }
    function s( $s ){
        return $this->db->real_escape_string( $s );
    }
}
