/*	based on jQuery Slug Generation Plugin by Perry Trinier (perrytrinier@gmail.com)
  Licensed under the GPL: http://www.gnu.org/copyleft/gpl.html*/

ff.ffField.slug = function(elem, title_field, pre, post) {
	var slugcontent = jQuery(elem).val().toLowerCase(); /* gli slug sono tutti minuscoli, converto a priori*/
	var pre = pre || "";
	var post = post || "";

	slugcontent = ff.slug(slugcontent);
	if(title_field) {
		if(jQuery.fn.escapeGet(title_field).is("INPUT,SELECT"))
			jQuery.fn.escapeGet(title_field).val(pre + slugcontent + post);
		else
			jQuery.fn.escapeGet(title_field).text(pre + slugcontent + post);
	}
	
	return pre + slugcontent + post;
};
