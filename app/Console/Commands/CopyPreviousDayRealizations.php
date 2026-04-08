<?php

namespace App\Console\Commands;

use App\Models\Realization;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CopyPreviousDayRealizations extends Command
{
    protected $signature = 'realizations:copy-previous-day';

    protected $description = 'If no realizations exist for today, copy them from the previous day';

    public function handle(): int
    {
        $today = Carbon::today();
        $yesterday = $today->copy()->subDay();

        $previousDayRealizations = Realization::with('shops')
            ->whereDate('date', $yesterday)
            ->get();

        if ($previousDayRealizations->isEmpty()) {
            $this->warn('No realizations found for the previous day. Nothing to copy.');
            return self::SUCCESS;
        }

        $todayBusIds = Realization::whereDate('date', $today)
            ->pluck('bus_id')
            ->toArray();

        $missingRealizations = $previousDayRealizations->filter(
            fn ($realization) => !in_array($realization->bus_id, $todayBusIds)
        );

        if ($missingRealizations->isEmpty()) {
            $this->info('All buses already have realizations for today. Nothing to do.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($missingRealizations, $today) {
            foreach ($missingRealizations as $previousRealization) {
                $newRealization = Realization::create([
                    'bus_id' => $previousRealization->bus_id,
                    'date'   => $today,
                ]);

                $shops = $previousRealization->shops->map(fn ($shop) => [
                    'realization_id' => $newRealization->id,
                    'shop'           => $shop->shop,
                    'amount'         => $shop->amount,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                if ($shops->isNotEmpty()) {
                    $newRealization->shops()->insert($shops->toArray());
                }
            }
        });

        $this->info("Copied {$missingRealizations->count()} realization(s) from previous day to today ({$today->toDateString()}).");

        return self::SUCCESS;
    }
}
