define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
/*            $(document).on('click', '.btn-switcher', function () {
                Layer.confirm('开启二级分销后将不可关闭，请谨慎操作！', {}, function (index, layer) {
                    Layer.close(index);
                }, function (index, layer) {
                    console.log(index);
                });
            })*/
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