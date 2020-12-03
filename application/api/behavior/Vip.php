<?php

namespace app\api\behavior;
use think\Db;
use think\Config;

class Vip
{
    public function run(&$params)
    {
        // if (request()->isPost()) {
        //     \app\admin\model\AdminLog::record();
        // }

        //先查询会员

        $Vip_list = Db::name('user')->where('vip_etime','>',time())->select();

        $stime = strtotime('first Day of this month 00:00:00');
        $etime = strtotime('first Day of next month 00:00:00');

        // 再查询是否赠送
        // vip_month
        foreach ($Vip_list as $key => $us) {
            if(empty($us['vip_month']) || $us['vip_month'] < 1){
                //赠送vip的优惠券

                $res = Db::name('coupons_user')->where('uid',$us['id'])->where('createtime','>',$stime)->where('createtime','<',$etime)->select();
                if(!$res){
                    $indata['uid'] = $us['id'];

                    $coupons = Db::name('coupons')->find();//优惠券的id

                    $indata['couponsid'] = $coupons['id'];
                    $indata['createtime'] = time();
                    $indata['end_time'] =  $indata['createtime'] + $coupons['timeu'] * 86400;//到期时间
                    $indata['status'] = '0';//使用状态:0=待使用,1=已使用,2=已过期
                    $indata['is_self'] = '0';//领取的方式:0=赠送的,1=自己领取的
                    

                    $ci = 0;
                    for ($i=0; $i < Config::get('site.give_num'); $i++) { 
                       $res = Db::name('coupons_user')->insert($indata);
                       if($res){
                            $ci++;
                       }
                    }

                }
            }
        }




    }

    //订单超时 就修改
    public function OrderUpdate()
    {
        $M = Config::get('site.overtime_minute');//超时时间分钟

        //状态:0=待支付,1=已支付,2=超时,3=已退款,4=申请退款中
        $list = Db::name('route_school')
            ->where('pay_status','0')
            ->where('createtime','<',(time()-$M*60))
            ->update(['pay_status'=>'2']);
    }
}
