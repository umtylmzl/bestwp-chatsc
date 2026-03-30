# BestWp Chat (chat-sc)

PHP + MySQL sohbet: genel forum, özel mesajlar, yönetim paneli, TOTP, okunmamış rozetleri.

## Gereksinimler

- PHP 7.4+ (önerilen 8.x), `pdo_mysql`
- MySQL veya MariaDB
- Temel tablolar: `kullanicilar`, `mesajlar`, `ayarlar` (orijinal BestWp / kurs şeması)

## Kurulum

1. Dosyaları web köküne kopyalayın.
2. Veritabanı bağlantısı:
   ```bash
   cp islemler/baglan.example.php islemler/baglan.php
   ```
   `islemler/baglan.php` içinde host, veritabanı adı, kullanıcı ve şifreyi düzenleyin.
3. Uzantı SQL’ini çalıştırın (phpMyAdmin veya `mysql`):
   ```text
   sql/install.sql
   ```
   Daha önce tek tek migration çalıştırdıysanız, “Duplicate column” uyarılarında ilgili satırları atlayın.

## Depo notları

- `islemler/baglan.php` `.gitignore` içindedir; canlı şifreleri repoya koymayın.
- Sohbet arayüzü: `messenger.php`. `chat.php` doğrudan `messenger.php` yönlendirir.

## Lisans

Proje bileşenleri kendi lisanslarına tabidir (ör. jQuery). Uygulama kodu için depo sahibinin tercihine bakın.
