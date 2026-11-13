<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CompetitionDemoSeederContractTest extends TestCase
{
    public function test_demo_seeder_has_eight_engine_driven_records_and_preserves_reference_tables(): void
    {
        $path = dirname(__DIR__, 2)
            . '/database/seeders/CompetitionDemoSeeder.php';

        $source = file_get_contents($path);

        $this->assertIsString($source);
        $this->assertStringContainsString(
            "DEMO_DATASET_VERSION = 'competition-demo-v1'",
            $source
        );

        foreach ([
            "'commodity' => 'Brokoli'",
            "'commodity' => 'Tomat'",
            "'commodity' => 'Timun'",
            "'commodity' => 'Bayam'",
            "'commodity' => 'Kopi'",
            "'commodity' => 'Jagung Pipilan Kering'",
            "'commodity' => 'Beras'",
            "'commodity' => 'Cabai'",
        ] as $commodity) {
            $this->assertStringContainsString($commodity, $source);
        }

        $this->assertSame(
            8,
            substr_count($source, "'demo_role' =>")
        );

        $this->assertStringContainsString('DecisionEngine', $source);
        $this->assertStringContainsString('AnalysisSnapshotService', $source);
        $this->assertStringContainsString('FreightCarbonEstimateService', $source);

        // Reference/user tables must not be part of the destructive cleanup.
        $cleanupBlockStart = strpos($source, 'private function clearOperationalData');
        $cleanupBlockEnd = strpos($source, 'private function assertRequiredCommodityProfiles');
        $cleanup = substr(
            $source,
            $cleanupBlockStart,
            $cleanupBlockEnd - $cleanupBlockStart
        );

        $this->assertStringNotContainsString("'commodity_profiles'", $cleanup);
        $this->assertStringNotContainsString("'users'", $cleanup);

        // Demo scores must remain engine-generated, not seeded as fake outcomes.
        $recordsStart = strpos($source, 'private function demoRecords');
        $records = substr($source, $recordsStart);
        $this->assertStringNotContainsString("'risk_score' =>", $records);
        $this->assertStringNotContainsString("'route_score' =>", $records);
    }
}
