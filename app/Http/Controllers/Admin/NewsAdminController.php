<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminPostHelper;
use App\Support\NewsRepository;
use App\Support\SqliteStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class NewsAdminController extends Controller
{
    public function __construct(
        private NewsRepository $news,
        private SqliteStore $db,
    ) {}

    public function index(Request $request): View
    {
        $result = $this->news->adminPaginate(
            (int) $request->query('page', 1),
            20,
            (string) $request->query('q', '')
        );

        return view('admin.news.index', [
            'nav' => 'news',
            'result' => $result,
        ]);
    }

    public function create(): View
    {
        return view('admin.news.form', [
            'nav' => 'news',
            'post' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $dates = AdminPostHelper::datesFromInput($data['published_on']);
        $slug = AdminPostHelper::uniqueSlug($this->db, 'news_posts', $data['title']);
        $image = '';
        if ($request->hasFile('cover')) {
            $image = AdminPostHelper::storeCover($request->file('cover'), 'news', $slug);
        }

        $id = $this->news->create([
            'slug' => $slug,
            'title' => $data['title'],
            'display_date' => $dates['display_date'],
            'published_at' => $dates['published_at'],
            'image_path' => $image,
            'content' => $data['body'] ?? '',
            'gallery_json' => json_encode($this->galleryPaths($request, $slug), JSON_UNESCAPED_SLASHES),
            'content_after' => $data['content_after'] ?? '',
        ]);

        return redirect()->route('admin.news.edit', $id)
            ->with('status', 'News published. It is now live on the website.');
    }

    public function edit(int $id): View
    {
        $post = $this->news->findById($id);
        abort_unless($post, 404);

        return view('admin.news.form', [
            'nav' => 'news',
            'post' => $post,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $post = $this->news->findById($id);
        abort_unless($post, 404);

        $data = $this->validated($request);
        $dates = AdminPostHelper::datesFromInput($data['published_on']);
        $slug = AdminPostHelper::uniqueSlug($this->db, 'news_posts', $data['title'], $id);
        $image = (string) ($post['image_path'] ?? '');
        if ($request->boolean('remove_cover')) {
            $image = '';
        }
        if ($request->hasFile('cover')) {
            $image = AdminPostHelper::storeCover($request->file('cover'), 'news', $slug);
        }

        $this->news->update($id, [
            'slug' => $slug,
            'title' => $data['title'],
            'display_date' => $dates['display_date'],
            'published_at' => $dates['published_at'],
            'image_path' => $image,
            'content' => $data['body'] ?? '',
            'gallery_json' => json_encode($this->galleryPaths($request, $slug), JSON_UNESCAPED_SLASHES),
            'content_after' => $data['content_after'] ?? '',
        ]);

        return redirect()->route('admin.news.edit', $id)
            ->with('status', 'News updated. Changes are live on the website.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $post = $this->news->findById($id);
        abort_unless($post, 404);
        $this->news->delete($id);

        return redirect()->route('admin.news.index')
            ->with('status', 'News deleted.');
    }

    /** @return array{title: string, published_on: string, body: ?string, content_after: ?string} */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'published_on' => 'required|date',
            'body' => 'nullable|string',
            'content_after' => 'nullable|string',
            'cover' => 'nullable|image|max:5120',
            'remove_cover' => 'nullable|boolean',
            'slots' => 'nullable|array',
            'slots.*.existing' => 'nullable|string|max:500',
            'slots.*.file' => 'nullable|image|max:5120',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function galleryPaths(Request $request, string $slug): array
    {
        $slots = $request->input('slots', []);
        if (! is_array($slots)) {
            return [];
        }

        $paths = [];
        foreach ($slots as $index => $slot) {
            if (! is_array($slot)) {
                continue;
            }

            $uploaded = $request->file('slots.'.$index.'.file');
            if ($uploaded instanceof UploadedFile && $uploaded->isValid()) {
                $paths[] = AdminPostHelper::storeCover(
                    $uploaded,
                    'news',
                    $slug,
                    'photo-'.((int) $index + 1).'-'.uniqid().'.'.strtolower($uploaded->getClientOriginalExtension() ?: 'jpg')
                );

                continue;
            }

            $existing = $this->safeGalleryPath((string) ($slot['existing'] ?? ''));
            if ($existing !== '') {
                $paths[] = $existing;
            }
        }

        return $paths;
    }

    private function safeGalleryPath(string $path): string
    {
        $path = trim($path);
        if ($path === '' || str_contains($path, '..') || str_contains($path, '\\')) {
            return '';
        }

        if (! str_starts_with($path, '/uploads/news/')) {
            return '';
        }

        return $path;
    }
}
