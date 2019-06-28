$(function() {
    $('#discordAuthModal').on('show.bs.modal', function (e) {
        $(e.target).find('.modal-body').load($('#discordAuthModal').attr('data-target'))
    });
});
