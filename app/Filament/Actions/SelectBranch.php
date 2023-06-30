<?php

namespace App\Filament\Actions;

use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Http;

class SelectBranch extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->options(function () {
            $repo = $this->getLivewire()->ownerRecord;
            $team = $repo->team;

            $result = Http::withHeaders([
                'Authorization' => 'Bearer ' . $team->gh_api_key,
            ])->get('https://api.github.com/repos/' . $repo->name . '/branches?per_page=100');

            return collect($result->json())
                ->pluck('name', 'name')
                ->toArray();
        });

        $this->searchable();
    }
}
