<?php

namespace Models\Entities;

use Models\Generics\ClassFactory;
use Models\Generics\BaseEntity;



/**
 * Classe entité
 */
class TableEntity extends BaseEntity 
{
    /**
     * Identifiant
     * @var int
     */
    protected $id;
    
    protected $data;
    
    protected $ajout;    
    /**
     * @{inherit}
     */
    public function isNew()
    {
        return !is_numeric($this->id);
    }
    
    /**
     * Get id
     * @return int
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Get data
     * @return array
     */
    public function getData($refresh = false)
    {
        $manager = ClassFactory::getManager('TableManager');
        
        if (!empty($this->data) && $refresh === false) {
            return $this->data;
        }

        return $this->data = $manager->getData($this);
    }

    /**
     * Set id
     * @param int $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Set data
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

     public function getajout()
    {
        return $this->ajout;
    }
    public function setajout($ajout)
    {
        $this->ajout = $ajout;
    }
    
}