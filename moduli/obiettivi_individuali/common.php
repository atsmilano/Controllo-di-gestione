<?php
//definizione parametri modulo
//numero massimo di obiettivi individuali assegnabili ad un dipendente (0 = illimiatto)
define("OBIETTIVI_INDIVIDUALI_N_MAX_ASSEGNABILI_CDR", 1);

$user = LoggedUser::getInstance();

foreach ($user->user_groups as $group) {
    if ($group == 1 || $group == 2 || $group == 3) {
        $user->user_privileges[] = "obiettivi_individuali_admin";
    }
}