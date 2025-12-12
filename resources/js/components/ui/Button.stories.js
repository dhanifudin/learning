import { Button } from './button';

export default {
  title: 'UI Components/Button',
  component: Button,
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component: 'A versatile button component that supports multiple variants, sizes, and accessibility features for the learning platform.',
      },
    },
  },
  tags: ['autodocs'],
  argTypes: {
    variant: {
      control: { type: 'select' },
      options: ['default', 'destructive', 'outline', 'secondary', 'ghost', 'link'],
      description: 'The visual variant of the button',
      table: {
        type: { summary: 'string' },
        defaultValue: { summary: 'default' },
      },
    },
    size: {
      control: { type: 'select' },
      options: ['default', 'sm', 'lg', 'icon'],
      description: 'The size of the button',
      table: {
        type: { summary: 'string' },
        defaultValue: { summary: 'default' },
      },
    },
    disabled: {
      control: { type: 'boolean' },
      description: 'Whether the button is disabled',
      table: {
        type: { summary: 'boolean' },
        defaultValue: { summary: 'false' },
      },
    },
    asChild: {
      control: { type: 'boolean' },
      description: 'Render as child component',
      table: {
        type: { summary: 'boolean' },
        defaultValue: { summary: 'false' },
      },
    },
    onClick: {
      action: 'clicked',
      description: 'Function called when button is clicked',
    },
  },
  args: {
    disabled: false,
    asChild: false,
  },
};

// Default button story
export const Default = {
  args: {
    children: 'Button',
  },
  parameters: {
    docs: {
      description: {
        story: 'The default button style used throughout the learning platform.',
      },
    },
  },
};

// Primary button for main actions
export const Primary = {
  args: {
    children: 'Start Learning',
    variant: 'default',
  },
  parameters: {
    docs: {
      description: {
        story: 'Primary button used for main call-to-action elements like "Start Learning" or "Submit Assessment".',
      },
    },
  },
};

// Secondary button for less prominent actions
export const Secondary = {
  args: {
    children: 'View Details',
    variant: 'secondary',
  },
  parameters: {
    docs: {
      description: {
        story: 'Secondary button used for supporting actions like "View Details" or "Learn More".',
      },
    },
  },
};

// Outline button for alternative actions
export const Outline = {
  args: {
    children: 'Cancel',
    variant: 'outline',
  },
  parameters: {
    docs: {
      description: {
        story: 'Outline button used for cancellation or alternative actions.',
      },
    },
  },
};

// Destructive button for dangerous actions
export const Destructive = {
  args: {
    children: 'Delete Content',
    variant: 'destructive',
  },
  parameters: {
    docs: {
      description: {
        story: 'Destructive button used for potentially harmful actions like deleting content.',
      },
    },
  },
};

// Ghost button for subtle actions
export const Ghost = {
  args: {
    children: 'Skip',
    variant: 'ghost',
  },
  parameters: {
    docs: {
      description: {
        story: 'Ghost button used for subtle actions that don\'t need visual emphasis.',
      },
    },
  },
};

// Link-style button
export const Link = {
  args: {
    children: 'View All Recommendations',
    variant: 'link',
  },
  parameters: {
    docs: {
      description: {
        story: 'Link-style button used for navigation or inline actions.',
      },
    },
  },
};

// Size variants
export const Small = {
  args: {
    children: 'Small Button',
    size: 'sm',
  },
  parameters: {
    docs: {
      description: {
        story: 'Small button used in compact spaces or for secondary actions.',
      },
    },
  },
};

export const Large = {
  args: {
    children: 'Large Button',
    size: 'lg',
  },
  parameters: {
    docs: {
      description: {
        story: 'Large button used for prominent call-to-action elements.',
      },
    },
  },
};

// Disabled state
export const Disabled = {
  args: {
    children: 'Disabled Button',
    disabled: true,
  },
  parameters: {
    docs: {
      description: {
        story: 'Disabled button state that prevents user interaction.',
      },
    },
  },
};

// Educational context examples
export const LearningActions = {
  render: () => ({
    components: { Button },
    template: `
      <div class="space-y-4">
        <div class="space-x-2">
          <Button variant="default">Start Assessment</Button>
          <Button variant="outline">Preview Questions</Button>
        </div>
        <div class="space-x-2">
          <Button variant="secondary">Save Progress</Button>
          <Button variant="ghost">Skip Section</Button>
        </div>
        <div class="space-x-2">
          <Button variant="destructive" size="sm">Reset Answers</Button>
          <Button variant="link" size="sm">Get Help</Button>
        </div>
      </div>
    `,
  }),
  parameters: {
    docs: {
      description: {
        story: 'Examples of button usage in educational contexts like assessments and learning activities.',
      },
    },
  },
};

// Mobile responsiveness
export const MobileLayout = {
  render: () => ({
    components: { Button },
    template: `
      <div class="w-80 space-y-4 p-4 bg-gray-50 rounded-lg">
        <h3 class="text-lg font-semibold mb-4">Mobile Learning Actions</h3>
        <Button class="w-full" size="lg">Continue Lesson</Button>
        <div class="grid grid-cols-2 gap-2">
          <Button variant="outline">Bookmark</Button>
          <Button variant="secondary">Share</Button>
        </div>
        <Button variant="ghost" size="sm" class="w-full">View Transcript</Button>
      </div>
    `,
  }),
  parameters: {
    viewport: {
      defaultViewport: 'mobile1',
    },
    docs: {
      description: {
        story: 'Button layouts optimized for mobile devices in learning scenarios.',
      },
    },
  },
};

// Accessibility demonstration
export const AccessibilityDemo = {
  render: () => ({
    components: { Button },
    template: `
      <div class="space-y-4">
        <Button 
          class="focus-educational" 
          aria-describedby="help-text"
        >
          Submit Assignment
        </Button>
        <p id="help-text" class="text-sm text-gray-600">
          Click to submit your completed assignment for grading
        </p>
        
        <Button 
          variant="outline"
          aria-label="Add to favorites"
          title="Add this content to your favorites"
        >
          ⭐ Favorite
        </Button>
        
        <Button 
          variant="secondary"
          aria-pressed="false"
          role="button"
          tabindex="0"
        >
          Toggle Notifications
        </Button>
      </div>
    `,
  }),
  parameters: {
    docs: {
      description: {
        story: 'Examples of accessible button implementations with proper ARIA attributes and focus management.',
      },
    },
  },
};

// Indonesian localization
export const IndonesianLocalization = {
  render: () => ({
    components: { Button },
    template: `
      <div class="space-y-4">
        <div class="space-x-2">
          <Button variant="default">Mulai Belajar</Button>
          <Button variant="outline">Lihat Detail</Button>
        </div>
        <div class="space-x-2">
          <Button variant="secondary">Simpan Kemajuan</Button>
          <Button variant="ghost">Lewati Bagian</Button>
        </div>
        <div class="space-x-2">
          <Button variant="destructive" size="sm">Hapus Jawaban</Button>
          <Button variant="link" size="sm">Minta Bantuan</Button>
        </div>
      </div>
    `,
  }),
  parameters: {
    docs: {
      description: {
        story: 'Button text examples in Bahasa Indonesia for the local educational context.',
      },
    },
  },
};