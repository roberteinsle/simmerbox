<?php

namespace App\Console\Commands;

use App\Services\SearchService;
use Illuminate\Console\Command;

class ReindexSearch extends Command
{
    protected $signature = 'search:reindex';

    protected $description = 'Baut den Volltextsuchindex komplett neu auf';

    public function handle(SearchService $searchService): int
    {
        $this->info('Starte Neuindizierung...');

        $count = $searchService->reindexAll();

        $this->info("Fertig: {$count} Rezepte indiziert.");

        return self::SUCCESS;
    }
}
