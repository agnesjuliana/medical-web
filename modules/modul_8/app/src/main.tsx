import { StrictMode, useState } from "react";
import { createRoot } from "react-dom/client";
import "./index.css";
import OnboardingPage from "./components/page/OnboardingPage.tsx";
import HomeScreen from "./screens/Home.tsx";
import { ThemeProvider } from "./components/theme/ThemeProvider";

function App() {
  const [onboarded, setOnboarded] = useState(false);
  return onboarded ? <HomeScreen /> : <OnboardingPage onComplete={() => setOnboarded(true)} />;
}

createRoot(document.getElementById("root")!).render(
  <StrictMode>
    <ThemeProvider>
      <App />
    </ThemeProvider>
  </StrictMode>,
);
