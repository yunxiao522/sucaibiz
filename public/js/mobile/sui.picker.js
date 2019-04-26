$j.scpicker = function (element, data, success,defaultValue) {
    if(defaultValue != undefined){
        init(defaultValue);
    }
    var element_name = element.substr(1);
    //绑定性别点击事件
    $(element).picker({
        toolbarTemplate: '<header class="bar bar-nav picker-header">\
                  <button class="button button-link pull-left close-picker">取消</button>\
                  <button class="button button-link pull-right close-picker ' + element_name + '-confirm">确定</button>\
                  <h1 class="title">选择性别</h1>\
                  </header>',
        cols: [
            {
                textAlign: 'center',
                values: data
            }
        ],
        rotateEffect: true,
    });
    $(document).on('click', '.'+ element_name +'-confirm', function () {
        var value = oneColGetPickerSelectedValue();
        success(value);
    });

    //获取单例选择值
    function oneColGetPickerSelectedValue() {
        var picker_items = $('.picker-items-col-wrapper').children('.picker-item');
        var value = '';
        $j.each(picker_items, function () {
            if ($(this).hasClass('picker-selected')) {
                value = this.dataset.pickerValue;
                return false;
            }
        });
        return value;
    }
    //初始化值
    function  init(defaultValue) {
        var picker_items = $('.picker-items-col-wrapper').children('.picker-item');
        $j.each(picker_items, function () {
            $(this).removeClass('picker-selected');
            var value = this.dataset.pickerValue;
            if(value == defaultValue){
                $(this).addClass('picker-selected');
            }
        });
    }
};
