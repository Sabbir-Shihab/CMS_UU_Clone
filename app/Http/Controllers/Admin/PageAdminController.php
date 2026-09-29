<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminPostHelper;
use App\Support\PageAssembler;
use App\Support\PageRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageAdminController extends Controller
{
    public function __construct(private PageRepository $pages) {}

    public function index(Request $request)
    {
        $filter = $request->string('status')->toString();
        $groups = $this->pages->grouped();
        if (in_array($filter, [PageRepository::STATUS_DRAFT, PageRepository::STATUS_PUBLISHED], true)) {
            foreach ($groups as $name => $items) {
                $groups[$name] = array_values(array_filter(
                    $items,
                    fn (array $page): bool => ($page['status'] ?? '') === $filter
                ));
            }
            $groups = array_filter($groups, fn ($items) => $items !== []);
        }

        return view('admin.pages.index', [
            'nav' => 'pages',
            'groups' => $groups,
            'statusFilter' => $filter,
            'draftCount' => $this->pages->draftCount(),
            'totalCount' => count($this->pages->all()),
        ]);
    }

    public function create()
    {
        return view('admin.pages.create', [
            'nav' => 'pages',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:180',
            'body' => 'nullable|string',
            'status' => 'nullable|in:draft,published',
        ]);

        $slug = trim((string) ($data['slug'] ?? ''));
        if ($slug === '') {
            $slug = AdminPostHelper::slugify($data['title']);
        } else {
            $slug = Str::slug($slug) ?: AdminPostHelper::slugify($slug);
        }
        if ($slug === '' || $this->pages->find($slug)) {
            return back()->withErrors(['slug' => 'Choose a different page address. This one is empty or already used.'])->withInput();
        }

        $title = $data['title'];
        if (! str_contains($title, 'Uttara University')) {
            $title .= ' | Uttara University';
        }

        $body = PageAssembler::join([
            'layout' => 'inner',
            'banner_class' => 'about-us-banner',
            'banner_title' => $data['title'],
            'banner_sub' => '',
            'crumb' => 'Home / '.$data['title'],
            'content' => '',
            'section_mode' => 'single',
            'sections' => [['key' => 'main', 'label' => 'Main content', 'html' => '', 'help' => '']],
            'tabs_meta' => null,
        ], [
            'layout' => 'inner',
            'banner_class' => 'about-us-banner',
            'banner_title' => $data['title'],
            'banner_sub' => '',
            'crumb' => 'Home / '.$data['title'],
            'section_mode' => 'single',
            'sections' => ['main' => $data['body'] ?? ''],
        ]);

        $status = $data['status'] ?? PageRepository::STATUS_DRAFT;
        $page = $this->pages->create([
            'slug' => $slug,
            'title' => $title,
            'menu_key' => 'other',
            'body' => $body,
            'status' => $status,
        ]);

        abort_unless($page, 500);

        $message = $status === PageRepository::STATUS_PUBLISHED
            ? 'Page created and is now live on the website.'
            : 'Draft saved. Review it, then approve to publish.';

        return redirect()->route('admin.pages.edit', $slug)->with('status', $message);
    }

    public function edit(string $slug)
    {
        $page = $this->pages->find($slug);
        abort_unless($page, 404);

        return view('admin.pages.form', [
            'nav' => 'pages',
            'groups' => $this->pages->grouped(),
            'page' => $page,
            'form' => PageAssembler::split($page['body'] ?? ''),
        ]);
    }

    public function update(Request $request, string $slug)
    {
        $page = $this->pages->find($slug);
        abort_unless($page, 404);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'banner_title' => 'nullable|string|max:255',
            'banner_sub' => 'nullable|string|max:255',
            'banner_class' => 'nullable|string|max:180',
            'layout' => 'nullable|string|max:20',
            'crumb' => 'nullable|string',
            'body' => 'nullable|string',
            'section_mode' => 'nullable|in:single,tabs,blocks',
            'sections' => 'nullable|array',
            'sections.*' => 'nullable|string',
            'status' => 'nullable|in:draft,published',
        ]);

        $split = PageAssembler::split($page['body'] ?? '');
        $body = PageAssembler::join($split, $data);

        $fields = [
            'title' => $data['title'],
            'body' => $body,
            'updated_at' => now()->toDateTimeString(),
        ];

        if (isset($data['status']) && ! in_array($slug, ['home', 'index'], true)) {
            $fields['status'] = $data['status'];
            if ($data['status'] === PageRepository::STATUS_PUBLISHED) {
                $fields['published_at'] = now()->toDateTimeString();
            }
        }

        $this->pages->updateExisting($slug, $fields);

        return redirect()->route('admin.pages.edit', $slug)->with('status', 'Changes saved.');
    }

    public function approve(string $slug)
    {
        abort_unless($this->pages->setStatus($slug, PageRepository::STATUS_PUBLISHED), 404);

        return back()->with('status', 'Approved — now live on the website.');
    }

    public function unpublish(string $slug)
    {
        abort_unless($this->pages->setStatus($slug, PageRepository::STATUS_DRAFT), 404);

        return back()->with('status', 'Page hidden. Approve again to make it visible.');
    }

    public function destroy(string $slug)
    {
        abort_unless($this->pages->clearContent($slug), 404);

        return redirect()->route('admin.pages.edit', $slug)->with('status', 'Page text cleared.');
    }

    public function destroyPage(string $slug)
    {
        abort_unless($this->pages->delete($slug), 404);

        return redirect()->route('admin.pages.index')->with('status', 'Page deleted.');
    }
}
