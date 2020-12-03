<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\api\controller\Xiaohe;

use think\Db;
/**
 * 首页接口
 */
class Coupons extends Xiaohe
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
        // 
    }



    /* 
    * @Description: 获取我的优惠券   status /0=待使用,1=已使用,2=已过期
    * @return: 
    */     
    public function get_my_coupons($status ='0')
    {
        $this->pd_status();//判断是否过期
        $this->is_uid();
        //使用状态:0=待使用,1=已使用,2=已过期
        $list = Db::name('coupons_user')

                ->where('uid',$this->uid)
                ->where('status',$status)
                ->select();
        $coupons = Db::name('coupons')->column('*','id');
        foreach($list  as $key=>$val){
            $list[$key]['coupons'] = $coupons[$list[$key]['couponsid']];
        }
        return json($list);
    }



    public function pd_status(){
        //使用状态:0=待使用,1=已使用,2=已过期
        $list = Db::name('coupons_user')->where('end_time','<',time())->where('status','0')->update(['status'=>2]);
    }



    /**
     * 首页
     *
     */
    public function index()
    {
        $this->success('请求成功');
    }
}
