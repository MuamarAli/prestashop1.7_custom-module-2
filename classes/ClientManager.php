<?php

include ('ClientSqlQueries.php');

/**
 * Class ClientManager
 *
 * @author Ali, Muamar
 */
class ClientManager
{
    /**
     * @var null
     */
    private $output = null;

    /**
     * @var ClientSqlQueries
     */
    private $clientSqlQuery;

    /**
     * manager constructor.
     *
     * @param ClientSqlQueries $clientSqlQuery
     *
     * @author Ali, Muamar
     */
    public function __construct(ClientSqlQueries $clientSqlQuery)
    {
        $this->clientSqlQuery = $clientSqlQuery;
    }

    /**
     * Uploading of image.
     *
     * @author Ali, Muamar
     *
     * @return bool|mixed|string
     */
    public function uploadImage()
    {
        foreach ($_FILES as $file) {
            $file_object = array();
            $file_object['name'] = $file['name'];
            $file_object['type'] = $file['type'];
            $file_object['tmp_name'] = $file['tmp_name'];
            $file_object['error'] = $file['error'];
            $file_object['size'] = $file['size'];

            $file_name = $file['name'];
            $file_name = md5($file_name) . '.' . substr($file_name, strrpos($file_name, '.') + 1);

            if (
                !move_uploaded_file($file_object['tmp_name'], _PS_CAT_IMG_DIR_ . $file_name)
            ) {
                return $this
                    ->displayError(
                        $this->l('An error occurred while attempting to upload the file.')
                    );
            }
        }

        return $file_name;
    }

    /**
     * Inserting new client to database.
     *
     * @param string $name | client name
     *
     * @throws PrestaShopDatabaseException
     * @author Ali, Muamar
     *
     * @return bool
     */
    public function insertProcess(string $name)
    {
        return $this->insertSql($name, $this->uploadImage());
    }

    /**
     * Updating the client details.
     *
     * @param string $name | client name
     * @param int $clientId | client id
     * @param $image
     *
     * @throws PrestaShopDatabaseException
     * @author Ali, Muamar
     *
     * @return null|string
     */
    public function updateProcess(string $name, int $clientId, $image)
    {
        $getClient = $this->clientSqlQuery->getClient($clientId);

        if (is_uploaded_file($image)) {
            $this->output .= $this->deleteImage($getClient[0]['image']);
            $this->output .= $this->updateSql($name, $clientId, $this->uploadImage());
        } else {
            $this->output .= $this->updateSql($name, $clientId, $getClient[0]['image']);
        }

        return $this->output;
    }

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
        return $this->clientSqlQuery->getAll();
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
        return $this->clientSqlQuery->isClientExist($clientName);
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
        return $this->clientSqlQuery->getClient($clientId);
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
        return $this->clientSqlQuery->insertSql($name, $file_name);
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
        return $this->clientSqlQuery->updateSql($name, $id, $image);
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
        return $this->clientSqlQuery->deleteSql($id);
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
        return $this->clientSqlQuery->deleteImage($imageName);
    }

    /**
     * Delete a client.
     *
     * @param int $id | id of the client.
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @author Ali, Muamar
     *
     * @return $this
     */
    public function deleteClient(int $id)
    {
        $getClient = $this->getClient($id);
        $this->deleteSql($id);
        $this->deleteImage($getClient[0]['image']);

        return $this;
    }
}