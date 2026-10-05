<?php

namespace App\Http\Controllers;

use App\Models\Carnet;
use Illuminate\Support\Str;

class CarnetController extends Controller
{
    public static function porToken(?string $token): ?Carnet
    {
        if (!$token) {
            return null;
        }

        return Carnet::with(['alumno.carrera', 'vencimientos'])
            ->whereRaw('LOWER(token_qr) = ?', [Str::lower(trim($token))])
            ->first();
    }

    public function show(string $token)
    {
        $carnet = static::porToken($token);

        abort_unless($carnet, 404, 'Carnet no encontrado. Verifique el código escaneado.');

        $estado = $carnet->alumno?->estado;
        abort_if($estado !== 'ACTIVO', 403, 'Carnet no válido. El estudiante tiene estado: ' . $estado);

        $vigencia = $carnet->vigenciaActual();
        if ($vigencia && \Carbon\Carbon::parse($vigencia)->isBefore(today())) {
            abort(403, 'Carnet vencido. La vigencia expiró el ' . \Carbon\Carbon::parse($vigencia)->format('d/m/Y') . '.');
        }

        return view('carnets.verificar', compact('carnet'));
    }

    public function qrTempFile(string $token): string
    {
        require_once base_path('app/Lib/phpqrcode/phpqrcode.php');

        $dir = storage_path('app/cache');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $file = $dir . '/qr_' . $token . '.png';
        $url = QrController::baseUrl() . '/verificar/' . $token . '/pdf';

        \QRcode::png($url, $file, 'M', 6, 2);

        return $file;
    }

    public static function getPdfBytes(string $token): string
    {
        $carnet = static::porToken($token);

        abort_unless($carnet, 404, 'Carnet no encontrado.');

        $estado = $carnet->alumno?->estado;
        abort_if($estado !== 'ACTIVO', 403, 'Carnet no válido. El estudiante tiene estado: ' . $estado);

        $qrFile = (new static)->qrTempFile($carnet->token_qr);

        $html = view('carnets.card', [
            'carnet' => $carnet,
            'pdf' => true,
            'qrSrc' => $qrFile,
        ])->render();

        $html = preg_replace_callback('/src="([^"]+\.(?:png|jpe?g|gif|webp))"/i', function ($m) {
            $p = $m[1];
            if (str_starts_with($p, 'data:')) {
                return $m[0];
            }
            $full = null;
            if (is_file($p)) {
                $full = $p;
            } elseif (is_file(public_path($p))) {
                $full = public_path($p);
            }
            if (!$full) {
                return $m[0];
            }
            $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
            if ($ext === 'jpg' || $ext === 'jpeg') {
                $ext = 'jpeg';
            }
            return 'src="data:image/' . $ext . ';base64,' . base64_encode((string) file_get_contents($full)) . '"';
        }, $html);

        $html = '<style>@page { size: 540px 800px; margin: 0; } html, body { margin: 0 !important; padding: 0 !important; } .carnet-wrap { box-sizing: border-box; overflow: hidden; }</style>' . $html;

        $dompdf = new \Dompdf\Dompdf(['defaultFont' => 'dejavu sans']);
        $dompdf->loadHtml($html);
        $dompdf->render();

        @unlink($qrFile);

        return $dompdf->output();
    }

    public function pdf(string $token)
    {
        $pdf = static::getPdfBytes($token);

        $nombre = static::porToken($token)?->alumno?->codigo_alumno ?: 'carnet';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="carnet-' . $nombre . '.pdf"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'public',
        ]);
    }
}