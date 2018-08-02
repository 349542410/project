<?php
/**
 * PDA
 */

namespace MkAuto\Controller;

use Think\Controller;

class LoginController extends Controller
{

    function _initialize()
    {
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('WAPIURL') . '/Mkil');        //读取、查询操作
        $this->client = $client;        //全局变量
    }

    public function index()
    {
		//根据session判断
        if($MKinfo  = session('MKinfo')){


            //session中不存在此值时
            if (!$MKinfo) {

                //$this->redirect('Index/index');
                $this->display();

            } else {
                $test = '';//"/test/1";
                switch ($MKinfo['usertype']) {//10:称重  20:转发快递   30:中转  40:返仓   50:清关
                    case '10':
                        $this->redirect('Weighing/index' . $test);
                        break;

                    case '20':
                        $this->redirect('ThreeInOne/index' . $test);
                        break;

                    case '30':
                        $this->redirect('Transfer/index' . $test);
                        break;

                    case '40':

                        $this->redirect('ThreeInOne/index' . $test);
                        break;

                    case '50':
                        $this->redirect('ThreeInOne/index' . $test);
                        break;

                    default:
                        $this->error('参数错误', U('Login/index'));
                }

            }

        } else {
            $this->display();
        }

    }

    /**
     * 扫描登陆
     * @return [type] [description]
     */
    public function login_in()
    {
        $uname = trim(I('post.uname'));                    //用户编号>=6位
        $upass = trim(I('post.upass'));                    //用户密码>=8位
        $umd5 = trim(I('post.umd5'));                    //从PDA来，但现在已不再使用

        //Man20150828 增加目的为登录超时时返回 code=-9,但为了避免过期 所以改为 PDA端每次POST都包含fm=pda
        $fm = trim(strtoupper(I('post.fm')));        // 如果从PDA来，则=PDA
        //cookie('fm',(strlen($fm)>0?$fm:''));

        $lang = 'zh-cn';//I('post.lang');				//语言

        //检查规则
        if (strlen($uname) < 6) {
            $this->error('用户名长度不能少于6位');
        }

        if (strlen($upass) < 8) {
            $this->error('密码长度不能少于8位');
        }

        if ($umd5 * 1 == 0) {
            $upass = md5($upass);    //密码加密
        }

        $client = $this->client;

        $data = $client->get_user($uname, $upass);    //查询用户信息

        if ($data['code'] == '0') {
            $this->ajaxReturn($data);
            exit;
        }

        $user = $data['user'];

        switch ($user['usertype']) {        //10:称重  20:转发快递   30:中转  40:返仓   50:清关
            case '10':
                $nickname = '称重';
                break;

            case '20':
                $nickname = '转发快递';
                break;

            case '30':
                $nickname = '中转';
                break;

            case '40':
                $nickname = '返仓';
                break;

            case '50':
                $nickname = '清关';
                break;
            case '60':
                $nickname = '中转到旧金山';
                break;

            default:
                $nickname = '';
        }

        $author = array(
            'ssid' => $user['id'],
            'uname' => $user['username'],
            'usertype' => $user['usertype'],    //权限类型 编号
            'tname' => $user['truename'],
            'phone' => $user['phone'],
            'type_name' => $nickname,    //权限类型 中文名
        );

        //Man20150728为 APP写，不知有无影响因data['user']本身带有密码所以去掉
        $data['user'] = $author;

        session('MKinfo', $author);    //设置session

        $this->ajaxReturn($data);

    }

    //测试时用的退出功能
    public function out()
    {
        session('MKinfo', null);
        $this->redirect('Login/index');
    }

}