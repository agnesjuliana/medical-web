import { useState, useRef, useEffect } from "react";
import { PanelLeft, Search, Plus, Dumbbell, Bookmark, Camera } from "lucide-react";
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
  onFabClick?: () => void;
  onMenuItemClick?: (id: string) => void;
  className?: string;
}

function MobileTabBar({
  tabs,
  activeTab,
  onTabChange,
  onFabClick,
  onMenuItemClick,
}: Pick<TabBarProps, "tabs" | "activeTab" | "onTabChange" | "onFabClick" | "onMenuItemClick">) {
  const [pressing, setPressing] = useState<string | null>(null);
  const tabRefs = useRef<(HTMLButtonElement | null)[]>([]);
  const iconRowRef = useRef<HTMLDivElement>(null);
  const [pillStyle, setPillStyle] = useState({ left: 0, width: 0 });
  const [ready, setReady] = useState(false);
  const [isFabOpen, setIsFabOpen] = useState(false);
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

  // Lock scroll when FAB is open
  useEffect(() => {
    if (isFabOpen) {
      document.body.style.overflow = "hidden";
    } else {
      document.body.style.overflow = "";
    }
    return () => {
      document.body.style.overflow = "";
    };
  }, [isFabOpen]);

  const menuItems = [
    { id: "log_exercise",  icon: Dumbbell, label: "Log exercise" },
    { id: "saved_foods",   icon: Bookmark, label: "Saved foods" },
    { id: "food_database", icon: Search,   label: "Food Database" },
    { id: "scan_food",     icon: Camera,   label: "Scan food" },
  ];

  return (
    <>
      {/* Overlay background */}
      {isFabOpen && (
        <div
          className="fixed inset-0 bg-black/40 backdrop-blur-sm z-[45] animate-in fade-in duration-300 pointer-events-auto"
          onClick={() => setIsFabOpen(false)}
        />
      )}

      <div className="fixed bottom-6 left-0 right-0 z-50 flex flex-col items-center gap-6 pb-safe pointer-events-none">
        {/* FAB Menu */}
        {isFabOpen && (
          <div className="grid grid-cols-2 gap-4 px-6 pointer-events-auto animate-in slide-in-from-bottom-10 fade-in duration-300 ease-out">
            {menuItems.map((item, idx) => (
              <button
                key={idx}
                className="bg-white dark:bg-slate-800 rounded-[24px] p-6 flex flex-col items-center justify-center gap-3 shadow-2xl w-[150px] h-[120px] active:scale-95 transition-transform border border-white/20"
                onClick={() => {
                  setIsFabOpen(false);
                  onMenuItemClick?.(item.id);
                }}
              >
                <item.icon size={28} className="text-black dark:text-white" />
                <span className="text-[14px] font-semibold text-black dark:text-white whitespace-nowrap">
                  {item.label}
                </span>
              </button>
            ))}
          </div>
        )}

        <div className="flex flex-row items-center gap-3 pointer-events-auto">
        <div
          ref={iconRowRef}
          className="relative flex items-center gap-1 rounded-full bg-white dark:bg-slate-800 px-2 h-14 shadow-xl"
        >
          {/* Sliding pill indicator */}
          <span
            className="absolute inset-y-1.5 rounded-full shadow-sm dark:bg-slate-700"
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
                  transition:
                    "transform 0.15s cubic-bezier(0.34, 1.56, 0.64, 1)",
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

        {/* FAB Button */}
        <button
          type="button"
          onClick={() => {
            setIsFabOpen(!isFabOpen);
            onFabClick?.();
          }}
          className="relative flex flex-none items-center justify-center w-14 h-14 rounded-full bg-black text-white shadow-xl active:scale-90 transition-transform duration-200"
          aria-label="Toggle actions"
          aria-expanded={isFabOpen}
        >
          <Plus
            size={24}
            className={cn(
              "transition-transform duration-300 ease-in-out",
              isFabOpen ? "-rotate-45" : "rotate-0",
            )}
          />
        </button>
      </div>
    </div>
    </>
  );
}

function DesktopTabBar({
  tabs,
  activeTab,
  onTabChange,
  onSidebarToggle,
  onSearch,
  onFabClick,
}: Pick<
  TabBarProps,
  | "tabs"
  | "activeTab"
  | "onTabChange"
  | "onSidebarToggle"
  | "onSearch"
  | "onFabClick"
>) {
  const [pressing, setPressing] = useState<string | null>(null);
  const tabRefs = useRef<(HTMLButtonElement | null)[]>([]);
  const containerRef = useRef<HTMLDivElement>(null);
  const [pillStyle, setPillStyle] = useState({ left: 0, width: 0 });
  const [ready, setReady] = useState(false);
  const [isFabOpen, setIsFabOpen] = useState(false);

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

  // Lock scroll when FAB is open
  useEffect(() => {
    if (isFabOpen) {
      document.body.style.overflow = "hidden";
    } else {
      document.body.style.overflow = "";
    }
    return () => {
      document.body.style.overflow = "";
    };
  }, [isFabOpen]);

  const handlePress = (id: string) => {
    setPressing(id);
    onTabChange(id);
    setTimeout(() => setPressing(null), 200);
  };

  return (
    <div className="flex flex-row items-center gap-3">
      <div
        ref={containerRef}
        className="relative inline-flex items-center gap-1 rounded-full bg-white dark:bg-slate-800 px-3 h-10 z-10 shadow-md"
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

      {/* Desktop FAB */}
      <button
        type="button"
        onClick={() => {
          setIsFabOpen(!isFabOpen);
          onFabClick?.();
        }}
        className="relative flex flex-none items-center justify-center w-10 h-10 rounded-full bg-blue-500 text-white shadow-md active:scale-90 transition-transform duration-200"
        aria-label="Toggle actions"
        aria-expanded={isFabOpen}
      >
        <Plus
          size={20}
          className={cn(
            "transition-transform duration-300 ease-in-out",
            isFabOpen ? "-rotate-45" : "rotate-0",
          )}
        />
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
  onFabClick,
  onMenuItemClick,
  className,
}: TabBarProps) {
  const isMobile = useIsMobile();

  if (isMobile) {
    return (
      <MobileTabBar
        tabs={tabs}
        activeTab={activeTab}
        onTabChange={onTabChange}
        onFabClick={onFabClick}
        onMenuItemClick={onMenuItemClick}
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
        onFabClick={onFabClick}
      />
    </div>
  );
}
