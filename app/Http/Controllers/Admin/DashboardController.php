<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\NewsRepository;
use App\Support\NoticeRepository;
use App\Support\PageRepository;
use App\Support\SqliteStore;
use App\Support\TeacherRepository;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private NewsRepository $news,
        private NoticeRepository $notices,
        private PageRepository $pages,
        private TeacherRepository $teachers,
    ) {}

    public function index(): View
    {
        $this->teachers->boot();
        $teacherRows = app(SqliteStore::class)->select('SELECT COUNT(*) AS total FROM teachers');

        return view('admin.dashboard', [
            'nav' => 'dashboard',
            'stats' => [
                'news' => $this->news->count(),
                'notices' => $this->notices->count(),
                'pages' => count($this->pages->all()),
                'drafts' => $this->pages->draftCount(),
                'teachers' => (int) ($teacherRows[0]['total'] ?? 0),
            ],
            'recentNews' => $this->news->adminPaginate(1, 5)['items'],
            'recentNotices' => $this->notices->adminPaginate(1, 5)['items'],
            'draftPages' => array_values(array_filter(
                $this->pages->all(),
                fn (array $page): bool => ! $this->pages->isPublished($page)
            )),
        ]);
    }
}
