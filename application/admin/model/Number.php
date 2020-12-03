<?php

namespace app\admin\model;

use think\Model;


class Number extends Model
{

    

    

    // 表名
    protected $name = 'number';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







    public function route()
    {
        return $this->belongsTo('Route', 'route_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function stime()
    {
        return $this->belongsTo('Stime', 'stime_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
