<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Config;
use think\Db;

use app\api\controller\Xiaohe;
/**
 * car接口
 */
class Car extends Xiaohe
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
        $this->success('请求成功');
    }


    public function get_index_list()
    {
        $data['banner'] = Db::name('banner')->where('switch',1)->select();
        $data['images']['banche'] = Config::get('site.banche');
        $data['images']['dingzhi'] = Config::get('site.dingzhi');
        $data['images']['school'] = Config::get('site.school');

        
        return json($data);
    }


    // //获取openid
    // public function get_openid($code=null){
    
    //     $app = $this->get_app();
    //     $user = $app->auth->session($code);
    //     if(!empty($user['errcode'])){
    //         halt($user);
    //     }
    //     $openid = $user['openid'];
    //     // file_put_contents('./'.$openid.'.txt',json_encode($user));
    //     //上面调取
    //     $data = array();
    //     $data['openid'] = $openid;
    //     $user_data = Db::name('user')->where('openid',$openid)->find();
    //     if($user_data){
    //         $data['uid'] = $this->uid_en($user_data['id']);
    //         if(empty($user_data['unionid']) && (!empty($user['unionid']))){
    //             Db::name('user')->where('openid',$openid)->update(['unionid'=>$user['unionid']]);
    //         }
    //     }else{

    //         $indata['openid'] = $openid;
    //         $indata['createtime'] = time();
    //         $indata['stateswitch'] = 1;
    //         if(!empty($indata['unionid'])){
    //             $indata['unionid'] = $user['unionid'];
    //         }
    //         $id = Db::name('user')->insertGetId($indata);
    //         $data['uid'] = $this->uid_en($id);
    //     }
        
    //     $data['is']     = $data['uid'];//未知
    //     return json($data);
    // }
}
