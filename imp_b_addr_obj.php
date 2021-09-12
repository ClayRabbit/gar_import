<?php
chdir(__DIR__);
require_once 'area/area.php';
require_once 'gar.php';
require_once 'garfn.php';
class imp_b_addr_obj extends \Area\Worker {
    public $workerID    = 512;
    public $workerGroup = 000;
    private $proc = 0;
    public function run(){
        if ( $tmp = GARFn::lockError() ) { $this->log($tmp); return $this->setResult(__LINE__); }
        GARFn::lockWrite($this->workerID);
        $AInc = 1;
        //
        foreach ( GAR::Regions as $region ) {
            $table_addr = 'tmp'.$region.'_addr';
            $this->db->fastQuery('DROP TABLE IF EXISTS '.$table_addr);
            $this->db->fastQuery(
                'CREATE TABLE '.$table_addr.' ( '.
                '`id` int NOT NULL,'.
                '`owner_adm` int NOT NULL DEFAULT 0 COMMENT \'Ранее owner\','. // административно-территориальное устройство — для упорядоченного осуществления функций государственного управления (ОКАТО)
                '`owner_mun` int NOT NULL DEFAULT 0,'. // муниципальное устройство — для организации местного самоуправления (ОКТМО)
                '`touch_adm` int NOT NULL DEFAULT 0,'.
                '`touch_mun` int NOT NULL DEFAULT 0,'.
                '`level` int NOT NULL DEFAULT 0,'.
                '`OBJECTID` decimal(31,0) NOT NULL DEFAULT 0,'.
                '`OBJECTGUID` char(36) COLLATE ascii_bin NOT NULL COMMENT \'Ранее AOGUID, идентификатор адресного объекта\','.
                '`NAME` varchar(255) DEFAULT NULL COMMENT \'Ранее OFFNAME\','.
                '`TYPENAME` varchar(31) DEFAULT NULL COMMENT \'Ранее SHORTNAME\','.
                '`OKATO` char(11) COLLATE ascii_bin DEFAULT NULL,'.
                '`OKTMO` char(11) COLLATE ascii_bin DEFAULT NULL,'.
                '`KLADR` char(31) COLLATE ascii_bin DEFAULT NULL'.
                ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;');
            $this->db->fastQuery('ALTER TABLE '.$table_addr.' ADD PRIMARY KEY (`id`);');
         // $this->db->fastQuery('ALTER TABLE '.$table_addr.' MODIFY `id` int NOT NULL AUTO_INCREMENT;'); // Не делать AUTO_INCREMENT, т.к. будет слияние таблиц по регионам в одну
        }
        //
        $list = [];
        $Z = new \ZipArchive();
        $Z->open($ZIPFile=GARFn::ZIPFile());
        foreach ( GAR::Regions as $region ){
            $foundIndex = false;
            for ( $i = 0 ; $i < $Z->numFiles ; $i++ ){
                if ( preg_match('#^'.$region.'/(?<name>AS_ADDR_OBJ_[\d]{8}_.*\.XML)$#i',$Z->getNameIndex($i),$M) ){
                    $foundIndex = $i;
                    break;
                }
            }
            if ( false === $foundIndex ){
                GARFn::lockWrite($this->workerID,__LINE__,'AS_ADDR_OBJ file not found for region '.$region);
                return $this->setResult(__LINE__);
            }
            $list[] = [ $region , $Z->getNameIndex($foundIndex) , $M['name'] ?? false ];
        }
        $Z->close();
        //
        foreach ( $list as list($region,$archive_filename,$filename) ){
            $table_addr = 'tmp'.$region.'_addr';
            if ( !$filename ){
                GARFn::lockWrite($this->workerID,__LINE__,'Internal error for '.$archive_filename);
                return $this->setResult(__LINE__);
            }
            if ( file_exists($filename) )
                unlink($filename);
            $this->log('Extract: '.$archive_filename.' ...');
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
            if ( !($i=strpos($raw,'<OBJECT ')) ) {
                GARFn::lockWrite($this->workerID,__LINE__,'OBJECT tag not found for '.$archive_filename);
                return $this->setResult(__LINE__);
            }
            $raw = substr($raw,$i);
            $nIn = 0;
            while(true){
                $raw .= ( $app = fread($H,GARFn::ReadSize) );
                $HRead += GARFn::ReadSize;
                while ( $HRead > $Hsz50 ) { echo '.'; $HRead -= $Hsz50; }
                preg_match_all('#<OBJECT ([^<>]+)/>#Ui',$raw,$M,PREG_SET_ORDER);
                foreach ( $M as $data ) {
                    $raw = str_replace($data[0],'',$raw);
                    if ( 1 != GARFn::rawXML_extract( $data[0] , 'ISACTUAL' ) )
                        continue;
                    if ( 1 != GARFn::rawXML_extract( $data[0] , 'ISACTIVE' ) )
                        continue;
                    $this->db->mkInsert($table_addr,[
                        'id' => $AInc++,
                        'level' => intval(GARFn::rawXML_extract($data[0],'LEVEL')),
                        'OBJECTID' => GARFn::rawXML_extract($data[0],'OBJECTID'),
                        'OBJECTGUID' => GARFn::rawXML_extract($data[0],'OBJECTGUID'),
                        'NAME' => trim(GARFn::rawXML_extract($data[0],'NAME')),
                        'TYPENAME' => trim(GARFn::rawXML_extract($data[0],'TYPENAME')),
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
        // -> В таблице tmp??_addr содержимое файлов **/AS_ADDR_OBJ_yyyymmdd_*.xml  //
        $this->setProcessedCount($this->proc)->setResult(0);
    }
}
new imp_b_addr_obj();
