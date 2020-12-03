<?php

namespace app\admin\model;

use think\Model;


class RoutePrice extends Model
{

    

    

    // 表名
    protected $name = 'route_price';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







    public function addrschool()
    {
        return $this->belongsTo('AddrSchool', 's_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function routeschool()
    {
        return $this->belongsTo('RouteSchool', 'routeid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
