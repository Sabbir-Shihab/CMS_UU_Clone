<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsAdminController extends Controller
{
    public function __construct(private SiteContent $content) {}

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'nav' => 'settings',
            'settings' => $this->content->settings(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone_display' => 'required|string|max:120',
            'phone_tel' => 'required|string|max:40',
            'logo_url' => 'nullable|string|max:500',
            'mobile_logo_url' => 'nullable|string|max:500',
            'copyright' => 'required|string|max:255',
            'social_label' => 'array',
            'social_url' => 'array',
            'social_icon' => 'array',
        ]);

        $socials = [];
        foreach ($data['social_label'] ?? [] as $i => $label) {
            $label = trim((string) $label);
            $url = trim((string) ($data['social_url'][$i] ?? ''));
            if ($label === '' || $url === '') {
                continue;
            }
            $socials[] = [
                'label' => $label,
                'url' => $url,
                'icon' => trim((string) ($data['social_icon'][$i] ?? 'link')) ?: 'link',
            ];
        }

        $current = $this->content->settings();
        $this->content->saveSettings([
            'phone_display' => $data['phone_display'],
            'phone_tel' => $data['phone_tel'],
            'logo_url' => $data['logo_url'] ?: ($current['logo_url'] ?? ''),
            'mobile_logo_url' => $data['mobile_logo_url'] ?: ($current['mobile_logo_url'] ?? ''),
            'copyright' => $data['copyright'],
            'socials' => $socials,
        ]);

        return back()->with('status', 'Website settings saved.');
    }
}
