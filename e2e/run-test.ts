import { execSync } from 'child_process';
import { siteConfigs, getSiteUrl, type SiteName } from './fixtures/sites';

const args = process.argv.slice(2);

// Positional args: site name and optional environment (staging/production)
// Everything starting with -- is passed through to playwright
const positionalArgs = args.filter(a => !a.startsWith('--'));
const playwrightArgs = args.filter(a => a.startsWith('--'));

const site = positionalArgs[0] as SiteName;
const envArg = positionalArgs[1] as 'staging' | 'production' | undefined;

if (!site) {
  console.error('Usage: npm run test:site -- <site-name> [staging] [--ui] [--headed] [...playwright flags]');
  console.error('');
  console.error('Examples:');
  console.error('  npm run test:site -- cannaclear              (production)');
  console.error('  npm run test:site -- cannaclear staging      (staging)');
  console.error('  npm run test:site -- cannaclear staging --ui (staging + UI mode)');
  console.error('  npm run test:site -- birdcontrol staging --headed');
  console.error('');
  console.error('Available sites:', Object.keys(siteConfigs).join(', '));
  process.exit(1);
}

if (!(site in siteConfigs)) {
  console.error(`Unknown site: "${site}"`);
  console.error('Available sites:', Object.keys(siteConfigs).join(', '));
  process.exit(1);
}

const isStaging = envArg === 'staging';
const envName = isStaging ? 'staging' : 'production';
const siteUrl = getSiteUrl(site, envName);

// Build environment variables
const env = {
  ...process.env,
  TEST_SITE: site,
  ...(isStaging && { TEST_ENV: 'staging' }),
};

// Build playwright command
const cmd = `npx playwright test ${playwrightArgs.join(' ')}`.trim();

console.log(`Running: ${cmd}`);
console.log(`Site: ${site} | Environment: ${envName} | ${siteUrl}`);
console.log('');

try {
  execSync(cmd, { stdio: 'inherit', env });
} catch (e: any) {
  process.exit(e.status || 1);
}
