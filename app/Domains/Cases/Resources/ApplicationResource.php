<?php

namespace App\Domains\Cases\Resources;

use App\Domains\Cases\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Application
 */
class ApplicationResource extends JsonResource
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
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'first_name' => $this->user?->first_name,
                'last_name' => $this->user?->last_name,
                'phone' => $this->user?->phone,
                'email' => $this->user?->email,
                'gender' => $this->user?->gender,
                'age' => $this->user?->age,
                'date_of_birth' => $this->user?->date_of_birth?->toDateString(),
                'blood_group' => $this->user?->blood_group,
            ],
            'category' => [
                'id' => $this->category?->id,
                'slug' => $this->category?->slug,
                'icon' => $this->category?->icon,
            ],
            'sub_category' => $this->subCategory ? [
                'id' => $this->subCategory->id,
                'slug' => $this->subCategory->slug,
            ] : null,
            'village' => $this->village ? [
                'id' => $this->village->id,
                'name_en' => $this->village->name_en,
                'name_gu' => $this->village->name_gu,
                'district' => $this->village->taluka?->district?->name_en,
            ] : null,
            'current_assignee' => $this->currentAssignee ? [
                'id' => $this->currentAssignee->id,
                'name' => $this->currentAssignee->name,
                'role' => $this->currentAssignee->getRoleNames()->first(),
            ] : null,
            'lat' => $this->lat !== null ? (float) $this->lat : null,
            'lng' => $this->lng !== null ? (float) $this->lng : null,
            'sla_due_at' => $this->sla_due_at?->toIso8601String(),
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'rating' => $this->rating,
            'feedback' => $this->feedback,
            'documents' => ApplicationDocumentResource::collection($this->whenLoaded('documents')),
            'timeline' => ApplicationTimelineResource::collection($this->whenLoaded('timelineEvents')),
            'timeline_events' => ApplicationTimelineResource::collection($this->whenLoaded('timelineEvents')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
