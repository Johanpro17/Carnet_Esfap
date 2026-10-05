<?php

namespace App\Http\Controllers;

class QrController extends Controller
{
    public static function baseUrl(): string
    {
        $env = trim((string) env('QR_BASE_URL'));
        if ($env !== '') {
            return rtrim($env, '/');
        }

        $host = strtolower(trim((string) request()->getHost()));
        $local = ['127.0.0.1', 'localhost', '::1'];

        if (in_array($host, $local, true)) {
            $lan = static::lanIp();
            if ($lan !== null) {
                $port = request()->getPort() ?: 80;
                return 'http://' . $lan . ($port != 80 ? ':' . $port : '');
            }
        }

        return rtrim(url('/'), '/');
    }

    private static function lanIp(): ?string
    {
        if (function_exists('network_get_interfaces')) {
            foreach (network_get_interfaces() as $if) {
                foreach ($if['unicast'] ?? [] as $u) {
                    if (static::isLanIpv4($u['address'] ?? '')) {
                        return $u['address'];
                    }
                }
            }
        }

        $ip = gethostbyname(trim((string) gethostname()));
        if (static::isLanIpv4($ip)) {
            return $ip;
        }

        if (DIRECTORY_SEPARATOR === '\\' && function_exists('exec')) {
            $out = [];
            exec('ipconfig', $out, $rc);
            if ($rc === 0) {
                foreach ($out as $line) {
                    if (preg_match('/IPv4[^\d]{0,10}\s*:\s*([0-9.]+)/', $line, $m)) {
                        $ip = trim($m[1]);
                        if (static::isLanIpv4($ip)) {
                            return $ip;
                        }
                    }
                }
            }
        }

        return null;
    }

    private static function isLanIpv4(string $ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        if (str_starts_with($ip, '127.') || str_starts_with($ip, '169.254.')) {
            return false;
        }

        return true;
    }

    public function png(string $token)
    {
        require_once base_path('app/Lib/phpqrcode/phpqrcode.php');

        $url = static::baseUrl() . '/verificar/' . $token . '/pdf';

        ob_start();
        \QRcode::png($url, false, 'M', 6, 2);
        $png = ob_get_clean();

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Length' => strlen($png),
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}