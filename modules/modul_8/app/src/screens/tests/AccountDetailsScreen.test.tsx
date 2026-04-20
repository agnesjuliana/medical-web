import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import AccountDetailsScreen from '../AccountDetailsScreen';
import { getProfile } from '../../services/api';

// Mock API service
vi.mock('../../services/api', () => ({
  getProfile: vi.fn(),
}));

const mockProfileData = {
  data: {
    user_id: 1,
    gender: 'male',
    birth_date: '2001-02-02',
    height_cm: 175,
    weight_kg: 75,
    activity_level: 'active',
    goal: 'lose',
    goal_weight_kg: 70,
    step_goal: 12000,
    barriers: ['consistency'],
    onboarded_at: '2026-04-19T00:00:00Z',
  },
};

describe('AccountDetailsScreen', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('fetches and displays profile data correctly', async () => {
    (getProfile as any).mockResolvedValue(mockProfileData);

    render(<AccountDetailsScreen onClose={() => {}} />);

    // Check loading spin initially
    expect(document.querySelector('.animate-spin')).toBeInTheDocument();

    await waitFor(() => {
      expect(screen.getByText('75 kg')).toBeInTheDocument(); // Current Weight
      expect(screen.getByText('175 cm')).toBeInTheDocument(); // Height
      expect(screen.getByText('February 02, 2001')).toBeInTheDocument(); // Date of birth
      expect(screen.getByText('Male')).toBeInTheDocument(); // Gender
      expect(screen.getByText('70 kg')).toBeInTheDocument(); // Goal Weight
    });
  });

  it('handles profile fetch error', async () => {
    (getProfile as any).mockRejectedValue(new Error('Fetch error'));

    render(<AccountDetailsScreen onClose={() => {}} />);

    await waitFor(() => {
      expect(screen.getByText('Failed to load profile data')).toBeInTheDocument();
    });
  });
});
