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
}
