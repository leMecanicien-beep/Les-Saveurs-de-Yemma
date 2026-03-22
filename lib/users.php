<?php

function lire_users() {
    $fichier = __DIR__ . '/../data/users.json';
    $contenu = file_get_contents($fichier);
    return json_decode($contenu, true);
}

function ecrire_users($users) {
    $fichier = __DIR__ . '/../data/users.json';
    file_put_contents($fichier, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function trouver_user_par_email($email) {
    $users = lire_users();
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            return $user;
        }
    }
    return null;
}

function connecter_user($email, $mot_de_passe) {
    $user = trouver_user_par_email($email);
    if ($user && $user['mot_de_passe'] === $mot_de_passe) {
        return $user;
    }
    return null;
}

function inscrire_user($nom, $prenom, $email, $telephone, $mot_de_passe) {
    $users = lire_users();
    
    // Vérifier si email déjà utilisé
    if (trouver_user_par_email($email)) {
        return false;
    }
    
    // Créer le nouvel utilisateur
    $nouvel_id = max(array_column($users, 'id')) + 1;
    $nouveau_user = [
        "id" => $nouvel_id,
        "nom" => $nom,
        "prenom" => $prenom,
        "email" => $email,
        "mot_de_passe" => $mot_de_passe,
        "telephone" => $telephone,
        "adresse" => "",
        "code_interphone" => "",
        "role" => "client",
        "points_fidelite" => 0,
        "date_inscription" => date("Y-m-d")
    ];
    
    $users[] = $nouveau_user;
    ecrire_users($users);
    return $nouveau_user;
}
?>
