<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuAdminController extends Controller
{
    public function __construct(private SiteContent $content) {}

    public function edit(): View
    {
        return view('admin.menus.edit', [
            'nav' => 'menus',
            'menus' => $this->content->menus(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'section' => 'required|in:quick_links,main_nav,footer_columns',
        ]);

        $menus = $this->content->menus();
        $section = $request->input('section');

        if ($section === 'footer_columns') {
            $columns = [];
            foreach ($request->input('footer_title', []) as $i => $title) {
                $title = trim((string) $title);
                if ($title === '') {
                    continue;
                }
                $labels = $request->input("footer_label.$i", []);
                $urls = $request->input("footer_url.$i", []);
                $links = [];
                foreach ($labels as $j => $label) {
                    $label = trim((string) $label);
                    $url = trim((string) ($urls[$j] ?? ''));
                    if ($label === '' || $url === '') {
                        continue;
                    }
                    $links[] = ['label' => $label, 'url' => $url];
                }
                $columns[] = ['title' => $title, 'links' => $links];
            }
            $menus['footer_columns'] = $columns;
        } else {
            $items = [];
            foreach ($request->input('label', []) as $i => $label) {
                $label = trim((string) $label);
                $url = trim((string) ($request->input("url.$i") ?? ''));
                if ($label === '') {
                    continue;
                }
                $item = [
                    'label' => $label,
                    'url' => $url !== '' ? $url : '#',
                ];
                if ($section === 'main_nav') {
                    if ($request->boolean("mega.$i")) {
                        $item['mega'] = true;
                    }
                    if ($nav = trim((string) $request->input("nav.$i"))) {
                        $item['nav'] = $nav;
                    }
                }
                $childLabels = $request->input("child_label.$i", []);
                $childUrls = $request->input("child_url.$i", []);
                $children = [];
                foreach ($childLabels as $j => $childLabel) {
                    $childLabel = trim((string) $childLabel);
                    $childUrl = trim((string) ($childUrls[$j] ?? ''));
                    if ($childLabel === '' || $childUrl === '') {
                        continue;
                    }
                    $children[] = ['label' => $childLabel, 'url' => $childUrl];
                }
                if ($children) {
                    $item['children'] = $children;
                }
                if ($section === 'quick_links') {
                    if ($class = trim((string) $request->input("class.$i"))) {
                        $item['class'] = $class;
                    }
                    if ($li = trim((string) $request->input("li_class.$i"))) {
                        $item['li_class'] = $li;
                    }
                }
                $items[] = $item;
            }
            $menus[$section] = $items;
        }

        $this->content->saveMenus($menus);

        return back()->with('status', 'Menus updated. Changes are live.');
    }
}
