# BestWp Chat
PHP ve MySQL ile çalışan, genel kanal + özel mesaj destekleyen hafif sohbet ve yönetim scripti.
## Özellikler
- Genel forum sohbeti ve kullanıcılar arası özel mesajlaşma
- Yönetici paneli, kullanıcı / ayar yönetimi, kayıt onayı akışı
- İsteğe bağlı iki adımlı giriş (TOTP)
- Okunmamış mesaj sayacı, sohbet arşivi, responsive arayüz (Tailwind)
## Gereksinimler
- PHP 7.4+ (önerilen: 8.x), `pdo_mysql` eklentisi
- MySQL veya MariaDB
## Kurulum
1. Depoyu indirip web sunucu köküne kopyalayın.
2. `islemler/baglan.example.php` dosyasını `islemler/baglan.php` olarak kopyalayın ve veritabanı bilgilerinizi girin.
3. Veritabanında `sql/install.sql` dosyasını çalıştırın (önce temel tablolarınız hazır olmalıdır).
Ayrıntılar için depo içindeki `sql/install.sql` ve `baglan.example.php` yorumlarına bakın.
## Varsayılan yönetici (örnek)
İlk kurulumda veritabanınıza eklediğiniz yönetici hesabı ile giriş yapın. Örnek:
| Alan    | Değer           |
|---------|-----------------|
| E-posta | admin@gmail.com |
| Şifre   | 123             |
**Önemli:** Canlı ortamda güçlü şifre kullanın ve bu örnek bilgileri değiştirin.
## Güvenlik
- `islemler/baglan.php` dosyasını repoya yüklemeyin (`.gitignore` ile hariç tutulabilir).
- Üretimde HTTPS kullanın.
## Yazar
**Umut Yılmaz** — [GitHub](https://github.com/umtylmzl)
## Lisans
Bu depodaki kullanım ve lisans tercihinize bağlıdır; dağıtırken kendi lisans dosyanızı eklemeniz önerilir.
<img width="1918" height="969" alt="Ekran Resmi 2026-03-30 04 11 41" src="https://github.com/user-attachments/assets/8fe3100c-c64a-46fc-a772-f836bdb22e38" />
<img width="1918" height="969" alt="Ekran Resmi 2026-03-30 04 19 40" src="https://github.com/user-attachments/assets/1c4ddf6d-d671-4b4f-9b68-1254f0325081" />
<img width="1918" height="969" alt="Ekran Resmi 2026-03-30 04 17 51" src="https://github.com/user-attachments/assets/f7097832-2893-423a-a9a2-9078343c4042" />
