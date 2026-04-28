<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    // GET /images  -> list folder uniq
    public function index(Request $request)
    {
        $basePath = rtrim((string) env('TESTING_IMAGES_PATH', ''), '/');
        if ($basePath === '' || !is_dir($basePath)) {
            $folders = [];
            $error = 'TESTING_IMAGES_PATH not set / folder not found';
            return view('admin.images.index', compact('folders', 'error'));
        }

        $q = trim((string) $request->get('q', '')); // optional search folder name

        $allowedExt = ['jpg','jpeg','png','webp','gif'];

        $folders = [];
        $dirs = File::directories($basePath);

        foreach ($dirs as $dir) {
            $name = basename($dir);

            if ($q !== '' && stripos($name, $q) === false) {
                continue;
            }

            $count = 0;
            foreach (File::allFiles($dir) as $file) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, $allowedExt, true)) $count++;
            }

            $folders[] = [
                'name' => $name,
                'count' => $count,
                'path' => $dir,
            ];
        }

        // folder banyak pun ok, sort latest name/alpha
        usort($folders, fn($a, $b) => strcmp($b['name'], $a['name']));

        $error = null;
        return view('admin.images.index', compact('folders', 'error', 'q'));
    }

    // GET /images/folder/{folder} -> show semua gambar dalam folder uniq
    public function folder(string $folder)
    {
        $basePath = rtrim((string) env('TESTING_IMAGES_PATH', ''), '/');
        $baseUrl  = rtrim((string) env('TESTING_IMAGES_BASE', ''), '/');

        abort_if($basePath === '' || $baseUrl === '' || !is_dir($basePath), 404);

        // security: prevent ../ traversal
        $folder = trim($folder);
        abort_if($folder === '' || Str::contains($folder, ['..', '/', '\\']), 404);

        $target = $basePath . DIRECTORY_SEPARATOR . $folder;
        abort_if(!is_dir($target), 404);

        $allowedExt = ['jpg','jpeg','png','webp','gif'];
        $images = [];

        foreach (File::allFiles($target) as $file) {
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, $allowedExt, true)) continue;

            // rel within images folder
            $rel = ltrim(str_replace($basePath, '', $file->getPathname()), DIRECTORY_SEPARATOR);
            $rel = str_replace(DIRECTORY_SEPARATOR, '/', $rel);

            $images[] = $baseUrl . '/' . $rel;
        }

        sort($images);

        return view('admin.images.folder', compact('folder', 'images'));
    }
}
