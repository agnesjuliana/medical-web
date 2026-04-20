import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import ProgressScreen from '../ProgressScreens';

const mockSummary = {
  weight: {
    current_weight: 72,
    start_weight: 80,
    goal_weight: 65,
    goal_progress: 53,
    height_cm: 170,
    bmi: 24.9,
    logs: [],
    deltas: { '3d': -0.2, '7d': -0.5, '30d': -2.0 },
  },
  energy: {
    week_start: '2026-04-14',
    week_end: '2026-04-20',
    days: [],
    total_consumed: 1800,
  },
  calories: {
    avg_7d: 1850,
    avg_30d: 1900,
    logs_7d: [],
  },
};

vi.mock('@/services/api', () => ({
  getProgressSummary: vi.fn(),
  getWeightProgress: vi.fn(),
  getWeeklyEnergy: vi.fn(),
  getCalorieAverages: vi.fn(),
  toast: { error: vi.fn() },
}));

vi.mock('@/components/header/Header', () => ({
  default: ({ title }: { title: string }) => <div>{title}</div>,
}));

vi.mock('@/components/page/ChangeRow', () => ({
  default: ({ timeframe }: { timeframe: string }) => <div>{timeframe}</div>,
}));

import * as api from '@/services/api';

describe('ProgressScreen — combined initial fetch', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders weight data from getProgressSummary without separate API calls', async () => {
    vi.mocked(api.getProgressSummary).mockResolvedValueOnce({ data: mockSummary });

    render(<ProgressScreen />);

    await waitFor(() => {
      expect(screen.getByText('72')).toBeInTheDocument();
    });

    expect(api.getProgressSummary).toHaveBeenCalledTimes(1);
    expect(api.getWeightProgress).not.toHaveBeenCalled();
    expect(api.getWeeklyEnergy).not.toHaveBeenCalled();
    expect(api.getCalorieAverages).not.toHaveBeenCalled();
  });

  it('shows avg_7d calories from summary payload', async () => {
    vi.mocked(api.getProgressSummary).mockResolvedValueOnce({ data: mockSummary });

    render(<ProgressScreen />);

    await waitFor(() => {
      expect(screen.getByText('1,850')).toBeInTheDocument();
    });
  });
});
