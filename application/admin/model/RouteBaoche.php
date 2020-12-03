<?php

namespace app\admin\model;

use think\Model;


class RouteBaoche extends Model
{

    

    

    // 表名
    protected $name = 'route_baoche';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'stime_text',
        'status_text',
        'return_status_text'
    ];
    

    
    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2'), '3' => __('Status 3')];
    }

    public function getReturnStatusList()
    {
        return ['1' => __('Return_status 1'), '2' => __('Return_status 2')];
    }


    public function getStimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['stime']) ? $data['stime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getReturnStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['return_status']) ? $data['return_status'] : '');
        $list = $this->getReturnStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setStimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
