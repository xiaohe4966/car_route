<?php

namespace app\admin\model;

use think\Model;


class Route_school2 extends Model
{

    

    

    // 表名
    protected $name = 'route_school';
    
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
        'paytime_text',
        'pay_status_text',
        'tk_time_text',
        'scan_time_text',
        'scan_status_text'
    ];
    

    
    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2'), '3' => __('Status 3')];
    }

    public function getPayStatusList()
    {
        return ['0' => __('Pay_status 0'), '1' => __('Pay_status 1'), '2' => __('Pay_status 2'), '3' => __('Pay_status 3'), '4' => __('Pay_status 4')];
    }

    public function getScanStatusList()
    {
        return ['0' => __('Scan_status 0'), '1' => __('Scan_status 1')];
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


    public function getPaytimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['paytime']) ? $data['paytime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getPayStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_status']) ? $data['pay_status'] : '');
        $list = $this->getPayStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getTkTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['tk_time']) ? $data['tk_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getScanTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['scan_time']) ? $data['scan_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getScanStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['scan_status']) ? $data['scan_status'] : '');
        $list = $this->getScanStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setStimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setPaytimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setTkTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setScanTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function user()
    {
        return $this->belongsTo('User', 'uid', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function route()
    {
        return $this->belongsTo('Route', 'routeid', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function addrschool()
    {
        return $this->belongsTo('AddrSchool', 's_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function couponsuser()
    {
        return $this->belongsTo('CouponsUser', 'cou_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function stime()
    {
        return $this->belongsTo('Stime', 'stime_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
