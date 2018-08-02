<?php

    namespace Lib11\RB;

    class CategoryLogic extends Logic{

        private $rb;

        public function __construct(){
            $this->rb = new \Lib11\RB\RedBook\RBCategory();
        }

        public function categorySearch($authentication=array(),$parameters){

            $result = $this->rb->categorySearch($authentication,$parameters);
            return $this->getInfo($result);

        }

        public function getSpec($authentication=array(),$data){

            if(empty($data['category_id'])){
                return $this->getInfo(false,'缺少category_id参数');
            }
            $result = $this->rb->getSpec($authentication,$data['category_id']);
            return $this->getInfo($result);

        }

        public function getProduct($authentication=array(),$data){

            if(empty($data['category_id'])){
                return $this->getInfo(false,'缺少category_id参数');
            }
            $result = $this->rb->getProduct($authentication,$data['category_id']);
            return $this->getInfo($result);

        }

    }