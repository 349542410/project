<?php
/**
 * 快件管理 服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class PostManagementController extends HproseController{
   // protected $allowMethodList  =   array('index','test1');
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

    /**
     * 计算总数
     * @param  [type] $map [查询条件]
     * @param  [type] $type [类型]
     * @return [type]      [description]
     */
    public function count($map,$p,$ePage,$type){

        //如果是已删除界面
        if($type == 'backup'){

            $list = M('TranListBackup t')->field('t.*,g.no as mStr1')->join('LEFT JOIN mk_transit_no g ON t.noid = g.id')->where($map)->order('t.tid')->page($p.','.$ePage)->select();

            $count = M('TranListBackup t')->join('LEFT JOIN mk_transit_no g ON t.noid = g.id')->where($map)->count();

        //作废件
        }else if($type == 'void'){

            $list = M('TranList t')->field('t.*,g.no as mStr1')->join('LEFT JOIN mk_daily_record d ON d.MKNO = t.MKNO')->join('LEFT JOIN mk_transit_no g ON t.noid = g.id')->where($map)->order('t.optime desc')->page($p.','.$ePage)->select();

            $count = M('TranList t')->join('LEFT JOIN mk_daily_record d ON d.MKNO = t.MKNO')->join('LEFT JOIN mk_transit_no g ON t.noid = g.id')->where($map)->count();

        }else{

            $list = M('TranList t')->field('t.*,g.no as mStr1')->join('LEFT JOIN mk_transit_no g ON t.noid = g.id')->where($map)->order('t.optime desc')->page($p.','.$ePage)->select();

            $count = M('TranList t')->join('LEFT JOIN mk_transit_no g ON t.noid = g.id')->where($map)->count();

        }

        return array('list'=>$list,'count'=>$count);
    }

    /**
     * 获取查询列表
     * @param  [type] $map   [查询条件]
     * @param  [type] $limit [限制条件]
     * @return [type]        [description]
     */
    public function getList($map,$limit,$type){

        //如果是已删除界面
        if($type == 'backup'){

            //2015-08-28 Man 取消$l,如果连il_logs 最后一条 一定是 该面单已被删除，没有意义
            //$l = ("left join mk_il_logs l ON(l.id=(select max(id) from mk_il_logs where MKNO=t.MKNO))");
            //$list = M('TranListBackup t')->field('t.*,l.content,l.create_time,g.mStr1')->join($l)->join('LEFT JOIN mk_logs_backup g ON t.MKNO = g.MKNO AND g.state = 20')->where($map)->limit($limit)->order('t.tid')->select(false);
            //
            // $list = M('TranListBackup t')->field('t.*,g.mStr1')->join('LEFT JOIN mk_logs_backup g ON t.MKNO = g.MKNO AND g.state = 20')->where($map)->limit($limit)->order('t.tid')->select();
            $list = M('TranListBackup t')->field('t.*,g.no as mStr1')->join('LEFT JOIN mk_transit_no g ON t.noid = g.id')->where($map)->limit($limit)->order('t.tid')->select();

        //作废件
        }else if($type == 'void'){
            //由于最新物流信息的获取方式改变，故以下语句注销 20160728 Jie  再添加一个修改：按照操作时间的最新时间来排序
            // $l = ("left join mk_il_logs l ON(l.id=(select max(id) from mk_il_logs where MKNO=t.MKNO))");
            $list = M('TranList t')->field('t.*,g.no as mStr1')->join('LEFT JOIN mk_daily_record d ON d.MKNO = t.MKNO')->join('LEFT JOIN mk_transit_no g ON t.noid = g.id')->where($map)->order('t.optime desc')->limit($limit)->select();

        }else{
            //由于最新物流信息的获取方式改变，故以下语句注销 20160728 Jie  再添加一个修改：按照操作时间的最新时间来排序
            // $l = ("left join mk_il_logs l ON(l.id=(select max(id) from mk_il_logs where MKNO=t.MKNO))");
            // $list = M('TranList t')->field('t.*,l.content,l.create_time,g.mStr1')->join($l)->join('LEFT JOIN mk_logs g ON t.MKNO = g.MKNO AND g.state = 20')->where($map)->order('optime desc')->limit($limit)->select();
            $list = M('TranList t')->field('t.*,g.no as mStr1')->join('LEFT JOIN mk_transit_no g ON t.noid = g.id')->where($map)->order('t.optime desc')->limit($limit)->select();

        }

        // 20161116 jie   增加 TrandKd = 中转线路.id 的查询
        $center_list = M('TransitCenter')->field('id,name')->where(array('status'=>1))->select();

        return array('list'=>$list,'center_list'=>$center_list);
    }

    /**
     * 获取某行数据详细信息
     * @param  [type] $id [被编辑的id]
     * @return [type]     [description]
     */
    public function getInfo($id,$MKNO = ''){
        if(isset($MKNO) && $MKNO != ''){
            $info = M('TranList')->where(array('MKNO'=>$MKNO))->find();
        }else{
            $info = M('TranList')->where(array('id'=>$id))->find();
        }
        //查询面单所包含的商品
        $pro_list = M('TranList l')->field('o.detail,o.number,o.price')->join('LEFT JOIN mk_tran_order o ON l.id = o.lid')->where(array('l.id'=>$info['id']))->select();

        return $res = array($info,$pro_list);
    }

    /**
     * 获取物流信息
     * @param  [type] $info [description]
     * @return [type]       [description]
     */
    public function getMsg($info,$MKNO){
        if(isset($MKNO) && $MKNO != ''){
            $msg = M('IlLogs')->where(array('MKNO'=>$MKNO))->order('create_time desc,id desc')->select();
        }else{
            $msg = M('IlLogs')->where(array('MKNO'=>$info['MKNO']))->order('create_time desc,id desc')->select();
        }
        // 20161229 jie 新增海关信息列表和海关错误信息
        $hg_info = M('Trainer')->where(array('LogisticsNo'=>$info['STNO']))->getField('Result');
        $hg_msg  = M('TrainerLogs')->where(array('LogisticsNo'=>$info['STNO']))->order('CreateTime desc')->select();

        return $res = array($msg, $hg_info, $hg_msg);
    }

    /**
     * 更新修改
     * @param  [type] $id   [id]
     * @param  [type] $data [数据]
     * @return [type]       [description]
     */
    public function update($id,$data,$MKNO,$auto_Indent2,$remark,$username,$address){

        $checkFirst = M('TranList')->where(array('id'=>$id))->find();

        if(!$checkFirst){
            $result = array('state'=>'no', 'msg'=>'参数错误，请刷新再试');
            return $result;
        }

        //验证美快单号与自定义2
        if($checkFirst['MKNO'] != $MKNO || $checkFirst['auto_Indent2'] != $auto_Indent2){
            $result = array('state' => 'no', 'msg' => '美快单号或自定义2有误');
            return $result;
        }

        //IL_state < 200  才可以修改
        if($checkFirst['IL_state'] >= '200'){
            $result = array('state' => 'no', 'msg' => '非法操作');
            return $result;
        }

        /* //如果以下信息为空的时候，则取原来的信息保持不变
        if($data['province'] == '') $data['province'] = $checkFirst['province']; //省份
        if($data['city']     == '') $data['city'] = $checkFirst['city']; //城市
        if($data['town']     == '') $data['town'] = $checkFirst['town']; //区/县*/
        //如果收件地址不填写，则以下字段取原来的数据不变
        if($address == ''){
            $data['province'] = $checkFirst['province'];  //省份
            $data['city']     = $checkFirst['city'];  //城市
            $data['town']     = $checkFirst['town'];  //区/县
            $data['reAddr']   = $checkFirst['reAddr']; //收件地址
        }else{
            if($data['province'] == ''){
                return $result = array('state'=>'no','msg'=>'省份不能为空');
            }
            if($data['city'] == ''){
                return $result = array('state'=>'no','msg'=>'城市不能为空');
            }
            if($data['town'] == ''){
                return $result = array('state'=>'no','msg'=>'区/县不能为空');
            }
        }

        // 数据修改记录
        $list = '';
        if($data['province'] != $checkFirst['province']){
            $list = '省份：'.$checkFirst['province']." => ".$data['province']."；";
        }
        if($data['city'] != $checkFirst['city']){
            $list = '城市：'.$checkFirst['city']." => ".$data['city']."；";
        }
        if($data['town'] != $checkFirst['town']){
            $list = '区/县：'.$checkFirst['town']." => ".$data['town']."；";
        }
        if($data['receiver'] != $checkFirst['receiver']){
            $list = '收件人：'.$checkFirst['receiver']." => ".$data['receiver']."；";
        }
        if($data['reTel'] != $checkFirst['reTel']){
            $list = $list.'收件电话：'.$checkFirst['reTel']." => ".$data['reTel']."；";
        }

        if($data['reAddr'] != $checkFirst['reAddr']){
            $list = $list.'收件地址：'.$checkFirst['reAddr']." => ".$data['reAddr']."；";
        }

        $result = M('TranList')->where(array('id'=>$id))->limit(1)->save($data);
        if($result == 0){

            return $result = array('state'=>'no','msg'=>'没有数据更新');

        }else if($result === false){

            return $result = array('state'=>'no','msg'=>'更新失败');

        }else{

            //保存操作记录到日志
            $recordData['tid']          = $id;
            $recordData['auto_Indent1'] = $checkFirst['auto_Indent1'];
            $recordData['auto_Indent2'] = $checkFirst['auto_Indent2'];
            $recordData['MKNO']         = $checkFirst['MKNO'];
            $recordData['STNO']         = $checkFirst['STNO'];
            $recordData['operate_user'] = $username;
            $recordData['t_optime']     = $checkFirst['optime'];
            $recordData['t_IL_State']   = $checkFirst['IL_state'];
            $recordData['remark']       = $remark;
            $recordData['optype']       = '修改';
            $recordData['change_item']  = $list;
            M('DailyRecord')->add($recordData);

            return $result = array('state'=>'yes','msg'=>'更新成功');
        }

    }

    /**
     * 订单暂停 方法
     * @param  [type] $id [id]
     * @param  [type] $MKNO [美快单号]
     * @return [type] [description]
     */
    public function toPause($id,$MKNO,$auto_Indent2,$remark,$username){

        //验证
        $checkFirst = M('TranList')->where(array('id'=>$id))->find();

        //验证是否存在
        if(!$checkFirst){
            $result = array('state'=>'no', 'msg'=>'参数错误，请刷新再试');
            return $result;
        }

        //验证美快单号与自定义2
        if($checkFirst['MKNO'] != $MKNO || $checkFirst['auto_Indent2'] != $auto_Indent2){
            $result = array('state' => 'no', 'msg' => '美快单号或自定义2有误');
            return $result;
        }

        // 8 < IL_state < 200  才可以暂停
        if($checkFirst['IL_state'] >= '200'){
            $result = array('state' => 'no', 'msg' => '非法操作');
            return $result;
        }

        $res = M('TranListStop')->where(array('tid'=>$id))->find(); //查询数据

        $pause_status = M('TranList')->where(array('id'=>$id))->getField('pause_status');   //查询 pause_status 状态是否为20

        //1 如果mk_tran_list_stop 表中有数据记录,但mk_tran_list 中的状态为20
        if($res && $pause_status == '20'){

            if($res['status'] == '0'){  //如果mk_tran_list_stop 状态为正常，则执行变更为已暂停

                $data['status'] = 20;   //20为已暂停
                M('TranListStop')->where(array('id'=>$res['id']))->save($data); //更新mk_tran_list_stop  状态为20

            }

            $result = array('state'=>'no','msg'=>'订单已暂停,无法重复操作');
            return $result;

        //2 如果mk_tran_list_stop 表中有数据记录,且mk_tran_list 中的状态为0
        }else if($res && $pause_status == '0'){

            //如果mk_tran_list_stop状态为20  则mk_tran_list执行变更为20
            if($res['status'] == '20'){

                $dat['pause_status'] = 20;  //20为已暂停
                M('TranList')->where(array('id'=>$id))->limit(1)->save($dat); //更新mk_tran_list  状态为20

                $result = array('state'=>'no','msg'=>'订单已暂停,无法重复操作');
                return $result;

            }else if($res['status'] == '0'){    //正常

                $data['status']      = 20;   //20为已暂停
                $dat['pause_status'] = 20;  //20为已暂停
                M('TranList')->where(array('id'=>$id))->limit(1)->save($dat); //更新mk_tran_list  状态为20
                M('TranListStop')->where(array('id'=>$res['id']))->save($data); //更新mk_tran_list_stop 状态为20

                //插入一条新的物流信息记录到mk_il_logs
                $logsData['MKNO']        = $MKNO;
                $logsData['content']     = '订单已被暂停运作';
                $logsData['create_time'] = date('Y-m-d H:i:s');
                $logsData['status']      = 400;      //原标识码为20  Jie 2015-09-15 更改标识码为400以示暂停
                $logsData['state']       = 0;      //推送至ERP状态
                $logsData['CID']         = $checkFirst['CID'];      //CID
                M('IlLogs')->add($logsData);

                //保存操作记录到日志
                $recordData['tid']          = $id;
                $recordData['auto_Indent1'] = $checkFirst['auto_Indent1'];
                $recordData['auto_Indent2'] = $checkFirst['auto_Indent2'];
                $recordData['MKNO']         = $checkFirst['MKNO'];
                $recordData['STNO']         = $checkFirst['STNO'];
                $recordData['operate_user'] = $username;
                $recordData['t_optime']     = $checkFirst['optime'];
                $recordData['t_IL_State']   = $checkFirst['IL_state'];
                $recordData['remark']       = $remark;
                $recordData['optype']       = '暂停';
                M('DailyRecord')->add($recordData);

                $result = array('state'=>'yes','msg'=>'订单暂停成功');
                return $result;
            }


        //3 如果mk_tran_list_stop 表中没有数据记录，但mk_tran_list中该单的pause_status状态为20
        }else if(!$res && $pause_status == '20'){

            $data['tid']    = $id;
            $data['MKNO']   = $MKNO;
            $data['status'] = 20;   //20为已暂停
            M('TranListStop')->add($data);  //添加新数据到mk_tran_list_stop

            $result = array('state'=>'no','msg'=>'订单已暂停,无法重复操作');
            return $result;

        //4 如果mk_tran_list_stop 表中没有数据记录，且mk_tran_list中该单的pause_status状态为0
        }else if(!$res && $pause_status == '0'){

            $data['tid']    = $id;
            $data['MKNO']   = $MKNO;
            $data['status'] = 20;   //20为已暂停
            M('TranListStop')->add($data);  //添加新数据到mk_tran_list_stop

            $dat['pause_status'] = 20;  //20为已暂停
            M('TranList')->where(array('id'=>$id))->limit(1)->save($dat); //更新mk_tran_list  状态为20

            //保存信息记录到mk_il_logs
            $logsData['MKNO']        = $MKNO;
            $logsData['content']     = '订单已被暂停运作';
            $logsData['create_time'] = date('Y-m-d H:i:s');
            $logsData['status']      = 400;      //原标识码为20  Jie 2015-09-15 更改标识码为400以示暂停
            $logsData['state']       = 0;      //推送至ERP状态
            $logsData['CID']         = $checkFirst['CID'];      //CID
            M('IlLogs')->add($logsData);

            //保存操作记录到日志
            $recordData['tid']          = $id;
            $recordData['auto_Indent1'] = $checkFirst['auto_Indent1'];
            $recordData['auto_Indent2'] = $checkFirst['auto_Indent2'];
            $recordData['MKNO']         = $checkFirst['MKNO'];
            $recordData['STNO']         = $checkFirst['STNO'];
            $recordData['operate_user'] = $username;
            $recordData['t_optime']     = $checkFirst['optime'];
            $recordData['t_IL_State']   = $checkFirst['IL_state'];
            $recordData['remark']       = $remark;
            $recordData['optype']       = '暂停';
            M('DailyRecord')->add($recordData);

            $result = array('state'=>'yes','msg'=>'订单暂停成功');
            return $result;

        }else{
            $result = array('state'=>'no','msg'=>'参数错误：502');
            return $result;
        }

    }

    /**
     * 订单恢复
     * @param  [type] $id [id]
     * @param  [type] $MKNO [美快单号]
     * @return [type] [description]
     */
    public function toRecover($id,$MKNO,$auto_Indent2,$remark,$username){

        $checkFirst = M('TranList')->where(array('id'=>$id))->find();

        if(!$checkFirst){
            $result = array('state'=>'no', 'msg'=>'参数错误，请刷新再试');
            return $result;
        }

        //验证美快单号与自定义2
        if($checkFirst['MKNO'] != $MKNO || $checkFirst['auto_Indent2'] != $auto_Indent2){
            $result = array('state' => 'no', 'msg' => '美快单号或自定义2有误');
            return $result;
        }

        // 8 < IL_state < 200  才可以恢复
        if($checkFirst['IL_state'] >= '200'){
            $result = array('state' => 'no', 'msg' => '非法操作');
            return $result;
        }

        $res = M('TranListStop')->where(array('tid'=>$id))->find(); //查询数据

        $pause_status = M('TranList')->where(array('id'=>$id))->getField('pause_status');   //查询 pause_status 状态是否为20

        if($res && $pause_status == '20'){

            if($res['status'] == '20'){
                $data['status']      = 0;    //0为正常
                $dat['pause_status'] = 0;   //0为正常
                M('TranList')->where(array('id'=>$id))->limit(1)->save($dat); //更新mk_tran_list  状态为20
                M('TranListStop')->where(array('id'=>$res['id']))->save($data); //更新mk_tran_list_stop  状态为0

                //保存一条新的物流信息记录到mk_il_logs
                $logsData['MKNO']        = $MKNO;
                $logsData['content']     = '订单恢复正常运作';
                $logsData['create_time'] = date('Y-m-d H:i:s');
                $logsData['status']      = 401;   //401为恢复
                $logsData['state']       = 0;   //推送至ERP状态
                $logsData['CID']         = $checkFirst['CID'];      //CID
                M('IlLogs')->add($logsData);

                //保存操作记录到日志
                $recordData['tid']          = $id;
                $recordData['auto_Indent1'] = $checkFirst['auto_Indent1'];
                $recordData['auto_Indent2'] = $checkFirst['auto_Indent2'];
                $recordData['MKNO']         = $checkFirst['MKNO'];
                $recordData['STNO']         = $checkFirst['STNO'];
                $recordData['operate_user'] = $username;
                $recordData['t_optime']     = $checkFirst['optime'];
                $recordData['t_IL_State']   = $checkFirst['IL_state'];
                $recordData['remark']       = $remark;
                $recordData['optype']       = '恢复';
                M('DailyRecord')->add($recordData);

                $result = array('state'=>'yes','msg'=>'订单恢复成功');
                return $result;

            }else{

                $result = array('state'=>'no','msg'=>'参数错误：500');
                return $result;

            }
        }else if($res && $pause_status == '0'){

            $data['status'] = 0;    //0为正常
            M('TranListStop')->where(array('id'=>$res['id']))->save($data); //更新mk_tran_list_stop  状态为0

            $result = array('state'=>'no','msg'=>'订单已恢复,无法重复操作');
            return $result;

        }else if(!$res && $pause_status == '20'){

            $data['tid']    = $id;
            $data['MKNO']   = $MKNO;
            $data['status'] = 0;    //0为已恢复正常
            M('TranListStop')->add($data);  //添加新数据到mk_tran_list_stop

            $dat['pause_status'] = 0;   //0为正常
            M('TranList')->where(array('id'=>$id))->limit(1)->save($dat); //更新mk_tran_list  状态为20

            //保存信息记录到mk_il_logs
            $logsData['MKNO']        = $MKNO;
            $logsData['content']     = '订单恢复正常运作';
            $logsData['create_time'] = date('Y-m-d H:i:s');
            $logsData['status']      = 401;       //401为恢复
            $logsData['state']       = 0;       //推送至ERP状态
            $logsData['CID']         = $checkFirst['CID'];      //CID
            M('IlLogs')->add($logsData);

            //保存操作记录到日志
            $recordData['tid']          = $id;
            $recordData['auto_Indent1'] = $checkFirst['auto_Indent1'];
            $recordData['auto_Indent2'] = $checkFirst['auto_Indent2'];
            $recordData['MKNO']         = $checkFirst['MKNO'];
            $recordData['STNO']         = $checkFirst['STNO'];
            $recordData['operate_user'] = $username;
            $recordData['t_optime']     = $checkFirst['optime'];
            $recordData['t_IL_State']   = $checkFirst['IL_state'];
            $recordData['remark']       = $remark;
            $recordData['optype']       = '恢复';
            M('DailyRecord')->add($recordData);

            $result = array('state'=>'yes','msg'=>'订单恢复成功');
            return $result;

        }else if(!$res && $pause_status == '0'){

            $data['tid']    = $id;
            $data['MKNO']   = $MKNO;
            $data['status'] = 0;    //0为已恢复正常
            M('TranListStop')->add($data);  //添加新数据到mk_tran_list_stop

            $result = array('state'=>'no','msg'=>'订单已恢复,无法重复操作');
            return $result;

        }else{
            $result = array('state'=>'no','msg'=>'参数错误：501');
            return $result;
        }

    }

    /**
     * 订单删除 疑难件除外(即疑难件的删除不在此处执行)
     * @param  [type] $id [id]
     * @return [type]     [description]
     */
    public function delete($id,$MKNO,$auto_Indent2,$remark,$username){

        $checkFirst = M('TranList')->where(array('id'=>$id))->find();

        if(!$checkFirst){
            $result = array('state'=>'no', 'msg'=>'参数错误，请刷新再试');
            return $result;
        }

        //验证美快单号与自定义2
        if($checkFirst['MKNO'] != $MKNO || $checkFirst['auto_Indent2'] != $auto_Indent2){
            $result = array('state' => 'no', 'msg' => '美快单号或自定义2有误');
            return $result;
        }

        //IL_state < 12  才可以删除
        if($checkFirst['IL_state'] >= '12' && $checkFirst['candel'] == '0'){
            $result = array('state' => 'no', 'msg' => '非法操作');
            return $result;
        }

        //查找mk_logs里面是否有这个MKNO
        $did = M('Logs')->where(array('MKNO'=>$checkFirst['MKNO']))->select();

        //查找tran_order里面是否有这个lid
        $lid = M('TranOrder')->where(array('lid'=>$id))->select();

        $Model = M();   //实例化
        $Model->startTrans();//开启事务 

        //先赋予默认的错误值，以便后面的判断，避免由于该变量未定义，系统自动判断不存在而报错
        $del = $dels = false;

        //il_logs有这个MKNO，tran_order也有此lid，则都删除
        if($did && $lid){

            //备份
            foreach($lid as $item){
                M('TranOrderBackup')->add($item);
            }

            foreach($did as $item2){
                M('LogsBackup')->add($item2);
            }

            //删除
            $del  = M('Logs')->where(array('MKNO'=>$checkFirst['MKNO']))->delete();
            $dels = M('TranOrder')->where(array('lid'=>$id))->delete();

        }else if($did && !$lid){//如果il_logs有这个MKNO，而tran_order没有此lid，则执行il_logs的删除

            //备份
            foreach($did as $item){
                M('LogsBackup')->add($item);
            }
            //删除
            $del  = M('Logs')->where(array('MKNO'=>$checkFirst['MKNO']))->delete();

        }else if(!$did && $lid){//如果il_logs没有这个MKNO，而tran_order有此lid，则执行tran_order的删除

            //备份
            foreach($lid as $item){
                M('TranOrderBackup')->add($item);
            }
            //删除
            $dels = M('TranOrder')->where(array('lid'=>$id))->delete();

        }else if(!$did && !$lid){

            //il_logs没有这个MKNO，tran_order也没有对应$id的lid,则tran_list里面的此ID数据可以安心删了
            //备份
            M('TranListBackup')->add($checkFirst);
            //删除
            M('TranList')->where(array('id'=>$id))->delete();

        /* Jie 2015-09-29 Man要求取消此项操作，以防其他原因造成的误删除，mk_stnolist中的数据保留以作备用
            // //校验是否为申通，如果是则清除mk_stnolist对应的申通号使用状态status，重置为0
            // if($checkFirst['TranKd'] == '1'){

            //     $data['uuid']        = NULL;
            //     $data['uuidtime']    = NULL;
            //     $data['status']      = 0;
            //     $data['MKNO']        = NULL;
            //     $data['usetime']     = NULL;
            //     $data['messages']    = NULL;
            //     $data['kd100status'] = 0;
            //     $data['cuid']        = NULL;
            //     M('Stnolist')->where(array('STNO'=>$checkFirst['STNO']))->save($data);
            // }
        */
                //保存操作记录到日志
                $recordData['tid']          = $id;
                $recordData['auto_Indent1'] = $checkFirst['auto_Indent1'];
                $recordData['auto_Indent2'] = $checkFirst['auto_Indent2'];
                $recordData['MKNO']         = $checkFirst['MKNO'];
                $recordData['STNO']         = $checkFirst['STNO'];
                $recordData['operate_user'] = $username;
                $recordData['t_optime']     = $checkFirst['optime'];
                $recordData['t_IL_State']   = $checkFirst['IL_state'];
                $recordData['remark']       = $remark;
                $recordData['optype']       = '删除';
                M('DailyRecord')->add($recordData);

                //插入一条新的物流信息信息到il_logs中
                $logsData['MKNO']        = $checkFirst['MKNO'];
                $logsData['content']     = '该面单已被删除';
                $logsData['create_time'] = date('Y-m-d H:i:s');
                $logsData['status']      = 1003;      //原标识码为400  Jie 2015-09-15 更改标识码为1003以示删除
                $logsData['state']       = 0;       //推送至ERP状态
                $logsData['CID']         = $checkFirst['CID'];      //CID
                M('IlLogs')->add($logsData);

            $Model->commit();//提交事务成功

            $result = array('state' => 'yes', 'msg' => '删除成功');

            return $result;

        }

        //如果已经在il_logs或tran_order上进行了删除，则tran_list也要删除对应的ID数据
        if($del || $dels){

            //备份
            M('TranListBackup')->add($checkFirst);
            //删除
            M('TranList')->where(array('id'=>$id))->delete();

            // //校验是否为申通，如果是则清除mk_stnolist对应的申通号使用状态status，重置为0
            // if($checkFirst['TranKd'] == '1'){

            //     $data['uuid']        = NULL;
            //     $data['uuidtime']    = NULL;
            //     $data['status']      = 0;
            //     $data['MKNO']        = NULL;
            //     $data['usetime']     = NULL;
            //     $data['messages']    = NULL;
            //     $data['kd100status'] = 0;
            //     $data['cuid']        = NULL;
            //     M('Stnolist')->where(array('STNO'=>$checkFirst['STNO']))->save($data);
            // }

                //保存操作记录到日志
                $recordData['tid']          = $id;
                $recordData['auto_Indent1'] = $checkFirst['auto_Indent1'];
                $recordData['auto_Indent2'] = $checkFirst['auto_Indent2'];
                $recordData['MKNO']         = $checkFirst['MKNO'];
                $recordData['STNO']         = $checkFirst['STNO'];
                $recordData['operate_user'] = $username;
                $recordData['t_optime']     = $checkFirst['optime'];
                $recordData['t_IL_State']   = $checkFirst['IL_state'];
                $recordData['remark']       = $remark;
                $recordData['optype']       = '删除';
                M('DailyRecord')->add($recordData);

                //插入一条新的物流信息信息到il_logs中
                $logsData['MKNO']        = $checkFirst['MKNO'];
                $logsData['content']     = '该面单已被删除';
                $logsData['create_time'] = date('Y-m-d H:i:s');
                $logsData['status']      = 1003;      //原标识码为400  Jie 2015-09-15 更改标识码为1003以示删除
                $logsData['state']       = 0;       //推送至ERP状态
                $logsData['CID']         = $checkFirst['CID'];      //CID
                M('IlLogs')->add($logsData);

            $Model->commit();//提交事务成功

            $result = array('state' => 'yes', 'msg' => '删除成功');

            return $result;
        }else{//其他情况，则终止所有删除行为

            $Model->rollback();//事务有错回滚

            $result = array('state' => 'no', 'msg' => '删除失败');

            return $result;
        }

    }

    /**
     * 订单还原  Jie 2015-08-27 暂时停用
     * @param  [type] $tid          [description]
     * @param  [type] $MKNO         [description]
     * @param  [type] $auto_Indent2 [description]
     * @param  [type] $remark       [description]
     * @param  [type] $username     [description]
     * @return [type]               [description]
     */
    public function reduction($tid,$MKNO,$auto_Indent2,$remark,$username){
        $checkFirst = M('TranListBackup')->where(array('tid'=>$tid))->find();

        if(!$checkFirst){
            $result = array('state'=>'no', 'msg'=>'参数错误，请刷新再试');
            return $result;
        }

        //验证美快单号与自定义2
        if($checkFirst['MKNO'] != $MKNO || $checkFirst['auto_Indent2'] != $auto_Indent2){
            $result = array('state' => 'no', 'msg' => '美快单号或自定义2有误');
            return $result;
        }

        // // 8 < IL_state < 200  才可以恢复
        // if($checkFirst['IL_state'] >= '200'){
        //     $result = array('state' => 'no', 'msg' => '非法操作');
        //     return $result; 
        // }

        //查找mk_logs_backup里面是否有这个MKNO
        $did = M('LogsBackup')->where(array('MKNO'=>$checkFirst['MKNO']))->select();

        //查找tran_order_backup里面是否有这个lid
        $lid = M('TranOrderBackup')->where(array('lid'=>$checkFirst['id']))->select();

        $Model = M();   //实例化
        $Model->startTrans();//开启事务 

        //先赋予默认的错误值，以便后面的判断，避免由于该变量未定义，系统自动判断不存在而报错
        $del = $dels = false;

        //il_logs_backup有这个MKNO，tran_order_backup也有此lid，则都删除
        if($did && $lid){

            //还原
            foreach($lid as $item){
                unset($item['tid']);
                M('TranOrder')->add($item);
            }

            foreach($did as $item2){
                unset($item2['tid']);
                M('Logs')->add($item2);
            }

            //删除
            $del  = M('LogsBackup')->where(array('MKNO'=>$checkFirst['MKNO']))->delete();
            $dels = M('TranOrderBackup')->where(array('lid'=>$checkFirst['id']))->delete();

        }else if($did && !$lid){//如果il_logs_backup有这个MKNO，而tran_order_backup没有此lid，则执行il_logs的删除

            //还原
            foreach($did as $item){
                unset($item['tid']);
                M('Logs')->add($item);
            }
            //删除
            $del  = M('LogsBackup')->where(array('MKNO'=>$checkFirst['MKNO']))->delete();

        }else if(!$did && $lid){//如果il_logs_backup没有这个MKNO，而tran_order_backup有此lid，则执行tran_order的删除

            //还原
            foreach($lid as $item){
                unset($item['tid']);
                M('TranOrder')->add($item);
            }
            //删除
            $dels = M('TranOrderBackup')->where(array('lid'=>$checkFirst['id']))->delete();

        }else if(!$did && !$lid){

            //il_logs_backup没有这个MKNO，tran_order_backup也没有对应$id的lid,则tran_list_backup里面的此ID数据可以安心删了
            //还原
            unset($checkFirst['tid']);
            M('TranList')->add($checkFirst);
            //删除
            M('TranListBackup')->where(array('tid'=>$tid))->delete();

            //校验是否为申通，如果是则清除mk_stnolist对应的申通号使用状态status，重置为0
            if($checkFirst['TranKd'] == '1'){

                $data['status'] = 20;   //已使用
                M('Stnolist')->where(array('STNO'=>$checkFirst['STNO'],'MKNO'=>$checkFirst['MKNO']))->save($data);
            }

                //保存操作记录到日志
                $recordData['tid']          = $checkFirst['id'];
                $recordData['auto_Indent1'] = $checkFirst['auto_Indent1'];
                $recordData['auto_Indent2'] = $checkFirst['auto_Indent2'];
                $recordData['MKNO']         = $checkFirst['MKNO'];
                $recordData['STNO']         = $checkFirst['STNO'];
                $recordData['operate_user'] = $username;
                $recordData['t_optime']     = $checkFirst['optime'];
                $recordData['t_IL_State']   = $checkFirst['IL_state'];
                $recordData['remark']       = $remark;
                $recordData['optype']       = '还原';
                M('DailyRecord')->add($recordData);

                //插入一条新的物流信息信息到il_logs中
                $logsData['MKNO']        = $checkFirst['MKNO'];
                $logsData['content']     = '该面单已还原';
                $logsData['create_time'] = date('Y-m-d H:i:s');
                $logsData['status']      = 402;      //402为还原
                $logsData['state']       = 0;       //推送至ERP状态
                $logsData['CID']         = $checkFirst['CID'];      //CID
                M('IlLogs')->add($logsData);

            $Model->commit();//提交事务成功

            $result = array('state' => 'yes', 'msg' => '还原成功');

            return $result;

        }

        //如果已经在il_logs_backup或tran_order_backup上进行了删除，则tran_list_backup也要删除对应的ID数据
        if($del || $dels){

            //还原
            unset($checkFirst['tid']);
            M('TranList')->add($checkFirst);
            //删除
            M('TranListBackup')->where(array('tid'=>$tid))->limit(1)->delete();

            //校验是否为申通，如果是则清除mk_stnolist对应的申通号使用状态status，重置为0
            if($checkFirst['TranKd'] == '1'){

                $data['status'] = 20;   //已使用
                M('Stnolist')->where(array('STNO'=>$checkFirst['STNO'],'MKNO'=>$checkFirst['MKNO']))->save($data);
            }

                //保存操作记录到日志
                $recordData['tid']          = $checkFirst['id'];
                $recordData['auto_Indent1'] = $checkFirst['auto_Indent1'];
                $recordData['auto_Indent2'] = $checkFirst['auto_Indent2'];
                $recordData['MKNO']         = $checkFirst['MKNO'];
                $recordData['STNO']         = $checkFirst['STNO'];
                $recordData['operate_user'] = $username;
                $recordData['t_optime']     = $checkFirst['optime'];
                $recordData['t_IL_State']   = $checkFirst['IL_state'];
                $recordData['remark']       = $remark;
                $recordData['optype']       = '还原';
                M('DailyRecord')->add($recordData);

                //插入一条新的物流信息信息到il_logs中
                $logsData['MKNO']        = $checkFirst['MKNO'];
                $logsData['content']     = '该面单已还原';
                $logsData['create_time'] = date('Y-m-d H:i:s');
                $logsData['status']      = 402;      //402为还原
                $logsData['state']       = 0;       //推送至ERP状态
                $logsData['CID']         = $checkFirst['CID'];      //CID
                M('IlLogs')->add($logsData);

            $Model->commit();//提交事务成功

            $result = array('state' => 'yes', 'msg' => '还原成功');

            return $result;
        }else{//其他情况，则终止所有删除行为

            $Model->rollback();//事务有错回滚

            $result = array('state' => 'no', 'msg' => '还原失败');

            return $result;
        }

    }

    /**
     * 操作日志 计算总数
     */
    public function countLog($where){

        $count = M('DailyRecord')->where($where)->count(); // 查询满足要求的总记录数
        return $count;
    }

    /**
     * 操作日志 获取查询列表
     */
    public function getLog($limit,$where){
        $list = M('DailyRecord')->order('operate_time desc,id')->where($where)->limit($limit)->select();
        return $list;
    }

//==================== 对账单 ============================

    /**
     * 2015-09-08 新增
     * @return [type] [description]
     */
    public function lost_count(){
        //统计已发快递中 无批次号的面单 总数
        $where['t.IL_state'] = array('eq','200');
        $where['l.id']       = array('exp','is NULL');
        $lost_count = M('TranList t')->join('LEFT JOIN mk_logs l ON l.MKNO = t.MKNO AND l.state = 20')->where($where)->count();
        return $lost_count;
    }

    /**
     * 父页 数据列表 + 统计总数
     * @param  [type] $map   [description]
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function account_list($map,$p,$ePage,$url,$dUrl){
        ini_set('max_execution_time', 0);

        // 进行分页数据查询
        $str = M('Logs')->field('mStr1,optime')->group('mStr1')->where($map)->order('optime desc')->page($p.','.$ePage)->select();

        // 统计总数
        $count = M('Logs')->field('id')->group('mStr1')->where($map)->select();
        $count = count($count);

        $list = array();
        foreach($str as $item){
            $mstr = $item['mStr1'];
            $map2['l.mStr1'] = array('eq',$mstr);       //中转批号
            /* Jie 2015-08-18 停用
            $map2['l.state'] = array('eq','20');  //物流状态要等于20 //Jie 2015-08-19 此查询条件更改为直接放入sql语句中

            $plist = M('Logs l')->field('count(t.id) as num,t.IL_state,l.mStr1,t.pause_status,t.candel,t.optime')
                ->join('LEFT JOIN mk_tran_list t ON t.MKNO = l.MKNO')
                ->group('t.IL_state,t.pause_status,t.candel')
                ->where($map2)->select();
            */
            //按照单个中转批号查找与之对应的所有MKNO
            $plist = M('TranList t')->field('count(t.id) as num,t.IL_state,l.mStr1,t.pause_status,t.candel,t.optime,t.noid')
                ->join('LEFT JOIN mk_logs l ON l.MKNO = t.MKNO AND l.state = 20')    //物流状态要等于20
                ->group('t.IL_state,t.pause_status,t.candel')
                ->where($map2)->select();

            if(count($plist)>0){
                $arr = array();
                // $other = '';     //其他
                $subtotal   = 0;      //小计
                $tol_amount = 0;    //美国发出总数
                $alr_amount = 0;    //香港仓扫描数
                // $arr2 = array(8,12,16,20,60,200);  //与IL_state一一对齐，不在此数组中的IL_state则会进行拼合处理
                $arr3 = array(
                    '1000' =>'在途',
                    '1001' =>'揽件',
                    '1002' =>'疑难',
                    '1003' =>'签收',
                    '1005' =>'派件中',
                    '1004' =>'退回',
                    '1006' =>'拒收',
                    '1012' =>'延迟',//20161013 Jie
                    '1400' =>'清关中',//20161209 Jie
                    '1410' =>'已出关',//20161209 Jie
                );

                $pause    = 0;     //暂停数量
                $pauser   = 0;    //暂停收到数量
                $timeus   = $item['optime'];  //美国发出时间
                $time_two = '';
                foreach($plist as $v){

                    $arr['s'.$v['IL_state']]['IL_state'] = $v['IL_state'];

                    //香港仓扫描时间
                    if($v['IL_state'] == '200'){
                        $time_two = $v['optime'];
                    }

                    //每个状态各自的总数
                    if(!isset($arr['s'.$v['IL_state']]['num'])) $arr['s'.$v['IL_state']]['num'] = 0;
                    $arr['s'.$v['IL_state']]['num'] += $v['num'];   //各IL_state快件总数

                    //Man 150819 将 IL_state>=200 即包括快递返回1001等状态的归入 香港已发货
                    if(intval($v['IL_state']) > 200){ //因上方 已加 =200的 这里 只加 >200
                        if(!isset($arr['s200'])) $arr['s200']=array();
                        if(!isset($arr['s200']['num'])) $arr['s200']['num'] = 0;
                        $arr['s200']['num'] += $v['num'];

                        if(!isset($arr['s200']['yy'])) $arr['s200']['yy'] = 0;
                        $arr['s200']['yy'] += $v['num'];
                    }

                    //美国发出数量
                    if($v['pause_status'] == '20'){
                        if(!isset($arr['s'.$v['IL_state']]['tt'])) $arr['s'.$v['IL_state']]['tt'] = 0;
                        $arr['s'.$v['IL_state']]['tt'] += $v['num'];    //暂停数量

                        $pause += $v['num'];

                    }

                    //香港扫描件数量
                    if($v['pause_status'] == '20' && $v['candel'] == '1'){
                        if(!isset($arr['s'.$v['IL_state']]['pp'])) $arr['s'.$v['IL_state']]['pp'] = 0;
                        $arr['s'.$v['IL_state']]['pp'] += $v['num'];  //暂停又收到的数量

                        $pauser += $v['num'];

                    }
                    //统计其他状态的各自的总数    //Jie 2015-08-18  停用
                    // if(!in_array($v['IL_state'],$arr2)){

                    //     $other .= $v['IL_state'].":".$v['num']." ; ";
                    // }

                    if(!isset($arr['s'.$v['IL_state']]['yy'])) $arr['s'.$v['IL_state']]['yy'] = 0;
                    $arr['s'.$v['IL_state']]['yy'] += $v['num'];
                    $subtotal += $v['num']; //小计

                }
                if(!isset($arr['s16']['num'])) $arr['s16']['num']   = 0;
                if(!isset($arr['s200']['num'])) $arr['s200']['num'] = 0;
                if(!isset($arr['s200']['yy'])) $arr['s200']['yy']   = 0;

                $tol_amount = (intval($subtotal)-intval($arr['s16']['num']));   //美国发出总数
                $alr_amount = (intval($arr['s200']['num'])+intval($pauser));       //香港仓扫描数

                $arr['msg'] = $timeus." 美国发出<span>&nbsp;<b><a target='_blank' href='".U($url,array('mstr'=>$v['mStr1'],'stype'=>'sent'))."'>[".$tol_amount."]</a></b>&nbsp;</span>件".($pause==0?"":("，暂停<span>&nbsp;<b><a target='_blank' href='".U($url,array('mstr'=>$v['mStr1'],'stype'=>'paused'))."'>[".$pause.']</a></b>&nbsp;</span>件'));

                if(isset($arr['s200']['num']) && intval($arr['s200']['num']) > 0){

                    $arr['msg'] .= "；".$time_two."香港已扫描&nbsp;<span><b><a target='_blank' href='".U($url,array('mstr'=>$v['mStr1'],'stype'=>'scanned'))."'>[".$alr_amount."]</a></b></span>&nbsp;件".(intval($tol_amount-$alr_amount) == 0?"。":("，<span style='color:red'>还差<b>".intval($tol_amount-$alr_amount)."</b>件</span>"));
                    if(intval($tol_amount)-intval($alr_amount) == 0){
                        $arr['operate'] = "<font color='green'>通过</font>";
                    }else{
                        $arr['operate'] = "<font color='red'>需查</font>";
                    }

                }else{
                    $arr['operate'] = "";
                }

                //快递状况
                $express = '';      //快递派送状态及对应的数量
                $kdstr   = '';
                $lack_num = $arr['s200']['yy'];
                foreach ($arr3 as $key => $vi) {
                    $kdnum = isset($arr['s'.$key]['num'])?$arr['s'.$key]['num']:0;
                    if(intval($kdnum) > 0){
                        // $express .= "<a target='_blank' href='".U($url,array('mstr'=>$v['mStr1'],'stype'=>'KD','Istate'=>$key))."'>".$kdnum."件".$vi."</a>；";
                        $express .= "<a target='_blank' href='".U($dUrl,array('IL_state'=>$key,'stype'=>'KD','noid'=>$v['noid']))."'>".$kdnum."件".$vi."</a>；";
                        $lack_num = intval($lack_num) - intval($kdnum);
                    }
                }

                $arr['lack_num']   = $lack_num;

                $arr['mstr']       = $mstr;         //中转批号
                $arr['express']    = $express;      //快递状态信息
                // $arr['other']   = $other;
                $arr['tol_amount'] = $tol_amount;  //美国发出总数
                $arr['alr_amount'] = $alr_amount;  //香港仓扫描数
                $arr['subtotal']   = $subtotal;     //每行数据的总数小计

                array_push($list, $arr);
            }

            // $count = count($list);  //统计总数

        }
        return $res = array($count,$list);
    }

    /**
     * 子页 根据mStr1，IL_state获取对应的数据列表
     * @param  [type] $map [description]
     * @return [type]      [description]
     */
    public function showList($mstr='',$stype='',$Istate='',$noid='',$p=1){
        if($stype == 'sent'){       //美国发出

            $map['l.no']       = array('eq',$mstr);   //中转批号
            $map['t.IL_state'] = array('eq','20');     //物流状态

            //20170113 jie
            $list = M('TranList t')->field('t.*,l.no')->join('LEFT JOIN mk_transit_no l ON l.id = t.noid')->where($map)->page($p.',50')->select();
            $count = M('TranList t')->join('LEFT JOIN mk_transit_no l ON l.id = t.noid')->where($map)->count();

            $record_list = M('AccountRecord')->where(array('mStr1'=>$mstr))->order('op_time asc')->select();

            return $res = array($list,$record_list,$count);

        }else if($stype == 'paused'){    //暂停
            $map['l.mStr1']         = array('eq',$mstr);   //mk_logs.中转批号
            $map['l.state']         = array('eq','20');     //mk_logs.state
            $map['t.pause_status']  = array('eq','20');     //20 已暂停

        }else if($stype == 'scanned'){    //香港扫描
            $map2                   = array();
            $map2['t.IL_state']     = 200;

            $map3                   = array();
            $map3['t.IL_state']     = 20;
            $map3['t.candel']       = 1;

            $map4                   = array();
            $map4[]                 = $map2;
            $map4[]                 = $map3;
            $map4['_logic']         = 'or';

            $map['l.mStr1']         = array('eq',$mstr);   //中转批号

            $map['_complex']        = $map4;

        }else if($stype == 'miss'){   //需查  20160307 Jie 2016年之前的批号的快件缺失需查 的查询方式

            // $noid = I('id');

            // 快件管理/对数/需查  20160914 jie
            if($noid == ''){
                $map2['t.pause_status']     = array('eq','0');

                $map3['t.pause_status']     = 20;
                $map3['t.candel']           = 0;

                $map4                   = array();
                $map4[]                 = $map2;
                $map4[]                 = $map3;
                $map4['_logic']         = 'or';

                $map['l.mStr1']    = array('eq',$mstr);   //中转批号
                $map['l.state']    = array('eq','20');     //mk_logs.state 20160307 Jie
                $map['t.IL_state'] = array('eq','20');     //mk_tran_list.IL_state

                $map['_complex']    = $map4;

            }else{  // 20160307 Jie 自2016年3月起，快件缺少需查采用以下查询方式

                $map['t.noid'] = array('eq',$noid);

                $map2 = array();
                $map2['t.pause_status'] = array('eq','0');

                $map3 = array();
                $map3['t.pause_status'] = array('eq','20');
                $map3['t.candel']       = array('eq','0');

                $map4 = array();
                $map4[]         = $map2;
                $map4[]         = $map3;
                $map4['_logic'] = 'or';

                $map['t.IL_state'] = array('eq','20');
                $map[]           = $map4;
                $map['_logic']   = 'and';


                $list = M('TranList t')->field('t.*,l.no')->join('LEFT JOIN mk_transit_no l ON l.id = t.noid')->where($map)->page($p.',50')->select();

                $list_count = M('TranList t')->join('LEFT JOIN mk_transit_no l ON l.id = t.noid')->where($map)->count();
                //每个中转批号对应的备注信息
                $record_list = M('AccountRecord')->where(array('mStr1'=>$mstr))->order('op_time asc')->select();

                return $res = array($list,$record_list,$list_count);
            }

        }else if($stype == 'lost'){ //已发快递中 无批次号的面单 详细数据列表
            $where['t.IL_state'] = array('eq','200');
            $where['l.id']       = array('exp','is NULL');

            $lost_list = M('TranList t')->field('t.*,l.mStr1')->join('LEFT JOIN mk_logs l ON l.MKNO = t.MKNO AND l.state = 20')->where($where)->page($p.',50')->select();
            $lost_count = M('TranList t')->join('LEFT JOIN mk_logs l ON l.MKNO = t.MKNO AND l.state = 20')->where($where)->count();

            return $res = array($lost_list,"",$lost_count);

        }else if($stype == 'normal'){// 批号对数.中转跟单.all  20160914 Jie
            // $map['l.mStr1']    = array('eq',$mstr);   //中转批号  20161215 jie

            //20161215 jie 这样查询才是批号对数上面的总数对应的所有数据
            $map['t.noid']    = array('eq',$noid);   //中转批号

            $list = M('TranList t')->field('t.*,l.no')->join('LEFT JOIN mk_transit_no l ON l.id = t.noid')->where($map)->page($p.',50')->select();
            $count = M('TranList t')->join('LEFT JOIN mk_transit_no l ON l.id = t.noid')->where($map)->count();

            $record_list = M('AccountRecord')->where(array('mStr1'=>$mstr))->order('op_time asc')->select();

            return $res = array($list,$record_list,$count);
            //end
        }else{
            $map['l.mStr1']    = array('eq',$mstr);   //中转批号
            if($Istate != ''){
                $map['t.IL_state'] = array('eq',$Istate);     //mk_tran_list.IL_state
            }else{
                $map['t.IL_state'] = array('between','1000,1010');
            }
        }

        $list = M('Logs l')->field('t.*,l.mStr1 as no')->join('LEFT JOIN mk_tran_list t ON t.MKNO = l.MKNO')->where($map)->page($p.',50')->select();

        $list_count = M('Logs l')->join('LEFT JOIN mk_tran_list t ON t.MKNO = l.MKNO')->where($map)->count();
        //每个中转批号对应的备注信息
        $record_list = M('AccountRecord')->where(array('mStr1'=>$mstr))->order('op_time asc')->select();

        return $res = array($list,$record_list,$list_count);
    }

    /**
     * 中转批号添加备注
     * @param [type] $mstr     [description]
     * @param [type] $username [description]
     * @param [type] $content  [description]
     */
    public function addRecord($mstr,$username,$content){
        $checkFirst = M('Logs')->where(array('mStr1'=>$mstr))->find();

        //检查是否存在这个批号
        if(!$checkFirst){
            $result = array('state'=>'no', 'msg'=>'该批号不存在');
            return $result;
        }
        $data['mStr1']   = $mstr;
        $data['op_user'] = $username;
        $data['op_time'] = date('Y-m-d H:i:s',time());
        $data['content'] = $content;
        $res = M('AccountRecord')->add($data);
        if($res){
            $result = array('state'=>'yes', 'msg'=>'添加成功');
        }else{
            $result = array('state'=>'no', 'msg'=>'添加失败');
        }
        return $result;
    }

//====================== 订单作废 =======================

    public function toVoid($id,$MKNO,$auto_Indent2,$remark,$username){
        //验证
        $checkFirst = M('TranList')->where(array('id'=>$id))->find();

        //验证是否存在
        if(!$checkFirst){
            $result = array('state'=>'no', 'msg'=>'参数错误，请刷新再试');
            return $result;
        }

        //验证美快单号与自定义2
        if($checkFirst['MKNO'] != $MKNO || $checkFirst['auto_Indent2'] != $auto_Indent2){
            $result = array('state' => 'no', 'msg' => '美快单号或自定义2有误');
            return $result;
        }

        // 验证mk_tran_list.optime减去当前的时间是否大于10天
        $nowtime = time();
        if($nowtime - strtotime($checkFirst['optime']) <= 864000){
            $result = array('state' => 'no', 'msg' => '未满足10天以上期限,禁止操作');
            return $result;
        }

        $where['tid']         = array('eq',$id);
        $where['optype']      = array('eq','作废');
        $where['change_item'] = array('exp','is NULL');

        $secondCheck = M('DailyRecord')->where($where)->find();

        if($secondCheck){

            $result = array('state' => 'no', 'msg' => '该单已被作废,请勿重复操作');
            return $result;

        }else{
            $dat['IL_state'] = 1003;  //此方法中的1003为已作废，(其他地方的1003为已完成)
            M('TranList')->where(array('id'=>$id))->save($dat); //更新mk_tran_list  状态为1003

            //保存信息记录到mk_il_logs
            $logsData['MKNO']        = $MKNO;
            $logsData['content']     = '订单已被作废';
            $logsData['create_time'] = date('Y-m-d H:i:s');
            $logsData['status']      = 1003;     //原标识码为500  Jie 2015-09-15 更改标识码为1003以示作废
            $logsData['state']       = 0;     //推送至ERP状态
            $logsData['CID']         = $checkFirst['CID'];      //CID
            M('IlLogs')->add($logsData);

            //保存操作记录到日志
            $recordData['tid']          = $id;
            $recordData['auto_Indent1'] = $checkFirst['auto_Indent1'];
            $recordData['auto_Indent2'] = $checkFirst['auto_Indent2'];
            $recordData['MKNO']         = $checkFirst['MKNO'];
            $recordData['STNO']         = $checkFirst['STNO'];
            $recordData['operate_user'] = $username;
            $recordData['t_optime']     = $checkFirst['optime'];
            $recordData['t_IL_State']   = $checkFirst['IL_state'];      //记录原本的状态
            $recordData['remark']       = $remark;
            $recordData['optype']       = '作废';
            M('DailyRecord')->add($recordData);

            $result = array('state'=>'yes','msg'=>'操作成功');

            return $result;
        }
    }

    /**
     * 撤销订单作废
     * @param  [type] $id           [description]
     * @param  [type] $MKNO         [description]
     * @param  [type] $auto_Indent2 [description]
     * @param  [type] $remark       [description]
     * @param  [type] $username     [description]
     * @return [type]               [description]
     */
    public function cancel_void($id,$MKNO,$auto_Indent2,$remark,$username){
        //查询条件
        $map['t.id']       = array('eq',$id);
        $map['t.IL_state'] = array('eq','1003');
        $map['d.optype']   = array('eq','作废');
        $checkFirst = M('TranList t')->field('t.*')->join('LEFT JOIN mk_daily_record d ON d.MKNO = t.MKNO')->where($map)->find();

        //验证是否存在
        if(!$checkFirst){
            $result = array('state'=>'no', 'msg'=>'参数错误，请刷新再试');
            return $result;
        }

        //验证美快单号与自定义2
        if($checkFirst['MKNO'] != $MKNO || $checkFirst['auto_Indent2'] != $auto_Indent2){
            $result = array('state' => 'no', 'msg' => '美快单号或自定义2有误');
            return $result;
        }

        if($checkFirst){

            //条件 筛选排除change_item
            $where['tid']         = array('eq',$id);
            $where['optype']      = array('eq','作废');
            $where['change_item'] = array('exp','is NULL');

            //用于取值和更新自身的信息
            $secondCheck = M('DailyRecord')->where($where)->find();

            $dat['IL_state'] = $secondCheck['t_IL_State'];  //原本的状态
            M('TranList')->where(array('id'=>$id))->save($dat); //更新mk_tran_list  状态为原本的状态

            //在change_item字段上填写内容并更新该条信息，以便下次发生再次作废时的筛选排除
            $secondCheck['change_item'] = '已进行撤销作废操作';

            M('DailyRecord')->where(array('id'=>$secondCheck['id']))->save($secondCheck);

            //保存信息记录到mk_il_logs
            $logsData['MKNO']        = $MKNO;
            $logsData['content']     = '订单已撤销作废，恢复正常运作';
            $logsData['create_time'] = date('Y-m-d H:i:s');
            $logsData['status']      = 403;     //403为撤销作废
            $logsData['state']       = 0;     //推送至ERP状态
            $logsData['CID']         = $checkFirst['CID'];      //CID

            M('IlLogs')->add($logsData);

            //保存操作记录到日志
            $recordData['tid']          = $id;
            $recordData['auto_Indent1'] = $checkFirst['auto_Indent1'];
            $recordData['auto_Indent2'] = $checkFirst['auto_Indent2'];
            $recordData['MKNO']         = $checkFirst['MKNO'];
            $recordData['STNO']         = $checkFirst['STNO'];
            $recordData['operate_user'] = $username;
            $recordData['t_optime']     = $checkFirst['optime'];
            $recordData['t_IL_State']   = $checkFirst['IL_state'];      //记录原本的状态
            $recordData['remark']       = $remark;
            $recordData['optype']       = '撤销作废';
            M('DailyRecord')->add($recordData);

            $result = array('state'=>'yes','msg'=>'操作成功');

            return $result;
        }
    }

    /**
     * 疑难件 删除 (规划未完善，暂不与上面的删除功能合拼，待后续完善合拼)
     * @param  [type] $id [id]
     * @return [type]     [description]
     */
    public function delete_nine($id,$username){

        $checkFirst = M('TranList')->where(array('id'=>$id))->find();

        if(!$checkFirst){
            $result = array('state'=>'no', 'msg'=>'参数错误，请刷新再试');
            return $result;
        }

        //IL_state < 200  才可以删除 无需验证mk_tran_list.candel的值
        if($checkFirst['IL_state'] >= '200'){
            $result = array('state' => 'no', 'msg' => '非法操作');
            return $result;
        }

        //查找mk_logs里面是否有这个MKNO
        $did = M('Logs')->where(array('MKNO'=>$checkFirst['MKNO']))->select();

        //查找tran_order里面是否有这个lid
        $lid = M('TranOrder')->where(array('lid'=>$id))->select();

        $Model = M();   //实例化
        $Model->startTrans();//开启事务 

        //先赋予默认的错误值，以便后面的判断，避免由于该变量未定义，系统自动判断不存在而报错
        $del = $dels = false;

        //mk_logs有这个MKNO，tran_order也有此lid，则都删除
        if($did && $lid){

            //备份
            foreach($lid as $item){
                M('TranOrderBackup')->add($item);
            }

            foreach($did as $item2){
                M('LogsBackup')->add($item2);
            }

            //删除
            $del  = M('Logs')->where(array('MKNO'=>$checkFirst['MKNO']))->delete();
            $dels = M('TranOrder')->where(array('lid'=>$id))->delete();

        }else if($did && !$lid){//如果mk_logs有这个MKNO，而tran_order没有此lid，则执行mk_logs的删除

            //备份
            foreach($did as $item){
                M('LogsBackup')->add($item);
            }
            //删除
            $del  = M('Logs')->where(array('MKNO'=>$checkFirst['MKNO']))->delete();

        }else if(!$did && $lid){//如果mk_logs没有这个MKNO，而tran_order有此lid，则执行tran_order的删除

            //备份
            foreach($lid as $item){
                M('TranOrderBackup')->add($item);
            }

            //删除
            $dels = M('TranOrder')->where(array('lid'=>$id))->delete();

        }else if(!$did && !$lid){

            //mk_logs没有这个MKNO，tran_order也没有对应$id的lid,则tran_list里面的此ID数据可以执行删除
            //备份
            M('TranListBackup')->add($checkFirst);
            //删除
            M('TranList')->where(array('id'=>$id))->delete();

            //校验是否为申通，如果是则 清除 mk_stnolist对应的申通号 部分信息
            if($checkFirst['TranKd'] == '1'){

                $data['uuid']        = NULL;
                $data['uuidtime']    = NULL;
                $data['status']      = 0;
                $data['MKNO']        = NULL;
                $data['usetime']     = NULL;
                $data['messages']    = NULL;
                $data['kd100status'] = 0;
                $data['cuid']        = NULL;

                M('Stnolist')->where(array('STNO'=>$checkFirst['STNO']))->limit(1)->save($data);
            }

                //保存操作记录到日志
                $recordData['tid']          = $id;
                $recordData['auto_Indent1'] = $checkFirst['auto_Indent1'];
                $recordData['auto_Indent2'] = $checkFirst['auto_Indent2'];
                $recordData['MKNO']         = $checkFirst['MKNO'];
                $recordData['STNO']         = $checkFirst['STNO'];
                $recordData['operate_user'] = $username;
                $recordData['t_optime']     = $checkFirst['optime'];
                $recordData['t_IL_State']   = $checkFirst['IL_state'];
                $recordData['remark']       = '疑难件删除';
                $recordData['optype']       = '删除';
                M('DailyRecord')->add($recordData);

                //插入一条新的物流信息信息到il_logs中
                $logsData['MKNO']        = $checkFirst['MKNO'];
                $logsData['content']     = '该面单为疑难件，已被删除';
                $logsData['create_time'] = date('Y-m-d H:i:s');
                $logsData['status']      = 1003;      //原标识码为400  Jie 2015-09-15 更改标识码为1003以示删除
                $logsData['state']       = 0;       //推送至ERP状态
                $logsData['CID']         = $checkFirst['CID'];      //CID
                M('IlLogs')->add($logsData);

            $Model->commit();//提交事务成功

            $result = array('state' => 'yes', 'msg' => '删除成功');

            return $result;

        }

        //如果已经在mk_logs或tran_order上进行了删除，则tran_list也要删除对应的ID数据
        if($del || $dels){

            //备份
            M('TranListBackup')->add($checkFirst);
            //删除
            M('TranList')->where(array('id'=>$id))->delete();

            //校验是否为申通，如果是则 清除 mk_stnolist对应的申通号 部分信息
            if($checkFirst['TranKd'] == '1'){

                $data['uuid']        = NULL;
                $data['uuidtime']    = NULL;
                $data['status']      = 0;
                $data['MKNO']        = NULL;
                $data['usetime']     = NULL;
                $data['messages']    = NULL;
                $data['kd100status'] = 0;
                $data['cuid']        = NULL;

                M('Stnolist')->where(array('STNO'=>$checkFirst['STNO']))->limit(1)->save($data);
            }

                //保存操作记录到日志
                $recordData['tid']          = $id;
                $recordData['auto_Indent1'] = $checkFirst['auto_Indent1'];
                $recordData['auto_Indent2'] = $checkFirst['auto_Indent2'];
                $recordData['MKNO']         = $checkFirst['MKNO'];
                $recordData['STNO']         = $checkFirst['STNO'];
                $recordData['operate_user'] = $username;
                $recordData['t_optime']     = $checkFirst['optime'];
                $recordData['t_IL_State']   = $checkFirst['IL_state'];
                $recordData['remark']       = '疑难件删除';
                $recordData['optype']       = '删除';
                M('DailyRecord')->add($recordData);

                //插入一条新的物流信息信息到il_logs中
                $logsData['MKNO']        = $checkFirst['MKNO'];
                $logsData['content']     = '该面单为疑难件，已被删除';
                $logsData['create_time'] = date('Y-m-d H:i:s');
                $logsData['status']      = 1003;      //原标识码为400  Jie 2015-09-15 更改标识码为1003以示删除
                $logsData['state']       = 0;       //推送至ERP状态
                $logsData['CID']         = $checkFirst['CID'];      //CID
                M('IlLogs')->add($logsData);

            $Model->commit();//提交事务成功

            $result = array('state' => 'yes', 'msg' => '删除成功');

            return $result;
        }else{//其他情况，则终止所有删除行为

            $Model->rollback();//事务有错回滚

            $result = array('state' => 'no', 'msg' => '删除失败');

            return $result;
        }

    }

//============== 已删除  查看 ==========
    /**
     * 获取某行数据详细信息
     * @param  [type] $id [被编辑的id]
     * @return [type]     [description]
     */
    public function getInfo_del($id){

        $info = M('TranListBackup')->where(array('id'=>$id))->find();

        //查询面单所包含的商品
        $pro_list = M('TranListBackup l')->field('o.detail,o.number,o.price')->join('LEFT JOIN mk_tran_order_backup o ON l.id = o.lid')->where(array('l.id'=>$id))->select();

        return $res = array($info,$pro_list);
    }

    /**
     * 获取物流信息
     * @param  [type] $info [description]
     * @return [type]       [description]
     */
    public function getMsg_del($info){
        $msg = M('IlLogs')->where(array('MKNO'=>$info['MKNO']))->order('create_time desc')->select();

        return $msg;
    }

    /**
     * 删除某个订单的手动加入的物流信息
     * @return [type] [description]
     */
    public function _del_its_msg($id){
        $info = M('il_logs')->where(array('id'=>$id))->find();

        if(!$info){
            return array('state'=>'no', 'msg'=>'信息不存在或已被删除，请刷新再试');
        }

        //已经推送到erp的物流信息，不予以删除
        if($info['state'] != 0){
            return array('state'=>'no', 'msg'=>'物流信息已被推送，不可删除');
        }

        M()->startTrans();

        $del = M('il_logs')->where(array('id'=>$id))->delete();

        // 已删除之后，查找最新的物理信息
        if($del !== false){
            $newest = M('il_logs')->where(array('MKNO'=>$info['MKNO']))->order('id desc')->find();

            $list_data = array();
            $list_data['ex_time']    = $newest['create_time'];
            $list_data['ex_context'] = $newest['content'];
            $list_data['IL_state']   = $newest['status'];

            $save_list = M('tran_list')->where(array('MKNO'=>$newest['MKNO']))->save($list_data);
            
            if($save_list !== false){
                M()->commit();
                return array('state'=>'yes', 'msg'=>'操作成功');
            }else{
                M()->rollback();
                return array('state'=>'no', 'msg'=>'操作失败');
            }
        }else{
            M()->rollback();
            return array('state'=>'no', 'msg'=>'信息不存在或已被删除，请刷新再试');
        }
    }
}