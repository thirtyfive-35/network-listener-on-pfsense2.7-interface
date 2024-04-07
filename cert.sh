


# C programını başlat
cd /usr/local/www/sertifika && ./network > /dev/null 2>&1 &

# Bash PID'sini al
BASH_PID=$$

# C programının çalıştığı PID'yi al
PID=$!

echo -e "$BASH_PID\n$PID" > /usr/local/www/sertifika/process_id.txt

# Belirli bir süre bekle
sleep 60  # Örneğin, 5 dakika (300 saniye)

# C programını durdur (isteğe bağlı)
kill $PID

# Çıktı dosyasını imzala
openssl dgst -sha256 -sign /usr/local/www/sertifika/private_key.pem -out /usr/local/www/sertifika/signature.bin -binary /usr/local/www/sertifika/output.txt

# Çıktı dosyasını sertifika ile doğrula
openssl dgst -sha256 -verify /usr/local/www/sertifika/public_key.pem -signature /usr/local/www/sertifika/signature.bin -binary /usr/local/www/sertifika/output.txt

echo "C programı başarıyla çalıştı ve durdu!" > /usr/local/www/sertifika/mesaj.txt