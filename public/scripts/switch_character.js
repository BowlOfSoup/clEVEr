$(function() {
    $('.character-switch').click(function(e) {
        $.post($(this).attr('data-target')).done(function() {
            document.location.reload();
        });

        e.stopPropagation();
    });
});