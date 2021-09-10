<?php
namespace Area;
require_once 'area/iconf.php';
require_once 'area/kernel/fw.php';
require_once 'area/kernel/validate.php';
require_once 'area/kernel/db_mysqli.php';
require_once 'area/kernel/settings.php';
require_once 'area/kernel/worker.php';
class Kernel {
    /* @var float     $timeStart */ static public $timeStart;
    /* @var DB_MySQLi $DB        */ static public $DB;
}
Kernel::$timeStart = microtime(true);
Kernel::$DB = new DB_MySQLi(GC_DB_MySQLi_Host,GC_DB_MySQLi_User,GC_DB_MySQLi_Pass,GC_DB_MySQLi_Base,GC_DB_Debug);
if ( !Kernel::$DB->install() )
    die('DBInstall failed'."\n");
foreach ( GC_DB_MySQLi_Init as $e )
    Kernel::$DB->fastQuery($e);
