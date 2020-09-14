jQuery(document).ready(function() {
    jQuery('a.print').click(function() {
    	jQuery(this).next().printElement( {pageTitle : jQuery(this).attr("title")});
    	return false;
	});
});
