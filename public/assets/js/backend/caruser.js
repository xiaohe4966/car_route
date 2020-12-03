define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'caruser/index' + location.search,
                    add_url: 'caruser/add',
                    edit_url: 'caruser/edit',
                    del_url: 'caruser/del',
                    multi_url: 'caruser/multi',
                    table: 'user',
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
                        {field: 'nickname', title: __('Nickname')},
                        {field: 'mobile', title: __('Mobile')},
                        {field: 'openid', title: __('Openid')},
                        {field: 'headurl', title: __('Headurl'), events: Table.api.events.image, formatter: Table.api.formatter.image},//formatter: Table.api.formatter.url
                        {field: 'vip_stime', title: __('Vip_stime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'vip_etime', title: __('Vip_etime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'vip_month', title: __('Vip_month')},
                        {field: 'scan_switch', title: __('Scan_switch'), searchList: {"0":__('Scan_switch 0'),"1":__('Scan_switch 1')}, table: table, formatter: Table.api.formatter.toggle},
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