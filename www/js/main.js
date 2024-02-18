
const visibilityTime = 2000;
const fadeOutTime = 2500;

$(document).ready(function() {
    setTimeout(function() {
        $('.flash').fadeOut(fadeOutTime);
    }, visibilityTime);

    setTimeout(function() {
        $('.flash-container').remove();
    }, visibilityTime + fadeOutTime);

    $.each($('.flash .btn-close'), function(i,e) {
        $(e).click(function() {
            $(e).parent().remove();
        });
    });
});

