@extends('layouts.app')

@section('title', 'Lista completa de alumnos')

@section('content')
    <div class="page-heading mb-4 flex-wrap">
        <div>
            <span class="eyebrow"><i class="bi bi-people-fill me-2"></i>Estudiantes</span>
            <h1>Lista completa de alumnos</h1>
            <p>Gestión de estudiantes, carnets y estados académicos.</p>
        </div>
        <a href="{{ route('alumnos.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Nuevo carnet</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('status') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="GET" action="{{ route('alumnos.index') }}" class="mb-3">
        <div class="row g-2 align-items-center">
            <div class="col-lg-5">
                <div class="input-group">
                    <input type="text" name="search" value="{{ $search }}" class="form-control"
                           placeholder="Escribe y los resultados aparecen al instante...">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Buscar</button>
                </div>
            </div>
            @if ($carreraId)
                <input type="hidden" name="carrera" value="{{ $carreraId }}">
            @endif
            @if ($estado)
                <input type="hidden" name="estado" value="{{ $estado }}">
            @endif
            @if ($proximos)
                <input type="hidden" name="proximos" value="1">
            @endif
        </div>
    </form>

    <div class="btn-group mb-2 flex-wrap gap-1" role="group" aria-label="Filtrar por carrera">
        @foreach ($carreras as $c)
            <button type="button"
                    class="btn btn-sm {{ (string) $carreraId === (string) $c->carrera_id ? 'btn-primary' : 'btn-outline-secondary' }}"
                    onclick="location.href='{{ route('alumnos.index', array_filter(['search' => $search, 'carrera' => $c->carrera_id, 'estado' => $estado ?: null, 'proximos' => $proximos ? 1 : null])) }}'">
                {{ App\Http\Controllers\AlumnoController::carreraCorta($c->nombre) }}
            </button>
        @endforeach
        @if ($carreraId)
            <button type="button" class="btn btn-sm btn-outline-dark"
                    onclick="location.href='{{ route('alumnos.index', array_filter(['search' => $search, 'estado' => $estado ?: null, 'proximos' => $proximos ? 1 : null])) }}'">
                Limpiar
            </button>
        @endif
    </div>

    <div class="btn-group mb-3 flex-wrap gap-1" role="group" aria-label="Filtrar por estado">
        @foreach ($estados as $e)
            <button type="button"
                    class="btn btn-sm {{ (string) $estado === $e ? 'btn-primary' : 'btn-outline-secondary' }}"
                    onclick="location.href='{{ route('alumnos.index', array_filter(['search' => $search, 'carrera' => $carreraId, 'estado' => $e])) }}'">
                {{ $e }}
            </button>
        @endforeach
        <button type="button"
                class="btn btn-sm {{ $proximos ? 'btn-warning' : 'btn-outline-warning' }}"
                onclick="location.href='{{ route('alumnos.index', array_filter(['search' => $search, 'carrera' => $carreraId, 'proximos' => 1])) }}'">
            <i class="bi bi-hourglass-split me-1"></i>Por vencer (30 días)
        </button>
        @if ($estado || $proximos)
            <button type="button" class="btn btn-sm btn-outline-dark"
                    onclick="location.href='{{ route('alumnos.index', array_filter(['search' => $search, 'carrera' => $carreraId])) }}'">
                Limpiar estado
            </button>
        @endif
    </div>

    @if ($estado || $proximos)
        <div class="alert alert-info py-2 px-3 d-flex align-items-center gap-2 mb-4" style="font-size:.85rem">
            <i class="bi bi-funnel-fill"></i>
            <span class="fw-semibold" style="color:#3d0650">
                @if ($proximos)
                    Mostrando alumnos con carnet por vencer en los próximos 30 días.
                @else
                    Mostrando solo alumnos con estado <span class="status-badge {{ strtolower($estado) }} mx-1">{{ $estado }}</span>.
                @endif
            </span>
            <a href="{{ route('alumnos.index', array_filter(['search' => $search, 'carrera' => $carreraId])) }}"
               class="ms-auto btn btn-sm btn-outline-secondary">
                <i class="bi bi-x-lg me-1"></i>Quitar filtro
            </a>
        </div>
    @endif

    <div class="card section-card">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-lg align-middle bg-white">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>DNI</th>
                        <th>Carrera</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Foto</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($alumnos as $alumno)
                        @php
                            $carnet = $alumno->carnets->sortByDesc('fecha_emision')->first();
                            $token = $carnet?->token_qr;
                        @endphp
                        <tr>
                            <td>{{ $alumno->alumno_id }}</td>
                            <td>{{ $alumno->nombres }}</td>
                            <td>{{ trim($alumno->apellido_paterno . ' ' . $alumno->apellido_materno) }}</td>
                            <td>{{ $alumno->dni }}</td>
                            <td>{{ App\Http\Controllers\AlumnoController::carreraCorta($alumno->carrera->nombre ?? '-') }}</td>
                            <td>{{ $alumno->correo ?: '-' }}</td>
                            <td>{{ $alumno->telefono ?: '-' }}</td>
                            <td>
                                @if ($alumno->foto_url)
                                    <img src="{{ $alumno->foto_url }}" width="60" class="rounded" style="object-fit:cover;height:60px" alt="Foto">
                                @else
                                    <span class="text-muted small">Sin foto</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="status-badge {{ strtolower($alumno->estado ?? 'ACTIVO') }}">{{ $alumno->estado ?? 'ACTIVO' }}</span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('alumnos.carnet', $alumno) }}" class="btn btn-outline-primary" title="Ver carnet">
                                        <i class="bi bi-credit-card-2-front"></i>
                                    </a>
                                    <a href="{{ route('alumnos.edit', $alumno) }}" class="btn btn-warning" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if ($token)
                                        <a href="{{ route('carnet.pdf', $token) }}" class="btn btn-primary" title="PDF">
                                            <i class="bi bi-file-pdf"></i>
                                        </a>
                                    @endif
                                    <form method="POST" action="{{ route('alumnos.destroy', $alumno) }}" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar este alumno? Esta acción no se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">No se encontraron alumnos con esos filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($alumnos->hasPages())
        <nav aria-label="Paginación" class="mt-4">
            <ul class="pagination justify-content-center gap-1">
                <li class="page-item {{ $alumnos->currentPage() <= 1 ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $alumnos->previousPageUrl() }}"><i class="bi bi-chevron-left me-1"></i>Anterior</a>
                </li>
                <li class="page-item disabled"><span class="page-link text-muted">Página {{ $alumnos->currentPage() }} de {{ $alumnos->lastPage() }} ({{ $alumnos->total() }} alumnos)</span></li>
                <li class="page-item {{ $alumnos->currentPage() >= $alumnos->lastPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $alumnos->nextPageUrl() }}">Siguiente<i class="bi bi-chevron-right ms-1"></i></a>
                </li>
            </ul>
        </nav>
    @else
        <p class="text-center text-muted mt-3">{{ $alumnos->total() }} alumno(s)</p>
    @endif
@endsection