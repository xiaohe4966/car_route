<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\api\controller\Xiaohe;

use think\Db;
use think\Config;
/**
 * 首页接口
 */
class Refund extends Xiaohe
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



    /* 
    * @Description: 获取退款信息
    * @return: 
    */     
    public function get_refund_data()
    {
        $data['refund_money'] = Config::get('site.refund_money');
        $data['refund_time'] = Config::get('site.refund_time');
        return json($data);
    }

    //申请退款
    public function apply_refund($id,$ps=null)
    {
        $this->is_uid();
        
        $order = Db::name('route_school')->find($id);
        
        //判断发车时间   24小时内 扣手续费 退还优惠券   》2小时前      2小时内 不退票
        // halt($this->get_stime_id($order['stime_id']));
        // ["id"] => int(4)
        // ["time"] => int(1605481200)
        // ["time_s"] => string(5) "07:00"
        $time_s = $this->get_stime_id($order['stime_id'])['time_s'];//"07:00"
        $stime = strtotime($order['stime_date'].' '.$time_s);

        //状态:0=待支付,1=已支付,2=超时,3=已退款,4=申请退款中
        switch ($order['pay_status']) {
            case '0':
                $this->error('待支付！');
                break;
            case '2':
                $this->error('待支付！！');
                break;
            case '3':
                $this->error('已退款');
                break;
            case '4':
                $this->error('申请退款中');
                break;
            default:
                break;
        }

        //退款单号
        if(empty($order['tk_order'])){
            $updata['tk_order'] = $this->rand_order('TK');
        }else{
            $updata['tk_order'] = $order['tk_order'];
        }
            
            $updata['tk_time'] = time();
            $updata['pay_status'] = 4;

        if($stime<(time()+2*3600)){
            $this->error('发车前2小时才可以退款！');
        }elseif($stime<(time()+24*3600)){
            //退优惠券
            // $this->tui_coupons($order['cou_id']);//退还优惠券
            //扣手续费
            $updata['tk_money'] = $order['price'] - Config::get('site.refund_money');

        }else{
            //直接退款
            
            $updata['tk_money'] = $order['money'];
            
           


        }
        $updata['ps'] = $ps;

        $res = Db::name('route_school')->where('id',$id)->update($updata);
        if($res){
            // $this->tui_coupons($order['cou_id']);//退还优惠券
            $res_tk = $this->tk($updata['tk_order'],$order['money']);
            if($res_tk['code'] == 1){
                $this->update_tk_status($updata['tk_order']);//成功
                $this->tui_coupons($order['cou_id']);//退还优惠券
                
            }
            return json($res_tk);
        }


        
        // halt($order);
        // $order['']

    }




    //后台退款
    public function refund_admin($ids,$ps=null)
    {
        $errormsg = null;
        $ci = 0;
        $errorci = 0;
        $orders = Db::name('route_school')->where('id','in',$ids)->select();
        
        foreach ($orders as $key => $order) {
            
           

                //判断发车时间   24小时内 扣手续费 退还优惠券   》2小时前      2小时内 不退票
            // halt($this->get_stime_id($order['stime_id']));
            // ["id"] => int(4)
            // ["time"] => int(1605481200)
            // ["time_s"] => string(5) "07:00"
            $time_s = $this->get_stime_id($order['stime_id'])['time_s'];//"07:00"
            $stime = strtotime($order['stime_date'].' '.$time_s);

            $error_s = false;
            //状态:0=待支付,1=已支付,2=超时,3=已退款,4=申请退款中
            switch ($order['pay_status']) {
                case '0':
                    $error_s = true;
                    
                    $errormsg = $errormsg.'ID:'.$order['id'].'待支付！'.PHP_EOL;
                    break;
                case '2':
                    $error_s = true;
                    $errormsg = $errormsg.'ID:'.$order['id'].'待支付！！'.PHP_EOL;
                    break;
                case '3':
                    $error_s = true;
                    $errormsg = $errormsg.'ID:'.$order['id'].'已退款'.PHP_EOL;
                    break;
                case '4':
                    $error_s = true;
                    $errormsg = $errormsg.'ID:'.$order['id'].'申请退款中'.PHP_EOL;
                    break;
                default:
                    break;
            }

            if($error_s){
                $errorci++;
                continue;
            }

            //退款单号
            if(empty($order['tk_order'])){
                $updata['tk_order'] = $this->rand_order('TKSYS');
            }else{
                $updata['tk_order'] = $order['tk_order'];
            }
                
                $updata['tk_time'] = time();
                $updata['pay_status'] = 4;

            if($stime<(time())){
                $errormsg = $errormsg.'ID:'.$order['id'].'发车前才可以退款！'.PHP_EOL;
                $errorci++;
                continue;
            }else{  //发车前60秒可以退款//($stime<(time()+60))
                //退优惠券
                // $this->tui_coupons($order['cou_id']);//退还优惠券
                // //扣手续费
                // $updata['tk_money'] = $order['price'] - Config::get('site.refund_money');

        
                //直接退款
                
                $updata['tk_money'] = $order['money'];
                
                


            }


            $res = Db::name('route_school')->where('id',$order['id'])->update($updata);
            if($res){
                
                $res_tk = $this->tk($updata['tk_order'],$order['money']);
                if($res_tk['code'] == 1){
                    $this->update_tk_status($updata['tk_order']);

                    $this->tui_coupons($order['cou_id']);//退还优惠券
                    $ci++;
                }else{
                    $errorci++;
                }
                // return json($res_tk);
            }




        }
        
       return('总退款数：'.$ci.'错误数：'.$errorci.PHP_EOL.$errormsg);


        
        // halt($order);
        // $order['']

    }

    //退还优惠券
    protected function tui_coupons($id){
        Db::name('coupons_user')->where('id',$id)->update(['usetime'=>null,'status'=>'0']);
    }


    protected function tk($tk_order,$money)
    {
        $order = Db::name('route_school')->where('tk_order',$tk_order)->find();

        $app = $this->get_pay_app();
        //// 参数分别为：微信订单号、商户退款单号、订单金额、退款金额、其他参数
        // halt($order);
        $res = $app->refund->byOutTradeNumber($order['order'], $order['tk_order'] , intval($order['money']*100), intval($order['tk_money']*100),  [ 
            'refund_desc' => '校园专线退票',
            ]);

        file_put_contents('./'.$tk_order,json_encode($res));
        $res['code'] = 0;
        $res['msg'] = '退款失败';

        if($res['return_code']=='SUCCESS'){
            if($res['result_code']=='SUCCESS'){
                $res['code'] = 1;
                $res['msg'] = '退款成功';
                Db::name('route_school')->where('tk_order',$tk_order)->update(['pay_status'=>3]);
            }
           
        }
            // {"return_code":"SUCCESS",
                // "return_msg":"OK",
                // "appid":"wx0cab47881bf85892",
                // "mch_id":"1504804021",
                // "nonce_str":"TEd5vcAsJPVkCuj3",
                // "sign":"144122307D0967A7ED33F894932EC21B",
                // "result_code":"SUCCESS",
                // "transaction_id":"4200000749202011161947820297",
                // "out_trade_no":"SCH2020111652971005",
                // "out_refund_no":"TK2020111649535299",
                // "refund_id":"50300106212020111603999462914",
                // "refund_channel":null,"refund_fee":"100",
                // "coupon_refund_fee":"0",
                // "total_fee":"100",
                // "cash_fee":"100",
                // "coupon_refund_count":"0",
                // "cash_refund_fee":"100"}





            // appid: "wxcee881d05d36f9be"
            // err_code: "REFUNDNOTEXIST"
            // err_code_des: "not exist"
            // mch_id: "1603577433"
            // nonce_str: "oJcLNLe5JYSf8lBW"
            // result_code: "FAIL"
            // return_code: "SUCCESS"
            // return_msg: "OK"
            // sign: "E4E4FE98A4A02F2DD2A52EBDAC7A877E"

        return $res;
    }



    /**
     * 首页
     *
     */
    public function query_tk($tk_order='TKSYS2020112649565348')
    {
        $app = $this->get_pay_app();
        $res = $app->refund->queryByOutRefundNumber($tk_order);
        $this->success('ok',$res);
    }


    public function update_tk_status($tk_order)
    {


        $app = $this->get_pay_app();
        $res = $app->refund->queryByOutRefundNumber($tk_order);
        if($res['return_code']=='SUCCESS'){
            if($res['result_code']=='SUCCESS'){
                Db::name('route_school')->where('tk_order',$tk_order)->update(['pay_status'=>3]);
            }
        }
        // $this->success('ok',$res);
    }
}
