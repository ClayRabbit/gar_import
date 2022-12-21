<?php
class GAR {
    const ScanDir = 'https://fias-file.nalog.ru/downloads/2022.12.20/gar_xml.zip';
    const Regions = [     '01','02','03','04','05','06','07','08','09',
                     '10','11','12','13','14','15','16','17','18','19',
                     '20','21','22','23','24','25','26','27','28','29',
                     '30','31','32','33','34','35','36','37','38','39',
                     '40','41','42','43','44','45','46','47','48','49',
                     '50','51','52','53','54','55','56','57','58','59',
                     '60','61','62','63','64','65','66','67','68','69',
                     '70','71','72','73','74','75','76','77','78','79',
                                    '83',          '86','87',     '89',
                          '91','92',                              '99'];
    const HouseTypes = [ 2 , 5 , 7 , 10 ];
    /* актуальные HouseTypes
     * 2 - Дом   5 - Здание   7 - Строение   10 - Корпус
     * 4 - Гараж   6 - Шахта   8 - Сооружение
     */
    static function isRemoteZip() {
        return preg_match('#https?://.*\.zip#i', GAR::ScanDir);
    }
}
/* Downloads:
 * 7za.exe  | https://www.7-zip.org/download.html
 * gzip.exe | http://gnuwin32.sourceforge.net/packages/gzip.htm
 */

class UnzipHttpWrapper {
    const UnzipHttp = '/root/unzip-http-master/unzip-http';
    private $url;
    private $files = [];
    public $numFiles = 0;

    public function open($url) {
        $this->files = [];
        if (!exec($this::UnzipHttp . ' '. $url, $out)) return;
        $this->url = $url;
        foreach ($out as $row) if (preg_match('#^(\d+) -> (\d+)\s+(.*)#', $row, $m)) {
            $this->files[$m[3]] = $m[2];
        }
        $this->numFiles = count($this->files);
    }
    public function getNameIndex($i) {
        return array_keys($this->files)[$i];
    }
    public function close() {
    }
    public function extract($file) {
        if (!isset($this->files[$file])) { echo $file; return false; }
        $cmd = $this::UnzipHttp . ' '. $this->url . ' ' . $file . '>' . basename($file);
        return exec($cmd);
    }
}
