<?php
chdir(__DIR__);
require_once 'area/area.php';
require_once 'gar.php';
require_once 'garfn.php';
class imp_m_prerelease extends \Area\Worker {
    public $workerID    = 523;
    public $workerGroup = 000;
    private $proc = 0;
    public function run(){
        if ( $tmp = GARFn::lockError() ) { $this->log($tmp); return $this->setResult(__LINE__); }
        GARFn::lockWrite($this->workerID);
        //
        foreach ( GAR::Regions as $region ){
            $table_addr = 'tmp'.$region.'_addr';
            $table_house = 'tmp'.$region.'_house';
            echo "$region ";
            $this->db->fastQuery('ALTER TABLE ' . $table_addr . ' DROP touch_adm');
            $this->db->fastQuery('ALTER TABLE ' . $table_addr . ' DROP touch_mun');
     //TODO $this->db->fastQuery('ALTER TABLE `tmp_gar_house` DROP `owner_mun`');
     //TODO $this->db->fastQuery('ALTER TABLE `tmp_gar_house` CHANGE `owner_adm` `owner` INT NOT NULL DEFAULT 0');
            $this->db->fastQuery('ALTER TABLE ' . $table_addr . ' DROP INDEX `OBJECTID`');
            $this->db->fastQuery('ALTER TABLE ' . $table_house . ' DROP INDEX `OBJECTID`');
        }
        echo "\n";
        //
        $this->setProcessedCount($this->proc)->setResult(0);
    }
}
new imp_m_prerelease();
