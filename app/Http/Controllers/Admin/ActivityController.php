<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    /**
     * Display a listing of activities
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        $query = Activity::with(['user', 'organization', 'subject'])
            ->forUser($user);

        // Apply filters
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('performed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('performed_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $activities = $query->orderBy('performed_at', 'desc')
            ->paginate(20)
            ->withQueryString()
            ->through(function ($activity) {
                $link = $this->getActivityLink($activity);
                
                return [
                    'id' => $activity->id,
                    'type' => $activity->type,
                    'description' => $activity->description,
                    'performed_at' => $activity->performed_at,
                    'user' => [
                        'id' => $activity->user->id,
                        'name' => $activity->user->name,
                        'email' => $activity->user->email,
                    ],
                    'organization' => $activity->organization ? [
                        'id' => $activity->organization->id,
                        'name' => $activity->organization->name,
                    ] : null,
                    'subject' => [
                        'type' => $activity->subject_type,
                        'id' => $activity->subject_id,
                        'name' => $this->getSubjectName($activity),
                    ],
                    'link' => $link,
                    'type_label' => $activity->getTypeLabel(),
                    'properties' => $activity->properties,
                ];
            });

        // Get filter options
        $filterOptions = $this->getFilterOptions($user);

        return Inertia::render('Admin/Activities/Index', [
            'activities' => $activities,
            'filters' => $request->only(['type', 'user_id', 'organization_id', 'date_from', 'date_to', 'search']),
            'filterOptions' => $filterOptions,
        ]);
    }

    /**
     * Get filter options for the activities page
     */
    private function getFilterOptions($user)
    {
        $query = Activity::forUser($user);

        return [
            'types' => Activity::getTypes(),
            'users' => $query->with('user')
                ->get()
                ->pluck('user')
                ->unique('id')
                ->values()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ];
                }),
            'organizations' => $user->isAdmin() 
                ? \App\Models\Organization::active()->get(['id', 'name'])
                : $user->organizations()->get(['organizations.id as id', 'organizations.name as name']),
        ];
    }

    /**
     * Get the appropriate link for an activity
     */
    private function getActivityLink($activity)
    {
        try {
            switch ($activity->subject_type) {
                case 'App\Models\Event':
                    return $activity->subject ? route('admin.events.show', $activity->subject->slug) : null;
                case 'App\Models\ProgramSession':
                    return $activity->subject ? route('admin.program-sessions.show', $activity->subject->id) : null;
                case 'App\Models\Participant':
                    return $activity->subject ? route('admin.participants.show', $activity->subject->id) : null;
                case 'App\Models\Venue':
                    return $activity->subject ? route('admin.venues.show', $activity->subject->id) : null;
                case 'App\Models\Sponsor':
                    return $activity->subject ? route('admin.sponsors.show', $activity->subject->id) : null;
                case 'App\Models\Organization':
                    return $activity->subject ? route('admin.organizations.show', $activity->subject->id) : null;
                default:
                    return null;
            }
        } catch (\Exception $e) {
            // If subject is deleted or route doesn't exist
            return null;
        }
    }

    /**
     * Get a human readable name for the subject
     */
    private function getSubjectName($activity)
    {
        if (!$activity->subject) {
            return 'Silinmiş';
        }

        try {
            switch ($activity->subject_type) {
                case 'App\Models\Event':
                    return $activity->subject->title ?: ($activity->subject->name ?: "#{$activity->subject->id}");
                case 'App\Models\ProgramSession':
                    return $activity->subject->title ?: "#{$activity->subject->id}";
                case 'App\Models\Participant':
                    return $activity->subject->full_name ?: ($activity->subject->first_name . ' ' . $activity->subject->last_name) ?: "#{$activity->subject->id}";
                case 'App\Models\Venue':
                    return $activity->subject->display_name ?: ($activity->subject->name ?: "#{$activity->subject->id}");
                case 'App\Models\Sponsor':
                    return $activity->subject->name ?: "#{$activity->subject->id}";
                case 'App\Models\Organization':
                    return $activity->subject->name ?: "#{$activity->subject->id}";
                default:
                    return 'Bilinmeyen';
            }
        } catch (\Exception $e) {
            return 'Bilinmeyen';
        }
    }

    /**
     * Show the specified activity
     */
    public function show(Request $request, Activity $activity): Response
    {
        $user = $request->user();

        // Check if user has access to this activity
        if (!$user->isAdmin()) {
            $userOrganizationIds = $user->organizations()->pluck('organizations.id');
            if (!$userOrganizationIds->contains($activity->organization_id)) {
                abort(403, 'Bu aktiviteye erişim yetkiniz yok.');
            }
        }

        $activity->load(['user', 'organization', 'subject']);

        return Inertia::render('Admin/Activities/Show', [
            'activity' => [
                'id' => $activity->id,
                'type' => $activity->type,
                'description' => $activity->description,
                'performed_at' => $activity->performed_at,
                'user' => [
                    'id' => $activity->user->id,
                    'name' => $activity->user->name,
                    'email' => $activity->user->email,
                ],
                'organization' => $activity->organization ? [
                    'id' => $activity->organization->id,
                    'name' => $activity->organization->name,
                ] : null,
                'subject' => [
                    'type' => $activity->subject_type,
                    'id' => $activity->subject_id,
                    'name' => $this->getSubjectName($activity),
                ],
                'link' => $this->getActivityLink($activity),
                'type_label' => $activity->getTypeLabel(),
                'properties' => $activity->properties,
            ],
        ]);
    }
}
