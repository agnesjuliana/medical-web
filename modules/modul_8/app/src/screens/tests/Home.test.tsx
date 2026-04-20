import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import HomeContent from '../Home';
import { getDashboard } from '../../services/api';

// Mock API service
vi.mock('../../services/api', () => ({
  getDashboard: vi.fn(),
  getUserInfo: vi.fn().mockResolvedValue({ data: { initials: 'CD' } }),
}));

// Mock components that might be problematic in tests
vi.mock('@/components/template/Daylist', () => ({
  default: ({ selectedDate, onDaySelect }: any) => (
    <div data-testid="daylist">
      <button onClick={() => onDaySelect(new Date('2026-04-21'))}>Select Next Day</button>
    </div>
  ),
}));

vi.mock('@/components/ui/Ring', () => ({
  default: ({ children, value, label }: any) => (
    <div data-testid="ring" data-value={value}>
      {children}
      {label}
    </div>
  ),
}));

const mockDashboardData = {
  data: {
    date: '2026-04-20',
    targets: {
      calories: 2000,
      protein_g: 150,
      carbs_g: 200,
      fats_g: 60,
    },
    consumed: {
      calories: 1500,
      protein_g: 100,
      carbs_g: 150,
      fats_g: 40,
      fiber_g: 25,
      water_ml: 1200,
    },
    remaining: {
      calories: 500,
      protein_g: 50,
      carbs_g: 50,
      fats_g: 20,
    },
    recent_meals: [
      {
        id: 1,
        name: 'Chicken Salad',
        calories: 400,
        protein_g: 30,
        carbs_g: 10,
        fats_g: 15,
        created_at: '2026-04-20T12:00:00Z',
      },
    ],
    health_score: 85,
  },
};

describe('HomeScreen Integration', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders loading state initially and then displays data', async () => {
    (getDashboard as any).mockResolvedValue(mockDashboardData);

    render(<HomeContent onFoodClick={() => {}} />);

    // Wait for the dashboard data to load and display
    await waitFor(() => {
      expect(screen.getByText('500')).toBeInTheDocument(); // Calories left
      expect(screen.getByText('Chicken Salad')).toBeInTheDocument();
    });
  });

  it('handles API error state', async () => {
    (getDashboard as any).mockRejectedValue(new Error('API Failure'));

    render(<HomeContent onFoodClick={() => {}} />);

    await waitFor(() => {
      expect(screen.getByText('Failed to load dashboard data')).toBeInTheDocument();
    });
  });

  it('updates data when a different date is selected', async () => {
    (getDashboard as any).mockResolvedValue(mockDashboardData);

    render(<HomeContent onFoodClick={() => {}} />);

    await waitFor(() => expect(getDashboard).toHaveBeenCalledWith(expect.stringContaining('2026-04-20')));

    const daylistBtn = screen.getByText('Select Next Day');
    fireEvent.click(daylistBtn);

    await waitFor(() => {
      expect(getDashboard).toHaveBeenCalledWith(expect.stringContaining('2026-04-21'));
    });
  });

  it('displays empty state when no meals are returned', async () => {
    const emptyData = {
      ...mockDashboardData,
      data: { ...mockDashboardData.data, recent_meals: [] },
    };
    (getDashboard as any).mockResolvedValue(emptyData);

    render(<HomeContent onFoodClick={() => {}} />);

    await waitFor(() => {
      expect(screen.getByText('No foods logged today.')).toBeInTheDocument();
    });
  });
});
