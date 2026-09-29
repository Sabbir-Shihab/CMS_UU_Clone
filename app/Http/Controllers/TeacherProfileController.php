<?php

namespace App\Http\Controllers;

use App\Support\TeacherRepository;

class TeacherProfileController extends Controller
{
    public function __construct(private TeacherRepository $teachers) {}

    public function show(int $id)
    {
        return $this->render($this->teachers->find($id));
    }

    public function showByKey(string $key)
    {
        return $this->render($this->teachers->findByEmpOrEncrypted($key));
    }

    private function render(?array $teacher)
    {
        abort_unless($teacher, 404);
        $profile = $this->teachers->decodeProfile($teacher);

        return view('site.teacher', [
            'teacher' => $teacher,
            'profile' => $profile,
        ]);
    }
}
