# Learning Platform Component Library

## Overview

This document provides comprehensive documentation for the Vue.js component library used in the AI-Powered Learning System. The components are designed with accessibility, responsiveness, and Indonesian educational context in mind.

## Design Principles

### 1. Educational Focus
All components are designed specifically for educational workflows:
- Learning style adaptation
- Assessment interfaces
- Progress tracking
- Analytics visualization
- Student-teacher interaction

### 2. Accessibility (WCAG 2.1 AA)
- Screen reader compatibility
- Keyboard navigation support
- High contrast mode support
- Focus management
- ARIA attributes

### 3. Internationalization
- Indonesian (Bahasa Indonesia) as primary language
- English as secondary language
- Cultural adaptation for Indonesian education system
- Right-to-left text support where needed

### 4. Mobile-First Design
- Responsive layouts for all screen sizes
- Touch-friendly interactive elements
- Optimized for Indonesian mobile usage patterns
- Progressive Web App compatibility

### 5. Performance Optimization
- Lazy loading for heavy components
- Code splitting for better load times
- Optimized images and assets
- Minimal bundle size

## Component Categories

### 1. UI Foundation Components

#### Button Component
**Location:** `@/components/ui/button`

**Purpose:** Versatile button component for all user interactions

**Variants:**
- `default` - Primary action button
- `secondary` - Supporting actions
- `outline` - Alternative actions
- `destructive` - Dangerous actions
- `ghost` - Subtle actions
- `link` - Navigation actions

**Sizes:**
- `sm` - Compact spaces
- `default` - Standard size
- `lg` - Prominent actions
- `icon` - Icon-only buttons

**Educational Usage:**
- "Mulai Belajar" (Start Learning)
- "Kirim Jawaban" (Submit Answers)
- "Lihat Hasil" (View Results)
- "Ulangi Penilaian" (Retake Assessment)

#### Card Component
**Location:** `@/components/ui/card`

**Purpose:** Container component for grouped content

**Features:**
- Consistent spacing and shadows
- Hover states for interactive cards
- Support for dark mode
- Mobile-optimized layouts

### 2. Learning-Specific Components

#### LearningStyleCard
**Location:** `@/components/LearningStyleCard.vue`

**Purpose:** Display student's learning style profile with recommendations

**Features:**
- Visual, Auditory, Kinesthetic score display
- Confidence indicator from AI analysis
- Personalized study recommendations
- Progress bars for score visualization
- Action buttons for retaking assessment

**Data Structure:**
```typescript
interface LearningStyleProfile {
  id: number;
  student_id: number;
  dominant_style: 'visual' | 'auditory' | 'kinesthetic' | 'mixed';
  visual_score: number;
  auditory_score: number;
  kinesthetic_score: number;
  ai_confidence_score: number;
  analysis_date: string;
  survey_data?: object;
}
```

#### RecommendationCard
**Location:** `@/components/RecommendationCard.vue`

**Purpose:** Display AI-generated content recommendations

**Features:**
- Relevance score indicator
- Content type badges
- Learning style alignment
- Quick action buttons
- Reason for recommendation

#### ProgressTracker
**Location:** `@/components/ProgressTracker.vue`

**Purpose:** Visualize learning progress and milestones

**Features:**
- Progress bars and circular indicators
- Milestone markers
- Achievement badges
- Time-based progress tracking
- Subject-specific progress

### 3. Analytics Components

#### MetricCard
**Location:** `@/components/analytics/MetricCard.vue`

**Purpose:** Display key performance metrics

**Features:**
- Numeric value display
- Trend indicators
- Comparison with previous periods
- Icon support for metric types

#### ChartWrapper
**Location:** `@/components/analytics/ChartWrapper.vue`

**Purpose:** Wrapper for chart libraries with consistent styling

**Supported Charts:**
- Line charts for trend analysis
- Bar charts for comparisons
- Pie charts for distributions
- Radar charts for learning style profiles

### 4. Form Components

#### AssessmentForm
**Location:** `@/components/forms/AssessmentForm.vue`

**Purpose:** Handle assessment question presentation and submission

**Features:**
- Multiple question types
- Progress indicator
- Time tracking
- Answer validation
- Review mode

#### SurveyForm
**Location:** `@/components/forms/SurveyForm.vue`

**Purpose:** Learning style survey and feedback collection

**Features:**
- Likert scale questions
- Progress tracking
- Validation
- Multi-language support

### 5. Navigation Components

#### StudentNavigation
**Location:** `@/components/navigation/StudentNavigation.vue`

**Purpose:** Main navigation for student interface

**Features:**
- Dashboard, Content, Analytics, Profile sections
- Active state indicators
- Responsive mobile menu
- Notification indicators

#### Breadcrumbs
**Location:** `@/components/navigation/Breadcrumbs.vue`

**Purpose:** Navigation breadcrumbs for deep pages

**Features:**
- Hierarchical navigation
- Custom separators
- Truncation for long paths

## Component Usage Guidelines

### 1. Importing Components

```vue
<script setup>
import { Button } from '@/components/ui/button'
import LearningStyleCard from '@/components/LearningStyleCard.vue'
import { Card, CardContent, CardHeader } from '@/components/ui/card'
</script>
```

### 2. Styling Guidelines

#### CSS Classes
- Use Tailwind CSS utility classes
- Follow the design system color palette
- Implement responsive breakpoints
- Support dark mode with `dark:` prefix

#### Custom Properties
```css
:root {
  --color-educational-primary: hsl(214 80% 52%);
  --color-indonesian-red: hsl(348 83% 47%);
  --color-learning-success: hsl(160 84% 39%);
  --space-content: 1.5rem;
  --radius-card: 0.75rem;
}
```

### 3. Accessibility Implementation

#### ARIA Attributes
```vue
<template>
  <button
    :aria-pressed="isActive"
    :aria-describedby="helpTextId"
    :aria-label="buttonLabel"
    class="focus-educational"
  >
    {{ buttonText }}
  </button>
</template>
```

#### Focus Management
```vue
<script setup>
import { useAccessibility } from '@/composables/useAccessibility'

const { manageFocus, trapFocus, announceToScreenReader } = useAccessibility()

const handleModalOpen = () => {
  trapFocus(modalElement.value)
  announceToScreenReader('Modal opened')
}
</script>
```

### 4. Internationalization

#### Using Translation Keys
```vue
<template>
  <div>
    <h1>{{ $t('dashboard.welcome', { name: studentName }) }}</h1>
    <p>{{ $t('analytics.overview') }}</p>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()

// Switch language
const switchLanguage = (newLocale) => {
  locale.value = newLocale
}
</script>
```

#### Cultural Adaptation
- Use appropriate color schemes for Indonesian context
- Adapt date/time formats
- Consider local educational terminology
- Respect cultural learning preferences

### 5. Performance Optimization

#### Lazy Loading
```vue
<script setup>
import { defineAsyncComponent } from 'vue'

const HeavyChart = defineAsyncComponent(() =>
  import('@/components/charts/HeavyChart.vue')
)
</script>
```

#### Image Optimization
```vue
<template>
  <img
    :src="getOptimizedImageSrc(originalSrc)"
    :alt="imageAlt"
    loading="lazy"
    class="learning-content-image"
  />
</template>
```

## Testing Components

### 1. Unit Testing with Vitest
```javascript
import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import LearningStyleCard from '@/components/LearningStyleCard.vue'

describe('LearningStyleCard', () => {
  it('displays visual learning style correctly', () => {
    const wrapper = mount(LearningStyleCard, {
      props: {
        learningProfile: {
          dominant_style: 'visual',
          visual_score: 4.5,
          // ... other props
        }
      }
    })
    
    expect(wrapper.text()).toContain('Visual')
    expect(wrapper.find('[data-testid="visual-score"]').text()).toBe('4.5/5')
  })
})
```

### 2. Accessibility Testing
```javascript
import { axe, toHaveNoViolations } from 'jest-axe'

expect.extend(toHaveNoViolations)

it('should not have accessibility violations', async () => {
  const wrapper = mount(LearningStyleCard, { props })
  const results = await axe(wrapper.element)
  expect(results).toHaveNoViolations()
})
```

### 3. Visual Testing with Storybook
- Run `npm run storybook` to view components
- Test different states and variants
- Verify responsive behavior
- Check dark mode compatibility

## Best Practices

### 1. Component Design
- Keep components focused and single-purpose
- Use composition over inheritance
- Implement proper prop validation
- Provide meaningful default values

### 2. State Management
- Use Vue 3 Composition API
- Implement reactive state with `ref()` and `reactive()`
- Share state through composables
- Avoid prop drilling with provide/inject

### 3. Error Handling
- Provide error boundaries for components
- Display user-friendly error messages
- Implement fallback UI states
- Log errors for debugging

### 4. Documentation
- Write comprehensive component stories
- Document props and events
- Provide usage examples
- Explain accessibility features

## Migration Guide

### From Vue 2 to Vue 3
If migrating components:
1. Update to Composition API
2. Replace `$listeners` with `attrs`
3. Update event handling
4. Migrate to `<script setup>`

### Breaking Changes
Document any breaking changes between versions:
- Prop name changes
- Event signature updates
- CSS class modifications
- Accessibility improvements

## Contributing

### 1. Component Guidelines
- Follow the established design patterns
- Implement full accessibility support
- Include comprehensive tests
- Write detailed documentation

### 2. Review Process
- Code review required for all changes
- Accessibility audit for new components
- Performance impact assessment
- Documentation updates

### 3. Release Process
- Version components semantically
- Update changelog
- Run full test suite
- Deploy to Storybook

## Resources

### External Libraries
- **Vue 3:** Framework foundation
- **Tailwind CSS:** Utility-first styling
- **Headless UI:** Accessible UI components
- **Chart.js:** Data visualization
- **Vue I18n:** Internationalization

### Design Resources
- **Figma Design System:** [Link to design files]
- **Indonesian Education Guidelines:** [Link to guidelines]
- **Accessibility Checklist:** [Link to WCAG checklist]

### Development Tools
- **Storybook:** Component development and documentation
- **Vitest:** Unit testing framework
- **Cypress:** End-to-end testing
- **ESLint:** Code quality
- **Prettier:** Code formatting