<?php
require_once("AbstractModel.php");
class UploadIDCard extends AbstractModel
{
    /**
     * @Assert\NotBlank(message="身份证号码不能为空.")
     */
    public $IDCardNumber;

    /**
     * @Assert\NotBlank()
     */
    public $Addressee;

    /**
     *@Assert\NotBlank()
     */
    public $OrderNumberOrTrackingNumber;

    /**
     * @Assert\NotBlank()
     */
    public $IDCardFront;

    /**
     * @Assert\NotBlank()
     */
    public $IDCardBack;

    /**
     * @Assert\NotBlank()
     */
    public $CoverOldIDCard;


    /**
     * 实体类中的接口Data数据集
     */
    public function getEntityData()
    {
        return [
            'IDCardNumber' => $this->IDCardNumber,
            'Addressee' => $this->Addressee,
            'OrderNumberOrTrackingNumber' => $this->OrderNumberOrTrackingNumber,
            'IDCardFront' => $this->IDCardFront,
            'IDCardBack' => $this->IDCardBack,
            'CoverOldIDCard' => $this->CoverOldIDCard,
        ];
    }
}
