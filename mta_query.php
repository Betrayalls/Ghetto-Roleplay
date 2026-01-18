<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Sunucu Bilgilerin
$server_ip = "212.38.88.96"; 
$query_port = 22126; // 22003 + 123

function getMTAData($ip, $port) {
    $socket = @fsockopen("udp://$ip", $port, $errno, $errstr, 2);
    if (!$socket) return null;

    fwrite($socket, "s");
    stream_set_timeout($socket, 2);
    $response = fread($socket, 8192);
    fclose($socket);

    if (!$response || strlen($response) < 10) return null;

    $parts = explode("\x01", substr($response, 4));
    $player_count = $parts[5] ?? '0';
    $max_players  = $parts[6] ?? '250';

    $player_data = substr($response, strpos($response, "\x01", 4) + 1);
    $players_raw = explode("\x01", $player_data);
    
    $players = [];
    for ($i = 9; $i < count($players_raw); $i += 5) {
        if (!empty($players_raw[$i])) {
            $clean_name = preg_replace('/#([a-fA-F0-9]{6})/', '', $players_raw[$i]);
            $players[] = [
                "id"    => count($players) + 1,
                "name"  => htmlspecialchars($clean_name),
                "score" => $parts[4] ?? "0", // Skor/Level
                "ping"  => $players_raw[$i+3] ?? "0"
            ];
        }
    }

    return [
        "status" => "online",
        "player_count" => "$player_count/$max_players",
        "players" => $players
    ];
}

$data = getMTAData($server_ip, $query_port);
echo json_encode($data ?: ["status" => "offline", "player_count" => "0/0", "players" => []]);
?>
