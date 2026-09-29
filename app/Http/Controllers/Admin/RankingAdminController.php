<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminPostHelper;
use App\Support\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RankingAdminController extends Controller
{
    public function __construct(private SiteContent $content) {}

    public function index(): View
    {
        return view('admin.rankings.index', [
            'nav' => 'rankings',
            'items' => $this->content->rankings(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'alt' => 'required|string|max:180',
            'url' => 'nullable|string|max:500',
            'image' => 'required|image|max:5120',
        ]);
        $path = AdminPostHelper::storeCover($request->file('image'), 'rankings', Str::slug($data['alt']).'-'.time());
        $items = $this->content->rankings();
        $items[] = [
            'id' => (string) Str::uuid(),
            'alt' => $data['alt'],
            'image' => $path,
            'url' => $data['url'] ?? '',
        ];
        $this->content->saveRankings($items);

        return back()->with('status', 'Ranking logo added.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $items = array_values(array_filter(
            $this->content->rankings(),
            fn ($row) => (string) ($row['id'] ?? '') !== $id
        ));
        $this->content->saveRankings($items);

        return back()->with('status', 'Ranking logo removed.');
    }
}
