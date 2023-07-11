<?php

namespace App\Enums;

enum VersionStatus: string
{
    case Syncing = 'syncing';

    case Published = 'published';

    case Paused = 'paused';

    case Failed = 'failed';
}
