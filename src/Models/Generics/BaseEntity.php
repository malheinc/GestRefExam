<?php

namespace Models\Generics;

use Models\Generics\EntityInterface;

abstract class BaseEntity implements EntityInterface
{
    /**
     * _reflexion
     * @var \ReflectionClass
     */
    private $_reflexion;




    /**
     * Constructor
     * @param array $data
     */
    function __construct($data = array())
    {
        $this->_reflexion = new \ReflectionClass($this);

        $this->fromArray($data);
    }

    /**
     * Magik method
     * 
     * @return string
     */
    public function __toString()
    {
        ob_start();
        var_dump($this);
        return ob_get_clean();
    }

    /**
     * Convert object in array and trim null value
     *
     * @see http://briancray.com/posts/remove-null-values-php-arrays
     *
     * @return array
     */
    public function toArray()
    {
        $keys = array();

        foreach ($this->_reflexion->getProperties() as $property) {
            
            $keys[] = $property->getName();
        }

        return array_intersect_key(get_object_vars($this), array_flip($keys));
    }

    /**
     * Init object
     * 
     * @param  array  $data
     */
    public function fromArray($data = array())
    {
        if (is_array($data)) {
            
            foreach ($data as $key => $value) {
                
                $method = 'set' . ucfirst($key);

                if ($this->_reflexion->hasMethod($method) === true) {

                    $this->$method($value);
                }
            }
        }
    }

    /**
     * return true if object is empty
     * 
     * @return boolean 
     */
    public function isNew()
    {
        return _empty(array_filter($this->toArray()));
    }
    
}