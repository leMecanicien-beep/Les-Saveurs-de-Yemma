<?php
function ajouter_log(string $type, string $message, ?int $user_id = null): void {
    $fichier = __DIR__ . '/../data/logs.json';
    $logs = file_exists($fichier) ? (json_decode(file_get_contents($fichier), true) ?? []) : [];
    $logs[] = [
        'date'    => date('Y-m-d H:i:s'),
        'type'    => $type,
        'message' => $message,
        'user_id' => $user_id,
        'ip'      => $_SERVER['REMOTE_ADDR'] ?? 'inconnue',
    ];
    if (count($logs) > 1000) {
        $logs = array_slice($logs, -1000);
    }
    file_put_contents($fichier, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function lire_logs(int $limit = 100): array {
    $fichier = __DIR__ . '/../data/logs.json';
    if (!file_exists($fichier)) return [];
    $logs = json_decode(file_get_contents($fichier), true) ?? [];
    return array_slice(array_reverse($logs), 0, $limit);
}

function compter_tentatives_ip(string $ip, int $minutes = 15): int {
    $fichier = __DIR__ . '/../data/logs.json';
    if (!file_exists($fichier)) return 0;
    $logs  = json_decode(file_get_contents($fichier), true) ?? [];
    $seuil = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));
    $count = 0;
    foreach ($logs as $log) {
        if (
            in_array($log['type'], ['mauvais_mdp', 'compte_bloque_tentative'])
            && ($log['ip'] ?? '') === $ip
            && ($log['date'] ?? '') >= $seuil
        ) {
            $count++;
        }
    }
    return $count;
}
