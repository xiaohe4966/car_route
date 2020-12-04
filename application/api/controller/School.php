<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Config;
use think\Db;


use think\Hook;//2020-11-26 14:43:44  

use app\api\controller\Xiaohe;
/**
 * 首页接口
 */
class School extends Xiaohe
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public $addr_school = [];
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

        $this->addr_school = Db::name('addr_school')->column('*','id');

        Hook::listen('order_update');//更新订单 超时取消订单
    }

    /**
     * 首页
     *
     */
    public function index()
    {
        $this->success('请求成功');
    }




    /* 
    * @Description:  搜索校园地址
    * @param: 
    * @return: 
    */     
    public function find_addr_school($key=null)
    {
        $where['addr'] = ['like','%'.$key.'%'];
        $where2['addrname'] = ['like','%'.$key.'%'];
       $list =  Db::name('addr_school')->where($where)->whereOr($where2)->select();
       return json($list);
    }



        /* 
    * @Description:  添加校园专线
    * @param: 
    * @return: 
    */     
    public function add_school_order()
    {

        $data = $this->request->param();

        $this->is_uid();
        // halt($data);
        $indata['uid'] = $this->uid;
        $indata['createtime'] = time();
        $indata['tel'] = $data['tel'];

        $indata['status'] = 0;
        $indata['order'] = $this->rand_order('SCH');

        $indata['s_id'] = $data['s_id'];//上车点id
        $indata['e_id'] = $data['e_id'];//下车点id   //2020-11-25 14:28:24增加
        $indata['num'] = empty($data['num'])?1:$data['num'];
        $indata['routeid'] = $data['routeid'];//路线id

        $s_id_data = Db::name('addr_school')->find($indata['s_id']);
        if(!$s_id_data){
            $this->error('没有此上车地点');
        }

        $indata['stime_id'] = $data['stime_id'];//乘车时间id  2020-11-16 14:03:59
        $stime = Db::name('stime')->find($indata['stime_id']);
        if($stime){
            $stime = date('H:i',$stime['time']);
        }else{
            $this->error('没有这个发车时间！');
        }
        $indata['stime_date'] = $data['stime_date'];
        $indata['stime'] = strtotime($indata['stime_date'].' '.$stime);

        
        //2020-11-26 13:31:45  判断票数是否够的
        $yupiao = $this->get_piao_num($indata['routeid'] ,$indata['s_id'] ,$indata['e_id'] ,$indata['stime_date'] ,$indata['stime_id']);
        if($yupiao<$indata['num']){
            $this->error('票数不够'.$indata['num']);
        }

        $indata['price'] = $this->count_price($data['routeid'],$data['s_id'])*$indata['num'];

        $cou_price = 0;//优惠券的价格
        if(!empty($data['cou_id'])){
            $indata['cou_id'] = $data['cou_id'];
            $cou_price = $this->get_my_coupons_price($data['cou_id']);
            if($indata['price']<$cou_price){
                $this->error('此优惠券的金额小于价格，不能使用！');
            }
        }

        $indata['money'] = $indata['price'] - $cou_price;   //实际支付

        $inid = Db::name('route_school')->insertGetId($indata);

        $res = $this->pay_school_order($inid);
        return json($res);
        

    }





    /* 
    * @Description: 判断这个路线的某个日期 时间 上车点 下车剩余的票数是否够
    * @param: 
    * @return: 
    */     
    public function pd_ren_num_gou(){

        return true;
    }

    public function pay_school_order_id($id)
    {
        $res = $this->pay_school_order($id);
        return json($res);
    }



    /* 
    * @Description: 获取我的优惠券金额
    * @param: 优惠券的订单id
    * @return: 
    */     
    public function get_my_coupons_price($cou_id)
    {
        $order = Db::name('coupons_user')->where('uid',$this->uid)->where('id',$cou_id)->find();
        if(!$order){
            $this->error('没有此优惠券！');
        }
        //使用状态:0=待使用,1=已使用,2=已过期
        if($order['status']==1){
            $this->error('此优惠券已使用！');
        }

        if($order['status']==2){
            $this->error('此优惠券已过期！');
        }

        $res = Db::name('route_school')->where('cou_id',$cou_id)->where('pay_status','0')->find();
        if($res){
            $this->error('此优惠券已在未支付订单里面！');
        }

        $coupons = Db::name('coupons')->find($order['couponsid']);

        return $coupons['amount'];
    }




    /* 
    * @Description: 支付校园专线订单
    * @param: 
    * @return: 
    */     
    public function pay_school_order($id)
    {
        $data = Db::name('route_school')->find($id);

        $user = Db::name('user')->find($data['uid']);
        //去支付
        $payment = $this->get_pay_app();

        $pay_data = [
            'body' => '校园专享',//$data['title'], //订单说明
            'out_trade_no' => $data['order'],    //订单号
            'total_fee' => ($data['money']*100), //金额分
            // 'spbill_create_ip' => '123.12.12.123', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
            'notify_url' => $this->request->domain().'/api/school/query_school_order?order='.$data['order'], // 支付结果通知网址，如果不设置则会使用配置里的默认地址
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
            return $config;
        }else{
            return $result;
        }

    }


    public function query_school_order($order)
    {   
        $app = $this->get_pay_app();
        $data = $app->order->queryByOutTradeNumber($order);
        // halt($data);
        //查询是否支付成功
        if(!empty($data['trade_state'])){
            if ($data['return_code'] == 'SUCCESS' && $data['trade_state'] == 'SUCCESS') {
                //查询如果还没修改订状态就修改状态 ，和加用户的累计金额
                
                $orderdata = Db::name('route_school')->where('order',$order)->where('pay_status','0')->find();
                if($orderdata){   //第一次修改   支付   


                  
                    $updata['paytime']   = strtotime($data['time_end']);
                    // $updata['endtime'] = $updata['paytime'] + $orderdata['month'] * 30 *24*60;//到期时间
                    $updata['pay_status'] = 1;
                    $up_res = Db::name('route_school')->where('order',$order)->update($updata);
                    if($up_res){
                        $this->update_use_cou($order);
                    }
                    $this->new_qrcode($order);
                    
                    
                }
            }else{
                $this->del_school_order($order);//删除此订单
            }
        }else{
            $this->del_school_order($order);//删除此订单
            return json(['code'=>-1,'msg'=>'没有此订单号！']);
        }
        $orderdata = Db::name('route_school')->where('order',$order)->find();
        $data['orderdata'] = $orderdata;

        return json($data);

    }



    public function del_school_order($order)
    {   
        // 状态:0=待支付,1=已支付,2=超时,3=已退款,4=申请退款中
        $orderdata = Db::name('route_school')->where('order',$order)->find();
        if($orderdata['pay_status']!='1'){
            Db::name('route_school')->where('order',$order)->delete();
        }
    }



    /* 
    * @Description: 修改使用优惠券
    * @param: 
    * @return: 
    */     
    public function update_use_cou($order)
    {
        $orderdata = Db::name('route_school')->where('order',$order)->find();
        if($orderdata['cou_id']>0){
            Db::name('coupons_user')->where('id',$orderdata['cou_id'])->update(['usetime'=>$orderdata['paytime'],'status'=>'1']);
        }
    }


    /* 
    * @Description: 价格
    * @param: 
    * @return: 
    */     
    public function count_price($routeid,$s_id)
    {
        $res = Db::name('route_price')->where('routeid',$routeid)->where('s_id',$s_id)->find();
        if($res){
            return $res['price'];
        }else{
            return 999;
        }
    }




    /* 
    * @Description: 获取所有的校园专线
    * @param: 
    * @return: 
    */     
    public function get_school_list($type_id=null)
    {
        if($type_id){
            $type_id = ['type_id'=>$type_id];
        }
        $list = Db::name('route')->where('status_switch',1)->where($type_id)->order('weigh desc')->select();
        foreach ($list as $key => $val) {
            $list[$key]['s_addr'] = $this->addr_school[$list[$key]['s_id']];
            $list[$key]['e_addr'] = $this->addr_school[$list[$key]['e_id']];
            // $list[$key]['s_time'] = $this->get_stime_ids($val['stime_ids']);//获取发车时间
            $list[$key]['route'] = Db::name('route_price')
                                    ->alias('rop')
                                    ->where('rop.routeid',$val['id'])
                                    ->join('addr_school addrs','rop.s_id=addrs.id','LEFT')
                                    ->order('rop.weigh asc')
                                    ->field('rop.*,addrs.addrname,addrs.addr')
                                    ->select();
            $list[$key]['go_date'] = $this->get_route_go_date($val['id']);
        }

        return json($list);
    }


    public function get_route_type_list()
    {
        $data = Db::name('route_type')->select();
        return json($data);
    }

    /* Function Info 
    * Author:      XiaoHe 
    * CreateTime:  2020/11/25 下午2:23:04 
    * LastEditor:  XiaoHe 
    * ModifyTime:  2020/11/25 下午2:23:04 
    * Description: 获取剩余车票  
    * @param: $routeid  路线id
    * @param: $s_id ,上车点
    * @param: $e_id ,下车点
    * @param: $stime_date ,乘车日期
    * @param: $stime_id ，发车时间
    */ 
    public function get_school_num($routeid ,$s_id ,$e_id ,$stime_date ,$stime_id)
    {
        $route_addrid_list = $this->get_route_all_addrid($routeid);//这条路线的 上车点+终点  数组id
        $res_car = $this->get_number_data($routeid,$stime_date,$stime_id);
        if($res_car){
            $ren_num = $res_car['ren_num'];
            $data['ren_num'] = $ren_num;
            $data['piao_num'] = $this->get_piao_num($routeid ,$s_id ,$e_id ,$stime_date ,$stime_id);
            $data['number'] = $res_car['number'];
            $this->success('ok',$data);
        }else{
            $this->error('此路线'.$stime_date.'没有找到改时间点发车');
        }
        
    }





        /* 
    * @Description: 取剩余票数
    * @param: $routeid  路线id
    * @param: $s_id ,上车点
    * @param: $e_id ,下车点
    * @param: $stime_date ,乘车日期
    * @param: $stime_id ，发车时间
    * @return: 
    */     
    public function get_piao_num($routeid ,$s_id ,$e_id ,$stime_date ,$stime_id){
        $route_all = $this->get_buy_piao_num($routeid ,$s_id ,$e_id ,$stime_date ,$stime_id);

        //路线的总人数
        $res_car = $this->get_number_data($routeid,$stime_date,$stime_id);
        $ren_num = $res_car['ren_num'];

        //总票数减去已卖的
        foreach ($route_all as $key3 => $val3) {
            $route_all[$key3] = $ren_num - $val3;
        }

        $num = $this->get_min_pioa_num($route_all,$s_id,$e_id);
        // halt($route_all);
        // $this->get_zhongjian_luxian($routeid,$s_id,$e_id)
        return $num;
    }

    /* 
    * @Description: 取已经卖了多少张
    * @param: $routeid  路线id
    * @param: $s_id ,上车点
    * @param: $e_id ,下车点
    * @param: $stime_date ,乘车日期
    * @param: $stime_id ，发车时间
    * @return: 
    */     
    public function get_buy_piao_num($routeid ,$s_id ,$e_id ,$stime_date ,$stime_id)
    {
        //查询这个路线的这个日期 和发车时间点的  待支付 和已支付
                //附上经过的路线ID   求交集 再减去e_id的交集

        $route_addrid_list = $this->get_route_all_addrid($routeid);//这条路线的 上车点+终点  数组id
        
        $order_list = Db::name('route_school')
            ->where('routeid',$routeid)
            ->where('stime_id',$stime_id)
            ->where('stime_date',$stime_date)
            ->where('pay_status','in','0,1')
            ->select();
        $go_list = array();
        $e_list = array();
        foreach ($order_list as $key => $value) {
            // halt($this->get_zhongjian_luxian($value['routeid'],$value['s_id'],$value['e_id']));
            // $go_list =  
            for ($i=0; $i < $value['num']; $i++) { 
                array_push($go_list,$this->get_zhongjian_luxian($value['routeid'],$value['s_id'],$value['e_id']));
                array_push($e_list,$value['e_id']);
            }
            
        }


        $route_all = array();
        foreach($route_addrid_list as $key9=>$val9){
            $route_all[$val9] = 0;
        }
                
        //合并总路线
        foreach ($go_list as $key1=> $vallist1) {
            foreach($vallist1 as $key => $val) {
                $route_all[$val]++; 
            }
        }
        
        //去除下车点
        foreach ($e_list as $key2 => $val2) {
            $route_all[$val2]--;
        }

   




                
        return $route_all;
        // halt($e_list);
        // halt($route_all);
    }



    


    /* Function Info 
    * Author:      XiaoHe 
    * CreateTime:  2020/11/25 上午10:17:28 
    * LastEditor:  XiaoHe 
    * ModifyTime:  2020/11/25 上午10:17:28 
    * Description: 获取某个路线的 某一天的发车时间列表
    */ 
    public function get_school_go_time($routeid,$date)
    {
       $res = Db::name('number')->where('route_id',$routeid)->where('stime_date',$date)->column('stime_id');
       $list = array();
       foreach ($res as $key => $val) {
            $time = $this->get_stime_id($val);
            $list[] = $time;//['stime_id'=>$val,'time'=>$time];
       }
       $this->success('ok',$list);
    }



    /* Function Info 
    * Author:      XiaoHe 
    * CreateTime:  2020/11/25 上午11:23:00 
    * LastEditor:  XiaoHe 
    * ModifyTime:  2020/11/25 上午11:23:00 
    * Description: 获取某个路线的默认乘车人数
    */ 
    public function get_route_ren_num($routeid)
    {
        $num = Db::name('route')->where('id',$routeid)->value('ren_num');
        if(!$num)$num = 0;

        $this->success('此路线默认可乘人数'.$num,$num);

    }



    /* 
    * @Description:  搜索校园路线
    * @param: 
    * @return: 
    */     
    public function find_school_list($saddr=null,$eaddr=null)
    {
        
        $s_ids = implode(',',$this->find_addr_school_self($saddr));
        $e_ids = implode(',',$this->find_addr_school_self($eaddr));

 
       $list =  Db::name('route_price')
                            // ->where('status_switch',1)
                            ->where('s_id','in',$s_ids)
                            ->whereOr('s_id','in',$e_ids)
                            ->column('routeid');
                            // ->select();
        $list2 = array_unique($list);//去重
        $list = array();
        foreach ($list2 as $key => $value) {
            $temp = $this->get_school_self($value);
            if($temp)
            $list[] = $temp;
        }
       return json($list);
    

    }

    /* 
    * @Description:  搜索校园地址
    * @param: 
    * @return: 
    */     
    public function find_addr_school_self($key)
    {
        $where['addr'] = ['like','%'.$key.'%'];
        $where2['addrname'] = ['like','%'.$key.'%'];
       $list =  Db::name('addr_school')->where($where)->whereOr($where2)->column('id');//field('id')->select();
       return $list;
    }


    /* 
    * @Description: 获取校园专线
    * @param: 
    * @return: 
    */     
    public function get_school($id)
    {
        $list = Db::name('route')->find($id);
        
            $list['s_addr'] = $this->addr_school[$list['s_id']];
            $list['e_addr'] = $this->addr_school[$list['e_id']];
            // $list['s_time'] = $this->get_stime_ids($list['stime_ids']);
            $list['route'] = Db::name('route_price')
                                    ->alias('rop')
                                    ->where('rop.routeid',$list['id'])
                                    ->join('addr_school addrs','rop.s_id=addrs.id','LEFT')
                                    ->order('rop.weigh asc')
                                    ->field('rop.*,addrs.addrname,addrs.addr')
                                    ->select();
      
            

        return json($list);
    }




    
    /* 
    * @Description: 获取校园专线
    * @param: 
    * @return: 
    */     
    public function get_school_self($id)
    {
        $list = Db::name('route')->where('status_switch',1)->where('id',$id)->find();
        if($list){
            $list['s_addr'] = $this->addr_school[$list['s_id']];
            $list['e_addr'] = $this->addr_school[$list['e_id']];
            // $list['s_time'] = $this->get_stime_ids($list['stime_ids']);
            $list['route'] = Db::name('route_price')
                                    ->alias('rop')
                                    ->where('rop.routeid',$list['id'])
                                    ->join('addr_school addrs','rop.s_id=addrs.id','LEFT')
                                    ->order('rop.weigh asc')
                                    ->field('rop.*,addrs.addrname,addrs.addr')
                                    ->select();
        }else{
            $list = null;
        }
        return $list;
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



    public function get_school_order($id)
    {
        $this->is_uid();
        $order = Db::name('route_school')
                ->where('uid',$this->uid)
                ->where('id',$id)
                ->find();

        $order['createtime'] = date('Y-m-d H:i:s',$order['createtime']);

        return json($order);
    }





    /* 
    * @Description: 核验二维码
    * @param: 
    * @return: 
    */     
    public function scan_qrcode($order,$routeid,$stime_id)
    {
        $this->is_uid();
        $user = Db::name('user')->find($this->uid);
        if($user['scan_switch']==0){
            $this->error('非扫码核销员！');
        }
        $orderdata = Db::name('route_school')->where('order',$order)->find();
        if(!$orderdata){
            $this->error('没有此订单!');
        }
        
        //状态:0=待支付,1=已支付,2=超时,3=已退款,4=申请退款中,5=已核验
        switch ($orderdata['pay_status']) {
            case '0':
                $this->error('待支付订单!');
                break;
            case '2':
                $this->error('待支付订单!超时');
                break;
            case '3':
                $this->error('待支付订单!已退款');
                break;
            case '4':
                $this->error('待支付订单!申请退款中');
                break;
            default:
                # code...
                break;
        }

        if($orderdata['stime_date']!=date('Y-m-d')){
            $this->error('此核销码乘车日期：'.$orderdata['stime_date']);
        }
        if($orderdata['scan_status']=='1'){
            $this->error('此订单已核验，核验时间:'.date('Y-m-d H:i:s',$orderdata['scan_time']));
        }


        if($routeid==$orderdata['routeid']  && $stime_id==$orderdata['stime_id']){
           $res_up = Db::name('route_school')->where('order',$order)->update(['scan_time'=>time(),'scan_status'=>1]);
           if($res_up)$this->success('通过核验，欢迎乘车！');
        }else{
            $this->error('请注意乘车时间和乘车路线！');
        }

    }
}
