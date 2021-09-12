<?php
chdir(__DIR__);
require_once 'area/area.php';
require_once 'gar.php';
require_once 'garfn.php';
class imp_i_touch extends \Area\Worker {
    public $workerID    = 519;
    public $workerGroup = 000;
    private $proc = 0;
    public function run(){
        if ( $tmp = GARFn::lockError() ) { $this->log($tmp); return $this->setResult(__LINE__); }
        GARFn::lockWrite($this->workerID);
        foreach ( GAR::Regions as $region ) {
            $table_addr = 'tmp'.$region.'_addr';
            $table_house = 'tmp'.$region.'_house';
            $this->log('Region '.$region);
            $this->db->fastQuery('ALTER TABLE ' . $table_addr . ' DROP INDEX `touch_adm`');
            $this->db->fastQuery('ALTER TABLE ' . $table_addr . ' DROP INDEX `touch_mun`');
            $this->db->fastQuery('ALTER TABLE ' . $table_addr . ' ADD INDEX(`touch_adm`)');
            $this->db->fastQuery('ALTER TABLE ' . $table_addr . ' ADD INDEX(`touch_mun`)');
            // Маркируем все корневые элементы (субъекты) достижимыми
            $this->db->fastQuery('UPDATE ' . $table_addr . ' SET touch_adm = 1 , touch_mun = 1 WHERE level = 1');
            // Проходим по adm
            $nTotalRecords = $this->db->mkCount($table_addr);
            $nTouchAdm = $this->db->mkCount($table_addr,['touch_adm'=>1]);
            while ( true ){
                $this->db->fastQuery(
                    'UPDATE ' . $table_addr . ' SET touch_adm = 1 WHERE owner_adm IN ' .
                    '( SELECT T.id FROM ( SELECT id FROM ' . $table_addr . ' WHERE touch_adm = 1 ) AS T )' // Mysql #1093 error fix
                );
                $cTouchAdm = $this->db->mkCount($table_addr,['touch_adm'=>1]);
                $this->log('adm: '.fw::f2($cTouchAdm/$nTotalRecords*100));
                if ( $cTouchAdm == $nTouchAdm )
                    break;
                $nTouchAdm = $cTouchAdm;
            }
            // Проходим по mun
            $nTotalRecords = $this->db->mkCount($table_addr);
            $nTouchMun = $this->db->mkCount($table_addr,['touch_mun'=>1]);
            while ( true ){
                $this->db->fastQuery(
                    'UPDATE ' . $table_addr . ' SET touch_mun = 1 WHERE owner_mun IN ' .
                    '( SELECT T.id FROM ( SELECT id FROM ' . $table_addr . ' WHERE touch_mun = 1 ) AS T )' // Mysql #1093 error fix
                );
                $cTouchMun = $this->db->mkCount($table_addr,['touch_mun'=>1]);
                $this->log('mun: '.fw::f2($cTouchMun/$nTotalRecords*100));
                if ( $cTouchMun == $nTouchMun )
                    break;
                $nTouchMun = $cTouchMun;
            }
            // Bug: если touch_adm = 0 и touch_mun = 0, то элемент недостижим -> логируем (сообщаем в ИФНС); удаляем из своей БД, т.к. запись бесполезна
            foreach ( $this->db->mkSelectFilter($table_addr,['touch_adm'=>0,'touch_mun'=>0]) as $e ){
                GARFn::bug($table_addr.': недостижимый элемент',serialize($e));
                $this->db->mkDelete($table_addr,'id',$e['id']);
                $this->db->mkDelete($table_house,'owner_adm',$e['id']);
                $this->db->mkDelete($table_house,'owner_mun',$e['id']);
            }
        }
        // -> Проверили иерархическую достижимость каждого элемента и удалили лишнее
        $this->setProcessedCount($this->proc)->setResult(0);
    }
}
new imp_i_touch();
