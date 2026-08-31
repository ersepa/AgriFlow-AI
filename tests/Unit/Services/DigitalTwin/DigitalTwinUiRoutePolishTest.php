<?php

namespace Tests\Unit\Services\DigitalTwin;

use PHPUnit\Framework\TestCase;

class DigitalTwinUiRoutePolishTest extends TestCase
{
    public function test_current_route_duplicate_detection_rule_is_strict(): void
    {
        $options = [
            [
                'rank' => 1,
                'label' => 'Current Route',
                'source' => 'current',
            ],
            [
                'rank' => 2,
                'label' => 'Alternative 1',
                'source' => 'ors',
            ],
        ];

        $filtered =
            array_values(
                array_filter(
                    $options,
                    static function (
                        array $option
                    ): bool {
                        $label =
                            strtolower(
                                trim(
                                    (string) (
                                        $option['label']
                                        ?? ''
                                    )
                                )
                            );

                        $source =
                            strtolower(
                                trim(
                                    (string) (
                                        $option['source']
                                        ?? ''
                                    )
                                )
                            );

                        $rank =
                            (int) (
                                $option['rank']
                                ?? 0
                            );

                        $looksLikeCurrent =
                            $label
                                === 'current route'
                            || str_starts_with(
                                $label,
                                'current route ·'
                            )
                            || $source
                                === 'current';

                        return !(
                            $rank > 0
                            && $looksLikeCurrent
                        );
                    }
                )
            );

        $this->assertCount(
            1,
            $filtered
        );

        $this->assertSame(
            'Alternative 1',
            $filtered[0]['label']
        );
    }
}
