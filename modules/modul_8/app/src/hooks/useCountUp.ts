import { useState, useEffect, useRef } from 'react';

/**
 * Hook that animates a number counting up from 0 to the target value.
 * Uses requestAnimationFrame for smooth 60fps animation.
 *
 * @param target   - The final number to count up to
 * @param duration - Animation duration in milliseconds (default: 2000ms)
 * @param delay    - Delay before starting animation in ms (default: 0)
 * @returns The current animated number value
 */
export function useCountUp(target: number, duration: number = 2000, delay: number = 0): number {
  const [count, setCount] = useState(0);
  const rafRef = useRef<number | null>(null);
  const startTimeRef = useRef<number | null>(null);

  useEffect(() => {
    // Reset
    setCount(0);
    startTimeRef.current = null;

    if (target <= 0) {
      setCount(0);
      return;
    }

    const timeoutId = setTimeout(() => {
      const animate = (timestamp: number) => {
        if (startTimeRef.current === null) {
          startTimeRef.current = timestamp;
        }

        const elapsed = timestamp - startTimeRef.current;
        const progress = Math.min(elapsed / duration, 1);

        // Ease-out cubic for a satisfying deceleration
        const eased = 1 - Math.pow(1 - progress, 3);
        const currentValue = Math.round(eased * target);

        setCount(currentValue);

        if (progress < 1) {
          rafRef.current = requestAnimationFrame(animate);
        }
      };

      rafRef.current = requestAnimationFrame(animate);
    }, delay);

    return () => {
      if (timeoutId) clearTimeout(timeoutId);
      if (rafRef.current !== null) {
        cancelAnimationFrame(rafRef.current);
      }
    };
  }, [target, duration, delay]);

  return count;
}
