<?php

/**
 * Drop the table created if the module is uninstalled.
 *
 * @return bool
 *
 * @author Ali, Muamar
 */
return (
    !Db::getInstance()->Execute('DROP TABLE '. _DB_PREFIX_ .'clients;')
) ? false : true;