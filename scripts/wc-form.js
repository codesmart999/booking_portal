function statusSubmitButton( ID ) {
    submitButtonSelector = ID + ' .btn-primary:submit'
    if ($(ID).valid()) {
        $(submitButtonSelector).removeAttr('disabled');
    } else {
        $(submitButtonSelector).prop('disabled', true);
    }
}

jQuery(document).ready(function ($) {
    jQuery.validator.methods.date = function (value, element) {
        return this.optional(element) || !/Invalid|NaN/.test(new Date(value)) || /^(\d+)\/(\d+)\/(\d{2,})$/.test(value);
    }
    $('.date').datepicker({
        dateFormat: 'dd/mm/yy',
        onSelect: function () {
            $(this).blur();
        }
    });

    var val = $('#APP_FORM, #PASSWORD_FORM').validate({
        rules: {
            email_addr: {
                required: true,
                email: true
            },
            email_addr1: {
                email: true
            },
            email_addr2: {
                email: true
            },
            password: {
                minlength: 6,
            },
            password_confirm: {
                minlength: 6,
                equalTo: "#password"
            }
        }
    });

    $(':input, :radio, :checkbox').on("change", function () {
        if( $('#APP_FORM').length ) {
            statusSubmitButton('#APP_FORM');
        }
        if( $('#PASSWORD_FORM').length ) {
            statusSubmitButton('#PASSWORD_FORM');
        }
    });

    $('textarea:not(.sml,.exsml,.vsml)').jqEasyCounter({
        'maxChars': 2000,
        'maxCharsWarning': 1900
    });

    $(document).click(function () {
        if( $('#APP_FORM').length ) {
            statusSubmitButton('#APP_FORM');
        }
        if( $('#PASSWORD_FORM').length ) {
            statusSubmitButton('#PASSWORD_FORM');
        }
    });
});
