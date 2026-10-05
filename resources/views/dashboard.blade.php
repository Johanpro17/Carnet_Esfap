@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-heading mb-4 flex-wrap">
        <div>
            <span class="eyebrow"><i class="bi bi-grid-1x2-fill me-2"></i>Administración</span>
            <h1>Panel de gestión de carnets</h1>
            <p>Control académico, vigencias y emisión de credenciales estudiantiles.</p>
        </div>
        <a href="{{ route('alumnos.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Nuevo carnet</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card metric-card metric-primary h-100">
                <div class="card-body">
                    <span class="metric-icon"><i class="bi bi-people-fill"></i></span>
                    <div>
                        <span>Total estudiantes</span>
                        <h2>{{ $totalAlumnos }}</h2>
                        <small>Alumnos registrados</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card metric-card metric-success h-100">
                <div class="card-body">
                    <span class="metric-icon"><i class="bi bi-person-check-fill"></i></span>
                    <div>
                        <span>Activos</span>
                        <h2>{{ $estados['ACTIVO'] }}</h2>
                        <small>Con carnet vigente</small>
                    </div>
                    <div class="metric-report">
                        <a href="{{ route('alumnos.index', ['estado' => 'ACTIVO']) }}" class="btn btn-outline-success">
                            <i class="bi bi-file-earmark-text me-1"></i>Reporte
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card metric-card metric-warning h-100">
                <div class="card-body">
                    <span class="metric-icon"><i class="bi bi-hourglass-split"></i></span>
                    <div>
                        <span>Por vencer (30 días)</span>
                        <h2>{{ $porVencer }}</h2>
                        <small>Vigencia próxima a expirar</small>
                    </div>
                    <div class="metric-report">
                        <a href="{{ route('alumnos.index', ['proximos' => 1]) }}" class="btn btn-outline-warning">
                            <i class="bi bi-file-earmark-text me-1"></i>Reporte
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card metric-card metric-info h-100">
                <div class="card-body">
                    <span class="metric-icon"><i class="bi bi-mortarboard-fill"></i></span>
                    <div>
                        <span>Egresados</span>
                        <h2>{{ $estados['EGRESADO'] }}</h2>
                        <small>Con situación egresado</small>
                    </div>
                    <div class="metric-report">
                        <a href="{{ route('alumnos.index', ['estado' => 'EGRESADO']) }}" class="btn btn-outline-info">
                            <i class="bi bi-file-earmark-text me-1"></i>Reporte
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card metric-card metric-danger h-100">
                <div class="card-body">
                    <span class="metric-icon"><i class="bi bi-person-x-fill"></i></span>
                    <div>
                        <span>Suspendidos</span>
                        <h2>{{ $estados['SUSPENDIDO'] }}</h2>
                        <small>Con carnet suspendido</small>
                    </div>
                    <div class="metric-report">
                        <a href="{{ route('alumnos.index', ['estado' => 'SUSPENDIDO']) }}" class="btn btn-outline-danger">
                            <i class="bi bi-file-earmark-text me-1"></i>Reporte
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card metric-card metric-warning h-100">
                <div class="card-body">
                    <span class="metric-icon"><i class="bi bi-person-dash-fill"></i></span>
                    <div>
                        <span>Inactivos</span>
                        <h2>{{ $estados['INACTIVO'] }}</h2>
                        <small>Sin actividad académica</small>
                    </div>
                    <div class="metric-report">
                        <a href="{{ route('alumnos.index', ['estado' => 'INACTIVO']) }}" class="btn btn-outline-warning">
                            <i class="bi bi-file-earmark-text me-1"></i>Reporte
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card metric-card metric-primary h-100">
                <div class="card-body">
                    <span class="metric-icon"><i class="bi bi-card-checklist"></i></span>
                    <div>
                        <span>Carnets emitidos</span>
                        <h2>{{ $totalCarnets }}</h2>
                        <small>Total de carnets</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card section-card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-mortarboard me-2"></i>Estudiantes por carrera</h5>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($porCarrera as $c)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">{{ App\Http\Controllers\AlumnoController::carreraCorta($c->nombre) }}</span>
                            </div>
                            <span class="badge text-bg-secondary">{{ $c->total }}</span>
                        </div>
                    @empty
                        <div class="list-group-item text-muted">Sin datos.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card section-card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pie-chart-fill me-2"></i>Situación académica</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach ($estados as $estado => $total)
                            <div class="col-6">
                                <div class="estado-box p-3 rounded-3 border d-flex flex-column" style="border-color:rgba(90,12,112,.12)!important">
                                    <span class="status-badge {{ $estado }} align-self-start mb-1">{{ $estado }}</span>
                                    <span class="h3 mb-0" style="color:#3d0650">{{ $total }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card section-card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Registros recientes</h5>
        </div>
        <div class="list-group list-group-flush">
            @forelse ($recientes as $a)
                <a href="{{ route('alumnos.carnet', $a) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-semibold">{{ $a->nombre_completo }}</span>
                        <br>
                        <small class="text-muted">{{ App\Http\Controllers\AlumnoController::carreraCorta($a->carrera->nombre ?? '-') }}</small>
                    </div>
                    <small class="text-muted">{{ $a->fecha_registro?->format('d/m/Y H:i') }}</small>
                </a>
            @empty
                <div class="list-group-item text-muted">Sin registros.</div>
            @endforelse
        </div>
    </div>
@endsection