import { describe, it, expect, vi } from 'vitest';
import { parseHeightCm, parseWeightKg, saveProfile } from './api';

describe('API Service Helpers', () => {
  describe('parseHeightCm', () => {
    it('parses imperial height (feet and inches)', () => {
      expect(parseHeightCm("5'7\"")).toBe(170); // 5 * 30.48 + 7 * 2.54 = 152.4 + 17.78 = 170.18 -> 170
      expect(parseHeightCm("6'0\"")).toBe(183);
    });

    it('parses metric height (cm)', () => {
      expect(parseHeightCm("180 cm")).toBe(180);
      expect(parseHeightCm("175")).toBe(175);
    });

    it('handles defaults and invalid input', () => {
      expect(parseHeightCm("")).toBe(170);
      expect(parseHeightCm("invalid")).toBe(170);
    });
  });

  describe('parseWeightKg', () => {
    it('parses imperial weight (lbs)', () => {
      expect(parseWeightKg("154 lbs")).toBe(70); // 154 / 2.20462 = 69.85 -> 70
      expect(parseWeightKg("200 lbs")).toBe(91);
    });

    it('parses metric weight (kg)', () => {
      expect(parseWeightKg("75 kg")).toBe(75);
      expect(parseWeightKg("80")).toBe(80);
    });

    it('handles defaults and invalid input', () => {
      expect(parseWeightKg("")).toBe(70);
      expect(parseWeightKg("invalid")).toBe(70);
    });
  });
});
