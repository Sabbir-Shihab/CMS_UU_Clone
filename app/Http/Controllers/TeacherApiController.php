<?php

namespace App\Http\Controllers;

use App\Support\TeacherRepository;
use Illuminate\Http\JsonResponse;

class TeacherApiController extends Controller
{
    public function __construct(private TeacherRepository $teachers) {}

    public function index(string $department): JsonResponse
    {
        $dept = $this->teachers->departmentByKey($department);
        abort_unless($dept, 404);

        return response()->json([
            'data' => $this->teachers->toApiRows($this->teachers->byDepartmentKey($department)),
        ]);
    }

    public function office(int $code): JsonResponse
    {
        return response()->json([
            'data' => $this->teachers->toApiRows($this->teachers->byOfficeCode($code)),
        ]);
    }
}
