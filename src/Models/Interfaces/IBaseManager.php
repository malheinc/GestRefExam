<?php 

namespace Models\Interfaces;
use Models\Generics\EntityInterface;
/**
 * IBaseManager
 */
interface IBaseManager
{
        public function save(EntityInterface $object);
}

?>