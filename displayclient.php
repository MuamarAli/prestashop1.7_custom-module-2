<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

include ('classes/ClientManager.php');

/**
 * Class DisplayClient
 *
 * @author Ali, Muamar
 */
class DisplayClient extends Module
{
    /**
     * Length of the accepted input in description.
     */
    const DESCRIPTION_CHARACTER_LENGTH = 255;

    /**
     * Length of the accepted input in title.
     */
    const TITLE_CHARACTER_LENGTH = 50;

    /**
     * Used to combine multiple values to display one value.
     */
    private $output = null;

    /**
     * @var ClientManager
     */
    private $clientManager;

    /**
     * DisplayClient constructor.
     *
     * @param ClientManager $clientManager
     *
     * @author Ali, Muamar
     */
    public function __construct(ClientManager $clientManager)
    {
        $this->name = 'displayclient';
        $this->tab = 'others';
        $this->version = '1.0';
        $this->author = 'Muamar Ali';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = array('min' => '1.7.1', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Display client\'s');
        $this->description = $this->l('Allows to display the list of client\'s logo in the homepage.');

        $this->clientManager = $clientManager;
    }

    /**
     * Allow module to be install.
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @author Ali, Muamar
     *
     * @return bool
     */
    public function install()
    {
        $createTable = include_once ($this->getLocalPath().'sql/createTable.php');

        return (
            !parent::install() OR
            !$createTable OR
            !$this->registerHook('displayHome')
        ) ? false : true;
    }

    /**
     * Uninstall the module installed.
     *
     * @author Ali, Muamar
     *
     * @return bool
     */
    public function uninstall()
    {
        $dropTable = include_once ($this->getLocalPath().'sql/dropTable.php');

        Configuration::deleteByName('title');
        Configuration::deleteByName('description');

        return (!parent::uninstall() OR !$dropTable) ? false : true;
    }

    /**
     * This automatically display the template of list of clients in the homepage.
     *
     * @throws PrestaShopDatabaseException
     * @author Ali, Muamar
     *
     * @return string
     */
    public function hookDisplayHome()
    {
        $this->getValues();

        return $this->display(__FILE__, 'client.tpl');
    }

    /**
     * This allow user to configure some setting in the back office.ss
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @author Ali, Muamar
     *
     * @return string|null
     */
    public function getContent()
    {
        $this->getValues();
        $form = $this->renderAddForm();
        $id = (int)Tools::getValue('id');

        if (Tools::isSubmit('submit')) {
            if ($this->postValidation()) {
                $this->output .= $this->postProcess();
                $this->output .= $this->renderList();
            } else {
                $this->output .= $form;
            }
        } elseif (
            Tools::isSubmit('addClient') ||
            Tools::isSubmit('editClient')
        ) {
            $this->output = $form;
        } elseif (Tools::isSubmit('deleteClient')) {
            $this->deleteClient($id);
        } elseif (Tools::isSubmit('viewClient')) {
            $this->showClient($id);
        } elseif (Tools::isSubmit('submitText')) {
            if ($this->postValidation()) {
                Configuration::updateValue('title', (string)Tools::getValue('title'));
                Configuration::updateValue('description', (string)Tools::getValue('description'));
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminModules', true) .
                    '&conf=4&configure=' . $this->name
                );
            } else {
                $this->output .= $this->renderForm();
                $this->output .= $this->renderList();
            }
        } else {
            $this->output .= $this->renderForm();
            $this->output .= $this->renderList();
        }

        return $this->output;
    }

    /**
     * Check the validation of every fields.
     *
     * @throws PrestaShopDatabaseException
     * @author Ali, Muamar
     *
     * @return bool
     */
    public function postValidation()
    {
        $errors = array();

        if (Tools::isSubmit('submit')) {
            if (empty((string)Tools::getValue('name'))) {
                $errors[] = $this->l('Please type something in the client name field.');
            }

            if (Tools::strlen((string)Tools::getValue('name')) > self::TITLE_CHARACTER_LENGTH) {
                $errors[] = $this->l('The name must be at least 50 character only.');
            }

            if ((int)Tools::getValue('id')) {
                if (
                    (string)Tools::getValue('name') ==
                    $this->getClient((int)Tools::getValue('id'))[0]['name']
                ) {
                    $this->output = true;
                } else {
                    if ($this->isClientExist((string)Tools::getValue('name')) == true) {
                        $errors[] = $this->l('You have entered an name that already exist.');
                    }
                }

                if (is_uploaded_file($_FILES['image']['tmp_name'])) {
                    if ($error = ImageManager::validateUpload($_FILES['image'])) {
                        $errors[] = $this->l($error);
                    }
                }
            } else {
                if ($this->isClientExist((string)Tools::getValue('name')) == true) {
                    $errors[] = $this->l('You have entered an name that already exist.');
                }

                if (is_uploaded_file($_FILES['image']['tmp_name'])) {
                    if ($error = ImageManager::validateUpload($_FILES['image'])) {
                        $errors[] = $this->l($error);
                    }
                } else {
                    $errors[] = $this->l('Please upload image.');
                }
            }

        } elseif (Tools::isSubmit('submitText')) {
            if (empty((string)Tools::getValue('title'))) {
                $errors[] = $this->l('Please type something in the title field.');
            }

            if ((string)Tools::strlen(Tools::getValue('title')) > self::TITLE_CHARACTER_LENGTH) {
                $errors[] = $this->l('The title must be at least 50 character only.');
            }

            if (empty((string)Tools::getValue('description'))) {
                $errors[] = $this->l('Please type something in the description field.');
            }

            if ((string)Tools::strlen(Tools::getValue('description')) > self::DESCRIPTION_CHARACTER_LENGTH) {
                $errors[] = $this->l('The description must be at least 255 character only.');
            }
        }

        if (count($errors)) {
            $this->output = $this->displayError(implode('<br />', $errors));

            return false;
        }

        return true;
    }

    /**
     * Handles the post request method.
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @author Ali, Muamar
     *
     * @return bool|string|null
     */
    public function postProcess()
    {
        if (Tools::isSubmit('submit')) {
            $name = (string)Tools::getValue('name');

            if ($clientId = (int)Tools::getValue('id')) {
                $this->clientManager->updateProcess($name, $clientId, $_FILES['image']['tmp_name']);
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) .
                    '&conf=4&configure=' . $this->name);
            } else {
                $this->clientManager->insertProcess($name);
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) .
                    '&conf=3&configure=' . $this->name);
            }
        }

        return $this->output;
    }

    /**
     * Display form in the back office.
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @author Ali, Muamar
     *
     * @return string
     */
    public function renderAddForm()
    {
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Client Form'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Client name'),
                    'name' => 'name',
                    'required' => true
                ),
                array(
                    'type' => 'file_lang',
                    'label' => $this->l('Image'),
                    'name' => 'image',
                    'required' => true
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            )
        );

        $helper = $this->helperFormConfiguration('submit');
        $helper->show_cancel_button = true;
        $helper->fields_value['name'] = Configuration::get('name');

        if (Tools::isSubmit('id')) {
            $getClient = $this->getClient((int)Tools::getValue('id'));
            $fields_form[0]['form']['input'][] = array('type' => 'hidden', 'name' => 'id');
            $fields_form[0]['form']['image'] = $getClient[0]['image'];

            $has_picture = true;

            if ($has_picture) {
                $fields_form[0]['form']['input'][] = array('type' => 'hidden', 'name' => 'has_picture');
            }

            $helper->tpl_vars = array(
                'fields_value' => $this->getFormValues(),
                'image_baseurl' => _PS_BASE_URL_ . DIRECTORY_SEPARATOR . 'img' .
                    DIRECTORY_SEPARATOR . 'c' . DIRECTORY_SEPARATOR,
            );
        }

        return $helper->generateForm($fields_form);
    }

    /**
     * Render the form for displaying title and description in homepage.
     *
     * @author Ali, Muamar
     *
     * @return string
     */
    public function renderForm()
    {
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Client Form'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Title'),
                    'name' => 'title',
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Description'),
                    'name' => 'description',
                    'required' => true
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            )
        );

        $helper = $this->helperFormConfiguration('submitText');

        $helper->fields_value['title'] = Configuration::get('title');
        $helper->fields_value['description'] = Configuration::get('description');

        return $helper->generateForm($fields_form);
    }

    /**
     * The configuration for the form.
     *
     * @param string $nameAttribute | name of the button attribute.
     *
     * @author Ali, Muamar
     *
     * @return HelperForm
     */
    public function helperFormConfiguration(string $nameAttribute)
    {
        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = $nameAttribute;
        $helper->toolbar_btn = array(
            'save' =>
                array(
                    'desc' => $this->l('Save'),
                    'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                        '&token='.Tools::getAdminTokenLite('AdminModules'),
                )
        );

        return $helper;
    }

    /**
     * Get the values of current client in the update section.
     *
     * @author Ali, Muamar
     * @throws PrestaShopDatabaseException
     *
     * @return array
     */
    public function getFormValues()
    {
        $fields = array();

        $getClient = $this->getClient((int)Tools::getValue('id'));

        if (Tools::isSubmit('id')) {
            $fields['id'] = (int)Tools::getValue('id', $getClient[0]['id']);
        }

        $fields['has_picture'] = true;
        $fields['name'] = (string)Tools::getValue('name', $getClient[0]['name']);

        return $fields;
    }

    /**
     * Display list of client.
     *
     * @throws PrestaShopDatabaseException
     * @author Ali, Muamar
     *
     * @return string
     */
    public function renderList()
    {
        $this->getValues();

        return $this->display(__FILE__, 'views/templates/admin/list.tpl');
    }

    /**
     * Assigning values in the variable to be passed in the template.
     *
     * @throws PrestaShopDatabaseException
     * @author Ali, Muamar
     *
     * @return $this
     */
    public function getValues()
    {
        $this->context->smarty->assign(
            array(
                'clients' => $this->getAll(),
                'imgPath' => _PS_BASE_URL_ . DIRECTORY_SEPARATOR . 'img' .
                            DIRECTORY_SEPARATOR . 'c' . DIRECTORY_SEPARATOR,
                'link' => $this->context->link,
                'moduleName' => $this->name,
                'title' => Configuration::get('title'),
                'description' => Configuration::get('description')
            )
        );

        return $this;
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
        return $this->clientManager->deleteImage($imageName);
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
        $this->clientManager->deleteClient($id);

        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminModules', true) .
            '&conf=1&configure=' . $this->name
        );

        return $this;
    }

    /**
     * View client informations.
     *
     * @param int $id | id of the client.
     *
     * @throws PrestaShopDatabaseException
     * @author Ali, Muamar
     *
     * @return $this
     */
    public function showClient(int $id)
    {
        $this->context->smarty->assign(
            array(
                'client' => $this->getClient($id)
            )
        );

        $this->output = $this->display(__FILE__, 'views/templates/admin/show.tpl');

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
        return $this->clientManager->getAll();
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
        return $this->clientManager->isClientExist($clientName);
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
        return $this->clientManager->getClient($clientId);
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
        return $this->clientManager->insertSql($name, $file_name);
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
        return $this->clientManager->updateSql($name, $id, $image);
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
        return $this->clientManager->deleteSql($id);
    }
}