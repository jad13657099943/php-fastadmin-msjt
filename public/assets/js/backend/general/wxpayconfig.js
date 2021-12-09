define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {

            $("form.edit-form").data("validator-options", {
                display: function (elem) {
                    return $(elem).closest('tr').find("td:first").text();
                }
            });
            Form.api.bindevent("form");
            $(document).on('click','.spec_add_btn', function (event) {
                var that=$(this);
                var url = $(this).attr('data-url');
                if(!url) return false;
                var msg = $(this).attr('data-title');
                var width = $(this).attr('data-width');
                var height = $(this).attr('data-height');
                var area = [$(window).width() > 800 ? (width?width:'800px') : '95%', $(window).height() > 600 ? (height?height:'600px') : '95%'];
                var options = {
                    shadeClose: false,
                    shade: [0.3, '#393D49'],
                    area: area,
                    callback:function(data){
                        that.parent().find('.week').html(data.url);
                        that.parent().find('.weekc').val(data.url);

                    }
                };

                Fast.api.open(url,msg,options);
            });
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