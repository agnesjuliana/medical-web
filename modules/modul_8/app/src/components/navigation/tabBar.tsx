import { useState, useRef, useEffect } from "react";
import { PanelLeft, Search } from "lucide-react";
import { useIsMobile } from "@/hooks/use-mobile";
import { useTheme } from "next-themes";
import { cn } from "@/lib/utils";

export interface TabItem {
  id: string;
  label: string;
  icon: React.ReactNode;
}

export interface TabBarProps {
  tabs: TabItem[];
  activeTab: string;
  onTabChange: (id: string) => void;
  onSidebarToggle?: () => void;
  onSearch?: () => void;
  className?: string;
}

function MobileTabBar({
  tabs,
  activeTab,
  onTabChange,
}: Pick<TabBarProps, "tabs" | "activeTab" | "onTabChange">) {
  const [pressing, setPressing] = useState<string | null>(null);
  const tabRefs = useRef<(HTMLButtonElement | null)[]>([]);
  const iconRowRef = useRef<HTMLDivElement>(null);
  const [pillStyle, setPillStyle] = useState({ left: 0, width: 0 });
  const [ready, setReady] = useState(false);
  const { theme } = useTheme();
  const isDark = theme === "dark";

  // Measure active tab position and slide the pill
  useEffect(() => {
    const activeIndex = tabs.findIndex((t) => t.id === activeTab);
    const el = tabRefs.current[activeIndex];
    const row = iconRowRef.current;
    if (!el || !row) return;

    const rowRect = row.getBoundingClientRect();
    const elRect = el.getBoundingClientRect();

    setPillStyle({
      left: elRect.left - rowRect.left,
      width: elRect.width,
    });

    setReady(true);
  }, [activeTab, tabs]);

  const handlePress = (id: string) => {
    setPressing(id);
    onTabChange(id);
    setTimeout(() => setPressing(null), 300);
  };

  return (
    <div className="fixed bottom-6 left-0 right-0 z-50 flex justify-center pb-safe pointer-events-none">
      <div
        ref={iconRowRef}
        className="relative flex items-center gap-1 rounded-full bg-white dark:bg-slate-800 px-2 py-2 shadow-xl pointer-events-auto"
      >
        {/* Sliding pill indicator */}
        <span
          className="absolute inset-y-2 rounded-full shadow-sm dark:bg-slate-700"
          style={{
            backgroundColor: "rgb(229, 231, 235)", // gray-200 (light mode)
            left: pillStyle.left,
            width: pillStyle.width,
            transition: ready
              ? "left 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), width 0.35s ease"
              : "none",
          }}
        />

        {tabs.map((tab, i) => {
          const isActive = tab.id === activeTab;
          const isPressing = pressing === tab.id;

          return (
            <button
              key={tab.id}
              ref={(el) => {
                tabRefs.current[i] = el;
              }}
              type="button"
              onClick={() => handlePress(tab.id)}
              className="relative z-10 flex flex-col items-center justify-center gap-0.5 px-5 py-2 rounded-full"
              style={{
                transform: isPressing ? "scale(0.88)" : "scale(1)",
                transition: "transform 0.15s cubic-bezier(0.34, 1.56, 0.64, 1)",
              }}
            >
              {/* Icon */}
              <span
                className="tab-icon flex items-center justify-center"
                style={{
                  color: isActive
                    ? "rgb(96, 165, 250)" // blue-400
                    : isDark
                      ? "rgb(148, 163, 184)" // slate-400 (dark)
                      : "rgb(107, 114, 128)", // gray-500 (light)
                  transform: isActive ? "scale(1.15)" : "scale(1)",
                  transition:
                    "transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.2s ease",
                }}
              >
                {tab.icon}
              </span>

              {/* Label */}
              <span
                className="text-[10px] font-medium"
                style={{
                  color: isActive
                    ? "rgb(96, 165, 250)" // blue-400
                    : isDark
                      ? "rgb(148, 163, 184)" // slate-400 (dark)
                      : "rgb(107, 114, 128)", // gray-500 (light)
                  transition: "color 0.2s ease",
                }}
              >
                {tab.label}
              </span>
            </button>
          );
        })}
      </div>
    </div>
  );
}

function DesktopTabBar({
  tabs,
  activeTab,
  onTabChange,
  onSidebarToggle,
  onSearch,
}: Pick<
  TabBarProps,
  "tabs" | "activeTab" | "onTabChange" | "onSidebarToggle" | "onSearch"
>) {
  const [pressing, setPressing] = useState<string | null>(null);
  const tabRefs = useRef<(HTMLButtonElement | null)[]>([]);
  const containerRef = useRef<HTMLDivElement>(null);
  const [pillStyle, setPillStyle] = useState({ left: 0, width: 0 });
  const [ready, setReady] = useState(false);

  useEffect(() => {
    const activeIndex = tabs.findIndex((t) => t.id === activeTab);
    const el = tabRefs.current[activeIndex];
    const container = containerRef.current;
    if (!el || !container) return;

    const containerRect = container.getBoundingClientRect();
    const elRect = el.getBoundingClientRect();

    setPillStyle({
      left: elRect.left - containerRect.left,
      width: elRect.width,
    });
    setReady(true);
  }, [activeTab, tabs]);

  const handlePress = (id: string) => {
    setPressing(id);
    onTabChange(id);
    setTimeout(() => setPressing(null), 200);
  };

  return (
    <div
      ref={containerRef}
      className="relative inline-flex items-center gap-1 rounded-full bg-white dark:bg-slate-800 px-3 py-1.5 z-10 shadow-md"
    >
      {/* Sliding pill */}
      <span
        className="absolute inset-y-1.5 rounded-full bg-background shadow-sm"
        style={{
          backgroundColor: "rgb(229, 231, 235)",
          left: pillStyle.left,
          width: pillStyle.width,
          transition: ready
            ? "left 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), width 0.3s ease"
            : "none",
        }}
      />

      <button
        type="button"
        onClick={onSidebarToggle}
        className="relative z-10 mr-2 flex items-center justify-center rounded-full p-1.5 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors"
        style={{ transition: "transform 0.15s ease" }}
        onMouseDown={(e) => (e.currentTarget.style.transform = "scale(0.88)")}
        onMouseUp={(e) => (e.currentTarget.style.transform = "scale(1)")}
        onMouseLeave={(e) => (e.currentTarget.style.transform = "scale(1)")}
        aria-label="Toggle sidebar"
      >
        <PanelLeft size={18} />
      </button>

      {tabs.map((tab, i) => {
        const isActive = tab.id === activeTab;
        const isPressing = pressing === tab.id;

        return (
          <button
            key={tab.id}
            ref={(el) => {
              tabRefs.current[i] = el;
            }}
            type="button"
            onClick={() => handlePress(tab.id)}
            className={cn(
              "relative z-10 rounded-full px-3 py-1 text-sm font-medium cursor-pointer",
              isActive
                ? "text-blue-500 dark:text-blue-400"
                : "text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white",
            )}
            style={{
              transform: isPressing ? "scale(0.92)" : "scale(1)",
              transition:
                "transform 0.15s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.2s ease",
            }}
          >
            {tab.label}
          </button>
        );
      })}

      <button
        type="button"
        onClick={onSearch}
        className="relative z-10 ml-2 flex items-center justify-center rounded-full p-1.5 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors"
        style={{ transition: "transform 0.15s ease" }}
        onMouseDown={(e) => (e.currentTarget.style.transform = "scale(0.88)")}
        onMouseUp={(e) => (e.currentTarget.style.transform = "scale(1)")}
        onMouseLeave={(e) => (e.currentTarget.style.transform = "scale(1)")}
        aria-label="Search"
      >
        <Search size={18} />
      </button>
    </div>
  );
}

export default function TabBar({
  tabs,
  activeTab,
  onTabChange,
  onSidebarToggle,
  onSearch,
  className,
}: TabBarProps) {
  const isMobile = useIsMobile();

  if (isMobile) {
    return (
      <MobileTabBar
        tabs={tabs}
        activeTab={activeTab}
        onTabChange={onTabChange}
      />
    );
  }

  return (
    <div className={cn("flex justify-center", className)}>
      <DesktopTabBar
        tabs={tabs}
        activeTab={activeTab}
        onTabChange={onTabChange}
        onSidebarToggle={onSidebarToggle}
        onSearch={onSearch}
      />
    </div>
  );
}
