<?php
// Formdan gelen verileri al
$interfaceNetwork = $_POST['interfaceNetwork'];
$ports = $_POST['ports'];

// Güvenlik kontrolleri ve temizleme işlemleri
$cleanedInterface = escapeshellarg($interfaceNetwork);

// Verileri dosyaya yazma
$cleanedInterface = trim($cleanedInterface, "'\"");
$dataToWrite = "$cleanedInterface\n";

$dataToWrite .= implode("\n", $ports) . "\n";
$serviceStatus = 'Dinleyici başlatıldı';

// Dosyaya yazma işlemi
file_put_contents('/usr/local/www/sertifika/input.txt', $dataToWrite);

file_put_contents('/usr/local/www/sertifika/serviceStatus.txt', $serviceStatus);

$command = '/usr/local/www/sertifika/cert.sh > /dev/null 2>&1 &';
exec('nohup ' . $command, $output, $returnCode);

// Yönlendirme işlemi
if ($returnCode === 0) {
    // İşlem başarıyla başlatıldı, formun olduğu sayfaya yönlendir
    header("Location: packet_analyzer.php");
    exit(); // Yönlendirmenin ardından işlemi sonlandır
} else {
    // İşlem başlatılamadı, 
    echo "İşlem başlatılamadı!";
}

?>
