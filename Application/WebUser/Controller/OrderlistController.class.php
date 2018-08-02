<?php
/**
 * 根据线路ID判断输出 bclist，cclist，picupload  3 个html
 */
namespace WebUser\Controller;
use Think\Controller;
class OrderlistController extends Controller{

    public function _initialize(){
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/Order');
        $this->client = $client;
    }

    //根据用户选择的线路ID，输出对应的页面内容
	public function console(){
		$TranKd   = I('post.id');//线路ID

        $this->TranKd = $TranKd;
		$order_id = (I('post.order_id')) ? trim(I('post.order_id')) : '';//订单ID
        $type     = (I('post.type')) ? trim(I('post.type')) : 'index';//是否为编辑页面发起的请求，默认是index页面

        $user_id = session('user_id');
        

        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/Orderlist');

        $res = $client->getline($TranKd, $order_id, $type);

//         dump($res);
//         die;

        //获取购物小票图片
        $where = array('order_id'=>$order_id);
        vendor('Hprose.HproseHttpClient');
        $pie = new \HproseHttpClient(C('RAPIURL').'/Piecemeal');
        $path = $pie->get_receipt_img($where);
        $this->assign('receipt_img',$path['receipt_img']);


        $line     = $res['line'];//线路信息
        $pro_list = $res['pro_list'];//订单的所有货品信息
        $picInfo  = $res['picInfo'];//该订单中收件人的证件照信息
        $brand = $res['brand'];

        // 英文品牌
        if(empty($brand)){
            $this->assign('brand_list', '{}');
        }else{
            $this->assign('brand_list', json_encode($brand));
        }
    
        // dump($brand);

        // 中文货品名称
        $user_goods_name = $client->get_goods_name(session('user_id'));
        if(empty($user_goods_name)){
            $this->assign('user_goods_name', '{}');
        }else{
            $this->assign('user_goods_name', json_encode($user_goods_name));
        }

        // dump($user_goods_name);
        // dump(json_encode($user_goods_name));
        // dump(session('user_id'));


        if($type == 'import'){
            $pro_list = session('pro_list');
            $picInfo  = '';
        }

//         dump($pro_list);


		
        if($line){

        	//如果订单ID为空，则默认是在线下单
        	if($order_id == ''){

        		$this->assign('count',5);//默认货品行数为5
	        	$this->assign('cat_list',$this->first_level());
        	}else{

        		$count = count($pro_list);
                foreach($pro_list as $k => $v){
                    $pro_list[$k]['category_one'] = addslashes($v['category_one']);
                }

        		$this->assign('count',$count);//按实际货品数据条数，输出货品行数
        		$this->assign('pro_list',$pro_list);
        		$this->assign('cat_list',$this->cat_list());
                
        	}

	        $this->assign('html_name',$type);//前端用于判断

	        // if(is_array($pro_list) && count($pro_list) > 0) $this->assign('pro_list',$pro_list);

			$bc  = '';
			$pic = '';
			//根据线路信息输出需要的html
	        if($line['bc_state'] == '1'){
	        	
	        	$bc = $this->fetch('bclist');//获取页面内容

	        }else if($line['cc_state'] == '1'){

                //如果 税金形式(tax_kind) 是固定值
                if($line['tax_kind'] == '0'){

                    $bc = $this->fetch('cclistfixed');//获取页面内容
                    
                }else{//如果 税金形式(tax_kind) 是百分比
                    //根据汇率计算出美元免税的额度

                    if(floor($line['taxthreshold']) > 0){
                        $free_duty = sprintf("%.2f", $line['taxthreshold'] / floatval(C('US_TO_RMB_RATE')));
                    }else{
                        $free_duty = sprintf("%.2f", floatval(C('RMB_Free_Duty')) / floatval(C('US_TO_RMB_RATE')));
                    }
                    $this->assign('free_duty',$free_duty);
                    $bc = $this->fetch('cclistpercent');//获取页面内容
                }
            }else{

	        	$bc = $this->fetch('cclist');//获取页面内容
	        }

            $tic = '';
            if($line['shop_state']==1){
                $tic = $this->fetch('Orderlist/ticupload');
                session('shop_state','1');
            }
            // dump($tic);




            // liao ya di 2017-10-16 start

            if($_POST['addr_id']&&$_POST['addr_id']!=0){
                $x_client = new \HproseHttpClient(C('RAPIURL').'/UserAddressee');
                $addr_id = I('post.addr_id');
                $x_result = $x_client->search(array('id'=>$addr_id),'');
                $x_result = $x_result['data'][0];

                $idcard_cli = new \HproseHttpClient(C('RAPIURL').'/IdcardInfo');
                $idcard_res = $idcard_cli->get_idcard_info_no_examine($x_result['name'],$x_result['tel'],$x_result['cre_num'], $user_id)[0];
            }

            // liao ya di 2017-10-16 end 

            // dump($line['id']);
            $this->assign('t_line',array('line_id'=>$line['id'], 'input_idno'=>$line['input_idno'], 'member_sfpic_state'=>$line['member_sfpic_state']));

            
	        //根据线路信息输出需要的html
	        if($line['member_sfpic_state'] == '1'){
	        	
                // dump($picInfo);
                /* 显示已上传的证件照正反面图片 */
                //证件照正面文件名不为空
                if($picInfo['front_file_name'] != ''){
//                    $this->assign('ID_front_img',$picInfo['front_file_name']);//把证件照正面文件名字保存到session
                    $this->assign('front_id_img',WU_FILE.$picInfo['front_id_img']);//显示证件照正面图片
                }else{
//                    dump($idcard_res);
                    //liao ya di
                    //当进来此页面而没有收件人id时，x_result为空
                    //当有收件人id并查询到数据了，而此收件人没有上传id_card_front时，x_result为none
                    if(!empty($idcard_res)){
                        $this->assign('front_id_img',C('TMPL_PARSE_STRING.__MEMBER__').'/images/upload_success_f.png');
                    }else if(!empty($x_result['id_card_front'])&&$x_result['id_card_front']!='none'){
//                        $this->assign('ID_front_img',$x_result['id_card_front']);
//                        $this->assign('front_id_img',WU_FILE.$x_result['id_card_front']);    //显示证件照正面图片
                        $this->assign('front_id_img',C('TMPL_PARSE_STRING.__MEMBER__').'/images/upload_success_f.png');
                    }else{
//                        $this->assign('ID_front_img','');
                        $this->assign('front_id_img',C('TMPL_PARSE_STRING.__MEMBER__').'/images/pho_front.png');//显示默认图片
                    }
                }
                //证件照反面文件名不为空
                if($picInfo['back_file_name'] != ''){
//                    $this->assign('ID_back_img',$picInfo['back_file_name']);
                    $this->assign('back_id_img',WU_FILE.$picInfo['back_id_img']);//显示证件照反面图片
                }else{
                    //liao ya di
                    if(!empty($idcard_res)){
                        $this->assign('back_id_img',C('TMPL_PARSE_STRING.__MEMBER__').'/images/upload_success_b.png');
                    }else if(!empty($x_result['id_card_back'])&&$x_result['id_card_back']!='none'){
//                        $this->assign('ID_back_img',$x_result['id_card_back']);
//                        $this->assign('back_id_img',WU_FILE.$x_result['id_card_back']);//显示证件照反面图片
                        $this->assign('back_id_img',C('TMPL_PARSE_STRING.__MEMBER__').'/images/upload_success_b.png');
                    }else{
//                        $this->assign('ID_back_img','');
                        $this->assign('back_id_img',C('TMPL_PARSE_STRING.__MEMBER__').'/images/pho_back.png');//显示默认图片
                    }
                    
                }
                /* 显示已上传的证件照正反面图片 end */
                
	        	$picupload = $this->fetch('picupload');
	        }
	        echo $picupload;

            echo $tic;
            echo $bc;

        }

	}

	//获取全部顶级类别
    public function first_level(){
        $client = $this->client;

        $first_level = $client->first_level($this->TranKd);//查询所有类别
        
		return $first_level;
    }

    /**
     * 根据顶级类别ID找出对应的下一级分类
     * @param  [type] $id [上级ID]
     * @return [type]     [description]
     */
    public function next_level(){
        $id = trim(I('id'));

        if(empty($id)){
            echo '';
            die;
        }

        $client = $this->client;

        // getTree(null);//上面调用完一次后重置静态变量
        $list = $client->_next_level($id);
        foreach($list as $k=>$v){
            $list[$k]['text'] = $list[$k]['cat_name'];
        }
        
        // dump($list);
        $this->ajaxReturn($list);
    }

    /**
     * 根据二级类别ID找出对应其对应的货品列表
     * @param  [type] $id      [上级ID]
     * @param  [type] $keyword [搜索关键字]
     * @return [type]          [description]
     */
    public function product(){
        $id      = trim(I('id'));//二级类别的id
        $keyword = (I('keyword')) ? trim(I('keyword')) : '';//搜索关键字

        $client = $this->client;

        $list = $client->_product($id,$keyword);
        
        // dump($list);
        $this->ajaxReturn($list);
    }

    //查出 所有类别列表
    public function cat_list(){
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/Orderlist');

        $cat_list = $client->cat_list($this->TranKd);//查询所有类别

        $cat_list = getTree($cat_list);//将普通数据转成树形结构
        getTree(null);//上面调用完一次后重置静态变量

        $naw = array();
        foreach($cat_list as $k=>$item){
            if($item['level'] == '0'){
                $naw[$item['id']] = $item;
            }
            
        }

        foreach($naw as $k1=>$v1){
            foreach($cat_list as $k2=>$v2){
                if($v2['fid'] == $v1['id']){
                    $naw[$k1]['child'][] = $v2;
                }
            }
        }

		return $naw;
    }

    public function import(){


        $excel = new \WebUser\PHPExcel\PHPExcel();

        $format = new \WebUser\PHPExcel\Format(array(
            'category_one',
            'category_two',
            'detail',
            'brand',
            'catname',
            'spec_unit',
            'amount',
            'is_suit',
            'price',
            'remark',
        ));

        $res = $excel->read($_FILES['file_two']['tmp_name']);
        $info = $format->exec($res, 4);
        foreach($info as $k=>$v){
            $info[$k]['is_suit'] = ($v['is_suit'] == '是' || $v['is_suit'] == 'yes' || $v['is_suit'] == 'y') ? 1 : 0;
        }
//        dump($info);die;

        echo \json_encode($info);
        die;

    }

}