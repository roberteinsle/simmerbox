<?php

namespace App\Console\Commands;

use App\Services\RecurrenceSchedulerService;
use Illuminate\Console\Command;

class UpdateRecurringGroceries extends Command
{
    protected $signature = 'groceries:update-recurring';

    protected $description = 'Aktualisiert die naechsten Vorkommensdaten fuer wiederkehrende Einkaeufe';

    public function handle(RecurrenceSchedulerService $scheduler): int
    {
        $count = $scheduler->updateDueItems();

        $this->info("Aktualisiert: {$count} Eintraege.");

        return self::SUCCESS;
    }
}
