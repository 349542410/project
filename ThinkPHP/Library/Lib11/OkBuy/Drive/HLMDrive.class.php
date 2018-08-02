<?php

    /*
    *
    *   好乐买接口驱动
    *
    */


    namespace Lib11\OkBuy\Drive;

    class HLMDrive{

        private $error = '';        //错误信息
        private $allowList;         //允许接口列表

        public function __construct(){

            $this->allowList = array(
                'searchstock',
                'updatestock',
                // 'undeliveredorder',
                // 'unconfirmedorder',
                'historyorder',
                // 'confirmdelivery',
                // 'outofstock',
                // 'confirmreturn',
                // 'needinvoiceodrlist',
                // 'confirmpostinvoice',
                // 'getPrintHtml',
                // 'searchStockByComCode',
                // 'cancelorder',
                'getOdrDetailInfo',
                'getDiffCatsOdrLists',
            );

        }

        private function _tec_par($data){

            $result = array();
            if($data!==false){
                $result['success'] = true;
                $result['data'] = $data;
                $result['errorstr'] = '';
            }else{
                $result['success'] = false;
                $result['data'] = false;
                $result['errorstr'] = $this->error;
            }
            return $result;

        }



        //参数分发
        //  interface : 接口方法名
        //  data ： 接口需要的数据
        //  return : 失败返回false，可使用getError()获取错误提示
        public function distribution($interface,$data){

            if(!in_array($interface,$this->allowList)){
                $this->error = "接口方法不在允许的列表内";
                return $this->_tec_par(false);
            }

            $url = '/supplier/api/' . $interface;
            $signObj = new \Lib11\OkBuy\Tool\HLMInterface($data);
            // $result = $signObj->run($url);
            return $this->_tec_par( $signObj->run($url) );

        }





        //参数分发
        //  此方法接受多个data，使用 $list = array($data1,$data2,...)这种形式，因此会分别使用每个data调用一次接口
        //  interface : 接口方法名
        //  list ： 接口需要的数据
        //  return : 失败返回false，可使用getError()获取错误提示
        // public function distribution_list($interface,$list){

        //     if(!in_array($interface,$this->allowList)){
        //         $this->error = "接口方法不在允许的列表内";
        //         return false;
        //     }

        //     $url = '/supplier/api/' . $interface;

        //     $arr = array();
        //     foreach($list as $k=>$data){

        //         // $signObj = new \Lib11\Common\HLMInterface($data);
        //         $signObj = new \Lib11\OkBuy\Tool\HLMInterface($data);
        //         $result = $signObj->run($url);

        //         $arr[$k]['return'] = $result;
        //         $arr[$k]['error'] = $this->error;

        //     }

        //     return $arr;

        // }

        // public function getError(){
        //     return $this->error;
        // }

    }