// =============================================================================
// Exercise tracking type definitions & MET-based calorie calculation
// Reference: Compendium of Physical Activities (Ainsworth et al., 2011)
// =============================================================================

export interface ExerciseEntry {
  id: string;
  type: 'run' | 'weight_lifting' | 'manual';
  label: string;
  intensity: 'low' | 'medium' | 'high';
  durationMinutes: number;
  caloriesBurned: number;
  emoji: string;
  timestamp: Date;
}

// MET values from the Compendium of Physical Activities
// Calories burned = MET × weight_kg × duration_hours
export const EXERCISE_TYPES = {
  run: {
    label: 'Lari',
    emoji: '🏃',
    description: 'Lari, jogging, sprint, dll.',
    intensities: {
      low: {
        label: 'Low',
        met: 3.5,
        description: 'Jalan santai ~ 5 km/jam',
      },
      medium: {
        label: 'Medium',
        met: 7.0,
        description: 'Jogging ~ 8‑10 km/jam',
      },
      high: {
        label: 'High',
        met: 11.5,
        description: 'Sprint ~ 14+ km/jam',
      },
    },
  },
  weight_lifting: {
    label: 'Angkat Beban',
    emoji: '🏋️',
    description: 'Mesin, free weights, dll.',
    intensities: {
      low: {
        label: 'Low',
        met: 2.0,
        description: 'Ringan, sedikit usaha',
      },
      medium: {
        label: 'Medium',
        met: 3.5,
        description: 'Berkeringat, banyak repetisi',
      },
      high: {
        label: 'High',
        met: 6.0,
        description: 'Training to failure, napas berat',
      },
    },
  },
} as const;

export type ExerciseType = keyof typeof EXERCISE_TYPES;
export type IntensityLevel = 'low' | 'medium' | 'high';

/**
 * Calculate calories burned using MET formula
 * Formula: Calories = MET × Weight(kg) × Duration(hours)
 * Source: Ainsworth et al. (2011) - Compendium of Physical Activities
 */
export function calculateCaloriesBurned(
  met: number,
  weightKg: number,
  durationMinutes: number
): number {
  const durationHours = durationMinutes / 60;
  return Math.round(met * weightKg * durationHours);
}

export const DURATION_PRESETS = [15, 30, 60, 90];
