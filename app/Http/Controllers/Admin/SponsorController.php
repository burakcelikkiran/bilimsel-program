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

class SponsorController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of sponsors
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
