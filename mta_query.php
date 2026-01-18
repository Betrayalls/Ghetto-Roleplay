<?php
/**
 * Ghetto Roleplay - Canlı Veri Çekme Motoru
 * Fortune Agency Tarafından Optimize Edilmiştir.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // CORS hatalarını önlemek için

// --- SUNUCU BİLGİLERİ ---
$server_ip = "212.38.88.96"; 
$query_port = 22126; // Game Port (22003) + 123
// ------------------------

function getMTAPlayers($ip, $port) {
    $socket = @fsockopen("udp://$ip", $port, $errno, $errstr, 2);
    if (!$socket) return null;

    // MTA ASE Protokolü sorgu paketi ("s" harfi sunucu bilgilerini ve oyuncuları ister)
    fwrite($socket, "s");
    
    // Zaman aşımı ayarı (Sunucu yanıt vermezse siteyi bekletmemek için)
    stream_set_timeout($socket, 2);
    $response = fread($socket, 8192);
    fclose($socket);

    if (!$response || strlen($response) < 10) return null;

    // MTA'dan gelen ham veriyi parçalayalım
    // İlk kısımlar sunucu adı, map, oyun modu vb. bilgilerdir.
    $parts = explode("\x01", substr($response, 4));
    
    $server_name = $parts[0] ?? 'Ghetto Roleplay';
    $game_mode   = $parts[1] ?? 'Roleplay';
    $map_name    = $parts[2] ?? 'San Andreas';
    $version     = $parts[3] ?? '1.6';
    $player_count = $parts[5] ?? '0';
    $max_players  = $parts[6] ?? '250';

    // Oyuncu listesini ayıklama
    $player_data = substr($response, strpos($response, "\x01", 4) + 1);
    $players_raw = explode("\x01", $player_data);
    
    $players = [];
    // MTA protokolünde oyuncular belirli bir ofsetten sonra başlar
    // Bu basit döngü isimleri ve temel verileri yakalar
    for ($i = 9; $i < count($players_raw); $i += 5) {
        if (!empty($players_raw[$i])) {
            $players[] = [
                "id"    => count($players) + 1,
                "name"  => htmlspecialchars($players_raw[$i]),
                "score" => $players_raw[$i+2] ?? "0",
                "ping"  => $parts[4] ?? "0" // Ping verisi ASE paketinde değişkenlik gösterebilir
            ];
        }
    }

    return [
        "status" => "online",
        "player_count" => "$player_count/$max_players",
        "players" => $players
    ];
}

// Veriyi çek
$data = getMTAPlayers($server_ip, $query_port);

// Eğer sunucu kapalıysa veya cevap gelmezse hata dön
if (!$data) {
    echo json_encode([
        "status" => "offline",
        "player_count" => "0/0",
        "players" => []
    ]);
} else {
    echo json_encode($data);
}
?>
