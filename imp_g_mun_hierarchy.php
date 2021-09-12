<?php
chdir(__DIR__);
require_once 'area/area.php';
require_once 'gar.php';
require_once 'garfn.php';
class imp_g_mun_hierarchy extends \Area\Worker {
    public $workerID    = 517;
    public $workerGroup = 000;
    private $proc = 0;
    public function run(){
        if ( $tmp = GARFn::lockError() ) { $this->log($tmp); return $this->setResult(__LINE__); }
        GARFn::lockWrite($this->workerID);
        //
        $list = [];
        $Z = new \ZipArchive();
        $Z->open($ZIPFile=GARFn::ZIPFile());
        foreach ( GAR::Regions as $region ){
            $foundIndex = false;
            for ( $i = 0 ; $i < $Z->numFiles ; $i++ ){
                if ( preg_match('#^'.$region.'/(?<name>AS_MUN_HIERARCHY_[\d]{8}_.*\.XML)$#i',$Z->getNameIndex($i),$M) ){
                    $foundIndex = $i;
                    break;
                }
            }
            if ( false === $foundIndex ){
                GARFn::lockWrite($this->workerID,__LINE__,'AS_MUN_HIERARCHY file not found for region '.$region);
                return $this->setResult(__LINE__);
            }
            $list[] = [ $region , $Z->getNameIndex($foundIndex) , $M['name'] ?? false ];
        }
        $Z->close();
        //
        foreach ( $list as list($region,$archive_filename,$filename) ){
            $table_addr = 'tmp'.$region.'_addr';
            $table_house = 'tmp'.$region.'_house';
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
            $Hsz50 = round($Hsz/50); if ( $Hsz50 < 1 ) $Hsz50 = 1;
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
            if ( !($i=strpos($raw,'<ITEM ')) ) {
                GARFn::lockWrite($this->workerID,__LINE__,'ITEM tag not found for '.$archive_filename);
                return $this->setResult(__LINE__);
            }
            $raw = substr($raw,$i);
            $nChanges = 0;
            while(true){
                $raw .= ( $app = fread($H,GARFn::ReadSize) );
                $HRead += GARFn::ReadSize;
                while ( $HRead > $Hsz50 ) { echo '.'; $HRead -= $Hsz50; }
                preg_match_all('#<ITEM ([^<>]+)/>#Ui',$raw,$M,PREG_SET_ORDER);
                foreach ( $M as $data ) {
                    $raw = str_replace($data[0],'',$raw);
                    if ( 1 != GARFn::rawXML_extract( $data[0] , 'ISACTIVE' ) )
                        continue;
                    $_PARENTOBJID = GARFn::rawXML_extract($data[0],'PARENTOBJID');
                    if ( 0 == $_PARENTOBJID )
                        continue; // level='1' (Субъекты)
                    if ( !$parent = $this->db->mkItem($table_addr,'id',['OBJECTID'=>$_PARENTOBJID]) )
                        continue;
                    $OBJECTID = GARFn::rawXML_extract($data[0],'OBJECTID');
                    if ( $obj = $this->db->mkItem($table_house,'id',['OBJECTID'=>$OBJECTID]) ){
                        $this->db->mkUpdate($table_house,['owner_mun'=>$parent],'id',$obj);
                        $nChanges++;
                        continue;
                    }
                    if ( $obj = $this->db->mkItem($table_addr,'id',['OBJECTID'=>$OBJECTID]) ){
                        $this->db->mkUpdate($table_addr,['owner_mun'=>$parent],'id',$obj);
                        $nChanges++;
                        continue;
                    }
                }
                if ( !$app )
                    break;
            }
            fclose($H);
            $this->db->mkUpdate('gar_info_files',[
                'tf' => microtime(true),
                'n' => $nChanges,
                'sz' => $Hsz
            ],'id',$HInfo);
            unlink($filename);
            echo "\n";
        }
        // -> В таблицах tmp**_addr , tmp**_house заполнен owner_mun на основе **/AS_MUN_HIERARCHY_yyyymmdd_*.xml  //
        $this->setProcessedCount($this->proc)->setResult(0);
    }
}
new imp_g_mun_hierarchy();
