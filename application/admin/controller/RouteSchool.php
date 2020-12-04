<?php

namespace app\admin\controller;

use app\common\controller\Backend;

use app\api\controller\Refund;

/**
 * 校园专线
 *
 * @icon fa fa-circle-o
 */
class RouteSchool extends Backend
{
    
    /**
     * RouteSchool模型对象
     * @var \app\admin\model\RouteSchool
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\RouteSchool;
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("payStatusList", $this->model->getPayStatusList());
        $this->view->assign("scanStatusList", $this->model->getScanStatusList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax())
        {
            
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                    ->with(['addrschool','addrschool2','route','user','couponsuser','stime',])//加入了addrschool2
                    ->where($where)
                    ->order($sort, $order)
                    ->count();
            
            $list = $this->model
                    ->with(['addrschool','addrschool2','route','user','couponsuser','stime'])//加入了addrschool2
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            
                
            
            foreach ($list as $row) {
                $row->visible(['id','uid','createtime','tel','status','order','updatetime','e_id','s_id','routeid','paytime','pay_status','tk_order','price','money','cou_id','stime_id','stime_date','tk_money','tk_time','scan_time','scan_status','num']);
                $row->visible(['addrschool']);
                $row->getRelation('addrschool')->visible(['addrname']);
                $row->visible(['addrschool2']);//加入的
				$row->getRelation('addrschool')->visible(['addrname']);//加入的  QQ496631085
				$row->visible(['route']);
				$row->getRelation('route')->visible(['name','type_status']);
				$row->visible(['user']);
				$row->getRelation('user')->visible(['nickname','wx_openid','headurl','vip']);
				$row->visible(['couponsuser']);
				$row->getRelation('couponsuser')->visible(['couponsid']);
				$row->visible(['stime']);
				$row->getRelation('stime')->visible(['time']);
            }


            



            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }



      /**
     * 批量更新
     */
    public function multi($ids = "")
    {
        $ids = $ids ? $ids : $this->request->param("ids");
        if ($ids) {

            $Refund = new Refund;
                    
            $data = $Refund->refund_admin($ids);
            $this->success($data,5);
            halt($data);


            if ($this->request->has('params')) {
                parse_str($this->request->post("params"), $values);
                $values = $this->auth->isSuperAdmin() ? $values : array_intersect_key($values, array_flip(is_array($this->multiFields) ? $this->multiFields : explode(',', $this->multiFields)));
                if ($values) {
                    $adminIds = $this->getDataLimitAdminIds();
                    if (is_array($adminIds)) {
                        $this->model->where($this->dataLimitField, 'in', $adminIds);
                    }
                    
                    $count = 0;
                    Db::startTrans();
                    try {
                        $list = $this->model->where($this->model->getPk(), 'in', $ids)->select();
                        foreach ($list as $index => $item) {
                            $count += $item->allowField(true)->isUpdate(true)->save($values);
                        }
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
                        $this->error(__('No rows were updated'));
                    }
                } else {
                    $this->error(__('You have no permission'));
                }
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }
}
