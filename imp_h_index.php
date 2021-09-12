<?php
chdir(__DIR__);
require_once 'area/area.php';
require_once 'gar.php';
require_once 'garfn.php';
class imp_h_index extends \Area\Worker {
    public $workerID    = 518;
    public $workerGroup = 000;
    private $proc = 0;
    public function run(){
        if ( $tmp = GARFn::lockError() ) { $this->log($tmp); return $this->setResult(__LINE__); }
        GARFn::lockWrite($this->workerID);
        foreach ( GAR::Regions as $region ) {
            $table_addr = 'tmp'.$region.'_addr';
            echo "$region ";
            $this->db->fastQuery('ALTER TABLE ' . $table_addr . ' DROP INDEX `owner_adm`');
            $this->db->fastQuery('ALTER TABLE ' . $table_addr . ' DROP INDEX `owner_mun`');
            $this->db->fastQuery('ALTER TABLE ' . $table_addr . ' ADD INDEX(`owner_adm`)');
            $this->db->fastQuery('ALTER TABLE ' . $table_addr . ' ADD INDEX(`owner_mun`)');
        }
        echo "\n";
        // -> Индексируем tmp**_addr по owner_adm , owner_mun
        $this->setProcessedCount($this->proc)->setResult(0);
    }
}
new imp_h_index();
