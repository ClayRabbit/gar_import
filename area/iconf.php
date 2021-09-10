<?php
// Kernel Area V9 // (based-on)
const GC_DB_Debug                  = false;
const GC_DB_MySQLi_Host            = '10.105.3.21';
const GC_DB_MySQLi_User            = 'sql';
const GC_DB_MySQLi_Pass            = 'sql';
const GC_DB_MySQLi_Base            = 'gar';
const GC_DB_MySQLi_Init            = ['SET NAMES utf8mb4'];
const GC_DefaultTimezone_Project   = 'UTC'; // Strongly recommended UTC
const GC_Curl_DefaultUserAgent     = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.142 Area/9.2006';
const GC_Curl_IDNAutoConvert       = false;
const GC_Worker_PortOffset         = 35000;
error_reporting(E_ALL);
date_default_timezone_set(GC_DefaultTimezone_Project);
setlocale(LC_ALL,'rus');     // setlocale(LC_ALL,'ru_RU.UTF-8');
setlocale(LC_NUMERIC,'enu'); // setlocale(LC_NUMERIC,'en_US.UTF-8');
