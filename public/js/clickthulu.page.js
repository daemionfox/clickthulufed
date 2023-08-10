
    Dropzone.options.pageUploadDropzoneForm = {
        paramName: "file",
        addedfile: function(file, response){
            $("#page_upload_spinner").removeClass('d-none');
        },
        success: function (file, response) {
            $("#page_upload_image").removeClass('d-none').attr('src', '/image/' + response.comic + "/" + response.file).attr('alt', response.file);
            $("#add_page_image").val(response.file);
            $("#add_page_ocr_button").attr('disabled', false);
            $("#page_upload_spinner").addClass('d-none');
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