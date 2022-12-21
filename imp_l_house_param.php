<?php
chdir(__DIR__);
require_once 'area/area.php';
require_once 'gar.php';
require_once 'garfn.php';
class imp_l_house_param extends \Area\Worker {
    public $workerID    = 522;
    public $workerGroup = 000;
    private $proc = 0;
    public function run(){
        if ( $tmp = GARFn::lockError() ) { $this->log($tmp); return $this->setResult(__LINE__); }
        GARFn::lockWrite($this->workerID);
        //
        $list = [];
        if (GAR::isRemoteZip()) $Z = new \UnzipHttpWrapper();
        else
        $Z = new \ZipArchive();
        $Z->open($ZIPFile=GARFn::ZIPFile());
        foreach ( GAR::Regions as $region ){
            $foundIndex = false;
            for ( $i = 0 ; $i < $Z->numFiles ; $i++ ){
                if ( preg_match('#^'.$region.'/(?<name>AS_HOUSES_PARAMS_[\d]{8}_.*\.XML)$#i',$Z->getNameIndex($i),$M) ){
                    $foundIndex = $i;
                    break;
                }
            }
            if ( false === $foundIndex ){
                GARFn::lockWrite($this->workerID,__LINE__,'AS_HOUSES_PARAMS file not found for region '.$region);
                return $this->setResult(__LINE__);
            }
            $list[] = [ $region , $Z->getNameIndex($foundIndex) , $M['name'] ?? false ];
        }
        $Z->close();
        //
        $today = date('Y-m-d');
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
            if ( !($i=strpos($raw,'<PARAM ')) ) {
                GARFn::lockWrite($this->workerID,__LINE__,'PARAM tag not found for '.$archive_filename);
                return $this->setResult(__LINE__);
            }
            $raw = substr($raw,$i);
            $nUpdate = 0;
            while(true){
                $raw .= ( $app = fread($H,GARFn::ReadSize) );
                $HRead += GARFn::ReadSize;
                while ( $HRead > $Hsz50 ) { echo '.'; $HRead -= $Hsz50; }
                preg_match_all('#<PARAM ([^<>]+)/>#Ui',$raw,$M,PREG_SET_ORDER);
                foreach ( $M as $data ) {
                    $raw = str_replace($data[0],'',$raw);
                    $ENDDATE = GARFn::rawXML_extract( $data[0] , 'ENDDATE' );
                    if ( $ENDDATE <= $today )
                        continue; // Устарел
                    if ( !$VALUE = GARFn::rawXML_extract( $data[0] , 'VALUE' ) )
                        continue; // Нет значения
                    switch ( intval(GARFn::rawXML_extract( $data[0] , 'TYPEID' )) ) {
                        case 5:
                            $this->db->mkUpdate($table_house,[
                                'POSTALCODE' => $VALUE
                            ],'OBJECTID',GARFn::rawXML_extract($data[0],'OBJECTID'));
                            $nUpdate++;
                            break;
                        case 6: // ОКАТО
                            $this->db->mkUpdate($table_house,[
                                'OKATO' => $VALUE
                            ],'OBJECTID',GARFn::rawXML_extract($data[0],'OBJECTID'));
                            $nUpdate++;
                            break;
                        case 7: // ОКТМО
                            $this->db->mkUpdate($table_house,[
                                'OKTMO' => $VALUE
                            ],'OBJECTID',GARFn::rawXML_extract($data[0],'OBJECTID'));
                            $nUpdate++;
                            break;
                    }
                }
                if ( !$app )
                    break;
            }
            fclose($H);
            $this->db->mkUpdate('gar_info_files',[
                'tf' => microtime(true),
                'n' => $nUpdate,
                'sz' => $Hsz
            ],'id',$HInfo);
            unlink($filename);
            echo "\n";
        }
        // -> В таблице tmp**_house заполнили OKATO, OKTMO на основе файлов AS_HOUSES_PARAMS_yyyymmdd_*.xml  //
        $this->setProcessedCount($this->proc)->setResult(0);
    }
}
new imp_l_house_param();
