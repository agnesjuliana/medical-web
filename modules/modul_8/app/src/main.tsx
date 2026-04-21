import { StrictMode, useState, useEffect, Component } from "react";
import type { ReactNode } from "react";
import { createRoot } from "react-dom/client";
import "./index.css";
import OnboardingPage from "./components/page/OnboardingPage.tsx";
import HomeScreen from "./screens/Home.tsx";
import { ThemeProvider } from "./components/theme/ThemeProvider";
import { useProfileStore } from "./store/profileStore";
import { getProfile } from "./services/api";
import "./lib/logger"; // init global error capture

const ONBOARDED_KEY = "m8_onboarded";

class ErrorBoundary extends Component<{ children: ReactNode }, { error: string | null }> {
  state = { error: null };
  static getDerivedStateFromError(e: Error) { return { error: e.message }; }
  render() {
    if (this.state.error) {
      return (
        <div style={{ padding: 24, fontFamily: "monospace", color: "red", background: "#fff", minHeight: "100vh" }}>
          <strong>Render error:</strong>
          <pre style={{ whiteSpace: "pre-wrap", marginTop: 8 }}>{this.state.error}</pre>
        </div>
      );
    }
    return this.props.children;
  }
}

export function App() {
  // localStorage = fast-path to avoid flicker; server = source of truth
  const [onboarded, setOnboarded] = useState(
    () => localStorage.getItem(ONBOARDED_KEY) === "1",
  );
  const [checking, setChecking] = useState(true);
  const fetchProfile = useProfileStore((s) => s.fetchProfile);

  useEffect(() => {
    getProfile()
      .then((res) => {
        const serverOnboarded = !!res.data?.onboarded_at;
        if (serverOnboarded) {
          localStorage.setItem(ONBOARDED_KEY, "1");
          setOnboarded(true);
          fetchProfile();
        } else {
          localStorage.removeItem(ONBOARDED_KEY);
          setOnboarded(false);
        }
      })
      .catch((err) => {
        console.warn("[App] Profile fetch failed, using localStorage state:", err);
      })
      .finally(() => setChecking(false));
  }, []);

  function handleOnboardingComplete() {
    localStorage.setItem(ONBOARDED_KEY, "1");
    setOnboarded(true);
    fetchProfile();
  }

  if (checking) {
    return (
      <div className="min-h-screen bg-slate-950 flex items-center justify-center">
        <div className="w-8 h-8 border-2 border-cyan-400 border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  return onboarded ? (
    <HomeScreen />
  ) : (
    <OnboardingPage onComplete={handleOnboardingComplete} />
  );
}

createRoot(document.getElementById("root")!).render(
  <StrictMode>
    <ErrorBoundary>
      <ThemeProvider>
        <App />
      </ThemeProvider>
    </ErrorBoundary>
  </StrictMode>,
);
