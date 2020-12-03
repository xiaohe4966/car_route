<?php

namespace app\admin\controller;

use app\common\controller\Backend;

use think\Db;
/**
 * 校园专线
 *
 * @icon fa fa-circle-o
 */
class Routeschool2 extends Backend
{
    
    /**
     * Routeschool2模型对象
     * @var \app\admin\model\Routeschool2
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Routeschool2;
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
                    ->with(['user','route','addrschool','couponsuser','stime'])
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->with(['user','route','addrschool','couponsuser','stime'])
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            $addrs = Db::name('addr_school')->column('*','id');
            foreach ($list as $row) {
                $row->visible(['id','saddr','eaddr','createtime','status','order','ps','paytime','pay_status','tk_order','price','money','stime_date','tk_money','tk_time','scan_time','scan_status','num','e_id']);
                $row->visible(['user']);
				$row->getRelation('user')->visible(['nickname','headurl','vip']);
				$row->visible(['route']);
				$row->getRelation('route')->visible(['name','alltime']);
				$row->visible(['addrschool']);
				$row->getRelation('addrschool')->visible(['addrname']);
				$row->visible(['couponsuser']);
				$row->getRelation('couponsuser')->visible(['couponsid']);
				$row->visible(['stime']);
                $row->getRelation('stime')->visible(['time']);
                
                // halt($row);   
            }
            $list = collection($list)->toArray();

            foreach($list as $key=>$row) {
                // halt($row);
                $list[$key]['e_addr'] =  $addrs[$row['e_id']];
            }

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }
}
