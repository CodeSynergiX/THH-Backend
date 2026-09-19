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
            ] : null,
            'sla_due_at' => $this->sla_due_at?->toIso8601String(),
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'rating' => $this->rating,
            'feedback' => $this->feedback,
            'documents' => ApplicationDocumentResource::collection($this->whenLoaded('documents')),
            // CRITICAL: Strictly public timeline events only! Never expose internal notes to citizens!
            'timeline' => ApplicationTimelineResource::collection(
                $this->timelineEvents->filter(fn (ApplicationTimelineEvent $e) => $e->visibility === 'public')
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
