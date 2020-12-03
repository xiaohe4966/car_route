define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'number/index' + location.search,
                    add_url: 'number/add',
                    edit_url: 'number/edit',
                    del_url: 'number/del',
                    multi_url: 'number/multi',
                    table: 'number',
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
                        {field: 'stime_date', title: __('Stime_date')},
                        // {field: 'stime_id', title: __('Stime_id')},
                        // {field: 'route_id', title: __('Route_id')},
                        {field: 'number', title: __('Number')},
                        {field: 'ren_num', title: __('Ren_num')},
                     
                        {field: 'route.name', title: __('Route.name')},
                        {field: 'route.saddr', title: __('Route.saddr')},
                        {field: 'route.eaddr', title: __('Route.eaddr')},
                        {field: 'stime.time', title: __('Stime.time'), operate:'RANGE'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                        
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            $('#c-route_id').change(function () {

                // var routeid = $(this).val();
                var routeid = $('#c-route_id').val();
                var url = '/api/school/get_route_ren_num?routeid='+routeid;
                Fast.api.ajax({
                    url:url
                }, function(res){
                    //成功的回调
                    $('#c-ren_num').val(res);
                }, function(){
                    //失败的回调
                });
                
                });
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