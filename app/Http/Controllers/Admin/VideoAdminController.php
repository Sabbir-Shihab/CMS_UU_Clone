<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoAdminController extends Controller
{
    public function __construct(private SiteContent $content) {}

    public function index(): View
    {
        return view('admin.videos.index', [
            'nav' => 'videos',
            'videos' => $this->content->videos(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'id' => 'required|string|max:200',
        ]);
        $id = $this->extractYoutubeId($data['id']);
        if (! $id) {
            return back()->withErrors(['id' => 'Enter a YouTube video ID or full YouTube URL.'])->withInput();
        }

        $videos = $this->content->videos();
        $videos[] = ['id' => $id, 'title' => $data['title']];
        $this->content->saveVideos($videos);

        return back()->with('status', 'Video added to gallery.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $videos = array_values(array_filter(
            $this->content->videos(),
            fn ($row) => (string) ($row['id'] ?? '') !== $id
        ));
        $this->content->saveVideos($videos);

        return back()->with('status', 'Video removed.');
    }

    private function extractYoutubeId(string $value): ?string
    {
        $value = trim($value);
        if (preg_match('/^[A-Za-z0-9_-]{6,20}$/', $value)) {
            return $value;
        }
        if (preg_match('/(?:v=|youtu\.be\/|embed\/)([A-Za-z0-9_-]{6,20})/', $value, $m)) {
            return $m[1];
        }

        return null;
    }
}
