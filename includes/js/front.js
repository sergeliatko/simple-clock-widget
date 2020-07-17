jQuery(document).ready(function ($) {
    $('.clock-widget').each(function () {
        $(this).clock({
            timestamp: clockWidget.timestamp,
            dateFormat: $(this).data('date-format'),
            timeFormat: $(this).data('time-format'),
            calendar: !(1 === parseInt($(this).data('hide-date'))),
            langSet: clockWidget.language,
            rate: 50
        });
    });
});