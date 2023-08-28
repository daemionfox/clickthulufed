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

    $("#theme_duplication_targetname").on('change', function(){
        let themeName = $(this).val();
        let themeSlug = themeName.toLowerCase().replace(/[^a-z0-9]/gi, '');
        $("#theme_duplication_targettheme").val(themeSlug);
        // Do the check for unique name
        checkTheme(themeSlug);
    });

    $("#theme_duplication_targettheme").on('keyup', function(){
        console.log('checking theme slug')
        checkTheme($(this).val());
    })

    function checkTheme(text)
    {
        let comicSlug = $("#theme_duplication_targettheme").data('comic');
        let submitButton = $("#theme_duplication_submit");
        let talert = $("#theme-alert");
        let errors = $("#theme-slug-errors");
        errors.html('');
        talert.removeClass('bg-success').removeClass('bg-danger').removeClass('text-white');
        submitButton.prop('disabled', true);
        if (text === "") {
            return;
        }
        $.get(
            "/themes/" + comicSlug + "/check/" + text
        ).done(function(data){
            submitButton.prop('disabled', false);
            talert.addClass('bg-success').addClass('text-white');
        }).fail(function(data){
            talert.addClass('bg-danger').addClass('text-white');
        });
    }

    function checkSlug(text)
    {
        let calert = $("#comic-slug-alert");
        let icon = $("#comic-slug-alert-icon");
        let errors = $("#comic-slug-errors");
        calert.removeClass('bg-danger').removeClass('bg-success');
        icon.removeClass('text-white').removeClass('fa-at').addClass('fa-spinner').addClass('fa-spin');
        errors.html('');
        $.get(
            "/comic/checkslug/" + text,
            {
            }
        ).done(function(data){
            calert.addClass('bg-success');
            icon.addClass('text-white').removeClass('fa-spinner').removeClass('fa-spin').addClass('fa-at');
        }).fail(function(data){
            errors.html(data.responseText);
            calert.addClass('bg-danger');
            icon.addClass('text-white').removeClass('fa-spinner').removeClass('fa-spin').addClass('fa-at');
        })
    }

});