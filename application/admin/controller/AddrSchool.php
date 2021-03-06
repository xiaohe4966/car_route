<?php

namespace app\admin\controller;

use app\common\controller\Backend;


use think\Db;
/**
 * 校园专线地址管理
 *
 * @icon fa fa-circle-o
 */
class AddrSchool extends Backend
{
    
    /**
     * AddrSchool模型对象
     * @var \app\admin\model\AddrSchool
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\AddrSchool;

    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */







         /**
     * 删除
     */
    public function del($ids = "")
    {
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();
            $count = 0;
            Db::startTrans();
            try {
                foreach ($list as $k => $val) {

                    //判断 路线ID是否有其他地方已经存储！没有可以删除
                    $s_id = $val['id'];

                    $order = Db::name('route_school')->where('s_id',$val['id'])->find();
                    if($order){
                        $this->error('上车点ID:【'.$val['id'].'】，有订单ID:【'.$order['id'].'】，订单号为:'.$order['order'].'不能删除！',5);
                    }


                    $route = Db::name('route_price')->where('s_id',$val['id'])->find();
                    if($route){
                        $this->error('上车点ID:【'.$val['id'].'】，有路线ID:【'.$route['id'].'】，路线名为:'.$route['name'].'不能删除！',5);
                    }

                      //2020-11-23 09:35:26 
                    $count += $val->delete();
                }
                // halt($list);
                Db::commit();
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }
    

}
