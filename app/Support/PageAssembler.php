<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

class PageAssembler
{
    public static function split(string $body): array
    {
        $empty = [
            'layout' => 'raw',
            'banner_class' => '',
            'banner_title' => '',
            'banner_sub' => '',
            'crumb' => '',
            'content' => $body,
            'section_mode' => 'single',
            'sections' => [],
            'tabs_meta' => null,
        ];

        if (trim($body) === '') {
            $empty['sections'] = [self::section('main', 'Main content', '', 'This is the main body of the page.')];

            return $empty;
        }

        [$dom, $root] = self::load($body);
        if (! $root) {
            $empty['sections'] = [self::section('main', 'Main content', $body, 'This is the main body of the page.')];

            return $empty;
        }

        $xpath = new DOMXPath($dom);
        $banner = self::firstClass($xpath, $root, 'admission-banner')
            ?? self::firstClass($xpath, $root, 'inner-head');

        $data = $empty;

        if ($banner) {
            $layout = str_contains($banner->getAttribute('class'), 'admission-banner') ? 'admission' : 'inner';
            $h1 = $xpath->query('.//h1', $banner)->item(0);
            $sub = self::firstClass($xpath, $banner, 'admission-banner-sub');
            $crumb = self::firstClass($xpath, $banner, 'dept-crumb')
                ?? self::firstClass($xpath, $root, 'crumb');

            $data['layout'] = $layout;
            $data['banner_class'] = $banner->getAttribute('class');
            $data['banner_title'] = $h1 ? trim($h1->textContent) : '';
            $data['banner_sub'] = $sub ? trim($sub->textContent) : '';
            $data['crumb'] = $crumb ? self::innerHtml($crumb) : '';

            $banner->parentNode?->removeChild($banner);
            if ($crumb && $crumb->parentNode && ! str_contains($crumb->getAttribute('class'), 'dept-crumb')) {
                $crumb->parentNode->removeChild($crumb);
            }
        }

        $content = self::unwrapContainers(trim(self::innerHtml($root)));
        $data['content'] = $content;

        $sectioned = self::extractSections($content);
        $data['section_mode'] = $sectioned['mode'];
        $data['sections'] = $sectioned['sections'];
        $data['tabs_meta'] = $sectioned['tabs_meta'];

        return $data;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>  $input
     */
    public static function join(array $meta, array $input): string
    {
        $layout = $input['layout'] ?? $meta['layout'] ?? 'raw';
        $bannerClass = trim($input['banner_class'] ?? $meta['banner_class'] ?? '');
        $title = htmlspecialchars($input['banner_title'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $sub = htmlspecialchars($input['banner_sub'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $crumb = $input['crumb'] ?? ($meta['crumb'] ?? '');

        $content = self::joinSections($meta, $input);

        if ($layout === 'raw' || ($bannerClass === '' && $layout !== 'admission')) {
            return $content;
        }

        $crumbHtml = $crumb !== '' ? '<p class="dept-crumb">'.$crumb.'</p>' : '';

        if ($layout === 'admission') {
            $subHtml = $sub !== '' ? '<p class="admission-banner-sub">'.$sub.'</p>' : '';

            return '<div class="admission-banner"><div class="container"><h1>'.$title.'</h1>'.$subHtml.$crumbHtml.'</div></div>'."\n".
                '<main class="page-wrap admission-page"><div class="container">'.$content.'</div></main>';
        }

        $cls = $bannerClass !== '' ? $bannerClass : 'inner-head';
        $banner = '<div class="'.$cls.'"><div class="container"><h1>'.$title.'</h1>'.$crumbHtml.'</div></div>';
        $trimmed = ltrim($content);

        if (str_starts_with($trimmed, '<div data-tabs')
            || str_contains($trimmed, 'class="page-wrap')
            || str_starts_with($trimmed, '<main')) {
            return $banner."\n".$content;
        }

        return $banner."\n".'<main class="page-wrap"><div class="container">'.$content.'</div></main>';
    }

    /**
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>  $input
     */
    public static function joinSections(array $meta, array $input): string
    {
        $mode = $input['section_mode'] ?? $meta['section_mode'] ?? 'single';
        $posted = is_array($input['sections'] ?? null) ? $input['sections'] : null;

        if ($posted === null) {
            return (string) ($input['body'] ?? $meta['content'] ?? '');
        }

        $metaSections = is_array($meta['sections'] ?? null) ? $meta['sections'] : [];
        $byKey = [];
        foreach ($metaSections as $section) {
            if (! empty($section['key'])) {
                $byKey[$section['key']] = $section;
            }
        }

        $sections = [];
        foreach ($posted as $key => $html) {
            $key = (string) $key;
            $label = (string) ($byKey[$key]['label'] ?? self::humanLabel($key));
            $help = (string) ($byKey[$key]['help'] ?? '');
            $sections[] = self::section($key, $label, trim((string) $html), $help);
        }

        if ($sections === [] && $metaSections !== []) {
            $sections = $metaSections;
        }

        if ($mode === 'tabs') {
            return self::buildTabsHtml($sections, is_array($meta['tabs_meta'] ?? null) ? $meta['tabs_meta'] : []);
        }

        if ($mode === 'blocks') {
            return self::buildBlocksHtml($sections);
        }

        return trim((string) ($sections[0]['html'] ?? ''));
    }

    /**
     * @return array{mode: string, sections: array<int, array{key: string, label: string, html: string, help: string}>, tabs_meta: ?array<string, mixed>}
     */
    public static function extractSections(string $content): array
    {
        $content = trim($content);
        if ($content === '') {
            return [
                'mode' => 'single',
                'sections' => [self::section('main', 'Main content', '', 'This is the main body of the page.')],
                'tabs_meta' => null,
            ];
        }

        if (str_contains($content, 'data-panel=') && str_contains($content, 'data-tab=')) {
            return self::extractTabSections($content);
        }

        if (str_contains($content, 'data-uu-section=')) {
            return self::extractMarkedBlocks($content);
        }

        $structural = self::extractStructuralSections($content);
        if (count($structural) >= 2) {
            return [
                'mode' => 'blocks',
                'sections' => $structural,
                'tabs_meta' => null,
            ];
        }

        return [
            'mode' => 'single',
            'sections' => [self::section(
                'main',
                'Main content',
                $content,
                'Everything visitors read in the main area of this page.'
            )],
            'tabs_meta' => null,
        ];
    }

    /**
     * @return array<int, array{key: string, label: string, html: string, help: string}>
     */
    private static function extractStructuralSections(string $content): array
    {
        [$dom, $root] = self::load($content);
        if (! $root) {
            return [];
        }

        $top = [];
        foreach ($root->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $top[] = $child;
            }
        }
        if ($top === []) {
            return [];
        }

        $chunks = [];
        foreach ($top as $el) {
            $class = $el->getAttribute('class');
            $isWrap = in_array(strtolower($el->nodeName), ['main', 'div', 'section'], true)
                && (str_contains($class, 'page-wrap') || strtolower($el->nodeName) === 'main');

            if ($isWrap) {
                $innerHtml = self::unwrapContainers(self::outerHtml($el));
                $headingChunks = self::extractHeadingBlocks($innerHtml);
                if (count($headingChunks) >= 2) {
                    foreach ($headingChunks as $chunk) {
                        $chunks[] = $chunk;
                    }
                } else {
                    $label = self::leadingHeading($el) ?? 'Main page body';
                    $chunks[] = self::section(
                        self::slugKey($label),
                        $label,
                        trim($innerHtml),
                        'Updates the “'.$label.'” area on the live page.'
                    );
                }

                continue;
            }

            $classToken = explode(' ', trim($class))[0] ?? 'section';
            $label = self::leadingHeading($el) ?? self::humanLabel($classToken);
            $chunks[] = self::section(
                self::slugKey($label),
                $label,
                trim(self::outerHtml($el)),
                'Updates the “'.$label.'” section on the live page.'
            );
        }

        $used = [];
        $sections = [];
        foreach ($chunks as $i => $chunk) {
            $key = $chunk['key'];
            if (isset($used[$key])) {
                $key .= '-'.($i + 1);
                $chunk['key'] = $key;
            }
            $used[$key] = true;
            $sections[] = $chunk;
        }

        return count($sections) >= 2 ? $sections : [];
    }

    /**
     * @return array{mode: string, sections: array<int, array{key: string, label: string, html: string, help: string}>, tabs_meta: array<string, mixed>}
     */
    private static function extractTabSections(string $content): array
    {
        [$dom, $root] = self::load($content);
        $xpath = new DOMXPath($dom);
        $labels = [];
        foreach ($xpath->query('.//*[@data-tab]', $root) as $button) {
            if (! $button instanceof DOMElement) {
                continue;
            }
            $key = trim($button->getAttribute('data-tab'));
            if ($key === '') {
                continue;
            }
            $labels[$key] = trim(preg_replace('/\s+/', ' ', $button->textContent) ?? $key) ?: self::humanLabel($key);
        }

        $panelClasses = [];
        $sections = [];
        foreach ($xpath->query('.//*[@data-panel]', $root) as $panel) {
            if (! $panel instanceof DOMElement) {
                continue;
            }
            $key = trim($panel->getAttribute('data-panel'));
            if ($key === '' || isset($panelClasses[$key])) {
                continue;
            }
            $panelClasses[$key] = trim($panel->getAttribute('class'));
            $label = $labels[$key] ?? self::humanLabel($key);
            $sections[] = self::section(
                $key,
                $label,
                trim(self::innerHtml($panel)),
                'Updates the “'.$label.'” tab on the live page.'
            );
        }

        $main = self::firstClass($xpath, $root, 'page-wrap') ?? self::firstTag($xpath, $root, 'main');
        $mainClass = $main instanceof DOMElement ? trim($main->getAttribute('class')) : 'page-wrap dept-page';
        if ($mainClass === '') {
            $mainClass = 'page-wrap dept-page';
        }

        return [
            'mode' => 'tabs',
            'sections' => $sections !== [] ? $sections : [self::section('main', 'Main content', $content, 'Main page body.')],
            'tabs_meta' => [
                'main_class' => $mainClass,
                'panel_classes' => $panelClasses,
                'labels' => $labels,
            ],
        ];
    }

    /**
     * @return array{mode: string, sections: array<int, array{key: string, label: string, html: string, help: string}>, tabs_meta: null}
     */
    private static function extractMarkedBlocks(string $content): array
    {
        [$dom, $root] = self::load($content);
        $xpath = new DOMXPath($dom);
        $sections = [];
        foreach ($xpath->query('.//*[@data-uu-section]', $root) as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }
            $key = trim($node->getAttribute('data-uu-section'));
            if ($key === '') {
                continue;
            }
            $label = trim($node->getAttribute('data-uu-label')) ?: self::humanLabel($key);
            $sections[] = self::section(
                $key,
                $label,
                trim(self::innerHtml($node)),
                'Updates the “'.$label.'” section on the live page.'
            );
        }

        return [
            'mode' => 'blocks',
            'sections' => $sections !== [] ? $sections : [self::section('main', 'Main content', $content, 'Main page body.')],
            'tabs_meta' => null,
        ];
    }

    /**
     * @return array<int, array{key: string, label: string, html: string, help: string}>
     */
    private static function extractHeadingBlocks(string $content): array
    {
        [$dom, $root] = self::load($content);
        if (! $root) {
            return [];
        }

        $children = [];
        foreach ($root->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $children[] = $child;
            } elseif (trim($child->textContent ?? '') !== '') {
                return [];
            }
        }

        if (count($children) === 1 && in_array(strtolower($children[0]->nodeName), ['main', 'div', 'section'], true)) {
            $inner = [];
            foreach ($children[0]->childNodes as $child) {
                if ($child instanceof DOMElement) {
                    $inner[] = $child;
                }
            }
            if (count($inner) >= 2) {
                $children = $inner;
            } elseif (count($inner) === 1 && str_contains($inner[0]->getAttribute('class'), 'container')) {
                $nested = [];
                foreach ($inner[0]->childNodes as $child) {
                    if ($child instanceof DOMElement) {
                        $nested[] = $child;
                    }
                }
                if (count($nested) >= 2) {
                    $children = $nested;
                }
            }
        }

        // Group by h2/h3 boundaries; keep intro content before the first heading.
        $chunks = [];
        $current = null;
        $pre = '';
        foreach ($children as $child) {
            $heading = null;
            $tag = strtolower($child->nodeName);
            if (in_array($tag, ['h1', 'h2', 'h3'], true)) {
                $heading = trim(preg_replace('/\s+/', ' ', $child->textContent) ?? '');
            }
            if ($heading) {
                if ($current !== null) {
                    $chunks[] = $current;
                } elseif ($pre !== '') {
                    $chunks[] = [
                        'key' => 'intro',
                        'label' => 'Introduction',
                        'html' => $pre,
                    ];
                    $pre = '';
                }
                $current = [
                    'key' => self::slugKey($heading),
                    'label' => $heading,
                    'html' => self::outerHtml($child),
                ];
            } elseif ($current !== null) {
                $current['html'] .= self::outerHtml($child);
            } else {
                $pre .= self::outerHtml($child);
            }
        }
        if ($current !== null) {
            $chunks[] = $current;
        } elseif ($pre !== '') {
            return [];
        }

        if (count($chunks) < 2) {
            return [];
        }

        $used = [];
        $sections = [];
        foreach ($chunks as $i => $chunk) {
            $key = $chunk['key'];
            if (isset($used[$key])) {
                $key .= '-'.($i + 1);
            }
            $used[$key] = true;
            $sections[] = self::section(
                $key,
                $chunk['label'],
                trim($chunk['html']),
                'Updates the “'.$chunk['label'].'” section on the live page.'
            );
        }

        return $sections;
    }

    /**
     * @param  array<int, array{key: string, label: string, html: string, help?: string}>  $sections
     * @param  array<string, mixed>  $meta
     */
    private static function buildTabsHtml(array $sections, array $meta): string
    {
        $mainClass = trim((string) ($meta['main_class'] ?? 'page-wrap dept-page')) ?: 'page-wrap dept-page';
        $panelClasses = is_array($meta['panel_classes'] ?? null) ? $meta['panel_classes'] : [];
        $buttons = '';
        $panels = '';
        foreach ($sections as $i => $section) {
            $key = htmlspecialchars($section['key'], ENT_QUOTES, 'UTF-8');
            $label = htmlspecialchars($section['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $active = $i === 0 ? ' is-active' : '';
            $btnClass = $i === 0 ? ' class="is-active"' : '';
            $savedClass = trim((string) ($panelClasses[$section['key']] ?? ''));
            $savedClass = preg_replace('/\bis-active\b/', '', $savedClass) ?? $savedClass;
            $savedClass = trim(preg_replace('/\s+/', ' ', $savedClass) ?? '');
            if ($savedClass === '') {
                $savedClass = 'panel';
            }
            if (! str_contains(' '.$savedClass.' ', ' panel ')) {
                $savedClass = 'panel '.$savedClass;
            }
            $buttons .= '      <button type="button" data-tab="'.$key.'"'.$btnClass.'>'.$label."</button>\n";
            $panels .= '        <div class="'.trim($savedClass.$active).'" data-panel="'.$key.'">'."\n".$section['html']."\n        </div>\n";
        }

        return <<<HTML
<div data-tabs>
  <div class="dept-tab-bg"><div class="container"><div class="dept-tabs">
{$buttons}  </div></div></div>
  <main class="{$mainClass}">
    <div class="container">
{$panels}    </div>
  </main>
</div>
HTML;
    }

    /**
     * @param  array<int, array{key: string, label: string, html: string}>  $sections
     */
    private static function buildBlocksHtml(array $sections): string
    {
        $html = '';
        foreach ($sections as $section) {
            $key = htmlspecialchars($section['key'], ENT_QUOTES, 'UTF-8');
            $label = htmlspecialchars($section['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html .= '<div data-uu-section="'.$key.'" data-uu-label="'.$label.'">'."\n".$section['html']."\n</div>\n";
        }

        return trim($html);
    }

    /** @return array{key: string, label: string, html: string, help: string} */
    private static function section(string $key, string $label, string $html, string $help = ''): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'html' => $html,
            'help' => $help,
        ];
    }

    private static function humanLabel(string $key): string
    {
        $key = str_replace(['-', '_'], ' ', $key);

        return ucwords($key);
    }

    private static function slugKey(string $label): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $label) ?? ''));
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'section';
    }

    private static function leadingHeading(DOMElement $node): ?string
    {
        $tag = strtolower($node->nodeName);
        if (in_array($tag, ['h1', 'h2', 'h3'], true)) {
            $text = trim(preg_replace('/\s+/', ' ', $node->textContent) ?? '');

            return $text !== '' ? $text : null;
        }

        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->nodeName);
                if (in_array($tag, ['h1', 'h2', 'h3'], true)) {
                    $text = trim(preg_replace('/\s+/', ' ', $child->textContent) ?? '');

                    return $text !== '' ? $text : null;
                }
                break;
            }
            if (trim($child->textContent ?? '') !== '') {
                break;
            }
        }

        return null;
    }

    private static function load(string $html): array
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<meta charset="utf-8"><div id="uu-root">'.$html.'</div>',
            LIBXML_HTML_NODEFDTD
        );
        $root = $dom->getElementById('uu-root');

        return [$dom, $root];
    }

    private static function firstClass(DOMXPath $xpath, DOMNode $context, string $class): ?DOMElement
    {
        $nodes = $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " '.$class.' ")]', $context);

        return $nodes->item(0) instanceof DOMElement ? $nodes->item(0) : null;
    }

    private static function firstTag(DOMXPath $xpath, DOMNode $context, string $tag): ?DOMElement
    {
        $nodes = $xpath->query('.//'.$tag, $context);

        return $nodes->item(0) instanceof DOMElement ? $nodes->item(0) : null;
    }

    private static function innerHtml(DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument->saveHTML($child);
        }

        return $html;
    }

    private static function outerHtml(DOMNode $node): string
    {
        return $node->ownerDocument->saveHTML($node) ?: '';
    }

    private static function unwrapContainers(string $html): string
    {
        $html = trim($html);
        if (preg_match('/^<main[^>]*>([\s\S]*)<\/main>$/i', $html, $m)) {
            $html = trim($m[1]);
        }
        if (preg_match('/^<div class="page-wrap[^"]*"[^>]*>([\s\S]*)<\/div>$/i', $html, $m)) {
            $html = trim($m[1]);
        }
        if (preg_match('/^<div class="container">([\s\S]*)<\/div>$/i', $html, $m)) {
            $html = trim($m[1]);
        }

        return $html;
    }
}
