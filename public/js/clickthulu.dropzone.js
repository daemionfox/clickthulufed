Dropzone.options.pageUploadDropzoneForm = {
    paramName: "file",
    success: function(file, response){
        console.log("Got to this point");



        this.removeAllFiles(true);
    },
}