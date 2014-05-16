<?php

namespace Models\Managers;

use Models\Generics\BaseManager;
use Silex\Application;


/**
 * Cette classe permet le lien entre la base de données et l'application
 * toutes les requêtes concernant les données des simulations
 * sont gérées par celle-ci.
 */
class TableManager extends BaseManager
{ 
    /**
     * Clé des données.
     * Permet de sélectionner une ligne particulière dans la base de données
     * 
     * @var string
     */
    protected $_table = 'REF_PARAM';
    
     /**
     * Nom de du modèle associé à cette classe
     * 
     * @var string
     */
    protected $_entity = 'Models\\Entities\\TableEntity';
    
    
    /**
     * Retourne la liste des valeurs de la simulation
     * 
     * @param \Models\Generics\BaseEntity $instance
     * @return array
     * @throws \Exception
     */
    public function getTableParam(Application $app, $contraintes = array())
    { 
        $sql = $app['db']->fetchAll('SELECT *'
                . ' FROM REF_PARAM'
                . $this->getSqlWhere($contraintes)
                . ' ORDER BY REF_ORDRE_AFF');
        
        return $sql;
    }
    
    public function selectRowTable($table = '', $constraints = array())
    {
        $sql = 'SELECT * FROM ' . $table . $this->getSqlWhere($constraints);
        return $this->connexion->fetchAssoc($sql, array_values($constraints));
    }
      
    public function affichageDynamique(Application $app, $tableId)
    {
        
      return $app['db']->fetchAll('SELECT * FROM ' .$tableId );
    }
    
    public function getPrimaryKey($tableId)
    {
        $champ = $this->connexion->fetchColumn('SELECT REF_CHAMPS_CLE FROM REF_PARAM WHERE REF_NOMTABLE = ?', array($tableId), 0);
        
        if (empty($champ) || $champ === false) {
            throw new \Exception('La table "' . $tableId . '" ne possède pas de clée primaire');
        }
        
        return explode(',', $champ);
    }
    
    public function saveData($tableId, $ligne)
    {
        $keys = array_fill_keys($this->getPrimaryKey($tableId), null);
        $update = true;
        
        foreach ($keys as $key => $value) {
            
            $keys[$key] = $ligne[$key];
            
            if (empty($ligne[$key])) {
                $update = false;
                break;
            }
        }
        
        $data = array_diff_key($ligne, $keys);
        
        if ($update) {
            return $this->connexion->update(
                $tableId, 
                $data,
                $keys
            );
        } else {
            if ($this->getAutoIncr($app, $tableId) == 'true'){
                
                return $this->connexion->insert($tableId, $data);
            }else{
                return $this->connexion->insert($tableId, $data);
            }
        }
    }
    
    Public function getAutoIncr(Application $app, $tableId)
    {
        return $app['db']->fetchall('SELECT REF_AUTO_INCR FROM REF_PARAM WHERE REF_NOMTABLE = ' .$tableId);
    }
}