define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'route_price/index' + location.search,
                    add_url: 'route_price/add',
                    edit_url: 'route_price/edit',
                    del_url: 'route_price/del',
                    multi_url: 'route_price/multi',
                    table: 'route_price',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                sortOrder:'ASC',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'routeid', title: __('Routeid')},
                        {field: 's_id', title: __('S_id')},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        
                        {field: 'addrschool.addrname', title: __('Addrschool.addrname')},
                        {field: 'routeschool.saddr', title: __('Routeschool.saddr')},
                        {field: 'routeschool.eaddr', title: __('Routeschool.eaddr')},
                        
                        {field: 'weigh', title: __('Weigh')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ],
                       
                       

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