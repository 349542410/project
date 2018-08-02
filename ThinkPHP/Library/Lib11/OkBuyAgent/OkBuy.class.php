<?php

    namespace Lib11\OkBuyAgent;

    class OkBuy{

        public function brand($authentication){

            $interface = __FUNCTION__;
            \Lib11\OkBuyAgent\Tools\Common::setAll($authentication);

            $obj = new \Lib11\OkBuyAgent\Tools\OBDrive();
            $result = $obj->distribution($interface,$data);

            // $obj->getError();

            return $result;

        }

        function test(){
            echo "hello";
        }

    }