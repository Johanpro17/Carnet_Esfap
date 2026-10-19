<?php

namespace Tests\Unit;

use Tests\TestCase;

class AlumnoTest extends TestCase
{
    public function test_full_name_omits_extra_spaces(): void
    {
        $alumno = [
            'nombres' => 'Ana',
            'apellido_paterno' => 'Lopez',
            'apellido_materno' => '',
        ];

        $this->assertSame('Ana Lopez', student_full_name($alumno));
    }
}
