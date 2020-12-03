<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;

/**
 * 临时包车
 *
 * @icon fa fa-circle-o
 */
class RouteBaoche extends Backend
{
    
    /**
     * RouteBaoche模型对象
     * @var \app\admin\model\RouteBaoche
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\RouteBaoche;
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("returnStatusList", $this->model->getReturnStatusList());
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
        $this->relationSearch = false;
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
                    
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            foreach ($list as $row) {
                $row->visible(['id','saddr','eaddr','name','tel1','stime','createtime','status','return_status','carjson']);
                
            }

            $list = collection($list)->toArray();
            $cars = Db::name('car')->column('*','id');
           
            foreach ($list as $key => $value) {
                $str = '';
                // halt(htmlspecialchars_decode($value['carjson'])); 
                $json = json_decode(htmlspecialchars_decode($value['carjson']),true);
                // halt($json);
                foreach ($json as $carid => $val) {
                    $str = $str .$cars[$val['id']]['name'].'数量:'.$val['num'].',';
                }
                $list[$key]['str'] = $str;
                // 
            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }
}
