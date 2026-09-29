<?php

namespace App\Console\Commands;

use App\Support\TeacherRepository;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportTeacherProfiles extends Command
{
    protected $signature = 'teachers:import-profiles {--limit=0} {--missing : Only staff without a saved profile} {--office : Only Administration office staff}';

    protected $description = 'Download full employee-details pages from uttara.ac.bd';

    private const TABS = [
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

    public function handle(TeacherRepository $teachers): int
    {
        @ini_set('memory_limit', '512M');
        $rows = $teachers->allWithEncryptedId((bool) $this->option('office'));
        if ($this->option('missing')) {
            $rows = array_values(array_filter($rows, fn ($row) => empty($row['profile_json'])));
        }
        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $rows = array_slice($rows, 0, $limit);
        }
        $this->info('Importing '.count($rows).' profiles from uttara.ac.bd…');
        $ok = 0;
        foreach ($rows as $i => $row) {
            $url = 'https://www.uttara.ac.bd/employee-details/?empid='.$row['encrypted_id'];
            $html = $this->fetchHtml($url);
            if ($html === null) {
                $this->warn('Skip '.$row['name'].' (request failed)');

                continue;
            }
            $profile = $this->parse($html);
            if ($profile === []) {
                $this->warn('No content: '.$row['name']);

                continue;
            }
            $teachers->updateProfile((int) $row['id'], json_encode($profile, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $ok++;
            if (($i + 1) % 25 === 0) {
                $this->info('  '.($i + 1).'/'.count($rows));
            }
            usleep(150000);
        }
        $this->info("Saved {$ok} profiles.");

        return self::SUCCESS;
    }

    private function fetchHtml(string $url): ?string
    {
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $response = Http::timeout(60)->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml',
                ])->get($url);
                if ($response->successful() && $response->body() !== '') {
                    return $response->body();
                }
            } catch (\Throwable $e) {
                if ($attempt === 3) {
                    $this->warn($e->getMessage());
                }
            }
            usleep(400000 * $attempt);
        }

        return null;
    }

    private function parse(string $html): array
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $heading = trim($xpath->query('//h3[contains(@class,"dept-name-common")]')->item(0)?->textContent ?? '');
        $title = trim($xpath->query('//h2[contains(@class,"title")]')->item(0)?->textContent ?? '');
        $roles = [];
        foreach ($xpath->query('//div[contains(@class,"col-md-9")]/p') as $p) {
            $text = trim(preg_replace('/\s+/', ' ', $p->textContent));
            if ($text !== '' && ! str_contains($text, '@') && ! str_contains(strtolower($text), 'extension')) {
                $roles[] = $text;
            }
        }
        $roles = array_values(array_unique(array_filter($roles)));

        $social = [];
        foreach ($xpath->query('//a[contains(@class,"icon")]') as $a) {
            $href = $a->getAttribute('href');
            if ($href) {
                $social[] = $href;
            }
        }

        $tabs = [];
        foreach (self::TABS as $id => $label) {
            $node = $xpath->query('//*[@id="'.$id.'"]')->item(0)
                ?? $dom->getElementById($id);
            $inner = $node ? $this->innerHtml($node) : '';
            if ($inner === '' && $node) {
                $inner = $dom->saveHTML($node) ?: '';
            }
            $inner = preg_replace('/src="data:image[^"]+"/', 'src=""', $inner) ?? $inner;
            $inner = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $inner) ?? $inner;
            $tabs[$id] = [
                'label' => $label,
                'html' => trim($inner),
            ];
        }

        if ($heading === '' && $title === '' && $tabs['educational']['html'] === '') {
            return [];
        }

        return [
            'heading' => $heading,
            'title' => $title,
            'roles' => $roles,
            'social' => $social,
            'tabs' => $tabs,
        ];
    }

    private function innerHtml(\DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument->saveHTML($child);
        }

        return $html;
    }
}
