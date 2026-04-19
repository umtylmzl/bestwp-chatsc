-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost
-- Üretim Zamanı: 31 Mar 2026, 20:52:52
-- Sunucu sürümü: 10.4.28-MariaDB
-- PHP Sürümü: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `chat`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ayarlar`
--

CREATE TABLE `ayarlar` (
  `id` int(11) NOT NULL,
  `site_logo` varchar(400) NOT NULL,
  `site_baslik` varchar(350) DEFAULT NULL,
  `site_aciklama` varchar(300) DEFAULT NULL,
  `site_link` varchar(100) DEFAULT NULL,
  `site_sahip_mail` varchar(100) DEFAULT NULL,
  `site_mail_host` varchar(100) DEFAULT NULL,
  `site_mail_mail` varchar(100) DEFAULT NULL,
  `site_mail_port` int(11) DEFAULT NULL,
  `site_mail_sifre` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `ayarlar`
--

INSERT INTO `ayarlar` (`id`, `site_logo`, `site_baslik`, `site_aciklama`, `site_link`, `site_sahip_mail`, `site_mail_host`, `site_mail_mail`, `site_mail_port`, `site_mail_sifre`) VALUES
(1, '333480BestWP.jpg', 'BestWp', 'BestWp - Ücretsiz Chat Scripti', '', 'umut@gmail.com', '00000', '000', 0, '000000');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kullanicilar`
--

CREATE TABLE `kullanicilar` (
  `kul_id` int(11) NOT NULL,
  `kul_isim` varchar(200) DEFAULT NULL,
  `kul_mail` varchar(200) DEFAULT NULL,
  `kul_sifre` varchar(100) DEFAULT NULL,
  `kul_telefon` varchar(100) DEFAULT NULL,
  `kul_yetki` int(11) NOT NULL DEFAULT 0,
  `kul_onay` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=beklemede, 1=onaylı',
  `kul_totp_secret` varchar(128) DEFAULT NULL,
  `kul_totp_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `kul_son_giris` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `kullanicilar`
--

INSERT INTO `kullanicilar` (`kul_id`, `kul_isim`, `kul_mail`, `kul_sifre`, `kul_telefon`, `kul_yetki`, `kul_onay`, `kul_totp_secret`, `kul_totp_enabled`, `kul_son_giris`) VALUES
(1, 'admin', 'admin@gmail.com', '202cb962ac59075b964b07152d234b70', '111111', 1, 1, NULL, 0, '2026-03-30 04:18:12'),
(2, 'umut', 'umut@gmail.com', '202cb962ac59075b964b07152d234b70', '32323323', 0, 1, NULL, 0, '2026-03-30 04:02:00'),
(3, 'Mustafa', 'mustafa@gmail.com', '202cb962ac59075b964b07152d234b70', '5349867545', 0, 1, NULL, 0, '2026-03-30 04:10:28'),
(4, 'Buse', 'buse@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', '05309282877', 0, 0, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `mesajlar`
--

CREATE TABLE `mesajlar` (
  `mesaj_id` int(11) NOT NULL,
  `mesaj_gonderen` int(11) DEFAULT NULL,
  `mesaj_alici` int(10) UNSIGNED DEFAULT NULL,
  `mesaj_detay` text DEFAULT NULL,
  `mesaj_eklenme_tarih` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `mesajlar`
--

INSERT INTO `mesajlar` (`mesaj_id`, `mesaj_gonderen`, `mesaj_alici`, `mesaj_detay`, `mesaj_eklenme_tarih`) VALUES
(123, 2, 3, 'mustiii uyann', '2026-03-30 03:29:02'),
(124, 2, 3, 'agaa', '2026-03-30 03:29:05'),
(125, 2, 3, 'acill', '2026-03-30 03:29:09'),
(126, 2, NULL, 'merhaba arkadaşlarr', '2026-03-30 03:29:26'),
(127, 1, NULL, 'Githubdan beni takip etmey unutmayın!', '2026-03-30 03:31:28'),
(128, 1, 2, 'son giriş veri sistemi aktif', '2026-03-30 03:40:16'),
(129, 2, 1, 'teşekkürler sorunsuz çalışıyor.', '2026-03-30 03:40:50'),
(130, 2, 3, 'Mustafa yarın kaçta buluşacağız?', '2026-03-30 03:46:27'),
(131, 2, 1, 'Selam admin', '2026-03-30 03:46:49'),
(132, 2, NULL, 'Hemen Takip ettim!', '2026-03-30 03:47:10'),
(133, 2, NULL, 'Yeni özellikler eklendi mi ?', '2026-03-30 03:47:45'),
(162, 3, 2, 'öğlen 12:30\" da', '2026-03-30 04:09:38'),
(163, 3, 1, 'Admin yardımın gerek! arkadaşım üye olmuş kabul eder misin?', '2026-03-30 04:10:28');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `mesaj_okuma`
--

CREATE TABLE `mesaj_okuma` (
  `kul_id` int(11) NOT NULL,
  `thread` varchar(24) NOT NULL COMMENT 'g=genel forum, p:123=özel sohbet',
  `son_mesaj_id` int(11) NOT NULL DEFAULT 0,
  `arsiv` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `mesaj_okuma`
--

INSERT INTO `mesaj_okuma` (`kul_id`, `thread`, `son_mesaj_id`, `arsiv`) VALUES
(1, 'g', 133, 0),
(1, 'p:2', 131, 0),
(1, 'p:3', 0, 0),
(2, 'g', 133, 1),
(2, 'p:1', 131, 0),
(2, 'p:3', 157, 0),
(3, 'g', 133, 0),
(3, 'p:1', 163, 0),
(3, 'p:2', 162, 0);

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `ayarlar`
--
ALTER TABLE `ayarlar`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `kullanicilar`
--
ALTER TABLE `kullanicilar`
  ADD PRIMARY KEY (`kul_id`);

--
-- Tablo için indeksler `mesajlar`
--
ALTER TABLE `mesajlar`
  ADD PRIMARY KEY (`mesaj_id`),
  ADD KEY `idx_mesaj_alici` (`mesaj_alici`);

--
-- Tablo için indeksler `mesaj_okuma`
--
ALTER TABLE `mesaj_okuma`
  ADD PRIMARY KEY (`kul_id`,`thread`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `ayarlar`
--
ALTER TABLE `ayarlar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `kullanicilar`
--
ALTER TABLE `kullanicilar`
  MODIFY `kul_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `mesajlar`
--
ALTER TABLE `mesajlar`
  MODIFY `mesaj_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=164;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
