<?php

namespace App\Domains\Cases\Resources;

use App\Domains\Cases\Models\Application;
use App\Domains\Cases\Models\ApplicationTimelineEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Application
 */
class CitizenApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'case_no' => $this->case_no,
            'title' => $this->title,
            'description' => $this->description,
            'urgency' => $this->urgency,
            'priority' => $this->priority,
            'status' => $this->status,
            'category' => [
                'id' => $this->category?->id,
                'slug' => $this->category?->slug,
                'name_en' => $this->category?->name_en,
                'name_gu' => $this->category?->name_gu,
                'icon' => $this->category?->icon,
            ],
            'module' => $this->category->name_en ?? $this->category->slug,
            'current_assignee' => $this->currentAssignee ? [
                'id' => $this->currentAssignee->id,
                'name' => $this->currentAssignee->name,
                'phone' => $this->currentAssignee->phone,
                'email' => $this->currentAssignee->email,
                'role' => $this->currentAssignee->getRoleNames()->first() ?? 'Field Coordinator',
                'helper_status' => $this->currentAssignee->helper_status,
            ] : null,
            'sub_category' => $this->subCategory ? [
                'id' => $this->subCategory->id,
                'slug' => $this->subCategory->slug,
            ] : null,
            'village' => $this->village ? [
                'id' => $this->village->id,
                'name_en' => $this->village->name_en,
                'name_gu' => $this->village->name_gu,
            ] : null,
            'taluka' => $this->village?->taluka ? [
                'id' => $this->village->taluka->id,
                'name_en' => $this->village->taluka->name_en,
                'name_gu' => $this->village->taluka->name_gu,
            ] : null,
            'district' => $this->village?->taluka?->district ? [
                'id' => $this->village->taluka->district->id,
                'name_en' => $this->village->taluka->district->name_en,
                'name_gu' => $this->village->taluka->district->name_gu,
            ] : null,
            'lat' => $this->lat !== null ? (float) $this->lat : null,
            'lng' => $this->lng !== null ? (float) $this->lng : null,
            'user' => $this->user ? [
                'name' => $this->user->name,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'phone' => $this->user->phone,
                'email' => $this->user->email,
                'gender' => $this->user->gender,
                'date_of_birth' => $this->user->date_of_birth?->toDateString(),
                'blood_group' => $this->user->blood_group,
            ] : null,
            'sla_due_at' => $this->sla_due_at?->toIso8601String(),
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'rating' => $this->rating,
            'feedback' => $this->feedback,
            'documents' => ApplicationDocumentResource::collection($this->whenLoaded('documents')),
            'timeline' => ApplicationTimelineResource::collection(
                $this->relationLoaded('timelineEvents')
                    ? $this->timelineEvents->filter(fn (ApplicationTimelineEvent $e) => $e->visibility === 'public')
                    : collect()
            ),
            'timeline_events' => ApplicationTimelineResource::collection(
                $this->relationLoaded('timelineEvents')
                    ? $this->timelineEvents->filter(fn (ApplicationTimelineEvent $e) => $e->visibility === 'public')
                    : collect()
            ),
            'workflow_stages' => Application::getWorkflowStagesFor($this->status),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
