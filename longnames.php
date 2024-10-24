<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class LongNames extends Module
{
    public function __construct()
    {
        $this->name = 'longnames';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Armando';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Long Names');
        $this->description = $this->l('Amplía el límite de caracteres para los campos nombre y apellidos a 512.');
    }

    public function install()
    {
        return parent::install() && $this->alterTables() && $this->registerOverrides();
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->removeOverrides();
    }

    private function alterTables()
    {
        $sql = array();

        $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'customer` MODIFY `firstname` VARCHAR(512) NOT NULL';
        $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'customer` MODIFY `lastname` VARCHAR(512) NOT NULL';
        $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'address` MODIFY `firstname` VARCHAR(512) NOT NULL';
        $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'address` MODIFY `lastname` VARCHAR(512) NOT NULL';

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    private function registerOverrides()
    {
        $this->addOverride('Customer');
        $this->addOverride('Address');
        return true;
    }

    private function removeOverrides()
    {
        $this->removeOverride('Customer');
        $this->removeOverride('Address');
        return true;
    }

    private function addOverride($className)
    {
        $overrideDir = _PS_OVERRIDE_DIR_.'classes/';
        $overrideFile = $overrideDir.$className.'.php';

        if (!is_dir($overrideDir)) {
            mkdir($overrideDir, 0755, true);
        }

        $content = $this->getOverrideContent($className);

        return file_put_contents($overrideFile, $content);
    }

    private function removeOverride($className)
    {
        $overrideFile = _PS_OVERRIDE_DIR_.'classes/'.$className.'.php';
        if (file_exists($overrideFile)) {
            unlink($overrideFile);
        }
        return true;
    }

    private function getOverrideContent($className)
    {
        if ($className == 'Customer') {
            return $this->getCustomerOverrideContent();
        } elseif ($className == 'Address') {
            return $this->getAddressOverrideContent();
        }
        return '';
    }

    private function getCustomerOverrideContent()
    {
        return '<?php
class Customer extends CustomerCore
{
    public static $definition = array(
        \'table\' => \'customer\',
        \'primary\' => \'id_customer\',
        \'fields\' => array(
            \'secure_key\' => array(\'type\' => self::TYPE_STRING, \'size\' => 64),
            \'note\' => array(\'type\' => self::TYPE_STRING, \'size\' => 65000),
            \'id_shop_group\' => array(\'type\' => self::TYPE_INT, \'validate\' => \'isUnsignedId\'),
            \'id_shop\' => array(\'type\' => self::TYPE_INT, \'validate\' => \'isUnsignedId\'),
            \'id_gender\' => array(\'type\' => self::TYPE_INT, \'validate\' => \'isUnsignedId\'),
            \'id_default_group\' => array(\'type\' => self::TYPE_INT, \'validate\' => \'isUnsignedId\'),
            \'id_lang\' => array(\'type\' => self::TYPE_INT, \'validate\' => \'isUnsignedId\'),
            \'lastname\' => array(\'type\' => self::TYPE_STRING, \'required\' => true, \'size\' => 512),
            \'firstname\' => array(\'type\' => self::TYPE_STRING, \'required\' => true, \'size\' => 512),
            // Resto de campos...
        ),
    );
}
';
    }

    private function getAddressOverrideContent()
    {
        return '<?php
class Address extends AddressCore
{
    public static $definition = array(
        \'table\' => \'address\',
        \'primary\' => \'id_address\',
        \'fields\' => array(
            \'id_customer\' => array(\'type\' => self::TYPE_INT, \'validate\' => \'isUnsignedId\'),
            \'id_manufacturer\' => array(\'type\' => self::TYPE_INT, \'validate\' => \'isUnsignedId\'),
            \'id_supplier\' => array(\'type\' => self::TYPE_INT, \'validate\' => \'isUnsignedId\'),
            \'id_warehouse\' => array(\'type\' => self::TYPE_INT, \'validate\' => \'isUnsignedId\'),
            \'id_country\' => array(\'type\' => self::TYPE_INT, \'validate\' => \'isUnsignedId\', \'required\' => true),
            \'id_state\' => array(\'type\' => self::TYPE_INT, \'validate\' => \'isUnsignedId\'),
            \'alias\' => array(\'type\' => self::TYPE_STRING, \'validate\' => \'isGenericName\', \'required\' => true, \'size\' => 32),
            \'company\' => array(\'type\' => self::TYPE_STRING, \'validate\' => \'isGenericName\', \'size\' => 255),
            \'lastname\' => array(\'type\' => self::TYPE_STRING, \'validate\' => \'isName\', \'required\' => true, \'size\' => 512),
            \'firstname\' => array(\'type\' => self::TYPE_STRING, \'validate\' => \'isName\', \'required\' => true, \'size\' => 512),
            // Resto de campos...
        ),
    );
}
';
    }
}