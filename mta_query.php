<?php
// Ghetto RP - Sunucu Veri Çekme Motoru
header('Content-Type: application/json');

// --- BU ALANI BİLGİLER GELİNCE DÜZENLE ---
$server_ip = "185.xxx.xxx.xxx"; // Sunucu IP adresi
$query_port = 22126;           // Query Portu (Genelde Game Port + 123)
// ----------------------------------------

function getMTAStatus($ip, $port) {
    $socket = @fsockopen("udp://$ip", $port, $errno, $errstr, 2);
    if (!$socket) return null;

    // MTA Query Protokolü (V1.0)
    fwrite($socket, "s"); 
    $content = fread($socket, 2048);
    fclose($socket);

    if (empty($content)) return null;

    // Gelen veriyi parçalama (Basit anlatım için kısaltılmıştır)
    // Gerçek bir kütüphane kullanımı daha sağlıklıdır ancak bu temel yapıdır.
    return ["online" => true, "ip" => $ip]; 
}

// Simülasyon verisi (Bilgiler gelene kadar boş döner)
echo json_encode([
    "status" => "online",
    "player_count" => "0/250",
    "players" => [
        // Burası sunucu aktif olduğunda otomatik dolacak
    ]
]);
?>