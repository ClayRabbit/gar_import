<?php
chdir(__DIR__);
require_once 'area/area.php';
require_once 'gar.php';
require_once 'garfn.php';
class imp_e_index extends \Area\Worker {
    public $workerID    = 515;
    public $workerGroup = 000;
    private $proc = 0;
    public function run(){
        if ( $tmp = GARFn::lockError() ) { $this->log($tmp); return $this->setResult(__LINE__); }
        GARFn::lockWrite($this->workerID);
        // В отличие от addr, проверяем, что tmp**_house.OBJECTID является уникальным потом (много записей)
        foreach ( GAR::Regions as $region ) {
            echo "$region ";
            $table_house = 'tmp'.$region.'_house';
            $this->db->fastQuery('ALTER TABLE ' . $table_house . ' DROP INDEX `OBJECTID`'); // debug mode: if it was
            $this->db->fastQuery('ALTER TABLE ' . $table_house . ' ADD UNIQUE(`OBJECTID`)');
            if ( 1 != count($this->db->getRecords(
                'SHOW INDEX FROM ' . $table_house . ' WHERE Key_name = "OBJECTID";'
            )) ){
                GARFn::lockWrite($this->workerID,__LINE__,'OBJECTID is not unique for '.$table_house);
                return $this->setResult(__LINE__);
            }
        }
        echo "\n";
        // -> Убедились, что tmp_gar_house.OBJECTID уникален и создали индекс //
        $this->setProcessedCount($this->proc)->setResult(0);
    }
}
new imp_e_index();
