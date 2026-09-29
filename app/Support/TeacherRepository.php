<?php

namespace App\Support;

class TeacherRepository
{
    public const SKIP = ['120046', '250069', '170604'];

    /** @var array<string, string> */
    public const PROFILE_TABS = [
        'educational' => 'Education',
        'publications' => 'Publications',
        'experience' => 'Experience',
        'bio' => 'Short Bio',
        'awards' => 'Awards & Honors',
        'membership' => 'Membership',
        'research' => 'Research Interest',
        'course' => 'Course Taught',
        'links' => 'Links',
    ];

    public const DEPARTMENTS = [
        ['key' => 'bangla', 'code' => 5, 'name' => 'Department of Bangla', 'slug' => 'department-of-bengali'],
        ['key' => 'bba', 'code' => 4, 'name' => 'Department of Business Administration', 'slug' => 'department-of-business-administration'],
        ['key' => 'civil', 'code' => 6, 'name' => 'Department of Civil Engineering', 'slug' => 'department-of-civil-engineering'],
        ['key' => 'cse', 'code' => 8, 'name' => 'Department of Computer Science and Engineering', 'slug' => 'department-of-cse'],
        ['key' => 'education', 'code' => 9, 'name' => 'Department of Education', 'slug' => 'department-of-education'],
        ['key' => 'eee', 'code' => 10, 'name' => 'Department of Electrical and Electronic Engineering', 'slug' => 'department-of-eee'],
        ['key' => 'english', 'code' => 11, 'name' => 'Department of English', 'slug' => 'department-of-english'],
        ['key' => 'fashion', 'code' => 13, 'name' => 'Department of Fashion Design and Technology', 'slug' => 'department-of-fashion-design'],
        ['key' => 'islamic', 'code' => 14, 'name' => 'Department of Islamic Studies', 'slug' => 'department-of-islamic-studies'],
        ['key' => 'law', 'code' => 16, 'name' => 'Department of Law', 'slug' => 'department-of-law'],
        ['key' => 'math', 'code' => 18, 'name' => 'Department of Mathematics', 'slug' => 'department-of-mathematics'],
        ['key' => 'physics', 'code' => 20, 'name' => 'Department of Physics', 'slug' => 'department-of-physics'],
        ['key' => 'pe', 'code' => 19, 'name' => 'Department of Physical Education', 'slug' => 'department-of-physical-education'],
        ['key' => 'textile', 'code' => 23, 'name' => 'Department of Textile Engineering', 'slug' => 'department-of-textile-engineering'],
    ];

    public const OFFICES = [
        ['key' => 'office-admin', 'code' => 47, 'name' => 'Advisory Board', 'slug' => 'administration'],
        ['key' => 'office-founder', 'code' => 26, 'name' => 'Office of the Founder', 'slug' => 'office-of-the-founder'],
        ['key' => 'office-vc', 'code' => 25, 'name' => 'Office of the Vice Chancellor', 'slug' => 'office-of-the-vice-chancellor'],
        ['key' => 'office-treasurer', 'code' => 24, 'name' => 'Office of the Treasurer', 'slug' => 'office-of-the-treasurer'],
        ['key' => 'office-registrar', 'code' => 3, 'name' => 'Office of the Registrar', 'slug' => 'office-of-the-registrar'],
        ['key' => 'office-coe', 'code' => 7, 'name' => 'Office of the Controller of Examinations', 'slug' => 'office-of-the-controller-of-examinations'],
        ['key' => 'office-proctor', 'code' => 28, 'name' => 'Office of the Proctor', 'slug' => 'office-of-the-proctor'],
        ['key' => 'office-finance', 'code' => 2, 'name' => 'Office of Finance and Accounts', 'slug' => 'office-of-finance-and-accounts'],
        ['key' => 'office-it', 'code' => 27, 'name' => 'Office of IT', 'slug' => 'office-of-it'],
        ['key' => 'office-pr', 'code' => 29, 'name' => 'Office of Public Relations', 'slug' => 'office-of-public-relations'],
        ['key' => 'office-hr', 'code' => 32, 'name' => 'Faculty & HR Office', 'slug' => 'faculty-hr-office'],
        ['key' => 'office-external', 'code' => 33, 'name' => 'Office of External Affairs', 'slug' => 'office-of-external-affairs'],
        ['key' => 'office-oia', 'code' => 34, 'name' => 'Office of International Affairs', 'slug' => 'office-of-international-affairs'],
        ['key' => 'office-osa', 'code' => 35, 'name' => 'Office of Student Affairs', 'slug' => 'office-of-student-affairs'],
        ['key' => 'office-brand', 'code' => 36, 'name' => 'Office of Brand & Communication', 'slug' => 'office-of-brand-communication'],
        ['key' => 'office-medical', 'code' => 37, 'name' => 'Medical & Emergency Center', 'slug' => 'medical-emergency-center'],
        ['key' => 'office-logistics', 'code' => 38, 'name' => 'Office of Logistics & Procurement', 'slug' => 'office-of-logistics-procurement'],
        ['key' => 'office-ssc', 'code' => 39, 'name' => 'Office of Student Services & Counselling', 'slug' => 'office-of-student-services-counselling'],
        ['key' => 'office-ia', 'code' => 40, 'name' => 'Office of Internal Affairs', 'slug' => 'office-of-internal-affairs'],
        ['key' => 'office-audit', 'code' => 41, 'name' => 'Office of Internal Audit', 'slug' => 'office-of-internal-audit'],
        ['key' => 'office-legal', 'code' => 42, 'name' => 'Office of Compliance & Legal Affairs', 'slug' => 'office-of-compliance-legal-affairs'],
        ['key' => 'office-pd', 'code' => 43, 'name' => 'Office of the Project Director', 'slug' => 'office-of-the-project-director'],
        ['key' => 'office-transport', 'code' => 44, 'name' => 'Office of Transport', 'slug' => 'office-of-transport'],
        ['key' => 'office-irc', 'code' => 52, 'name' => 'Institutional Ranking Cell', 'slug' => 'institutional-ranking-cell'],
    ];

    public function __construct(private SqliteStore $db) {}

    public function boot(): void
    {
        $this->db->ensure();
        foreach (array_merge(self::DEPARTMENTS, self::OFFICES) as $dept) {
            $existing = $this->db->select('SELECT id FROM departments WHERE dept_key = ?', [$dept['key']]);
            if ($existing) {
                continue;
            }
            $this->db->execute(
                'INSERT INTO departments (dept_key, code, name, slug) VALUES (?, ?, ?, ?)',
                [$dept['key'], $dept['code'], $dept['name'], $dept['slug']]
            );
        }
    }

    public function departments(): array
    {
        $this->boot();

        return $this->db->select('SELECT * FROM departments ORDER BY name');
    }

    public function departmentByKey(string $key): ?array
    {
        $this->boot();
        $rows = $this->db->select('SELECT * FROM departments WHERE dept_key = ?', [$key]);

        return $rows[0] ?? null;
    }

    public function byDepartmentKey(string $key): array
    {
        $this->boot();

        return $this->db->select(
            'SELECT teachers.* FROM teachers
             INNER JOIN departments ON departments.id = teachers.department_id
             WHERE departments.dept_key = ?
             ORDER BY teachers.sort_order, teachers.name',
            [$key]
        );
    }

    public function find(int $id): ?array
    {
        $this->boot();
        $rows = $this->db->select(
            'SELECT teachers.*, departments.dept_key, departments.name as department_name, departments.slug
             FROM teachers INNER JOIN departments ON departments.id = teachers.department_id
             WHERE teachers.id = ?',
            [$id]
        );

        return $rows[0] ?? null;
    }

    public function findByEmpOrEncrypted(string $key): ?array
    {
        $this->boot();
        $key = trim($key);
        if ($key === '') {
            return null;
        }
        $rows = $this->db->select(
            'SELECT teachers.*, departments.dept_key, departments.name as department_name, departments.slug
             FROM teachers LEFT JOIN departments ON departments.id = teachers.department_id
             WHERE teachers.emp_id = ? OR teachers.encrypted_id = ?
             ORDER BY teachers.id LIMIT 1',
            [$key, $key]
        );

        return $rows[0] ?? null;
    }

    public function byOfficeCode(int $code): array
    {
        $this->boot();

        return $this->db->select(
            'SELECT teachers.* FROM teachers
             INNER JOIN departments ON departments.id = teachers.department_id
             WHERE departments.code = ?
             ORDER BY teachers.sort_order, teachers.name',
            [$code]
        );
    }

    public function create(array $data): int
    {
        $this->boot();
        $now = now()->toDateTimeString();
        $result = $this->db->execute(
            'INSERT INTO teachers (department_id, emp_id, name, designation, institute1, institute2, email, office_ext, encrypted_id, photo_path, sort_order, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['department_id'],
                $data['emp_id'] ?? null,
                $data['name'],
                $data['designation'] ?? null,
                $data['institute1'] ?? null,
                $data['institute2'] ?? null,
                $data['email'] ?? null,
                $data['office_ext'] ?? null,
                $data['encrypted_id'] ?? null,
                $data['photo_path'] ?? null,
                $data['sort_order'] ?? 0,
                $now,
                $now,
            ]
        );

        return (int) ($result['lastrowid'] ?? 0);
    }

    public function update(int $id, array $data): void
    {
        $this->boot();
        $this->db->execute(
            'UPDATE teachers SET department_id=?, name=?, designation=?, institute1=?, institute2=?, email=?, office_ext=?, photo_path=?, updated_at=? WHERE id=?',
            [
                $data['department_id'],
                $data['name'],
                $data['designation'] ?? null,
                $data['institute1'] ?? null,
                $data['institute2'] ?? null,
                $data['email'] ?? null,
                $data['office_ext'] ?? null,
                $data['photo_path'] ?? null,
                now()->toDateTimeString(),
                $id,
            ]
        );
    }

    public function updatePhotoByEmpId(string $empId, int $departmentId, string $photoPath): void
    {
        if ($empId === '') {
            return;
        }
        $this->boot();
        $this->db->execute(
            'UPDATE teachers SET photo_path = ?, updated_at = ? WHERE emp_id = ? AND department_id = ?',
            [$photoPath, now()->toDateTimeString(), $empId, $departmentId]
        );
    }

    public function delete(int $id): void
    {
        $this->boot();
        $this->db->execute('DELETE FROM teachers WHERE id = ?', [$id]);
    }

    public function replaceDepartmentTeachers(int $departmentId, array $teachers): void
    {
        $this->boot();
        $this->db->execute('DELETE FROM teachers WHERE department_id = ?', [$departmentId]);
        foreach ($teachers as $i => $teacher) {
            $this->create(array_merge($teacher, [
                'department_id' => $departmentId,
                'sort_order' => $i,
            ]));
        }
    }

    public function upsertDepartmentTeachers(int $departmentId, array $teachers): void
    {
        $this->boot();
        $existing = $this->db->select('SELECT id, emp_id FROM teachers WHERE department_id = ?', [$departmentId]);
        $byEmp = [];
        foreach ($existing as $row) {
            $byEmp[(string) ($row['emp_id'] ?? '')] = (int) $row['id'];
        }
        $keep = [];
        foreach ($teachers as $i => $teacher) {
            $emp = (string) ($teacher['emp_id'] ?? '');
            $keep[$emp] = true;
            if ($emp !== '' && isset($byEmp[$emp])) {
                $this->db->execute(
                    'UPDATE teachers SET name=?, designation=?, institute1=?, institute2=?, email=?, office_ext=?, encrypted_id=?, photo_path=COALESCE(?, photo_path), sort_order=?, updated_at=? WHERE id=?',
                    [
                        $teacher['name'],
                        $teacher['designation'] ?? null,
                        $teacher['institute1'] ?? null,
                        $teacher['institute2'] ?? null,
                        $teacher['email'] ?? null,
                        $teacher['office_ext'] ?? null,
                        $teacher['encrypted_id'] ?? null,
                        $teacher['photo_path'] ?? null,
                        $i,
                        now()->toDateTimeString(),
                        $byEmp[$emp],
                    ]
                );
            } else {
                $this->create(array_merge($teacher, [
                    'department_id' => $departmentId,
                    'sort_order' => $i,
                ]));
            }
        }
        foreach ($existing as $row) {
            $emp = (string) ($row['emp_id'] ?? '');
            if ($emp === '' || isset($keep[$emp])) {
                continue;
            }
            $this->delete((int) $row['id']);
        }
    }

    public function updateProfile(int $id, string $profileJson): void
    {
        $this->boot();
        $this->db->execute(
            'UPDATE teachers SET profile_json = ?, updated_at = ? WHERE id = ?',
            [$profileJson, now()->toDateTimeString(), $id]
        );
    }

    /**
     * @param  array<string, mixed>|null  $raw
     * @return array{heading: string, title: string, roles: array<int, string>, social: array<int, string>, tabs: array<string, array{label: string, html: string, active: bool}>}
     */
    public function normalizeProfile(?array $raw): array
    {
        $raw = is_array($raw) ? $raw : [];
        $tabs = [];
        $storedTabs = is_array($raw['tabs'] ?? null) ? $raw['tabs'] : [];

        foreach (self::PROFILE_TABS as $key => $label) {
            $tab = is_array($storedTabs[$key] ?? null) ? $storedTabs[$key] : [];
            $html = trim((string) ($tab['html'] ?? ''));
            $hasActive = array_key_exists('active', $tab);
            $tabs[$key] = [
                'label' => trim((string) ($tab['label'] ?? $label)) ?: $label,
                'html' => $html,
                'active' => $hasActive ? (bool) $tab['active'] : $html !== '',
            ];
        }

        $roles = array_values(array_filter(array_map(
            fn ($role) => trim((string) $role),
            is_array($raw['roles'] ?? null) ? $raw['roles'] : []
        )));
        $social = array_values(array_filter(array_map(
            fn ($href) => trim((string) $href),
            is_array($raw['social'] ?? null) ? $raw['social'] : []
        )));

        return [
            'heading' => trim((string) ($raw['heading'] ?? '')),
            'title' => trim((string) ($raw['title'] ?? '')),
            'roles' => $roles,
            'social' => $social,
            'tabs' => $tabs,
        ];
    }

    public function decodeProfile(?array $teacher): array
    {
        $raw = json_decode((string) ($teacher['profile_json'] ?? ''), true);

        return $this->normalizeProfile(is_array($raw) ? $raw : []);
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{heading: string, title: string, roles: array<int, string>, social: array<int, string>, tabs: array<string, array{label: string, html: string, active: bool}>}
     */
    public function profileFromInput(array $input): array
    {
        $roles = preg_split('/\r\n|\r|\n/', (string) ($input['profile_roles'] ?? '')) ?: [];
        $social = preg_split('/\r\n|\r|\n/', (string) ($input['profile_social'] ?? '')) ?: [];
        $tabBodies = is_array($input['tab'] ?? null) ? $input['tab'] : [];
        $tabActive = is_array($input['tab_active'] ?? null) ? $input['tab_active'] : [];

        $tabs = [];
        foreach (self::PROFILE_TABS as $key => $label) {
            $html = trim((string) ($tabBodies[$key] ?? ''));
            $tabs[$key] = [
                'label' => $label,
                'html' => $html,
                'active' => ! empty($tabActive[$key]),
            ];
        }

        return $this->normalizeProfile([
            'heading' => $input['profile_heading'] ?? '',
            'title' => $input['profile_title'] ?? '',
            'roles' => $roles,
            'social' => $social,
            'tabs' => $tabs,
        ]);
    }

    public function allWithEncryptedId(bool $officeOnly = false): array
    {
        $this->boot();

        $sql = 'SELECT teachers.id, teachers.name, teachers.encrypted_id, teachers.photo_path, teachers.profile_json
                FROM teachers LEFT JOIN departments ON departments.id = teachers.department_id
                WHERE teachers.encrypted_id IS NOT NULL AND teachers.encrypted_id != \'\'';
        $params = [];
        if ($officeOnly) {
            $sql .= ' AND departments.dept_key LIKE ?';
            $params[] = 'office-%';
        }

        return $this->db->select($sql, $params);
    }

    public function toApiRows(array $teachers): array
    {
        return array_map(function ($teacher) {
            $photo = $teacher['photo_path'] ?? '';
            if ($photo && ! str_starts_with($photo, 'http') && ! str_starts_with($photo, 'data:')) {
                $photo = asset(ltrim($photo, '/'));
            }

            return [
                'id' => (int) ($teacher['id'] ?? 0),
                'EmpID' => $teacher['emp_id'] ?? '',
                'EmpName' => $teacher['name'] ?? '',
                'EmpIMG' => $photo,
                'Designation' => $teacher['designation'] ?? '',
                'Institute1' => $teacher['institute1'] ?? '',
                'Institute2' => $teacher['institute2'] ?? '',
                'email' => $teacher['email'] ?? '',
                'office_ext' => $teacher['office_ext'] ?? '',
                'EncryptedID' => $teacher['encrypted_id'] ?? '',
                'ProfileURL' => ! empty($teacher['id']) ? url('/faculty/'.$teacher['id']) : '',
            ];
        }, $teachers);
    }
}
