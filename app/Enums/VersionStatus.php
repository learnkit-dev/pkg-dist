<?php

namespace App\Enums;

use Closure;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Hamcrest\SelfDescribing;

enum VersionStatus: string implements HasLabel, HasColor
{
    case Syncing = 'syncing';

    case Published = 'published';

    case Paused = 'paused';

    case Failed = 'failed';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Syncing => 'warning',
            self::Published => 'success',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Syncing => 'Syncing',
            self::Published => 'Published',
        };
    }
}
