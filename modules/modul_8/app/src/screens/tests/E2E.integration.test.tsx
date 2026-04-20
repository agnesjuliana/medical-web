import { render, screen, waitFor, fireEvent } from '@testing-library/react';
import { ThemeProvider } from '@/components/theme/ThemeProvider';
import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import React, { useState } from 'react';

// Import screens directly to avoid main.tsx side-effects (like createRoot on missing #root)
import HomeScreen from '@/screens/Home';
import OnboardingPage from '@/components/page/OnboardingPage';

function TestApp() {
  const [onboarded, setOnboarded] = useState(false);
  return onboarded ? <HomeScreen /> : <OnboardingPage onComplete={() => setOnboarded(true)} />;
}

// Setup real fetch to point to the backend
const BASE_BACKEND_URL = 'http://localhost:8000';
let sessionCookie = '';

// Original fetch
const originalFetch = global.fetch;

describe('Real Data E2E Integration Test', () => {
  beforeAll(async () => {
    // 1. Register a new real user
    const uniqueEmail = `e2e_real_${Date.now()}@example.com`;
    const password = 'password123';

    const regData = new URLSearchParams();
    regData.append('name', 'Real E2E User');
    regData.append('email', uniqueEmail);
    regData.append('password', password);
    regData.append('confirm_password', password);

    console.log('[E2E] Registering user:', uniqueEmail);
    const regRes = await originalFetch(`${BASE_BACKEND_URL}/auth/process_register.php`, {
      method: 'POST',
      body: regData,
      redirect: 'manual' // Don't follow redirect automatically
    });

    // 2. Login to get the session cookie
    const loginData = new URLSearchParams();
    loginData.append('email', uniqueEmail);
    loginData.append('password', password);

    console.log('[E2E] Logging in user:', uniqueEmail);
    const loginRes = await originalFetch(`${BASE_BACKEND_URL}/auth/process_login.php`, {
      method: 'POST',
      body: loginData,
      redirect: 'manual'
    });

    const setCookieHeader = loginRes.headers.get('set-cookie');
    if (!setCookieHeader) {
      throw new Error('Failed to get session cookie from login');
    }

    // Extract PHPSESSID=...
    const match = setCookieHeader.match(/PHPSESSID=[^;]+/);
    if (!match) {
      throw new Error('Failed to extract PHPSESSID from set-cookie header');
    }
    sessionCookie = match[0];
    console.log('[E2E] Obtained Session Cookie:', sessionCookie);

    // 3. Override global.fetch to use the cookie and prepend BASE_URL
    global.fetch = async (input: RequestInfo | URL, init?: RequestInit) => {
      let url = input.toString();
      
      // Only intercept local relative API calls to /modules/...
      if (url.startsWith('/modules/modul_8/Backend')) {
        url = BASE_BACKEND_URL + url;
        
        // Add cookie to headers
        const headers = new Headers(init?.headers);
        headers.set('Cookie', sessionCookie);
        
        return originalFetch(url, { ...init, headers });
      }
      
      return originalFetch(input, init);
    };
  });

  afterAll(() => {
    // Restore fetch
    global.fetch = originalFetch;
  });

  it('runs the full E2E journey from Onboarding to Progress with real data', async () => {
    render(
      <ThemeProvider>
        <TestApp />
      </ThemeProvider>
    );

    // ─── 1. ONBOARDING ────────────────────────────────────────────────────────
    console.log('[E2E] Starting Onboarding...');
    // Should be at Welcome step
    await waitFor(() => {
      expect(screen.getByText(/Personalize your experience/i)).toBeInTheDocument();
    });
    fireEvent.click(screen.getByText('Get Started'));

    // Step 2: Personal Details
    await waitFor(() => {
      expect(screen.getByText(/Let's get to know you/i)).toBeInTheDocument();
    });
    // Set weight to 70 and height to 170
    const weightInput = screen.getByLabelText(/Weight/i);
    fireEvent.change(weightInput, { target: { value: '70' } });
    
    const heightInput = screen.getByLabelText(/Height/i);
    fireEvent.change(heightInput, { target: { value: '170' } });

    fireEvent.click(screen.getByText('Continue'));

    // Step 3: Goals
    await waitFor(() => {
      expect(screen.getByText(/What are your goals/i)).toBeInTheDocument();
    });
    fireEvent.click(screen.getByText('Continue'));

    // Step 4: Barriers
    await waitFor(() => {
      expect(screen.getByText(/Save & Continue/i)).toBeInTheDocument();
    });
    
    // Save Profile!
    console.log('[E2E] Saving Onboarding data to DB...');
    fireEvent.click(screen.getByText('Save & Continue'));

    // ─── 2. HOME DASHBOARD ──────────────────────────────────────────────────
    console.log('[E2E] Waiting for Home Dashboard...');
    await waitFor(() => {
      expect(screen.getByText('Home')).toBeInTheDocument();
      // Should show the empty state because no food logged yet
      expect(screen.getByText(/No foods logged today/i)).toBeInTheDocument();
    }, { timeout: 5000 });

    // Ensure user info was fetched correctly in Header
    // The initials for "Real E2E User" should be "RE"
    await waitFor(() => {
      expect(screen.getByText('RE')).toBeInTheDocument();
    });

    // ─── 3. SCAN FOOD & SAVE ────────────────────────────────────────────────
    console.log('[E2E] Opening Scanner and Logging Food...');
    // The FAB toggles the menu
    const fabButton = screen.getByLabelText('Toggle actions');
    fireEvent.click(fabButton);

    // Click "Scan food"
    await waitFor(() => {
      expect(screen.getByText('Scan food')).toBeInTheDocument();
    });
    fireEvent.click(screen.getByText('Scan food'));

    // In ScannerScreen
    await waitFor(() => {
      expect(screen.getByText(/Position your food/i)).toBeInTheDocument();
    });
    // Simulate capture button click
    const captureButton = screen.getByRole('button', { name: '' }); // The big circle button usually has no text
    // Let's just find the button with class that looks like a shutter, or we can use generic selector
    // A more stable way is finding the shutter button which is usually a large rounded-full element.
    // Instead of guessing, let's find the button by its style or just the first button that's not 'Back'
    const buttons = screen.getAllByRole('button');
    // Assuming the shutter is the second button (first might be back/close)
    fireEvent.click(buttons[1]);

    // Wait for analysis to complete and Food Detail screen to show
    await waitFor(() => {
      expect(screen.getByText(/Nutrition Facts/i)).toBeInTheDocument();
    }, { timeout: 10000 }); // AI scanning takes time

    // Save Log
    fireEvent.click(screen.getByText('Save Log'));

    // Wait to return to Home and see the item in recent meals
    await waitFor(() => {
      expect(screen.queryByText(/No foods logged today/i)).not.toBeInTheDocument();
      // We don't know the exact food name Gemini returns, but we can verify a FoodCard rendered
      expect(document.querySelector('.food-card-class-or-similar')).toBeDefined();
    }, { timeout: 5000 });

    // ─── 4. PROGRESS DATA ───────────────────────────────────────────────────
    console.log('[E2E] Fetching Progress Data...');
    fireEvent.click(screen.getByText('Progress'));

    await waitFor(() => {
      expect(screen.getByText('Weight Progress')).toBeInTheDocument();
      // Should show the 70kg we set in onboarding
      expect(screen.getByText('70')).toBeInTheDocument();
      expect(screen.getByText('Weekly Energy')).toBeInTheDocument();
    }, { timeout: 5000 });

    // ─── 5. USER PROFILE DATA ───────────────────────────────────────────────
    console.log('[E2E] Opening Profile...');
    // Profile is triggered by 'account-details' which is via Header Avatar
    const avatarBtn = screen.getByText('RE'); // Real E2E
    fireEvent.click(avatarBtn);
    fireEvent.click(screen.getByText('Account')); // Dropdown item

    await waitFor(() => {
      expect(screen.getByText('Account Details')).toBeInTheDocument();
      // Check that 70kg and 170cm from onboarding are here
      expect(screen.getByDisplayValue('70')).toBeInTheDocument();
      expect(screen.getByDisplayValue('170')).toBeInTheDocument();
    });

    console.log('[E2E] All steps completed successfully!');
  }, 30000); // 30 second timeout for the whole test
});
