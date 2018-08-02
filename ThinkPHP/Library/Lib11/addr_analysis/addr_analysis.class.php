<?php

/**
 * 利用前缀树分词
 * liao ya di
 * 2018-05-23
 */

    namespace Lib11\addr_analysis;

    class addr_analysis{

        private $sheng;
        private $shi;
        private $qu;

        public function __construct(){

            $this->sheng = \unserialize(\file_get_contents(dirname(__DIR__) . '/addr_analysis/sheng.txt'));
            $this->shi = \unserialize(\file_get_contents(dirname(__DIR__) . '/addr_analysis/shi.txt'));
            $this->qu = \unserialize(\file_get_contents(dirname(__DIR__) . '/addr_analysis/qu.txt'));

        }


        // 智能地址解析
        // "名字、电话号码、地址" 的顺序可以不同，但是名字和地址如果相邻，则必须使用分隔符
        // 如 "刘先生12310001000湖北省武汉市"  "12310001000，刘先生，湖北省武汉市"  "12310001000刘先生-湖北省武汉市" 等都是可以的
        // 而 "12310001000刘先生湖北省武汉市" 是错误的(无法识别名字，'刘先生湖北省武汉市'整个字符串会被当作地址或名字)
        public function exec($info){

            if(empty($info) || !is_string($info)){
                return array();
            }

            // $str = preg_replace('/(,)+|(，)+|(、)+|(\s)+|(-)+|(_)+|(\/)+/', ',', $info);

            $str = preg_replace("/\s+/", '', $info);

            // "刘先生12310001000湖北省"  =>  "刘先生,12310001000,湖北省"
            $str = preg_replace('/([0-9]{8,})/', ',${1},', $str);

            // 将可能的分隔符变为英文逗号
            $str = str_replace("、",",",$str);
            $str = str_replace("，",",",$str);


            // 整理数组
            $res = explode(',', $str);
            $res = array_filter($res);
            $res = array_values($res);
            
        
            $info = array();    // 存储实际的数据（即待返回的数据）

            foreach($res as $k=>$v){
                if(is_numeric($v) && strlen($v)>=6){
                    // 当前项是电话号码
                    if(empty($info['tel'])){
                        $info['tel'] = $v;
                        unset($res[$k]);    // 拿到电话号码以后，就删除此元素
                    }else{
                        // 多个电话号码，错误
                        return array();
                    }
                }
            }

            // 丢弃数组下标，如果剩下的元素大于两个，则只比较第一个和第二个元素，其中短的为姓名，长的为地址，后面的为无用数据
            // 如果只有一个数据，则此数据为地址
            // 如果没有数据，说明只提交了一个电话号码，因此直接返回即可
            $res = array_values($res);
            if(count($res) >= 2){
                if(mb_strlen($res[0]) > mb_strlen($res[1])){
                    $info['name'] = $res[1];
                    $addr = $res[0];
                }else{
                    $info['name'] = $res[0];
                    $addr = $res[1];
                }
                
            }else if(count($res) == 1){
                $addr = $res[0];
            }else{
                return $info;
            }


            // 解析地址
            $res = $this->analyze($addr);
            $storage = $res;


            // 特殊的省
            $zxs_1 = ['北京','上海','天津','重庆'];
            $zxs_2 = ['北京市','上海市','天津市','重庆市'];
            $zzq_1 = ['内蒙古', '新疆', '广西', '宁夏', '西藏'];
            $zzq_2 = ['内蒙古自治区', '新疆维吾尔自治区', '广西壮族自治区', '宁夏回族自治区', '西藏自治区'];

            if(in_array($res['province'], $zxs_1)){
                // 省如果在zxs_1里
                $res['province'] .= '市';
            }else if(!in_array($res['province'], $zxs_2)){
                // 省既不在zxs_1里，也不在zxs_2里
                if(mb_substr($res['province'], mb_strlen($res['province'])-1, 1) != '省'){
                    // 对于自治区特殊处理，正常的就是后面加上省
                    if(in_array($res['province'], $zzq_1)){

                    }else if(in_array($res['province'], $zzq_2)){
                        $res['province'] = $zzq_1[array_search($res['province'], $zzq_2)];
                    }else{
                        if(!empty($res['province'])){
                            $res['province'] .= '省';
                        }
                    }
                }
            }

            // 特殊的市
            $city_n1 = ['延边','恩施','湘西','阿坝','甘孜','凉山','黔西','楚雄','红河','西双版纳','大理','德宏','伊犁',
            '怒江','迪庆','临夏','甘南','海北','黄南','海南','果洛','玉树','海西','昌吉','博尔塔拉','巴音郭楞','克孜勒苏',];
            $city_n1_p = ['延边朝鲜族自治州','恩施土家族苗族自治州','湘西土家族苗族自治州','阿坝藏族羌族自治州',
            '甘孜藏族自治州','凉山彝族自治州','黔西南布依族苗族自治州','楚雄彝族自治州','红河哈尼族彝族自治州',
            '西双版纳傣族自治州','大理白族自治州','德宏傣族景颇族自治州','怒江傈僳族自治州','迪庆藏族自治州',
            '临夏回族自治州','甘南藏族自治州','海北藏族自治州','黄南藏族自治州','海南藏族自治州','果洛藏族自治州',
            '玉树藏族自治州','海西蒙古族藏族自治州','昌吉回族自治州','博尔塔拉蒙古自治州','巴音郭楞蒙古自治州',
            '克孜勒苏柯尔克孜自治州','伊犁哈萨克自治州',];

            $city_n2 = ['大兴安岭','昌都','山南','日喀则','那曲','阿里','林芝','海东',
            '塔城','阿勒泰','喀什','和田','吐鲁番','哈密','阿克苏',
            '兴安','锡林郭勒','阿拉善','神农架',];
            $city_n2_p = ['大兴安岭地区','昌都地区','山南地区','日喀则地区','那曲地区','阿里地区','林芝地区','海东地区',
            '塔城地区','阿勒泰地区','喀什地区','和田地区','吐鲁番地区','哈密地区','阿克苏地区',
            '兴安盟','锡林郭勒盟','阿拉善盟','神农架林区',];
            
            if(in_array($res['province'], $zxs_2)){
                // 省在zxs_2里
                if(empty($res['city'])){
                    // 市为空
                    $res['city'] = $res['province'];
                }else if(!in_array($res['city'], $zxs_1)){
                    // 市不为空，也不在zxs_1里
                    $res['town'] = $res['city'] . '区';
                    $res['city'] = $res['province'];
                }else{
                    // 市不为空，在zxs_1里
                    if(mb_substr($res['city'], mb_strlen($res['city'])-1, 1) != '市'){
                        $res['city'] .= '市';
                    }
                }
            }else{
                // 省不在zxs里
                if(!empty($res['city']) && mb_substr($res['city'], mb_strlen($res['city'])-1, 1) != '市'){

                    //排除特殊的市，正常的在后面加上'市'后缀
                    if(in_array($res['city'], $city_n1)){
                        $res['city'] = $city_n1_p[array_search($res['city'], $city_n1)];
                    }else if(in_array($res['city'], $city_n2)){
                        $res['city'] = $city_n2_p[array_search($res['city'], $city_n2)];
                    }else{
                        $res['city'] .= '市';
                    }

                }
            }


            //如果区存在，则查询数据库完善区的字段（因为区的末尾可以是 '区'、'县'、'镇'、'市' 等）
            if(!empty($res['town'])){
                $res['town'] = M('District')->where(array(
                                    'district' => array('like', $res['town'] . '%'),
                                    'level' => array('eq', 3)
                                ))->getfield('district');
            }

            // 当区存在而市不存在时，可以联想查询市
            if(empty($storage['city']) && !in_array($res['city'], $zxs_2) && !empty($res['town'])){
                $town_pid = M('District')->where(array(
                                'district' => array('like', $storage['town'] . '%'),
                                'level' => array('eq', 3)
                            ))->getfield('pid');
                $res['city'] = M('District')->where(array('district_id' => $town_pid))->getfield('district');
                $storage['city'] = $res['city'];
                $res['city'] .= '市';
            }
            
            // 当市存在而省不存在时，可以联想查询省
            if(empty($storage['province']) && !empty($res['city'])){
                $city_pid = M('District')->where(array(
                                'district' => array('like', $storage['city'] . '%'),
                                'level' => array('eq', 2)
                            ))->getfield('pid');
                $res['province'] = M('District')->where(array('district_id' => $city_pid))->getfield('district');
                
                if(in_array($res['province'], $zzq_2)){
                    // 对于自治区特殊处理
                    $res['province'] = $zzq_1[array_search($res['province'], $zzq_2)];
                }
            }

            
            

            $info['addrinfo'] = $res;
            return $info;


        }





        /**
         * 地址解析
         */
        private function analyze($addr){

            $sheng = $this->sheng;
            $shi = $this->shi;
            $qu = $this->qu;

            /* 开始分词 */

            $length = mb_strlen($addr);     // 字符串总长度
            $char_arr = [];                 // 将字符串分割为字符数组

            for($i=0; $i<$length; $i++){
                $char_arr[$i] = mb_substr($addr, $i, 1);
            }


            $positioner = 0;        // 定位器
            $point = 0;             // 指针

            $province = [];     // 省
            $city = [];         // 市
            $town = [];         // 区

            // 由于地址只需要匹配三次，因此这里不进行循环了

            // 第一次，匹配省
            for($point = $positioner; $point < $length; $point++){
                if(!empty($sheng[$char_arr[$point]])){
                    // 匹配成功，将其加入到省的变量里，并更新 $sheng 的值
                    $province[] = $char_arr[$point];
                    $sheng = $sheng[$char_arr[$point]];
                }else{
                    // 如果只匹配到一个字，则此次匹配是失败的
                    if(count($province) == 1){
                        $province = [];
                    }else{
                        $positioner = $point;
                    }

                    break;
                }
            }

            // 第二次，匹配市
            for($point = $positioner; $point < $length; $point++){
                if(!empty($shi[$char_arr[$point]])){
                    // 匹配成功，将其加入到省的变量里，并更新 $sheng 的值
                    $city[] = $char_arr[$point];
                    $shi = $shi[$char_arr[$point]];
                }else{
                    // 如果只匹配到一个字，则此次匹配是失败的
                    if(count($city) == 1){
                        $city = [];
                    }else{
                        $positioner = $point;
                        if($char_arr[$positioner] == '市' || $char_arr[$positioner] == '区' || $char_arr[$positioner] == '盟'){
                            $positioner++;
                        }else if($char_arr[$positioner] == '地' && $char_arr[$positioner+1] == '区'){
                            $positioner+=2;
                        }else if($char_arr[$positioner] == '林' && $char_arr[$positioner+1] == '区'){
                            $positioner+=2;
                        }else if($char_arr[$positioner] == '自' && $char_arr[$positioner+1] == '治' && $char_arr[$positioner+2] == '州'){
                            $positioner+=2;
                        }
                    }
                    break;
                }
            }

            // 第三次，匹配区
            for($point = $positioner; $point < $length; $point++){
                if(!empty($qu[$char_arr[$point]])){
                    // 匹配成功，将其加入到省的变量里，并更新 $sheng 的值
                    $town[] = $char_arr[$point];
                    $qu = $qu[$char_arr[$point]];
                }else{
                    // 如果只匹配到一个字，则此次匹配是失败的
                    if(count($town) == 1){
                        $town = [];
                    }else{
                        $positioner = $point;
                    }
                    break;
                }
            }

            // 最后得到详细地址
            $addrinfo = [];
            if($point != $length){
                for($point = $positioner; $point < $length; $point++){
                    $addrinfo[] = $char_arr[$point];
                }
            }
            


            /* 结束分词 */

            return [
                'province' => implode('', $province), 
                'city' => implode('', $city), 
                'town' => implode('', $town), 
                'addr' => implode('', $addrinfo)
            ];

        }


    }