<?php

namespace App\Support;

use Illuminate\Support\Str;

class PageRepository
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public function path(): string
    {
        return storage_path('app/cms/pages.json');
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $path = $this->path();
        if (! is_file($path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($path), true);
        if (! is_array($data)) {
            return [];
        }

        return array_map(fn (array $page): array => $this->normalize($page), $data);
    }

    /**
     * @param  array<string, mixed>  $page
     * @return array<string, mixed>
     */
    public function normalize(array $page): array
    {
        $status = (string) ($page['status'] ?? self::STATUS_PUBLISHED);
        if (! in_array($status, [self::STATUS_DRAFT, self::STATUS_PUBLISHED], true)) {
            $status = self::STATUS_PUBLISHED;
        }
        $page['status'] = $status;
        $page['is_published'] = $status === self::STATUS_PUBLISHED;
        $title = (string) ($page['title'] ?? $page['slug'] ?? '');
        $page['short_title'] = trim(explode('|', $title)[0]);

        return $page;
    }

    public function isPublished(?array $page): bool
    {
        if (! $page) {
            return false;
        }

        return ($page['status'] ?? self::STATUS_PUBLISHED) === self::STATUS_PUBLISHED;
    }

    public function find(string $slug): ?array
    {
        foreach ($this->all() as $page) {
            if (($page['slug'] ?? '') === $slug) {
                return $page;
            }
        }

        return null;
    }

    public function findPublished(string $slug): ?array
    {
        $page = $this->find($slug);
        if (! $page || ! $this->isPublished($page)) {
            return null;
        }

        return $page;
    }

    /**
     * @return array<int, array{title: string, slug: string, type: string}>
     */
    public function search(string $query, int $limit = 40): array
    {
        $term = mb_strtolower(trim($query));
        if ($term === '') {
            return [];
        }

        $hits = [];
        $skip = ['home', 'index', 'search'];
        foreach ($this->all() as $page) {
            $slug = (string) ($page['slug'] ?? '');
            if ($slug === '' || in_array($slug, $skip, true) || ! $this->isPublished($page)) {
                continue;
            }

            $title = html_entity_decode(strip_tags((string) ($page['title'] ?? $slug)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $title = preg_replace('/\s*\|\s*Uttara University.*$/iu', '', $title) ?: $title;
            $hay = mb_strtolower($title.' '.$slug);
            if (! str_contains($hay, $term)) {
                continue;
            }

            $hits[] = [
                'title' => trim($title) !== '' ? trim($title) : $slug,
                'slug' => $slug,
                'type' => 'Page',
            ];
            if (count($hits) >= $limit) {
                break;
            }
        }

        return $hits;
    }

    public function saveAll(array $pages): void
    {
        $dir = dirname($this->path());
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $clean = [];
        foreach ($pages as $page) {
            $page = $this->normalize($page);
            unset($page['is_published'], $page['short_title']);
            $clean[] = $page;
        }

        usort($clean, fn ($a, $b) => strcmp($a['slug'] ?? '', $b['slug'] ?? ''));
        file_put_contents(
            $this->path(),
            json_encode(array_values($clean), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }

    public function upsert(array $page): void
    {
        $pages = $this->all();
        $found = false;
        foreach ($pages as $i => $existing) {
            if (($existing['slug'] ?? '') === ($page['slug'] ?? '') || (($page['id'] ?? '') !== '' && ($existing['id'] ?? '') === ($page['id'] ?? ''))) {
                $pages[$i] = array_merge($existing, $page);
                $found = true;
                break;
            }
        }
        if (! $found) {
            $page['id'] = $page['id'] ?? (string) Str::uuid();
            $pages[] = $page;
        }

        $this->saveAll($pages);
    }

    public function updateExisting(string $slug, array $fields): ?array
    {
        $page = $this->find($slug);
        if (! $page) {
            return null;
        }

        $this->upsert(array_merge($page, $fields, ['slug' => $page['slug'], 'id' => $page['id']]));

        return $this->find($slug);
    }

    public function setStatus(string $slug, string $status): ?array
    {
        if (! in_array($status, [self::STATUS_DRAFT, self::STATUS_PUBLISHED], true)) {
            return null;
        }
        if (in_array($slug, ['home', 'index'], true) && $status !== self::STATUS_PUBLISHED) {
            return null;
        }

        $page = $this->find($slug);
        if (! $page) {
            return null;
        }

        return $this->updateExisting($slug, [
            'status' => $status,
            'updated_at' => now()->toDateTimeString(),
            'published_at' => $status === self::STATUS_PUBLISHED
                ? now()->toDateTimeString()
                : ($page['published_at'] ?? null),
        ]);
    }

    public function clearContent(string $slug): bool
    {
        $page = $this->find($slug);
        if (! $page) {
            return false;
        }

        $this->upsert(array_merge($page, ['body' => '']));

        return true;
    }

    public function create(array $page): ?array
    {
        $slug = trim((string) ($page['slug'] ?? ''));
        if ($slug === '' || $this->find($slug)) {
            return null;
        }

        $status = (string) ($page['status'] ?? self::STATUS_DRAFT);
        if (! in_array($status, [self::STATUS_DRAFT, self::STATUS_PUBLISHED], true)) {
            $status = self::STATUS_DRAFT;
        }

        $record = [
            'id' => (string) Str::uuid(),
            'slug' => $slug,
            'title' => $page['title'] ?? $slug,
            'menu_key' => $page['menu_key'] ?? 'other',
            'body' => $page['body'] ?? '',
            'status' => $status,
            'updated_at' => now()->toDateTimeString(),
            'published_at' => $status === self::STATUS_PUBLISHED ? now()->toDateTimeString() : null,
        ];
        $this->upsert($record);

        return $this->find($slug) ?? $record;
    }

    public function delete(string $slug): bool
    {
        if (in_array($slug, ['home', 'index'], true)) {
            return false;
        }
        $before = $this->all();
        $pages = array_values(array_filter($before, fn ($page) => ($page['slug'] ?? '') !== $slug));
        if (count($pages) === count($before)) {
            return false;
        }
        $this->saveAll($pages);

        return true;
    }

    public function draftCount(): int
    {
        return count(array_filter($this->all(), fn (array $page): bool => ! $this->isPublished($page)));
    }

    /** @return array<string, array<int, array<string, mixed>>> */
    public function grouped(): array
    {
        $groups = [
            'Home' => [],
            'About' => [],
            'Academic' => [],
            'Department' => [],
            'Admission' => [],
            'Administration' => [],
            'Schools' => [],
            'Research & Innovation' => [],
            'Teachers' => [],
            'Journal & Publication' => [],
            'Gallery & Convocation' => [],
            'Alumni & Career' => [],
            'Other' => [],
        ];

        $about = ['about', 'mission', 'overview', 'unique-features', 'glance', 'rankings', 'campus', 'campus-life', 'sustainability', 'logo', 'bus'];
        $academic = ['academics', 'undergraduate', 'graduate', 'professional', 'iqac', 'partnerships', 'corporate', 'academic-calendar', 'grading-system'];
        $admission = ['admission', 'online-admission', 'scholarship-policy', 'entry-requirements', 'admission-test', 'credit-transfer', 'admission-procedure', 'tuition-fees', 'tuition-fee-calculator', 'scholarship', 'founders-scholarship-policy', 'm-azizur-rahman-inclusive-scholarship', 'student-support-loan', 'international-students', 'payment-guidelines', 'office-of-admission'];
        $research = ['research', 'center-for-research-and-training', 'fintech-research-group', 'centre-for-sustainable-nanoparticle-synthesis', 'center-for-clean-energy-research', 'center-for-bioinformatics-and-artificial-intelligence'];
        $journal = ['research-uu', 'journals'];
        $gallery = ['gallery', 'video-gallery', 'convocation', 'convocations', '1st-convocation', '2nd-convocation', '3rd-convocation', '4th-convocation', '5th-convocation', '6th-convocation', '7th-convocation', '8th-convocation', '9th-convocation'];
        $alumni = ['alumni', 'alumni-announcements', 'alumni-login', 'get-involved', 'employment-records', 'career', 'job-application-form'];

        foreach ($this->all() as $page) {
            $slug = $page['slug'] ?? '';
            $group = 'Other';
            if ($slug === 'home') {
                $group = 'Home';
            } elseif (str_starts_with($slug, 'department-')) {
                $group = 'Department';
            } elseif (str_starts_with($slug, 'school-of-') || $slug === 'academics') {
                $group = $slug === 'academics' ? 'Academic' : 'Schools';
            } elseif (str_starts_with($slug, 'office-of-') || in_array($slug, ['administration', 'faculty-hr-office', 'medical-emergency-center', 'institutional-ranking-cell'], true)) {
                $group = $slug === 'office-of-admission' ? 'Admission' : 'Administration';
            } elseif (in_array($slug, $about, true)) {
                $group = 'About';
            } elseif (in_array($slug, $academic, true)) {
                $group = 'Academic';
            } elseif (in_array($slug, $admission, true)) {
                $group = 'Admission';
            } elseif (in_array($slug, $research, true) || str_starts_with($slug, 'center-') || str_starts_with($slug, 'centre-') || $slug === 'fintech-research-group') {
                $group = 'Research & Innovation';
            } elseif (in_array($slug, ['teachers', 'visiting-faculty-members', 'faculty-bio'], true)) {
                $group = 'Teachers';
            } elseif (in_array($slug, $journal, true)) {
                $group = 'Journal & Publication';
            } elseif (in_array($slug, $gallery, true)) {
                $group = 'Gallery & Convocation';
            } elseif (in_array($slug, $alumni, true)) {
                $group = 'Alumni & Career';
            }
            $groups[$group][] = $page;
        }

        return array_filter($groups, fn ($items) => $items !== []);
    }
}
