# gar_import

PHP-скрипт для импорта данных ГАР БД ФИАС из XML (gar_xml.zip) в БД MySQL

Взят отсюда: https://habr.com/ru/post/577752/

Добавлена поддержка https://github.com/saulpw/unzip-http/ позволяющая обойтись без скачивания всего архива. (Не рекомендуется использовать ссылку на zip последней выгрузки, т.к. как показала практика, туда могут выгрузить обновленные данные и вы не сможете закончить импорт. Надежнее все-таки скачивать архив локально.)

Изменены настройки фильтрации домов, т.к. оригинальные настройки отфильтровывали почти все дома Санкт-Петербурга.
