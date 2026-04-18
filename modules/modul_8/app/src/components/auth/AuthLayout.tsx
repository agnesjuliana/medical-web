import React from 'react';

export function AuthLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="min-h-screen bg-base flex flex-col relative overflow-x-hidden">
      {/* Visual Header (Mobile First / Responsive) */}
      <div className="relative w-full h-[35vh] md:h-[40vh] bg-surface overflow-hidden flex-shrink-0">
        <div className="absolute inset-0 bg-gradient-to-t from-base via-transparent to-transparent z-10" />
        <img 
          src="/healthy_food.png" 
          alt="Healthy Food" 
          className="absolute inset-0 object-cover w-full h-full opacity-40 dark:opacity-60 scale-110 blur-[2px]"
          draggable="false"
        />
        
        {/* Logo Overlay */}
        <div className="absolute top-8 left-8 z-20">
          <div className="w-10 h-10 bg-primary rounded-full items-center justify-center flex font-bold text-lg text-white shadow-md">
            M
          </div>
        </div>

        {/* Text Overlay */}
        <div className="absolute bottom-6 left-8 z-20">
          <h1 className="text-3xl font-extrabold tracking-tight text-text-main">
            Fuel Your <span className="text-primary">Body</span>
          </h1>
          <p className="text-text-muted font-medium text-sm mt-1 max-w-[250px]">
            Discover healthy habits and take control.
          </p>
        </div>
      </div>
      
      {/* Form Content Area */}
      <div className="flex-1 flex flex-col items-center justify-start p-6 pt-2 pb-32 md:pb-12 w-full max-w-xl mx-auto z-20">
          <div className="w-full bg-base rounded-t-[32px] -mt-6 pt-8 px-2 shadow-sm border-t border-black/5 dark:border-white/5">
             {children}
          </div>
      </div>

      {/* Ambient Glows (Kept as requested) */}
      <div className="fixed top-1/2 left-[-10%] w-[60%] h-[60%] bg-primary/10 rounded-full blur-[120px] pointer-events-none -z-10 animate-pulse duration-[10000ms]" />
      <div className="fixed bottom-[-10%] right-[-10%] w-[50%] h-[50%] bg-blue-500/5 rounded-full blur-[100px] pointer-events-none -z-10" />
    </div>
  );
}
