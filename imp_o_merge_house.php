<?php
chdir(__DIR__);
require_once 'area/area.php';
require_once 'gar.php';
require_once 'garfn.php';
class imp_o_merge_house extends \Area\Worker {
    public $workerID    = 525;
    public $workerGroup = 000;
    private $proc = 0;
    public function run(){
        if ( $tmp = GARFn::lockError() ) { $this->log($tmp); return $this->setResult(__LINE__); }
        GARFn::lockWrite($this->workerID);
        //
        $this->db->fastQuery('DROP TABLE IF EXISTS tmpxx_house');
        $this->db->fastQuery(
            'CREATE TABLE tmpxx_house ( '.
            '`id` int NOT NULL,'.
            '`owner_adm` int NOT NULL DEFAULT 0,'.
            '`owner_mun` int NOT NULL DEFAULT 0,'.
            '`OBJECTID` decimal(31,0) NOT NULL DEFAULT 0,'.
            '`OBJECTGUID` char(36) COLLATE ascii_bin NOT NULL,'.
            '`HOUSENUM` char(36) COLLATE utf8mb4_bin DEFAULT NULL,'.
            '`ADDNUM1` char(36) COLLATE utf8mb4_bin DEFAULT NULL,'.
            '`ADDNUM2` char(36) COLLATE utf8mb4_bin DEFAULT NULL,'.
            '`HOUSETYPE` int NOT NULL DEFAULT 0,'.
            '`ADDTYPE1` int NOT NULL DEFAULT 0,'.
            '`ADDTYPE2` int NOT NULL DEFAULT 0,'.
            '`OKATO` char(11) COLLATE ascii_bin DEFAULT NULL,'.
            '`OKTMO` char(11) COLLATE ascii_bin DEFAULT NULL,'.
            '`POSTALCODE` char(6) COLLATE ascii_bin DEFAULT NULL'.
            ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;');
        foreach ( GAR::Regions as $region ){
            $table_house = 'tmp'.$region.'_house';
            echo "$region ";
            $this->db->fastQuery('INSERT INTO tmpxx_house ( SELECT * FROM ' . $table_house . ')');
        }
        echo "ID:index ";
        $this->db->fastQuery('ALTER TABLE tmpxx_house ADD PRIMARY KEY (`id`);');
        echo "\n";
        //
        $this->setProcessedCount($this->proc)->setResult(0);
    }
}
new imp_o_merge_house();
