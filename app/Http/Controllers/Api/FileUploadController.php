<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileUploadController extends Controller
{
    public function __construct()
    {
        // Optional authentication is handled at route level
    }

    /**
     * Upload image for TinyMCE editor
     */
    public function uploadTinyMCEImage(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Max 5MB
        ], [
            'file.required' => 'لطفا یک تصویر انتخاب کنید',
            'file.image' => 'فایل انتخابی باید یک تصویر باشد',
            'file.mimes' => 'فرمت تصویر باید از نوع: jpeg, png, jpg, gif, webp باشد',
            'file.max' => 'حجم تصویر نباید بیشتر از 5 مگابایت باشد',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'خطا در اعتبارسنجی: ' . $validator->errors()->first()
            ], 422);
        }

        try {
            $image = $request->file('file');

            // Generate unique filename as recommended by TinyMCE docs
            $timestamp = time();
            $microseconds = substr(microtime(), 2, 6);
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $filename = $originalName . '_' . $timestamp . '_' . $microseconds . '.' . $extension;

            // Store the image in the 'editor-images' directory
            $path = $image->storeAs('editor-images', $filename, 'public');

            // Generate the full URL
            $fullUrl = asset('storage/' . $path);

            // Return the location as expected by TinyMCE
            // This MUST be a JSON object with a "location" property as per TinyMCE docs
            return response()->json([
                'location' => $fullUrl
            ], 200, [
                'Content-Type' => 'application/json'
            ]);

        } catch (\Exception $e) {
            // Return error in TinyMCE expected format
            return response()->json([
                'error' => 'خطا در آپلود تصویر: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload image for Quill editor
     */
    public function uploadQuillImage(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Max 5MB
        ], [
            'file.required' => 'لطفا یک تصویر انتخاب کنید',
            'file.image' => 'فایل انتخابی باید یک تصویر باشد',
            'file.mimes' => 'فرمت تصویر باید از نوع: jpeg, png, jpg, gif, webp باشد',
            'file.max' => 'حجم تصویر نباید بیشتر از 5 مگابایت باشد',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'خطا در اعتبارسنجی: ' . $validator->errors()->first()
            ], 422);
        }

        try {
            $image = $request->file('file');

            // Generate unique filename
            $timestamp = time();
            $microseconds = substr(microtime(), 2, 6);
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $filename = $originalName . '_' . $timestamp . '_' . $microseconds . '.' . $extension;

            // Store the image in the 'editor-images' directory
            $path = $image->storeAs('editor-images', $filename, 'public');

            // Generate the full URL
            $fullUrl = asset('storage/' . $path);

            // Return the location as expected by Quill
            return response()->json([
                'location' => $fullUrl
            ], 200, [
                'Content-Type' => 'application/json'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'خطا در آپلود تصویر: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload general file
     */
    public function uploadFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // Max 10MB
            'type' => 'sometimes|string|in:document,image,video,audio',
        ], [
            'file.required' => 'لطفا یک فایل انتخاب کنید',
            'file.file' => 'فایل انتخابی معتبر نیست',
            'file.max' => 'حجم فایل نباید بیشتر از 10 مگابایت باشد',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'خطا در اعتبارسنجی',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $type = $request->input('type', 'general');

            // Generate unique filename
            $timestamp = time();
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $filename = $originalName . '_' . $timestamp . '.' . $extension;

            // Determine storage directory based on type
            $directory = match($type) {
                'image' => 'images',
                'document' => 'documents',
                'video' => 'videos',
                'audio' => 'audio',
                default => 'files'
            };

            // Store the file
            $path = $file->storeAs($directory, $filename, 'public');
            $fullUrl = asset('storage/' . $path);

            return response()->json([
                'message' => 'فایل با موفقیت آپلود شد',
                'file_url' => $fullUrl,
                'file_path' => $path,
                'filename' => $filename,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'خطا در آپلود فایل: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete uploaded file
     */
    public function deleteFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_path' => 'required|string',
        ], [
            'file_path.required' => 'مسیر فایل الزامی است',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'خطا در اعتبارسنجی',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $filePath = $request->input('file_path');

            // Check if file exists and delete it
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);

                return response()->json([
                    'message' => 'فایل با موفقیت حذف شد'
                ]);
            } else {
                return response()->json([
                    'message' => 'فایل یافت نشد'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'خطا در حذف فایل: ' . $e->getMessage()
            ], 500);
        }
    }
}
