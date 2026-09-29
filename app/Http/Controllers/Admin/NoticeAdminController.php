<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminPostHelper;
use App\Support\NoticeRepository;
use App\Support\SqliteStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoticeAdminController extends Controller
{
    public function __construct(
        private NoticeRepository $notices,
        private SqliteStore $db,
    ) {}

    public function index(Request $request): View
    {
        $result = $this->notices->adminPaginate(
            (int) $request->query('page', 1),
            20,
            (string) $request->query('q', '')
        );

        return view('admin.notices.index', [
            'nav' => 'notices',
            'result' => $result,
        ]);
    }

    public function create(): View
    {
        return view('admin.notices.form', [
            'nav' => 'notices',
            'post' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $dates = AdminPostHelper::datesFromInput($data['published_on']);
        $slug = AdminPostHelper::uniqueSlug($this->db, 'notice_posts', $data['title']);
        $image = '';
        if ($request->hasFile('cover')) {
            $image = AdminPostHelper::storeCover($request->file('cover'), 'notices', $slug);
        }

        $id = $this->notices->create([
            'slug' => $slug,
            'title' => $data['title'],
            'display_date' => $dates['display_date'],
            'day_label' => $dates['day_label'],
            'month_label' => $dates['month_label'],
            'published_at' => $dates['published_at'],
            'image_path' => $image,
            'content' => $data['body'] ?? '',
        ]);

        return redirect()->route('admin.notices.edit', $id)
            ->with('status', 'Notice published. It is now live on the website.');
    }

    public function edit(int $id): View
    {
        $post = $this->notices->findById($id);
        abort_unless($post, 404);

        return view('admin.notices.form', [
            'nav' => 'notices',
            'post' => $post,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $post = $this->notices->findById($id);
        abort_unless($post, 404);

        $data = $this->validated($request);
        $dates = AdminPostHelper::datesFromInput($data['published_on']);
        $slug = AdminPostHelper::uniqueSlug($this->db, 'notice_posts', $data['title'], $id);
        $image = (string) ($post['image_path'] ?? '');
        if ($request->boolean('remove_cover')) {
            $image = '';
        }
        if ($request->hasFile('cover')) {
            $image = AdminPostHelper::storeCover($request->file('cover'), 'notices', $slug);
        }

        $this->notices->update($id, [
            'slug' => $slug,
            'title' => $data['title'],
            'display_date' => $dates['display_date'],
            'day_label' => $dates['day_label'],
            'month_label' => $dates['month_label'],
            'published_at' => $dates['published_at'],
            'image_path' => $image,
            'content' => $data['body'] ?? '',
        ]);

        return redirect()->route('admin.notices.edit', $id)
            ->with('status', 'Notice updated. Changes are live on the website.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $post = $this->notices->findById($id);
        abort_unless($post, 404);
        $this->notices->delete($id);

        return redirect()->route('admin.notices.index')
            ->with('status', 'Notice deleted.');
    }

    /** @return array{title: string, published_on: string, body: ?string} */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'published_on' => 'required|date',
            'body' => 'nullable|string',
            'cover' => 'nullable|image|max:5120',
            'remove_cover' => 'nullable|boolean',
        ]);
    }
}
