<?php
chdir(__DIR__);
require_once 'area/area.php';
require_once 'gar.php';
require_once 'garfn.php';
class imp_q_replacer extends \Area\Worker {
    public $workerID    = 527;
    public $workerGroup = 000;
    private $proc = 0;
    public function run(){
        if ( $tmp = GARFn::lockError() ) { $this->log($tmp); return $this->setResult(__LINE__); }
        GARFn::lockWrite($this->workerID);
        $this->db->fastQuery('DROP TABLE gar_addr');
        $this->db->fastQuery('DROP TABLE gar_house');
        $this->db->fastQuery('RENAME TABLE tmpxx_addr TO gar_addr');
        $this->db->fastQuery('RENAME TABLE tmpxx_house TO gar_house');
        GARFn::lockClear();
        //
        $this->setProcessedCount($this->proc)->setResult(0);
    }
}
new imp_q_replacer();
