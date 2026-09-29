<?php

namespace App\Console\Commands;

use App\Support\PageRepository;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportOfficePages extends Command
{
    protected $signature = 'cms:import-offices {--slug= : Import only this office slug}';

    protected $description = 'Refresh Administration office pages and inner tabs from uttara.ac.bd';

    /** @var array<string, array<int, string>> */
    private const PAGES = [
        'administration' => ['administration'],
        'office-of-the-founder' => ['office-of-the-founder'],
        'office-of-the-vice-chancellor' => ['office-of-the-vice-chancellor'],
        'office-of-the-treasurer' => ['office-of-the-treasurer'],
        'office-of-the-registrar' => ['office-of-the-registrar'],
        'office-of-the-controller-of-examinations' => ['office-of-the-controller-of-examinations'],
        'office-of-the-proctor' => ['office-of-the-proctor'],
        'office-of-finance-and-accounts' => ['office-of-finance-and-accounts'],
        'office-of-it' => ['office-of-it'],
        'office-of-public-relations' => ['office-of-public-relations'],
        'faculty-hr-office' => ['faculty-hr-office'],
        'office-of-external-affairs' => ['office-of-external-affairs'],
        'office-of-international-affairs' => ['office-of-international-affairs'],
        'office-of-student-affairs' => ['office-of-students-affairs', 'office-of-student-affairs'],
        'office-of-brand-communication' => ['office-of-brand-communication'],
        'medical-emergency-center' => ['medical-emergency-center'],
        'office-of-logistics-procurement' => ['office-of-logistics-procurement'],
        'office-of-student-services-counselling' => ['office-of-student-services-counselling'],
        'office-of-internal-affairs' => ['office-of-internal-affairs'],
        'office-of-internal-audit' => ['office-of-internal-audit'],
        'office-of-compliance-legal-affairs' => ['office-of-compliance-legal-affairs'],
        'office-of-the-project-director' => ['office-of-the-project-director'],
        'office-of-transport' => ['office-of-transport'],
        'institutional-ranking-cell' => ['institutional-ranking-cell'],
    ];

    /** @var array<string, string> */
    private const CODES = [
        'administration' => '47',
        'office-of-the-founder' => '26',
        'office-of-the-vice-chancellor' => '25',
        'office-of-the-treasurer' => '24',
        'office-of-the-registrar' => '3',
        'office-of-the-controller-of-examinations' => '7',
        'office-of-the-proctor' => '28',
        'office-of-finance-and-accounts' => '2',
        'office-of-it' => '31',
        'office-of-public-relations' => '29',
        'faculty-hr-office' => '32',
        'office-of-external-affairs' => '33',
        'office-of-international-affairs' => '34',
        'office-of-student-affairs' => '35',
        'office-of-brand-communication' => '36',
        'medical-emergency-center' => '37',
        'office-of-logistics-procurement' => '38',
        'office-of-student-services-counselling' => '39',
        'office-of-internal-affairs' => '40',
        'office-of-internal-audit' => '41',
        'office-of-compliance-legal-affairs' => '42',
        'office-of-the-project-director' => '43',
        'office-of-transport' => '44',
        'institutional-ranking-cell' => '52',
    ];

    public function handle(PageRepository $pages): int
    {
        $ok = 0;
        $only = (string) $this->option('slug');
        foreach (self::PAGES as $slug => $candidates) {
            if ($only !== '' && $slug !== $only) {
                continue;
            }
            $page = $pages->find($slug);
            if (! $page) {
                $this->warn('Missing local page: '.$slug);

                continue;
            }
            $fetched = $this->fetchFirst($candidates);
            if (! $fetched) {
                $this->warn('Official page not found: '.$slug);

                continue;
            }
            [$url, $html] = $fetched;
            $title = $this->extractTitle($html) ?: ($page['title'] ?? 'Uttara University');
            $heading = $this->extractHeading($html) ?: $this->headingFromSlug($slug);
            $code = $this->facultyCode($html, $page['body'] ?? '', $slug);
            $tabs = $this->extractTabs($html, $code);
            if ($tabs === []) {
                $this->warn('Empty tabs: '.$slug.' '.$url);

                continue;
            }
            $body = $this->buildBody($slug, $heading, $tabs);
            $pages->upsert(array_merge($page, [
                'title' => $title,
                'menu_key' => 'admin',
                'body' => $body,
            ]));
            $ok++;
            $labels = implode(', ', array_column($tabs, 'label'));
            $this->info('Updated '.$slug.' ['.$labels.']');
            usleep(150000);
        }
        $this->info("Updated {$ok} Administration pages.");

        return self::SUCCESS;
    }

    /** @param array<int, string> $slugs */
    private function fetchFirst(array $slugs): ?array
    {
        foreach ($slugs as $path) {
            $url = 'https://www.uttara.ac.bd/'.trim($path, '/').'/';
            $html = $this->get($url);
            if ($html && ! preg_match('/<body[^>]*class="[^"]*error404/i', $html) && ! preg_match('/<title>\s*Page not found/i', $html)) {
                return [$url, $html];
            }
        }

        return null;
    }

    private function get(string $url): ?string
    {
        for ($i = 1; $i <= 3; $i++) {
            try {
                $response = Http::timeout(60)->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
                    'Accept' => 'text/html',
                ])->get($url);
                if ($response->successful()) {
                    return $response->body();
                }
            } catch (\Throwable) {
            }
            usleep(300000 * $i);
        }

        return null;
    }

    private function extractTitle(string $html): string
    {
        if (preg_match('/<title>(.*?)<\/title>/si', $html, $m)) {
            return html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return '';
    }

    private function extractHeading(string $html): string
    {
        [$xpath] = $this->xpath($html);
        foreach (['//h3[contains(@class,"dept-name-common")]', '//h1'] as $q) {
            $node = $xpath->query($q)->item(0);
            if ($node) {
                $text = trim(preg_replace('/\s+/', ' ', $node->textContent) ?? '');
                if ($text !== '') {
                    return $text;
                }
            }
        }

        return '';
    }

    /**
     * @return array<int, array{key: string, label: string, html: string, about: bool}>
     */
    private function extractTabs(string $html, string $code): array
    {
        [$xpath] = $this->xpath($html);
        $links = $xpath->query('//ul[@id="myTab"]//a[@href]');
        $tabs = [];
        if ($links && $links->length > 0) {
            foreach ($links as $link) {
                $href = trim($link->getAttribute('href'), '#');
                $label = trim(preg_replace('/\s+/', ' ', $link->textContent) ?? '');
                if ($href === '' || $label === '') {
                    continue;
                }
                $key = $this->tabKey($href, $label);
                $isStaff = $key === 'staff' || str_contains(Str::lower($label), 'staff');
                $content = $isStaff
                    ? $this->staffPane($xpath, $href, $code)
                    : $this->paneHtml($xpath, $href);
                if (! $isStaff && $content === '') {
                    continue;
                }
                $tabs[] = [
                    'key' => $key,
                    'label' => $label,
                    'html' => $content,
                    'about' => in_array($key, ['about', 'rules'], true) || $tabs === [],
                ];
            }
        }

        if ($tabs === []) {
            $about = $this->fallbackAbout($xpath);
            if ($about !== '') {
                $tabs[] = ['key' => 'about', 'label' => 'About', 'html' => $about, 'about' => true];
            }
            $tabs[] = ['key' => 'staff', 'label' => 'Staff Directory', 'html' => $this->staffHtml($code), 'about' => false];
        }

        $seen = [];
        $unique = [];
        foreach ($tabs as $tab) {
            if (isset($seen[$tab['key']])) {
                continue;
            }
            $seen[$tab['key']] = true;
            $unique[] = $tab;
        }

        return $unique;
    }

    private function tabKey(string $href, string $label): string
    {
        $href = Str::lower($href);
        $label = Str::lower($label);
        if ($href === 'faculty' || str_contains($label, 'staff')) {
            return 'staff';
        }
        if (in_array($href, ['rules', 'about', 'external'], true) || $label === 'about') {
            return 'about';
        }

        return Str::slug($href);
    }

    private function paneHtml(DOMXPath $xpath, string $id): string
    {
        $node = $xpath->query('//*[@id="'.$id.'"]')->item(0);
        if (! $node) {
            return '';
        }
        $inner = $this->innerHtml($node);
        $bodies = $xpath->query('.//*[contains(@class,"card-body")]', $node);
        if ($bodies && $bodies->length > 0) {
            $parts = '';
            foreach ($bodies as $body) {
                $parts .= $this->innerHtml($body);
            }
            $fullLen = strlen(trim(strip_tags($inner)));
            $partsLen = strlen(trim(strip_tags($parts)));
            if ($partsLen >= $fullLen - 20 && $partsLen > 40) {
                $inner = $parts;
            }
        }

        return $this->cleanHtml($inner);
    }

    private function fallbackAbout(DOMXPath $xpath): string
    {
        $selectors = [
            '//*[@id="accordion-1"]//*[contains(@class,"card-body")]',
            '//p[contains(@class,"chancellor-para")]/ancestor::div[contains(@class,"container")][1]',
            '//*[@id="about"]',
        ];
        foreach ($selectors as $q) {
            $found = $xpath->query($q)->item(0);
            if ($found) {
                return $this->cleanHtml($this->innerHtml($found));
            }
        }

        return '';
    }

    private function staffPane(DOMXPath $xpath, string $id, string $code): string
    {
        $html = $this->paneHtml($xpath, $id);
        if (str_contains($html, 'key-person-title') || str_contains($html, 'class="media"')) {
            $html = preg_replace(
                '#https?://www\.uttara\.ac\.bd/employee-details/?\?empid=([^"&]+)#i',
                '/faculty/e/$1',
                $html
            ) ?? $html;
            $html = preg_replace(
                '#href="(?:/employee-details/?)\?empid=([^"]+)"#i',
                'href="/faculty/e/$1"',
                $html
            ) ?? $html;

            return $html;
        }

        return $this->staffHtml($code);
    }

    private function staffHtml(string $code): string
    {
        if ($code !== '') {
            return '<div data-faculty-code="'.$code.'"></div>';
        }

        return '<p class="faculty-status">Staff directory is published on the official office page.</p>';
    }

    private function facultyCode(string $html, string $existing, string $slug): string
    {
        if (preg_match('/data-code="(\d+)"/', $html, $m)) {
            return $m[1];
        }
        if (preg_match('/data-faculty-code="(\d+)"/', $existing, $m)) {
            return $m[1];
        }

        return self::CODES[$slug] ?? '';
    }

    private function headingFromSlug(string $slug): string
    {
        return match ($slug) {
            'administration' => 'Advisory Board',
            'faculty-hr-office' => 'Faculty & HR Office',
            'medical-emergency-center' => 'Medical & Emergency Center and Mental Health Unit',
            'institutional-ranking-cell' => 'Institutional Ranking Cell',
            default => ucwords(str_replace('-', ' ', $slug)),
        };
    }

    /** @param array<int, array{key: string, label: string, html: string, about: bool}> $tabs */
    private function buildBody(string $slug, string $heading, array $tabs): string
    {
        $safeHead = htmlspecialchars($heading, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $crumb = htmlspecialchars($heading, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $id = $slug === 'administration' ? ' id="office-of-the-adviser"' : '';
        $buttons = '';
        $panels = '';
        foreach ($tabs as $i => $tab) {
            $key = htmlspecialchars($tab['key'], ENT_QUOTES, 'UTF-8');
            $label = htmlspecialchars($tab['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $active = $i === 0 ? ' is-active' : '';
            $aboutClass = $tab['about'] || $tab['key'] === 'about' ? ' office-about' : ' office-subpage';
            $buttons .= '      <button type="button" data-tab="'.$key.'" class="'.trim($active).'">'.$label."</button>\n";
            $panels .= '        <div class="panel'.$active.$aboutClass.'" data-panel="'.$key.'">'."\n".$tab['html']."\n        </div>\n";
        }

        return <<<HTML
<div class="about-us-banner"{$id}><div class="container">
  <h3 class="dept-name-common">{$safeHead}</h3>
  <p class="dept-crumb"><a href="/">Home</a> / Administration / {$crumb}</p>
</div></div>
  <div data-tabs>
    <div class="dept-tab-bg"><div class="container"><div class="dept-tabs">
{$buttons}    </div></div></div>
    <main class="page-wrap dept-page office-page">
      <div class="container">
{$panels}      </div>
    </main>
  </div>
HTML;
    }

    private function cleanHtml(string $inner): string
    {
        $inner = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $inner) ?? $inner;
        $inner = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $inner) ?? $inner;
        $inner = preg_replace('/\s+on\w+="[^"]*"/i', '', $inner) ?? $inner;
        $inner = preg_replace('#(src|href)="//www\.uttara\.ac\.bd/#i', '$1="https://www.uttara.ac.bd/', $inner) ?? $inner;
        $inner = preg_replace('#(src|href)="(/wp-content/[^"]+)"#i', '$1="https://www.uttara.ac.bd$2"', $inner) ?? $inner;
        $inner = preg_replace('~href="https://www\\.uttara\\.ac\\.bd/(?!wp-content)([^"]+)"~i', 'href="/$1"', $inner) ?? $inner;
        $inner = preg_replace('/<div class="row\s+resource-person-list"><\/div>/', '', $inner) ?? $inner;

        return trim($inner);
    }

    /** @return array{0: DOMXPath, 1: DOMDocument} */
    private function xpath(string $html): array
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        $dom->loadHTML($html);
        libxml_clear_errors();

        return [new DOMXPath($dom), $dom];
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
