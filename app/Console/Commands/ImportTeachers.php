<?php

namespace App\Console\Commands;

use App\Support\TeacherRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportTeachers extends Command
{
    protected $signature = 'teachers:import {--photos-only : Only download and update photos}';

    protected $description = 'Download department teachers from uttara.ac.bd into the local database';

    public function handle(TeacherRepository $teachers): int
    {
        $teachers->boot();
        $dir = public_path('uploads/teachers');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        @ini_set('memory_limit', '1024M');

        foreach (TeacherRepository::DEPARTMENTS as $dept) {
            $this->info('Fetching '.$dept['name'].'…');
            $url = 'https://www.uttara.ac.bd/api/upload.php?action=departmentEmpList&deptCode='.$dept['code'];
            $response = Http::timeout(180)->acceptJson()->get($url);
            if (! $response->successful()) {
                $this->error('Failed: '.$dept['key'].' HTTP '.$response->status());

                continue;
            }
            $people = collect($response->json('data') ?? [])
                ->filter(fn ($person) => ! in_array((string) ($person['EmpID'] ?? ''), TeacherRepository::SKIP, true))
                ->values();

            $row = $teachers->departmentByKey($dept['key']);
            if ($this->option('photos-only')) {
                $saved = 0;
                foreach ($people as $person) {
                    $path = $this->storePhoto($person, $dept['key'], $dir);
                    if (! $path) {
                        continue;
                    }
                    $teachers->updatePhotoByEmpId((string) ($person['EmpID'] ?? ''), (int) $row['id'], $path);
                    $saved++;
                }
                $this->info("  photos saved: {$saved}");

                continue;
            }
            $rows = [];
            foreach ($people as $i => $person) {
                $rows[] = [
                    'emp_id' => $person['EmpID'] ?? null,
                    'name' => trim($person['EmpName'] ?? ''),
                    'designation' => trim($person['Designation'] ?? ''),
                    'institute1' => trim($person['Institute1'] ?? ''),
                    'institute2' => trim($person['Institute2'] ?? ''),
                    'email' => trim($person['email'] ?? ''),
                    'office_ext' => trim($person['office_ext'] ?? ''),
                    'encrypted_id' => trim($person['EncryptedID'] ?? ''),
                    'photo_path' => $this->storePhoto($person, $dept['key'], $dir),
                    'sort_order' => $i,
                ];
            }
            $teachers->replaceDepartmentTeachers((int) $row['id'], $rows);
            $this->info('  saved '.count($rows).' teachers');
        }

        return self::SUCCESS;
    }

    private function storePhoto(array $person, string $key, string $dir): ?string
    {
        $img = trim((string) ($person['EmpIMG'] ?? ''));
        if ($img === '') {
            return null;
        }
        if (preg_match('#/api/image/(male|female)\.jpe?g#i', $img)) {
            return null;
        }
        if (preg_match('/^https?:\/\//i', $img)) {
            $response = Http::timeout(60)->get($img);
            $bin = $response->successful() ? $response->body() : '';
            if ($bin === '' || ! $this->looksLikeImage($bin)) {
                return $img;
            }
            $ext = str_contains($img, '.png') ? 'png' : 'jpg';
            $name = $key.'_'.preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($person['EmpID'] ?? uniqid())).'.'.$ext;
            file_put_contents($dir.'/'.$name, $bin);

            return 'uploads/teachers/'.$name;
        }
        if (preg_match('#^data:image/([^;]+)(?:;charset=[^;]+)?;base64,(.+)$#s', $img, $m)) {
            $ext = strtolower($m[1]);
            $ext = in_array($ext, ['jpeg', 'jpg'], true) ? 'jpg' : (preg_replace('/[^a-z0-9]/', '', $ext) ?: 'jpg');
            $bin = base64_decode($m[2], true);
            if ($bin === false) {
                return null;
            }
            if (str_starts_with($bin, "\x89PNG")) {
                $ext = 'png';
            } elseif (str_starts_with($bin, "\xFF\xD8")) {
                $ext = 'jpg';
            }
            $name = $key.'_'.preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($person['EmpID'] ?? uniqid())).'.'.$ext;
            file_put_contents($dir.'/'.$name, $bin);

            return 'uploads/teachers/'.$name;
        }

        return null;
    }

    private function looksLikeImage(string $bin): bool
    {
        return str_starts_with($bin, "\xFF\xD8") || str_starts_with($bin, "\x89PNG");
    }
}
