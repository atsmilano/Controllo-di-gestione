<?php
$anno_budget = $cm->oPage->globals["anno"]["value"];
$user = LoggedUser::Instance();

$cm->oPage->addContent("<div id='home_contents'>");
$sezioni = CmsHomeSezione::getSezioneAnno($anno_budget);
foreach($sezioni as $sezione) {
    if ($sezione->isAllegato()) {
        $allegati = CmsHomeSezioneAllegato::getAll(["ID_sezione" => $sezione->id]);

        if (!empty($allegati)) {
            $allegati_helper = new AllegatoHelper();

            //START GRANT PERMISSIONS
            $allegati_permissions = $allegati_helper->defineAllegatiPermission($user);
            $allegati_permissions = $allegati_helper->definePermission($allegati, $allegati_permissions, true, false);
            $permission_cookie = $allegati_helper->encodePermissions($allegati_permissions);
            //Call before every output or will not work!!! IMPORTANT
            setcookie('p_2_#', $permission_cookie, time() + 600, '/');
            //END GRANT PERMISSIONS

            foreach ($allegati as $allegato) {
                if ($sezione->testo !== null && !ctype_space($sezione->testo)) {
                    $cm->oPage->addContent($allegati_helper->getDownloadLink($allegato->filename_md5, $sezione->testo));
                    $cm->oPage->addContent("<br/ >");
                }
            }
        }
    }
    else {
        $cm->oPage->addContent(ffCommon_charset_encode($sezione->testo));
    }
}
$cm->oPage->addContent("</div>");