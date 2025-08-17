<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Sponsor;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * @OA\Tag(
 *     name="Sponsors",
 *     description="API endpoints for managing sponsors"
 * )
 */
class SponsorController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of sponsors
     * 
     * @OA\Get(
     *     path="/admin/sponsors",
     *     operationId="getSponsors",
     *     tags={"Sponsors"},
     *     summary="Get list of sponsors",
     *     description="Returns a paginated list of sponsors with filtering and search capabilities",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="organization_id",
     *         in="query",
     *         description="Filter by organization ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sponsor_level",
     *         in="query",
     *         description="Filter by sponsor level",
     *         required=false,
     *         @OA\Schema(type="string", enum={"platinum", "gold", "silver", "bronze"})
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "inactive"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for sponsor name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort field",
     *         required=false,
     *         @OA\Schema(type="string", enum={"name", "sponsor_level", "created_at", "sort_order"}, default="name")
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Sort direction",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="asc")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="sponsors", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Sponsor")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             ),
     *             @OA\Property(property="stats", ref="#/components/schemas/SponsorStats"),
     *             @OA\Property(property="organizations", type="array", @OA\Items(ref="#/components/schemas/Organization")),
     *             @OA\Property(property="filters", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();

        $query = Sponsor::with(['organization'])
            ->withCount(['programSessions', 'presentations']);

        // Apply user access restrictions
        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $query->whereIn('organization_id', $organizationIds);
        }

        // Filter by organization
        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        // Filter by sponsor level
        if ($request->filled('sponsor_level')) {
            $query->where('sponsor_level', $request->sponsor_level);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->inactive();
            }
        }

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Sort options
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');

        $validSortFields = ['name', 'sponsor_level', 'created_at', 'sort_order'];
        if (in_array($sortField, $validSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $sponsors = $query->paginate($request->per_page ?? 15)->withQueryString();

        // Get statistics
        $stats = [
            'total' => Sponsor::count(),
            'active' => Sponsor::active()->count(),
            'by_level' => [
                'platinum' => Sponsor::where('sponsor_level', 'platinum')->count(),
                'gold' => Sponsor::where('sponsor_level', 'gold')->count(),
                'silver' => Sponsor::where('sponsor_level', 'silver')->count(),
                'bronze' => Sponsor::where('sponsor_level', 'bronze')->count(),
            ],
        ];

        // Get organizations for filter dropdown
        $organizations = $user->isAdmin()
            ? Organization::orderBy('name')->get(['id', 'name'])
            : $user->organizations()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Sponsors/Index', [
            'sponsors' => $sponsors,
            'stats' => $stats,
            'organizations' => $organizations,
            'filters' => $request->only(['search', 'organization_id', 'sponsor_level', 'status', 'sort', 'direction', 'per_page']),
        ]);
    }

    /**
     * Show the form for creating a new sponsor
     * 
     * @OA\Get(
     *     path="/admin/sponsors/create",
     *     operationId="createSponsorForm",
     *     tags={"Sponsors"},
     *     summary="Show sponsor creation form",
     *     description="Returns the sponsor creation form with required data",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="organizations", type="array", @OA\Items(ref="#/components/schemas/Organization"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function create(): Response
    {
        $user = auth()->user();

        $organizations = $user->isAdmin()
            ? Organization::orderBy('name')->get(['id', 'name'])
            : $user->organizations()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Sponsors/Create', [
            'organizations' => $organizations,
        ]);
    }

    /**
     * Store a newly created sponsor
     * 
     * @OA\Post(
     *     path="/admin/sponsors",
     *     operationId="storeSponsor",
     *     tags={"Sponsors"},
     *     summary="Create a new sponsor",
     *     description="Creates a new sponsor with the provided data",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="organization_id", type="integer", description="Organization ID"),
     *                 @OA\Property(property="name", type="string", maxLength=255, description="Sponsor name"),
     *                 @OA\Property(property="sponsor_level", type="string", enum={"platinum", "gold", "silver", "bronze"}, description="Sponsor level"),
     *                 @OA\Property(property="website", type="string", format="url", maxLength=500, description="Website URL"),
     *                 @OA\Property(property="contact_email", type="string", format="email", maxLength=255, description="Contact email"),
     *                 @OA\Property(property="logo", type="string", format="binary", description="Logo image file"),
     *                 @OA\Property(property="is_active", type="boolean", description="Active status")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to sponsors index with success message"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'organization_id' => [
                'required',
                'exists:organizations,id',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    if (!$user->isAdmin() && !$user->organizations()->where('organizations.id', $value)->exists()) {
                        $fail('Bu organizasyona sponsor ekleyemezsiniz.');
                    }
                },
            ],
            'name' => 'required|string|max:255',
            'sponsor_level' => 'required|in:platinum,gold,silver,bronze',
            'website' => 'nullable|url|max:500',
            'contact_email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        try {
            DB::beginTransaction();

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('sponsors/logos', 'public');
                $validated['logo'] = $logoPath;
            }

            $sponsor = Sponsor::create($validated);

            DB::commit();

            return redirect()
                ->route('admin.sponsors.index')
                ->with('success', "Sponsor '{$sponsor->name}' başarıyla oluşturuldu.");
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded file if exists
            if (isset($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => 'Sponsor oluşturulurken bir hata oluştu: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified sponsor
     * 
     * @OA\Get(
     *     path="/admin/sponsors/{sponsor}",
     *     operationId="showSponsor",
     *     tags={"Sponsors"},
     *     summary="Get sponsor details",
     *     description="Returns detailed information about a specific sponsor",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="sponsor",
     *         in="path",
     *         description="Sponsor ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="sponsor", ref="#/components/schemas/SponsorDetail"),
     *             @OA\Property(property="stats", ref="#/components/schemas/SponsorDetailStats")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sponsor not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function show(Sponsor $sponsor): Response
    {
        $this->authorize('view', $sponsor);

        // Doğru relationship yüklemeleri
        $sponsor->load([
            'organization',
            'programSessions.venue.eventDay.event',
            'presentations.programSession.venue.eventDay.event'
        ]);

        // Sponsor'un authorize edilebilir işlemlerini kontrol et
        $sponsor->can_edit = auth()->user()?->can('update', $sponsor) ?? false;
        $sponsor->can_delete = auth()->user()?->can('delete', $sponsor) ?? false;

        $stats = [
            'total_sessions' => $sponsor->programSessions()->count(),
            'total_presentations' => $sponsor->presentations()->count(),
            'upcoming_sessions' => $sponsor->programSessions()
                ->whereHas('venue.eventDay.event', function ($query) {
                    $query->where('end_date', '>=', now()->toDateString());
                })
                ->count(),
            'total_events' => $sponsor->programSessions()
                ->join('venues', 'program_sessions.venue_id', '=', 'venues.id')
                ->join('event_days', 'venues.event_day_id', '=', 'event_days.id')
                ->distinct('event_days.event_id')
                ->count('event_days.event_id'),
        ];

        return Inertia::render('Admin/Sponsors/Show', [
            'sponsor' => $sponsor,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified sponsor
     * 
     * @OA\Get(
     *     path="/admin/sponsors/{sponsor}/edit",
     *     operationId="editSponsorForm",
     *     tags={"Sponsors"},
     *     summary="Show sponsor edit form",
     *     description="Returns the sponsor edit form with current data",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="sponsor",
     *         in="path",
     *         description="Sponsor ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="sponsor", ref="#/components/schemas/Sponsor"),
     *             @OA\Property(property="organizations", type="array", @OA\Items(ref="#/components/schemas/Organization"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sponsor not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function edit(Sponsor $sponsor): Response
    {
        $this->authorize('update', $sponsor);

        $user = auth()->user();

        $organizations = $user->isAdmin()
            ? Organization::orderBy('name')->get(['id', 'name'])
            : $user->organizations()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Sponsors/Edit', [
            'sponsor' => $sponsor,
            'organizations' => $organizations,
        ]);
    }

    /**
     * Update the specified sponsor
     * 
     * @OA\Put(
     *     path="/admin/sponsors/{sponsor}",
     *     operationId="updateSponsor",
     *     tags={"Sponsors"},
     *     summary="Update sponsor",
     *     description="Updates the specified sponsor with provided data",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="sponsor",
     *         in="path",
     *         description="Sponsor ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="organization_id", type="integer", description="Organization ID"),
     *                 @OA\Property(property="name", type="string", maxLength=255, description="Sponsor name"),
     *                 @OA\Property(property="sponsor_level", type="string", enum={"platinum", "gold", "silver", "bronze"}, description="Sponsor level"),
     *                 @OA\Property(property="website", type="string", format="url", maxLength=500, description="Website URL"),
     *                 @OA\Property(property="contact_email", type="string", format="email", maxLength=255, description="Contact email"),
     *                 @OA\Property(property="logo", type="string", format="binary", description="Logo image file"),
     *                 @OA\Property(property="remove_logo", type="boolean", description="Remove existing logo"),
     *                 @OA\Property(property="is_active", type="boolean", description="Active status")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to sponsors index with success message"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sponsor not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function update(Request $request, Sponsor $sponsor): RedirectResponse
    {
        $this->authorize('update', $sponsor);

        $validated = $request->validate([
            'organization_id' => [
                'required',
                'exists:organizations,id',
                function ($attribute, $value, $fail) use ($sponsor) {
                    $user = auth()->user();
                    if (!$user->isAdmin() && !$user->organizations()->where('organizations.id', $value)->exists()) {
                        $fail('Bu organizasyona sponsor ekleyemezsiniz.');
                    }
                },
            ],
            'name' => 'required|string|max:255',
            'sponsor_level' => 'required|in:platinum,gold,silver,bronze',
            'website' => 'nullable|url|max:500',
            'contact_email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'remove_logo' => 'boolean',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $oldLogo = $sponsor->logo;

            // Handle logo removal
            if ($request->boolean('remove_logo')) {
                $validated['logo'] = null;
                if ($oldLogo) {
                    Storage::disk('public')->delete($oldLogo);
                }
            }

            // Handle new logo upload
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('sponsors/logos', 'public');
                $validated['logo'] = $logoPath;

                // Delete old logo if exists
                if ($oldLogo) {
                    Storage::disk('public')->delete($oldLogo);
                }
            }

            unset($validated['remove_logo']);

            $sponsor->update($validated);

            DB::commit();

            return redirect()
                ->route('admin.sponsors.index')
                ->with('success', "Sponsor '{$sponsor->name}' başarıyla güncellendi.");
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded file if exists
            if (isset($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => 'Sponsor güncellenirken bir hata oluştu: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified sponsor
     * 
     * @OA\Delete(
     *     path="/admin/sponsors/{sponsor}",
     *     operationId="deleteSponsor",
     *     tags={"Sponsors"},
     *     summary="Delete sponsor",
     *     description="Deletes the specified sponsor if it has no associated sessions or presentations",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="sponsor",
     *         in="path",
     *         description="Sponsor ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to sponsors index with success message"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cannot delete sponsor with associated data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sponsor not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function destroy(Sponsor $sponsor): RedirectResponse
    {
        $this->authorize('delete', $sponsor);

        // Check if sponsor has any program sessions or presentations
        if ($sponsor->programSessions()->exists() || $sponsor->presentations()->exists()) {
            return back()->withErrors([
                'error' => "'{$sponsor->name}' sponsoru silinemez çünkü bu sponsora ait program oturumları veya sunumları bulunmaktadır."
            ]);
        }

        try {
            DB::beginTransaction();

            $sponsorName = $sponsor->name;
            $logoPath = $sponsor->logo_path;

            $sponsor->delete();

            // Delete logo file if exists
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }

            DB::commit();

            return redirect()
                ->route('admin.sponsors.index')
                ->with('success', "Sponsor '{$sponsorName}' başarıyla silindi.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Sponsor silinirken bir hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Duplicate the specified sponsor
     * 
     * @OA\Post(
     *     path="/admin/sponsors/{sponsor}/duplicate",
     *     operationId="duplicateSponsor",
     *     tags={"Sponsors"},
     *     summary="Duplicate sponsor",
     *     description="Creates a copy of the specified sponsor",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="sponsor",
     *         in="path",
     *         description="Sponsor ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to edit form of duplicated sponsor"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sponsor not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function duplicate(Sponsor $sponsor): RedirectResponse
    {
        $this->authorize('create', Sponsor::class);

        try {
            DB::beginTransaction();

            $duplicatedSponsor = $sponsor->duplicate();

            DB::commit();

            return redirect()
                ->route('admin.sponsors.edit', $duplicatedSponsor)
                ->with('success', "Sponsor '{$sponsor->name}' kopyalandı.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Sponsor kopyalanırken bir hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Toggle sponsor status
     * 
     * @OA\Patch(
     *     path="/admin/sponsors/{sponsor}/toggle-status",
     *     operationId="toggleSponsorStatus",
     *     tags={"Sponsors"},
     *     summary="Toggle sponsor active status",
     *     description="Toggles the active status of the specified sponsor",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="sponsor",
     *         in="path",
     *         description="Sponsor ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Status toggled successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sponsor not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function toggleStatus(Sponsor $sponsor): RedirectResponse
    {
        $this->authorize('update', $sponsor);

        try {
            DB::beginTransaction();

            $sponsor->update([
                'is_active' => !$sponsor->is_active
            ]);

            DB::commit();

            $status = $sponsor->is_active ? 'aktif' : 'pasif';

            return back()->with('success', "Sponsor '{$sponsor->name}' durumu {$status} olarak değiştirildi.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Durum değiştirilirken bir hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Bulk update sort orders
     * 
     * @OA\Patch(
     *     path="/admin/sponsors/sort-order",
     *     operationId="updateSponsorSortOrder",
     *     tags={"Sponsors"},
     *     summary="Update sponsors sort order",
     *     description="Updates the sort order for multiple sponsors",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="sponsors", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", description="Sponsor ID"),
     *                     @OA\Property(property="sort_order", type="integer", description="Sort order")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Sort order updated successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function updateSortOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sponsors' => 'required|array',
            'sponsors.*.id' => 'required|exists:sponsors,id',
            'sponsors.*.sort_order' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['sponsors'] as $sponsorData) {
                $sponsor = Sponsor::find($sponsorData['id']);
                $this->authorize('update', $sponsor);

                $sponsor->update(['sort_order' => $sponsorData['sort_order']]);
            }

            DB::commit();

            return back()->with('success', 'Sponsor sıralaması güncellendi.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Sıralama güncellenirken bir hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get sponsors for select options (AJAX)
     * 
     * @OA\Get(
     *     path="/admin/sponsors/for-select",
     *     operationId="getSponsorsForSelect",
     *     tags={"Sponsors"},
     *     summary="Get sponsors for select dropdown",
     *     description="Returns sponsors formatted for use in select dropdowns",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="organization_id",
     *         in="query",
     *         description="Filter by organization ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="value", type="integer", description="Sponsor ID"),
     *                 @OA\Property(property="label", type="string", description="Sponsor name"),
     *                 @OA\Property(property="level", type="string", description="Sponsor level"),
     *                 @OA\Property(property="logo_url", type="string", nullable=true, description="Logo URL")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function getForSelect(Request $request)
    {
        $user = auth()->user();

        $query = Sponsor::query()->active();

        // Apply user access restrictions
        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $query->whereIn('organization_id', $organizationIds);
        }

        if ($request->organization_id) {
            $query->where('organization_id', $request->organization_id);
        }

        if ($request->search) {
            $query->search($request->search);
        }

        $sponsors = $query->orderBy('sponsor_level')
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'sponsor_level', 'logo'])
            ->map(function ($sponsor) {
                return [
                    'value' => $sponsor->id,
                    'label' => $sponsor->name,
                    'level' => $sponsor->sponsor_level,
                    'logo_url' => $sponsor->logo ? asset('storage/' . $sponsor->logo) : null,
                ];
            });

        return response()->json($sponsors);
    }

    /**
     * Get sponsor statistics
     * 
     * @OA\Get(
     *     path="/admin/sponsors/stats",
     *     operationId="getSponsorStats",
     *     tags={"Sponsors"},
     *     summary="Get sponsor statistics",
     *     description="Returns various statistics about sponsors",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="organization_id",
     *         in="query",
     *         description="Filter by organization ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SponsorDetailedStats")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function getStats(Request $request)
    {
        $user = auth()->user();

        $query = Sponsor::query();

        // Apply user access restrictions
        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $query->whereIn('organization_id', $organizationIds);
        }

        if ($request->organization_id) {
            $query->where('organization_id', $request->organization_id);
        }

        $stats = [
            'total' => $query->count(),
            'active' => $query->clone()->active()->count(),
            'inactive' => $query->clone()->inactive()->count(),
            'by_level' => [
                'platinum' => $query->clone()->where('sponsor_level', 'platinum')->count(),
                'gold' => $query->clone()->where('sponsor_level', 'gold')->count(),
                'silver' => $query->clone()->where('sponsor_level', 'silver')->count(),
                'bronze' => $query->clone()->where('sponsor_level', 'bronze')->count(),
            ],
            'with_logo' => $query->clone()->whereNotNull('logo_path')->count(),
            'with_website' => $query->clone()->whereNotNull('website_url')->count(),
        ];

        return response()->json($stats);
    }
}

/**
 * @OA\Schema(
 *     schema="Sponsor",
 *     type="object",
 *     title="Sponsor",
 *     description="Sponsor model",
 *     @OA\Property(property="id", type="integer", description="Sponsor ID"),
 *     @OA\Property(property="organization_id", type="integer", description="Organization ID"),
 *     @OA\Property(property="name", type="string", description="Sponsor name"),
 *     @OA\Property(property="sponsor_level", type="string", enum={"platinum", "gold", "silver", "bronze"}, description="Sponsor level"),
 *     @OA\Property(property="website", type="string", nullable=true, description="Website URL"),
 *     @OA\Property(property="contact_email", type="string", nullable=true, description="Contact email"),
 *     @OA\Property(property="logo", type="string", nullable=true, description="Logo path"),
 *     @OA\Property(property="is_active", type="boolean", description="Active status"),
 *     @OA\Property(property="sort_order", type="integer", description="Sort order"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Update date"),
 *     @OA\Property(property="organization", ref="#/components/schemas/Organization", description="Related organization"),
 *     @OA\Property(property="program_sessions_count", type="integer", description="Number of program sessions"),
 *     @OA\Property(property="presentations_count", type="integer", description="Number of presentations")
 * )
 */

/**
 * @OA\Schema(
 *     schema="SponsorDetail",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Sponsor"),
 *         @OA\Schema(
 *             @OA\Property(property="can_edit", type="boolean", description="User can edit this sponsor"),
 *             @OA\Property(property="can_delete", type="boolean", description="User can delete this sponsor"),
 *             @OA\Property(property="program_sessions", type="array", @OA\Items(ref="#/components/schemas/ProgramSession")),
 *             @OA\Property(property="presentations", type="array", @OA\Items(ref="#/components/schemas/Presentation"))
 *         )
 *     }
 * )
 */

/**
 * @OA\Schema(
 *     schema="SponsorStats",
 *     type="object",
 *     title="Sponsor Statistics",
 *     description="Basic sponsor statistics",
 *     @OA\Property(property="total", type="integer", description="Total sponsors"),
 *     @OA\Property(property="active", type="integer", description="Active sponsors"),
 *     @OA\Property(property="by_level", type="object",
 *         @OA\Property(property="platinum", type="integer", description="Platinum sponsors"),
 *         @OA\Property(property="gold", type="integer", description="Gold sponsors"),
 *         @OA\Property(property="silver", type="integer", description="Silver sponsors"),
 *         @OA\Property(property="bronze", type="integer", description="Bronze sponsors")
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="SponsorDetailStats",
 *     type="object",
 *     title="Sponsor Detail Statistics",
 *     description="Detailed statistics for a specific sponsor",
 *     @OA\Property(property="total_sessions", type="integer", description="Total program sessions"),
 *     @OA\Property(property="total_presentations", type="integer", description="Total presentations"),
 *     @OA\Property(property="upcoming_sessions", type="integer", description="Upcoming sessions"),
 *     @OA\Property(property="total_events", type="integer", description="Total events")
 * )
 */

/**
 * @OA\Schema(
 *     schema="SponsorDetailedStats",
 *     type="object",
 *     title="Detailed Sponsor Statistics",
 *     description="Comprehensive sponsor statistics",
 *     @OA\Property(property="total", type="integer", description="Total sponsors"),
 *     @OA\Property(property="active", type="integer", description="Active sponsors"),
 *     @OA\Property(property="inactive", type="integer", description="Inactive sponsors"),
 *     @OA\Property(property="by_level", type="object",
 *         @OA\Property(property="platinum", type="integer", description="Platinum sponsors"),
 *         @OA\Property(property="gold", type="integer", description="Gold sponsors"),
 *         @OA\Property(property="silver", type="integer", description="Silver sponsors"),
 *         @OA\Property(property="bronze", type="integer", description="Bronze sponsors")
 *     ),
 *     @OA\Property(property="with_logo", type="integer", description="Sponsors with logo"),
 *     @OA\Property(property="with_website", type="integer", description="Sponsors with website")
 * )
 */

/**
 * @OA\Schema(
 *     schema="Organization",
 *     type="object",
 *     title="Organization",
 *     description="Organization model",
 *     @OA\Property(property="id", type="integer", description="Organization ID"),
 *     @OA\Property(property="name", type="string", description="Organization name")
 * )
 */