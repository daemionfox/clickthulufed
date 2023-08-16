
    Dropzone.options.pageUploadDropzoneForm = {
        paramName: "file",
        addedfile: function(file, response){
            $("#image_upload_spinner").removeClass('d-none');
        },
        success: function (file, response) {
            $("#page_upload_image").removeClass('d-none').attr('src', '/image/' + response.comic + "/" + response.file).attr('alt', response.file);
            $("#add_page_image").val(response.file);
            $("#image_upload_spinner").addClass('d-none');
            this.removeAllFiles(true);
        },
    }

    Dropzone.options.castUploadDropzoneForm = {
        paramName: "file",
        addedfile: function(file, response){
            $("#image_upload_spinner").removeClass('d-none');
        },
        success: function (file, response) {
            $("#cast_upload_image").removeClass('d-none').attr('src', '/castimage/' + response.comic + "/" + response.file).attr('alt', response.file);
            $("#add_cast_image").val(response.file);
            $("#image_upload_spinner").addClass('d-none');
            this.removeAllFiles(true);
        },
    }

    // $("#add_page_ocr_button").on('click', function(event){
    //     event.preventDefault();
    //
    //
    //
    //
    // });