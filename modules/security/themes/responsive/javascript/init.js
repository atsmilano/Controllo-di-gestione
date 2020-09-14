jQuery(function ($, undefined) {
	ff.pluginAddInit("ff.modules.security", function () {
		<!--BeginSect_service_login-->
		ff.modules.security.services.login = '{login_path}';
		<!--EndSect_service_login-->
		<!--BeginSect_service_check_session-->
		ff.modules.security.services.check_session = '{check_session_path}';
		<!--EndSect_service_check_session-->
		<!--BeginSect_service_oauth2-->
		ff.modules.security.services.oauth2 = '{oauth2_path}';
		<!--EndSect_service_oauth2-->		
		ff.modules.security.session.session_name = '{session_name}';
	});
}(jQuery));
