<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrganizationRequest;
use App\Http\Requests\Admin\UpdateOrganizationRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of organizations
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Organization::class);

        $user = auth()->user();

        $query = Organization::with(['users'])
            ->withCount(['events', 'participants', 'sponsors']);

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->inactive();
            }
        }

        // Sort options
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');

        $allowedSorts = ['name', 'created_at', 'events_count', 'participants_count'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $organizations = $query->paginate(15)
            ->withQueryString()
            ->through(function ($organization) {
                return [
                    'id' => $organization->id,
                    'name' => $organization->name,
                    'description' => $organization->description,
                    'logo_url' => $organization->logo_url,
                    'contact_email' => $organization->contact_email,
                    'contact_phone' => $organization->contact_phone,
                    'is_active' => $organization->is_active,
                    'created_at' => $organization->created_at,
                    'events_count' => $organization->events_count,
                    'participants_count' => $organization->participants_count,
                    'sponsors_count' => $organization->sponsors_count,
                    'users_count' => $organization->users->count(),
                    'can_edit' => auth()->user()?->can('update', $organization) ?? false,
                    'can_delete' => auth()->user()?->can('delete', $organization) ?? false,
                ];
            });

        return Inertia::render('Admin/Organizations/Index', [
            'organizations' => $organizations,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'sort' => $sortField,
                'direction' => $sortDirection,
            ],
            'can_create' => auth()->user()?->can('create', Organization::class) ?? false,
        ]);
    }

    /**
     * Show the form for creating a new organization
     */
    public function create(): Response
    {
        $this->authorize('create', Organization::class);

        return Inertia::render('Admin/Organizations/Create');
    }

    /**
     * Store a newly created organization
     */
    public function store(StoreOrganizationRequest $request)
    {
        $this->authorize('create', Organization::class);

        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $data['logo'] = $request->file('logo')->store('organizations', 'public');
            }

            $organization = Organization::create($data);

            // If current user is not admin, attach them as organizer
            if (!auth()->user()->isAdmin()) {
                $organization->users()->attach(auth()->id(), ['role' => 'organizer']);
            }

            DB::commit();

            return redirect()
                ->route('admin.organizations.show', $organization)
                ->with('success', 'Organizasyon başarıyla oluşturuldu.');
        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up uploaded file if exists
            if (isset($data['logo'])) {
                Storage::disk('public')->delete($data['logo']);
            }

            return back()
                ->withErrors(['error' => 'Organizasyon oluşturulurken bir hata oluştu.'])
                ->withInput();
        }
    }

    /**
     * Display the specified organization
     */
    public function show(Organization $organization): Response
    {
        $this->authorize('view', $organization);

        $organization->load([
            'users' => function ($query) {
                $query->withPivot('role')->orderBy('name');
            },
            'events' => function ($query) {
                $query->latest()->limit(5);
            },
            'participants' => function ($query) {
                $query->latest()->limit(10);
            },
            'sponsors' => function ($query) {
                $query->active()->latest();
            }
        ]);

        // Get organization statistics
        $statistics = [
            'total_events' => $organization->events()->count(),
            'published_events' => $organization->events()->published()->count(),
            'upcoming_events' => $organization->events()->upcoming()->count(),
            'total_participants' => $organization->participants()->count(),
            'active_sponsors' => $organization->sponsors()->active()->count(),
            'total_sessions' => $organization->events()
                ->with('eventDays.venues.programSessions')
                ->get()
                ->flatMap(function ($event) {
                    return $event->eventDays->flatMap(function ($day) {
                        return $day->venues->flatMap->programSessions;
                    });
                })
                ->count(),
        ];

        // Get all available users for assignment
        $availableUsers = User::active()
            ->whereDoesntHave('organizations', function ($query) use ($organization) {
                $query->where('organization_id', $organization->id);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('Admin/Organizations/Show', [
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'description' => $organization->description,
                'logo_url' => $organization->logo_url,
                'contact_email' => $organization->contact_email,
                'contact_phone' => $organization->contact_phone,
                'is_active' => $organization->is_active,
                'created_at' => $organization->created_at,
                'updated_at' => $organization->updated_at,
                'users' => $organization->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->pivot->role,
                        'joined_at' => $user->pivot->created_at,
                    ];
                }),
                'recent_events' => $organization->events->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'name' => $event->name,
                        'slug' => $event->slug,
                        'status' => $event->status,
                        'formatted_date_range' => $event->formatted_date_range,
                        'is_published' => $event->is_published,
                    ];
                }),
                'recent_participants' => $organization->participants->map(function ($participant) {
                    return [
                        'id' => $participant->id,
                        'full_name' => $participant->full_name,
                        'email' => $participant->email,
                        'affiliation' => $participant->affiliation,
                        'is_speaker' => $participant->is_speaker,
                        'is_moderator' => $participant->is_moderator,
                    ];
                }),
                'sponsors' => $organization->sponsors->map(function ($sponsor) {
                    return [
                        'id' => $sponsor->id,
                        'name' => $sponsor->name,
                        'sponsor_level' => $sponsor->sponsor_level,
                        'formatted_level' => $sponsor->formatted_level,
                        'logo_url' => $sponsor->logo_url,
                        'is_active' => $sponsor->is_active,
                    ];
                }),
            ],
            'statistics' => $statistics,
            'available_users' => $availableUsers,
            'can_edit' => auth()->user()?->can('update', $organization) ?? false,
            'can_delete' => auth()->user()?->can('delete', $organization) ?? false,
            'can_manage_users' => auth()->user()?->can('manageUsers', $organization) ?? false,
        ]);
    }

    /**
     * Show the form for editing organization
     */
    public function edit(Organization $organization): Response
    {
        $this->authorize('update', $organization);

        return Inertia::render('Admin/Organizations/Edit', [
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'description' => $organization->description,
                'logo_url' => $organization->logo_url,
                'contact_email' => $organization->contact_email,
                'contact_phone' => $organization->contact_phone,
                'is_active' => $organization->is_active,
            ],
        ]);
    }

    /**
     * Update the specified organization
     */
    public function update(UpdateOrganizationRequest $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        // Debug: Validated data'yı logla
        \Log::info('Validated data:', $request->validated());
        \Log::info('All request data:', $request->all());

        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Handle logo removal
            if ($request->boolean('_remove_logo') && $organization->logo) {
                Storage::disk('public')->delete($organization->logo);
                $data['logo'] = null;
            }

            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo
                if ($organization->logo) {
                    Storage::disk('public')->delete($organization->logo);
                }
                $data['logo'] = $request->file('logo')->store('organizations', 'public');
            }

            // Debug: Final data before update
            \Log::info('Final data before update:', $data);

            $organization->update($data);

            DB::commit();

            return redirect()
                ->route('admin.organizations.show', $organization)
                ->with('success', 'Organizasyon başarıyla güncellendi.');
        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error
            \Log::error('Organization update error:', [
                'error' => $e->getMessage(),
                'organization_id' => $organization->id,
                'request_data' => $request->all()
            ]);

            // Clean up uploaded file if exists
            if (isset($data['logo']) && $data['logo'] !== $organization->logo) {
                Storage::disk('public')->delete($data['logo']);
            }

            return back()
                ->withErrors(['error' => 'Organizasyon güncellenirken bir hata oluştu: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified organization
     */
    public function destroy(Organization $organization)
    {
        $this->authorize('delete', $organization);

        try {
            // Check if organization has active events
            if ($organization->events()->published()->exists()) {
                return back()->withErrors([
                    'error' => 'Yayınlanmış etkinlikleri olan organizasyon silinemez.'
                ]);
            }

            DB::beginTransaction();

            // Delete logo
            if ($organization->logo) {
                Storage::disk('public')->delete($organization->logo);
            }

            $organizationName = $organization->name;
            $organization->delete();

            DB::commit();

            return redirect()
                ->route('admin.organizations.index')
                ->with('success', "'{$organizationName}' organizasyonu başarıyla silindi.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Organizasyon silinirken bir hata oluştu.'
            ]);
        }
    }

    /**
     * Attach user to organization
     */
    public function attachUser(Request $request, Organization $organization)
    {
        $this->authorize('manageUsers', $organization);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:organizer,editor',
        ]);

        try {
            $user = User::findOrFail($request->user_id);

            // Check if user is already attached
            if ($organization->users()->where('user_id', $user->id)->exists()) {
                return back()->withErrors([
                    'error' => 'Kullanıcı zaten bu organizasyona bağlı.'
                ]);
            }

            $organization->users()->attach($user->id, ['role' => $request->role]);

            return back()->with('success', "'{$user->name}' organizasyona eklendi.");
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Kullanıcı eklenirken bir hata oluştu.'
            ]);
        }
    }

    /**
     * Detach user from organization
     */
    public function detachUser(Request $request, Organization $organization, User $user)
    {
        $this->authorize('manageUsers', $organization);

        try {
            // Prevent removing the last organizer
            $organizerCount = $organization->organizers()->count();
            $userRole = $organization->users()->where('user_id', $user->id)->first()?->pivot->role;

            if ($userRole === 'organizer' && $organizerCount <= 1) {
                return back()->withErrors([
                    'error' => 'Son organizatör kullanıcı çıkarılamaz.'
                ]);
            }

            $organization->users()->detach($user->id);

            return back()->with('success', "'{$user->name}' organizasyondan çıkarıldı.");
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Kullanıcı çıkarılırken bir hata oluştu.'
            ]);
        }
    }

    /**
     * Update user role in organization
     */
    public function updateUserRole(Request $request, Organization $organization, User $user)
    {
        $this->authorize('manageUsers', $organization);

        $request->validate([
            'role' => 'required|in:organizer,editor',
        ]);

        try {
            $currentRole = $organization->users()->where('user_id', $user->id)->first()?->pivot->role;

            // Prevent removing the last organizer
            if ($currentRole === 'organizer' && $request->role === 'editor') {
                $organizerCount = $organization->organizers()->count();
                if ($organizerCount <= 1) {
                    return back()->withErrors([
                        'error' => 'Son organizatör kullanıcının rolü değiştirilemez.'
                    ]);
                }
            }

            $organization->users()->updateExistingPivot($user->id, ['role' => $request->role]);

            return back()->with('success', "'{$user->name}' kullanıcısının rolü güncellendi.");
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Kullanıcı rolü güncellenirken bir hata oluştu.'
            ]);
        }
    }

    /**
     * Export organization data
     */
    public function export(Organization $organization)
    {
        $this->authorize('view', $organization);

        // This will be implemented in ExportController
        return redirect()->route('admin.export.organization', $organization);
    }
}
