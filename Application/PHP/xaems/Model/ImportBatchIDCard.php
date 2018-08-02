<?php
require_once("ExpressServiceApi.php");
class ImportBatchIDCard extends ExpressServiceApi
{
    /**
     * @Assert\NotBlank(message="不能为空.")
     *
     */
    public $CustomerIdentity;

    /**
     * @Assert\NotBlank(message="不能为空.")
     *
     */
    public $IdCardNumber;

    /**
     * @Assert\NotBlank(message="不能为空.")
     *
     */
    public $Receiver;

    /**
     * @Assert\NotBlank(message="不能为空.")
     *
     */
    public $TrackingNumber;


    /**
     *
     */
    public $_IdCardInfos;


    /**
     * 实体类中的接口Data数据集
     */
    public function getEntityData()
    {
        return [
            'CustomerIdentity' => $this->CustomerIdentity,

            'IDCardInfos' => $this->_IdCardInfos,

        ];
    }



    /**
     * 整合卡信息数据
     */
    public function setIdCardInfos($idcardinfomsg){

        if(!empty($idcardinfomsg)){

            foreach($idcardinfomsg as $key=>$val){
                $arr = [];
                $arr['IDCardNumber'] = $val['IDCardNumber'];
                $arr['Receiver'] = $val['Receiver'];
                $arr['TrackingNumber'] = $val['TrackingNumber'];

                $this->_IdCardInfos[] = $arr;
            }

        }

    }


}
