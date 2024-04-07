# introduce
I developed a network listener on a page added to the Pfsense 2.7 interface. By selecting specific ports and network interfaces, I developed a network listener that monitors the network and saves packets to a text document in a certain format (Format: time, source IP, destination IP, source MAC, source port, destination port). Additionally, it periodically signs the network traffic with a certificate to record it.

![Ekran görüntüsü 2024-04-07 225407](https://github.com/thirtyfive-35/network-listener-on-pfsense2.7-interface/assets/99458931/029d0157-326f-4080-ad58-ee80481bf0b4)

![Ekran görüntüsü 2024-04-07 225345](https://github.com/thirtyfive-35/network-listener-on-pfsense2.7-interface/assets/99458931/38348fe1-c994-4216-9162-09d1b15e9603)

![Ekran görüntüsü 2024-04-07 225322](https://github.com/thirtyfive-35/network-listener-on-pfsense2.7-interface/assets/99458931/480aad0a-efae-4f39-b3eb-9b3bf2074e95)


# Setup

## Firsty use command below in virtual machine

```bash
  git clone https://github.com/thirtyfive-35/network-listener-on-pfsense2.7-interface.git 
```


## transfer via scp and establish ssh connection

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

# Compile C code in freeBSD-14.0 and transfer
```bash
  gcc v9.c -o network -lpcap
```

```bash
  scp network root@192.168.1.1://usr/local/www/sertifika
```
## authorization of files

```bash
  chmod +x /usr/local/www/sertifika/cert.sh
```

```bash
  chmod +x /usr/local/www/sertifika/network
```
## certification procedures

```bash
  cd /usr/local/www/sertifika
```

```bash
  openssl genpkey -algorithm RSA -out private_key.pem -aes256
```

```bash
  openssl rsa -pubout -in private_key.pem -out public_key.pem
```


