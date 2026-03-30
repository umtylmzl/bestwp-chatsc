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
