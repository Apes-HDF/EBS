<?php

declare(strict_types=1);

namespace App\Enum;

enum SocialMediaType: string
{
    use AsArrayTrait;

    case FACEBOOK = 'facebook';
    case TWITTER = 'twitter';
    case MASTODON = 'mastodon';
    case INSTAGRAM = 'instagram';
    case YOUTUBE = 'youtube';
    case LINKEDIN = 'linkedin';
    case WEB = 'web';

    public function isWeb(): bool
    {
        return $this === self::WEB;
    }
}
