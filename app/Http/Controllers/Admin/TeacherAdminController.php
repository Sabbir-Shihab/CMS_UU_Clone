<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\TeacherRepository;
use Illuminate\Http\Request;

class TeacherAdminController extends Controller
{
    public function __construct(private TeacherRepository $teachers) {}

    public function index(Request $request)
    {
        $departments = $this->teachers->departments();
        $key = $request->query('department', $departments[0]['dept_key'] ?? 'cse');
        $dept = $this->teachers->departmentByKey($key) ?? $departments[0] ?? null;

        return view('admin.teachers.index', [
            'nav' => 'teachers',
            'departments' => $departments,
            'department' => $dept,
            'teachers' => $dept ? $this->teachers->byDepartmentKey($dept['dept_key']) : [],
            'activeTeachers' => true,
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.teachers.form', [
            'nav' => 'teachers',
            'departments' => $this->teachers->departments(),
            'teacher' => null,
            'profile' => $this->teachers->normalizeProfile([]),
            'tabDefinitions' => TeacherRepository::PROFILE_TABS,
            'selected' => $request->query('department'),
            'activeTeachers' => true,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['photo_path'] = $this->photoPath($request);
        $id = $this->teachers->create($data);
        $this->teachers->updateProfile(
            $id,
            json_encode($this->teachers->profileFromInput($request->all()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}'
        );

        return redirect()->route('admin.teachers.edit', $id)
            ->with('status', 'Teacher added. You can fill profile tabs below.');
    }

    public function edit(int $id)
    {
        $teacher = $this->teachers->find($id);
        abort_unless($teacher, 404);

        return view('admin.teachers.form', [
            'nav' => 'teachers',
            'departments' => $this->teachers->departments(),
            'teacher' => $teacher,
            'profile' => $this->teachers->decodeProfile($teacher),
            'tabDefinitions' => TeacherRepository::PROFILE_TABS,
            'selected' => $teacher['dept_key'],
            'activeTeachers' => true,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $teacher = $this->teachers->find($id);
        abort_unless($teacher, 404);
        $data = $this->validated($request);
        $data['photo_path'] = $this->photoPath($request, $teacher['photo_path'] ?? null);
        $this->teachers->update($id, $data);
        $this->teachers->updateProfile(
            $id,
            json_encode($this->teachers->profileFromInput($request->all()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}'
        );

        return redirect()->route('admin.teachers.edit', $id)
            ->with('status', 'Saved. Active tabs will show on the profile.');
    }

    public function destroy(int $id)
    {
        $teacher = $this->teachers->find($id);
        abort_unless($teacher, 404);
        $this->teachers->delete($id);

        return redirect()->route('admin.teachers.index', ['department' => $teacher['dept_key']])
            ->with('status', 'Teacher removed.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'department_id' => 'required|integer',
            'name' => 'required|string|max:180',
            'designation' => 'nullable|string|max:180',
            'institute1' => 'nullable|string|max:255',
            'institute2' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:180',
            'office_ext' => 'nullable|string|max:40',
            'profile_heading' => 'nullable|string|max:255',
            'profile_title' => 'nullable|string|max:255',
            'profile_roles' => 'nullable|string|max:2000',
            'profile_social' => 'nullable|string|max:2000',
            'tab' => 'nullable|array',
            'tab.*' => 'nullable|string',
            'tab_active' => 'nullable|array',
            'tab_active.*' => 'nullable',
        ]);
    }

    private function photoPath(Request $request, ?string $current = null): ?string
    {
        if (! $request->hasFile('photo')) {
            return $current;
        }
        $request->validate(['photo' => 'image|max:4096']);
        $file = $request->file('photo');
        $dir = public_path('uploads/teachers');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $name = 'manual_'.date('YmdHis').'_'.preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
        $file->move($dir, $name);

        return 'uploads/teachers/'.$name;
    }
}
