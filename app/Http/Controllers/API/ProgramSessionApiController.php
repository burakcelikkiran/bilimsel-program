<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProgramSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProgramSessionApiController extends Controller
{
    /**
     * Bulk update session positions and times (for drag & drop)
     */
    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessions' => 'required|array',
            'sessions.*.id' => 'required|exists:program_sessions,id',
            'sessions.*.venue_id' => 'nullable|exists:venues,id',
            'sessions.*.start_time' => 'nullable|date_format:H:i',
            'sessions.*.end_time' => 'nullable|date_format:H:i',
            'sessions.*.sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz veri.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            foreach ($request->sessions as $sessionData) {
                $session = ProgramSession::findOrFail($sessionData['id']);
                
                // Check permissions
                if (!$this->canUpdateSession($session)) {
                    continue;
                }

                // Update only provided fields
                $updateData = [];
                
                if (isset($sessionData['venue_id'])) {
                    $updateData['venue_id'] = $sessionData['venue_id'];
                }
                
                if (isset($sessionData['start_time'])) {
                    $updateData['start_time'] = $sessionData['start_time'];
                }
                
                if (isset($sessionData['end_time'])) {
                    $updateData['end_time'] = $sessionData['end_time'];
                }
                
                if (isset($sessionData['sort_order'])) {
                    $updateData['sort_order'] = $sessionData['sort_order'];
                }

                // Validate time conflicts
                if (isset($updateData['start_time']) || isset($updateData['end_time']) || isset($updateData['venue_id'])) {
                    $hasConflict = $this->checkTimeConflict(
                        $session->id,
                        $updateData['venue_id'] ?? $session->venue_id,
                        $updateData['start_time'] ?? $session->start_time,
                        $updateData['end_time'] ?? $session->end_time
                    );

                    if ($hasConflict) {
                        return response()->json([
                            'success' => false,
                            'message' => "'{$session->title}' oturumu için zaman çakışması var.",
                            'conflicting_session' => $session->title
                        ], 422);
                    }
                }

                $session->update($updateData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Oturumlar başarıyla güncellendi.',
                'updated_count' => count($request->sessions)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Güncelleme sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update session venue (for drag & drop between venues)
     */
    public function updateVenue(Request $request, ProgramSession $session)
    {
        $validator = Validator::make($request->all(), [
            'venue_id' => 'required|exists:venues,id',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (!$this->canUpdateSession($session)) {
            return response()->json([
                'success' => false,
                'message' => 'Bu oturumu güncelleme yetkiniz yok.'
            ], 403);
        }

        try {
            // Check for time conflicts in new venue
            $hasConflict = $this->checkTimeConflict(
                $session->id,
                $request->venue_id,
                $request->start_time ?? $session->start_time,
                $request->end_time ?? $session->end_time
            );

            if ($hasConflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hedef salonda zaman çakışması var.'
                ], 422);
            }

            $updateData = ['venue_id' => $request->venue_id];
            
            if ($request->start_time) {
                $updateData['start_time'] = $request->start_time;
            }
            
            if ($request->end_time) {
                $updateData['end_time'] = $request->end_time;
            }

            $session->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Oturum salonu güncellendi.',
                'session' => $session->load(['venue', 'moderators', 'presentations'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Güncelleme hatası: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update session time (for timeline resize)
     */
    public function updateTime(Request $request, ProgramSession $session)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (!$this->canUpdateSession($session)) {
            return response()->json([
                'success' => false,
                'message' => 'Bu oturumu güncelleme yetkiniz yok.'
            ], 403);
        }

        try {
            // Check for time conflicts
            $hasConflict = $this->checkTimeConflict(
                $session->id,
                $session->venue_id,
                $request->start_time,
                $request->end_time
            );

            if ($hasConflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zaman çakışması var.'
                ], 422);
            }

            $session->update([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Oturum zamanı güncellendi.',
                'session' => $session
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Güncelleme hatası: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sessions for timeline view
     */
    public function getTimelineData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_day_id' => 'required|exists:event_days,id',
            'venue_ids' => 'nullable|array',
            'venue_ids.*' => 'exists:venues,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = ProgramSession::with([
                'venue',
                'moderators',
                'presentations',
                'categories'
            ])
            ->whereHas('venue', function ($q) use ($request) {
                $q->where('event_day_id', $request->event_day_id);
                
                if ($request->venue_ids) {
                    $q->whereIn('id', $request->venue_ids);
                }
            })
            ->orderBy('start_time')
            ->orderBy('sort_order');

            $sessions = $query->get();

            // Group sessions by venue
            $groupedSessions = $sessions->groupBy('venue_id');

            // Get venues for the event day
            $venues = \App\Models\Venue::where('event_day_id', $request->event_day_id)
                ->when($request->venue_ids, function ($q) use ($request) {
                    return $q->whereIn('id', $request->venue_ids);
                })
                ->orderBy('sort_order')
                ->get();

            return response()->json([
                'success' => true,
                'sessions' => $groupedSessions,
                'venues' => $venues,
                'total_sessions' => $sessions->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Veri alınırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick create session (for timeline quick add)
     */
    public function quickCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'venue_id' => 'required|exists:venues,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'session_type' => 'nullable|in:main,satellite,oral_presentation,special,break',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check for time conflicts
            $hasConflict = $this->checkTimeConflict(
                null,
                $request->venue_id,
                $request->start_time,
                $request->end_time
            );

            if ($hasConflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu zaman aralığında çakışma var.'
                ], 422);
            }

            $session = ProgramSession::create([
                'title' => $request->title,
                'venue_id' => $request->venue_id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'session_type' => $request->session_type ?? 'main',
                'description' => $request->description,
                'sort_order' => $this->getNextSortOrder($request->venue_id),
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Oturum başarıyla oluşturuldu.',
                'session' => $session->load(['venue', 'moderators', 'presentations'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Oluşturma hatası: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user can update session
     */
    private function canUpdateSession(ProgramSession $session): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Admin can update any session
        if ($user->hasRole('admin')) {
            return true;
        }

        // Organizer can update sessions in their events
        if ($user->hasRole('organizer')) {
            return $session->venue->eventDay->event->organizer_id === $user->id;
        }

        // Editor can update sessions if they have permission for the event
        if ($user->hasRole('editor')) {
            return $session->venue->eventDay->event->editors()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Check for time conflicts
     */
    private function checkTimeConflict($sessionId, $venueId, $startTime, $endTime): bool
    {
        $query = ProgramSession::where('venue_id', $venueId)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($q2) use ($startTime, $endTime) {
                      $q2->where('start_time', '<=', $startTime)
                         ->where('end_time', '>=', $endTime);
                  });
            });

        if ($sessionId) {
            $query->where('id', '!=', $sessionId);
        }

        return $query->exists();
    }

    /**
     * Get next sort order for venue
     */
    private function getNextSortOrder($venueId): int
    {
        $maxOrder = ProgramSession::where('venue_id', $venueId)->max('sort_order');
        return ($maxOrder ?? 0) + 1;
    }
}