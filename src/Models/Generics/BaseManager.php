<?php

namespace Models\Generics;
use Models\Interfaces\IBaseManager;
use Doctrine\DBAL\Connection;

abstract class BaseManager implements IBaseManager
{    
    /**
     * _properties
     * @var array
     */
    private $_properties;

    /**
     * DBAL connector
     * 
     * @var \Doctrine\DBAL\Connection
     */
    protected $connexion;

    /**
    * Nom de la table dans la base données
    * @var string
    */
    protected $_table;

    /**
     * Nom de la méthode à appeler avant un traitement base de données
     * @var array
     */
    protected $_preRequete = array();

    /**
     * Nom de la méthode à appeler après un traitement base de données
     * @var array
     */
    protected $_postRequete = array();

    /**
     * Nom de la méthode à appeler avant une procédure de sauvegarde
     * @var array
     */
    protected $_preSave = array();

    /**
     * Nom de la méthode à appeler après une procédure de sauvegarde
     * @var array
     */
    protected $_postSave = array();

    /**
     * True pour ne pas inclure les éléments de la clé primaire dans la requête 
     * insert (cas des champs auto-incrément)
     * @var boolean
     */
    protected $_skipPrimaryInsert = false;

    
    
    
    /**
     * Constructor
     * 
     * @param \Doctrine\DBAL\Connection $connexion
     */
    public function __construct(Connection $connexion)
    {
        $this->connexion = $connexion;
        
        $this->_preRequete  =  array(get_called_class(), 'preRequete');
        $this->_postRequete = array(get_called_class(), 'postRequete');
        
        $this->_preSave =  array(get_called_class(), 'preSave');
        $this->_postSave = array(get_called_class(), 'postSave');
    }

    /**
     * Initialise une transaction
     */
    public function beginTransaction() {

        $this->connexion->beginTransaction();
    }

    /**
     * Commit une transaction
     */
    public function commit() {

        $this->connexion->commit();
    }

    /**
     * Rollback d'une transaction
     */
    public function rollback() {

        $this->connexion->rollback();
    }

    /**
     * Separate values and primary key
     * 
     * @param  array    $data 
     * @param  array    $primary
     * 
     * @return array    Array with 2 keys (primary, values).
     */
    public function keysValues($primary, $data) {

        return array(
            'primary' => array_intersect_key($data, $primary),
            'values'  => array_diff_key($data, $primary),
        );
    }

    /**
     * Save 
     * 
     * @param  EntityInterface $object
     * @param boolean   Indique si l'on force la valeur de la clé primaire ou si l'on délégue la tache à la base de données
     */
    public function save(EntityInterface $object)
    {
        if (is_callable($this->_preRequete))    $this->preRequete($object);
        if (is_callable($this->_preSave))       $this->preSave($object);
        
        $keysValues = $this->keysValues($this->_primary, $this->_bindValues($object));
        $element = $this->select($keysValues['primary']);

        if (empty($element)) 
            $element = new $this->_entity();

        if ($element->isNew()) {
            
            $res = $this->insert($object);
            
            if ($this->_skipPrimaryInsert === true) {
                
                reset($this->_primary);
                $method = 'set' . ucfirst(key($this->_primary));
                $object->$method($res);
            }
        } else {
         
            $res = $this->update($object);
        }
        
        if (is_callable($this->_postRequete)) $this->postRequete($object);
        if (is_callable($this->_postSave)) $this->postSave($object);
        
        return $res;
    }

    /**
     * Select one raw in database
     * 
     * @param  array  $key key of search
     * 
     * @return array
     */
    public function select($keys = array(), $forceArray = false)
    {
        
        
        $sql = '';

        foreach ($keys as $key => $value) {
            
            $sql .= (empty($sql) ? ' WHERE ' : ' AND ') . $key . ' = ?';
        }

        $sql = 'SELECT * FROM '. $this->_table . $sql;
        
        if ($forceArray === true) {
            return $this->connexion->fetchAssoc($sql, array_values($keys));
        } else {

            $data = $this->getObject($this->connexion->fetchAssoc($sql, array_values($keys)));
            
            if (is_callable($this->_postRequete)) {
                
                $this->postRequete($data);
            }

            return $data;
        }
    }

    /**
     * Select all raw in database
     * 
     * @param  array  $constraints
     * 
     * @return array
     */
    public function findOne($constraints = array(), $column = '')
    {
        $sql = '';

        foreach ($constraints as $key => $value) {
            
            $sql .= (empty($sql) ? ' WHERE ' : ' AND ') . $key . ' = ?';
        }

        $sql = 'SELECT ' . $column . ' FROM ' . $this->_table . $sql;

        return $this->connexion->fetchColumn($sql, array_values($constraints));
    }

    /**
     * Select all raw in database
     * 
     * @param  array  $constraints
     * 
     * @return array
     */
    public function find($constraints = array(), $forceArray = false)
    {
        $sql = '';

        foreach ($constraints as $key => $value) {
            
            $sql .= (empty($sql) ? ' WHERE ' : ' AND ') . $key . ' = ?';
        }

        $sql = 'SELECT * FROM ' . $this->_table . $sql;

        if ($forceArray === true)
            return $this->connexion->fetchAll($sql, array_values($constraints));
        else{
            
            $data = $this->getListObject($this->connexion->fetchAll($sql, array_values($constraints)));
            
            if (is_callable($this->_postRequete)) {
                
                $this->postRequete($data);
            }

            return $data;
        }
    }

    /**
     * Retour un EntityInterface depuis le résultat d'une requête SQL
     * 
     * @param  array  $rdata
     * 
     * @return EntityInterface
     */
    public function getObject($rdata = array())
    {
        $bind = array_flip($this->getBinding());

        $data = array();

        if (is_array($rdata)){

            foreach ($rdata as $key => $value) {

                if (array_key_exists($key, $bind))
                    $data[$bind[$key]] = $value;
            }
        }

        return new $this->_entity($data);
    }

    /**
     * Retourne un tableau de EntityInterface depuis une requête SQL
     * 
     * @param  array  $result
     * 
     * @return array
     */
    public function getListObject($result = array())
    {
        $objects = array();

        if (is_array($result)) {

            foreach ($result as $value) {
                
                $objects[] = $this->getObject($value);
            }
        }

        return $objects;
    }

    /**
     * Insert in database from entity
     * 
     * @param EntityInterface or array
     * @param boolean   Indique si l'on force la valeur de la clé primaire ou si l'on délégue la tache à la base de données
     */
    public function insert($object)
    {
        if (is_array($object))
            return $this->connexion->insert($this->_table, $object);
        
        if($object instanceof EntityInterface) {
            
            $data = $this->_bindValues($object);
            
            if ($this->_skipPrimaryInsert === true) {
                
                $data = $this->keysValues($this->_primary, $data);
                $data = $data['values'];
            }
            
            $res = $this->connexion->insert($this->_table, $data);
            
            if ($this->_skipPrimaryInsert === true) {
                
                $res = $this->connexion->lastInsertId();
            }
            
            return $res;
        }

        return 0;
    }

    /**
     * Update in database
     * 
     * @param EntityInterface
     */
    public function update(EntityInterface $object)
    {
        $bound = $this->_bindValues($object);

        $keysValues = $this->keysValues($this->_primary, $bound);

        return $this->connexion->update($this->_table, $keysValues['values'], $keysValues['primary']);
    }

    /**
     * Supprimer dans la base de données
     * 
     * @param EntityInterface
     */
    public function delete($contrainte = array(), $table)
    {
        return $this->connexion->delete($table, $contrainte);
    }

     /**
     * Count all raw in database
     * 
     * @param  array  $constraints
     * 
     * @return array
     */
    public function count($constraints = array())
    {
        $sql = '';

        foreach ($constraints as $key => $value) {
            
            $sql .= (empty($sql) ? ' WHERE ' : ' AND ') . $key . ' = ?';
        }

        $sql = 'SELECT COUNT(*) FROM ' . $this->_table . $sql;

        return $this->connexion->fetchColumn($sql, array_values($constraints));
    }
    
    /**
     * Permet la construction d'une clause where à partir d'un tableau de 
     * contrainte. Seul l'opérateur "AND" est utilisé.
     * 
     * @param array $contraintes
     * 
     * @return string
     */
    protected function getSqlWhere($contraintes = array())
    {
        $sql = '';

        foreach ($contraintes as $key => $value) {
            
            $sql .= (empty($sql) ? ' WHERE ' : ' AND ') . $key . ' = ?';
        }
        
        return $sql;
    }


    /**
     * Return an array where key bind with column database
     * 
     * @param  EntityInterface $object
     * @return array
     */
    private function _bindValues(EntityInterface $object)
    {
        $data = $object->toArray();
        
        $bind = array_flip($this->getBinding());

        foreach ($bind as $key => $value) {
            
            $bind[$key] = $data[$value];
        }
        
        return $bind;
    }
}