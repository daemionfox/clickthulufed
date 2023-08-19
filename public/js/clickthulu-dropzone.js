
    Dropzone.options.pageUploadDropzoneForm = {
        paramName: "file",
        addedfile: function(file, response){
            $("#image_upload_spinner").removeClass('d-none');
        },
        success: function (file, response) {
            $("#page_upload_image").removeClass('d-none').attr('src', '/pageimage/' + response.comic + "/" + response.file).attr('alt', response.file);
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

    Dropzone.options.headerimageUploadDropzoneForm = {
        paramName: "file",
        addedfile: function(file, response){
            $("#image_upload_spinner").removeClass('d-none');
        },
        success: function (file, response) {
            $("#headerimage_upload_image").removeClass('d-none').attr('src', '/media/' + response.comic + "/" + response.file).attr('alt', response.file);
            $("#layout_headerimage").val(response.file);
            $("#image_upload_spinner").addClass('d-none');
            this.removeAllFiles(true);
        },
    }
