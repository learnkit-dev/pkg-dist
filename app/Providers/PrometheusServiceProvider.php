<?php

namespace App\Providers;

use App\Models\License;
use App\Models\Package;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Spatie\Prometheus\Collectors\Horizon\CurrentMasterSupervisorCollector;
use Spatie\Prometheus\Collectors\Horizon\CurrentProcessesPerQueueCollector;
use Spatie\Prometheus\Collectors\Horizon\CurrentWorkloadCollector;
use Spatie\Prometheus\Collectors\Horizon\FailedJobsPerHourCollector;
use Spatie\Prometheus\Collectors\Horizon\HorizonStatusCollector;
use Spatie\Prometheus\Collectors\Horizon\JobsPerMinuteCollector;
use Spatie\Prometheus\Collectors\Horizon\RecentJobsCollector;
use Spatie\Prometheus\Facades\Prometheus;

class PrometheusServiceProvider extends ServiceProvider
{
    public function register()
    {
        /*
         * Here you can register all the exporters that you
         * want to export to prometheus
         */
        Prometheus::addGauge('Users')
            ->value(function() {
                return User::count();
            });

        Prometheus::addGauge('Packages')
            ->value(function() {
                return Package::count();
            });

        Prometheus::addGauge('Licenses')
            ->value(function() {
                return License::count();
            });

        Prometheus::addGauge('Teams')
            ->value(function() {
                return Team::count();
            });

        /*
         * Uncomment this line if you want to export
         * all Horizon metrics to prometheus
         */
        $this->registerHorizonCollectors();
    }

    public function registerHorizonCollectors(): self
    {
        Prometheus::registerCollectorClasses([
            CurrentMasterSupervisorCollector::class,
            CurrentProcessesPerQueueCollector::class,
            CurrentWorkloadCollector::class,
            FailedJobsPerHourCollector::class,
            HorizonStatusCollector::class,
            JobsPerMinuteCollector::class,
            RecentJobsCollector::class,
        ]);

        return $this;
    }
}
