<?php
require_once("ExpressServiceApi.php");
class CreateDirectMailOrder extends ExpressServiceApi
{
    /**
     *
     * @Assert\NotBlank()
     */
    public $HasPrepaid;

    /**
     *
     * @Assert\NotBlank()
     */
    public $HasReplaceUploadIdCard;

    /**
     *
     * @Assert\NotBlank()
     */
    public $IdCardNumber;


    /**
     *
     * @Assert\NotBlank()
     */
    public $Height;

    /**
     *
     * @Assert\NotBlank()
     */
    public $IDCardId;

    /**
     *
     * @Assert\NotBlank()
     */
    public $InsureStatus;


    /**
     *
     * @Assert\NotBlank()
     */
    public $Length;

    /**
     *
     * @Assert\NotBlank()
     */
    public $Origin;


    /**
     *
     * @Assert\NotBlank()
     */
    public $ToAddressee;

    /**
     *
     * @Assert\NotBlank()
     */
    public $ToCity;

    /**
     *
     * @Assert\NotBlank()
     */
    public $ToName;

    /**
     *
     * @Assert\NotBlank()
     */
    public $ToProvince;

    /**
     *
     * @Assert\NotBlank()
     */
    public $ToArea;

    /**
     *
     * @Assert\NotBlank()
     */
    public $ToTelphone;

    /**
     *
     *
     */
    public $ToEmail;

    /**
     *
     * @Assert\NotBlank()
     */
    public $ToZIP;

    /**
     *
     * @Assert\NotBlank()
     */
    public $CurrencyCode;

    /**
     *
     * @Assert\NotBlank()
     */
    public $Currency;

    /**
     *
     * @Assert\NotBlank()
     */
    public $OrderType;

    /**
     *
     * @Assert\NotBlank()
     */
    public $Area;

    /**
     *
     * @Assert\NotBlank()
     */
    public $City;

    /**
     *
     * @Assert\NotBlank()
     */
    public $Provine;

    /**
     *
     * @Assert\NotBlank()
     */
    public $Street;

    /**
     *
     * @Assert\NotBlank()
     */
    public $Telphone;

    /**
     *
     * @Assert\NotBlank()
     */
    public $ZIP;


    /**
     *
     * @Assert\NotBlank()
     */
    public $CustomerIdentity;

    /**
     *
     * @Assert\NotBlank()
     */
    public $UserName;

    /**
     *
     * @Assert\NotBlank()
     */
    public $Weight;

    /**
     *
     * @Assert\NotBlank()
     */
    public $Width;

    /**
     *
     * @Assert\NotBlank()
     */
    public $Name;

    /**
     *
     * @Assert\NotBlank()
     */
    public $FromAddresseeTo;

    /**
     *
     * @Assert\NotBlank()
     */
    public $FromTelphone;

    /**
     *
     * @Assert\NotBlank()
     */
    public $ProvinceID;

    /**
     *
     * @Assert\NotBlank()
     */
    public $InsureRate;

    /**
     *
     * @Assert\NotBlank()
     */
    public $TrackingCenterCode;
    /**
     *
     * @Assert\NotBlank()
     */
    public $EMSTrackingNumber;

    /**
     * @Assert\NotBlank()
     */
    public $ChannelCode;

    /**
     *
     * @Assert\NotBlank()
     */
    public $CategoryCode;

    /**
     *
     *
     */
    private $_innerGoods = [];

    private $_Sender;

    private $_Addressee = [];


    /**
     * 实体类中的接口Data数据集
     */
    public function getEntityData()
    {
        return [
            'Addressee' => $this->_Addressee,

            'ChannelCode' => $this->ChannelCode,
            'CustomerIdentity' => $this->CustomerIdentity,
            'EMSTrackingNumber' => $this->EMSTrackingNumber,

            'Goods' => $this->_innerGoods,

            'Height' => $this->Height,
            'InsureStatus' => $this->InsureStatus,
            'Length' => $this->Length,
            'Origin' => intval($this->Origin),


            'Sender' => $this->_Sender,

            'TrackingCenterCode' => $this->TrackingCenterCode,
            'Weight' => $this->Weight,
            'Width' => $this->Width,

        ];
    }

    //内件信息整编
    public function setInnerGoods($post)
    {
        if (empty($post['form']['InsureStatus'])) {
            $InsureStatusChage = 0;
        } else {
            $InsureStatusChage = 1;
        }

        $this->ChannelCode = $post['form']['ChannelCode'];
        $this->EMSTrackingNumber = $post['form']['EMSTrackingNumber'];
        $this->CustomerIdentity = $post['form']['CustomerIdentity'];
        $this->HasPrepaid = $post['form']['HasPrepaid'];
        $this->HasReplaceUploadIdCard = $post['form']['HasReplaceUploadIdCard'];
        $this->Height = $post['form']['Height'];
        $this->IdCardNumber = $post['form']['IdCardNumber'];
        $this->InsureStatus = $InsureStatusChage;
        $this->Length = $post['form']['Length'];
        $this->Origin = $post['form']['Origin'];
        $this->TrackingCenterCode = $post['form']['TrackingCenterCode'];
        $this->Weight = $post['form']['Weight'];
        $this->Width = $post['form']['Width'];

        if (!empty($post['form']['Brands'])) {
            foreach ($post['form']['Brands'] as $key => $val) {
                $arr = [];
                $arr['Brands'] = $post['form']['Brands'][$key];
                $arr['CategoryCode'] = intval($post['form']['CategoryCode'][$key]);
                $arr['CurrencyCode'] = $post['form']['CurrencyCode'][$key];
                $arr['GoodsName'] = $post['form']['GoodsName'][$key];
                $arr['ModelNo'] = $post['form']['ModelNo'][$key];
                $arr['Price'] = $post['form']['Price'][$key];
                $arr['Qty'] = $post['form']['Qty'][$key];
                $arr['Unit'] = $post['form']['Unit'][$key];

                $this->_innerGoods[] = $arr;
            }
        }
    }


    //整合发件人地址
    public function setSender($sendmsg)
    {
        if (!empty($sendmsg)) {
            $date = array(
                'Name' => $sendmsg['Name'],
                'Street' => $sendmsg['Street'],
                'Telphone' => $sendmsg['Telphone'],
                'ZIP' => $sendmsg['ZIP'],
            );
            $this->_Sender = $date;
        }
    }

    //整合收件人地址
    public function setAddressee($sddresseemsg)
    {
        if (!empty($sddresseemsg)) {
            $date = array(
                'ToAddress' => $sddresseemsg['ToAddress'],
                'ToArea' => $sddresseemsg['ToArea'],
                'ToCity' => $sddresseemsg['ToCity'],
                'ToEmail' => $sddresseemsg['ToEmail'],
                'ToMobile' => $sddresseemsg['ToMobile'],
                'ToName' => $sddresseemsg['ToName'],
                'ToProvince' => $sddresseemsg['ToProvince'],
                'ToProvinceCode' => $sddresseemsg['ToProvinceCode'],
                'ToZIP' => $sddresseemsg['ToZIP'],
            );
            $this->_Addressee = $date;
        }
    }

}