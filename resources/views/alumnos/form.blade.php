@extends('layouts.app')

@section('title', $alumno ? 'Editar carnet - ' . $alumno->nombre_completo : 'Crear carnet - Sistema de Carnets')

@section('content')
    @php
        $esAdmin = Auth::user()->esAdmin();
    @endphp
    <div class="page-heading mb-4 flex-wrap">
        <div>
            <span class="eyebrow"><i class="bi bi-{{ $alumno ? 'pencil-square' : 'person-plus-fill' }} me-2"></i>{{ $alumno ? 'Edición' : 'Registro' }}</span>
            <h1 class="mb-1">{{ $alumno ? 'Editar carnet' : 'Crear nuevo carnet' }}</h1>
            <p>{{ $alumno ? 'Actualiza los datos del estudiante y su carnet.' : 'Registra al estudiante, sube su foto y genera su carnet con QR.' }}</p>
        </div>
        <a href="{{ $esAdmin ? route('alumnos.index') : route('alumnos.carnet', $alumno) }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>Volver a la lista
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Corrige los siguientes errores:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="card form-card shadow-sm rounded-4">
                <div class="card-header text-white rounded-top-4">
                    <h5 class="mb-0"><i class="bi bi-credit-card-2-front me-2"></i>Datos del estudiante</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST"
                          action="{{ $alumno ? route('alumnos.update', $alumno) : route('alumnos.store') }}"
                          enctype="multipart/form-data">
                        @csrf
                        @if ($alumno)
                            @method('PUT')
                        @endif

                        @if ($alumno && !$esAdmin)
                            <div class="alert alert-light border d-flex align-items-center gap-2">
                                <i class="bi bi-lock fs-4 text-primary"></i>
                                <span>Los datos académicos son administrados por ESFAP. Aquí solo puedes actualizar tu DNI y teléfono.</span>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Nombres *</label>
                                @if ($esAdmin)
                                    <input type="text" class="form-control" name="nombres" required value="{{ old('nombres', $alumno?->nombres) }}">
                                @else
                                    <input type="text" class="form-control" value="{{ $alumno?->nombres }}" readonly disabled>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Apellido paterno *</label>
                                @if ($esAdmin)
                                    <input type="text" class="form-control" name="apellido_paterno" required value="{{ old('apellido_paterno', $alumno?->apellido_paterno) }}">
                                @else
                                    <input type="text" class="form-control" value="{{ $alumno?->apellido_paterno }}" readonly disabled>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Apellido materno</label>
                                @if ($esAdmin)
                                    <input type="text" class="form-control" name="apellido_materno" value="{{ old('apellido_materno', $alumno?->apellido_materno) }}">
                                @else
                                    <input type="text" class="form-control" value="{{ $alumno?->apellido_materno }}" readonly disabled>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">N° Carnet (código) *</label>
                                @if ($esAdmin)
                                    <input type="text" class="form-control" name="codigo_alumno" required value="{{ old('codigo_alumno', $alumno?->codigo_alumno ?? $proximoCodigo) }}">
                                    @if (!$alumno && $proximoCodigo)
                                        <div class="form-text">Siguiente código sugerido: {{ $proximoCodigo }}</div>
                                    @endif
                                @else
                                    <input type="text" class="form-control" value="{{ $alumno?->codigo_alumno }}" readonly disabled>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">DNI *</label>
                                <input type="text" class="form-control" name="dni" maxlength="8" inputmode="numeric"
                                       pattern="[0-9]{8}" required value="{{ old('dni', $alumno?->dni) }}" {{ $esAdmin ? '' : '' }}>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha de nacimiento *</label>
                                @if ($esAdmin)
                                    <input type="date" class="form-control" name="fecha_nacimiento" required value="{{ old('fecha_nacimiento', optional($alumno?->fecha_nacimiento)->format('Y-m-d')) }}">
                                @else
                                    <input type="text" class="form-control" value="{{ optional($alumno?->fecha_nacimiento)->format('d/m/Y') ?: '-' }}" readonly disabled>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Carrera *</label>
                                @if ($esAdmin)
                                    <select class="form-select" name="carrera_id" required>
                                        <option value="">Selecciona una carrera</option>
                                        @foreach ($carreras as $c)
                                            <option value="{{ $c->carrera_id }}"
                                                    @selected($c->carrera_id == old('carrera_id', $alumno?->carrera_id))>
                                                {{ App\Http\Controllers\AlumnoController::carreraCorta($c->nombre) }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" class="form-control" value="{{ App\Http\Controllers\AlumnoController::carreraCorta($alumno?->carrera->nombre ?? '-') }}" readonly disabled>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo</label>
                                @if ($esAdmin)
                                    <input type="email" class="form-control" name="correo" value="{{ old('correo', $alumno?->correo) }}">
                                @else
                                    <input type="text" class="form-control" value="{{ $alumno?->correo ?: '-' }}" readonly disabled>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" name="telefono" maxlength="9" inputmode="numeric"
                                       value="{{ old('telefono', $alumno?->telefono) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sexo</label>
                                @if ($esAdmin)
                                    <select class="form-select" name="sexo">
                                        <option value="">-</option>
                                        @foreach (['M' => 'Masculino', 'F' => 'Femenino', 'O' => 'Otro'] as $val => $label)
                                            <option value="{{ $val }}" @selected($val == old('sexo', $alumno?->sexo))>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" class="form-control" value="{{ $alumno?->sexo ?: '-' }}" readonly disabled>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado académico *</label>
                                @if ($esAdmin)
                                    <select class="form-select" name="estado" required>
                                        @foreach ($estados as $state)
                                            <option value="{{ $state }}" @selected($state == old('estado', $alumno?->estado ?? 'ACTIVO'))>{{ $state }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" class="form-control" value="{{ $alumno?->estado }}" readonly disabled>
                                @endif
                            </div>

                            @if (!$alumno)
                                <div class="col-12 bg-light rounded-3 p-3">
                                    <h6 class="text-uppercase small fw-bold text-muted mb-3">Datos del carnet</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Semestre actual *</label>
                                            <select class="form-select" name="semestre" required>
                                                <option value="1" @selected((int) old('semestre', $semestreActual) === 1)>I</option>
                                                <option value="2" @selected((int) old('semestre', $semestreActual) === 2)>II</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Periodo académico *</label>
                                            <input type="number" class="form-control" name="periodo" min="2000" max="2100" required
                                                   value="{{ old('periodo', $periodoActual) }}" placeholder="Ej. 2026">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Vigencia del carnet *</label>
                                            <input type="date" class="form-control" name="fecha_vigencia" required
                                                   value="{{ old('fecha_vigencia', $fechaVigenciaDefault) }}">
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-12">
                                <label class="form-label">Foto del alumno {{ $alumno ? '' : '*' }} (JPG/PNG/GIF, máx 5 MB)</label>
                                @if ($esAdmin)
                                    <input type="file" class="form-control" name="foto" accept="image/*" {{ $alumno ? '' : 'required' }}>
                                    @if ($alumno)
                                        <div class="form-text">Dejar vacío para conservar la foto. Nueva foto reemplazará la actual.</div>
                                    @endif
                                @else
                                    <div class="form-text">Solo el administrador puede cambiar la foto.</div>
                                @endif
                                @if ($alumno?->foto_url)
                                    <div class="mt-2">
                                        <img src="{{ $alumno->foto_url }}" width="64" class="rounded-2" style="object-fit:cover;height:72px" alt="Foto actual">
                                    </div>
                                @elseif ($alumno)
                                    <div class="text-muted small mt-1">Sin foto.</div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn {{ $alumno ? 'btn-primary' : 'btn-success' }} btn-lg">
                                <i class="bi bi-{{ $alumno ? 'check-circle' : 'lightning-charge-fill' }} me-2"></i>
                                {{ $alumno ? 'Actualizar carnet' : 'Generar carnet' }}
                            </button>
                            <a href="{{ $esAdmin ? route('alumnos.index') : route('alumnos.carnet', $alumno) }}" class="btn btn-secondary btn-lg ms-2">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection