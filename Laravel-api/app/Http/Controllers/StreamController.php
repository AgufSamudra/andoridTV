<?php

namespace App\Http\Controllers;

use App\Http\Resources\StreamDetailResource;
use App\Models\Stream;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\StreamResource;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Cloudinary\Asset\Image;



class StreamController extends Controller
{
    public function index()
    {
        $stream = Stream::all();
        $streamResource = StreamResource::collection($stream);

        $responseData = $streamResource->toArray(request());

        $responseData = [
            'event' => 'List URL Video',
            'data' => $responseData
        ];

        return response()->json($responseData);
    }

    public function store(Request $request)
    {

        $cloudinary = Cloudinary::class;

        // Validation: Update the validation rules to include mime types for images
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                Rule::unique('streams')->where(function ($query) use ($request) {
                    return $query->where('name', $request->input('name'))
                        ->where('title', $request->input('title'))
                        ->where('link', $request->input('link'))
                        ->where('category', $request->input('category'));
                }),
            ],
            'title' => 'required',
            'link' => 'required',
            'category' => 'required',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $image = $request->file('thumbnail');
        $response = $cloudinary->uploadApi()->upload($image->getRealPath(), ['folder' => 'festivals']);

        // Get the secure URL of the uploaded file
        $thumbnailUrl = $response['secure_url'];

        $stream = new Stream();
        $stream->title = $request->input('title');
        $stream->code_id = $request->input('code_id');
        $stream->mentor_id = $request->input('mentor_id');
        $stream->name = $request->input('name');
        $stream->category = $request->input('category');
        $stream->link = $request->input('link');
        $stream->thumbnail = $thumbnailUrl;
        $stream->save();

        return response()->json($stream, 201);
    }

    public function show($code_id)
    {
        $stream = Stream::where('code_id', $code_id)->first();

        if ($stream) {
            $streamDetailResource = new StreamDetailResource($stream);
            $response = $streamDetailResource->toArray(request());

            return response()->json(['data' => $response], 200);
        } else {

            return response()->json(['error' => 'Masukan CODE_ID Dengan Benar'], 404);
        }
    }




public function destroy($code_id)
{
    // Cari Stream berdasarkan ID
    $stream = Stream::where('code_id', $code_id)->first();

    // Jika Stream tidak ditemukan, kembalikan response error
    if (!$stream) {
        return response()->json(['error' => 'Stream not found'], 404);
    }


    // Hapus thumbnail di Cloudinary
    $publicId = Cloudinary::publicId($stream->thumbnail);
    $response = Cloudinary::uploadApi()->destroy($publicId);

    // Jika thumbnail berhasil dihapus di Cloudinary, hapus Stream dari database
    if ($response['result'] === 'ok') {
        $stream->delete();
        return response()->json(['message' => 'Stream deleted successfully']);
    } else {
        return response()->json(['error' => 'Failed to delete Stream'], 500);
    }
}

}

