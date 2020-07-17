// noinspection JSUnresolvedFunction
jQuery(document).ready(function ($) {
    // noinspection JSUnresolvedFunction,JSUnresolvedVariable
    $.post(
        clockWidget.url,
        {
            action: clockWidget.action
        },
        function (response) {
            if (true === response.success) {
                let timestamp = parseInt(response.data);
                // noinspection JSUnresolvedFunction
                $('.clock-widget').each(function () {
                    // noinspection JSUnresolvedVariable
                    $(this).clock({
                        timestamp: timestamp,
                        dateFormat: $(this).data('date-format'),
                        timeFormat: $(this).data('time-format'),
                        calendar: !(1 === parseInt($(this).data('hide-date'))),
                        langSet: clockWidget.language,
                        rate: 250
                    });
                });
            } else {
                console.log(response);
            }
        }
    ).fail(function (response) {
        console.log(response);
    });
});
