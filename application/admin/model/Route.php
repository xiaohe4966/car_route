<?php

namespace app\admin\model;

use think\Model;


class Route extends Model
{

    

    

    // 表名
    protected $name = 'route';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'stime_text',
        'type_status_text'
    ];
    

    
    public function getTypeStatusList()
    {
        return ['1' => __('Type_status 1'), '2' => __('Type_status 2')];
    }


    public function getStimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['stime']) ? $data['stime'] : '');
        $d = is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
     
         return $d;
    }


    public function getTypeStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type_status']) ? $data['type_status'] : '');
        $list = $this->getTypeStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setStimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
