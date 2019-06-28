$(function() {
    $(document.links).filter(function() {
        return this.hostname !== window.location.hostname;
    }).attr('target', '_blank');

    $('.corporation-bulletin-edit-button').click(function() {
        $('#bulletin-edit-editor').show();
        $('.bulletin-content').hide();
        $('.corporation-bulletin-edit-button').hide();
        $('.corporation-bulletin-cancel-button').show();

        var simplemde = new SimpleMDE({
            spellChecker: false,
            element: $('#bulletin-edit')[0]
        });

        simplemde.codemirror.on('change', function() {
            $('.bulletin-edit-save').show();
            $('textarea#bulletin-edit').val(simplemde.value());
        });
    });

    $('.bulletin-edit-save').click(function() {
        $.post($('#bulletin-save-button').attr('data-target'), { bulletin: $('textarea#bulletin-edit').val() })
            .done(function() {
                document.location.reload();
            });
    });

    $( ".corporation-bulletin-cancel-button" ).click(function() {
        document.location.reload();
    });
});