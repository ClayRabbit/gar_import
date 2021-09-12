@echo off

del export\gar_sm.sql
del export\gar_sm.sql.gz
Z:\WS\modules\database\MySQL-8.0\bin\mysqldump.exe --user sql -psql -h 10.105.3.21 --no-tablespaces gar gar_addr gar_info_files > export\gar_sm.sql
gzip export\gar_sm.sql

del export\gar_md.sql
del export\gar_md.sql.gz
Z:\WS\modules\database\MySQL-8.0\bin\mysqldump.exe --user sql -psql -h 10.105.3.21 --no-tablespaces gar gar_addr gar_house gar_info_files > export\gar_md.sql
gzip export\gar_md.sql
