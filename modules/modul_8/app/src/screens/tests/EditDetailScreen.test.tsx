import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import EditDetailScreen from '../EditDetailScreen';
import { saveProfile } from '../../services/api';

// Mock API service
vi.mock('../../services/api', () => ({
  saveProfile: vi.fn(),
  parseHeightCm: vi.fn((val) => parseInt(val) || 170),
  parseWeightKg: vi.fn((val) => parseInt(val) || 70),
  toast: {
    success: vi.fn(),
    error: vi.fn(),
  },
}));

// Mock child components from OnboardingPage to simplify tests
vi.mock('@/components/page/OnboardingPage', () => ({
  SingleSelectContent: ({ options, value, onChange }: any) => (
    <div data-testid="single-select">
      {options.map((opt: any) => (
        <button 
          key={opt.value} 
          onClick={() => onChange(opt.value)}
          data-selected={value === opt.value}
        >
          {opt.label}
        </button>
      ))}
    </div>
  ),
  BodyPickerContent: ({ form, onChange }: any) => (
    <div data-testid="body-picker">
      <input 
        data-testid="height-input" 
        value={form.height} 
        onChange={(e) => onChange('height', e.target.value)} 
      />
    </div>
  ),
  DatePickerContent: ({ form, onChange }: any) => (
    <div data-testid="date-picker">
      <input 
        data-testid="year-input" 
        value={form.birthYear} 
        onChange={(e) => onChange('birthYear', e.target.value)} 
      />
    </div>
  ),
  RulerPickerContent: ({ form, step, onChange }: any) => (
    <div data-testid="ruler-picker">
      <input 
        type="number"
        data-testid="ruler-input" 
        value={form.desiredWeight} 
        onChange={(e) => onChange(Number(e.target.value))} 
      />
    </div>
  ),
}));

const initialForm = {
  gender: 'male',
  activity: 'active',
  height: '175 cm',
  weight: '75 kg',
  birthMonth: 'February',
  birthDay: '02',
  birthYear: '2001',
  goal: 'lose',
  desiredWeight: 70,
  barriers: [],
};

describe('EditDetailScreen', () => {
  const mockOnSave = vi.fn();
  const mockOnClose = vi.fn();

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders correctly for Gender field and saves change', async () => {
    (saveProfile as any).mockResolvedValue({ data: { saved: true } });

    render(
      <EditDetailScreen 
        field="Gender" 
        initialData={{ form: initialForm, stepGoal: 10000 }} 
        onClose={mockOnClose} 
        onSave={mockOnSave} 
      />
    );

    expect(screen.getByTestId('single-select')).toBeInTheDocument();
    
    // Select Female
    fireEvent.click(screen.getByText('Female'));
    
    // Click Save
    fireEvent.click(screen.getByText('Save Changes'));

    await waitFor(() => {
      expect(saveProfile).toHaveBeenCalledWith(expect.objectContaining({
        gender: 'female',
      }));
      expect(mockOnSave).toHaveBeenCalled();
    });
  });

  it('renders correctly for Height/Weight and handles API error', async () => {
    (saveProfile as any).mockRejectedValue(new Error('Validation failed'));

    render(
      <EditDetailScreen 
        field="Height" 
        initialData={{ form: initialForm, stepGoal: 10000 }} 
        onClose={mockOnClose} 
        onSave={mockOnSave} 
      />
    );

    expect(screen.getByTestId('body-picker')).toBeInTheDocument();
    
    // Change height
    fireEvent.change(screen.getByTestId('height-input'), { target: { value: '180 cm' } });
    
    // Click Save
    fireEvent.click(screen.getByText('Save Changes'));

    await waitFor(() => {
      expect(saveProfile).toHaveBeenCalled();
      // Error toast should have been called (via the mock)
      // Check that onSave was NOT called
      expect(mockOnSave).not.toHaveBeenCalled();
    });
  });

  it('renders correctly for Goal Weight', async () => {
    (saveProfile as any).mockResolvedValue({ data: { saved: true } });

    render(
      <EditDetailScreen 
        field="Goal Weight" 
        initialData={{ form: initialForm, stepGoal: 10000 }} 
        onClose={mockOnClose} 
        onSave={mockOnSave} 
      />
    );

    expect(screen.getByTestId('ruler-picker')).toBeInTheDocument();
    
    // Change goal weight
    fireEvent.change(screen.getByTestId('ruler-input'), { target: { value: '65' } });
    
    // Click Save
    fireEvent.click(screen.getByText('Save Changes'));

    await waitFor(() => {
      expect(saveProfile).toHaveBeenCalledWith(expect.objectContaining({
        goal_weight_kg: 65,
      }));
    });
  });
});
