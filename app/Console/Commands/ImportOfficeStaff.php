<?php

namespace App\Console\Commands;

use App\Support\TeacherRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportOfficeStaff extends Command
{
    protected $signature = 'cms:import-office-staff';

    protected $description = 'Download Administration staff directories and photos from uttara.ac.bd';

    public function handle(TeacherRepository $teachers): int
    {
        $teachers->boot();
        $dir = public_path('uploads/teachers');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $ok = 0;
        $photos = 0;
        foreach (TeacherRepository::OFFICES as $office) {
            $url = 'https://www.uttara.ac.bd/api/upload.php?action=departmentEmpList&deptCode='.$office['code'];
            try {
                $response = Http::timeout(90)->acceptJson()->get($url);
            } catch (\Throwable $e) {
                $this->warn('Failed '.$office['key'].' '.$e->getMessage());

                continue;
            }
            if (! $response->successful()) {
                $this->warn('Failed '.$office['key'].' HTTP '.$response->status());

                continue;
            }
            $people = collect($response->json('data') ?? [])
                ->filter(fn ($person) => ! in_array((string) ($person['EmpID'] ?? ''), TeacherRepository::SKIP, true))
                ->values();
            $row = $teachers->departmentByKey($office['key']);
            if (! $row) {
                $this->warn('Missing department '.$office['key']);

                continue;
            }
            $rows = [];
            foreach ($people as $i => $person) {
                $photo = $this->storePhoto($person, $office['key'], $dir);
                if ($photo) {
                    $photos++;
                }
                $rows[] = [
                    'emp_id' => $person['EmpID'] ?? null,
                    'name' => trim($person['EmpName'] ?? ''),
                    'designation' => trim($person['Designation'] ?? ''),
                    'institute1' => trim($person['Institute1'] ?? ''),
                    'institute2' => trim($person['Institute2'] ?? ''),
                    'email' => trim($person['email'] ?? ''),
                    'office_ext' => trim($person['office_ext'] ?? ''),
                    'encrypted_id' => trim($person['EncryptedID'] ?? ''),
                    'photo_path' => $photo,
                    'sort_order' => $i,
                ];
            }
            $teachers->upsertDepartmentTeachers((int) $row['id'], $rows);
            $ok += count($rows);
            $this->info($office['name'].': '.count($rows).' staff');
            usleep(120000);
        }

        [$staticOk, $staticPhotos] = $this->importStaticOffice($teachers, 'office-of-it', 'office-it', $dir);
        $ok += $staticOk;
        $photos += $staticPhotos;

        $this->info("Saved {$ok} office staff records ({$photos} photos).");

        return self::SUCCESS;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function importStaticOffice(TeacherRepository $teachers, string $slug, string $officeKey, string $dir): array
    {
        $row = $teachers->departmentByKey($officeKey);
        if (! $row) {
            $this->warn('Missing department '.$officeKey);

            return [0, 0];
        }

        $response = Http::timeout(90)->get('https://www.uttara.ac.bd/'.$slug.'/');
        if (! $response->successful()) {
            $this->warn('Failed static '.$slug);

            return [0, 0];
        }

        $html = $response->body();
        if (! preg_match('/<div[^>]+id=["\']faculty["\'][^>]*>(.*)<\/div>\s*<div[^>]+id=/is', $html, $pane)) {
            $this->warn('No faculty pane on '.$slug);

            return [0, 0];
        }

        $chunk = $pane[1];
        preg_match_all('/<div class="media">.*?employee-details[^\"]*empid=([a-f0-9]+).*?<\/div>\s*<\/div>/is', $chunk, $blocks, PREG_SET_ORDER);
        $rows = [];
        $photos = 0;
        foreach ($blocks as $i => $block) {
            $card = $block[0];
            $enc = $block[1];
            preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $card, $img);
            preg_match('/<h5[^>]*>(.*?)<\/h5>/is', $card, $name);
            preg_match_all('/<h4[^>]*>(.*?)<\/h4>/is', $card, $h4);
            $titles = array_values(array_filter(array_map(fn ($t) => trim(html_entity_decode(strip_tags($t))), $h4[1] ?? [])));
            $designation = $titles[0] ?? '';
            $inst = array_values(array_filter($titles, fn ($t) => $t !== $designation && ! str_contains($t, '@') && ! str_contains(strtolower($t), 'ext')));
            preg_match('/mailto:([^"\']+)/', $card, $mail);
            preg_match('/Ext:\s*(\d+)/i', $card, $ext);
            $person = [
                'EmpID' => $enc,
                'EmpName' => trim(html_entity_decode(strip_tags($name[1] ?? ''))),
                'Designation' => $designation,
                'Institute1' => $inst[0] ?? '',
                'Institute2' => $inst[1] ?? '',
                'email' => $mail[1] ?? '',
                'office_ext' => $ext[1] ?? '',
                'EncryptedID' => $enc,
                'EmpIMG' => $img[1] ?? '',
            ];
            $photo = $this->storePhoto($person, $officeKey, $dir);
            if ($photo) {
                $photos++;
            }
            $rows[] = [
                'emp_id' => $enc,
                'name' => $person['EmpName'],
                'designation' => $designation,
                'institute1' => $person['Institute1'],
                'institute2' => $person['Institute2'],
                'email' => $person['email'],
                'office_ext' => $person['office_ext'],
                'encrypted_id' => $enc,
                'photo_path' => $photo,
                'sort_order' => $i,
            ];
        }

        $teachers->upsertDepartmentTeachers((int) $row['id'], $rows);
        $this->info('Static '.$slug.': '.count($rows).' staff');

        return [count($rows), $photos];
    }

    private function storePhoto(array $person, string $key, string $dir): ?string
    {
        $img = trim((string) ($person['EmpIMG'] ?? ''));
        if ($img === '' || preg_match('#/api/image/(male|female)\.jpe?g#i', $img)) {
            return null;
        }
        if (preg_match('#^https?://#i', $img)) {
            try {
                $response = Http::timeout(60)->get($img);
                $bin = $response->successful() ? $response->body() : '';
            } catch (\Throwable) {
                $bin = '';
            }
            if ($bin === '' || ! $this->looksLikeImage($bin)) {
                return $img;
            }
            $ext = str_contains($img, '.png') ? 'png' : 'jpg';

            return $this->writePhoto($dir, $key, $person, $ext, $bin);
        }
        if (preg_match('#^data:image/([^;]+)(?:;charset=[^;]+)?;base64,(.+)$#s', $img, $m)) {
            $bin = base64_decode($m[2], true);
            if ($bin === false || ! $this->looksLikeImage($bin)) {
                return null;
            }
            $ext = str_starts_with($bin, "\x89PNG") ? 'png' : 'jpg';

            return $this->writePhoto($dir, $key, $person, $ext, $bin);
        }

        return null;
    }

    private function writePhoto(string $dir, string $key, array $person, string $ext, string $bin): string
    {
        $name = $key.'_'.preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($person['EmpID'] ?? uniqid())).'.'.$ext;
        file_put_contents($dir.'/'.$name, $bin);

        return 'uploads/teachers/'.$name;
    }

    private function looksLikeImage(string $bin): bool
    {
        return str_starts_with($bin, "\xFF\xD8") || str_starts_with($bin, "\x89PNG");
    }
}
