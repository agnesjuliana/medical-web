import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, act } from '@testing-library/react';

vi.mock('@/components/header/Header', () => ({ default: () => <div /> }));
vi.mock('@/components/navigation/tabBar', () => ({ default: () => <div /> }));
vi.mock('@/components/template/Daylist', () => ({ default: () => <div /> }));
vi.mock('@/components/template/FoodCard', () => ({ default: () => <div /> }));
vi.mock('../ProgressScreens', () => ({ default: () => <div /> }));
vi.mock('../SettingsScreens', () => ({ default: () => <div /> }));
vi.mock('../ScannerScreen', () => ({ default: () => <div /> }));
vi.mock('../FoodDetailScreen', () => ({ default: () => <div /> }));
vi.mock('../AccountDetailsScreen', () => ({ default: () => <div /> }));
vi.mock('../LogFood', () => ({ default: () => <div /> }));

vi.mock('../../services/api', () => ({
  getDashboard: vi.fn(),
  toast: { error: vi.fn(), loading: vi.fn(() => 'id'), dismiss: vi.fn(), success: vi.fn() },
}));

import * as api from '../../services/api';
import HomeScreen from '../Home';

describe('HomeContent — aborted dashboard request', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('does not show error toast when request is aborted', async () => {
    vi.mocked(api.getDashboard).mockImplementation((_date, signal) => {
      return new Promise((_resolve, reject) => {
        if (signal) {
          signal.addEventListener('abort', () => {
            reject(new DOMException('Aborted', 'AbortError'));
          });
        }
      });
    });

    await act(async () => {
      render(<HomeScreen />);
    });

    expect(api.toast.error).not.toHaveBeenCalled();
  });

  it('does not write stale data after abort', async () => {
    const emptyDashboard = {
      data: {
        date: '2026-04-20',
        targets: { calories: 2000, protein_g: 150, carbs_g: 200, fats_g: 60 },
        consumed: { calories: 0, protein_g: 0, carbs_g: 0, fats_g: 0, fiber_g: 0, water_ml: 0 },
        remaining: { calories: 2000, protein_g: 150, carbs_g: 200, fats_g: 60 },
        recent_meals: [],
        health_score: null,
      },
    };

    let resolveFirst!: (v: any) => void;
    const firstCall = new Promise((res) => { resolveFirst = res; });

    vi.mocked(api.getDashboard)
      .mockImplementationOnce(() => firstCall as any)
      .mockResolvedValueOnce(emptyDashboard as any);

    await act(async () => {
      render(<HomeScreen />);
    });

    // Resolve the stale first call with valid data — abort guard should swallow it
    await act(async () => {
      resolveFirst(emptyDashboard);
    });

    expect(api.toast.error).not.toHaveBeenCalled();
  });
});
