cd /usr/local/www
mkdir sertifika /usr/local/www





scp execute_ccode.php stop_analyzer.php packet_analyzer.php head.inc root@192.168.1.1://usr/local/www 

ssh root@192.168.1.1

mkdir /usr/local/www/sertifika

scp cert.sh network root@192.168.1.1://usr/local/www/sertifika
chmod +x /usr/local/www/sertifika/cert.sh
chmod +x /usr/local/www/sertifika/network


openssl genpkey -algorithm RSA -out private_key.pem -aes256
openssl rsa -pubout -in private_key.pem -out public_key.pem

gcc v9.c -o network -lpcap 

