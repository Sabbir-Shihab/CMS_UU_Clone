<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminPostHelper;
use App\Support\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AlbumAdminController extends Controller
{
    public function __construct(private SiteContent $content) {}

    public function index(): View
    {
        return view('admin.albums.index', [
            'nav' => 'albums',
            'albums' => $this->content->albums(),
        ]);
    }

    public function create(): View
    {
        return view('admin.albums.form', [
            'nav' => 'albums',
            'album' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:180',
            'photos' => 'nullable|array',
            'photos.*' => 'image|max:8192',
        ]);
        $id = (string) random_int(1000, 999999);
        $photos = [];
        foreach ($request->file('photos', []) as $i => $file) {
            $photos[] = AdminPostHelper::storeCover($file, 'albums', $id, 'photo-'.($i + 1).'-'.time());
        }
        $albums = $this->content->albums();
        array_unshift($albums, [
            'id' => $id,
            'title' => $data['title'],
            'photos' => $photos,
        ]);
        $this->content->saveAlbums($albums);

        return redirect()->route('admin.albums.edit', $id)->with('status', 'Album created.');
    }

    public function edit(string $id): View
    {
        $album = $this->content->album($id);
        abort_unless($album, 404);

        return view('admin.albums.form', [
            'nav' => 'albums',
            'album' => $album,
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $album = $this->content->album($id);
        abort_unless($album, 404);
        $data = $request->validate([
            'title' => 'required|string|max:180',
            'photos' => 'nullable|array',
            'photos.*' => 'image|max:8192',
            'keep_photo' => 'array',
        ]);

        $photos = array_values(array_filter($request->input('keep_photo', [])));
        foreach ($request->file('photos', []) as $i => $file) {
            $photos[] = AdminPostHelper::storeCover($file, 'albums', $id, 'photo-'.Str::random(6));
        }

        $albums = $this->content->albums();
        foreach ($albums as &$row) {
            if ((string) ($row['id'] ?? '') === $id) {
                $row['title'] = $data['title'];
                $row['photos'] = $photos;
            }
        }
        unset($row);
        $this->content->saveAlbums($albums);

        return back()->with('status', 'Album updated.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $albums = array_values(array_filter(
            $this->content->albums(),
            fn ($row) => (string) ($row['id'] ?? '') !== $id
        ));
        $this->content->saveAlbums($albums);

        return redirect()->route('admin.albums.index')->with('status', 'Album deleted.');
    }
}
