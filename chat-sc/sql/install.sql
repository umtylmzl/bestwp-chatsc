-- BestWp chat-sc — veritabanı uzantıları
-- Önkoşul: temel `kullanicilar`, `mesajlar`, `ayarlar` tabloları zaten kurulu olmalıdır.
-- MySQL / MariaDB. "Duplicate column" / "already exists" hatası alırsanız o bölümü atlayın.

-- ---------------------------------------------------------------------------
-- Özel mesaj: NULL = genel forum, dolu = alıcı kullanıcı ID
-- ---------------------------------------------------------------------------
ALTER TABLE `mesajlar`
	ADD COLUMN `mesaj_alici` INT UNSIGNED NULL DEFAULT NULL AFTER `mesaj_gonderen`,
	ADD INDEX `idx_mesaj_alici` (`mesaj_alici`);

-- ---------------------------------------------------------------------------
-- Kayıt sonrası admin onayı (0=beklemede, 1=onaylı)
-- ---------------------------------------------------------------------------
ALTER TABLE `kullanicilar`
	ADD COLUMN `kul_onay` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0=beklemede, 1=onaylı' AFTER `kul_yetki`;

-- ---------------------------------------------------------------------------
-- İki adımlı doğrulama (TOTP)
-- ---------------------------------------------------------------------------
ALTER TABLE `kullanicilar`
	ADD COLUMN `kul_totp_secret` VARCHAR(128) NULL DEFAULT NULL AFTER `kul_onay`,
	ADD COLUMN `kul_totp_enabled` TINYINT(1) NOT NULL DEFAULT 0 AFTER `kul_totp_secret`;

-- ---------------------------------------------------------------------------
-- Son giriş / son aktivite (sohbet başlığı)
-- ---------------------------------------------------------------------------
ALTER TABLE `kullanicilar`
	ADD COLUMN `kul_son_giris` DATETIME NULL DEFAULT NULL;

-- ---------------------------------------------------------------------------
-- Okunmamış sayacı + arşiv (thread bazlı son okunan mesaj)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `mesaj_okuma` (
	`kul_id` INT NOT NULL,
	`thread` VARCHAR(24) NOT NULL COMMENT 'g=genel forum, p:123=özel sohbet',
	`son_mesaj_id` INT NOT NULL DEFAULT 0,
	`arsiv` TINYINT(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (`kul_id`, `thread`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Eski sürümde `mesaj_okuma` sadece 3 sütunla oluşturulduysa (arsiv yok), bir kez:
-- ALTER TABLE `mesaj_okuma` ADD COLUMN `arsiv` TINYINT(1) NOT NULL DEFAULT 0 AFTER `son_mesaj_id`;
