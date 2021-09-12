<?php
chdir(__DIR__);
require_once 'area/area.php';
require_once 'gar.php';
require_once 'garfn.php';
class imp_p_index extends \Area\Worker {
    public $workerID    = 526;
    public $workerGroup = 000;
    private $proc = 0;
    public function run(){
        if ( $tmp = GARFn::lockError() ) { $this->log($tmp); return $this->setResult(__LINE__); }
        GARFn::lockWrite($this->workerID);
        $this->log('Addr by OBJECTGUID');
        $this->db->fastQuery('ALTER TABLE tmpxx_addr ADD INDEX(`OBJECTGUID`)');
        //
        $this->log('Addr by owner_adm');
        $this->db->fastQuery('ALTER TABLE tmpxx_addr ADD INDEX(`owner_adm`)');
        //
        $this->log('Addr by owner_mun');
        $this->db->fastQuery('ALTER TABLE tmpxx_addr ADD INDEX(`owner_mun`)');
        //
        $this->log('House by owner_adm');
        $this->db->fastQuery('ALTER TABLE tmpxx_house ADD INDEX(`owner_adm`)'); //TODO возможно будет один owner
        //
        $this->log('House by owner_mun');
        $this->db->fastQuery('ALTER TABLE tmpxx_house ADD INDEX(`owner_mun`)'); //TODO возможно будет один owner
        //
        $this->setProcessedCount($this->proc)->setResult(0);
    }
}
new imp_p_index();
