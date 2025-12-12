import { setup } from '@storybook/vue3';
import { INITIAL_VIEWPORTS } from '@storybook/addon-viewport';

// Import the CSS
import '../resources/css/app.css';

// Setup Vue 3
setup((app) => {
  // Add global properties, plugins, etc.
  app.config.globalProperties.$t = (key, values) => {
    // Simple translation mock for Storybook
    const translations = {
      'common.loading': 'Loading...',
      'common.save': 'Save',
      'common.cancel': 'Cancel',
      'dashboard.welcome': 'Welcome, {name}!',
      'analytics.title': 'Learning Analytics',
      'learning_styles.visual.name': 'Visual',
      'learning_styles.auditory.name': 'Auditory',
      'learning_styles.kinesthetic.name': 'Kinesthetic',
    };
    
    let result = translations[key] || key;
    
    if (values) {
      Object.entries(values).forEach(([placeholder, value]) => {
        result = result.replace(`{${placeholder}}`, value);
      });
    }
    
    return result;
  };
});

/** @type { import('@storybook/vue3').Preview } */
const preview = {
  parameters: {
    actions: { argTypesRegex: "^on[A-Z].*" },
    controls: {
      matchers: {
        color: /(background|color)$/i,
        date: /Date$/,
      },
    },
    
    // Viewport configuration for responsive testing
    viewport: {
      viewports: {
        ...INITIAL_VIEWPORTS,
        mobile1: {
          name: 'Mobile Portrait',
          styles: {
            width: '375px',
            height: '667px',
          },
        },
        mobile2: {
          name: 'Mobile Landscape',
          styles: {
            width: '667px',
            height: '375px',
          },
        },
        tablet: {
          name: 'Tablet',
          styles: {
            width: '768px',
            height: '1024px',
          },
        },
        desktop: {
          name: 'Desktop',
          styles: {
            width: '1200px',
            height: '800px',
          },
        },
      },
      defaultViewport: 'desktop',
    },
    
    // Background options
    backgrounds: {
      default: 'light',
      values: [
        {
          name: 'light',
          value: '#ffffff',
        },
        {
          name: 'dark',
          value: '#1a1a1a',
        },
        {
          name: 'educational',
          value: '#f8fafc',
        },
      ],
    },
    
    // Documentation configuration
    docs: {
      toc: true,
    },
  },
  
  globalTypes: {
    locale: {
      description: 'Internationalization locale',
      defaultValue: 'id',
      toolbar: {
        icon: 'globe',
        items: [
          { value: 'id', title: 'Bahasa Indonesia' },
          { value: 'en', title: 'English' },
        ],
      },
    },
    theme: {
      description: 'Global theme for components',
      defaultValue: 'light',
      toolbar: {
        icon: 'paintbrush',
        items: [
          { value: 'light', title: 'Light Theme' },
          { value: 'dark', title: 'Dark Theme' },
        ],
      },
    },
  },
  
  decorators: [
    (story, context) => {
      // Apply theme to HTML element
      const theme = context.globals.theme || 'light';
      document.documentElement.className = theme === 'dark' ? 'dark' : '';
      
      // Apply locale
      const locale = context.globals.locale || 'id';
      document.documentElement.lang = locale;
      
      return {
        components: { story },
        template: '<div class="storybook-wrapper p-4"><story /></div>',
      };
    },
  ],
};

export default preview;