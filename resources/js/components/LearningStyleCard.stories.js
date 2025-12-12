import LearningStyleCard from './LearningStyleCard.vue';

export default {
  title: 'Learning Components/LearningStyleCard',
  component: LearningStyleCard,
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component: 'A card component that displays a student\'s learning style profile with visual indicators, scores, and personalized recommendations.',
      },
    },
  },
  tags: ['autodocs'],
  argTypes: {
    learningProfile: {
      description: 'The learning style profile data',
      control: { type: 'object' },
      table: {
        type: { summary: 'Object' },
        defaultValue: { summary: '{}' },
      },
    },
    showRecommendations: {
      description: 'Whether to show study recommendations',
      control: { type: 'boolean' },
      table: {
        type: { summary: 'boolean' },
        defaultValue: { summary: 'true' },
      },
    },
    compact: {
      description: 'Whether to use compact layout',
      control: { type: 'boolean' },
      table: {
        type: { summary: 'boolean' },
        defaultValue: { summary: 'false' },
      },
    },
    locale: {
      description: 'Language locale for content',
      control: { type: 'select' },
      options: ['id', 'en'],
      table: {
        type: { summary: 'string' },
        defaultValue: { summary: 'id' },
      },
    },
  },
  args: {
    showRecommendations: true,
    compact: false,
    locale: 'id',
  },
};

// Mock learning profile data
const createMockProfile = (dominantStyle, confidence = 0.85) => ({
  id: 1,
  student_id: 1,
  dominant_style: dominantStyle,
  visual_score: dominantStyle === 'visual' ? 4.5 : 2.8,
  auditory_score: dominantStyle === 'auditory' ? 4.2 : 2.5,
  kinesthetic_score: dominantStyle === 'kinesthetic' ? 4.1 : 3.0,
  ai_confidence_score: confidence,
  analysis_date: '2024-12-11T10:30:00Z',
  survey_data: {
    total_questions: 15,
    completion_time: 8.5,
  },
  created_at: '2024-12-11T10:30:00Z',
  updated_at: '2024-12-11T10:30:00Z',
});

// Visual learner profile
export const VisualLearner = {
  args: {
    learningProfile: createMockProfile('visual', 0.92),
  },
  parameters: {
    docs: {
      description: {
        story: 'Learning style card showing a student with visual learning preference. Note the high visual score and appropriate study recommendations.',
      },
    },
  },
};

// Auditory learner profile
export const AuditoryLearner = {
  args: {
    learningProfile: createMockProfile('auditory', 0.88),
  },
  parameters: {
    docs: {
      description: {
        story: 'Learning style card for an auditory learner, emphasizing listening-based learning strategies.',
      },
    },
  },
};

// Kinesthetic learner profile
export const KinestheticLearner = {
  args: {
    learningProfile: createMockProfile('kinesthetic', 0.78),
  },
  parameters: {
    docs: {
      description: {
        story: 'Learning style card for a kinesthetic learner, focusing on hands-on and movement-based learning approaches.',
      },
    },
  },
};

// Mixed learning style (balanced scores)
export const MixedLearner = {
  args: {
    learningProfile: {
      ...createMockProfile('visual', 0.65),
      visual_score: 3.5,
      auditory_score: 3.3,
      kinesthetic_score: 3.8,
      dominant_style: 'mixed',
    },
  },
  parameters: {
    docs: {
      description: {
        story: 'Learning style card for a student with mixed/balanced learning preferences across all three styles.',
      },
    },
  },
};

// Low confidence analysis
export const LowConfidenceAnalysis = {
  args: {
    learningProfile: {
      ...createMockProfile('visual', 0.45),
      visual_score: 3.1,
      auditory_score: 2.9,
      kinesthetic_score: 3.0,
    },
  },
  parameters: {
    docs: {
      description: {
        story: 'Learning style card showing low confidence in the AI analysis, prompting the student to retake the assessment.',
      },
    },
  },
};

// Compact layout for dashboard
export const CompactLayout = {
  args: {
    learningProfile: createMockProfile('visual'),
    compact: true,
    showRecommendations: false,
  },
  parameters: {
    docs: {
      description: {
        story: 'Compact version of the learning style card suitable for dashboard widgets or small spaces.',
      },
    },
  },
};

// Without recommendations
export const WithoutRecommendations = {
  args: {
    learningProfile: createMockProfile('auditory'),
    showRecommendations: false,
  },
  parameters: {
    docs: {
      description: {
        story: 'Learning style card displaying only the profile information without study recommendations.',
      },
    },
  },
};

// English localization
export const EnglishLocalization = {
  args: {
    learningProfile: createMockProfile('kinesthetic'),
    locale: 'en',
  },
  parameters: {
    docs: {
      description: {
        story: 'Learning style card displayed in English for international users.',
      },
    },
  },
};

// Mobile responsive layout
export const MobileLayout = {
  args: {
    learningProfile: createMockProfile('visual'),
  },
  parameters: {
    viewport: {
      defaultViewport: 'mobile1',
    },
    docs: {
      description: {
        story: 'Learning style card optimized for mobile devices with stacked layout and touch-friendly elements.',
      },
    },
  },
};

// Dark theme
export const DarkTheme = {
  args: {
    learningProfile: createMockProfile('auditory'),
  },
  parameters: {
    backgrounds: {
      default: 'dark',
    },
    docs: {
      description: {
        story: 'Learning style card in dark theme mode for better visibility in low-light conditions.',
      },
    },
  },
  decorators: [
    (story) => ({
      components: { story },
      template: '<div class="dark"><story /></div>',
    }),
  ],
};

// Loading state
export const LoadingState = {
  args: {
    learningProfile: null,
  },
  parameters: {
    docs: {
      description: {
        story: 'Loading state shown while the learning style analysis is being processed by AI.',
      },
    },
  },
};

// Error state
export const ErrorState = {
  args: {
    learningProfile: null,
    error: 'Failed to load learning profile. Please try again.',
  },
  parameters: {
    docs: {
      description: {
        story: 'Error state displayed when learning profile data cannot be loaded.',
      },
    },
  },
};

// Interactive demo with actions
export const InteractiveDemo = {
  args: {
    learningProfile: createMockProfile('visual'),
  },
  render: (args) => ({
    components: { LearningStyleCard },
    setup() {
      const handleRetakeAssessment = () => {
        console.log('Retaking assessment...');
      };
      
      const handleViewRecommendations = () => {
        console.log('Viewing detailed recommendations...');
      };
      
      return {
        args,
        handleRetakeAssessment,
        handleViewRecommendations,
      };
    },
    template: `
      <LearningStyleCard 
        v-bind="args"
        @retake-assessment="handleRetakeAssessment"
        @view-recommendations="handleViewRecommendations"
      />
    `,
  }),
  parameters: {
    docs: {
      description: {
        story: 'Interactive learning style card with clickable actions for retaking assessment and viewing recommendations.',
      },
    },
  },
};

// Multiple learning styles comparison
export const MultipleStylesComparison = {
  render: () => ({
    components: { LearningStyleCard },
    template: `
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-6xl">
        <LearningStyleCard 
          :learning-profile="{
            ...${JSON.stringify(createMockProfile('visual'))},
            dominant_style: 'visual'
          }"
          :compact="true"
        />
        <LearningStyleCard 
          :learning-profile="{
            ...${JSON.stringify(createMockProfile('auditory'))},
            dominant_style: 'auditory'
          }"
          :compact="true"
        />
        <LearningStyleCard 
          :learning-profile="{
            ...${JSON.stringify(createMockProfile('kinesthetic'))},
            dominant_style: 'kinesthetic'
          }"
          :compact="true"
        />
      </div>
    `,
  }),
  parameters: {
    layout: 'padded',
    docs: {
      description: {
        story: 'Comparison view showing different learning styles side by side, useful for teachers analyzing multiple students.',
      },
    },
  },
};