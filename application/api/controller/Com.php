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
class Com extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    protected $stores = null;






    public function get_car_list()
    {
       $list = Db::name('car')->select();
         return json($list);
    }


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
            // 'cert_path'  => 'path/to/your/cert.pem', // XXX: 绝对路径！！！！
            // 'key_path'   => 'path/to/your/key',      // XXX: 绝对路径！！！！
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
    * @Description: 传入店铺id 返回adminid
    * @param: 店铺id
    * @return: admin_id
    */     
    protected function get_adminid_Bystoresid($storesid)
    {   
        $adminid = Db::name('admin')->where('storesid',$storesid)->value('id');
        return $adminid;
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



    public function FunctionName(Type $var = null)
    {
        # code...
    }

}