<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,webp,gif,pdf',
        ]);

        $file = $request->file('file');
        $folder = preg_replace('/[^a-z0-9_-]+/i', '', (string) $request->input('folder', 'uploads')) ?: 'uploads';
        $dir = public_path($folder === 'uploads' ? 'uploads' : 'uploads/'.$folder);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $name = date('YmdHis').'_'.preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
        $file->move($dir, $name);
        $path = ($folder === 'uploads' ? 'uploads/' : 'uploads/'.$folder.'/').$name;

        return response()->json([
            'location' => asset($path),
            'path' => '/'.$path,
        ]);
    }
}
