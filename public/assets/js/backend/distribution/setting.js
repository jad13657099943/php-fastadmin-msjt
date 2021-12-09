define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            Controller.api.bindevent();
            $("#agent input:radio").change(function () {
                var type = $(this).val();
                type == 1 ? $('#consumption').addClass('hidden') : $('#consumption').removeClass('hidden');
            });
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"), function (data, ret) {
                    if (ret.url) {
                        parent.location.reload();
                    }
                });
            }
        }
    };
    return Controller;
});