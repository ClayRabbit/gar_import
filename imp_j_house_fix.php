<?php
chdir(__DIR__);
require_once 'area/area.php';
require_once 'gar.php';
require_once 'garfn.php';
class imp_j_house_fix extends \Area\Worker {
    public $workerID    = 520;
    public $workerGroup = 000;
    private $proc = 0;
    public function run(){
        if ( $tmp = GARFn::lockError() ) { $this->log($tmp); return $this->setResult(__LINE__); }
        GARFn::lockWrite($this->workerID);
        foreach ( GAR::Regions as $region ){
            $table_house = 'tmp'.$region.'_house';
            echo "$region ";
            // Недоступные по обеим иерархии:
            foreach ( $this->db->getRecords('SELECT * FROM ' . $table_house . ' WHERE owner_adm = 0 AND owner_mun = 0') as $e ) {
                GARFn::bug($table_house.': владелец элемента (both owners) не определён',serialize($e));
                $this->db->mkDelete($table_house,'id',$e['id']);
            }
            // Недоступные по административно-территориальной иерархии:
            foreach ( $this->db->getRecords('SELECT * FROM ' . $table_house . ' WHERE owner_adm = 0') as $e ) {
                GARFn::bug($table_house.': владелец элемента (owner_adm) не определён',serialize($e));
                $this->db->mkDelete($table_house,'id',$e['id']);
            }
            // Недоступные по муниципальной иерархии:
            foreach ( $this->db->getRecords('SELECT * FROM ' . $table_house . ' WHERE owner_mun = 0') as $e ) {
                GARFn::bug($table_house.': владелец элемента (owner_mun) не определён',serialize($e));
                $this->db->mkDelete($table_house,'id',$e['id']);
            }
            //TODO Под вопросом: owner_adm != owner_mun , то... удалить "проблемные"?
            /*
            foreach ( $this->db->getRecords(
                'SELECT * FROM ' . $table_house . ' WHERE ' .
                'owner_adm <> owner_mun'
            ) as $e ) {
                GARFn::bug('Поля owner_adm и owner_mun в ' . $table_house . ' не совпадают',serialize($e));
                $this->db->mkDelete($table_house,'id',$e['id']);
            }
            */
        }
        echo "\n";
        //
        $this->setProcessedCount($this->proc)->setResult(0);
    }
}
new imp_j_house_fix();
