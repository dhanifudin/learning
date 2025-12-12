const { defineConfig } = require('cypress')

module.exports = defineConfig({
  e2e: {
    baseUrl: 'http://localhost:8000',
    viewportWidth: 1280,
    viewportHeight: 720,
    video: true,
    screenshotOnRunFailure: true,
    defaultCommandTimeout: 10000,
    requestTimeout: 15000,
    responseTimeout: 15000,
    
    // Test file patterns
    specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
    
    // Support file
    supportFile: 'cypress/support/e2e.js',
    
    // Environment variables
    env: {
      API_URL: 'http://localhost:8000/api',
      COVERAGE: false,
    },
    
    setupNodeEvents(on, config) {
      // Cypress plugins and tasks
      on('task', {
        log(message) {
          console.log(message)
          return null
        },
        
        // Database seeding task
        seedDatabase() {
          // This would trigger Laravel artisan commands
          return new Promise((resolve) => {
            const { exec } = require('child_process');
            exec('php artisan migrate:fresh --seed', (error, stdout, stderr) => {
              if (error) {
                console.error(`Error: ${error}`);
                resolve(false);
              } else {
                console.log(`Database seeded: ${stdout}`);
                resolve(true);
              }
            });
          });
        },
        
        // Performance testing helpers
        measurePerformance() {
          return {
            startTime: Date.now()
          };
        }
      })
      
      // Browser configuration
      on('before:browser:launch', (browser = {}, launchOptions) => {
        if (browser.name === 'chrome') {
          launchOptions.args.push('--disable-dev-shm-usage');
          launchOptions.args.push('--no-sandbox');
          launchOptions.args.push('--disable-gpu');
        }
        
        return launchOptions;
      });
      
      return config;
    },
    
    // Mobile device testing
    retries: {
      runMode: 2,
      openMode: 0
    }
  },
  
  component: {
    devServer: {
      framework: 'vue',
      bundler: 'vite',
    },
    specPattern: 'cypress/component/**/*.cy.{js,jsx,ts,tsx}',
    indexHtmlFile: 'cypress/support/component-index.html',
  },
  
  // Global configuration
  chromeWebSecurity: false,
  modifyObstructiveCode: false,
  
  // Accessibility testing
  env: {
    'cypress-axe': {
      context: {},
      options: {}
    }
  }
})