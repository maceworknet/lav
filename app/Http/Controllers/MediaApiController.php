<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MediaApiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Media::latest();

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Sayfalama istenirse (medya kütüphanesi sayfası) sayfalı dön
        if ($request->filled('per_page')) {
            $paginated = $query->paginate((int) $request->input('per_page', 40));

            return response()->json([
                'media' => $paginated->items(),
                'total' => $paginated->total(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
            ]);
        }

        $media = $query->get();

        return response()->json([
            'media' => $media
        ]);
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|image|max:10240', // max 10MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            
            $extension = $file->getClientOriginalExtension();
            $cleanName = pathinfo($originalName, PATHINFO_FILENAME);
            $fileName = Str::slug($cleanName) . '-' . time() . '.' . $extension;
            
            $path = $file->storeAs('media', $fileName, 'public');
            
            $media = Media::create([
                'name' => $originalName,
                'file_path' => $path,
            ]);

            return response()->json([
                'success' => true,
                'media' => $media
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Dosya bulunamadı.'
        ], 400);
    }

    /**
     * Toplu veya tekil medya silme. Hem dosyayı hem kaydı kaldırır.
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $items = Media::whereIn('id', $request->input('ids'))->get();
        $deleted = 0;

        foreach ($items as $item) {
            if ($item->file_path && Storage::disk('public')->exists($item->file_path)) {
                Storage::disk('public')->delete($item->file_path);
            }
            $item->delete();
            $deleted++;
        }

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
        ]);
    }

    /**
     * Medya adını güncelle (kütüphane detay panelinden).
     */
    public function rename(Request $request, Media $media)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $media->update(['name' => $request->input('name')]);

        return response()->json([
            'success' => true,
            'media' => $media->fresh(),
        ]);
    }
}
