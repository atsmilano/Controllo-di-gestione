ff.ffField.imagepicker = function() {
    jQuery(".imagepicker OPTION").each(function() {
        if(jQuery(this).val().length) {
            jQuery(this).attr("data-img-src", jQuery(this).val());
        }
    });
    $(".imagepicker SELECT").imagepicker();
    return false;
};
