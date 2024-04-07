


![Ekran görüntüsü 2024-04-07 225407](https://github.com/thirtyfive-35/network-listener-on-pfsense2.7-interface/assets/99458931/029d0157-326f-4080-ad58-ee80481bf0b4)
![Ekran görüntüsü 2024-04-07 225345](https://github.com/thirtyfive-35/network-listener-on-pfsense2.7-interface/assets/99458931/38348fe1-c994-4216-9162-09d1b15e9603)
![Ekran görüntüsü 2024-04-07 225322](https://github.com/thirtyfive-35/network-listener-on-pfsense2.7-interface/assets/99458931/480aad0a-efae-4f39-b3eb-9b3bf2074e95)


## Setup

#Firsty use command below in virtual machine

```bash
  git clone https://github.com/thirtyfive-35/network-listener-on-pfsense2.7-interface.git 
```


transfer that with scp

```bash
  scp execute_ccode.php stop_analyzer.php packet_analyzer.php head.inc root@192.168.1.1://usr/local/www 
```

```bash
  ssh root@192.168.1.1
```

```bash
  mkdir /usr/local/www/sertifika
```


```bash
  scp cert.sh root@192.168.1.1://usr/local/www/sertifika
```
Compile C code in freeBSD-14.0.
```bash
  gcc v9.c -o network -lpcap
```

```bash
  scp cert.sh root@192.168.1.1://usr/local/www/sertifika
```

```bash
  chmod +x /usr/local/www/sertifika/cert.sh
```

```bash
  chmod +x /usr/local/www/sertifika/network
```

```bash
  openssl genpkey -algorithm RSA -out private_key.pem -aes256
```

```bash
  openssl rsa -pubout -in private_key.pem -out public_key.pem
```


