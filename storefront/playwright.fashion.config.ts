import { defineConfig } from '@playwright/test';
export default defineConfig({
  testDir: './tests/fashion', workers: 1, fullyParallel: false, timeout: 240_000,
  expect: { timeout: 15_000 }, retries: 0,
  reporter: [['list'], ['json', { outputFile: 'artifacts/fashion-redesign/browser-results.json' }]],
  outputDir: 'artifacts/fashion-redesign/test-results',
  use: { browserName: 'chromium', actionTimeout: 15_000, navigationTimeout: 30_000, viewport: { width: 393, height: 852 }, reducedMotion: 'reduce', screenshot: 'only-on-failure', trace: 'retain-on-failure' },
});
