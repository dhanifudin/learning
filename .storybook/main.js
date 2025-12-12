import { dirname, join } from "path";

/** @type { import('@storybook/vue3-vite').StorybookConfig } */
const config = {
  stories: [
    "../resources/js/**/*.stories.@(js|jsx|ts|tsx|mdx)",
    "../resources/js/**/*.story.@(js|jsx|ts|tsx)",
  ],
  
  addons: [
    getAbsolutePath("@storybook/addon-links"),
    getAbsolutePath("@storybook/addon-essentials"),
    getAbsolutePath("@storybook/addon-interactions"),
    getAbsolutePath("@storybook/addon-a11y"),
    getAbsolutePath("@storybook/addon-docs"),
    getAbsolutePath("@storybook/addon-controls"),
    getAbsolutePath("@storybook/addon-viewport"),
    getAbsolutePath("@storybook/addon-backgrounds"),
  ],
  
  framework: {
    name: getAbsolutePath("@storybook/vue3-vite"),
    options: {},
  },
  
  typescript: {
    check: false,
    reactDocgen: "react-docgen-typescript",
    reactDocgenTypescriptOptions: {
      shouldExtractLiteralValuesFromEnum: true,
      propFilter: (prop) => (prop.parent ? !/node_modules/.test(prop.parent.fileName) : true),
    },
  },
  
  viteFinal: async (config) => {
    // Customize Vite config for Storybook
    config.resolve.alias = {
      ...config.resolve.alias,
      '@': new URL('../resources/js', import.meta.url).pathname,
    };
    
    // Add support for Vue files
    config.plugins = config.plugins || [];
    
    return config;
  },
  
  docs: {
    autodocs: 'tag',
    defaultName: 'Documentation',
  },

  features: {
    buildStoriesJson: true,
  },
};

export default config;

/**
 * This function is used to resolve the absolute path of a package.
 * It is needed in projects that use Yarn PnP or are set up within a monorepo.
 */
function getAbsolutePath(value) {
  return dirname(require.resolve(join(value, "package.json")));
}