<?php

/**
 * Class ClientSqlQueries
 *
 * @author Ali, Muamar
 */
class ClientSqlQueries
{
    /**
     * Get all the clients in the database.
     *
     * @throws PrestaShopDatabaseException
     * @author Ali, Muamar
     *
     * @return array|false|mysqli_result|PDOStatement|resource|null
     */
    public function getAll()
    {
        return Db::getInstance()
            ->executeS(
                'SELECT * FROM '._DB_PREFIX_.'clients'
            );
    }

    /**
     * Check if the client name is already exist in database.
     *
     * @param string $clientName | name of the client.
     *
     * @throws PrestaShopDatabaseException
     * @author Ali, Muamar
     *
     * @return bool
     */
    public function isClientExist(string $clientName)
    {
        if (
        empty(
        Db::getInstance()
            ->executeS(
                'SELECT `name` FROM '._DB_PREFIX_.'clients WHERE `name` = "' . pSQL($clientName) .'"'
            )
        )
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Return single client.
     *
     * @param int $clientId | id the the current client.
     *
     * @throws PrestaShopDatabaseException
     * @author Ali, Muamar
     *
     * @return array|false|mysqli_result|PDOStatement|resource|null
     */
    public function getClient(int $clientId)
    {
        return Db::getInstance()
            ->executeS(
                'SELECT * FROM '._DB_PREFIX_.'clients WHERE id = ' . pSQL($clientId)
            );
    }

    /**
     * Insert clients in the database.
     *
     * @param string $name | the client name.
     * @param string $file_name | the image name.
     *
     * @throws PrestaShopDatabaseException
     * @author Ali, Muamar
     *
     * @return bool
     */
    public function insertSql(string $name, string $file_name)
    {
        return Db::getInstance()->insert(
            'clients', array(
                'name' => pSQL($name),
                'image' => pSQL($file_name),
            )
        );
    }

    /**
     * Update clients information.
     *
     * @param string $name | client name.
     * @param int $id | current if of the client
     * @param string|null $image | name of uploaded file.
     *
     * @author Ali, Muamar
     *
     * @return bool
     */
    public function updateSql(string $name, int $id, string $image = null)
    {
        return Db::getInstance()
            ->execute(
                'UPDATE ' ._DB_PREFIX_. 'clients SET `name` = "' . pSQL($name) . '",
                    `image` = "' . pSQL($image) . '" WHERE `id` =' . pSQL($id)
            );
    }

    /**
     * Delete client.
     *
     * @param int $id | current id of the client.
     *
     * @author Ali, Muamar
     *
     * @return bool
     */
    public function deleteSql(int $id)
    {
        return Db::getInstance()
            ->execute(
                'DELETE FROM '._DB_PREFIX_.'clients WHERE `id` =' . pSQL($id)
            );
    }

    /**
     * Removing the images in the /images directory.
     *
     * @param string $imageName | filename of the target to be deleted.
     *
     * @author Ali, Muamar
     *
     * @return bool
     */
    public function deleteImage(string $imageName)
    {
        return unlink(_PS_CAT_IMG_DIR_ . $imageName);
    }
}