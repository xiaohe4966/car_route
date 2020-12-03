<?php

namespace app\api\controller;

use app\common\controller\Api;
use EasyWeChat\Factory;
use think\Db;

use think\Config;
use think\Validate;//验证
use fast\Random;
use fast\Http;


/**
 * 公共方法
 */
class Xiaohe extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    protected $stores = null;




    /* 
    * @Description: 获取支付app
    * @return: 
    */     
    public function get_pay_app(){
        $config = [
            'app_id' => Config('site.app_id'),
            'secret' => Config('site.secret'),
            // 下面为可选项
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',

            'log' => [
                'level' => 'info', //info  'driver' => 'daily',
                'file' => __DIR__.'/wechat_order.log',
            ],

            'mch_id'     => Config('site.mch_id'),//商户号
            'key'        => Config('site.key'),   // API 密钥

            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path'  => 'wxcert/apiclient_cert.pem', // XXX: 绝对路径！！！！
            'key_path'   => 'wxcert/apiclient_key.pem',      // XXX: 绝对路径！！！！
            // 'notify_url' => '默认的订单回调地址',     // 你也可以在下单时单独设置来想覆盖它
            'cert_path'  => 'wxcert/apiclient_cert.pem', // XXX: 绝对路径！！！！
            'key_path'   => 'wxcert/apiclient_key.pem',      // XXX: 绝对路径！！！！
        ];

        $app = Factory::payment($config);
        return $app;
    }



    protected function get_app(){
        $config = [
            'app_id' => Config('site.app_id'),
            'secret' => Config('site.secret'),
            // 下面为可选项
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',

            'log' => [
                'driver' => 'daily',
               // 'level' => 'debug', //info  'driver' => 'daily', 
                'file' => __DIR__.'/wechat.log',
            ],
        ];

        $app = Factory::miniProgram($config);
        return $app;
    }



    //生成订单号
    protected function rand_order($qian=null)
    {
        $order = date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        if($qian){$order = $qian.$order;}
        return $order;
    }



    //获取优惠券订单 的信息
    protected function get_coupons_order_data($id){
        if($id){
            $coupons = Db::name('coupons_user')->find($id);
            $coupons['end_time'] = date('Y-m-d H:i',$coupons['end_time']);
            $coupons['createtime'] = date('Y-m-d H:i',$coupons['createtime']);
            $coupons['usetime'] = date('Y-m-d H:i',$coupons['usetime']);
            
            $coupons['data'] = Db::name('coupons')
                            ->where('id',$coupons['id'])
                            ->field('title,amount,timeu,description')
                            ->find();
        }else{
            $coupons = null;
        }
        
        return $coupons;
    }


    /* 
    * @Description: 创建二维码
    * @param: 
    * @return: 
    */     
    public function new_qrcode($order)
    {   
        if(!file_exists('./assets/orderqr/'.$order.'.jpg')){
            $app = $this->get_app();
            // 或者指定颜色
            $response = $app->app_code->get($order, [
                'width' => 600,
                'line_color' => [
                    'r' => 0,
                    'g' => 0,
                    'b' => 0,
                ],
            ]);

    
            if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
                $filename = $response->saveAs('./assets/orderqr', $order.'.jpg');
            }
        }
    }



    //获取发车时间
    protected function get_stime_ids($ids){
        $time = [ 'id,time,FROM_UNIXTIME(time,"%H:%i") as time_s'];
        $res = Db::name('stime')->where('id','in',$ids)->field($time)->select();
        return $res;
    }


    //获取发车时间
    protected function get_stime_id($id){
        $time = [ 'id,time,FROM_UNIXTIME(time,"%H:%i") as time_s'];
        $res = Db::name('stime')->where('id','in',$id)->field($time)->find();
        return $res;
    }


    
    //获取发车地址
    protected function get_addr_id($id){
       
        $res = Db::name('addr_school')->where('id',$id)->value('addrname');
        return $res;
    }


    //车牌号码
    protected function find_car_number($routeid,$stime_date,$stime_id){
        $res = Db::name('number')
            ->where('route_id',$routeid)
            ->where('stime_date',$stime_date)
            ->where('stime_id',$stime_id)
            ->find();
        if($res){
            return $res['number'];
        }else{
            return null;
        }

    }



    /* Function Info 
    * Author:      XiaoHe 
    * CreateTime:  2020/11/25 上午9:58:51 
    * LastEditor:  XiaoHe 
    * ModifyTime:  2020/11/25 上午9:58:51 
    * Description: 获取路线的出发日期
    */ 
    public function get_route_go_date($routeid,$car_go_time = '23:59:59'){
        $list = Db::name('number')
                    ->where('route_id',$routeid)
                    // ->field('stime_date')
                    ->column('stime_date');

        // 判断发车时间是否过了 过了去除
        $list2 = array();  
        foreach ($list as $key => $val) {
            $time = strtotime($val.' '.$car_go_time); 
            if($time>time()){
                $list2[] = $val;
            }
        }
        return $list2;
    }



    /* Function Info 
    * Author:      XiaoHe 
    * CreateTime:  2020/11/25 下午2:33:19 
    * LastEditor:  XiaoHe 
    * ModifyTime:  2020/11/25 下午2:33:19 
    * Description: 获取路线的全程地址id

    */ 
    public function get_route_all_addrid($routeid){

        $list = Db::name('route_price')
                    ->where('routeid',$routeid)
                    ->order('weigh asc')
                    ->column('s_id');
        $list[] = Db::name('route')->where('id',$routeid)->value('e_id');
 
        return $list;
    }


    
    /* 
    * @Description: 取中间路线
    * @param: 
    * @return: 
    */     
    public function get_zhongjian_luxian($routeid,$s_id,$e_id)
    {
        $route_addrid_list = $this->get_route_all_addrid($routeid);//这条路线的 上车点+终点  数组id
        $s = false;
        $go_list = array();
        foreach ($route_addrid_list as $key => $val) {
            if(!$s){
                if($val==$s_id)$s=true;
            }
            if($s){
                $go_list[] = $val;
            }
            if($val==$e_id)break;
        }

        return $go_list;

    }
    /* 
    * @Description: 获取最小的车票数
    * @param: 车票剩余的张数  数组
    * @param: 上车点
    * @param: 下车点
    * @return: int
    */     
    public function get_min_pioa_num($route_all,$s_id,$e_id){
        $s = false;
        $go_list = array();
        // halt($route_all);
        foreach ($route_all as $key => $val) {
            if(!$s){
                if($key==$s_id)$s=true;
            }
            if($s){
                $go_list[] = $val;
            }
            if($key==$e_id)break;

        }
        return  min($go_list);
        // halt($go_list);
    }

    /* 
    * @Description: 取 某（日期，路线，发车时间）的信息
    * @param: 
    * @return: 
    */     
    public function get_number_data($routeid,$stime_date,$stime_id)
    {
        $res_car = Db::name('number')
        ->where('stime_date',$stime_date)
        ->where('stime_id',$stime_id)
        ->where('route_id',$routeid)
        ->find();
        return $res_car;
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
    * @Description: 修改用户vip状态
    * @param: 
    * @return: 
    */     
    protected function update_vip($id,$order)
    {   
        $user = Db::name('user')->find($id);
        $orderdata = Db::name('card_order')->where('order',$order)->find();

        if($user['vip']){
            $updata['vip_etime'] =  $user['vip_etime'] + ($orderdata['month'] * 30 *24*60*60);//到期时间
            
        }else{
            $updata['vip_stime'] = $orderdata['paytime'];
            $updata['vip_etime'] =  $updata['vip_stime'] + ($orderdata['month'] * 30 *24*60*60);//到期时间
            $updata['vip'] = 1;
        }
        
        // $updata['vip_etime'] =  $user['vip_stime'] + ($orderdata['month'] * 30 *24*60);//到期时间

        $res = Db::name('user')->where('id',$id)->update($updata);
        return $res;
    }


    /* 
    * @Description: 传入adminid 返回店铺id
    * @param: adminid
    * @return: 店铺id
    */     
    protected function get_storesid_Byadminid($adminid)
    {   
        $storesid = Db::name('admin')->where('id',$adminid)->value('storesid');
        return $storesid;
    }

        /* 
    * @Description: 判断是否有店铺 如果有就复制变量 没有就报错退出
    * @param: 店铺id
    * @return: none
    */     
    protected function is_stores($storesid){
        $stores = Db::name('shop_stores')->where('id',$storesid)->where('status',1)->find();
        if($stores){
            $this->stores = $stores;
            return $stores;
        }else{
            return json(['code'=>-1,'msg'=>'没有此店铺']);
        }
    }



   

}