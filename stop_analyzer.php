<?php

$path = '/usr/local/www/sertifika/process_id.txt';

// Dosyadaki içeriği oku
$content = file_get_contents($path);

// Satırları \n karakterine göre ayır
$pids = explode("\n", $content);

// Servis durumunu güncelle
$serviceStatus = 'Dinleyici Durduruldu';
file_put_contents('/usr/local/www/sertifika/serviceStatus.txt', $serviceStatus);

// Pid'leri kullanarak işlemi durdur
$command = 'kill ' . $pids[0] . ' ' . $pids[1];
exec('nohup ' . $command . ' > /dev/null 2>&1 &', $output, $returnCode);

// Yönlendirme işlemi
if ($returnCode === 0) {
    // İşlem başarıyla durduruldu, formun olduğu sayfaya yönlendir
    header("Location: packet_analyzer.php");
    exit(); // Yönlendirmenin ardından işlemi sonlandır
} else {
    // İşlem durdurulamadı
    echo "İşlem durdurulamadı!";
}
?>
