<?php
chdir(__DIR__);
require_once 'area/area.php';
require_once 'gar.php';
require_once 'garfn.php';
class imp_a_check extends \Area\Worker {
    public $workerID    = 511;
    public $workerGroup = 000;
    private $proc = 0;
    public function run(){
        GARFn::lockWrite($this->workerID);
        if ( file_exists(GARFn::bugFile) )
            @unlink(GARFn::bugFile);
        if ( !($zipFile=GARFn::ZIPFile()) ){
            GARFn::lockWrite($this->workerID,__LINE__,'Wrong path or GAR ZIP archive not found in '.GAR::ScanDir);
            return $this->setResult(__LINE__);
        }
        if (GAR::isRemoteZip()) $Z = new \UnzipHttpWrapper();
        else
        $Z = new \ZipArchive();
        $Z->open($zipFile);
        $ZRegions = [];
        for ( $i = 0 ; $i < $Z->numFiles ; $i++ ){
            if ( preg_match('#^(?<region>\d\d)/as_addr_obj.*\.xml$#i',$Z->getNameIndex($i),$M) )
                $ZRegions[$M['region']] = true;
        }
        $Z->close();
        foreach ( GAR::Regions as $region ){
            if ( !isset($ZRegions[$region]) ){
                GARFn::lockWrite($this->workerID,__LINE__,'Region '.$region.' not found in ZIP archive');
                return $this->setResult(__LINE__);
            }
        }
        // -> Есть ZIP архив с содержимым похожим на ГАР //
        $this->setProcessedCount($this->proc)->setResult(0);
    }
}
new imp_a_check();
