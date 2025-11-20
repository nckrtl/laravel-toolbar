<?php

namespace NckRtl\Toolbar\Collectors;

use NckRtl\Toolbar\Measurement;
use NckRtl\Toolbar\Enums\TimeUnit;
use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Data\ProfilerData;
use NckRtl\Toolbar\Enums\DataSizeUnit;
use NckRtl\Toolbar\Data\RequestStageData;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;
use NckRtl\Toolbar\Data\Configurations\ProfilerConfig;

class ProfilerCollector extends Collector implements CollectorInterface
{
    public function key(): string
    {
        return 'profiler';
    }

    public function configClass(): string
    {
        return ProfilerConfig::class;
    }

    public function collectData(CollectorManager $collectorManager): ProfilerData
    {
        $requestStages = Profiler::getRequestStages();

        [$totalWallTime, $totalRealMemory, $totalAllocatedMemory] = $this->getTotals($requestStages);

        foreach ($requestStages as $requestStageData) {
            $requestStageData->calculatePercentages(
                totalWallTime: $totalWallTime,
                totalRealMemory: $totalRealMemory,
                totalAllocatedMemory: $totalAllocatedMemory
            );
        }

        return new ProfilerData(
            total_wall_time: $totalWallTime,
            total_real_memory: $totalRealMemory,
            total_allocated_memory: $totalAllocatedMemory,
            stages: $requestStages
        );
    }

    private function getTotals(array $requestStages): array
    {
        $requestStages = $this->prepareRequestStagesForTotalsCalculation($requestStages);

        $totalWallTime = 0;
        $totalRealMemory = 0;

        $firstStage = $requestStages[0];

        foreach ($requestStages as $requestStage) {
            $totalWallTime += $requestStage->wall_time->measurement->value;

            if ($requestStage->memory_real_delta) {
                $totalRealMemory += $requestStage->memory_real_delta->measurement->value;
            }

            $startTime = $firstStage->start->time->value;
            $requestStageEndTime = $requestStage->end->time->value;

            $differenceFromStartToCurrentRequestStageEnd = new Measurement($requestStageEndTime - $startTime, TimeUnit::SECONDS)->convertTo(TimeUnit::MILLISECONDS)->value;

            if ($differenceFromStartToCurrentRequestStageEnd > $totalWallTime) {
                throw new \Exception('Wall time overlap detected at stage "'.$requestStage->label.'" discrepancy: '.(new Measurement($differenceFromStartToCurrentRequestStageEnd - $totalWallTime, TimeUnit::MILLISECONDS))->formattedValue);
            }

            if ($totalWallTime > 0 && $differenceFromStartToCurrentRequestStageEnd < $totalWallTime) {
                throw new \Exception('Wall time gap detected at stage "'.$requestStage->label.'" discrepancy: '.(new Measurement($totalWallTime - $differenceFromStartToCurrentRequestStageEnd, TimeUnit::MILLISECONDS))->formattedValue);
            }
        }

        return [
            new Measurement($totalWallTime, TimeUnit::MILLISECONDS),
            new Measurement($totalRealMemory, DataSizeUnit::BYTES),
            new Measurement(memory_get_peak_usage(), DataSizeUnit::BYTES),
        ];
    }

    private function prepareRequestStagesForTotalsCalculation(array $requestStages): array
    {
        $stagesWithStartOrEnd = array_values(
            array_filter($requestStages, function (RequestStageData $requestStage) {
                return $requestStage->recordedEnd || $requestStage->recordedStart;
            })
        );

        return $this->fillInMissingStartAndEnd($stagesWithStartOrEnd);
    }

    private function fillInMissingStartAndEnd(array $stagesWithStartOrEnd): array
    {
        // Make sure each stage has a start and end
        foreach ($stagesWithStartOrEnd as $index => $requestStage) {
            if (! $requestStage->recordedEnd && ! $requestStage->recordedStart) {
                throw new \Exception('Stage "'.$requestStage->label.'" has no start or end.');
            }

            if (! $requestStage->recordedEnd) {
                $requestStage->end = $this->findNextStageWithEnd($requestStage, $stagesWithStartOrEnd, $index)->end;
            }

            if (! $requestStage->recordedStart) {
                $requestStage->start = $requestStage->end;
            }

            $requestStage->calculateWallTime();
            $requestStage->calculateMemoryDeltas();
        }

        return $stagesWithStartOrEnd;
    }

    private function findNextStageWithEnd(RequestStageData $requestStage, array $stagesWithStartOrEnd, int $currentIndex): RequestStageData
    {
        $nextStages = array_slice($stagesWithStartOrEnd, $currentIndex + 1);

        $nextStageWithEnd = array_values(
            array_filter($nextStages, function (RequestStageData $requestStage) {
                return $requestStage->recordedEnd;
            })
        );

        if (empty($nextStageWithEnd)) {
            throw new \Exception('No next stage with a end time found for stage "'.$requestStage->label.'". This is probably due to a missing request checkpoint.');
        }

        $nextStageWithEnd = $nextStageWithEnd[0];
        $requestStage->end = $nextStageWithEnd->end;

        return $requestStage;
    }
}
