declare module 'react-activity-rings' {
  import { FC } from 'react';

  export interface ActivityRingsData {
    value: number;
  }

  export interface ActivityRingsConfig {
    width: number;
    height: number;
    gap?: number;
    radius?: number;
    strokeWidth?: number;
    colors?: string[];
  }

  export interface ActivityRingsProps {
    data: ActivityRingsData[];
    config: ActivityRingsConfig;
  }

  const ActivityRings: FC<ActivityRingsProps>;
  export default ActivityRings;
}
