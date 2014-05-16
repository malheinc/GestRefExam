<?php

namespace Models\Generics;

interface EntityInterface
{
        public function __toString();

        public function toArray();

        public function fromArray();
}