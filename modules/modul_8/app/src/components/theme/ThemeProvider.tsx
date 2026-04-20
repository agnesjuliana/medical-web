import { ThemeProvider as NextThemesProvider } from "next-themes";
import type { ReactNode } from "react";

interface ThemeProviderProps {
  children: ReactNode;
}

export function ThemeProvider({ children }: ThemeProviderProps) {
  return (
    <NextThemesProvider
      attribute="class"
      defaultTheme="light"
      forcedTheme="light"
      enableSystem={false}
      enableColorScheme={false}
      storageKey="theme-preference"
      disableTransitionOnChange={false}
    >
      {children}
    </NextThemesProvider>
  );
}
