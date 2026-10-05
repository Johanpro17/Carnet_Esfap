<?php
$alumno = $carnet->alumno;
$carrera = $alumno?->carrera;
$vigencia = $carnet->vigenciaActual();
$qr = $qrSrc ?? url('/qr/' . $carnet->token_qr);
$qrImagen = empty($ocultarQr);
$logoMineduImagen = empty($ocultarLogo);
$foto = null;
if ($alumno && $alumno->foto && file_exists(public_path('uploads/' . $alumno->foto))) {
    $foto = $pdf ? public_path('uploads/' . $alumno->foto) : asset('uploads/' . $alumno->foto);
}
$mostrarEncabezado = empty($ocultarEncabezado);
$mostrarCuerpo = empty($ocultarCuerpo);
$mostrarPie = empty($ocultarPie);
$vigLabel = $vigencia ? \Carbon\Carbon::parse($vigencia)->format('d/m/Y') : 'PENDIENTE';
$ini = ($alumno?->nombres[0] ?? '') . ($alumno?->apellido_paterno[0] ?? '');
?>
<div class="carnet-wrap" style="width:400px;height:620px;margin:0 auto;border:1px solid #d9c4e6;border-radius:14px;overflow:hidden;box-shadow:0 12px 28px rgba(39,21,73,.2);font-family:{{ $pdf ? 'dejavu sans' : 'system-ui, -apple-system, sans-serif' }};background:#fff;">
    <table width="100%" cellpadding="0" cellspacing="0" style="width:100%;height:100%;table-layout:fixed;border-collapse:collapse;">
        @if ($mostrarEncabezado)
        <tr>
            <td height="96" style="height:96px;background:#53386F;padding:3px 10px 0;color:#fff;vertical-align:top;">
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                    <tr>
                        <td width="54" align="center" valign="top">
                            @if ($logoMineduImagen)
                                <img src="{{ $pdf ? public_path('imagenes/logo_minedu.png') : asset('imagenes/logo_minedu.png') }}" width="44" height="44" style="object-fit:contain;margin-top:4px;" alt="MINEDU">
                            @endif
                        </td>
                        <td align="center" valign="top" style="padding-top:6px;">
                            <div style="font-size:12px;color:#fff;letter-spacing:1px;text-transform:uppercase;font-weight:600;">Escuela Superior de Formación Artística Pública</div>
                            <div style="font-size:12px;font-weight:700;letter-spacing:1px;color:#fff;">«EDUARDO MEZA SARAVIA»</div>
                            <div style="width:120px;height:2px;background:#BB8F38;margin:4px auto 0;"></div>
                        </td>
                        <td width="54" align="center" valign="top">
                            @if ($logoMineduImagen)
                                <img src="{{ $pdf ? public_path('imagenes/logo_de_institucion.png') : asset('imagenes/logo_de_institucion.png') }}" width="44" height="44" style="object-fit:contain;background:#fff;border-radius:50%;padding:3px;margin-top:4px;" alt="ESFAP">
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        @endif
        @if ($mostrarCuerpo)
        <tr>
            <td style="background:#fff;padding:10px 16px;vertical-align:top;">
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                    <tr>
                        <td align="center" style="padding-bottom:8px;">
                            <table cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <tr>
                                    <td style="border:2px solid #53386F;padding:0;background:#fff;">
                                        @if ($foto)
                                            <img src="{{ $foto }}" width="336" height="258" style="object-fit:cover;display:block;margin:0 auto;" alt="Foto">
                                        @else
                                            <div style="width:336px;height:258px;color:#c9a7d8;font-size:13px;text-align:center;padding-top:100px;">SIN FOTO<br><br><span style="font-size:11px;">{{ $ini }}</span></div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border:1px solid #53386F;">
                                <tr>
                                    <td width="55%" style="vertical-align:top;padding:8px 12px;">
                                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                            @foreach ([
                                                ['Nombres', $alumno?->nombres],
                                                ['Apellidos', trim(($alumno?->apellido_paterno ?? '') . ' ' . ($alumno?->apellido_materno ?? ''))],
                                                ['DNI', $alumno?->dni],
                                                ['Carrera profesional', $carrera ? App\Http\Controllers\AlumnoController::carreraCorta($carrera->nombre) : null],
                                            ] as $campo)
                                            <tr>
                                                <td style="padding:4px 0;">
                                                    <div style="font-size:8px;color:#8a6a9b;text-transform:uppercase;letter-spacing:1px;">{{ $campo[0] }}</div>
                                                    <div style="font-size:13px;font-weight:600;color:#24192e;">{{ $campo[1] ?: '-' }}</div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </table>
                                    </td>
                                    <td width="45%" style="vertical-align:top;text-align:center;padding:8px 4px;border-left:1px solid #53386F;">
                                        @if ($qrImagen)
                                            <div style="text-align:center;">
                                                <div style="display:inline-block;border:1px solid #271549;padding:4px;background:#fff;border-radius:6px;">
                                                    <img src="{{ $qr }}" width="104" height="104" style="object-fit:contain;" alt="QR">
                                                </div>
                                                <div style="font-size:7px;color:#8a6a9b;text-transform:uppercase;letter-spacing:.5px;margin-top:2px;">Escanea para verificar</div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        @endif
        @if ($mostrarPie)
        <tr>
            <td height="40" style="height:40px;background:#271549;color:#fff;padding:3px 12px;vertical-align:middle;">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="vertical-align:middle;">
                            <div style="font-size:13px;font-weight:700;letter-spacing:.5px;">CARNET ESTUDIANTIL&nbsp;&nbsp;<span style="color:#D9A441;">N° {{ $alumno?->codigo_alumno ?: '------' }}</span></div>
                            <div style="font-size:6px;color:#c9b6d6;letter-spacing:.5px;text-transform:uppercase;margin-top:1px;">Documento institucional de identificación, de uso exclusivo del titular.</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        @endif
    </table>
</div>