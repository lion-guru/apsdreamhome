<?php

namespace App\Services;

use PDO;

/**
 * LocationService — centralized geography for the whole project.
 *
 * Single source of truth for: states/districts/cities master data,
 * pincode lookup, geocoding (cached), distance math and nearby landmarks.
 * Master tables are cross-tenant reference data, so reads are unscoped.
 */
class LocationService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?: \App\Core\Database\Database::getInstance()->getConnection();
    }

    // ========== Master data ==========

    public function states(): array
    {
        return $this->pdo->query(
            "SELECT id, name, code FROM states WHERE is_active = 1 ORDER BY name"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function districtsByState(int $stateId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, name, code, state_id FROM districts WHERE state_id = ? AND is_active = 1 ORDER BY name"
        );
        $stmt->execute([$stateId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function citiesByDistrict(int $districtId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, name, type FROM cities WHERE district_id = ? ORDER BY name"
        );
        $stmt->execute([$districtId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========== Pincode lookup (India Post table first, learned data fallback) ==========

    public function pincodeLookup(string $pincode): ?array
    {
        $clean = preg_replace('/\D/', '', $pincode);
        if (strlen($clean) !== 6) return null;

        // 1. Official India Post data
        try {
            $stmt = $this->pdo->prepare(
                "SELECT pincode, taluk, district_name, state_name, state_code, latitude, longitude
                 FROM pincodes WHERE pincode = ? LIMIT 1"
            );
            $stmt->execute([$clean]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return [
                    'pincode' => $row['pincode'],
                    'city' => $row['taluk'] ?: $row['district_name'],
                    'district' => $row['district_name'],
                    'state' => $row['state_name'],
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'source' => 'pincode_table',
                ];
            }
        } catch (\Throwable $e) { error_log('LocationService::pincodeLookup table failed: ' . $e->getMessage()); }

        // 2. Learned from past user input (legacy fallback)
        try {
            $stmt = $this->pdo->prepare(
                "SELECT city, state FROM user_addresses WHERE pincode = ? AND city != '' AND state != '' ORDER BY id DESC LIMIT 1"
            );
            $stmt->execute([$clean]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return [
                    'pincode' => $clean,
                    'city' => $row['city'],
                    'state' => $row['state'],
                    'source' => 'learned',
                ];
            }
        } catch (\Throwable $e) { error_log('LocationService::pincodeLookup fallback failed: ' . $e->getMessage()); }

        return null;
    }

    // ========== Geocoding (Nominatim, cached in map_cache) ==========

    public function geocode(string $address): ?array
    {
        $address = trim($address);
        if ($address === '') return null;

        $key = 'geocode:' . md5(mb_strtolower($address));
        $cached = $this->cacheGet($key);
        if ($cached) return $cached;

        $url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' . urlencode($address . ', India');
        $res = $this->httpJson($url);
        if (empty($res[0]['lat']) || empty($res[0]['lon'])) return null;

        $out = [
            'latitude' => (float)$res[0]['lat'],
            'longitude' => (float)$res[0]['lon'],
            'display_name' => $res[0]['display_name'] ?? $address,
        ];
        $this->cacheSet($key, $out, '+30 days');
        return $out;
    }

    public function reverseGeocode(float $lat, float $lng): ?array
    {
        $key = 'revgeo:' . round($lat, 5) . ',' . round($lng, 5);
        $cached = $this->cacheGet($key);
        if ($cached) return $cached;

        $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lng";
        $res = $this->httpJson($url);
        if (empty($res['address'])) return null;

        $a = $res['address'];
        $out = [
            'city' => $a['city'] ?? $a['town'] ?? $a['village'] ?? null,
            'district' => $a['state_district'] ?? $a['county'] ?? null,
            'state' => $a['state'] ?? null,
            'pincode' => $a['postcode'] ?? null,
            'display_name' => $res['display_name'] ?? null,
        ];
        $this->cacheSet($key, $out, '+30 days');
        return $out;
    }

    // ========== Distance math (Haversine) ==========

    public function distanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    // ========== Nearby landmarks for a colony ==========

    public function nearbyLandmarks(int $colonyId, float $maxKm = 10.0, ?string $type = null): array
    {
        $colonyStmt = $this->pdo->prepare(
            "SELECT latitude, longitude FROM colonies WHERE id = ?"
        );
        $colonyStmt->execute([$colonyId]);
        $colony = $colonyStmt->fetch(PDO::FETCH_ASSOC);
        if (!$colony || empty($colony['latitude']) || empty($colony['longitude'])) return [];

        // Prefer pre-calculated distances, fall back to live Haversine
        try {
            $sql = "SELECT l.*, cld.distance_km
                    FROM colony_landmark_distances cld
                    JOIN landmarks l ON l.id = cld.landmark_id
                    WHERE cld.colony_id = ? AND cld.distance_km <= ?";
            $params = [$colonyId, $maxKm];
            if ($type) { $sql .= " AND l.type = ?"; $params[] = $type; }
            $sql .= " ORDER BY cld.distance_km";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($rows) return $rows;
        } catch (\Throwable $e) { error_log('LocationService::nearbyLandmarks matrix failed: ' . $e->getMessage()); }

        $sql = "SELECT * FROM landmarks WHERE is_active = 1 AND latitude IS NOT NULL AND longitude IS NOT NULL";
        $params = [];
        if ($type) { $sql .= " AND type = ?"; $params[] = $type; }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $l) {
            $d = $this->distanceKm(
                (float)$colony['latitude'], (float)$colony['longitude'],
                (float)$l['latitude'], (float)$l['longitude']
            );
            if ($d <= $maxKm) { $l['distance_km'] = round($d, 2); $out[] = $l; }
        }
        usort($out, fn($a, $b) => $a['distance_km'] <=> $b['distance_km']);
        return $out;
    }

    // ========== map_cache helpers ==========

    private function cacheGet(string $key): ?array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT cache_value FROM map_cache WHERE cache_key = ? AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1"
            );
            $stmt->execute([$key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return null;
            $val = json_decode($row['cache_value'], true);
            return is_array($val) ? $val : null;
        } catch (\Throwable $e) { return null; }
    }

    private function cacheSet(string $key, array $value, string $ttl): void
    {
        try {
            // cache_key has no UNIQUE index, so replace via DELETE + INSERT
            $del = $this->pdo->prepare("DELETE FROM map_cache WHERE cache_key = ?");
            $del->execute([$key]);
            $stmt = $this->pdo->prepare(
                "INSERT INTO map_cache (cache_key, cache_value, expires_at, created_at)
                 VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY), NOW())"
            );
            $stmt->execute([$key, json_encode($value)]);
        } catch (\Throwable $e) { error_log('LocationService::cacheSet failed: ' . $e->getMessage()); }
    }

    private function httpJson(string $url): ?array
    {
        try {
            $ctx = stream_context_create(['http' => [
                'timeout' => 8,
                'header' => "User-Agent: APSDreamHome/1.0 (contact: official@apsdreamhomes.com)\r\nAccept: application/json\r\n",
            ]]);
            $raw = @file_get_contents($url, false, $ctx);
            if ($raw === false) return null;
            $data = json_decode($raw, true);
            return is_array($data) ? $data : null;
        } catch (\Throwable $e) { return null; }
    }
}
