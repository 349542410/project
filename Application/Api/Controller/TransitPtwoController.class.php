<?php
/**
 * 美快后台快递公司管理  服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class TransitPtwoController extends HproseController{
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

	/**
	 * 查总数 中转线路
	 * @param  [type] $where [description]
	 * @return [type]        [description]
	 */
    public function _center($where,$p,$ePage){
        $list = M('TransitCenter tc')->field('tc.*,ec.company_name')->join('LEFT JOIN mk_express_company ec ON tc.airid = ec.id')->where($where)->order('id asc,ctime desc')->page($p.','.$ePage)->select();
		
    	$count = M('TransitCenter tc')->join('LEFT JOIN mk_express_company ec ON tc.airid = ec.id')->where($where)->count();

    	return array('count'=>$count, 'list'=>$list);
    }

    /**
     * 航空公司列表
     * @return [type] [description]
     */
    public function ec(){
    	$elist = M('ExpressCompany')->where(array('status'=>'1'))->select();
    	return $elist;
    }

    /**
     * 查看  中转线路
     * @return [type] [description]
     */
    public function center_info($map,$type=''){

    	if($type == '2'){	//如果是修改页面请求的
    		$info = M('TransitCenter')->where($map)->find();
    	}else{
    		$info = M('TransitCenter tc')->field('tc.*,ec.company_name')->join('LEFT JOIN mk_express_company ec ON tc.airid = ec.id')->where($map)->find();
    	}
        
        return $info;
    }

    /**
     * 添加  中转线路
     * @return [type] [description]
     */
    public function center_add($arr,$creater){

        $m = D('TransitCenter');

        if($m->create($arr)){

            foreach($arr as $key=>$v){
                $data[$key] = trim($v);
            }
            
            $data['creater'] = $creater;
            $data['ctime']   = date('Y-m-d H:i:s');

            $res = M('TransitCenter')->add($data);
            
            if($res){
                $result = array('state'=>'yes', 'msg'=>'添加成功');
            }else{
                $result = array('state'=>'no', 'msg'=>'添加失败');
            }
            return $result;

        }else{
            $msg = $m->getError();
            $result = array('state'=>'no','msg'=>$msg);
            return $result;
        }

    }

    /*====================================线路地区开		start=======================================================================================*/
    /**
     * 线路地区列表
     * Enter description here ...
     */
    public function line_area($data){
    	if(!empty($data['area_id'])){
    		$pid = $data['area_id'];
    	}else{
    		$pid = 0;
    	}
    	//取得线路信息
    	$line_id = $data['line_id'];
    	$line = M('zcode_line')->where('id = '.$line_id.' ')->find();  
    	//取得线路地区信息
    	$where['line_id'] = $line_id;
    	$where['pid'] = $pid;
    	$area = M('zcode_line')->where($where)->select();
    	
    	//获取线路信息
    	$transit_center = M('transit_center')->field('id, name')->where('status = 1')->select();
    	$res['transit_center'] = $transit_center;
    	
    	$res['line'] 		= $line;
    	$res['zcode_line'] 	= $area;
    	
    	return $res;
    	
    	
    }

    
    /**
     * 线路地区列表添加
     * Enter description here ...
     */
    public function line_area_add(){
    	
    
    }
    

    /**
     * 线路地区列表添加处理
     * Enter description here ...
     */
    public function line_area_handle($data){
    	if(!empty($data['id'])){
    		//检验地区状态是否一致
    		$line_status = M('zcode_line')->field('status')->where('id = '.$data['id'].' ')->find();
    		//不一致 则修改地区状态
    		if($data['status'] != $line_status['status']){
    			$res = D('ZcodeLine')->zcodeline($data['id'], $r = array(), $data['line_id']);
    			$dat['status'] = $data['status'];
    			$wh['id'] = array('in', $res);
    			M('zcode_line')->data($dat)->where($wh)->save();
    			
    		}
    		$res = M('zcode_line')->data($data)->where('id = '.$data['id'].' ')->save();
    	}else{
    		$res = M('zcode_line')->data($data)->add();
    	}
    	return $res;
    }
    
    
    
    public function line_area_up($data, $line_id, $pid){
    	set_time_limit(0);
    	//return $data;
	    //声明三个数组保存省市区  作用：验证是否需要提交
	    $province = array();
	    $city = array();
	    $area = array();
	    if(!empty($pid)){
	    	$pid_one = M('zcode_line')->where('id = '.$pid.' ')->find();
	    	if(empty($pid_one)){
	    		$province[$pid_one['id']] = $pid_one['name'];
	    	}else{
	    		$pid_two = M('zcode_line')->where('id = '.$pid_one['pid'].' ')->find();
	    		if(empty($pid_two['pid'])){
	    			$province[$pid_two['id']] = $pid_two['name'];
	    			$city[$pid_one['id']] = $pid_one['name'];
	    		}else{
	    			$pid_three = M('zcode_line')->where('id = '.$pid_two['pid'].' ')->find();
	    			if(empty($pid_three['pid'])){
	    				$province[$pid_three['id']] = $pid_three['name'];
	    				$city[$pid_two['id']] = $pid_two['name'];

                        //更改后函数  地区第三级可能会存在重复
	    				$three_name = $pid_three['name'];
	    				$three_pid = $pid_three['pid'];
                        $three_name = $three_name . $three_pid;
                        $area[$pid_three['id']] = $three_name;
	    				
	    			}
	    		}
	    	}
	    	
	    }
	    

	    foreach ($data as $k => $v){
    		if($k > 0){
				if(!in_array($v['0'], $province)){
					$da['line_id'] 	= $line_id;
					$da['name'] 	= $v['0'];
					$da['zipcode'] 	= (string)$v['3'];
					$da['status']	= 1;
					$da['pid']		= $pid;

					//usleep(10);
					$province_id = M('zcode_line')->data($da)->add();
					$province[$province_id] = $v['0'];
				}else{
					$province_id = array_search($v['0'], $province);
				}
				
    			if(!in_array($v['1'], $city) && !empty($province_id)){
					$da['line_id'] 	= $line_id;
					$da['name'] 	= $v['1'];
					$da['zipcode'] 	= (string)$v['3'];
					$da['status']	= 1;
					$da['pid']		= $province_id;
					//usleep(10);
					$city_id = M('zcode_line')->data($da)->add();
					$city[$city_id] = $v['1'];
					//$i++;
					
				}else{
					$city_id = array_search($v['1'], $city);
				}

                $area_name = $v['2'];
                $area_name =  $area_name . $city_id;
                //原函数
                //if (!in_array($v['2'], $area) && !empty($city_id)){
                if (!in_array($area_name, $area) && !empty($city_id)){
					$da['line_id'] 	= $line_id;
					$da['name'] 	= $v['2'];
					$da['zipcode'] 	= (string)$v['3'];
					$da['status']	= 1;
					$da['pid']		= $city_id;
					//usleep(10);
					$area_id = M('zcode_line')->data($da)->add();
                    //原来的
					//$area[$area_id] = $v['2'];
                    //修改后的  因为地区名称可能存在重复
                    $area_name = $v['2'];
					$area[$area_id] = $area_name . $city_id;
					//$i++;
					
				}
    		}
    	}
    	set_time_limit(30);
    	
    	return true;
    	
    }
    
    
    
    /**
     * 线路地区列表修改
     * Enter description here ...
     */
    public function line_area_edit($data){
    	$rek = M('zcode_line')->where('id = '.$data['area_id'].' ')->find();
        if(!empty($rek['pid'])){
    		$pid = $rek['pid'];
    	}else{
    		$pid = 0;
    	}
    	//取得线路地区信息
    	$where['line_id'] = $data['line_id'];
    	$where['id'] = $pid;
    	$area = M('zcode_line')->where($where)->select();
    	$res['area'] = $area;
    	$res['line'] = $rek;
    	
    	return $res;
    	
    }    
    
    
    /**
     * 线路地区删除
     * Enter description here ...
     */
    public function line_area_delete($data){
    	set_time_limit(0);
    	if(!empty($data['zcode_id'])){
	    	$res = D('ZcodeLine')->zcodeline($data['zcode_id'], $r = array(), $data['line_id']);
//	    	if(!empty($data['zcode_id'])){
//	    		array_push($res, $data['zcode_id']);
//	    	}
//			$da['id'] = array('in', $res);
			//当下级为空的时候才能删除
			if(!empty($res)){
				$rek = array();
				return $rek;
			}else{
				$da['id'] = $data['zcode_id'];
			}
	    	
    	}else{
    		$da['line_id'] = $data['line_id'];
    	}	
    	$rek = M('zcode_line')->where($da)->delete();
    	//$rek =  M('zcode_line')->delete($zcode_line);
    	set_time_limit(30);
    	return $rek;
    }    
        
    
    /**
     * 线路复制
     * Enter description here ...
     */
    public function line_copy($data){
    	if(!empty($data['line_id']) && empty($data['province'])){
    		$province['line_id'] = $data['line_id'];
    		$province['pid'] = 0;
    		$res['province'] = M('zcode_line')->field('id, line_id, name')->where($province)->select();
    	}
    	if(!empty($data['province']) && empty($data['city'])){
    		$city['line_id'] = $data['line_id'];
    		$city['pid'] = $data['province'];
    		$res['city'] = M('zcode_line')->field('id, line_id, name')->where($city)->select();
    	}
    	if(!empty($data['city'])){
    		$area['line_id'] = $data['line_id'];
    		$area['pid'] = $data['city'];
    		$res['area'] = M('zcode_line')->field('id, line_id,  name')->where($area)->select();
    		
    	}
    	return $res;
    	
    }
        
    
    /**
     * 线路复制处理
     * Enter description here ...
     */
    public function line_copy_handle($data){
    	set_time_limit(0);
    	
    	if(!empty($data['area_id'])){
    		$rek = D('ZcodeLine')->zcodeline($data['area_id'], $rew = array(), $data['line_zcode']);
    		//if(empty($rek)){
	    		array_push($rek, $data['city_id'], $data['province_id'], $data['area_id']);
	    		$where['id'] = array('in', $rek);
	    		$where['line_id'] = $data['line_zcode'];
	    		$res = M('zcode_line')->where($where)->select();
    		//}else{
    		//	$res = array();
    		//}
    		
    	}elseif (!empty($data['city_id']) && empty($data['area_id'])){
    		$rek = D('ZcodeLine')->zcodeline($data['city_id'], $rew = array(), $data['line_zcode']);
    		if(!empty($rek)){
	    		array_push($rek, $data['city_id'], $data['province_id']);
	    		$where['id'] = array('in', $rek);
	    		$where['line_id'] = $data['line_zcode'];
	    		$res = M('zcode_line')->where($where)->select();
    		}else{
    			$res = array();
    		}
    	}elseif (!empty($data['province_id']) && empty($data['city_id'])){
    		$rek = D('ZcodeLine')->zcodeline($data['province_id'], $r = array(), $data['line_zcode']);
    		if(!empty($rek)){
	    		array_push($rek, $data['province_id']);
	    		$where['id'] = array('in', $rek);
	    		$where['line_id'] = $data['line_zcode'];
	    		$res = M('zcode_line')->where($where)->select();
    		}else{
    			$res = array();
    		}
    	}else{
    		//$rek = D('ZcodeLine')->zcodeline($pid = 0, $r = array(), $data['line_zcode']);
    		//return $rek;
    		//array_push($rek, $data['province_id']);
//    		if(!empty($rek)){
//	    		$where['id'] = array('in', $rek);
//	    		$where['line_id'] = $data['line_zcode'];
//	    		$res = M('zcode_line')->where($where)->select();
//    		}else{
//    			$res = array();
//    		}
			$where['line_id'] = $data['line_zcode'];
	    	$res = M('zcode_line')->where($where)->select();
    			
    	}
    	
    	if(empty($res)){
    		return $arr = false;
    	}
    	//return $res;
    	foreach ($res as $key => $val){
    		if($val['pid'] == 0){
	    		$rew_one['line_id'] 		= $data['line_id'];
	    		$rew_one['status'] 			= $val['status'];
	    		$rew_one['name']			= $val['name'];
	    		//$rew_one['name']	· 		= $val['name'];		//使用这行代码会报错 
	    		$rew_one['alias_name'] 		= $val['alias_name'];
	    		$rew_one['zipcode'] 		= $val['zipcode'];
	    		$rew_one['pid'] 			= 0;
	    		$rew_id = M('zcode_line')->data($rew_one)->add();
				foreach ($res as $k => $v){
	    			if($val['id'] == $v['pid']){
	    				$rew_two['line_id'] 		= $data['line_id'];
			    		$rew_two['status']  		= $v['status'];
			    		$rew_two['name']			= $v['name'];
			    		//$rew_two['name'] 	· 		= $v['name'];		//使用这行代码会报错
			    		$rew_two['alias_name'] 		= $v['alias_name'];
			    		$rew_two['zipcode'] 		= $v['zipcode'];
			    		$rew_two['pid'] 			= $rew_id;
			    		$rek_id = M('zcode_line')->data($rew_two)->add();
			    		foreach ($res as $ks => $vs){
			    			if($v['id'] == $vs['pid']){
			    				$rew_three['line_id'] 		= $data['line_id'];
					    		$rew_three['status']  		= $vs['status'];
					    		$rew_three['name']			= $vs['name'];
					    		//$rew_three['name'] 	· 		= $vs['name'];		//使用这行代码会报错
					    		$rew_three['alias_name'] 	= $vs['alias_name'];
					    		$rew_three['zipcode'] 		= $vs['zipcode'];
					    		$rew_three['pid'] 			= $rek_id;
					    		$rea_id = M('zcode_line')->data($rew_three)->add();
					    		unset($res[$ks]);	
			    			}
			    		}
			    		unset($res[$k]);
			    		usleep(100);
	    			}
	    		}
	    		unset($res[$key]);
	    		usleep(100);
	   		}
    	}
    	set_time_limit(30);
    	return $arr = true;
    	
    	
    }
    
    /**
     * 根据线路地址查询地址邮编
     * Enter description here ...
     * @param unknown_type $data
     */
    public function zcode_line_api($data){
    	//return $data;
    	if(!empty($data['province'])){
    		$province =  array('like',''.$data['province'].'%');
    		$where['zl.name|zl.alias_name'][] = $province;
    	}
    	if(!empty($data['city'])){
    		$city =  array('like',''.$data['city'].'%');
    		$where['zl.name|zl.alias_name'][] = $city;
    	}
    	if(!empty($data['area'])){
    		$area =  array('like',''.$data['area'].'%');
    		$where['zl.name|zl.alias_name'][] = $area;
    	}
    	array_push($where['zl.name|zl.alias_name'], 'OR');
    	$where['zl.line_id'] = $data['line_id'];
    	//return $where;
    	//$res = M('zcode_line')->alias('zl')->field('zl.*, tc.status')->join('left join mk_transit_center AS tc ON zl.line_id = tc.id ')->where($where)->select();
		$res = M('zcode_line')->alias('zl')->field('zl.*')->where($where)->select();
		//return $res;
    	foreach ($res as $key => $val){
    		if(0 == $val['pid']){
    			//判断下级是否存在
    			$zcode[$key][0] = $val;
    			//二级地区存在$res
    			foreach ($res as $ks => $vs){
//    				if($ks == 3){
//    					return $vs;
//    				}
    				if($val['id'] != $vs['id']){
    					if($vs['pid'] == $val['id']){
    						$zcode[$key][1] = $vs;
    						$zcode_two = $vs; 
    					}
    					
    				}   				
    				if(isset($zcode_two)){
    					//三级地区存在存在$res
    					foreach ($res as $k => $v){
    						if($zcode_two['id'] != $v['id'] && $val['id'] != $v['id']){
    							if(in_array($v, $zcode_two['id'])){
    								$zcode[$key][2] = $v;
    								return $zcode;
    							}
    						}
    					}
    					//三级地区不存在存在$res
    					$wthree['zl.pid'] = $zcode_two['id'];
    					$wthree['zl.line_id'] = $data['line_id'];
    					//$zthree = M('zcode_line')->alias('zl')->field('zl.*, tc.status as line_status')->join('left join mk_transit_center AS tc ON zl.line_id = tc.id ')->where($wthree)->select();
    					$zthree = M('zcode_line')->alias('zl')->field('zl.*')->where($wthree)->select();
    					if(!empty($zthree)){
    						foreach ($zthree as $kay => $valt){
    							foreach ($res as $kaw => $vaw){
    								if($valt['id'] == $vaw['id']){
    									$zcode[$key][2] = $valt;
    									return $zcode;		
    								}
    							}
    						}
    						
    					}
    					unset($zcode_two);
    				}
    				//return $res;
    			}
    			//return $res;
    			//二级地区不存在$res
    			if(empty($zcode[$key][1])){
    				//查询二级
    				$wtwo['zl.pid'] = $val['id'];
    				$wtwo['zl.line_id'] = $data['line_id'];
    				//$ztwo = M('zcode_line')->alias('zl')->field('zl.*, tc.status as line_status')->join('left join mk_transit_center AS tc ON zl.line_id = tc.id ')->where($wtwo)->select();
    				$ztwo = M('zcode_line')->alias('zl')->field('zl.*')->where($wtwo)->select();
    				//return $res;
    				if(!empty($ztwo)){
    					//保存二级地区
    					//$zcode[$key][1] = $ztwo;
    					//三级地区存在$res
    					//return $res;
    					foreach ($ztwo as $kss => $vss){
	    					foreach ($res as $ke => $ve){
	    						//$res 存在第三级数据
	    						if($vss['id'] == $ve['pid']){
    								$zcode[$key][1] = $vss;
    								$zcode[$key][2] = $ve;
    								return $zcode;
	    						}
	    						//$res 不存在第三级数据  则返回存在第二级的数据
	    						if($vss['id'] == $ve['id']){
	    							$zcode[$key][1] = $vss;
    								return $zcode;
	    						}
	    						
	    						
	    					}
    					}
    					//if(!empty($zcode[$key][2])){
    					//	return $zcode;
    					//}
    					//三级地区不存在$res
//    					if(empty($zcode['$key'][2])){
//	    					$wthree['zl.pid'] = $ztwo['id'];
//	    					$wthree['zl.line_id'] = $data['line_id'];
//	    					//return $wthree;
//	    					$zthree = M('zcode_line')->alias('zl')->field('zl.*, tc.status as line_status')->join('left join mk_transit_center AS tc ON zl.line_id = tc.id ')->where($wthree)->find();
//	    					if(!empty($zthree)){
//	    						$zcode[$key][2] = $zthree;
//	    					}
//    					}
    				}
    				
    			}
    			
    			//return $zcode;
    		}
    		
    		else{
    			//当前数据在三级地区表中的第二第三层数据位置
    			
    			//查询当前数据上一等级
    			$wheres['zl.id'] = $val['pid'];
    			$wheres['zl.line_id'] = $data['line_id'];
    			//$rek = M('zcode_line')->alias('zl')->field('zl.*, tc.status as line_status')->join('left join mk_transit_center AS tc ON zl.line_id = tc.id ')->where($wheres)->find();
    			$rek = M('zcode_line')->alias('zl')->field('zl.*')->where($wheres)->find();
    			if(!empty($rek)){
    				//确定当前数据在第二层位置
    				if(0 == $rek['pid']){
    					$zcode[$key][0] = $rek;
    					$zcode[$key][1] = $val;
    					$wheret['zl.pid'] = $val['id'];
    					$wheret['zl.line_id'] = $data['line_id'];
    					//return $wheret;
    					//$rew =  M('zcode_line')->alias('zl')->field('zl.*, tc.status as line_status')->join('left join mk_transit_center AS tc ON zl.line_id = tc.id ')->where($wheret)->select();
    					$rew =  M('zcode_line')->alias('zl')->field('zl.*')->where($wheret)->select();
    					
    					foreach ($rew as $keys => $vals){
    						foreach ($res as $ka => $va){
    							if($vals['id'] == $va['id']){
    								$zcode[$key][2] = $vals;
    								return $zcode;		
    							}
    						}
    					}
    					
//    					return $rew;
//    					if(!empty($rew)){
//    						$zcode[$key][2] = $rew;
//    						return $zcode;
//    					}
    				}
    				//确定当前数据在第三层位置
    				else{
    					//查询当前数据上二等级
    					$wherets['zl.id'] = $rek['pid'];
    					$wherets['zl.line_id'] = $data['line_id'];
    					//$rews =  M('zcode_line')->alias('zl')->field('zl.*, tc.status as line_status')->join('left join mk_transit_center AS tc ON zl.line_id = tc.id ')->where($wherets)->find();
    					$rews =  M('zcode_line')->alias('zl')->field('zl.*')->where($wherets)->find();
    					
    					if(!empty($rews)){
    						if(0 == $rews['pid']){
    							$zcode[$key][0] = $rews;
    							$zcode[$key][1] = $rek;
    							$zcode[$key][2] = $val;
    							return $zcode;
    							
    						}
    					}
    				}
    			}
    		}
	   	}
    	
    	
    	return array();
    	
    }
    
    public function line_api(){
    	$res = M('transit_center')->field('id, name')->where('status = 1')->select();
    	return $res;
    }
    
    
    
    /*====================================线路地区开		end=======================================================================================*/

    /*====================================线路日志保存       start=============================================*/
    public function upload_add_log($data){
        $res = M('line_template_log')->add($data);
        return $res;
    }
    /*====================================线路日志保存       end=============================================*/



}