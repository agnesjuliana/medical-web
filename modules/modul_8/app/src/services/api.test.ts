import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { getDashboard, getProgressSummary } from './api';

// ── getDashboard AbortSignal ──────────────────────────────────────────────────

describe('getDashboard', () => {
  beforeEach(() => {
    vi.stubGlobal('fetch', vi.fn());
  });

  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('passes AbortSignal to fetch', async () => {
    const mockFetch = vi.mocked(fetch);
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: {} }),
    } as Response);

    const controller = new AbortController();
    await getDashboard('2026-04-20', controller.signal);

    expect(mockFetch).toHaveBeenCalledWith(
      expect.stringContaining('get_dashboard'),
      expect.objectContaining({ signal: controller.signal }),
    );
  });

  it('rejects with AbortError when signal is aborted', async () => {
    const controller = new AbortController();
    vi.mocked(fetch).mockImplementationOnce((_url, init) => {
      if (init?.signal?.aborted) {
        const err = new DOMException('Aborted', 'AbortError');
        return Promise.reject(err);
      }
      return Promise.resolve({ ok: true, json: async () => ({ data: {} }) } as Response);
    });

    controller.abort();
    await expect(getDashboard('2026-04-20', controller.signal)).rejects.toMatchObject({
      name: 'AbortError',
    });
  });
});

// ── getProgressSummary ────────────────────────────────────────────────────────

describe('getProgressSummary', () => {
  beforeEach(() => {
    vi.stubGlobal('fetch', vi.fn());
  });

  afterEach(() => {
    vi.unstubAllGlobals();
  });

  const mockSummary = {
    weight: {
      current_weight: 72,
      start_weight: 80,
      goal_weight: 65,
      goal_progress: 53,
      height_cm: 170,
      bmi: 24.9,
      logs: [{ day: 'Mon', date: '2026-04-14', weight: 72 }],
      deltas: { '3d': -0.2, '7d': -0.5, '30d': -2.0 },
    },
    energy: {
      week_start: '2026-04-14',
      week_end: '2026-04-20',
      days: [{ day: 'Mon', date: '2026-04-14', consumed_cal: 1800 }],
      total_consumed: 1800,
    },
    calories: {
      avg_7d: 1850,
      avg_30d: 1900,
      logs_7d: [{ log_date: '2026-04-14', calories: 1800 }],
    },
  };

  it('calls get_progress_summary endpoint', async () => {
    vi.mocked(fetch).mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockSummary }),
    } as Response);

    const result = await getProgressSummary();
    expect(fetch).toHaveBeenCalledWith(
      expect.stringContaining('get_progress_summary'),
      expect.objectContaining({ signal: undefined }),
    );
    expect(result.data.weight.current_weight).toBe(72);
    expect(result.data.energy.total_consumed).toBe(1800);
    expect(result.data.calories.avg_7d).toBe(1850);
  });

  it('passes AbortSignal to fetch', async () => {
    vi.mocked(fetch).mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockSummary }),
    } as Response);

    const controller = new AbortController();
    await getProgressSummary(controller.signal);

    expect(fetch).toHaveBeenCalledWith(
      expect.any(String),
      expect.objectContaining({ signal: controller.signal }),
    );
  });

  it('throws on non-ok response', async () => {
    vi.mocked(fetch).mockResolvedValueOnce({
      ok: false,
      status: 500,
    } as Response);

    await expect(getProgressSummary()).rejects.toThrow('Failed to fetch progress summary');
  });
});
