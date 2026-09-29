<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminPostHelper;
use App\Support\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeAdminController extends Controller
{
    public function __construct(private SiteContent $content) {}

    public function edit(): View
    {
        return view('admin.home.edit', [
            'nav' => 'home',
            'home' => $this->content->home(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'ticker' => 'nullable|string',
            'slide_alt' => 'array',
            'slide_link' => 'array',
            'slide_image' => 'array',
            'slide_file' => 'array',
            'slide_file.*' => 'nullable|image|max:5120',
            'new_slide' => 'nullable|image|max:5120',
            'new_slide_alt' => 'nullable|string|max:255',
        ]);

        $home = $this->content->home();
        $slides = [];
        foreach ($request->input('slide_image', []) as $i => $image) {
            if ($request->boolean("remove_slide.$i")) {
                continue;
            }
            $image = trim((string) $image);
            if ($request->hasFile("slide_file.$i")) {
                $image = AdminPostHelper::storeCover($request->file("slide_file.$i"), 'home', 'slide-'.($i + 1).'-'.time());
            }
            if ($image === '') {
                continue;
            }
            $slides[] = [
                'image' => $image,
                'alt' => trim((string) ($request->input("slide_alt.$i") ?? '')),
                'link' => trim((string) ($request->input("slide_link.$i") ?? '')),
            ];
        }

        if ($request->hasFile('new_slide')) {
            $image = AdminPostHelper::storeCover($request->file('new_slide'), 'home', 'slide-'.time());
            $slides[] = [
                'image' => $image,
                'alt' => trim((string) $request->input('new_slide_alt', 'Home banner')),
                'link' => '',
            ];
        }

        $ticker = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $request->input('ticker', '')) ?: [])));

        $this->content->saveHome([
            'slides' => $slides,
            'ticker' => $ticker,
        ]);

        return back()->with('status', 'Homepage banners and ticker saved.');
    }
}
