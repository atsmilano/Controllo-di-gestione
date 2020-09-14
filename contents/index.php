<?php
$url_params = "";
foreach ($_GET as $key=>$value) {
    $url_params .= $key."=".$value."&";
}
ffRedirect(FF_SITE_PATH . "/area_riservata?".$url_params);