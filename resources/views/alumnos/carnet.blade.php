@extends('layouts.app')

@section('title', 'Carnet de ' . $alumno->nombre_completo)

@section('content')
    @php
        $esAdmin = Auth::user()->esAdmin();
        $carnetActual = $alumno->carnets->sortByDesc('fecha_emision')->first();
        $vigencia = $carnetActual?->vigenciaActual();
        $vigDate = $vigencia ? \Carbon\Carbon::parse($vigencia)->format('d/m/Y') : 'PENDIENTE';
        $foto = $alumno->foto_url;
        $ini = strtoupper(substr($alumno->nombres ?? '', 0, 1) . substr($alumno->apellido_paterno ?? '', 0, 1));
        $dotColor = [
            'ACTIVO' => '#4ade80',
            'SUSPENDIDO' => '#fbbf24',
            'INACTIVO' => '#94a3b8',
            'EGRESADO' => '#38bdf8',
            'RETIRADO' => '#f87171',
        ][$alumno->estado] ?? '#94a3b8';
    @endphp

    <style>
        .carnet-pagina {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .carnet-single {
            width: 100%;
            max-width: 540px;
            border-radius: 24px;
            background: #ffffff;
            border: 4px solid #5a0c70;
            box-shadow: 0 20px 60px rgba(90, 12, 112, 0.3);
            color: #5a0c70;
            padding: 1.6rem 1.8rem 1.4rem;
            position: relative;
            overflow: hidden;
        }

        .carnet-single::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(90, 12, 112, 0.05) 0%, transparent 70%);
            z-index: 0;
        }

        .carnet-single > * {
            position: relative;
            z-index: 1;
        }

        .carnet-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.1rem;
        }

        .carnet-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .carnet-logo .emoj {
            font-size: 1.75rem;
            line-height: 1;
        }

        .carnet-logo .txt {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 0.15rem;
        }

        .carnet-vigencia {
            text-align: right;
        }

        .carnet-vigencia .lab {
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            opacity: 0.9;
        }

        .carnet-vigencia .val {
            font-size: 0.85rem;
            font-weight: 700;
            margin-top: 0.25rem;
        }

        .carnet-foto {
            display: flex;
            justify-content: center;
            margin-bottom: 1.1rem;
        }

        .carnet-foto img,
        .carnet-foto-empty {
            width: 118px;
            height: 144px;
            object-fit: cover;
            border-radius: 14px;
            border: 3px solid #5a0c70;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .carnet-foto-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
            background: #faf5fd;
            color: #c9a7d8;
            font-size: 0.68rem;
            text-transform: uppercase;
        }

        .carnet-foto-empty .ini {
            font-size: 1.9rem;
            font-weight: 700;
            letter-spacing: 3px;
            color: #9d6fb3;
        }

        .carnet-medio {
            display: flex;
            justify-content: space-between;
            gap: 1.2rem;
        }

        .carnet-datos {
            flex: 1;
            min-width: 0;
        }

        .carnet-nombre {
            font-size: clamp(1.2rem, 3.5vw, 1.55rem);
            font-weight: 700;
            line-height: 1.15;
        }

        .carnet-apellidos {
            font-size: clamp(1rem, 2.6vw, 1.25rem);
            font-weight: 600;
            margin-bottom: 0.35rem;
        }

        .carnet-carrera {
            font-size: 0.85rem;
            font-weight: 600;
            opacity: 0.95;
            margin-bottom: 0.8rem;
            border-bottom: 1px solid rgba(90, 12, 112, 0.15);
            padding-bottom: 0.6rem;
        }

        .carnet-data-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.55rem;
            font-size: 0.84rem;
        }

        .carnet-data-label {
            opacity: 0.85;
        }

        .carnet-data-value {
            font-weight: 600;
            text-align: right;
        }

        .carnet-estado {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: #f3e7f9;
            border: 1px solid #e0ccee;
            border-radius: 999px;
            padding: 0.2rem 0.7rem;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .carnet-estado .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .carnet-qr-side {
            flex-shrink: 0;
            text-align: center;
        }

        .carnet-qr {
            width: 96px;
            height: 96px;
            display: block;
            background: #fff;
            border: 2px solid #5a0c70;
            border-radius: 10px;
            padding: 5px;
        }

        .carnet-qr img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .carnet-qr-label {
            font-size: 0.66rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.85;
            margin-top: 0.4rem;
        }

        @media (max-width: 576px) {
            .carnet-single {
                padding: 1.2rem;
                border-radius: 16px;
            }

            .carnet-medio {
                flex-direction: column;
            }

            .carnet-qr-side {
                order: -1;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.8rem;
                margin-bottom: 0.5rem;
            }

            .carnet-qr {
                width: 74px;
                height: 74px;
            }

            .carnet-qr-label {
                margin-top: 0;
                font-size: 0.62rem;
            }
        }
    </style>

    <div class="carnet-pagina">
        <div class="page-heading mb-3 flex-wrap justify-content-center text-center">
            <div>
                <span class="eyebrow"><i class="bi bi-credit-card-2-front me-2"></i>Carnet</span>
                <h1 class="mb-1">{{ $alumno->nombre_completo }}</h1>
                <p>Código: {{ $alumno->codigo_alumno }} · DNI: {{ $alumno->dni }}</p>
            </div>
        </div>

        @php
            $pdf = false;
        @endphp
        @if ($carnetActual)
            <div style="width:100%;max-width:640px;">
                @include('carnets.card', ['carnet' => $carnetActual])
            </div>
        @else
            <p class="text-muted">Este alumno no tiene un carnet generado todavía.</p>
        @endif
    </div>

    @if ($carnetActual)
        <div class="text-center mt-4">
            <a href="{{ route('carnet.pdf', $carnetActual->token_qr) }}" class="btn btn-outline-success px-4">
                <i class="bi bi-file-earmark-pdf me-2"></i>Ver carnet (PDF)
            </a>
        </div>
    @endif
@endsection