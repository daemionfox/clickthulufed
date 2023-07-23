// Uses jQuery

$(document).ready(function(){

    $("#create_comic_name").on('change', function(){
        let comicName = $(this).val();
        let comicSlug = comicName.toLowerCase().replace(/[^a-z0-9]/gi, '');
        $("#create_comic_slug").val(comicSlug);
        // Do the check for unique name
        checkSlug(comicSlug);
    });

    $("#create_comic_slug").on('keyup', function(){
        checkSlug($(this).val());
    })

    function checkSlug(text)
    {
        let alert = $("#comic-slug-alert");
        let icon = $("#comic-slug-alert-icon");
        let errors = $("#comic-slug-errors");
        alert.removeClass('bg-danger').removeClass('bg-success');
        icon.removeClass('text-white').removeClass('fa-at').addClass('fa-spinner').addClass('fa-spin');
        errors.html('');
        $.get(
            "/create/checkslug/" + text,
            {
            }
        ).done(function(data){
            alert.addClass('bg-success');
            icon.addClass('text-white').removeClass('fa-spinner').removeClass('fa-spin').addClass('fa-at');
        }).fail(function(data){
            errors.html(data.responseText);
            alert.addClass('bg-danger');
            icon.addClass('text-white').removeClass('fa-spinner').removeClass('fa-spin').addClass('fa-at');
        })
    }

});