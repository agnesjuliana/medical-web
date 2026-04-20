import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { render, screen } from '@testing-library/react';
import Header from '../../components/header/Header';

vi.mock('../../services/api', () => ({
  getUserInfo: vi.fn(),
  logout: vi.fn(),
  deleteAccount: vi.fn(),
  toast: { error: vi.fn() },
}));

import * as api from '../../services/api';

describe('Header — initials from window.__USER__', () => {
  afterEach(() => {
    delete (window as any).__USER__;
    vi.clearAllMocks();
  });

  it('uses initials from window.__USER__.initials without calling getUserInfo', () => {
    (window as any).__USER__ = { id: 1, name: 'Alice Smith', email: 'a@b.com', initials: 'AS' };

    render(<Header title="Home" subtitle="Today" />);

    // initials rendered in avatar fallbacks
    const fallbacks = screen.getAllByText('AS');
    expect(fallbacks.length).toBeGreaterThan(0);
    expect(api.getUserInfo).not.toHaveBeenCalled();
  });

  it('computes initials from window.__USER__.name when initials field absent', () => {
    (window as any).__USER__ = { id: 1, name: 'Bob Jones', email: 'b@j.com' };

    render(<Header title="Home" subtitle="Today" />);

    const fallbacks = screen.getAllByText('BJ');
    expect(fallbacks.length).toBeGreaterThan(0);
    expect(api.getUserInfo).not.toHaveBeenCalled();
  });

  it('calls getUserInfo when window.__USER__ is absent', async () => {
    vi.mocked(api.getUserInfo).mockResolvedValueOnce({
      data: { id: 2, name: 'Carol', email: 'c@d.com', initials: 'C' },
    });

    render(<Header title="Home" subtitle="Today" />);

    expect(api.getUserInfo).toHaveBeenCalledTimes(1);
  });
});
