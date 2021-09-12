<?php
chdir(__DIR__);
require_once 'area/area.php';
require_once 'gar.php';
require_once 'garfn.php';
class imp_c_index extends \Area\Worker {
    public $workerID    = 513;
    public $workerGroup = 000;
    private $proc = 0;
    public function run(){
        if ( $tmp = GARFn::lockError() ) { $this->log($tmp); return $this->setResult(__LINE__); }
        GARFn::lockWrite($this->workerID);
        // Проверяем, что tmp**_addr.OBJECTID является уникальным (т.к. в базе записи с ISACTUAL=1 & ISACTIVE=1)
        foreach ( GAR::Regions as $region ) {
            echo "$region ";
            $table_addr = 'tmp'.$region.'_addr';
            if ( $nL = count($this->db->getRecords('SELECT OBJECTID , count(*) AS h FROM '.$table_addr.' GROUP BY 1 HAVING h > 1')) ) {
                GARFn::lockWrite($this->workerID,__LINE__,'OBJECTID is not unique, region: ' . $region . 'conflicts: ' . $nL);
                return $this->setResult(__LINE__);
            }
            $this->db->fastQuery('ALTER TABLE ' . $table_addr . ' DROP INDEX `OBJECTID`'); // debug mode: if it was
            $this->db->fastQuery('ALTER TABLE ' . $table_addr . ' ADD UNIQUE(`OBJECTID`)');
        }
        echo "\n";
        // -> Проверили, что tmp**_addr.OBJECTID уникален и создали индекс //
        $this->setProcessedCount($this->proc)->setResult(0);
    }
}
new imp_c_index();
