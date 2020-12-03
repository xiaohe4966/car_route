define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'route/index' + location.search,
                    add_url: 'route/add',
                    edit_url: 'route/edit',
                    del_url: 'route/del',
                    multi_url: 'route/multi',
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
                        {field: 'saddr', title: __('Saddr')},
                        {field: 'eaddr', title: __('Eaddr')},
                        {field: 'ren_num', title: __('Ren_num')},
                        {field: 'status_switch', title: __('Status_switch'), searchList: {"0":__('Status_switch 0'),"1":__('Status_switch 1')}, table: table, formatter: Table.api.formatter.toggle},
                        {field: 'stime', title: __('Stime') ,addclass:'datetimerange', formatter: Table.api.formatter.datetime,datetimeFormat:"HH-MM"},
                        {field: 'type_status', title: __('Type_status'), searchList: {"1":__('Type_status 1'),"2":__('Type_status 2')}, formatter: Table.api.formatter.status},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'ps', title: __('Ps')},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                                buttons:[  {
                                                name: 'detail',
                                                title: '编辑途径路线',
                                                classname: 'btn btn-xs btn-primary btn-dialog',
                                                icon: 'fa fa-list',
                                                url: 'route/edit_route',
                                                callback: function (data) {
                                                    Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                                }
                                            },
                                    ],
                        
                        formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {


            var v2 = $("select,input[type='radio']").val();
            if(v2==1){
                $('#t-price').show();
            }else{
                $('#t-price').hide();
            }

            $(document).on("change", "select,input[type='radio']", function () {
                
                console.log('我被点击改变了');
                var v = $(this).val();
                if(v==1){
                    $('#t-price').show();
                }else{
                    $('#t-price').hide();
                }
            });


            // Controller.api.bindevent($("form[role=form]"), function () {
            //     // $(this).find('button[type=reset]').trigger('click'); //提交完后重置表单

            //     alert('2222');
            //     // var id = $('#add_addr').attr('data');
            //     var id = 13;
            //     // // 
            //     // Fast.common.openNewWindow('/route_price/add','_blank',400);
            //     Fast.api.open("route/add_route?id="+id,'添加途径点',{area:['460px', '300px']},{callback:function(value){
            //         // 在这里可以接收弹出层中使用`Fast.api.close(data)`进行回传数据
            //         window.location.reload();
            //         console.log('hhhhhhhhhh');
            //         alert(1);
            //     }});

            // });



            Form.api.bindevent($("form[role=form]"), function(data, ret){
                //如果我们需要在提交表单成功后做跳转，可以在此使用location.href="链接";进行跳转
                // location.href='route/add_route?id=12';
                console.log(data);
                var id = data;
                Fast.api.open("route/edit_route?ids="+id,'编辑途径路线',{area:['95%', '95%']},{callback:function(value){
                    // 在这里可以接收弹出层中使用`Fast.api.close(data)`进行回传数据
                    // window.location.reload();
                    // console.log('data');

                    var index = parent.Layer.getFrameIndex('添加');//关闭原来的还需要改
                    parent.Layer.close(index);
                    window.parent.location.reload();
                    
                }});

                // Toastr.success("成功成功成功成功成功成功");
                
                return false;
            }, function(data, ret){
                  Toastr.success("失败");
            }, function(success, error){
                
                //bindevent的第三个参数为提交前的回调
                //如果我们需要在表单提交前做一些数据处理，则可以在此方法处理
                //注意如果我们需要阻止表单，可以在此使用return false;即可
                //如果我们处理完成需要再次提交表单则可以使用submit提交,如下
                //Form.api.submit(this, success, error);
                return true;
            });
        },
        add_route: function () {
            $(document).on('click', '#add', function () {
                

                


                // table.bootstrapTable('refresh', Table.api.init);
            //     alert('添加地址');
                var price = $('#price').val();
                var id = $('#id').val();
                var addr = $('#addr option:selected').val();
                $.ajax({
                    url:'route/add_route?id='+id,
                    data:{'s_id':addr,'price':price},
                    success:function(e) {
                       
                        console.log(e);
                        if(e.code==0){
                            alert(e.msg);
                        }else{
                             var index = parent.Layer.getFrameIndex('添加途径点');
                            parent.Layer.close(index);
                            window.parent.location.reload();
                        }
                        
                        
                        // alert('chenggong');
                    }
                });
            //     return true;
            });


         

            Controller.api.bindevent();
        },


        edit_route: function () {

         

            $(document).on('click', '#add_addr', function () {
                var id = $('#add_addr').attr('data');
                // alert('2222');
                // Fast.common.openNewWindow('/route_price/add','_blank',400);
                Fast.api.open("route/add_route?id="+id,'添加途径点',{area:['460px', '300px']},{callback:function(value){
                    // 在这里可以接收弹出层中使用`Fast.api.close(data)`进行回传数据
                    window.location.reload();
                    console.log('hhhhhhhhhh');
                    alert(1);
                }});
                
            });




            $(document).on('click', '.del-addr', function () {
                // $("#table").bootstrapTable('refresh');
                var id = $(this).attr('data');
                // alert(id);
                $.ajax({
                    url:'route/del_route?id='+id,
                    // data:{'s_id':'1','price':price},
                    success:function() {
                        var index = parent.Layer.getFrameIndex('添加途径点');
                        parent.Layer.close(index);
                        window.location.reload();
                        // alert('chenggong');
                    }
                });

            });

            Controller.api.bindevent();
        },
        edit: function () {
            
           
            


            var type = $('input[name="row[type_status]"]:checked').val();
            if(type==1){
                $('#t-price').show();
            }else{
                $('#t-price').hide();
            }

            $(document).on("change", "select,input[type='radio']", function () {                
                // console.log('我被点击改变了');
                var v = $(this).val();
                if(v==1){
                    $('#t-price').show();
                }else{
                    $('#t-price').hide();
                }
            });

            // $(document).on("dp.change", "#second-form .datetimepicker", function () {
            //     $(this).parent().prev().find("input").trigger("change");
            // });
            



            // 
            // buttons: [
            //     {
            //         name: 'add_addr',
            //         text: __('弹出窗口打开'),
            //         title: __('弹出窗口打开'),
            //         classname: 'btn btn-xs btn-primary btn-dialog',
            //         icon: 'fa fa-list',
            //         url: 'example/bootstraptable/detail',
            //         callback: function (data) {
            //             Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
            //         },
            //         visible: function (row) {
            //             //返回true时按钮显示,返回false隐藏
            //             return true;
            //         }
            //     }],
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