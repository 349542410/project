<?php

    namespace Lib11\RB;

    class BrandLogic extends Logic{

        private $rb;

        public function __construct(){
            $this->rb = new \Lib11\RB\RedBook\RBBrand();
        }

        public function brandSearch($authentication=array(),$parameters){

            $result = $this->rb->brandSearch($authentication,$parameters);
            return $this->getInfo($result);

        }

    }