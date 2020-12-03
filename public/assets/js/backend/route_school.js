define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'route_school/index' + location.search,
                    add_url: 'route_school/add',
                    edit_url: 'route_school/edit',
                    // del_url: 'route_school/del',
                    multi_url: 'route_school/multi',
                    table: 'route_school',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',

                // rowStyle:function(row, index){
                //     if (row.num == 1) {
                //         return{
                //             css:{
                //                 color:'blue'
                //             }
                //         }
                //     }else{
                //         return{
                //             css:{
                //                 color:'red'
                //             }
                //         }
                //     }                    
                // },

                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate:false},
                        
                        {field: 'createtime', title: '时间查询', operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,operate:false},
                        


                        // {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3')}, formatter: Table.api.formatter.status},
                        
                        
                        // {field: 's_id', title: __('S_id'),operate:false},
                        {field: 'addrschool.addrname', title: __('Addrschool.addrname'),operate:false},
                        // {field: 'e_id', title: __('E_id'),operate:false},

                        {field: 'routeid', title: __('Routeid'),
                            cellStyle: function (value, row, index) {
                                return {css: {"background-color": "rgb(24,188,156)"}};
                            }    
                        },
                        {field: 'route.name', title: __('Route.name'),operate:false},
                        // {field: 'route.type_status', title: __('Route.type_status'), formatter: Table.api.formatter.status,operate:false},

                        
                        {field: 'pay_status', title: __('Pay_status'),operate:false, searchList: {"0":__('Pay_status 0'),"1":__('Pay_status 1'),"2":__('Pay_status 2'),"3":__('Pay_status 3'),"4":__('Pay_status 4')}, formatter: Table.api.formatter.status},
                        {field: 'num', title: __('Num'),operate:false,
                            cellStyle: function (value, row, index) {

                                if (row.num == 1) {
                                    return{
                                        css:{
                                            color:'blue'
                                        }
                                    }
                                }else{
                                    return{
                                        css:{
                                            color:'red'
                                        }
                                    }
                                }    
                                // return {css: {"color": "red"}};
                            }    
                        },

                        {field: 'paytime', title: __('Paytime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'price', title: __('Price'), operate:'BETWEEN',operate:false},
                        {field: 'money', title: __('Money'), operate:'BETWEEN',operate:false},
                        

                        {field: 'tk_order', title: __('Tk_order'),operate:false},
                        {field: 'tk_money', title: __('Tk_money'), operate:'BETWEEN',operate:false},
                        {field: 'tk_time', title: __('Tk_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,operate:false},

                        {field: 'stime_id', title: __('Stime_id'),
                            cellStyle: function (value, row, index) {
                                return {css: {"background-color": "rgb(24,188,156)"}};
                            } 
                        },
                        {field: 'stime.time', title: __('Stime.time'),operate:false},//, operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime
                        {field: 'stime_date', title: __('Stime_date'),
                            cellStyle: function (value, row, index) {
                                return {css: {"background-color": "rgb(24,188,156)"}};
                            } 
                        },


                        {field: 'scan_time', title: __('Scan_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'scan_status', title: __('Scan_status'), searchList: {"0":__('Scan_status 0'),"1":__('Scan_status 1')}, formatter: Table.api.formatter.status,operate:false},
                        
                        {field: 'order', title: __('Order'),operate:false},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,operate:false},
                        

                        // {field: 'uid', title: __('Uid'),operate:false},
                        {field: 'user.nickname', title: __('User.nickname'),operate:false},
                        {field: 'user.wx_openid', title: __('User.wx_openid'),operate:false},
                        // {field: 'user.headurl', title: __('User.headurl'), formatter: Table.api.formatter.url,operate:false},
                        // {field: 'user.vip', title: __('User.vip'),operate:false},
                        {field: 'tel', title: __('Tel')},

                        {field: 'cou_id', title: __('Cou_id'),operate:false},
                        {field: 'couponsuser.couponsid', title: __('Couponsuser.couponsid'),operate:false},
                        
                        
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });






             //一键退款
             $(document).on("click", ".btn-approve", function () {
                var data = table.bootstrapTable('getSelections');
                var ids = [];
                if (data.length === 0) {
                    Toastr.error("请选择操作信息");
                    return;
                }
                for (var i = 0; i < data.length; i++) {
                    ids[i] = data[i]['id']
                }
                Layer.confirm(
                    '确认选中'+ids.length+'条退款吗?',
                    {icon: 3, title: __('Warning'), offset: '40%', shadeClose: true},
                    function (index) {
                        Layer.close(index);
                        Backend.api.ajax({
                            //url: "route_school/multi?ids=" + JSON.stringify(ids),
                            //方法一：传参方式，后台需要转换变成数组
                            url: "route_school/multi?ids=" + (ids),
                            data: {}
                            //方法二：传参方式，直接是数组传递给后台
                            // url: "route_school/multi",
                            // data: {ids:ids}
                        }, function(data, ret){//成功的回调
                            if (ret.code === 1) {
                                table.bootstrapTable('refresh');
                                Layer.close(index);
                            } else {
                                Layer.close(index);
                                Toastr.error(ret.msg);
                            }
                        }, function(data, ret){//失败的回调
                            console.log(ret);
                            // Toastr.error(ret.msg);
                            Layer.close(index);
                        });
                    }
                );
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