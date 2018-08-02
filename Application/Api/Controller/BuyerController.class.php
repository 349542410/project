<?php
/**
 * 手机端 服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class BuyerController extends HproseController{
	protected $crossDomain =	true;
	protected $P3P		   =    true;
	protected $get		   =    true;
	protected $debug 	   =    true;

//==================================== Login ===================================================
	/**
	 * 登陆
	 * @param  [type] $map [条件语句]
	 * @return [type]      [description]
	 */
	public function is_login($username,$pwd){
        //Man 这里使用的是 mkil.MIS_User 与 ERP是不同的，150818
        $map['UserCode'] = $username;
        $map['IsUsed']   = '1';
        $map['IsBuyer']  = 1;
        $map['Password'] = md5(strtolower(trim($pwd)));

		$user = M()->table('MIS_User')->field('Id,UserCode,Name')->where($map)->find();
		if($user){
            return $arr = array('U'=>$user,'G'=>$this->group());
        }else{
            return $arr = array('U'=>false);
        }
        
	}
//==================================== Index 父级 顶部搜索栏===================================================

	public function group(){
		//品牌列表
		$brand = M()->table('MIS_Brand')->where(array('IsUsed'=>'1'))->order('Name')->select();
		//父级类别列表
		$category = M()->table('MIS_Category')->where(array('Tid'=>'0','IsUsed'=>'1'))->order('sort,CLevel')->select();
		//子级类别列表
        $condition['Tid'] = array('gt','0');
        $condition['IsUsed'] = array('eq','1');
		$cate = M()->table('MIS_Category')->where($condition)->order('sort,CLevel')->select();
        //仓库
		$warehouse = M()->table('MIS_Warehouse')->where(array('IsUsed'=>'1','IsPurchase'=>'1'))->order('WarehouseName')->select();
		$group = array(
			'brand'    =>$brand,
			'category' =>$category,
			'cate'     =>$cate,
		);
		return $group;
	}

	/**
	 * 获取数据列表信息
	 * @param  [type] $type  [查询类别]
	 * @param  [type] $where [查询条件]
	 * @param  [type] $page  [页码]
	 * @return [type]        [description]
	 */
	public function getList($Code,$Color,$Size,$brand,$categoryid,$warehouseid,$type,$page,$starttime,$endtime,$MKBuyer){
        if($brand)$where['Brand'] = $brand;

        if($categoryid){
            //id集  二维数组
            $ids = M()->table('MIS_Product')->field('Id')->where(array('CategoryId'=>$categoryid))->select();

            $arr = array();
            //如果$ids为true
            if($ids){
                foreach($ids as $key => $v){
                    foreach($v as $m => $n){
                        $arr[] = $n;    //id集 一维数组
                    }
                }
            }
            //如果$arr为true
            if($arr){
                $where['ProductId'] = array('in',$arr);
            }else{
                $where['ProductId'] = array('in','');
            }
        }

        if($warehouseid)$where['WarehouseId'] = $warehouseid;

        if(!empty($Code)){
            $where['MIS_AreDetail.Code'] = array('like','%'.$Code.'%');
        }
        if(!empty($Color)){
            $where['Color'] = array('like','%'.$Color.'%');
        }
        if(!empty($Size)){
            $where['Size'] = array('like','%'.$Size.'%');
        }

        $nowtime = date("Y-m-d H:i:s",time());        //获取当前时间

        // 如果搜索结束时间大于当前时间或结束时间不存在，则结束时间为当前时间
        if($endtime > $nowtime || !$endtime){
            $endtime = $nowtime;
        }else{
            $endtime = $endtime." 23:59:59";
        }

        //开始时间已被默认为该日期的0时0分0秒开始计算
        if($starttime && $endtime){
            $where['CreateDate'] = array('between',array($starttime,$endtime));
        }else if(!$starttime && $endtime){
            $where['CreateDate'] = array('elt',$endtime);
        }else if($starttime && !$endtime){
            $where['CreateDate'] = array('egt',$starttime);
        }

        $where['IsCancel']   = array('eq','0');   //是否取消，0表示未取消
        $where['IsPurchase'] = array('eq','1');   //是否为代购仓库，1表示是

		if($type == 'index'){
            $where['IsSend']     = array('eq','0');  //是否已发货
            $where['IsDeducted'] = array('eq','0');  //是否已扣库存
            $where['OrderType']  = array('eq','1');  //订购状态，1表示未订购

			$list = M()->table('MIS_AreDetail')
                        ->field("sum(Amount) as numbers,MIS_AreDetail.ProductName,MIS_AreDetail.Id,MIS_AreDetail.CreateDate,MIS_AreDetail.Brand,MIS_AreDetail.Code,Color,Size,MIS_Warehouse.WarehouseName,MIS_Product.MainImg")
                        ->join('LEFT JOIN MIS_Product ON MIS_AreDetail.ProductId=MIS_Product.Id')
                        ->join('LEFT JOIN MIS_Warehouse ON MIS_AreDetail.WarehouseId=MIS_Warehouse.Id')
                        ->group('MIS_AreDetail.ProductName,MIS_AreDetail.Brand,MIS_AreDetail.Code,ColorId,SizeId,WarehouseId')
                        ->where($where)->limit(($page-1)*20 .',20')->order('CreateDate ASC')->select();


		}else if($type == 'deliver'){
	        $where['OrderType']  = array('eq','3');  //订购状态，3为订购中
	        $where['GetOrderUserId']  = array('eq',$MKBuyer['mkid']);   //代购人id

			$list = M()->table('MIS_AreDetail')
                        ->field('sum(Amount) as numbers,MIS_AreDetail.ProductName,MIS_AreDetail.Id,MIS_AreDetail.CreateDate,MIS_AreDetail.Brand,MIS_AreDetail.Code,Color,Size,GetOrderDate,MIS_Warehouse.WarehouseName,MIS_Product.MainImg')
                        ->join('LEFT JOIN MIS_Product ON MIS_AreDetail.ProductId=MIS_Product.Id')
                        ->join('LEFT JOIN MIS_Warehouse ON MIS_AreDetail.WarehouseId=MIS_Warehouse.Id')
                        ->group('MIS_AreDetail.ProductName,MIS_AreDetail.Brand,MIS_AreDetail.Code,ColorId,SizeId,WarehouseId,GetOrderDate')
                        ->where($where)->limit(($page-1)*20 .',20')->order('GetOrderDate desc')->select();

		}else if($type == 'finished'){
	        $where['OrderType']  = array('eq','2');  //订购状态，2为已订购
	        $where['GetOrderUserId'] = array('eq',$MKBuyer['mkid']);   //代购人id

			$list = M()->table('MIS_AreDetail')
                        ->field('sum(Amount) as numbers,MIS_AreDetail.ProductName,MIS_AreDetail.Id,MIS_AreDetail.CreateDate,MIS_AreDetail.Brand,MIS_AreDetail.Code,Color,Size,GetOrderDate,DeliveryDate,MIS_Warehouse.WarehouseName,MIS_Product.MainImg')
                        ->join('LEFT JOIN MIS_Product ON MIS_AreDetail.ProductId=MIS_Product.Id')
                        ->join('LEFT JOIN MIS_Warehouse ON MIS_AreDetail.WarehouseId=MIS_Warehouse.Id')
                        ->group('MIS_AreDetail.ProductName,MIS_AreDetail.Brand,MIS_AreDetail.Code,ColorId,SizeId,WarehouseId,GetOrderDate,DeliveryDate')
                        ->where($where)->limit(($page-1)*20 .',20')->order('DeliveryDate desc')->select();

		}else if($type == 'mylist'){
	        $where['OrderType']  = array('gt','1');  //订购状态，2为已订购
	        $where['GetOrderUserId'] = array('eq',$MKBuyer['mkid']);   //代购人id

			// $list = M()->table('MIS_AreDetail')->field('sum(Amount) as numbers,MIS_AreDetail.ProductName,MIS_AreDetail.Id,MIS_AreDetail.CreateDate,MIS_AreDetail.Brand,MIS_AreDetail.Code,Color,Size,Amount,WarehouseId,GetOrderDate,DeliveryDate,MIS_Product.MainImg')->join('LEFT JOIN MIS_Product ON MIS_AreDetail.ProductId=MIS_Product.Id')->group('MIS_AreDetail.ProductName,MIS_AreDetail.Brand,MIS_AreDetail.Code,ColorId,SizeId,WarehouseId,DeliveryDate,GetOrderUserId,GetOrderDate')->where($where)->limit(($page-1)*10 .',10')->order('CreateDate asc,GetOrderDate desc,DeliveryDate desc')->select();
            $list = M()->table('MIS_AreDetail')
                        ->field('sum(Amount) as numbers,MIS_AreDetail.ProductName,MIS_AreDetail.Id,MIS_AreDetail.CreateDate,MIS_AreDetail.Brand,MIS_AreDetail.Code,Color,Size,GetOrderDate,DeliveryDate,MIS_Warehouse.WarehouseName,MIS_Product.MainImg')
                        ->join('LEFT JOIN MIS_Product ON MIS_AreDetail.ProductId=MIS_Product.Id')
                        ->join('LEFT JOIN MIS_Warehouse ON MIS_AreDetail.WarehouseId=MIS_Warehouse.Id')
                        ->group('MIS_AreDetail.ProductName,MIS_AreDetail.Brand,MIS_AreDetail.Code,ColorId,SizeId,WarehouseId,GetOrderDate,OrderType')
                        ->where($where)->limit(($page-1)*20 .',20')->order('GetOrderDate desc,DeliveryDate asc')->select();
		}
		return $list;
	}

//=================================== Details ================================================

	/**
	 * 待订购Detail
	 * @param [type] $Id [description]
	 */
	public function IndexDetail($Id){
		$info = M()->table('MIS_AreDetail')->join('LEFT JOIN MIS_Warehouse ON MIS_AreDetail.WarehouseId=MIS_Warehouse.Id')->where(array('MIS_AreDetail.Id'=>$Id))->find();
        if(!$info){
            $result = array('do'=>'none');
            return $result;
        }
        //把这些特征赋值为条件查询语句
        $map['ProductName'] = $info['ProductName'];
        $map['Brand']       = $info['Brand'];
        $map['Code']        = $info['Code'];
        $map['ColorId']     = $info['ColorId'];
        $map['SizeId']      = $info['SizeId'];
        $map['WarehouseId'] = $info['WarehouseId'];
        $map['OrderType']   = 1;
        $map['IsCancel']    = 0;
        $map['IsSend']      = 0;  //是否已发货
        $map['IsDeducted']  = 0;  //是否已扣库存
        
        $count = M()->table('MIS_AreDetail')->where($map)->count();		//计算出包含有这些特征的数据的总数
        $result	= array('info'=>$info,'count'=>$count);
        return $result;
	}

	/**
	 * 待发货Detail
	 * @param [type] $Id     [description]
	 * @param [type] $MKBuyer [description]
	 */
	public function DeliverDetail($Id,$MKBuyer){
		$info = M()->table('MIS_AreDetail')->join('LEFT JOIN MIS_Warehouse ON MIS_AreDetail.WarehouseId=MIS_Warehouse.Id')->where(array('MIS_AreDetail.Id'=>$Id))->find();
        if(!$info){
            $result = array('do'=>'none');
            return $result;
        }
        if($info['GetOrderUserId'] != $MKBuyer['mkid']){     //判断该单号的代购者id是否为当前登陆者的id
            $result = array('do'=>'no');
            return $result;
        }
        //把这些特征赋值为条件查询语句
        $map['ProductName']    = $info['ProductName'];
        $map['Brand']          = $info['Brand'];
        $map['Code']           = $info['Code'];
        $map['ColorId']        = $info['ColorId'];
        $map['SizeId']         = $info['SizeId'];
        $map['WarehouseId']    = $info['WarehouseId'];
        $map['OrderType']      = $info['OrderType'];
        $map['IsCancel']       = $info['IsCancel'];
        $map['GetOrderUserId'] = $info['GetOrderUserId'];
        $map['GetOrderDate']   = $info['GetOrderDate'];

        $count = M()->table('MIS_AreDetail')->where($map)->count();		//计算出包含有这些特征的数据的总数
        $result	= array('info'=>$info,'count'=>$count);
        return $result;   
	}

	/**
	 * 已出库Detail
	 * @param [type] $Id     [description]
	 * @param [type] $MKBuyer [description]
	 */
	public function FinishedDetail($Id,$MKBuyer){
		$info = M()->table('MIS_AreDetail')->join('LEFT JOIN MIS_Warehouse ON MIS_AreDetail.WarehouseId=MIS_Warehouse.Id')->where(array('MIS_AreDetail.Id'=>$Id))->find();
        if(!$info){
            $result = array('do'=>'none');
            return $result;
        }
        if($info['GetOrderUserId'] != $MKBuyer['mkid']){     //判断该单号的代购者id是否为当前登陆者的id
            $result = array('do'=>'no');
            return $result;
        }
        //把这些特征赋值为条件查询语句
        $map['ProductName']    = $info['ProductName'];
        $map['Brand']          = $info['Brand'];
        $map['Code']           = $info['Code'];
        $map['ColorId']        = $info['ColorId'];
        $map['SizeId']         = $info['SizeId'];
        $map['WarehouseId']    = $info['WarehouseId'];
        $map['OrderType']      = $info['OrderType'];
        $map['IsCancel']       = $info['IsCancel'];
        $map['GetOrderUserId'] = $info['GetOrderUserId'];
        $map['GetOrderDate']   = $info['GetOrderDate'];
        $map['DeliveryDate']   = $info['DeliveryDate'];

        $count = M()->table('MIS_AreDetail')->where($map)->count();		//计算出包含有这些特征的数据的总数
        $result	= array('info'=>$info,'count'=>$count);
        return $result;
	}

	/**
	 * 购买记录Detail
	 * @param [type] $Id     [description]
	 * @param [type] $MKBuyer [description]
	 */
	public function MylistDetail($Id,$MKBuyer){
		$info = M()->table('MIS_AreDetail')->join('LEFT JOIN MIS_Warehouse ON MIS_AreDetail.WarehouseId=MIS_Warehouse.Id')->where(array('MIS_AreDetail.Id'=>$Id))->find();
        if(!$info){
            $result = array('do'=>'none');
            return $result;
        }
        if($info['GetOrderUserId'] != $MKBuyer['mkid']){     //判断该单号的代购者id是否为当前登陆者的id
            $result = array('do'=>'no');
            return $result;
        }
        //把这些特征赋值为条件查询语句
        $map['ProductName']    = $info['ProductName'];
        $map['Brand']          = $info['Brand'];
        $map['Code']           = $info['Code'];
        $map['ColorId']        = $info['ColorId'];
        $map['SizeId']         = $info['SizeId'];
        $map['WarehouseId']    = $info['WarehouseId'];
        $map['OrderType']      = $info['OrderType'];
        $map['IsCancel']       = $info['IsCancel'];
        $map['GetOrderUserId'] = $info['GetOrderUserId'];
        $map['GetOrderDate']   = $info['GetOrderDate'];
        if($info['OrderType'] == '2'){
            $map['DeliveryDate']   = $info['DeliveryDate'];
        }

        $count = M()->table('MIS_AreDetail')->where($map)->count();		//计算出包含有这些特征的数据的总数
        $result	= array('info'=>$info,'count'=>$count);
        return $result;
	}



//=================================== Ajax操作 ====================================================

	/**
	 * 订购 Ajax
	 * @param  [type] $id     [样本id]
	 * @param  [type] $num    [订购数量]
	 * @param  [type] $MKBuyer [当前登陆的用户信息]
	 * @return [type]         [description]
	 */
	public function toBuy($id,$num,$MKBuyer){

        $check = M()->table('MIS_AreDetail')->where(array('Id'=>$id))->find();  //查出此样板Id的数据的特征

        //判断该单号是否已被代购
        if($check['GetOrderUserId'] != '' || $check['GetOrderDate'] != '' || $check['OrderType'] != '1'){
        	$result = array('do'=>'no','typeId'=>'1');
            return $result;
        }

        //把这些特征赋值为条件查询语句
        $map['ProductName'] = $check['ProductName'];
        $map['Brand']       = $check['Brand'];
        $map['Code']        = $check['Code'];
        $map['ColorId']     = $check['ColorId'];
        $map['SizeId']      = $check['SizeId'];
        $map['WarehouseId'] = $check['WarehouseId'];
        $map['OrderType']   = $check['OrderType'];
        $map['IsCancel']    = $check['IsCancel'];
        $map['IsSend']      = $check['IsSend'];;  //是否已发货
        $map['IsDeducted']  = $check['IsDeducted'];;  //是否已扣库存

        $arr = M()->table('MIS_AreDetail')->field('Id')->where($map)->select();  //根据条件查找符合要求的总数据，以二维数组形式显示

        foreach($arr as $key=>$v){
            $ids[] = $v['Id'];  //取出id集
        }

        $count = M()->table('MIS_AreDetail')->where($map)->count(); //计算符合要求的数据总数

        if($num > '0' && $num <= $count){
            $num = $num;
        }else if($num == '0' ){
        	$result = array('do'=>'no','typeId'=>'2');
            return $result;
        }else if($num > '0' && $num > $count){
            $num = $count;
        }else{
        	$result = array('do'=>'no','typeId'=>'3');
            return $result;
        }

        //如果订购状态为1，即未订购，并且该单未取消，代购人id为空时，可进行订购操作
        if($check['OrderType'] == 1 && $check['IsCancel'] == 0 && $check['GetOrderUserId'] == ''){
            $data2['OrderType']      = 3;       //临时性标记状态，也表示已经被代购
            $data2['GetOrderUserId'] = $MKBuyer['mkid'];      //代购人id=>当前操作员id
            $data2['GetOrderUser']   = $MKBuyer['mkname'];      //代购人id=>当前操作员名字
            $data2['GetOrderDate']   = date("Y-m-d H:i:s",time());   //代购日期时间=>当前时间

            $where['Id'] = array('in',$ids);

            $res = M()->table('MIS_AreDetail')->where($where)->order('CreateDate ASC')->limit($num)->save($data2);   //更新

            if($res){       //如果更新成功
            	$result = array('do'=>'yes','typeId'=>'1','res'=>$res);
                return $result;
            }else{          //如果更新失败
            	$result = array('do'=>'no','typeId'=>'4');
                return $result;
            }
        }else {
            // if($check['OrderType'] != 1 && $check['OrderType'] != 0 && $check['IsCancel'] == 0 || $check['IsCancel'] == 1 && $check['GetOrderUserId'] == '')
            //如果订购状态不为1，也不为0时，并且该单未取消，销售人id为空时，可禁止订购操作，返回错误信息
        	$result = array('do'=>'no','typeId'=>'5');
            return $result;
        }

	}

	/**
	 * 发货 Ajax（默认发货数量为该订单的订购数量）
	 * @param  [type] $id      [样本id]
	 * @param  [type] $MKBuyer  [当前登陆的用户信息]
	 * @return [type]          [description]
	 */
	public function toSend($id,$MKBuyer){

        $check = M()->table('MIS_AreDetail')->where(array('Id'=>$id))->find();  //查出此样板Id的数据的特征

        //判断该单号的代购者id是否为当前登陆者的id
        if($check['GetOrderUserId'] != $MKBuyer['mkid']){
        	$result = array('do'=>'no','typeId'=>'1');
            return $result;
        }

        //把这些特征赋值为条件查询语句
        $map['ProductName']    = $check['ProductName'];
        $map['Brand']          = $check['Brand'];
        $map['Code']           = $check['Code'];
        $map['ColorId']        = $check['ColorId'];
        $map['SizeId']         = $check['SizeId'];
        $map['WarehouseId']    = $check['WarehouseId'];
        $map['OrderType']      = $check['OrderType'];
        $map['IsCancel']       = $check['IsCancel'];
        $map['GetOrderUserId'] = $MKBuyer['mkid'];   		//当前登陆的id
        $map['GetOrderUser']   = $MKBuyer['mkname'];   	//当前登陆用户的真实姓名
        $map['GetOrderDate']   = $check['GetOrderDate'];

        $arr = M()->table('MIS_AreDetail')->field('Id')->where($map)->select();  //根据条件查找符合要求的总数据，以二维数组形式显示

        foreach($arr as $key=>$v){
            $ids[] = $v['Id'];  //取出id集
        }

        //如果订购状态为3，即已订购但未发货，并且该单未取消，代购人id为当前登陆的用户的id时，可进行发货操作
        if($check['OrderType'] == 3 && $check['IsCancel'] == 0 && $check['GetOrderUserId'] == $MKBuyer['mkid']){
            $data2['OrderType']    = 2;       //改为已订购状态
            $data2['DeliveryDate'] = date("Y-m-d H:i:s",time());   //发货日期时间=>当前时间

            $where['Id'] = array('in',$ids);

            $res = M()->table('MIS_AreDetail')->where($where)->save($data2);   //更新

            if($res){       //如果更新成功
	        	$result = array('do'=>'yes','typeId'=>'1','res'=>$res);
	            return $result;
            }else{          //如果更新失败
	        	$result = array('do'=>'no','typeId'=>'2');
	            return $result;
            }
        }else {
        	// if($check['GetOrderUserId'] != $MKBuyer['mkid'] || $check['OrderType'] != 3 || $check['IsCancel'] == 1)
        	$result = array('do'=>'no','typeId'=>'3');
            return $result;
        }
	}

	/**
	 * 取消 Ajax
	 * @param  [type] $id     [样本id]
	 * @param  [type] $num    [取消数量]
	 * @param  [type] $MKBuyer [当前登陆的用户信息]
	 * @return [type]         [description]
	 */
	public function toCancel($id,$num,$MKBuyer){

			$check = M()->table('MIS_AreDetail')->where(array('Id'=>$id))->find();
            //判断该单号的代购者id是否为当前登陆者的id
            if($check['GetOrderUserId'] != $MKBuyer['mkid']){
	        	$result = array('do'=>'no','typeId'=>'1');
	            return $result;
            }

            //把这些特征赋值为条件查询语句
			$map['ProductName']    = $check['ProductName'];
			$map['Brand']          = $check['Brand'];
			$map['Code']           = $check['Code'];
			$map['ColorId']        = $check['ColorId'];
			$map['SizeId']         = $check['SizeId'];
			$map['WarehouseId']    = $check['WarehouseId'];
			$map['OrderType']      = $check['OrderType'];
			$map['IsCancel']       = $check['IsCancel'];
			$map['GetOrderUserId'] = $MKBuyer['mkid'];   		//当前登陆的id
			$map['GetOrderUser']   = $MKBuyer['mkname'];   	//当前登陆用户的真实姓名
			$map['GetOrderDate']   = $check['GetOrderDate'];

            $arr = M()->table('MIS_AreDetail')->field('Id')->where($map)->select();   //根据条件查找符合要求的总数据，以二维数组形式显示

            foreach($arr as $key=>$v){
                $ids[] = $v['Id'];  //取出id集
            }
            $count = M()->table('MIS_AreDetail')->where($map)->count(); //计算符合要求的数据总数

            if($num > '0' && $num <= $count){
                $num = $num;
            }else if($num == '0' ){
	        	$result = array('do'=>'no','typeId'=>'2');
	            return $result;
            }else if($num > '0' && $num > $count){
                $num = $count;
            }else{
	        	$result = array('do'=>'no','typeId'=>'3');
	            return $result;
            }
            //如果订购状态为3，即已订购但未发货，并且该单未取消，代购人id为当前登陆的用户的id时，可进行取消操作
            if($check['OrderType'] == 3 && $check['IsCancel'] == 0 && $check['GetOrderUserId'] == $MKBuyer['mkid']){
                $data2['OrderType']      = 1;      //改为未订购状态
                $data2['GetOrderUserId'] = null;   //代购人id
                $data2['GetOrderUser']   = null;   //代购人名字
                $data2['GetOrderDate']   = null;   //代购日期时间=>当前时间

                $where['Id'] = array('in',$ids);
                $res = M()->table('MIS_AreDetail')->where($where)->order('CreateDate desc')->limit($num)->save($data2);    //更新

                if($res){       //如果更新成功
		        	$result = array('do'=>'yes','typeId'=>'1','res'=>$res);
		            return $result;

                }else{          //如果更新失败
		        	$result = array('do'=>'no','typeId'=>'4');
		            return $result;

                }
            }else {
	        	$result = array('do'=>'no','typeId'=>'5');
	            return $result;

            }

	}

}