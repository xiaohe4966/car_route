define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'route2/index' + location.search,
                    add_url: 'route2/add',
                    edit_url: 'route2/edit',
                    del_url: 'route2/del',
                    multi_url: 'route2/multi',
                    table: 'route',
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
                        {field: 'name', title: __('Name')},
                        {field: 'eaddr', title: __('Eaddr')},
                        {field: 'stime_ids', title: __('Stime_ids')},
                        {field: 'type_status', title: __('Type_status'), searchList: {"1":__('Type_status 1'),"2":__('Type_status 2')}, formatter: Table.api.formatter.status},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'alltime', title: __('Alltime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status_switch', title: __('Status_switch'), searchList: {"0":__('Status_switch 0'),"1":__('Status_switch 1')}, table: table, formatter: Table.api.formatter.toggle},
                        {field: 'addrschool.addrname', title: __('Addrschool.addrname')},
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