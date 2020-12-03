<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Config;
use think\Db;

use app\api\controller\Xiaohe;
/**
 * 首页接口
 */
class User extends Xiaohe
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];


    public $uid = '';
    public function _initialize()
    {
        parent::_initialize();
        // header('Access-Control-Allow-Origin: *');
        $uid = $this->request->param('uid');//用户uid 需要解密
        if(!empty($uid))$this->uid_de($uid);
        // halt($this->uid);
        //移除HTML标签
        // $this->request->filter('trim,strip_tags,htmlspecialchars');
    }

    /**
     * 首页
     *
     */
    public function index()
    {
        $this->success('请求成功');
    }


    //获取openid
    public function get_openid($code=null){
    
        $app = $this->get_app();
        $user = $app->auth->session($code);
        if(!empty($user['errcode'])){
            halt($user);
        }
        $openid = $user['openid'];
        // file_put_contents('./'.$openid.'.txt',json_encode($user));
        //上面调取
        $data = array();
        $data['openid'] = $openid;
        $user_data = Db::name('user')->where('openid',$openid)->find();
        if($user_data){
            $data['uid'] = $this->uid_en($user_data['id']);

            $data['logintime'] = time();
            if(empty($user_data['unionid']) && (!empty($user['unionid']))){
                Db::name('user')->where('openid',$openid)->update(['unionid'=>$user['unionid']]);
            }
        }else{

            $indata['openid'] = $openid;
            $indata['createtime'] = time();
            $indata['jointime'] = $indata['createtime'];
            $indata['joinip'] = $this->request->ip();
            $indata['logintime'] = time();

            // $indata['stateswitch'] = 1;
            if(!empty($indata['unionid'])){
                $indata['unionid'] = $user['unionid'];
            }
            $id = Db::name('user')->insertGetId($indata);
            $data['uid'] = $this->uid_en($id);
        }
        
        $data['is']     = $data['uid'];//未知
        return json($data);
    }



    /* 
    * @Description: 修改小程序用户的昵称和头像 微信的参数
    * @param:  昵称 头像
    * @return: json
    */     
    public function che_user($nickname,$img)
    {   
        $this->is_uid();
        $u['id'] = $this->uid;
        $json['code'] = -1;
        $json['msg'] = '修改失败';

        $data['nickname'] = $nickname;
        $data['headurl'] = $img;
        $res = Db::name('user')->where($u)->update($data);
        if($res){
            $json['code'] = 1;
            $json['msg'] = '修改成功';
        }
        return json($json);
    }



    /* 
    * @Description: 获取用户信息
    * @return: 
    */     
    public function get_user()
    {
        $this->is_uid();

        $list['user'] = Db::name('user')->find($this->uid);
        $list['user']['vip_stime'] = date('Y-m-d',$list['user']['vip_stime']);
        $list['user']['vip_etime'] = date('Y-m-d',$list['user']['vip_etime']);


        return json($list);
    }



    /* 
    * @Description: 小程序授权获取手机号
    * @param: 
    * @return: 
    */     
    public function get_user_mobile($code=null)
    {   
        $app = $this->get_app();
        $data = $this->request->param();
        $user = $app->auth->session($code);
        $iv = $data['iv'];
        $encryptedData = $data['encryptedData'];

        $session = $user['session_key'];//$app->sns->getSessionKey($code);
        $decryptedData = $app->encryptor->decryptData($session, $iv, $encryptedData);

        //修改手机号
        Db::name('user')->where('openid',$user['openid'])->update(['mobile'=>$decryptedData['phoneNumber']]);
        
        return $decryptedData['phoneNumber'];
        // halt($decryptedData);
        
    }


    /* 
    * @Description: 添加反馈意见
    * @return: 
    */     
    public function yijian()
    {
        $this->is_uid();
        $data = $this->request->param();
        $indata['uid'] = $this->uid;
        $indata['content'] = $data['content'];
        $indata['images']  = $data['images'];
        $indata['time']  = time();

        $id  = Db::name('yijian')->insertGetId($indata);
        if($id){
            $this->success('添加成功',['id'=>$id]);
        }else{
            $this->error('添加失败');
        }
    }

    public function get_content($id=1)
    {
        $data = Db::name('content')->find($id);
        if($data){
            $this->success('OK',$data);
        }else{
            $this->error('获取失败');
        }
    }

    /* 
    * @Description:  搜索地址
    * @param: 
    * @return: 
    */     
    public function find_addr($key=null)
    {   
        if($key){
            $where['addr'] = ['like','%'.$key.'%'];
            $where2['addrname'] = ['like','%'.$key.'%'];
        }else{
            $where = 'id > 0';
            $where2 = null;
        }
       $list =  Db::name('addr')->where($where)->whereOr($where2)->select();
       return json($list);
    }



    /* 
    * @Description:  搜索校园地址
    * @param: 
    * @return: 
    */     
    public function find_addr_school($key)
    {
        $where['addr'] = ['like','%'.$key.'%'];
        $where2['addrname'] = ['like','%'.$key.'%'];
       $list =  Db::name('addr')->where($where)->whereOr($where2)->select();
       return json($list);
    }



    /* 
    * @Description: 获取会员卡的 信息
    * @return: 
    */     
    public function get_vip_data()
    {
        
        $data['give_num'] = Config::get('site.give_num');//开通会员赠送几张卡；
        $data['car_list'] = Db::name('card')->select();//会员价卡的列表
        $data['coupons']  = Db::name('coupons')->find();

        return json($data);
    }
}
