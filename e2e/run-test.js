const { execSync } = require('child_process');

const args = process.argv.slice(2);

// Extract site name (first non-flag argument)
const site = args.find(a => !a.startsWith('--'));

if (!site) {
  console.error('Usage: npm run test:site -- <site-name> [--staging] [--ui] [--video] [...playwright flags]');
  console.error('');
  console.error('Examples:');
  console.error('  npm run test:site -- cannaclear');
  console.error('  npm run test:site -- cannaclear --staging');
  console.error('  npm run test:site -- cannaclear --staging --ui');
  console.error('  npm run test:site -- birdcontrol --staging --headed');
  process.exit(1);
}

// Check for our custom flags
const isStaging = args.includes('--staging');
const recordVideo = args.includes('--video');

// Remove our custom flags, pass the rest to playwright
const playwrightArgs = args.filter(a => a !== site && a !== '--staging' && a !== '--video');

// Build environment variables
const env = {
  ...process.env,
  TEST_SITE: site,
};

if (isStaging) {
  env.TEST_ENV = 'staging';
}

if (recordVideo) {
  env.RECORD_VIDEO = 'true';
}

// Build playwright command
const cmd = `npx playwright test ${playwrightArgs.join(' ')}`.trim();

console.log(`Running: ${cmd}`);
console.log(`Site: ${site} | Environment: ${isStaging ? 'staging' : 'production'}`);
console.log('');

try {
  execSync(cmd, { stdio: 'inherit', env });
} catch (e) {
  process.exit(e.status || 1);
}
