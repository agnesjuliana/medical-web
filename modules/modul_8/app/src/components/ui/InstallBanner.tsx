import { useState, useEffect } from "react";
import { X, Download } from "lucide-react";

interface BeforeInstallPromptEvent extends Event {
  prompt: () => Promise<void>;
  userChoice: Promise<{ outcome: "accepted" | "dismissed" }>;
}

const DISMISS_KEY = "pwa_install_dismissed";
const DISMISS_TTL = 7 * 24 * 60 * 60 * 1000; // 7 days

export default function InstallBanner() {
  const [deferredPrompt, setDeferredPrompt] = useState<BeforeInstallPromptEvent | null>(null);
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    const dismissed = localStorage.getItem(DISMISS_KEY);
    if (dismissed && Date.now() - Number(dismissed) < DISMISS_TTL) return;

    const isStandalone =
      window.matchMedia("(display-mode: standalone)").matches ||
      (window.navigator as unknown as { standalone?: boolean }).standalone === true;
    if (isStandalone) return;

    const handler = (e: Event) => {
      e.preventDefault();
      setDeferredPrompt(e as BeforeInstallPromptEvent);
      setVisible(true);
    };
    window.addEventListener("beforeinstallprompt", handler);
    return () => window.removeEventListener("beforeinstallprompt", handler);
  }, []);

  if (!visible || !deferredPrompt) return null;

  async function handleInstall() {
    if (!deferredPrompt) return;
    await deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;
    if (outcome === "accepted") setVisible(false);
  }

  function handleDismiss() {
    localStorage.setItem(DISMISS_KEY, String(Date.now()));
    setVisible(false);
  }

  return (
    <div className="fixed bottom-24 left-4 right-4 z-50 bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-4 flex items-center gap-3 border border-slate-200 dark:border-slate-700 animate-in slide-in-from-bottom-2 fade-in duration-300">
      <div className="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center shrink-0">
        <Download size={18} className="text-white" />
      </div>
      <div className="flex-1 min-w-0">
        <p className="font-semibold text-sm text-foreground">Install Calorie Tracker</p>
        <p className="text-xs text-muted-foreground truncate">Add to home screen for the best experience</p>
      </div>
      <button
        onClick={handleDismiss}
        className="text-muted-foreground hover:text-foreground shrink-0"
        aria-label="Dismiss"
      >
        <X size={16} />
      </button>
      <button
        onClick={handleInstall}
        className="text-xs font-bold bg-slate-900 text-white px-3 py-1.5 rounded-full shrink-0"
      >
        Install
      </button>
    </div>
  );
}
