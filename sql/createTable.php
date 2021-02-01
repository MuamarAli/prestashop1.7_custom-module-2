<?php

/**
 * Automatically create table if not exist in the database.
 *
 * @author Ali, Muamar
 *
 * @return bool
 */
$sql = '
           CREATE TABLE IF NOT EXISTS '. _DB_PREFIX_ .'clients (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `image` longtext NOT NULL,
            PRIMARY KEY (`id`)
            )ENGINE=' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        ';

return (!Db::getInstance()->Execute($sql)) ? false : true;