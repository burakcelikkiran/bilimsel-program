<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Intervention\Image\Facades\Image;

class FileUploadController extends Controller
{
    use AuthorizesRequests;

    /**
     * Upload participant photo
     */
    public function participantPhoto(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'participant_id' => 'required|exists:participants,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz dosya.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $participant = \App\Models\Participant::find($request->participant_id);
            
            // Check if user can upload for this participant
            if ($participant->organization_id !== auth()->user()->currentOrganization->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu katılımcı için dosya yükleyemezsiniz.'
                ], 403);
            }

            $file = $request->file('file');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = 'participants/photos/' . $filename;

            // Resize and optimize image
            $image = Image::make($file)
                ->resize(400, 400, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->encode('jpg', 85);

            Storage::disk('public')->put($path, $image->stream());

            // Delete old photo if exists
            if ($participant->photo_path) {
                Storage::disk('public')->delete($participant->photo_path);
            }

            // Update participant
            $participant->update(['photo_path' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Fotoğraf başarıyla yüklendi.',
                'data' => [
                    'path' => $path,
                    'url' => Storage::url($path)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dosya yüklenirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload sponsor logo
     */
    public function sponsorLogo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'sponsor_id' => 'required|exists:sponsors,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz dosya.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sponsor = \App\Models\Sponsor::find($request->sponsor_id);
            
            // Check permissions
            if ($sponsor->organization_id !== auth()->user()->currentOrganization->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu sponsor için dosya yükleyemezsiniz.'
                ], 403);
            }

            $file = $request->file('file');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = 'sponsors/logos/' . $filename;

            // Handle SVG files differently
            if ($file->getClientOriginalExtension() === 'svg') {
                Storage::disk('public')->put($path, file_get_contents($file));
            } else {
                // Resize and optimize image
                $image = Image::make($file)
                    ->resize(300, 150, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->encode('png', 90);

                Storage::disk('public')->put($path, $image->stream());
            }

            // Delete old logo if exists
            if ($sponsor->logo_path) {
                Storage::disk('public')->delete($sponsor->logo_path);
            }

            // Update sponsor
            $sponsor->update(['logo_path' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Logo başarıyla yüklendi.',
                'data' => [
                    'path' => $path,
                    'url' => Storage::url($path)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dosya yüklenirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload presentation file
     */
    public function presentationFile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,ppt,pptx,doc,docx|max:10240',
            'presentation_id' => 'required|exists:presentations,id',
            'file_type' => 'required|in:presentation,document,abstract',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz dosya.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $presentation = \App\Models\Presentation::with('programSession.event')->find($request->presentation_id);
            
            // Check permissions
            if ($presentation->programSession->event->organization_id !== auth()->user()->currentOrganization->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu sunum için dosya yükleyemezsiniz.'
                ], 403);
            }

            $file = $request->file('file');
            $fileType = $request->file_type;
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            
            $filename = time() . '_' . Event::createSlugFromTurkish($originalName) . '.' . $extension;
            $path = "presentations/{$fileType}s/" . $filename;

            // Store file
            $file->storeAs('public/' . dirname($path), basename($path));

            // Update presentation with file path
            $fieldName = $fileType . '_file_path';
            $presentation->update([$fieldName => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Dosya başarıyla yüklendi.',
                'data' => [
                    'path' => $path,
                    'url' => Storage::url($path),
                    'filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'type' => $fileType
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dosya yüklenirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload event banner/poster
     */
    public function eventBanner(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'event_id' => 'required|exists:events,id',
            'banner_type' => 'required|in:banner,poster,logo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz dosya.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $event = \App\Models\Event::find($request->event_id);
            
            // Check permissions
            if ($event->organization_id !== auth()->user()->currentOrganization->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu etkinlik için dosya yükleyemezsiniz.'
                ], 403);
            }

            $file = $request->file('file');
            $bannerType = $request->banner_type;
            $filename = time() . '_' . $bannerType . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = "events/{$bannerType}s/" . $filename;

            // Resize based on banner type
            $dimensions = [
                'banner' => [1200, 400],
                'poster' => [800, 1200],
                'logo' => [300, 300]
            ];

            $size = $dimensions[$bannerType];
            $image = Image::make($file)
                ->resize($size[0], $size[1], function ($constraint) use ($bannerType) {
                    $constraint->aspectRatio();
                    if ($bannerType !== 'logo') {
                        $constraint->upsize();
                    }
                })
                ->encode('jpg', 85);

            Storage::disk('public')->put($path, $image->stream());

            // Delete old file if exists
            $fieldName = $bannerType . '_path';
            if ($event->$fieldName) {
                Storage::disk('public')->delete($event->$fieldName);
            }

            // Update event
            $event->update([$fieldName => $path]);

            return response()->json([
                'success' => true,
                'message' => ucfirst($bannerType) . ' başarıyla yüklendi.',
                'data' => [
                    'path' => $path,
                    'url' => Storage::url($path),
                    'type' => $bannerType
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dosya yüklenirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete uploaded file
     */
    public function deleteFile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file_path' => 'required|string',
            'model_type' => 'required|in:participant,sponsor,presentation,event',
            'model_id' => 'required|integer',
            'field_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz parametreler.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $modelClass = 'App\\Models\\' . ucfirst($request->model_type);
            $model = $modelClass::find($request->model_id);

            if (!$model) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kayıt bulunamadı.'
                ], 404);
            }

            // Check permissions
            $organizationId = auth()->user()->currentOrganization->id;
            
            switch ($request->model_type) {
                case 'participant':
                case 'sponsor':
                    if ($model->organization_id !== $organizationId) {
                        return response()->json(['success' => false, 'message' => 'Yetkiniz yok.'], 403);
                    }
                    break;
                case 'presentation':
                    if ($model->programSession->event->organization_id !== $organizationId) {
                        return response()->json(['success' => false, 'message' => 'Yetkiniz yok.'], 403);
                    }
                    break;
                case 'event':
                    if ($model->organization_id !== $organizationId) {
                        return response()->json(['success' => false, 'message' => 'Yetkiniz yok.'], 403);
                    }
                    break;
            }

            // Delete file from storage
            if (Storage::disk('public')->exists($request->file_path)) {
                Storage::disk('public')->delete($request->file_path);
            }

            // Update model
            $model->update([$request->field_name => null]);

            return response()->json([
                'success' => true,
                'message' => 'Dosya başarıyla silindi.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dosya silinirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get file info
     */
    public function getFileInfo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file_path' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz parametreler.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $filePath = $request->file_path;
            
            if (!Storage::disk('public')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dosya bulunamadı.'
                ], 404);
            }

            $fileInfo = [
                'exists' => true,
                'url' => Storage::url($filePath),
                'size' => Storage::disk('public')->size($filePath),
                'last_modified' => Storage::disk('public')->lastModified($filePath),
                'mime_type' => Storage::disk('public')->mimeType($filePath),
            ];

            return response()->json([
                'success' => true,
                'data' => $fileInfo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dosya bilgisi alınırken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk upload files (for drag & drop multiple files)
     */
    public function bulkUpload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array|max:10',
            'files.*' => 'file|max:5120',
            'upload_type' => 'required|in:participant_photos,sponsor_logos,presentation_files',
            'target_folder' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz dosyalar.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $uploadType = $request->upload_type;
            $targetFolder = $request->target_folder ?? 'temp';
            $uploaded = [];
            $errors = [];

            foreach ($request->file('files') as $index => $file) {
                try {
                    $filename = time() . '_' . $index . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $path = $targetFolder . '/' . $filename;

                    // Validate file type based on upload type
                    switch ($uploadType) {
                        case 'participant_photos':
                        case 'sponsor_logos':
                            if (!in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'gif', 'svg'])) {
                                throw new \Exception('Geçersiz resim formatı');
                            }
                            break;
                        case 'presentation_files':
                            if (!in_array($file->getClientOriginalExtension(), ['pdf', 'ppt', 'pptx', 'doc', 'docx'])) {
                                throw new \Exception('Geçersiz dosya formatı');
                            }
                            break;
                    }

                    $file->storeAs('public/' . dirname($path), basename($path));

                    $uploaded[] = [
                        'original_name' => $file->getClientOriginalName(),
                        'stored_path' => $path,
                        'url' => Storage::url($path),
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ];

                } catch (\Exception $e) {
                    $errors[] = [
                        'file' => $file->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($uploaded) . ' dosya yüklendi, ' . count($errors) . ' hata.',
                'data' => [
                    'uploaded' => $uploaded,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Toplu yükleme sırasında bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }
}