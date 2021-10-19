<?php
$user = LoggedUser::getInstance();

//gruppo programmazione strategica
$menu["programmazione"] = array(
    "key"           => "programmazione"
    , "label"       => "Programmazione"
    , "icon"		=> ""
    , "path"		=> ""
    , "redir"		=> ""
    , "actions"     => array(

                            )
    , "acl"			=> "1,2,3"
    , "hide"        => 0
									);
mod_restricted_add_menu_child($menu["programmazione"]);