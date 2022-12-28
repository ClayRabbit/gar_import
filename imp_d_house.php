<?php
chdir(__DIR__);
require_once 'area/area.php';
require_once 'gar.php';
require_once 'garfn.php';
class imp_d_house extends \Area\Worker {
    public $workerID    = 514;
    public $workerGroup = 000;
    private $proc = 0;
    public function run(){
        if ( $tmp = GARFn::lockError() ) { $this->log($tmp); return $this->setResult(__LINE__); }
        GARFn::lockWrite($this->workerID);
        $AInc = 1;
        //
        foreach ( GAR::Regions as $region ) {
            $table_house = 'tmp'.$region.'_house';
            $this->db->fastQuery('DROP TABLE IF EXISTS '.$table_house);
            $this->db->fastQuery(
                'CREATE TABLE ' . $table_house . ' ( '.
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
            $this->db->fastQuery('ALTER TABLE ' . $table_house . ' ADD PRIMARY KEY (`id`);');
         // $this->db->fastQuery('ALTER TABLE ' . $table_house . ' MODIFY `id` int NOT NULL AUTO_INCREMENT;'); // Не делать AUTO_INCREMENT, т.к. будет слияние таблиц по регионам в одну
        }
        //
        $list = [];
        if (GAR::isRemoteZip()) $Z = new \UnzipHttpWrapper();
        else
        $Z = new \ZipArchive();
        $Z->open($ZIPFile=GARFn::ZIPFile());
        foreach ( GAR::Regions as $region ){
            $foundIndex = false;
            for ( $i = 0 ; $i < $Z->numFiles ; $i++ ){
                if ( preg_match('#^'.$region.'/(?<name>AS_HOUSES_[\d]{8}_.*\.XML)$#i',$Z->getNameIndex($i),$M) ){
                    $foundIndex = $i;
                    break;
                }
            }
            if ( false === $foundIndex ){
                GARFn::lockWrite($this->workerID,__LINE__,'AS_HOUSES file not found for region '.$region);
                return $this->setResult(__LINE__);
            }
            $list[] = [ $region , $Z->getNameIndex($foundIndex) , $M['name'] ?? false ];
        }
        $Z->close();
        //
        foreach ( $list as list($region,$archive_filename,$filename) ){
            $table_house = 'tmp'.$region.'_house';
            if ( !$filename ){
                GARFn::lockWrite($this->workerID,__LINE__,'Internal error for '.$archive_filename);
                return $this->setResult(__LINE__);
            }
            if ( file_exists($filename) )
                unlink($filename);
            $this->log('Extract: '.$archive_filename.' ...');
            if (GAR::isRemoteZip()) $Z->extract($archive_filename);
            else
            exec('7za.exe e "'.$ZIPFile.'" "'.$archive_filename.'"',$outputNull);
            if ( !file_exists($filename) ) {
                GARFn::lockWrite($this->workerID,__LINE__,'Extract error for '.$archive_filename);
                return $this->setResult(__LINE__);
            }
            $Hsz = filesize($filename);
            $Hsz50 = round($Hsz/50);
            $this->log('Import '.fw::f2($Hsz/GARFn::MB).'Mb ...');
            if ( !( $H = fopen($filename,'r') ) ){
                GARFn::lockWrite($this->workerID,__LINE__,'Can\'t read '.$filename);
                return $this->setResult(__LINE__);
            }
            if ( !$HInfo = $this->db->mkInsert('gar_info_files',[
                'ts' => microtime(true),
                'filename' => $archive_filename
            ]) ){
                GARFn::lockWrite($this->workerID,__LINE__,'Internal DB error (table gar_info_files)');
                return $this->setResult(__LINE__);
            }
            $raw = fread($H,$HRead=GARFn::ReadSize);
            if ( !($i=strpos($raw,'<HOUSE ')) ) {
                GARFn::lockWrite($this->workerID,__LINE__,'HOUSE tag not found for '.$archive_filename);
                return $this->setResult(__LINE__);
            }
            $raw = substr($raw,$i);
            $nIn = 0;
            while(true){
                $raw .= ( $app = fread($H,GARFn::ReadSize) );
                $HRead += GARFn::ReadSize;
                while ( $HRead > $Hsz50 ) { echo '.'; $HRead -= $Hsz50; }
                preg_match_all('#<HOUSE ([^<>]+)/>#Ui',$raw,$M,PREG_SET_ORDER);
                foreach ( $M as $data ) {
                    $raw = str_replace($data[0],'',$raw);
                    if ( 1 != GARFn::rawXML_extract( $data[0] , 'ISACTUAL' ) )
                        continue;
                    if ( 1 != GARFn::rawXML_extract( $data[0] , 'ISACTIVE' ) )
                        continue;
                    $HOUSETYPE = intval(GARFn::rawXML_extract( $data[0] , 'HOUSETYPE' ));
                    if ( $HOUSETYPE && !in_array($HOUSETYPE,GAR::HouseTypes) )
                        continue;
                    $ADDTYPE1 = intval(GARFn::rawXML_extract( $data[0] , 'ADDTYPE1' ));
                    /*
                    if ( $ADDTYPE1 && !in_array($ADDTYPE1,GAR::HouseTypes) )
                        continue;
                    */
                    $ADDTYPE2 = intval(GARFn::rawXML_extract( $data[0] , 'ADDTYPE2' ));
                    /*
                    if ( $ADDTYPE2 && !in_array($ADDTYPE2,GAR::HouseTypes) )
                        continue;
                    */
                    $this->db->mkInsert($table_house,[
                        'id' => $AInc++,
                        'OBJECTID' => GARFn::rawXML_extract($data[0],'OBJECTID'),
                        'OBJECTGUID' => GARFn::rawXML_extract($data[0],'OBJECTGUID'),
                        'HOUSENUM' => GARFn::rawXML_extract($data[0],'HOUSENUM'),
                        'ADDNUM1' => GARFn::rawXML_extract($data[0],'ADDNUM1'),
                        'ADDNUM2' => GARFn::rawXML_extract($data[0],'ADDNUM2'),
                        'HOUSETYPE' => $HOUSETYPE,
                        'ADDTYPE1' => $ADDTYPE1,
                        'ADDTYPE2' => $ADDTYPE2,
                    ]);
                    $nIn++;
                }
                if ( !$app )
                    break;
            }
            fclose($H);
            $this->db->mkUpdate('gar_info_files',[
                'tf' => microtime(true),
                'n' => $nIn,
                'sz' => $Hsz
            ],'id',$HInfo);
            unlink($filename);
            echo "\n";
        }
        // -> В таблице tmp**_house содержимое файлов **/AS_HOUSES_yyyymmdd_*.xml  //
        $this->setProcessedCount($this->proc)->setResult(0);
    }
}
new imp_d_house();
