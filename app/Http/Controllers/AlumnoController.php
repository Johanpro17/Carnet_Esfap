<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Carnet;
use App\Models\CarnetVencimiento;
use App\Models\Carrera;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AlumnoController extends Controller
{
    public const ESTADOS = ['ACTIVO', 'SUSPENDIDO', 'INACTIVO', 'EGRESADO', 'RETIRADO'];

    public function index(Request $request)
    {
        $search = trim($request->query('search', ''));
        $carreraId = $request->query('carrera');
        $estado = $request->query('estado');
        $proximos = $request->boolean('proximos');

        $query = Alumno::query()
            ->with('carrera', 'carnets.vencimientos')
            ->leftJoin('carnets as c_ult', function ($join) {
                $join->on('c_ult.alumno_id', '=', 'alumnos.alumno_id')
                    ->whereRaw('c_ult.fecha_emision = (SELECT MAX(fecha_emision) FROM carnets WHERE alumno_id = alumnos.alumno_id)');
            })
            ->select('alumnos.*');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('alumnos.nombres', 'like', "%{$search}%")
                    ->orWhere('alumnos.apellido_paterno', 'like', "%{$search}%")
                    ->orWhere('alumnos.apellido_materno', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(alumnos.nombres, ' ', alumnos.apellido_paterno, ' ', alumnos.apellido_materno) LIKE ?", ["%{$search}%"])
                    ->orWhere('alumnos.dni', 'like', "%{$search}%")
                    ->orWhere('alumnos.codigo_alumno', 'like', "%{$search}%");
            });
        }

        if ($carreraId) {
            $query->where('alumnos.carrera_id', $carreraId);
        }

        if ($estado) {
            $query->where('alumnos.estado', $estado);
        }

        if ($proximos) {
            $hoy = now()->toDateString();
            $limite = now()->addDays(30)->toDateString();
            $query->whereHas('carnets.vencimientos', function ($q) use ($hoy, $limite) {
                $q->whereBetween('fecha_vigencia', [$hoy, $limite]);
            });
        }

        $alumnos = $query
            ->orderBy('alumnos.carrera_id')
            ->orderBy('alumnos.apellido_paterno')
            ->orderBy('alumnos.nombres')
            ->paginate(50)
            ->withQueryString();

        $carreras = Carrera::orderBy('nombre')->get();
        $estados = self::ESTADOS;

        return view('alumnos.index', compact('alumnos', 'carreras', 'estados', 'search', 'carreraId', 'estado', 'proximos'));
    }

    public function create()
    {
        $carreras = Carrera::where('estado', 1)->orderBy('nombre')->get();
        $proximoCodigo = (int) DB::table('alumnos')
            ->whereRaw('codigo_alumno REGEXP "^[0-9]+$"')
            ->max(DB::raw('CAST(codigo_alumno AS UNSIGNED)')) + 1;

        [$periodo, $semestre] = $this->periodoActual();

        return view('alumnos.form', [
            'alumno' => null,
            'carreras' => $carreras,
            'estados' => self::ESTADOS,
            'proximoCodigo' => $proximoCodigo,
            'periodoActual' => $periodo,
            'semestreActual' => $semestre,
            'fechaVigenciaDefault' => now()->addYear()->toDateString(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validarDatos($request, null);
        $foto = $this->guardarFoto($request, $data['codigo_alumno'], null);

        try {
            DB::beginTransaction();

            $alumno = Alumno::create([
                'carrera_id' => $data['carrera_id'],
                'codigo_alumno' => $data['codigo_alumno'],
                'dni' => $data['dni'],
                'nombres' => $data['nombres'],
                'apellido_paterno' => $data['apellido_paterno'],
                'apellido_materno' => $data['apellido_materno'] ?? '',
                'fecha_nacimiento' => $data['fecha_nacimiento'],
                'sexo' => $data['sexo'] ?? null,
                'telefono' => $data['telefono'] ?? null,
                'correo' => $data['correo'] ?? null,
                'direccion' => $data['direccion'] ?? null,
                'foto' => $foto,
                'estado' => $data['estado'],
                'fecha_registro' => now(),
            ]);

            $carnet = Carnet::create([
                'alumno_id' => $alumno->alumno_id,
                'codigo' => $data['codigo_alumno'],
                'token_qr' => bin2hex(random_bytes(20)),
                'periodo' => $data['periodo'],
                'semestre' => $data['semestre'],
                'estado' => 'ACTIVO',
                'fecha_emision' => now(),
            ]);

            CarnetVencimiento::create([
                'carnet_id' => $carnet->carnet_id,
                'fecha_vigencia' => $data['fecha_vigencia'],
                'fecha_emision' => now()->toDateString(),
                'fecha_registro' => now(),
            ]);

            AuditoriaService::registrar('CREAR_ALUMNO', 'alumnos', $alumno->alumno_id, 'Registro del alumno ' . $alumno->getNombreCompletoAttribute());
            AuditoriaService::registrar('GENERAR_CARNET', 'carnets', $carnet->carnet_id, 'Carnet ' . $carnet->codigo . ' emitido');

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($foto) {
                @unlink(public_path('uploads/' . $foto));
            }
            throw $e;
        }

        return redirect()->route('alumnos.carnet', $alumno)
            ->with('status', 'Alumno registrado y carnet generado correctamente.');
    }

    public function edit(Alumno $alumno)
    {
        $carreras = Carrera::where('estado', 1)->orderBy('nombre')->get();

        return view('alumnos.form', [
            'alumno' => $alumno,
            'carreras' => $carreras,
            'estados' => self::ESTADOS,
            'proximoCodigo' => null,
            'periodoActual' => null,
            'semestreActual' => null,
            'fechaVigenciaDefault' => null,
        ]);
    }

    public function update(Request $request, Alumno $alumno)
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();
        $parcial = !$usuario->esAdmin();

        if ($parcial) {
            $data = $this->validarDatosEstudiante($request, $alumno);
            $alumno->update([
                'dni' => $data['dni'],
                'telefono' => $data['telefono'] ?? null,
            ]);
            AuditoriaService::registrar('EDITAR_ALUMNO', 'alumnos', $alumno->alumno_id, 'Alumno actualizó su DNI/teléfono');
            return redirect()->route('alumnos.carnet', $alumno)
                ->with('status', 'Tus datos fueron actualizados correctamente.');
        }

        $data = $this->validarDatos($request, $alumno, false);
        $foto = $this->guardarFoto($request, $alumno->codigo_alumno, $alumno);

        $alumno->update([
            'codigo_alumno' => $data['codigo_alumno'],
            'dni' => $data['dni'],
            'nombres' => $data['nombres'],
            'apellido_paterno' => $data['apellido_paterno'],
            'apellido_materno' => $data['apellido_materno'] ?? '',
            'carrera_id' => $data['carrera_id'],
            'estado' => $data['estado'],
            'fecha_nacimiento' => $data['fecha_nacimiento'],
            'sexo' => $data['sexo'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'correo' => $data['correo'] ?? null,
            'direccion' => $data['direccion'] ?? $alumno->direccion,
            'foto' => $foto,
        ]);

        AuditoriaService::registrar('EDITAR_ALUMNO', 'alumnos', $alumno->alumno_id, 'Alumno actualizado');

        return redirect()->route('alumnos.index')
            ->with('status', 'Alumno actualizado correctamente.');
    }

    public function destroy(Alumno $alumno)
    {
        AuditoriaService::registrar('ELIMINAR_ALUMNO', 'alumnos', $alumno->alumno_id, 'Eliminado ' . $alumno->getNombreCompletoAttribute());
        $alumno->delete();

        return redirect()->route('alumnos.index')
            ->with('status', 'Alumno eliminado correctamente.');
    }

    public function carnet(Alumno $alumno)
    {
        $alumno->load(['carrera', 'carnets.vencimientos']);

        return view('alumnos.carnet', compact('alumno'));
    }

    public function renovarCarnet(Request $request, Alumno $alumno)
    {
        $request->validate([
            'fecha_vigencia' => ['required', 'date'],
        ]);

        $carnet = $alumno->carnets()->orderByDesc('fecha_emision')->first();
        abort_unless($carnet, 422, 'El alumno no tiene carnets registrados.');

        [$periodo, $semestre] = $this->periodoActual();

        DB::beginTransaction();

        try {
            CarnetVencimiento::create([
                'carnet_id' => $carnet->carnet_id,
                'fecha_vigencia' => $request->input('fecha_vigencia'),
                'fecha_emision' => now()->toDateString(),
                'fecha_registro' => now(),
            ]);

            $carnet->update([
                'periodo' => $periodo,
                'semestre' => $semestre,
                'estado' => 'ACTIVO',
                'fecha_emision' => now(),
            ]);

            AuditoriaService::registrar('RENOVAR_CARNET', 'carnets', $carnet->carnet_id, 'Vigencia renovada hasta ' . $request->input('fecha_vigencia'));

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $vigencia = \Carbon\Carbon::parse($request->input('fecha_vigencia'))->format('d/m/Y');

        return redirect()->route('alumnos.carnet', $alumno)
            ->with('status', 'Carnet renovado, nueva vigencia: ' . $vigencia . '.');
    }

    public static function carreraCorta(?string $carrera): string
    {
        $nombre = preg_replace('/\s+/', ' ', trim((string) $carrera));

        return match ($nombre) {
            'Artistica Profesional' => 'Artística Profesional',
            'Profesor de Educacion Artistica-Musica' => 'Artística Música',
            'Profesor de Educacion de Artes Visuales' => 'Artes Visuales',
            default => $nombre,
        };
    }

    public static function vigenciaDe(Alumno $alumno): ?string
    {
        $carnet = $alumno->carnets->sortByDesc('fecha_emision')->first();
        return $carnet?->vigenciaActual();
    }

    private function periodoActual(): array
    {
        $mes = (int) now()->format('m');
        return [now()->format('Y'), $mes >= 8 ? 2 : 1];
    }

    private function validarDatos(Request $request, ?Alumno $alumno): array
    {
        $alumnoId = $alumno?->alumno_id;

        $rules = [
            'codigo_alumno' => ['required', 'string', 'max:30', Rule::unique('alumnos', 'codigo_alumno')->ignore($alumnoId, 'alumno_id')],
            'dni' => ['required', 'digits:8', Rule::unique('alumnos', 'dni')->ignore($alumnoId, 'alumno_id')],
            'nombres' => ['required', 'string', 'max:80'],
            'apellido_paterno' => ['required', 'string', 'max:60'],
            'apellido_materno' => ['nullable', 'string', 'max:60'],
            'carrera_id' => ['required', 'integer', 'exists:carreras,carrera_id'],
            'estado' => ['required', 'in:' . implode(',', self::ESTADOS)],
            'fecha_nacimiento' => ['required', 'date'],
            'sexo' => ['nullable', 'in:M,F,O'],
            'telefono' => ['nullable', 'digits:9'],
            'correo' => ['nullable', 'email', 'max:150', Rule::unique('alumnos', 'correo')->ignore($alumnoId, 'alumno_id')],
            'direccion' => ['nullable', 'string', 'max:200'],
        ];

        if ($alumno === null) {
            $rules['periodo'] = ['required', 'integer', 'min:2000', 'max:2100'];
            $rules['semestre'] = ['required', 'integer', 'in:1,2'];
            $rules['fecha_vigencia'] = ['required', 'date'];
        }

        return $request->validate($rules, [
            'dni.unique' => 'El DNI ya está registrado.',
            'codigo_alumno.unique' => 'El código de alumno ya está registrado.',
            'correo.unique' => 'El correo ya está registrado.',
            'dni.digits' => 'El DNI debe tener 8 dígitos.',
            'telefono.digits' => 'El teléfono debe tener 9 dígitos.',
        ]);
    }

    private function validarDatosEstudiante(Request $request, Alumno $alumno): array
    {
        return $request->validate([
            'dni' => ['required', 'digits:8', Rule::unique('alumnos', 'dni')->ignore($alumno->alumno_id, 'alumno_id')],
            'telefono' => ['nullable', 'digits:9'],
        ], [
            'dni.unique' => 'El DNI ya está registrado.',
            'dni.digits' => 'El DNI debe tener 8 dígitos.',
            'telefono.digits' => 'El teléfono debe tener 9 dígitos.',
        ]);
    }

    private function guardarFoto(Request $request, string $codigo, ?Alumno $alumno): ?string
    {
        if (!$request->hasFile('foto')) {
            return $alumno?->foto;
        }

        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,gif', 'max:5120'],
        ]);

        $file = $request->file('foto');
        $extension = strtolower($file->getClientOriginalExtension() ?: 'png');
        $nombre = preg_replace('/[^A-Za-z0-9]+/', '_', $codigo) . '_' . time() . '.' . $extension;

        $dir = public_path('uploads');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $file->move($dir, $nombre);

        if ($alumno && $alumno->foto && $alumno->foto !== $nombre) {
            @unlink(public_path('uploads/' . $alumno->foto));
        }

        return $nombre;
    }
}
