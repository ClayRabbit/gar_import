<?php
/*
 * @version 2020-06-27 1.0
 * @version 2020-09-24 1.1
 */
namespace Area;
class Worker {
    public $workerID = 0;
    public $workerGroup = 0;
    public $db;
    private $_ts;
    private $_tf;
    private $_key;
    private $_instance;
    private $_n_queue = 0;
    private $_n_proc = 0;
    private $_result = -1;
    function __construct(){
        if (
            ( $this->workerID <= 0 ) || ( $this->workerID > 999 ) ||
            ( $this->workerGroup < 0 ) || ( $this->workerGroup > 999 ) ||
            ( $this->workerID == $this->workerGroup )
        )
            die('Worker ID\Group configuration error');
        if ( isset($_SERVER['HTTP_HOST']) )
            die('Only console run enabled');
        //
        $this->db = Kernel::$DB;
        $this->_key = 'worker.' . $this->workerID . '.instance';
        $this->_instance = 1 + Settings::readInt($this->_key);
        //
        $terminateLater = false;
        if ( !@socket_bind ( $sockID = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) , '127.0.0.1', GC_Worker_PortOffset + $this->workerID  ) ) {
            echo 'Worker #' . $this->workerID . ' already running [1]' . "\n";
            $terminateLater = true; // -> log conflict, try run before previously not completed
        }
        if ( !@socket_listen($sockID) ) {
            echo 'Worker #' . $this->workerID . ' already running [2]' . "\n";
            $terminateLater = true; // -> log conflict, try run before previously not completed
        }
        $sockGr = false;
        if ( !$terminateLater && $this->workerGroup ) {
            if ( !@socket_bind ( $sockGr = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) , '127.0.0.1', GC_Worker_PortOffset + $this->workerGroup  ) )
                die('Group #' . $this->workerGroup . ' already running [1]' . "\n"); // -> die (no conflict, only group limit)
            if ( !@socket_listen($sockGr) )
                die('Group #' . $this->workerGroup . ' already running [2]' . "\n"); // -> die (no conflict, only group limit)
        }
        if ( $terminateLater ){
            $this->db->mkInsert('l_worker_conflict',array(
                't' => time() ,
                'ts' => \fw::clockSQL() ,
                'worker' => $this->workerID ,
                'instance' => $this->_instance
            ));
            die;
        }
        //
        \Area\Settings::writeInt( $this->_key , $this->_instance );
        set_time_limit(0);
        //
        $this->_ts = microtime(true);
        $this->log('>S< '.get_class($this).' ID:'.$this->workerID.' instance:'.$this->_instance); // log uses $this->_ts
        $this->run();
        $this->_tf = microtime(true);
        $this->log('>F<');
        $this->db->mkInsert('l_worker',array(
            'worker' => $this->workerID ,
            'instance' => $this->_instance ,
            's' => $this->_ts , 'ts' => date('Y-m-d H:i:s',$this->_ts) ,
            'f' => $this->_tf , 'tf' => date('Y-m-d H:i:s',$this->_tf) ,
            'runtime' => $this->_tf - $this->_ts ,
            'n_queue' => $this->_n_queue ,
            'n_proc' => $this->_n_proc ,
            'result' => $this->_result
        ));
        socket_close($sockID);
        if ( $sockGr )
            socket_close($sockGr);
        $this->log('>E<');
    }
    function setResult( $resultCode ){
        $this->_result = intval($resultCode);
        return $this;
    }
    function setQueue( $n ){
        $this->_n_queue = intval($n);
        return $this;
    }
    function setProcessedCount( $n ){
        $this->_n_proc = intval($n);
        return $this;
    }
    function log( $s ){
        echo date('ymd H:i:s').' #'.$this->workerID.' ['.str_pad(\fw::f4(microtime(true)-$this->_ts),9,' ',STR_PAD_LEFT).'] '.$s."\n";
        return $this;
    }
    function run(){
        $this->log('Worker::run must be redefined');
    }
}
