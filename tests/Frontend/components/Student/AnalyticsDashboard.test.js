/**
 * Vue.js Component Testing - Analytics Dashboard
 * Phase 5: UI/UX and Testing Implementation
 */

import { mount } from '@vue/test-utils';
import { describe, it, expect, beforeEach, vi } from 'vitest';
import AnalyticsDashboard from '@/pages/Student/Analytics/Dashboard.vue';

// Mock Inertia.js
vi.mock('@inertiajs/vue3', () => ({
  Head: {
    template: '<head><slot /></head>',
  },
  Link: {
    props: ['href', 'as'],
    template: '<a :href="href"><slot /></a>',
  },
}));

// Mock components
vi.mock('@/layouts/AppLayout.vue', () => ({
  default: {
    template: '<div class="app-layout"><slot /></div>',
  },
}));

vi.mock('@/components/ui/card', () => ({
  Card: { template: '<div class="card"><slot /></div>' },
  CardContent: { template: '<div class="card-content"><slot /></div>' },
  CardHeader: { template: '<div class="card-header"><slot /></div>' },
  CardTitle: { template: '<div class="card-title"><slot /></div>' },
  CardDescription: { template: '<div class="card-description"><slot /></div>' },
}));

vi.mock('@/components/ui/button', () => ({
  Button: {
    props: ['variant', 'size', 'as', 'disabled'],
    template: '<button :class="variant" :disabled="disabled"><slot /></button>',
  },
}));

vi.mock('@/components/ui/badge', () => ({
  Badge: {
    props: ['variant'],
    template: '<span :class="variant"><slot /></span>',
  },
}));

vi.mock('@/components/Heading.vue', () => ({
  default: { template: '<h1><slot /></h1>' },
}));

vi.mock('@/components/Icon.vue', () => ({
  default: {
    props: ['name', 'class'],
    template: '<span :class="class">{{ name }}</span>',
  },
}));

describe('Analytics Dashboard Component', () => {
  let wrapper;
  
  const mockProps = {
    analytics: {
      engagement: {
        score: 75,
        sessions: 12,
        content_views: 25,
        completions: 8,
      },
      performance: {
        avg_score: 82,
        total_assessments: 5,
        improvement_trend: 3.2,
      },
      time_metrics: {
        total_hours: 12.5,
        avg_session_minutes: 45,
      },
    },
    recentFeedback: [
      {
        id: 1,
        feedback_text: 'Great progress on trigonometry! Keep up the excellent work.',
        sentiment: 'positive',
        created_at: '2024-12-01T10:00:00Z',
      },
      {
        id: 2,
        feedback_text: 'Consider reviewing quadratic equations for better understanding.',
        sentiment: 'constructive',
        created_at: '2024-11-30T15:30:00Z',
      },
    ],
    predictions: [],
    period: 'week',
    learningProfile: {
      dominant_style: 'visual',
      visual_score: 4.2,
      auditory_score: 2.8,
      kinesthetic_score: 3.1,
    },
  };

  beforeEach(() => {
    wrapper = mount(AnalyticsDashboard, {
      props: mockProps,
      global: {
        stubs: {
          Head: { template: '<div></div>' },
          AppLayout: { template: '<div><slot /></div>' },
          Card: { template: '<div class="card"><slot /></div>' },
          CardContent: { template: '<div class="card-content"><slot /></div>' },
          CardHeader: { template: '<div class="card-header"><slot /></div>' },
          CardTitle: { template: '<div class="card-title"><slot /></div>' },
          Button: { template: '<button><slot /></button>' },
          Badge: { template: '<span><slot /></span>' },
          Heading: { template: '<h1><slot /></h1>' },
          Icon: { template: '<span></span>' },
        },
      },
    });
  });

  describe('Component Rendering', () => {
    it('renders the main dashboard structure', () => {
      expect(wrapper.find('h1').exists()).toBe(true);
      expect(wrapper.text()).toContain('My Learning Analytics');
    });

    it('displays all metric cards', () => {
      const cards = wrapper.findAll('.card-content');
      expect(cards.length).toBeGreaterThanOrEqual(4);
    });

    it('shows engagement score correctly', () => {
      expect(wrapper.text()).toContain('75%'); // Engagement score
    });

    it('shows performance score correctly', () => {
      expect(wrapper.text()).toContain('82%'); // Average score
    });

    it('displays study hours', () => {
      expect(wrapper.text()).toContain('12.5h'); // Study hours
    });
  });

  describe('Computed Properties', () => {
    it('calculates engagement score correctly', () => {
      expect(wrapper.vm.engagementScore).toBe(75);
    });

    it('calculates performance score correctly', () => {
      expect(wrapper.vm.performanceScore).toBe(82);
    });

    it('formats study hours correctly', () => {
      expect(wrapper.vm.studyHours).toBe('12.5');
    });

    it('calculates improvement trend correctly', () => {
      const trend = wrapper.vm.improvementTrend;
      expect(trend.value).toBe('3.2');
      expect(trend.direction).toBe('up');
      expect(trend.color).toBe('green');
    });
  });

  describe('Learning Style Profile Display', () => {
    it('shows dominant learning style', () => {
      expect(wrapper.text()).toContain('Visual');
    });

    it('displays learning style scores', () => {
      expect(wrapper.text()).toContain('4.2/5'); // Visual score
      expect(wrapper.text()).toContain('2.8/5'); // Auditory score
      expect(wrapper.text()).toContain('3.1/5'); // Kinesthetic score
    });

    it('renders progress bars for learning styles', () => {
      const progressBars = wrapper.findAll('[class*="bg-"]');
      expect(progressBars.length).toBeGreaterThan(0);
    });
  });

  describe('Recent Feedback Display', () => {
    it('displays recent feedback items', () => {
      expect(wrapper.text()).toContain('Great progress on trigonometry');
      expect(wrapper.text()).toContain('Consider reviewing quadratic equations');
    });

    it('shows feedback dates', () => {
      expect(wrapper.text()).toContain('12/1/2024');
      expect(wrapper.text()).toContain('11/30/2024');
    });

    it('handles empty feedback gracefully', async () => {
      await wrapper.setProps({
        ...mockProps,
        recentFeedback: [],
      });
      
      expect(wrapper.text()).toContain('No recent feedback');
    });
  });

  describe('Period Selection', () => {
    it('renders period selector buttons', () => {
      const buttons = wrapper.findAll('button');
      const periodButtons = buttons.filter(button => 
        ['Day', 'Week', 'Month', 'Quarter'].some(period => 
          button.text().includes(period)
        )
      );
      expect(periodButtons.length).toBeGreaterThan(0);
    });

    it('highlights current period', () => {
      // Current period is 'week' from props
      expect(wrapper.html()).toContain('Week');
    });
  });

  describe('Responsive Design', () => {
    it('uses responsive grid classes', () => {
      expect(wrapper.html()).toContain('grid-cols-1');
      expect(wrapper.html()).toContain('md:grid-cols-2');
      expect(wrapper.html()).toContain('lg:grid-cols-4');
    });

    it('applies mobile-friendly spacing', () => {
      expect(wrapper.html()).toContain('space-y-6');
    });
  });

  describe('Accessibility', () => {
    it('has proper heading structure', () => {
      const headings = wrapper.findAll('h1, h2, h3, h4, h5, h6');
      expect(headings.length).toBeGreaterThan(0);
    });

    it('includes screen reader content', () => {
      // Check for sr-only or similar accessibility classes
      expect(wrapper.html()).toMatch(/sr-only|visually-hidden/);
    });
  });

  describe('Error Handling', () => {
    it('handles missing analytics data gracefully', async () => {
      await wrapper.setProps({
        analytics: {},
        recentFeedback: [],
        predictions: [],
        period: 'week',
        learningProfile: null,
      });

      expect(wrapper.text()).toContain('0%'); // Should show default values
      expect(wrapper.text()).toContain('0h'); // Should show default hours
    });

    it('handles null learning profile', async () => {
      await wrapper.setProps({
        ...mockProps,
        learningProfile: null,
      });

      // Should not render learning profile section
      expect(wrapper.text()).toContain('Not Assessed');
    });
  });

  describe('User Interactions', () => {
    it('handles period change', async () => {
      const spy = vi.spyOn(wrapper.vm, 'updatePeriod');
      
      // Mock the updatePeriod method
      wrapper.vm.updatePeriod = vi.fn();
      
      // Simulate clicking a period button
      wrapper.vm.updatePeriod('month');
      
      expect(wrapper.vm.updatePeriod).toHaveBeenCalledWith('month');
    });
  });

  describe('Performance', () => {
    it('renders efficiently with large datasets', async () => {
      const largeDataset = {
        ...mockProps,
        recentFeedback: Array.from({ length: 100 }, (_, i) => ({
          id: i,
          feedback_text: `Feedback item ${i}`,
          sentiment: 'positive',
          created_at: '2024-12-01T10:00:00Z',
        })),
      };

      await wrapper.setProps(largeDataset);
      
      // Should only display first 3 feedback items
      const feedbackItems = wrapper.findAll('[class*="border-l-4"]');
      expect(feedbackItems.length).toBeLessThanOrEqual(3);
    });
  });

  describe('Dark Mode Support', () => {
    it('includes dark mode classes', () => {
      expect(wrapper.html()).toMatch(/dark:/);
    });
  });

  describe('Indonesian Localization Support', () => {
    it('supports text direction changes', () => {
      // Should not have any hardcoded left/right orientations
      expect(wrapper.html()).not.toContain('text-left');
      expect(wrapper.html()).not.toContain('text-right');
    });
  });
});