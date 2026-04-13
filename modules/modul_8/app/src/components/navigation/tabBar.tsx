import React from "react";
import { PanelLeft, Search } from "lucide-react";
import { useIsMobile } from "@/hooks/use-mobile";
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
  return (
    <div className="inline-flex items-center gap-1 rounded-full bg-muted p-1.5">
      {tabs.map((tab) => {
        const isActive = tab.id === activeTab;
        return (
          <button
            key={tab.id}
            type="button"
            onClick={() => onTabChange(tab.id)}
            className={cn(
              "flex flex-col items-center justify-center gap-1 rounded-full px-5 py-2 transition-all duration-200",
              isActive
                ? "bg-background text-primary shadow-sm"
                : "text-foreground hover:text-foreground/80"
            )}
          >
            {tab.icon}
            <span className="text-xs font-medium leading-none">{tab.label}</span>
          </button>
        );
      })}
    </div>
  );
}

function DesktopTabBar({
  tabs,
  activeTab,
  onTabChange,
  onSidebarToggle,
  onSearch,
}: Pick<TabBarProps, "tabs" | "activeTab" | "onTabChange" | "onSidebarToggle" | "onSearch">) {
  return (
    <div className="inline-flex items-center gap-1 rounded-full bg-muted px-3 py-1.5">
      <button
        type="button"
        onClick={onSidebarToggle}
        className="mr-2 flex items-center justify-center rounded-full p-1.5 text-foreground/60 transition-colors hover:text-foreground"
        aria-label="Toggle sidebar"
      >
        <PanelLeft size={18} />
      </button>

      {tabs.map((tab) => {
        const isActive = tab.id === activeTab;
        return (
          <button
            key={tab.id}
            type="button"
            onClick={() => onTabChange(tab.id)}
            className={cn(
              "rounded-full px-3 py-1 text-sm font-medium transition-colors duration-150",
              isActive
                ? "text-primary"
                : "text-foreground/60 hover:text-foreground"
            )}
          >
            {tab.label}
          </button>
        );
      })}

      <button
        type="button"
        onClick={onSearch}
        className="ml-2 flex items-center justify-center rounded-full p-1.5 text-foreground/60 transition-colors hover:text-foreground"
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
      <div className={cn("flex justify-center", className)}>
        <MobileTabBar
          tabs={tabs}
          activeTab={activeTab}
          onTabChange={onTabChange}
        />
      </div>
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
