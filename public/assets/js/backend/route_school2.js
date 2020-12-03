define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'route_school2/index' + location.search,
                    add_url: 'route_school2/add',
                    edit_url: 'route_school2/edit',
                    del_url: 'route_school2/del',
                    multi_url: 'route_school2/multi',
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
                        {field: 'saddr', title: __('Saddr')},
                        {field: 'eaddr', title: __('Eaddr')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'tel', title: __('Tel')},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3')}, formatter: Table.api.formatter.status},
                        {field: 'order', title: __('Order')},
                        {field: 'ps', title: __('Ps')},
                        {field: 'paytime', title: __('Paytime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'pay_status', title: __('Pay_status'), searchList: {"0":__('Pay_status 0'),"1":__('Pay_status 1'),"2":__('Pay_status 2'),"3":__('Pay_status 3'),"4":__('Pay_status 4')}, formatter: Table.api.formatter.status},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        {field: 'stime_date', title: __('Stime_date')},
                        {field: 'tk_money', title: __('Tk_money'), operate:'BETWEEN'},
                        {field: 'tk_time', title: __('Tk_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'scan_time', title: __('Scan_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'scan_status', title: __('Scan_status'), searchList: {"0":__('Scan_status 0'),"1":__('Scan_status 1')}, formatter: Table.api.formatter.status},
                        {field: 'user.nickname', title: __('User.nickname')},
                        {field: 'user.headurl', title: __('User.headurl'), formatter: Table.api.formatter.url},
                        {field: 'user.vip', title: __('User.vip')},
                        {field: 'route.name', title: __('Route.name')},
                        {field: 'addrschool.addrname', title: __('Addrschool.addrname')},
                        {field: 'couponsuser.couponsid', title: __('Couponsuser.couponsid')},
                        {field: 'stime.time', title: __('Stime.time')},//, operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime
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