<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\WebpConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    private const DISK = 'public';

    public function __construct()
    {
        $this->middleware('role:admin|owner');
    }

    public function index(Request $request)
    {
        $type = in_array($request->query('type'), ['image', 'video'], true) ? $request->query('type') : null;
        $search = trim((string) $request->query('q', ''));

        $media = Media::query()
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($search !== '', fn ($q) => $q->where('original_name', 'like', '%' . $search . '%'))
            ->latest('id')
            ->paginate(24)
            ->withQueryString();

        return view('admin.media.index', [
            'media' => $media,
            'type' => $type,
            'search' => $search,
            'webpAvailable' => WebpConverter::available(),
        ]);
    }

    public function store(Request $request, WebpConverter $converter)
    {
        $request->validate([
            'files' => 'required|array|min:1',
            'files.*' => 'file|mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov|max:102400',
        ], [
            'files.*.mimes' => 'Only JPG, PNG, GIF, WebP images and MP4, WebM, MOV videos are allowed.',
            'files.*.max' => 'Each file may be at most 100 MB.',
            'files.*.uploaded' => 'A file failed to upload - it is probably larger than the server limit (upload_max_filesize / post_max_size).',
        ]);

        $toWebp = $request->boolean('convert_webp') && WebpConverter::available();
        $dir = 'media/' . now()->format('Y/m');
        $stored = 0;

        foreach ($request->file('files') as $file) {
            $isImage = Str::startsWith($file->getMimeType(), 'image/');
            $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file';
            $unique = Str::lower(Str::random(8));
            $width = $height = null;
            $webp = ($isImage && $toWebp) ? $converter->convert($file) : null;

            if ($webp) {
                $path = "$dir/$baseName-$unique.webp";
                Storage::disk(self::DISK)->put($path, $webp['binary']);
                $mime = 'image/webp';
                $size = strlen($webp['binary']);
                [$width, $height] = [$webp['width'], $webp['height']];
            } else {
                $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
                $path = Storage::disk(self::DISK)->putFileAs($dir, $file, "$baseName-$unique.$ext");
                $mime = $file->getMimeType();
                $size = $file->getSize();
                if ($isImage && ($info = @getimagesize($file->getRealPath()))) {
                    [$width, $height] = $info;
                }
            }

            Media::create([
                'user_id' => $request->user()->id,
                'type' => $isImage ? 'image' : 'video',
                'disk' => self::DISK,
                'path' => $path,
                'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
                'mime_type' => $mime,
                'size' => $size,
                'width' => $width,
                'height' => $height,
            ]);
            $stored++;
        }

        return redirect()->route('admin.media.index')
            ->with('success', $stored . ' ' . Str::plural('file', $stored) . ' uploaded.');
    }

    public function destroy(Media $medium)
    {
        $medium->delete();

        return back()->with('success', 'Media deleted.');
    }
}
