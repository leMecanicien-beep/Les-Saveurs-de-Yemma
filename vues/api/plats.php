<?php

header('Content-Type: application/json; charset=utf-8');

$plats = json_decode(file_get_contents(__DIR__ . '/../../data/plats.json'), true);
if (!$plats) {
    echo json_encode([]);
    exit();
}

$categorie  = trim($_GET['categorie'] ?? 'Tous');
$recherche  = strtolower(trim($_GET['recherche'] ?? ''));
$excl_allerg = !empty($_GET['allergenes'])
    ? array_map('trim', explode(',', strtolower($_GET['allergenes'])))
    : [];

$result = array_values(array_filter($plats, function ($plat) use ($categorie, $recherche, $excl_allerg) {
    // Filtre catégorie
    if ($categorie !== 'Tous' && $plat['categorie'] !== $categorie) {
        return false;
    }
    // Filtre recherche texte
    if ($recherche !== '' && strpos(strtolower($plat['nom']), $recherche) === false &&
        strpos(strtolower($plat['description']), $recherche) === false) {
        return false;
    }
    // Filtre exclusion allergènes
    if (!empty($excl_allerg)) {
        foreach ($plat['allergenes'] as $allerg) {
            if (in_array(strtolower($allerg), $excl_allerg)) {
                return false;
            }
        }
    }
    // Plats disponibles uniquement
    if (!($plat['disponible'] ?? true)) {
        return false;
    }
    return true;
}));

echo json_encode($result, JSON_UNESCAPED_UNICODE);
