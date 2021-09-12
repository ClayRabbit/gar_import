<?php
chdir(__DIR__);
require_once 'area/area.php';
require_once 'gar.php';
require_once 'garfn.php';
class imp_n_merge_addr extends \Area\Worker {
    public $workerID    = 524;
    public $workerGroup = 000;
    private $proc = 0;
    public function run(){
        if ( $tmp = GARFn::lockError() ) { $this->log($tmp); return $this->setResult(__LINE__); }
        GARFn::lockWrite($this->workerID);
        //
        $this->db->fastQuery('DROP TABLE IF EXISTS tmpxx_addr');
        $this->db->fastQuery(
            'CREATE TABLE tmpxx_addr ( '.
            '`id` int NOT NULL,'.
            '`owner_adm` int NOT NULL DEFAULT 0 COMMENT \'Ранее owner\','.
            '`owner_mun` int NOT NULL DEFAULT 0,'.
            '`level` int NOT NULL DEFAULT 0,'.
            '`OBJECTID` decimal(31,0) NOT NULL DEFAULT 0,'.
            '`OBJECTGUID` char(36) COLLATE ascii_bin NOT NULL COMMENT \'Ранее AOGUID, идентификатор адресного объекта\','.
            '`NAME` varchar(255) DEFAULT NULL COMMENT \'Ранее OFFNAME\','.
            '`TYPENAME` varchar(31) DEFAULT NULL COMMENT \'Ранее SHORTNAME\','.
            '`OKATO` char(11) COLLATE ascii_bin DEFAULT NULL,'.
            '`OKTMO` char(11) COLLATE ascii_bin DEFAULT NULL,'.
            '`KLADR` char(31) COLLATE ascii_bin DEFAULT NULL'.
            ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;');
        foreach ( GAR::Regions as $region ){
            $table_addr = 'tmp'.$region.'_addr';
            echo "$region ";
            $this->db->fastQuery('INSERT INTO tmpxx_addr ( SELECT * FROM ' . $table_addr . ')');
        }
        echo "ID:index ";
        $this->db->fastQuery('ALTER TABLE tmpxx_addr ADD PRIMARY KEY (`id`);');
        echo "\n";
        //
        $this->setProcessedCount($this->proc)->setResult(0);
    }
}
new imp_n_merge_addr();
