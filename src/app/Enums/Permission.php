<?php

namespace App\Enums;

enum Permission: string
{
    case AccessAdminPanel = 'filament.access';
    case PublishContent = 'content.publish';
    case UnpublishContent = 'content.unpublish';
    case PreviewContent = 'content.preview';
    case ForceDeleteContent = 'content.force-delete';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $permission) => $permission->value, self::cases());
    }
}
