<?php
/**
 * RFC 6238 TOTP (Google Authenticator uyumlu), SHA-1, 30 sn, 6 hane.
 */
class BestWpTotp {
	private static function base32Map() {
		return 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
	}

	public static function randomSecret($bytes = 10) {
		$raw = random_bytes($bytes);
		return self::base32Encode($raw);
	}

	public static function base32Encode($binary) {
		$map = self::base32Map();
		$len = strlen($binary);
		$bits = 0;
		$value = 0;
		$out = '';
		for ($i = 0; $i < $len; $i++) {
			$value = ($value << 8) | ord($binary[$i]);
			$bits += 8;
			while ($bits >= 5) {
				$out .= $map[($value >> ($bits - 5)) & 31];
				$bits -= 5;
			}
		}
		if ($bits > 0) {
			$out .= $map[($value << (5 - $bits)) & 31];
		}
		return $out;
	}

	public static function base32Decode($secret) {
		$secret = strtoupper(preg_replace('/[^A-Z2-7]/', '', (string) $secret));
		if ($secret === '') {
			return '';
		}
		$map = array_flip(str_split(self::base32Map()));
		$bits = 0;
		$value = 0;
		$out = '';
		$len = strlen($secret);
		for ($i = 0; $i < $len; $i++) {
			$c = $secret[$i];
			if (!isset($map[$c])) {
				continue;
			}
			$value = ($value << 5) | $map[$c];
			$bits += 5;
			if ($bits >= 8) {
				$out .= chr(($value >> ($bits - 8)) & 255);
				$bits -= 8;
			}
		}
		return $out;
	}

	public static function codeForCounter($secret, $counter) {
		$key = self::base32Decode($secret);
		if ($key === '') {
			return '000000';
		}
		$counter = (int) $counter;
		$bin = pack('N*', 0) . pack('N*', $counter);
		$hash = hash_hmac('sha1', $bin, $key, true);
		$offset = ord(substr($hash, -1)) & 0x0F;
		$truncated = (
			((ord($hash[$offset]) & 0x7F) << 24) |
			((ord($hash[$offset + 1]) & 0xFF) << 16) |
			((ord($hash[$offset + 2]) & 0xFF) << 8) |
			(ord($hash[$offset + 3]) & 0xFF)
		) % 1000000;
		return str_pad((string) $truncated, 6, '0', STR_PAD_LEFT);
	}

	public static function getCode($secret, $timestamp = null) {
		$t = $timestamp === null ? time() : (int) $timestamp;
		$slice = (int) floor($t / 30);
		return self::codeForCounter($secret, $slice);
	}

	public static function verify($userCode, $secret, $discrepancy = 1) {
		$userCode = preg_replace('/\D/', '', (string) $userCode);
		if (strlen($userCode) !== 6) {
			return false;
		}
		$secret = trim((string) $secret);
		if ($secret === '') {
			return false;
		}
		$ts = (int) floor(time() / 30);
		for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
			if (hash_equals(self::codeForCounter($secret, $ts + $i), $userCode)) {
				return true;
			}
		}
		return false;
	}

	public static function otpauthUri($secret, $accountLabel, $issuer = 'BestWp') {
		$label = rawurlencode($issuer . ':' . $accountLabel);
		$query = http_build_query(
			array(
				'secret' => $secret,
				'issuer' => $issuer,
				'algorithm' => 'SHA1',
				'digits' => 6,
				'period' => 30,
			),
			'',
			'&',
			PHP_QUERY_RFC3986
		);
		return 'otpauth://totp/' . $label . '?' . $query;
	}
}
