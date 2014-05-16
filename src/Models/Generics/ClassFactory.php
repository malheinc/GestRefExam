<?php

namespace Models\Generics;

use Doctrine\DBAL\Connection;

/**
 * Description of ClassFactory
 *
 * @author glr735
 */
class ClassFactory
{
    private static $_instance;
    
    private $_connection = null;
    
    /**
     * Empêche la création externe d'instances.
     */
    private function __construct (Connection $connection) {
        
        $this->_connection = $connection;
    }
 
    /**
     * Empêche la copie externe de l'instance.
     */
    private function __clone () {}
    
    /**
     * Renvoi de l'instance
     * 
     * @return ClassFactory
     * @throws \Exception
     */
    private static function _getInstance()
    {
        if (is_null(self::$_instance->_connection)) {
            throw new \Exception(__CLASS__ . ' non initialisé');
        }
        
        return self::$_instance;
    }
 
    /**
     * Initialise l'instance
     * 
     * @throws \Exception si l'instance n'est pas correctement initialisé
     */
    public static function init(Connection $connection = null)
    {
        if (!(self::$_instance instanceof self)) {
            
            self::$_instance = new self($connection);
        }
    }
    
    /**
     * Retourne une instance de manager
     * 
     * @param string $name
     * @return \Models\Generics\BaseManager;
     */
    public static function getManager($name = '')
    {
        $className = 'Models\\Managers\\' . $name;
        
        return new $className(self::_getInstance()->_connection);
    }
    
    /**
     * Retourne une instance d'entité
     * 
     * @param string $name
     * @param array  $parameters
     * 
     * @return \Models\Generics\BaseEntity;
     */
    public static function getEntity($name = '', $parameters = array())
    {
        $className = 'Models\\Entities\\' . $name;
        
        return new $className($parameters);
    }
}
