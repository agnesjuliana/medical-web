<?php

namespace Backend\Services;

class NutritionService
{
    /**
     * Compute the health score for a given day.
     * Returns score (0–100), calorie deviation %, and macro deviation %.
     */
    public function computeHealthScore(array $targets, array $consumed): array
    {
        $calDev = round(
            abs($consumed['calories'] - $targets['calories'])
            / max($targets['calories'], 1) * 100,
            2
        );

        $proteinDev = abs($consumed['protein_g'] - $targets['protein_g'])
                      / max($targets['protein_g'], 1) * 100;
        $carbsDev   = abs($consumed['carbs_g'] - $targets['carbs_g'])
                      / max($targets['carbs_g'], 1) * 100;
        $fatsDev    = abs($consumed['fats_g'] - $targets['fats_g'])
                      / max($targets['fats_g'], 1) * 100;
        $macroDev   = round(($proteinDev + $carbsDev + $fatsDev) / 3, 2);

        $healthScore = 100 - min(30, $calDev * 0.5) - min(30, $macroDev * 0.5);
        $healthScore = (int) max(0, min(100, $healthScore));

        return [
            'score'     => $healthScore,
            'cal_dev'   => $calDev,
            'macro_dev' => $macroDev,
        ];
    }
}
