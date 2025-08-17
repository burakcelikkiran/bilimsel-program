<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramSessionCategory;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Program Session Categories",
 *     description="Program session category management endpoints"
 * )
 */
class ProgramSessionCategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/program-session-categories",
     *     summary="List program session categories",
     *     description="Get a paginated list of program session categories with filtering and search",
     *     operationId="listProgramSessionCategories",
     *     tags={"Program Session Categories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in category name and description",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="event_id",
     *         in="query",
     *         description="Filter by event ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="color",
     *         in="query",
     *         description="Filter by color (hex format)",
     *         @OA\Schema(type="string", pattern="^#[0-9A-Fa-f]{6}$")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort field",
     *         @OA\Schema(
     *             type="string",
     *             enum={"name", "sort_order", "created_at", "program_sessions_count"},
     *             default="sort_order"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Sort direction",
     *         @OA\Schema(
     *             type="string",
     *             enum={"asc", "desc"},
     *             default="asc"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page (max 100)",
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/ProgramSessionCategory")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            $query = ProgramSessionCategory::with(['event.organization'])
                ->withCount('programSessions');

            // Apply user access restrictions
            if (!$user->isAdmin()) {
                $organizationIds = $user->organizations()->pluck('organizations.id');
                $query->whereHas('event', function ($q) use ($organizationIds) {
                    $q->whereIn('organization_id', $organizationIds);
                });
            }

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filter by event
            if ($request->filled('event_id')) {
                $query->where('event_id', $request->event_id);
            }

            // Filter by color
            if ($request->filled('color')) {
                $query->where('color', $request->color);
            }

            // Sort options
            $sortField = $request->get('sort', 'sort_order');
            $sortDirection = $request->get('direction', 'asc');
            
            $allowedSorts = ['name', 'sort_order', 'created_at', 'program_sessions_count'];
            if (in_array($sortField, $allowedSorts)) {
                $query->orderBy($sortField, $sortDirection);
            }

            // Pagination
            $perPage = min($request->get('per_page', 15), 100);
            $categories = $query->paginate($perPage);

            // Transform data
            $categories->getCollection()->transform(function ($category) {
                return $this->transformCategory($category);
            });

            return response()->json([
                'success' => true,
                'data' => $categories,
                'meta' => [
                    'total' => $categories->total(),
                    'per_page' => $categories->perPage(),
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Category listing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Kategoriler yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/program-session-categories",
     *     summary="Create a new program session category",
     *     description="Create a new program session category for an event",
     *     operationId="createProgramSessionCategory",
     *     tags={"Program Session Categories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ProgramSessionCategoryRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kategori başarıyla oluşturuldu."),
     *             @OA\Property(property="data", ref="#/components/schemas/ProgramSessionCategoryDetailed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Girilen bilgiler geçersiz."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="Kategori adı zorunludur.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have permission to create categories",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $this->authorize('create', ProgramSessionCategory::class);

            // Validate request data
            $validated = $this->validateCategoryData($request);

            DB::beginTransaction();

            // Set sort order if not provided
            if (!isset($validated['sort_order'])) {
                $maxOrder = ProgramSessionCategory::where('event_id', $validated['event_id'])
                    ->max('sort_order') ?? 0;
                $validated['sort_order'] = $maxOrder + 1;
            }

            $category = ProgramSessionCategory::create($validated);

            // Load relationships for response
            $category->load(['event.organization']);

            DB::commit();

            Log::info('Category created successfully', [
                'category_id' => $category->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kategori başarıyla oluşturuldu.',
                'data' => $this->transformCategoryDetailed($category)
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Girilen bilgiler geçersiz.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Category creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Kategori oluşturulurken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/program-session-categories/{category}",
     *     summary="Get a specific program session category",
     *     description="Retrieve detailed information about a specific program session category",
     *     operationId="getProgramSessionCategory",
     *     tags={"Program Session Categories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="category", ref="#/components/schemas/ProgramSessionCategoryDetailed"),
     *                 @OA\Property(
     *                     property="permissions",
     *                     type="object",
     *                     @OA\Property(property="can_edit", type="boolean"),
     *                     @OA\Property(property="can_delete", type="boolean")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have access to this category",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(ProgramSessionCategory $category): JsonResponse
    {
        try {
            $this->authorize('view', $category);

            $category->load([
                'event.organization',
                'programSessions' => function ($query) {
                    $query->with(['venue.eventDay', 'moderators', 'presentations.speakers'])
                          ->orderBy('start_time');
                }
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'category' => $this->transformCategoryDetailed($category),
                    'permissions' => [
                        'can_edit' => auth()->user()->can('update', $category),
                        'can_delete' => auth()->user()->can('delete', $category),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Category show failed', [
                'category_id' => $category->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Kategori detayları yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/program-session-categories/{category}",
     *     summary="Update a program session category",
     *     description="Update an existing program session category",
     *     operationId="updateProgramSessionCategory",
     *     tags={"Program Session Categories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ProgramSessionCategoryRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kategori başarıyla güncellendi."),
     *             @OA\Property(property="data", ref="#/components/schemas/ProgramSessionCategoryDetailed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Girilen bilgiler geçersiz."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have permission to update this category",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function update(Request $request, ProgramSessionCategory $category): JsonResponse
    {
        try {
            $this->authorize('update', $category);

            // Validate request data
            $validated = $this->validateCategoryData($request, $category->id);

            DB::beginTransaction();

            $category->update($validated);

            // Load relationships for response
            $category->load(['event.organization']);

            DB::commit();

            Log::info('Category updated successfully', [
                'category_id' => $category->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kategori başarıyla güncellendi.',
                'data' => $this->transformCategoryDetailed($category)
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Girilen bilgiler geçersiz.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Category update failed', [
                'category_id' => $category->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Kategori güncellenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/program-session-categories/{category}",
     *     summary="Delete a program session category",
     *     description="Delete an existing program session category (only if no program sessions are assigned)",
     *     operationId="deleteProgramSessionCategory",
     *     tags={"Program Session Categories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kategori 'Category Name' başarıyla silindi.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete category with assigned program sessions",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Kategori silinemez çünkü bu kategoriye ait program oturumları bulunmaktadır.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have permission to delete this category",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function destroy(ProgramSessionCategory $category): JsonResponse
    {
        try {
            $this->authorize('delete', $category);

            // Check if category has any program sessions
            if ($category->programSessions()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori silinemez çünkü bu kategoriye ait program oturumları bulunmaktadır.'
                ], 422);
            }

            DB::beginTransaction();

            $categoryName = $category->name;
            $category->delete();

            DB::commit();

            Log::info('Category deleted successfully', [
                'category_id' => $category->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Kategori '{$categoryName}' başarıyla silindi."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Category deletion failed', [
                'category_id' => $category->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Kategori silinirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/program-session-categories/{category}/duplicate",
     *     summary="Duplicate a program session category",
     *     description="Create a copy of an existing program session category",
     *     operationId="duplicateProgramSessionCategory",
     *     tags={"Program Session Categories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         description="Category ID to duplicate",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category duplicated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kategori başarıyla kopyalandı."),
     *             @OA\Property(property="data", ref="#/components/schemas/ProgramSessionCategoryDetailed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have permission to create categories or view this category",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function duplicate(ProgramSessionCategory $category): JsonResponse
    {
        try {
            $this->authorize('create', ProgramSessionCategory::class);
            $this->authorize('view', $category);

            DB::beginTransaction();

            $newCategory = $category->replicate();
            $newCategory->name = $category->name . ' (Kopya)';
            
            // Get new sort order
            $maxOrder = ProgramSessionCategory::where('event_id', $category->event_id)
                ->max('sort_order') ?? 0;
            $newCategory->sort_order = $maxOrder + 1;
            
            $newCategory->save();

            // Load relationships for response
            $newCategory->load(['event.organization']);

            DB::commit();

            Log::info('Category duplicated successfully', [
                'original_id' => $category->id,
                'new_id' => $newCategory->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kategori başarıyla kopyalandı.',
                'data' => $this->transformCategoryDetailed($newCategory)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Category duplication failed', [
                'category_id' => $category->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Kategori kopyalanırken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/program-session-categories/sort-order",
     *     summary="Update sort order for multiple categories",
     *     description="Update the sort order for multiple program session categories at once",
     *     operationId="updateCategoriesSortOrder",
     *     tags={"Program Session Categories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"categories"},
     *             @OA\Property(
     *                 property="categories",
     *                 type="array",
     *                 description="Array of categories with their new sort orders",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"id", "sort_order"},
     *                     @OA\Property(property="id", type="integer", description="Category ID", example=1),
     *                     @OA\Property(property="sort_order", type="integer", minimum=0, description="New sort order", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sort order updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kategori sıralaması başarıyla güncellendi.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function updateSortOrder(Request $request): JsonResponse
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:program_session_categories,id',
            'categories.*.sort_order' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $user = auth()->user();
            
            foreach ($request->categories as $categoryData) {
                $category = ProgramSessionCategory::find($categoryData['id']);
                
                // Check authorization
                if (!$user->can('update', $category)) {
                    continue;
                }

                $category->update(['sort_order' => $categoryData['sort_order']]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Kategori sıralaması başarıyla güncellendi.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Category sort order update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sıralama güncellenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/program-session-categories/for-select",
     *     summary="Get categories for select dropdown",
     *     description="Get a simplified list of categories for use in select dropdowns",
     *     operationId="getCategoriesForSelect",
     *     tags={"Program Session Categories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="event_id",
     *         in="query",
     *         description="Filter by event ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in category names",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="value", type="integer", description="Category ID", example=1),
     *                     @OA\Property(property="label", type="string", description="Category name", example="Keynote"),
     *                     @OA\Property(property="color", type="string", description="Category color", example="#FF5733"),
     *                     @OA\Property(property="sort_order", type="integer", description="Sort order", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function getForSelect(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $query = ProgramSessionCategory::query();

            // Apply user access restrictions
            if (!$user->isAdmin()) {
                $organizationIds = $user->organizations()->pluck('organizations.id');
                $query->whereHas('event', function ($q) use ($organizationIds) {
                    $q->whereIn('organization_id', $organizationIds);
                });
            }

            // Event filter
            if ($request->filled('event_id')) {
                $query->where('event_id', $request->event_id);
            }

            // Search filter
            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $categories = $query->orderBy('sort_order')
                ->orderBy('name')
                ->limit(50)
                ->get(['id', 'name', 'color', 'sort_order']);

            return response()->json([
                'success' => true,
                'data' => $categories->map(function ($category) {
                    return [
                        'value' => $category->id,
                        'label' => $category->name,
                        'color' => $category->color,
                        'sort_order' => $category->sort_order,
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Categories could not be loaded.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate category data
     *
     * @param Request $request
     * @param int|null $categoryId
     * @return array
     * @throws ValidationException
     */
    private function validateCategoryData(Request $request, ?int $categoryId = null): array
    {
        $rules = [
            'event_id' => [
                'required',
                'exists:events,id',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    if (!$user->isAdmin()) {
                        $event = Event::find($value);
                        if (!$event) {
                            $fail('Seçilen etkinlik geçersiz.');
                            return;
                        }
                        
                        $organizationIds = $user->organizations()->pluck('organizations.id');
                        if (!$organizationIds->contains($event->organization_id)) {
                            $fail('Bu etkinliğe kategori ekleyemezsiniz.');
                        }
                    }
                },
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];

        // Add uniqueness check for name within the same event
        if ($categoryId) {
            $rules['name'] .= '|unique:program_session_categories,name,' . $categoryId . ',id,event_id,' . $request->event_id;
        } else {
            $rules['name'] .= '|unique:program_session_categories,name,NULL,id,event_id,' . $request->event_id;
        }

        $messages = [
            'event_id.required' => 'Etkinlik seçimi zorunludur.',
            'event_id.exists' => 'Seçilen etkinlik geçersiz.',
            'name.required' => 'Kategori adı zorunludur.',
            'name.max' => 'Kategori adı en fazla 255 karakter olabilir.',
            'name.unique' => 'Bu etkinlikte aynı isimde bir kategori zaten var.',
            'description.max' => 'Açıklama en fazla 1000 karakter olabilir.',
            'color.required' => 'Renk seçimi zorunludur.',
            'color.regex' => 'Renk geçerli hex formatında olmalıdır (#RRGGBB).',
            'sort_order.integer' => 'Sıralama sayı olmalıdır.',
            'sort_order.min' => 'Sıralama 0 veya daha büyük olmalıdır.',
        ];

        $validated = $request->validate($rules, $messages);

        // Set default values
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        return $validated;
    }

    /**
     * Transform category for API response
     *
     * @param ProgramSessionCategory $category
     * @return array
     */
    private function transformCategory(ProgramSessionCategory $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'color' => $category->color,
            'sort_order' => $category->sort_order,
            'is_active' => $category->is_active ?? true,
            'event' => [
                'id' => $category->event->id,
                'name' => $category->event->name,
                'slug' => $category->event->slug,
                'organization' => [
                    'id' => $category->event->organization->id,
                    'name' => $category->event->organization->name,
                ]
            ],
            'program_sessions_count' => $category->program_sessions_count ?? 0,
            'permissions' => [
                'can_edit' => auth()->user()?->can('update', $category) ?? false,
                'can_delete' => auth()->user()?->can('delete', $category) ?? false,
            ],
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
        ];
    }

    /**
     * Transform category with detailed information
     *
     * @param ProgramSessionCategory $category
     * @return array
     */
    private function transformCategoryDetailed(ProgramSessionCategory $category): array
    {
        $basic = $this->transformCategory($category);
        
        if ($category->relationLoaded('programSessions')) {
            $basic['program_sessions'] = $category->programSessions->map(function ($session) {
                return [
                    'id' => $session->id,
                    'title' => $session->title,
                    'start_time' => $session->start_time,
                    'end_time' => $session->end_time,
                    'session_type' => $session->session_type,
                    'venue' => $session->venue ? [
                        'id' => $session->venue->id,
                        'name' => $session->venue->display_name ?? $session->venue->name,
                        'event_day' => $session->venue->eventDay ? [
                            'id' => $session->venue->eventDay->id,
                            'title' => $session->venue->eventDay->title,
                            'date' => $session->venue->eventDay->date,
                        ] : null
                    ] : null,
                    'moderators_count' => $session->moderators->count(),
                    'presentations_count' => $session->presentations->count(),
                ];
            });
        }
        
        return $basic;
    }
}

/**
 * @OA\Schema(
 *     schema="ProgramSessionCategoryRequest",
 *     type="object",
 *     required={"event_id", "name", "color"},
 *     @OA\Property(
 *         property="event_id",
 *         type="integer",
 *         description="Event ID that this category belongs to",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         description="Category name",
 *         example="Keynote Sessions"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         maxLength=1000,
 *         nullable=true,
 *         description="Category description",
 *         example="Main presentation sessions featuring industry leaders"
 *     ),
 *     @OA\Property(
 *         property="color",
 *         type="string",
 *         pattern="^#[0-9A-Fa-f]{6}$",
 *         description="Category color in hex format",
 *         example="#FF5733"
 *     ),
 *     @OA\Property(
 *         property="sort_order",
 *         type="integer",
 *         minimum=0,
 *         nullable=true,
 *         description="Sort order for category display",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="is_active",
 *         type="boolean",
 *         nullable=true,
 *         description="Whether the category is active",
 *         example=true
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="ProgramSessionCategory",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Keynote Sessions"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Main presentation sessions"),
 *     @OA\Property(property="color", type="string", example="#FF5733"),
 *     @OA\Property(property="sort_order", type="integer", example=1),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(
 *         property="event",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Tech Conference 2024"),
 *         @OA\Property(property="slug", type="string", example="tech-conference-2024"),
 *         @OA\Property(
 *             property="organization",
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="TechCorp")
 *         )
 *     ),
 *     @OA\Property(property="program_sessions_count", type="integer", example=5),
 *     @OA\Property(
 *         property="permissions",
 *         type="object",
 *         @OA\Property(property="can_edit", type="boolean", example=true),
 *         @OA\Property(property="can_delete", type="boolean", example=true)
 *     ),
 *     @OA\Property(property="created_at", type="string", format="datetime", example="2024-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="datetime", example="2024-01-01T00:00:00Z")
 * )
 */

/**
 * @OA\Schema(
 *     schema="ProgramSessionCategoryDetailed",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ProgramSessionCategory"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(
 *                 property="program_sessions",
 *                 type="array",
 *                 nullable=true,
 *                 description="Program sessions in this category",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="title", type="string", example="Opening Keynote"),
 *                     @OA\Property(property="start_time", type="string", format="datetime", example="2024-01-01T09:00:00Z"),
 *                     @OA\Property(property="end_time", type="string", format="datetime", example="2024-01-01T10:00:00Z"),
 *                     @OA\Property(property="session_type", type="string", example="keynote"),
 *                     @OA\Property(
 *                         property="venue",
 *                         type="object",
 *                         nullable=true,
 *                         @OA\Property(property="id", type="integer", example=1),
 *                         @OA\Property(property="name", type="string", example="Main Hall"),
 *                         @OA\Property(
 *                             property="event_day",
 *                             type="object",
 *                             nullable=true,
 *                             @OA\Property(property="id", type="integer", example=1),
 *                             @OA\Property(property="title", type="string", example="Day 1"),
 *                             @OA\Property(property="date", type="string", format="date", example="2024-01-01")
 *                         )
 *                     ),
 *                     @OA\Property(property="moderators_count", type="integer", example=1),
 *                     @OA\Property(property="presentations_count", type="integer", example=3)
 *                 )
 *             )
 *         )
 *     }
 * )
 */