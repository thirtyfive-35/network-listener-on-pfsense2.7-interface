#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include <pcap.h>
#include <netinet/in.h>
#include <netinet/if_ether.h>
#include <netinet/ip.h>
#include <netinet/tcp.h>
#include <arpa/inet.h>
#include <netinet/udp.h> 

#define MAX_PACKETS 1000

char *network_device;
int *globalDizi;
int globalBoyut = 10;

void my_packet_handler(
    u_char *args,
    const struct pcap_pkthdr *packet_header,
    const u_char *packet_body
);

char *print_packet_info(const u_char *packet, struct pcap_pkthdr packet_header);
int *input_read();

int main(int argc, char *argv[]) {
    char *device;
    char error_buffer[PCAP_ERRBUF_SIZE];
    pcap_t *handle;
    int timeout_limit = 100; /* In milliseconds */

    globalDizi = input_read();

    device = network_device;
    if (device == NULL) {
        printf("Error finding device: %s\n", error_buffer);
        return 1;
    }

    handle = pcap_open_live(
        device,
        BUFSIZ,
        0,
        timeout_limit,
        error_buffer
    );
    if (handle == NULL) {
        fprintf(stderr, "Could not open device %s: %s\n", device, error_buffer);
        return 2;
    }

    // Paketleri tutacak olan dizi
    const int max_packets = MAX_PACKETS;
    u_char *packet_array[max_packets];

    pcap_loop(handle, 0, my_packet_handler, (u_char *)packet_array);

    return 0;
}

void my_packet_handler(
    u_char *args,
    const struct pcap_pkthdr *packet_header,
    const u_char *packet_body
)
{
    // Paket dizisi
    u_char **packet_array = (u_char **)args;

    // Dizi doluysa print_packet_info fonksiyonunu çağır ve dosyaya yaz
    static int packet_count = 0; // packet_count'u burada tanımlıyoruz
    if (packet_count == MAX_PACKETS) {
        FILE *file = fopen("output.txt", "a"); // "a" dosyaya ekleme modu
        if (file == NULL) {
            perror("Error opening file");
            return;
        }

        for (int i = 0; i < MAX_PACKETS; i++) {
            char *result = print_packet_info(packet_array[i], *packet_header);
            if (result != NULL) {
                fprintf(file, "%s", result);
                free(result);
            }
            free(packet_array[i]);
        }

        fclose(file);
        packet_count = 0;
    }

    // Paketi diziye ekle
    packet_array[packet_count] = malloc(packet_header->len);
    memcpy(packet_array[packet_count], packet_body, packet_header->len);
    packet_count++;

    return;
}

char *print_packet_info(const u_char *packet, struct pcap_pkthdr packet_header) {
    // Ekstra özellikleri çıkarmak için paketi parse et
    struct ether_header *eth_header = (struct ether_header *)packet;
    struct ip *ip_header = (struct ip *)(packet + ETHER_HDR_LEN);

    // Zaman bilgisini al
    time_t rawtime = packet_header.ts.tv_sec;
    struct tm *timestamp = localtime(&rawtime);
    char time_str[20];
    strftime(time_str, sizeof(time_str), "%Y-%m-%d %H:%M:%S", timestamp);

    // IP ve Ethernet bilgileri
    char source_mac_str[18];
    snprintf(source_mac_str, sizeof(source_mac_str),
             "%02x:%02x:%02x:%02x:%02x:%02x",
             eth_header->ether_shost[0], eth_header->ether_shost[1],
             eth_header->ether_shost[2], eth_header->ether_shost[3],
             eth_header->ether_shost[4], eth_header->ether_shost[5]);

    char src_ip_str[INET_ADDRSTRLEN];
    char dst_ip_str[INET_ADDRSTRLEN];

    // inet_ntop kullanımı
    if (inet_ntop(AF_INET, &(ip_header->ip_src), src_ip_str, INET_ADDRSTRLEN) == NULL) {
        perror("Error converting source IP address");
        return NULL;
    }

    if (inet_ntop(AF_INET, &(ip_header->ip_dst), dst_ip_str, INET_ADDRSTRLEN) == NULL) {
        perror("Error converting destination IP address");
        return NULL;
    }

    // Bilgileri bir string olarak oluştur
    char *result = malloc(256); // Uygun boyutta bir bellek ayırın
    int match_found = 0;

    if (ip_header->ip_p == IPPROTO_TCP) {
    snprintf(result, 256, "%s,%s,%s,%s,%u,%u\n",
        time_str,
        src_ip_str,
        dst_ip_str,
        source_mac_str,
        ntohs(((struct tcphdr *)(packet + ETHER_HDR_LEN + (ip_header->ip_hl << 2)))->th_sport),
        ntohs(((struct tcphdr *)(packet + ETHER_HDR_LEN + (ip_header->ip_hl << 2)))->th_dport)
    );
    } else if (ip_header->ip_p == IPPROTO_UDP) {
        snprintf(result, 256, "%s,%s,%s,%s,%u,%u\n",
            time_str,
            src_ip_str,
            dst_ip_str,
            source_mac_str,
            ntohs(((struct udphdr *)(packet + ETHER_HDR_LEN + (ip_header->ip_hl << 2)))->uh_sport),
            ntohs(((struct udphdr *)(packet + ETHER_HDR_LEN + (ip_header->ip_hl << 2)))->uh_dport)
        );
    } 

    for (int i = 0; i < globalBoyut; i++) {
        if ((ip_header->ip_p == IPPROTO_TCP) &&
            (ntohs(((struct tcphdr *)(packet + ETHER_HDR_LEN + (ip_header->ip_hl << 2)))->th_sport) == globalDizi[i] || 
            ntohs(((struct tcphdr *)(packet + ETHER_HDR_LEN + (ip_header->ip_hl << 2)))->th_dport) == globalDizi[i]))
        {
            return result;
        }

        if ((ip_header->ip_p == IPPROTO_UDP) &&
            (ntohs(((struct udphdr *)(packet + ETHER_HDR_LEN + (ip_header->ip_hl << 2)))->uh_sport) == globalDizi[i] || 
            ntohs(((struct udphdr *)(packet + ETHER_HDR_LEN + (ip_header->ip_hl << 2)))->uh_dport) == globalDizi[i]))
        {
            return result;
        }
    }
return 0;
}


int *input_read() {
    FILE *dosya;
    char karakter;
    int satir = 2;
    int *sayilar = malloc(10 * sizeof(int));
    int i = 0;
    int bos = 0;

    dosya = fopen("input.txt", "r");

    if (dosya == NULL) {
        perror("Dosya açma hatası");
        return NULL;
    }

    char *ilkSatirChar = malloc(4);
    fscanf(dosya, " %s", ilkSatirChar);

    network_device = strdup(ilkSatirChar);
    free(ilkSatirChar);

    for (int i = 1; i < satir; ++i) {
        while ((karakter = fgetc(dosya)) != '\n') {
            if (karakter == EOF) {
                // Dosya sonuna ulaşıldı, çıkış
                fclose(dosya);
                free(sayilar); // Hafızayı serbest bırak
                return NULL;
            }
        }
    }

    // Kalan kısmı kaydet
    while ((karakter = fgetc(dosya)) != EOF) {
        if (karakter != '\n' && '0' <= karakter && karakter <= '9') {
            karakter = karakter - '0';
            bos = bos * 10 + karakter; // Sayıları düzgün bir şekilde birleştir
        } else {
            sayilar[i] = bos; // Sayıyı diziye ekle
            i += 1;
            bos = 0; // bos'u sıfırla
        }
    }

    fclose(dosya);

    return sayilar;
}
