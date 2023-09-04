// Uses jQuery

$(document).ready(function(){

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    $("#edit_comic_name").on('change', function(){
        let comicName = $(this).val();
        let comicSlug = comicName.toLowerCase().replace(/[^a-z0-9]/gi, '');
        $("#edit_comic_slug").val(comicSlug);
        // Do the check for unique name
        checkSlug(comicSlug);
    });

    $("#edit_comic_slug").on('keyup', function(){
        let slug = $(this).val();
        if (slug.length > 0) {
            checkSlug($(this).val());
        }
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


    $(".comic-delete").on('click', function(event){
        event.preventDefault();
        let slug = $(this).data('comic');
        let name = $(this).data('comicname');
        $("#delete-comic-modal-title").html("Delete \"" + name + "\"");
        $("#delete-comic-modal-submit").prop('href', "/comic/" + slug + "/delete")
        $("#delete-comic-modal-input").off("keyup").on("keyup", function(event){
            event.preventDefault();
            if ($(this).val().toLowerCase() === 'delete') {
                $("#delete-comic-modal-submit").removeClass('disabled');
            } else {
                $("#delete-comic-modal-submit").addClass('disabled');

            }
        })

        $("#delete-comic-modal").modal("show");
    });


    $(".theme-delete").on('click', function(event){
        event.preventDefault();
        let comic = $(this).data('comic');
        let slug = $(this).data('slug');
        let name = $(this).data('theme');

        $("#delete-theme-modal-title").html("Delete Theme: \"" + name + "\"");
        $("#delete-theme-modal-submit").prop('href', "/themes/" + comic + "/delete/" + slug)
        $("#delete-theme-modal-input").off("keyup").on("keyup", function(event){
            event.preventDefault();
            if ($(this).val().toLowerCase() === 'delete') {
                $("#delete-theme-modal-submit").removeClass('disabled');
            } else {
                $("#delete-theme-modal-submit").addClass('disabled');

            }
        })

        $("#delete-theme-modal").modal("show");
    });

    $("#sidebar-collapse-button").on('click', function(event){
        event.preventDefault();
        $("#sidebar-container").toggleClass('collapsed').toggleClass('col-3').toggleClass('col-1');
        let isCollapsed = $("#sidebar-container").hasClass('collapsed');
        let date = new Date();
        date.setFullYear(date.getFullYear()+1);

        if (isCollapsed) {
            document.cookie="clickthuluSidebarCollapsed=1;path=/;samesite=strict;expires=" + date.getUTCDate();
        } else {
            document.cookie="clickthuluSidebarCollapsed=0;path=/;samesite=strict;expires=" + date.getUTCDate();
        }
    })

    setTimeout(function(){
        $(".pop").each(function(){
            closePop($(this));
        })
    }, 10000)

    $(".pop-close").on('click', function(event){
        event.preventDefault();
        let target = $(this).parents(".pop").first();
        closePop(target);
    });

    function closePop(target)
    {
        console.log("Close pop triggered")
        if(target){
            target.fadeOut(1000, function(){ console.log("Closing pop"); target.remove(); });
        }
    }

});