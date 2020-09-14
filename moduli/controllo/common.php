<?php
$user = LoggedUser::Instance();

//gruppo controllo
$menu["controllo"] = array(
    "key"     => "controllo",
    "label"   => "Controllo",
    "icon"	  => "",
    "path"	  => "",
    "redir"	  => "",
    "actions" => array(),
    "acl"	  => "1,2,3",
    "hide"    => 0,
);

mod_restricted_add_menu_child($menu["controllo"]);