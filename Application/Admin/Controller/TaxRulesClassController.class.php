<?php
/**
 * CC税则种类/类别
*/
namespace Admin\Controller;
use Think\Controller;
class TaxRulesClassController extends AdminbaseController
{
    function _initialize() {

        parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/TaxRulesClass');		//读取、查询操作
        $this->client = $client;		//全局变量

    }

    //类别列表
    public function index(){

        $keyword = trim(I('get.keyword'));//查询内容
		
		$searchtype = I('get.searchtype');//查询类型 
		
		$ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');

		$p = (I('p')) ? trim(I('p')) : '1';

		if(!empty($keyword)){

			switch ($searchtype) {
				
				case 'ONE':
					$where['cname1'] = array('like','%'.$keyword.'%');
					break;
	
				case 'TWO':
					$where['cname2'] = array('like','%'.$keyword.'%');
                    break;
                
                case 'THREE':
					$where['cname3'] = array('like','%'.$keyword.'%');
                    break;
                    
                case 'FOUR':
					$where['cname4'] = array('like','%'.$keyword.'%');
                    break;
                    
                case 'FIVE':
					$where['cname5'] = array('like','%'.$keyword.'%');
					break;
				
				default:
					$this->error('请选择正确的查询类型',U('TaxRulesClass/index'),1);
					break;
			}
        }
        
        //行邮税号
        $hscode = trim(I('get.hscode'));

        if(!empty($hscode)){

            $where['hs_code'] = $hscode;
        }

		$this->assign($_GET);
		
		$this->assign('ePage',$ePage);

		$res = $this->client->_index($where,$p,$ePage);
		
		$count = $res['count'];

		$list  = $res['list'];

		$page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)

		$page->setConfig('prev', "上一页");//上一页
		$page->setConfig('next', '下一页');//下一页
		$page->setConfig('first', '首页');//第一页
		$page->setConfig('last', "末页");//最后一页
		$page->setConfig ( 'theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
			
		$show = $page->show(); // 分页显示输出

		$this->assign('page',$show);// 赋值分页输出

		$this->assign('list',$list);

        $this->display();
    }

    //新增类别
    public function add(){

        $this->display();
    }

    public function form_add(){

        if(IS_POST && IS_AJAX){
        
            $code = trim(I('post.code'));//行邮税号

            if(!preg_match("/^[0-9]*$/",$code)){

                $this->ajaxReturn(array('state' => 'no','msg' => '行邮税号为一组数字'));
            }

            if(strlen($code) != 8){

                $this->ajaxReturn(array('state' => 'no','msg' => '请输入8位数的行邮税号'));
            }

            $price = trim(I('post.price'));//完税价格
            
            $rate = trim(I('post.rate_num'));//税率

            $sort = trim(I('post.sort'));//序号

            if(!preg_match("/^[1-9][0-9]*$/",$price)){
                
                $this->ajaxReturn(array('state' => 'no','msg' => '完税价格是一个正整数')); 
            }

            if(!preg_match("/^[1-9][0-9]*$/",$rate)){
                
                $this->ajaxReturn(array('state' => 'no','msg' => '税率是一个正整数')); 

            }else if($rate < 0 || $rate > 100){

                $this->ajaxReturn(array('state' => 'no','msg' => '税率是0到100的正整数')); 
            }

            if(!preg_match("/^[1-9][0-9]*$/",$sort)){
                
                $this->ajaxReturn(array('state' => 'no','msg' => '排序值是一个正整数')); 
            }

            $data['hs_code'] = $code;

            $data['name_and_spec'] = $code_name = trim(I('post.code_name'));//品名及规格

            $data['cname1'] = $one = trim(I('post.one'));//一级

            $data['cname2'] = $two = trim(I('post.two'));//二级

            $data['cname3'] = $three = trim(I('post.three'));//三级

            $data['cname4'] = $four = trim(I('post.four'));//四级

            $data['cname5'] = $five = trim(I('post.five'));//五级

            $unit = trim(I('post.unit'));//规格单位

            $number = trim(I('post.number'));//数量单位

            // 把中文逗号 替换成英文逗号
            $data['specifications'] = preg_replace("/(，)/",',',$unit);//规格单位
            
            $data['number'] = preg_replace("/(，)/",',',$number);//数量单位

            $data['price'] = $price;//完税价格

            $data['rate'] = $rate;//税率

            $data['sort'] = $sort;//序号

            $data['status'] = $state = I('post.state');//状态

            $data['operator_id'] = session('admin')['adid'];

            $res = $this->client->_tax_add($data);

            \Lib11\Queue\CateCache::set_category_cache();
            
            $this->ajaxReturn($res);

        }else{

            $this->ajaxReturn(array('state' => 'no','msg' => '非法提交'));
        }
    }

    //编辑类别
    public function edit(){

        $id = I('get.id');

        $one = $this->client->_tax_one($id);

        $this->assign('one',$one);

        $this->display();
    }

    public function form_edit(){

        if(IS_POST && IS_AJAX){

            $id = I('post.id'); 

            $code = trim(I('post.code'));//行邮税号
            
            if(!preg_match("/^[0-9]*$/",$code)){

                $this->ajaxReturn(array('state' => 'no','msg' => '行邮税号为一组数字'));
            }

            if(strlen($code) != 8){

                $this->ajaxReturn(array('state' => 'no','msg' => '请输入8位数的行邮税号'));
            }

            $price = trim(I('post.price'));//完税价格
            
            $rate = trim(I('post.rate_num'));//税率

            $sort = trim(I('post.sort'));//序号

            if(!preg_match("/^[1-9][0-9]*$/",$price)){
                
                $this->ajaxReturn(array('state' => 'no','msg' => '完税价格是一个正整数')); 
            }

            if(!preg_match("/^[1-9][0-9]*$/",$rate)){
                
                $this->ajaxReturn(array('state' => 'no','msg' => '税率是一个正整数')); 

            }else if($rate < 0 || $rate > 100){

                $this->ajaxReturn(array('state' => 'no','msg' => '税率是0到100的正整数')); 
            }

            if(!preg_match("/^[1-9][0-9]*$/",$sort)){
                
                $this->ajaxReturn(array('state' => 'no','msg' => '排序值是一个正整数')); 
            }
            
            $data['hs_code'] = $code;

            $data['name_and_spec'] = $code_name = trim(I('post.code_name'));//品名及规格

            $data['cname1'] = $one = trim(I('post.one'));//一级

            $data['cname2'] = $two = trim(I('post.two'));//二级

            $data['cname3'] = $three = trim(I('post.three'));//三级

            $data['cname4'] = $four = trim(I('post.four'));//四级

            $data['cname5'] = $five = trim(I('post.five'));//五级

            $unit = trim(I('post.unit'));//规格单位
            
            $number = trim(I('post.number'));//数量单位

            // 把中文逗号 替换成英文逗号
            $data['specifications'] = preg_replace("/(，)/",',',$unit);//规格单位

            $data['number'] = preg_replace("/(，)/",',',$number);//数量单位

            $data['price'] = $price;//完税价格

            $data['rate'] = $rate;//税率

            $data['sort'] = $sort;//序号

            $data['status'] = $state = I('post.state');//状态

            $data['operator_id'] = session('admin')['adid'];

            $res = $this->client->_tax_edit($id,$data);

            \Lib11\Queue\CateCache::set_category_cache();
            
            $this->ajaxReturn($res);

        }else{

            $this->ajaxReturn(array('state' => 'no','msg' => '非法提交'));            
        }
    }

    //查看
    public function info(){

        $id = I('get.id');
        
        $one = $this->client->_tax_one($id);

        $this->assign('info',$one);

        $this->display();
    }

    /**
     * 导入CSV
     * @return [type] [description]
     */
    public function import_csv(){
     
        ini_set('memory_limit','4088M');
        ini_set('max_execution_time', 0);

        $file = $_FILES['file']['name'];

        //判断文件后缀  暂时只支持xlsx和xls
        $hz = pathinfo($file, PATHINFO_EXTENSION);

        if($hz == 'xlsx' || $hz == 'xls'){

                        
        }else{

            $this->ajaxReturn(array('status'=>'0','msg'=>'只支持excel文档导入'));
        }

        $importexcel = new \Libm\MKILExcel\MkilImportMarket;

        $importexcel->inputFileName  = $_FILES['file']['tmp_name'];

        $arr = $importexcel->import();
        
        //如果返回的是false
        if($arr === false){

            $this->ajaxReturn(array('status'=>'0','msg'=>$importexcel->getError()));
        }
        
        // $adminid = $_SESSION['admin']['adid'];
        $adminid = session('admin.adid');

        $client = $this->client;

        $result = $client->_import_csv($arr,$adminid);

        \Lib11\Queue\CateCache::set_category_cache();
   
        $this->ajaxReturn($result);
    }
}