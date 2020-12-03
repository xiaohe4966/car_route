 define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'routeschool2/index' + location.search,
                    add_url: 'routeschool2/add',
                    edit_url: 'routeschool2/edit',
                    del_url: 'routeschool2/del',
                    multi_url: 'routeschool2/multi',
                    table: 'route_school',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        // {field: 'saddr', title: __('Saddr')},
                        // {field: 'eaddr', title: __('Eaddr')},
                        
                        // {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3')}, formatter: Table.api.formatter.status,operate:false},
                        {field: 'order', title: __('Order'),operate:false},
                       
                        {field: 'paytime', title: __('Paytime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'pay_status', title: __('Pay_status'), searchList: {"0":__('Pay_status 0'),"1":__('Pay_status 1'),"2":__('Pay_status 2'),"3":__('Pay_status 3'),"4":__('Pay_status 4')}, formatter: Table.api.formatter.status},
                        {field: 'num', title: __('Num'),operate:false},

                        {field: 'price', title: __('Price'), operate:'BETWEEN',operate:false},
                        {field: 'money', title: __('Money'), operate:'BETWEEN',operate:false},
                        {field: 'stime_date', title: __('Stime_date'),operate:false},
                        
                       
                        {field: 'scan_time', title: __('Scan_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'scan_status', title: __('Scan_status'), searchList: {"0":__('Scan_status 0'),"1":__('Scan_status 1')}, formatter: Table.api.formatter.status,operate:false},
                        {field: 'user.nickname', title: '微信'+__('User.nickname')},
                        {field: 'user.headurl', title: __('User.headurl'),events: Table.api.events.image, formatter: Table.api.formatter.image,operate:false},// formatter: Table.api.formatter.url
                        // {field: 'user.vip', title: __('User.vip'),operate:false},
                        {field: 'route.name', title: __('Route.name')},
                        {field: 'route.alltime', title: __('Route.alltime'), operate:'RANGE',operate:false},//, addclass:'datetimerange', formatter: Table.api.formatter.datetime
                        {field: 'addrschool.addrname', title: '上车点'+__('Addrschool.addrname')},
                        {field: 'e_addr.addrname', title: '下车点' ,operate:false},
                        {field: 'couponsuser.couponsid', title: __('Couponsuser.couponsid'),operate:false},
                        {field: 'stime.time', title: __('Stime.time'), operate:'RANGE',operate:false},//, addclass:'datetimerange', formatter: Table.api.formatter.datetime
                        
                        {field: 'ps', title: __('Ps'),operate:false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},

                        {field: 'tk_time', title: __('Tk_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'tk_money', title: __('Tk_money'), operate:'BETWEEN',operate:false},
                        {field: 'tk_order', title: __('Tk_order')},

                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});