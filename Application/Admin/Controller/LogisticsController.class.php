<?php
/**
 * 号段管理
 */
namespace Admin\Controller;
use Think\Controller;
class LogisticsController extends AdminbaseController
{
    function _initialize() {

        parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/Logistics');		//读取、查询操作
        $this->client = $client;		//全局变量
    }

    //号段列表
    public function index()
    {
        //线路id
        $line = I('get.line','');

        //获取所有可用线路
        $center = $this->client->get_center_list();
        $this->assign('center',$center);

        //获取线路下的所有号段
        $keyword    = trim(I('get.keyword'));
        $searchtype = I('get.searchtype');
        $status     = I('get.status');
        $starttime  = intval(I('get.starttime'));
        $endtime    = intval(I('get.endtime'));

        $p = (I('p')) ? trim(I('p')) : '1';
        $ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');
        $this->assign($_GET);
        $this->assign('ePage',$ePage);

        $where['t.transit_center_id'] = $line;

        if(!empty($searchtype)){
            switch ($searchtype) {
                case 'NO':
                    $where['n.no'] = array('like','%'.$keyword.'%');
                    break;
                case 'MKNO':
                    $where['n.MKNO'] = array('like','%'.$keyword.'%');
                    break;
                default:
                    $this->error('请选择正确的查询类型',U('Logistics/index'),1);
                    break;
            }
        }

        if($starttime && $endtime){
            $where['n.use_time'] = array('between',array($starttime,$endtime));
        }else if(!$starttime && $endtime){
            $where['n.use_time'] = array('elt',$endtime);
        }else if($starttime && !$endtime){
            $where['n.use_time'] = array('egt',$starttime);
        }

        if($status != ''){
            $where['n.status'] = $status;
        }

        $res = $this->client->_index($where,$p,$ePage);

        $count = $res['count'];
        $list  = $res['list'];
        $warn  = $res['warn'];

        $page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)

        $page->setConfig('prev', "上一页");//上一页
        $page->setConfig('next', '下一页');//下一页
        $page->setConfig('first', '首页');//第一页
        $page->setConfig('last', "末页");//最后一页
        $page->setConfig ( 'theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');

        $show = $page->show(); // 分页显示输出

        $warn_num = C('WARNING')?C('WARNING'):1000;	//设置提示数量
        $this->assign('warn_num',$warn_num);
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('list',$list);
        $this->assign('warn',$warn);

        $this->display();
    }

    //导入号段视图
    public function importView()
    {
        //线路id
        $line_id = I('get.line');

        if(empty($line_id)){

            exit('请先选择线路再导入号段');
        }

        $this->assign('line_id',$line_id);
        $this->display();
    }

    //导入号段方法
    public function import_excel()
    {
        if(!IS_POST){
            $this->ajaxReturn(array('status'=>'0','msg'=>'非法提交'));
        }

        ini_set('memory_limit','4088M');
        ini_set('max_execution_time', 0);

        $line_id = I('post.line_id');
        if(! $this->client->TranKd($line_id)){
            $this->ajaxReturn(array('status'=>'0','msg'=>'请选择正确的线路'));
        }

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

        $result = $this->client->_import_excel($arr,$line_id);

        $this->ajaxReturn($result);
    }

    //手动增加一条(增加多条以后再说)
    public function CreateView()
    {
        $line = I('get.line');//线路id

        //获取所有可用线路
        $center = $this->client->get_center_list();
        $this->assign('center',$center);
        $this->assign('line',$line);
        $this->display();
    }

    public function formCreate()
    {
        if(IS_POST){
            $no = I('post.no');
            $line = I('post.line');

            if(! preg_match("/^[A-Za-z0-9]{1,30}$/",$no)){
                $this->ajaxReturn(array('state'=>'no','msg'=>'号段由1~30位的数字与字母组成'));
            }
            $res = $this->client->_add($no,$line);
            $this->ajaxReturn($res);
        }else{
            $this->ajaxReturn(array('state'=>'no','msg'=>'非法提交'));
        }
    }
}

