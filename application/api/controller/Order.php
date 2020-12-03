<?php

namespace app\api\controller;

use app\common\controller\Api;
use EasyWeChat\Factory;
use think\Db;

use think\Config;
use think\Validate;//验证
use fast\Random;
use fast\Http;

use app\api\controller\Xiaohe;


/**
 * 公共方法
 */
class Order extends Xiaohe
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    protected $stores = null;


    public $addr_school = [];

    public $uid = '';
    public function _initialize()
    {
        parent::_initialize();
     
        $uid = $this->request->param('uid');//用户uid 需要解密
        if(!empty($uid))$this->uid_de($uid);
        $this->addr_school = Db::name('addr_school')->column('*','id');
        // $this->request->filter('trim,strip_tags,htmlspecialchars');
    }


 




    
    //                    _ooOoo_
	//                   o8888888o
	//                   88" . "88
	//                   (| -_- |)
	//                   O\  =  /O
	//                ____/`---'\____
	//              .'  \\|     |//  `.
	//             /  \\|||  :  |||//  \
	//            /  _||||| -:- |||||-  \
	//            |   | \\\  -  /// |   |
	//            | \_|  ''\-/''  |   |
	//            \  .-\__  `-`  ___/-. /
	//          ___`. .'  /-.-\  `. . __
	//       ."" '<  `.___\_<|>_/___.'  >'"".
	//      | | :  `- \`.;`\ _ /`;.`/ - ` : | |
	//      \  \ `-.   \_ __\ /__ _/   .-` /  /
	// ======`-.____`-.___\_____/___.-`____.-'======
	//                    `=-='



    /* 
    * @Description: 添加包车
    * @return: 
    */     
    public function baoche_add()
    {
        $data = $this->request->param();

        $this->is_uid();
        // halt($data);
        $indata['uid'] = $this->uid;
        $indata['saddr'] = $data['saddr'];
        $indata['eaddr'] = $data['eaddr'];

        $indata['tel1'] = $data['tel1'];
        $indata['stime'] = strtotime($data['stime']);
        $indata['createtime'] = time();


        $indata['carjson'] = $data['carjson'];

        $indata['return_status'] = $data['return_status'];
        $indata['name'] = $data['name'];
        // $indata['saddr'] = $data['saddr'];
        $id  = Db::name('route_baoche')->insertGetId($indata);
        if($id){
            $this->success('添加成功',['id'=>$id]);
        }else{
            $this->error('添加失败');
        }

    }


    /* 
    * @Description: 获取包车信息
    * @param: 
    * @return: 
    */     
    public function get_baoche($id)
    {
        $this->is_uid();
        $data = Db::name('route_baoche')->find($id);
        if($data){

            if($data['uid']!=$this->uid){
                $this->error('非自己提交的信息！');
            }


            $data['stime'] = date('Y-m-d H:i',$data['stime']);

            $car_list =  json_decode(html_entity_decode($data['carjson']),true);
            $new_car = array();
            $car = Db::name('car')->where('id','>',0)->column('name','id');
            foreach ($car_list as $key => $val) {
                $new_car[] = $car[$val['id']].'数量:'.$val['num'];
            }
            $data['carlist'] = $new_car;
            // halt($data);

            $this->success('ok',$data);
        }else{
            $this->error('没有找到');
        }

    }




    /* 
    * @Description: 添加企业班车
    * @param: 
    * @return: 
    */     
    public function banche_add()
    {
        $data = $this->request->param();
        $this->is_uid();
        $indata['uid'] = $this->uid;
        $indata['tel'] = $data['tel'];
        $indata['ltdname'] = $data['ltdname'];

        $indata['createtime'] = time();

        $indata['name'] = $data['name'];

        $indata['num'] = $data['num'];


        $id  = Db::name('route_banche')->insertGetId($indata);
        if($id){
            $this->success('添加成功',['id'=>$id]);
        }else{
            $this->error('添加失败');
        }
    }




    /* 
    * @Description: 添加定制路线
    * @return: 
    */     
    public function dingzhi_add()
    {
        $data = $this->request->param();

        $this->is_uid();
        // halt($data);
        $indata['uid'] = $this->uid;
        $indata['saddr'] = $data['saddr'];
        $indata['eaddr'] = $data['eaddr'];

        $indata['tel'] = $data['tel'];
        $indata['stime'] = strtotime($data['stime']);
        $indata['etime'] = strtotime($data['etime']);
        $indata['createtime'] = time();


        // $indata['name'] = $data['name'];
        // $indata['saddr'] = $data['saddr'];
        $id  = Db::name('route_dingzhi')->insertGetId($indata);
        if($id){
            $this->success('添加成功',['id'=>$id]);
        }else{
            $this->error('添加失败');
        }

    }




    //                    _ooOoo_
	//                   o8888888o
	//                   88" . "88
	//                   (| -_- |)
	//                   O\  =  /O
	//                ____/`---'\____
	//              .'  \\|     |//  `.
	//             /  \\|||  :  |||//  \
	//            /  _||||| -:- |||||-  \
	//            |   | \\\  -  /// |   |
	//            | \_|  ''\-/''  |   |
	//            \  .-\__  `-`  ___/-. /
	//          ___`. .'  /-.-\  `. . __
	//       ."" '<  `.___\_<|>_/___.'  >'"".
	//      | | :  `- \`.;`\ _ /`;.`/ - ` : | |
	//      \  \ `-.   \_ __\ /__ _/   .-` /  /
	// ======`-.____`-.___\_____/___.-`____.-'======
	//                    `=-='



    /* 
    * @Description: 买卡order
    * @param: 
    * @return: 
    */     



    /* 
    * @Description: 
    * @param: $type 1= 全部   2=未支付    3=待出行  4=已支付  5=以前支付的订单
    * @return: 
    */     
    public function get_my_order_list($type='1')
    {
        $this->is_uid();

        $createtime = [ '*,FROM_UNIXTIME(createtime,"%Y-%m-%d %H:%i:%s") as date'];
        if($type == '1'){
            //班车
            $data['banche'] = Db::name('route_banche')->where('uid',$this->uid)->field($createtime)->order('createtime desc')->select();

            //包车
            $data['baoche'] = Db::name('route_baoche')->where('uid',$this->uid)->field($createtime)->order('createtime desc')->select();
            foreach ($data['baoche'] as $key => $val) {
                $car_list =  json_decode(html_entity_decode($val['carjson']),true);
                $new_car = array();
                $car = Db::name('car')->where('id','>',0)->column('*','id');
                foreach ($car_list as $key2 => $val2) {
                    // $new_car[] = $car[$val2['id']].'数量:'.$val2['num'];
                    $temp = $car[$val2['id']];
                    $temp['num'] = $val2['num'];
                    $new_car[] = $temp;
                }
                $data['baoche'][$key]['cardata'] = $new_car;
                $data['baoche'][$key]['stime'] =date('Y-m-d H:i:s',$val['stime']);
            }
            

            //定制
            $data['dingzhi'] = Db::name('route_dingzhi')->where('uid',$this->uid)->field($createtime)->order('createtime desc')->select();
            foreach ($data['dingzhi'] as $key => $val) {
                $data['dingzhi'][$key]['stime'] =date('Y-m-d H:i:s',$val['stime']);
                $data['dingzhi'][$key]['etime'] =date('Y-m-d H:i:s',$val['etime']);
            }

            $school_where = null;
        }elseif($type == '2'){
            $school_where = ['pay_status'=>'0'];

        }elseif($type == '3'){
            $school_where = ['pay_status'=>'1','stime'=>['>',time()]];

        }elseif($type == '4'){
            $school_where = ['pay_status'=>'1'];

        }elseif($type == '5'){
            $school_where = ['pay_status'=>'1','stime'=>['<',time()]];

        }
        

        //校园专线
        $data['school'] = Db::name('route_school')->where('uid',$this->uid)->where($school_where)->field($createtime)->order('createtime desc')->select();
        foreach ($data['school'] as $key => $school) {
            $data['school'][$key]['s_time'] = $this->get_stime_id($school['stime_id']);//发车时间
            $data['school'][$key]['coupons'] = $this->get_coupons_order_data($school['cou_id']);//优惠券
            $data['school'][$key]['paytime'] = date('Y-m-d H:i:s',$school['paytime']);//支付时间
            //查询这趟车的车牌号
            $data['school'][$key]['carnumber'] = $this->find_car_number($school['routeid'],$school['stime_date'],$school['stime_id']);

            //路线信息
            $data['school'][$key]['route'] = $this->get_school_self($school['routeid']);

            //上车地址
            $data['school'][$key]['s_addr'] = $this->get_addr_id($school['s_id']);
            //下车地址
            $data['school'][$key]['e_addr'] = $this->get_addr_id($school['e_id']);
            
            $data['school'][$key]['qrcode'] = '/assets/orderqr/'.$school['order'].'.jpg';
            if(!file_exists('.'.$data['school'][$key]['qrcode'])){$this->new_qrcode($school['order']);}


        }

        $this->success('ok',$data);
    }




    /* 
    * @Description: 获取校园专线
    * @param: 
    * @return: 
    */     
    public function get_school_self($id)
    {
        $list = Db::name('route')->find($id);

            $list['s_addr'] = $this->addr_school[$list['s_id']];
            $list['e_addr'] = $this->addr_school[$list['e_id']];
            $list['s_time'] = $this->get_stime_ids($list['stime_ids']);
            $list['route'] = Db::name('route_price')
                                    ->alias('rop')
                                    ->where('rop.routeid',$list['id'])
                                    ->join('addr_school addrs','rop.s_id=addrs.id','LEFT')
                                    ->field('rop.*,addrs.addrname,addrs.addr')
                                    ->select();

        return $list;
    }




        /* 
    * @Description: 
    * @param: $type 1= 全部   2=未支付    3=待出行
    * @return: 
    */     
    public function get_my_order_list2($type='1')
    {
        $this->is_uid();

        $createtime = [ '*,FROM_UNIXTIME(createtime,"%Y-%m-%d %H:%i:%s") as date'];
        if($type == '1'){
            //班车
            $data['banche'] = Db::name('route_banche')->where('uid',$this->uid)->field($createtime)->select();
            

            //包车
            $data['baoche'] = Db::name('route_baoche')->where('uid',$this->uid)->field($createtime)->select();
            foreach ($data['baoche'] as $key => $val) {
                $car_list =  json_decode(html_entity_decode($val['carjson']),true);
                $new_car = array();
                $car = Db::name('car')->where('id','>',0)->column('*','id');
                foreach ($car_list as $key2 => $val2) {
                    // $new_car[] = $car[$val2['id']].'数量:'.$val2['num'];
                    $temp = $car[$val2['id']];
                    $temp['num'] = $val2['num'];
                    $new_car[] = $temp;
                }
                $data['baoche'][$key]['cardata'] = $new_car;
            }

            //定制
            $data['dingzhi'] = Db::name('route_dingzhi')->where('uid',$this->uid)->field($createtime)->select();

            $school_where = null;
        }elseif($type == '2'){
            $school_where = ['pay_status'=>'0'];

        }elseif($type == '3'){
            $school_where = ['pay_status'=>'1','stime'=>['>',time()]];



        }
        // {"code":1,"msg":"ok","time":"1605495298","data":{"school":"SELECT *,FROM_UNIXTIME(createtime,\"%Y-%m-%d %H:%i:%s\") as date FROM `car_route_school` WHERE  `uid` = 3  AND `pay_status` = '0'"}}
        // {"code":1,"msg":"ok","time":"1605495356","data":{"school":"SELECT *,FROM_UNIXTIME(createtime,\"%Y-%m-%d %H:%i:%s\") as date FROM `car_route_school` WHERE  `uid` = 3  AND `pay_status` = '0'  AND `simte` > 1605495357"}}

        //校园专线
        $data['school'] = Db::name('route_school')->where('uid',$this->uid)->where($school_where)->field($createtime)->select();
        foreach ($data['school'] as $key => $school) {
            
            $data['school'][$key]['coupons'] = $this->get_coupons_order_data($school['cou_id']);
        }

        $this->success('ok',$data);
    }





    /* 
    * @Description: 创建订单
    * @return: 
    */     
    public function create_card_order()
    {
        $data = $this->request->param();

        $this->is_uid();
        $card =Db::name('card')->find($data['cardid']);

        $indata['uid'] = $this->uid;
        $indata['cardid'] = $data['cardid'];
        $indata['title'] = $card['title'];//卡标题
        $indata['month'] = $card['month'];//
        $indata['price'] = $card['price'];//
        $indata['order'] = $this->rand_order('CARD');//



        // $indata['title'] = $card['title'];//
        $indata['pay_status'] = 0;//使用状态:0=待支付,1=已支付,2=已退款,3=超时支付
        $indata['createtime'] = time();//
        




        $id  = Db::name('card_order')->insertGetId($indata);

        if($id){
            $indata['id'] = $id;
            return $indata;
        }
        //     $this->success('添加成功',['id'=>$id]);
        // }else{
        //     $this->error('添加失败');
        // }
    }



    public function buy_card()
    {
        $data = $this->create_card_order();
        $user = Db::name('user')->find($data['uid']);
        //去支付
        $payment = $this->get_pay_app();

        $pay_data = [
            'body' => $data['title'], //订单说明
            'out_trade_no' => $data['order'],    //订单号
            'total_fee' => ($data['price']*100), //金额分
            // 'spbill_create_ip' => '123.12.12.123', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
            'notify_url' => $this->request->domain().'/api/order/query_card_order?order='.$data['order'], // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'trade_type' => 'JSAPI', // 小程序支付类型的值
            'openid' => $user['openid'],//用户的openid
            ];
            
        $result = $payment->order->unify($pay_data);


        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            $jssdk = $payment->jssdk;
            $config = $jssdk->bridgeConfig($result['prepay_id'], false);
            $config['order'] = $data['order'];
            $config['msg'] = '请支付';
            $config['code'] = 1;
            // halt($config);
            // $prepayId = $result['prepay_id'];
            // $config = $jssdk->sdkConfig($prepayId);
            return json($config);
        }else{
            return json($result);
        }

    }


    public function query_card_order($order)
    {   
        $app = $this->get_pay_app();
        $data = $app->order->queryByOutTradeNumber($order);
        // halt($data);
        //查询是否支付成功
        if ($data['return_code'] == 'SUCCESS' && $data['trade_state'] == 'SUCCESS') {
            //查询如果还没修改订状态就修改状态 ，和加用户的累计金额
            
            $orderdata = Db::name('card_order')->where('order',$order)->where('pay_status','0')->find();
            if($orderdata){   //第一次修改   支付   


                //赠送会员卡
               

                //增加kai
                

                $updata['paytime']   = strtotime($data['time_end']);
                $updata['endtime'] = $updata['paytime'] + $orderdata['month'] * 30 *24*60;//到期时间
                $updata['pay_status'] = 1;
                $up_res = Db::name('card_order')->where('order',$order)->update($updata);
                if($up_res){
                    $this->update_vip($orderdata['uid'],$order);
                }

                
                
            }
        }

        

        $orderdata = Db::name('card_order')->where('order',$order)->find();
        return json($orderdata);

    }


   
}