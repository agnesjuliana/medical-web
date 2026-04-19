import React from 'react';
import { TrendingDown, TrendingUp, Minus } from 'lucide-react';
import { cn } from "@/lib/utils";

interface ChangeRowProps {
  timeframe: string;
  trendIcon?: React.ReactNode;
  value: string;
  changeText: string;
  changeStatus: 'increase' | 'decrease' | 'none';
}

const ChangeRow: React.FC<ChangeRowProps> = ({
  timeframe,
  trendIcon,
  value,
  changeText,
  changeStatus
}) => {
  const statusStyles = {
    increase: {
      color: 'text-orange-500',
      icon: <TrendingUp className="w-4 h-4" />
    },
    decrease: {
      color: 'text-orange-600',
      icon: <TrendingDown className="w-4 h-4" />
    },
    none: {
      color: 'text-blue-500',
      icon: <Minus className="w-4 h-4" />
    }
  };

  const currentStatus = statusStyles[changeStatus];

  return (
    <div className="grid grid-cols-4 items-center py-3 border-b border-gray-100 last:border-0 dark:border-gray-800">
      <div className="col-span-1 text-gray-500 text-sm">{timeframe}</div>
      <div className="col-span-1 flex justify-center">
        {trendIcon || <div className="w-8 h-4 bg-gray-100 rounded-sm dark:bg-gray-800" />}
      </div>
      <div className="col-span-1 text-black font-medium dark:text-white">{value}</div>
      <div className={cn("col-span-1 flex items-center justify-end gap-1 text-xs", currentStatus.color)}>
        {currentStatus.icon}
        <span>{changeText}</span>
      </div>
    </div>
  );
};

export default ChangeRow;
