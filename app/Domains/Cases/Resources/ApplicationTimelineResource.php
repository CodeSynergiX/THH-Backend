<?php

namespace App\Domains\Cases\Resources;

use App\Domains\Cases\Models\ApplicationTimelineEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ApplicationTimelineEvent
 */
class ApplicationTimelineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_type' => $this->event_type,
            'from_status' => $this->from_status,
            'to_status' => $this->to_status,
            'title_key' => $this->title_key,
            'body' => $this->body,
            'actor_name' => $this->actor ? $this->actor->name : 'System',
            'actor_role' => $this->actor_role,
            'visibility' => $this->visibility,
            'meta' => $this->meta,
            'notification_status' => $this->notification_status,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];
    }
}
