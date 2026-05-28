<?php

/**
 * Vérifie si la session courante doit être révoquée (utilisateur bloqué).
 * À appeler après session_start() sur chaque page protégée.
 */
function verifier_session_revoquee() {
    if (!isset($_SESSION['user'])) return;
    $revoked_file = __DIR__ . '/../data/sessions_revoquees.json';
    if (!file_exists($revoked_file)) return;
    $revoked = json_decode(file_get_contents($revoked_file), true) ?? [];
    if (in_array($_SESSION['user']['id'], $revoked)) {
        session_destroy();
        header('Location: ' . (strpos($_SERVER['PHP_SELF'], '/vues/') !== false ? '../' : '') . 'index.php?bloque=1');
        exit();
    }
}

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
    $users = lire_users();
    foreach ($users as &$user) {
        if ($user['email'] !== $email) continue;

        $hash = $user['mot_de_passe'];
        // Migration : si l'ancien MDP est en clair, on vérifie en clair puis on hash
        if (str_starts_with($hash, '$2y$')) {
            $ok = password_verify($mot_de_passe, $hash);
        } else {
            $ok = ($hash === $mot_de_passe);
            if ($ok) {
                $user['mot_de_passe'] = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            }
        }

        if (!$ok) continue;

        if ($user['bloque'] ?? false) {
            return 'bloque';
        }
        $user['date_derniere_connexion'] = date('Y-m-d');
        ecrire_users($users);
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
        "mot_de_passe" => password_hash($mot_de_passe, PASSWORD_DEFAULT),
        "telephone" => $telephone,
        "adresse" => "",
        "code_interphone" => "",
        "role" => "client",
        "statut" => "Standard",
        "bloque" => false,
        "taux_remise" => 0,
        "points_fidelite" => 0,
        "date_inscription" => date("Y-m-d"),
        "date_derniere_connexion" => date("Y-m-d")
    ];
    
    $users[] = $nouveau_user;
    ecrire_users($users);
    return $nouveau_user;
}
?>
