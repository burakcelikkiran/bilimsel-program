<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramSessionCategory;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProgramSessionCategoryController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of categories
     */
    public function index(Request $request): Response
    {
        $organizationId = auth()->user()->currentOrganization->id;
        
        $categories = ProgramSessionCategory::query()
            ->where('organization_id', $organizationId)
            ->withCount('programSessions')
            ->when($request->search, function ($query, $search) {
                $query->search($search);
            })
            ->when($request->status, function ($query, $status) {
                if ($status === 'active') {
                    $query->active();
                } elseif ($status === 'inactive') {
                    $query->inactive();
                }
            })
            ->orderBy($request->sort ?? 'sort_order', $request->direction ?? 'asc')
            ->paginate($request->per_page ?? 15)
            ->withQueryString();

        $stats = [
            'total' => ProgramSessionCategory::where('organization_id', $organizationId)->count(),
            'active' => ProgramSessionCategory::where('organization_id', $organizationId)->active()->count(),
            'used' => ProgramSessionCategory::where('organization_id', $organizationId)
                        ->has('programSessions')->count(),
        ];

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'sort', 'direction', 'per_page']),
        ]);
    }

    /**
     * Show the form for creating a new category
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Categories/Create');
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request): RedirectResponse
    {
        $organizationId = auth()->user()->currentOrganization->id;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('program_session_categories')->where('organization_id', $organizationId),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('program_session_categories')->where('organization_id', $organizationId),
            ],
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = ProgramSessionCategory::generateUniqueSlug($validated['name'], $organizationId);
        }

        $validated['organization_id'] = $organizationId;
        $validated['is_active'] = $validated['is_active'] ?? true;

        try {
            DB::beginTransaction();
            
            $category = ProgramSessionCategory::create($validated);
            
            DB::commit();

            return redirect()
                ->route('admin.categories.index')
                ->with('success', "Kategori '{$category->name}' başarıyla oluşturuldu.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Kategori oluşturulurken bir hata oluştu: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified category
     */
    public function show(ProgramSessionCategory $category): Response
    {
        $this->authorize('view', $category);

        $category->load([
            'programSessions' => function ($query) {
                $query->with(['event', 'venue', 'eventDay'])
                      ->orderBy('start_time');
            }
        ]);

        $stats = [
            'total_sessions' => $category->programSessions()->count(),
            'upcoming_sessions' => $category->programSessions()
                ->whereHas('event', function ($query) {
                    $query->where('end_date', '>=', now()->toDateString());
                })
                ->count(),
            'total_events' => $category->programSessions()
                ->distinct('event_id')
                ->count('event_id'),
        ];

        return Inertia::render('Admin/Categories/Show', [
            'category' => $category,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit(ProgramSessionCategory $category): Response
    {
        $this->authorize('update', $category);

        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, ProgramSessionCategory $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('program_session_categories')
                    ->where('organization_id', $category->organization_id)
                    ->ignore($category->id),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('program_session_categories')
                    ->where('organization_id', $category->organization_id)
                    ->ignore($category->id),
            ],
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = ProgramSessionCategory::generateUniqueSlug($validated['name'], $category->organization_id, $category->id);
        }

        try {
            DB::beginTransaction();
            
            $category->update($validated);
            
            DB::commit();

            return redirect()
                ->route('admin.categories.index')
                ->with('success', "Kategori '{$category->name}' başarıyla güncellendi.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Kategori güncellenirken bir hata oluştu: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified category
     */
    public function destroy(ProgramSessionCategory $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        if ($category->programSessions()->exists()) {
            return back()->withErrors([
                'error' => "'{$category->name}' kategorisi silinemez çünkü bu kategoriye ait program oturumları bulunmaktadır."
            ]);
        }

        try {
            DB::beginTransaction();
            
            $categoryName = $category->name;
            $category->delete();
            
            DB::commit();

            return redirect()
                ->route('admin.categories.index')
                ->with('success', "Kategori '{$categoryName}' başarıyla silindi.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'error' => 'Kategori silinirken bir hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Duplicate the specified category
     */
    public function duplicate(ProgramSessionCategory $category): RedirectResponse
    {
        $this->authorize('create', ProgramSessionCategory::class);

        try {
            DB::beginTransaction();
            
            $duplicatedCategory = $category->duplicate();
            
            DB::commit();

            return redirect()
                ->route('admin.categories.edit', $duplicatedCategory)
                ->with('success', "Kategori '{$category->name}' kopyalandı.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'error' => 'Kategori kopyalanırken bir hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Toggle category status
     */
    public function toggleStatus(ProgramSessionCategory $category): RedirectResponse
    {
        $this->authorize('update', $category);

        try {
            DB::beginTransaction();
            
            $category->update([
                'is_active' => !$category->is_active
            ]);
            
            DB::commit();

            $status = $category->is_active ? 'aktif' : 'pasif';
            
            return back()->with('success', "Kategori '{$category->name}' durumu {$status} olarak değiştirildi.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'error' => 'Durum değiştirilirken bir hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Bulk update sort orders
     */
    public function updateSortOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:program_session_categories,id',
            'categories.*.sort_order' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();
            
            foreach ($validated['categories'] as $categoryData) {
                $category = ProgramSessionCategory::find($categoryData['id']);
                $this->authorize('update', $category);
                
                $category->update(['sort_order' => $categoryData['sort_order']]);
            }
            
            DB::commit();

            return back()->with('success', 'Kategori sıralaması güncellendi.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'error' => 'Sıralama güncellenirken bir hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get categories for select options (AJAX)
     */
    public function getForSelect(Request $request)
    {
        $organizationId = auth()->user()->currentOrganization->id;
        
        $categories = ProgramSessionCategory::query()
            ->where('organization_id', $organizationId)
            ->active()
            ->when($request->search, function ($query, $search) {
                $query->search($search);
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'color', 'icon'])
            ->map(function ($category) {
                return [
                    'value' => $category->id,
                    'label' => $category->name,
                    'color' => $category->color,
                    'icon' => $category->icon,
                ];
            });

        return response()->json($categories);
    }
}