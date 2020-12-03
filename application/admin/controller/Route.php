<?php

namespace app\admin\controller;

use app\common\controller\Backend;

use think\Db;
/**
 * 校园路线管理
 *
 * @icon fa fa-circle-o
 */
class Route extends Backend
{
    
    /**
     * Route模型对象
     * @var \app\admin\model\Route
     */
    protected $model = null;
    protected $multiFields = 'status_switch';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Route;
        $this->view->assign("typeStatusList", $this->model->getTypeStatusList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */




    /**
     * 排除前台提交过来的字段
     * @param $params
     * @return array
     */
    protected function preExcludeFields($params)
    {
        if (is_array($this->excludeFields)) {
            foreach ($this->excludeFields as $field) {
                if (key_exists($field, $params)) {
                    unset($params[$field]);
                }
            }
        } else {
            if (key_exists($this->excludeFields, $params)) {
                unset($params[$this->excludeFields]);
            }
        }
        return $params;
    }


    public function add_route($id = null)
    {   
        $params = $this->request->param();
        $route = Db::name('route')->find($id);

        if(!empty($params['s_id'])){
            $data['routeid'] = $id;            
            $data['s_id'] = $params['s_id'];
            $res_addr = Db::name('addr_school')->where('id',$data['s_id'])->find();
            if(!$res_addr){
                $this->error('请选择正确的路线');
            }
            $res = Db::name('route_price')->where($data)->find();
            if($res){
                $this->error('此路线已有此上车点！');
            }

            


            //如果是统一路线就获取 路线的统一价格
            if($route['type_status']=='1'){
                $data['price'] = $route['price'];
            }else{
                $data['price'] = $params['price'];
            }
            
            //2020-11-25 13:56:04  增加判断如果第一增加这个路线取weigh的最大值 》取整*100
            //否则就按这条路线的最大weigh+1
            $route_max = Db::name('route_price')->where('routeid',$id)->max('weigh');
            if($route_max){
                $data['weigh'] = $route_max+1;
            }else{//第一次
                $max = Db::name('route_price')->max('weigh');
                $weigh = intval((($max/100)+1)*100)+2;
                $data['weigh'] = $weigh;

                //2020-12-01 14:09:53 增加第一个必须是起点站
                if($data['s_id']!=$route['s_id']){
                    $this->error('第一个请选择添加起点站！');
                }
               
                //2020-12-01 14:09:58
            }
            //2020-11-25 14:03:56

            $res = Db::name('route_price')->insert($data);
            if($res){
                $this->success('添加成功');
            }
        }
        

        $addr = Db::name('addr_school')->select();
        $this->view->assign("addr", $addr);

        $this->view->assign("id", $id);
        $this->view->assign("route", $route);
        return $this->view->fetch();
        // Db::name('route_price')
    }

     
    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }


                $params['saddr'] = Db::name('addr_school')->where('id',$params['s_id'])->value('addrname');//起点
                $params['eaddr'] = Db::name('addr_school')->where('id',$params['e_id'])->value('addrname');//终点

                if($params['type_status']=='1'){
                    if($params['price']<0.01){
                        $this->error('统一价格选中后 必须要填车票价格');
                    }
                }


                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    
                    $this->success('okokok',null,$this->model->id);
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $addr = Db::name('addr_school')->select();
        $this->view->assign("addr", $addr);
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {   
       
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                // halt($params);
                
                $params['saddr'] = Db::name('addr_school')->where('id',$params['s_id'])->value('addrname');//起点
                $params['eaddr'] = Db::name('addr_school')->where('id',$params['e_id'])->value('addrname');//终点
                // $params['stime'] = strtotime(date('Y-m-d ').$params['stime'].':00');
                if($params['type_status']=='1'){
                    Db::name('route_price')->where('routeid',$ids)->update(['price'=>$params['price']]);
                }
                


                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        // halt($row);
        $saddr = Db::name('route_price')->where('routeid',$ids)->select();
        $addr = Db::name('addr_school')->column('*','id');
        $this->view->assign("saddr", $saddr);

        $this->view->assign("addr", $addr);
        // halt($row);
        $this->view->assign("row", $row);
        return $this->view->fetch('edit2');
    }


    public function del_route($id)
    {
       $res = Db::name('route_price')->where('id',$id)->delete();
       if($res){
           $this->success('ok');
       }else{
           $this->error('del_error');
       }
    }



      /**
     * 编辑
     */
    public function edit_route($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        // halt($row);
        $saddr = Db::name('route_price')->where('routeid',$ids)->select();
        $addr = Db::name('addr_school')->column('*','id');
        $this->view->assign("saddr", $saddr);

        $this->view->assign("addr", $addr);
        // halt($row);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }



    
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
                    $routeid = $val['id'];

                    $order = Db::name('route_school')->where('routeid',$val['id'])->find();
                    if($order){
                        $this->error('路线ID:【'.$val['id'].'】，有订单ID:【'.$order['id'].'】，订单号为:'.$order['order'].'不能删除！',5);
                    }

                    // halt($val);  //2020-11-23 09:35:26 
                    $count += $val->delete();
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
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }


}
