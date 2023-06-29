<?php

namespace App\Filament\Actions;

use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Http;

class SelectVersion extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->options(function () {
            $repo = $this->getLivewire()->ownerRecord;

            $result = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('repomap.github_personal_token'),
            ])->get('https://api.github.com/repos/' . $repo->name . '/releases');

            return collect($result->json())
                ->pluck('name', 'tag_name')
                ->toArray();
        });
    }
}