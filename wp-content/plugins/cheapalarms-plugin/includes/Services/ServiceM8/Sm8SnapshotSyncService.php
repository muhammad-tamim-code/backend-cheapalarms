<?php

namespace CheapAlarms\Plugin\Services\ServiceM8;

use CheapAlarms\Plugin\Services\ServiceM8Client;
use CheapAlarms\Plugin\Services\Logger;
use WP_Error;

use function delete_transient;
use function get_transient;
use function is_wp_error;
use function set_transient;
use function wp_json_encode;

class Sm8SnapshotSyncService
{
    public function __construct(
        private ServiceM8Client $client,
        private Sm8JobSnapshotRepository $jobRepo,
        private Sm8CompanySnapshotRepository $companyRepo,
        private Logger $logger
    ) {
    }

    /**
     * Sync ALL jobs from ServiceM8 into the local snapshot table.
     *
     * Uses a distributed lock (transient) to prevent concurrent syncs.
     * ServiceM8 API returns all records in a single response (no pagination).
     *
     * @param array<string, mixed> $filters Optional filters: status (e.g., sync only active jobs)
     * @return array{ok:bool, count:int, durationMs:float}|WP_Error
     */
    public function syncJobs(array $filters = [])
    {
        $lockKey = 'ca_sync_sm8_jobs_lock';
        if (get_transient($lockKey)) {
            return new WP_Error('sync_locked', 'SM8 jobs sync already in progress', ['status' => 409]);
        }
        set_transient($lockKey, true, 120); // 2-minute lock

        $start = microtime(true);

        try {
            $query = [];
            if (!empty($filters['status'])) {
                $query['status'] = $filters['status'];
            }

            $result = $this->client->get('/job.json', $query);

            if (is_wp_error($result)) {
                return $result;
            }

            $jobs = is_array($result) ? $result : [];

            if (empty($jobs)) {
                $this->logger->debug('[SM8_SNAPSHOTS] No jobs returned from API');
                return [
                    'ok'         => true,
                    'count'      => 0,
                    'durationMs' => round((microtime(true) - $start) * 1000, 2),
                ];
            }

            // Normalize all jobs
            $normalized = [];
            foreach ($jobs as $job) {
                $uuid = $job['uuid'] ?? '';
                if (!$uuid) {
                    continue;
                }
                $normalized[] = Sm8JobSnapshotRepository::normalizeFromApi($job);
            }

            // Batch upsert (SM8 typically has manageable volumes — hundreds, not thousands)
            $batchSize = 100;
            $count     = 0;

            foreach (array_chunk($normalized, $batchSize) as $batch) {
                $res = $this->jobRepo->upsertMany($batch);
                if (is_wp_error($res)) {
                    return $res;
                }
                $count += count($batch);
            }

            $durationMs = round((microtime(true) - $start) * 1000, 2);

            $this->logger->debug('[SM8_SNAPSHOTS] synced jobs', [
                'count'      => $count,
                'durationMs' => $durationMs,
            ]);

            return [
                'ok'         => true,
                'count'      => $count,
                'durationMs' => $durationMs,
            ];
        } finally {
            delete_transient($lockKey);
        }
    }

    /**
     * Sync ALL companies from ServiceM8 into the local snapshot table.
     *
     * @return array{ok:bool, count:int, durationMs:float}|WP_Error
     */
    public function syncCompanies()
    {
        $lockKey = 'ca_sync_sm8_companies_lock';
        if (get_transient($lockKey)) {
            return new WP_Error('sync_locked', 'SM8 companies sync already in progress', ['status' => 409]);
        }
        set_transient($lockKey, true, 120); // 2-minute lock

        $start = microtime(true);

        try {
            $result = $this->client->get('/company.json');

            if (is_wp_error($result)) {
                return $result;
            }

            $companies = is_array($result) ? $result : [];

            if (empty($companies)) {
                $this->logger->debug('[SM8_SNAPSHOTS] No companies returned from API');
                return [
                    'ok'         => true,
                    'count'      => 0,
                    'durationMs' => round((microtime(true) - $start) * 1000, 2),
                ];
            }

            $normalized = [];
            foreach ($companies as $company) {
                $uuid = $company['uuid'] ?? '';
                if (!$uuid) {
                    continue;
                }
                $normalized[] = Sm8CompanySnapshotRepository::normalizeFromApi($company);
            }

            $batchSize = 100;
            $count     = 0;

            foreach (array_chunk($normalized, $batchSize) as $batch) {
                $res = $this->companyRepo->upsertMany($batch);
                if (is_wp_error($res)) {
                    return $res;
                }
                $count += count($batch);
            }

            $durationMs = round((microtime(true) - $start) * 1000, 2);

            $this->logger->debug('[SM8_SNAPSHOTS] synced companies', [
                'count'      => $count,
                'durationMs' => $durationMs,
            ]);

            return [
                'ok'         => true,
                'count'      => $count,
                'durationMs' => $durationMs,
            ];
        } finally {
            delete_transient($lockKey);
        }
    }

    /**
     * Sync both jobs and companies (convenience method for daily full sync).
     *
     * @return array{ok:bool, jobs:int, companies:int, durationMs:float}|WP_Error
     */
    public function syncAll()
    {
        $start = microtime(true);

        $jobResult = $this->syncJobs();
        if (is_wp_error($jobResult)) {
            $this->logger->warning('[SM8_SNAPSHOTS] Job sync failed during full sync', [
                'error' => $jobResult->get_error_message(),
            ]);
            // Continue with companies even if jobs fail
        }

        $companyResult = $this->syncCompanies();
        if (is_wp_error($companyResult)) {
            $this->logger->warning('[SM8_SNAPSHOTS] Company sync failed during full sync', [
                'error' => $companyResult->get_error_message(),
            ]);
        }

        $durationMs = round((microtime(true) - $start) * 1000, 2);

        return [
            'ok'         => true,
            'jobs'       => is_wp_error($jobResult) ? 0 : ($jobResult['count'] ?? 0),
            'companies'  => is_wp_error($companyResult) ? 0 : ($companyResult['count'] ?? 0),
            'durationMs' => $durationMs,
        ];
    }
}
