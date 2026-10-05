@extends('layouts.publica')

@section('title', 'Verificación de carnet')

@section('content')
    @php
        $vigencia = $carnet->vigenciaActual();
        $vencido = $vigencia && \Carbon\Carbon::parse($vigencia)->isBefore(today());
    @endphp

    <div class="text-center mb-4">
        @if ($vencido)
            <div class="alert alert-danger d-inline-flex align-items-center gap-2 px-4 mb-1">
                <i class="bi bi-x-octagon-fill"></i>
                <div class="text-start">
                    <div class="fw-bold">CARNET VENCIDO</div>
                    <small>La vigencia del documento concluyó el {{ \Carbon\Carbon::parse($vigencia)->format('d/m/Y') }}.</small>
                </div>
            </div>
        @else
            <div class="alert alert-success d-inline-flex align-items-center gap-2 px-4 mb-1">
                <i class="bi bi-patch-check-fill"></i>
                <div class="text-start">
                    <div class="fw-bold">CARNET VÁLIDO</div>
                    <small>El documento corresponde a un estudiante registrado en la ESFAP.</small>
                </div>
            </div>
        @endif
        <div class="small text-muted">Registro oficial verificado por el sistema institucional.</div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('verificar') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Verificar otro QR
        </a>
        <a href="{{ route('carnet.pdf', $carnet->token_qr) }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-file-earmark-pdf me-1"></i>Ver carnet (PDF)
        </a>
    </div>

    @include('carnets.card', ['pdf' => false, 'qrSrc' => url('/qr/' . $carnet->token_qr)])

    <div class="d-flex justify-content-center gap-2 mt-4">
        <a href="{{ route('verificar') }}" class="btn btn-primary">
            <i class="bi bi-qr-code-scan me-1"></i>Verificar otro QR
        </a>
        <a href="{{ route('carnet.pdf', $carnet->token_qr) }}" class="btn btn-outline-success">
            <i class="bi bi-download me-1"></i>Descargar PDF
        </a>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i>Imprimir
        </button>
    </div>
@endsection