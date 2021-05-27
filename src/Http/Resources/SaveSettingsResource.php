<?php

namespace Codificar\PushNotification\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class SaveSettingsResource
 *
 *
 * @OA\Schema(
 *      schema="SaveSettingsResource",
 *      type="object",
 *      description="Save push notification settings",
 *      title="Save Settings Push Notification",
 *      allOf={
 *          @OA\Schema(ref="#/components/schemas/SaveSettingsResource"),
 *          @OA\Schema(
 *              required={"success"},
 *              @OA\Property(property="success", format="boolean", type="boolean"),
 *              @OA\Property(property="error", format="boolean", type="boolean")
 *          )
 *      }
 * )
 */
class SaveSettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'success'           		=> $this['success'],
            'error'             		=> $this['error']
        ];
    }
}