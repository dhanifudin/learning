/**
 * End-to-End Testing: Complete Student Learning Journey
 * Phase 5: UI/UX and Testing Implementation
 */

describe('Complete Student Learning Journey', () => {
  before(() => {
    // Seed database with test data
    cy.task('seedDatabase');
  });

  beforeEach(() => {
    // Visit the application
    cy.visit('/');
    
    // Mock API responses for consistent testing
    cy.intercept('POST', '/api/gemini/analyze-learning-style', {
      statusCode: 200,
      body: {
        dominant_style: 'visual',
        confidence_score: 0.92,
        visual_score: 4.5,
        auditory_score: 2.8,
        kinesthetic_score: 3.1
      }
    }).as('analyzeLearningStyle');
    
    cy.intercept('GET', '/student/analytics', { fixture: 'student-analytics.json' }).as('getAnalytics');
    cy.intercept('GET', '/student/recommendations', { fixture: 'recommendations.json' }).as('getRecommendations');
  });

  describe('Student Registration and Profile Setup', () => {
    it('should complete the full registration and profile setup flow', () => {
      // Step 1: Register as new student
      cy.visit('/register');
      
      // Fill registration form
      cy.get('[data-cy=name-input]').type('Ahmad Ridwan');
      cy.get('[data-cy=email-input]').type('ahmad.ridwan@student.test');
      cy.get('[data-cy=password-input]').type('SecurePassword123!');
      cy.get('[data-cy=password-confirmation-input]').type('SecurePassword123!');
      cy.get('[data-cy=register-button]').click();

      // Should redirect to profile setup
      cy.url().should('include', '/profile/setup');
      
      // Step 2: Complete biodata
      cy.get('[data-cy=student-number-input]').type('STD2024001');
      cy.get('[data-cy=grade-select]').select('11');
      cy.get('[data-cy=class-input]').type('11 IPA 1');
      cy.get('[data-cy=major-select]').select('IPA');
      cy.get('[data-cy=biodata-next-button]').click();

      // Step 3: Input grades from report card
      cy.get('[data-cy=grade-input-MTK]').type('85');
      cy.get('[data-cy=grade-input-FIS]').type('78');
      cy.get('[data-cy=grade-input-KIM]').type('82');
      cy.get('[data-cy=grade-input-BIO]').type('89');
      cy.get('[data-cy=grades-next-button]').click();

      // Step 4: Take learning style assessment
      cy.get('[data-cy=take-survey-button]').click();
      
      // Fill out learning style survey
      // Visual learning questions (high scores)
      for (let i = 1; i <= 5; i++) {
        cy.get(`[data-cy=question-${i}] input[value="5"]`).check();
      }
      
      // Auditory learning questions (medium scores)
      for (let i = 6; i <= 10; i++) {
        cy.get(`[data-cy=question-${i}] input[value="3"]`).check();
      }
      
      // Kinesthetic learning questions (medium scores)
      for (let i = 11; i <= 15; i++) {
        cy.get(`[data-cy=question-${i}] input[value="3"]`).check();
      }
      
      cy.get('[data-cy=submit-survey-button]').click();
      
      // Wait for AI analysis
      cy.wait('@analyzeLearningStyle');
      
      // Should redirect to dashboard after completion
      cy.url().should('include', '/student/dashboard');
      cy.get('[data-cy=welcome-message]').should('contain', 'Ahmad Ridwan');
    });
  });

  describe('Learning Style Assessment Results', () => {
    beforeEach(() => {
      // Login as existing student
      cy.login('ahmad.ridwan@student.test', 'SecurePassword123!');
    });

    it('should display learning style profile correctly', () => {
      cy.visit('/student/learning-profile');
      
      // Check dominant learning style is displayed
      cy.get('[data-cy=dominant-style]').should('contain', 'Visual');
      
      // Check learning style scores are displayed
      cy.get('[data-cy=visual-score]').should('contain', '4.5');
      cy.get('[data-cy=auditory-score]').should('contain', '2.8');
      cy.get('[data-cy=kinesthetic-score]').should('contain', '3.1');
      
      // Check radar chart is visible
      cy.get('[data-cy=learning-style-chart]').should('be.visible');
      
      // Check study recommendations are provided
      cy.get('[data-cy=study-recommendations]').should('contain', 'Visual');
      cy.get('[data-cy=retake-survey-button]').should('be.visible');
    });
  });

  describe('Content Discovery and Consumption', () => {
    beforeEach(() => {
      cy.login('ahmad.ridwan@student.test', 'SecurePassword123!');
    });

    it('should browse and consume learning content', () => {
      // Navigate to content library
      cy.visit('/student/content');
      
      // Check content filters are available
      cy.get('[data-cy=subject-filter]').should('be.visible');
      cy.get('[data-cy=content-type-filter]').should('be.visible');
      cy.get('[data-cy=difficulty-filter]').should('be.visible');
      
      // Filter by subject
      cy.get('[data-cy=subject-filter]').select('Mathematics');
      cy.get('[data-cy=apply-filters-button]').click();
      
      // Check filtered results
      cy.get('[data-cy=content-grid] [data-cy=content-card]').should('have.length.at.least', 1);
      
      // Click on first content item
      cy.get('[data-cy=content-grid] [data-cy=content-card]').first().click();
      
      // Should navigate to content view
      cy.url().should('include', '/student/content/');
      
      // Check content player/viewer is loaded
      cy.get('[data-cy=content-viewer]').should('be.visible');
      
      // Mark content as completed
      cy.get('[data-cy=mark-complete-button]').click();
      cy.get('[data-cy=completion-message]').should('be.visible');
    });

    it('should display personalized recommendations', () => {
      cy.visit('/student/recommendations');
      cy.wait('@getRecommendations');
      
      // Check recommendations are displayed
      cy.get('[data-cy=recommendation-grid] [data-cy=recommendation-card]')
        .should('have.length.at.least', 1);
      
      // Check recommendation reasons are provided
      cy.get('[data-cy=recommendation-card]').first()
        .find('[data-cy=recommendation-reason]')
        .should('be.visible');
      
      // Check relevance scores are displayed
      cy.get('[data-cy=recommendation-card]').first()
        .find('[data-cy=relevance-score]')
        .should('contain', '%');
    });
  });

  describe('Assessment Taking and Results', () => {
    beforeEach(() => {
      cy.login('ahmad.ridwan@student.test', 'SecurePassword123!');
    });

    it('should take an assessment and view results', () => {
      // Navigate to assessments
      cy.visit('/student/assessments');
      
      // Select an available assessment
      cy.get('[data-cy=assessment-list] [data-cy=assessment-item]').first().click();
      
      // Start assessment
      cy.get('[data-cy=start-assessment-button]').click();
      
      // Answer questions (mock assessment)
      cy.get('[data-cy=question-1] input[value="a"]').check();
      cy.get('[data-cy=question-2] input[value="b"]').check();
      cy.get('[data-cy=question-3] input[value="c"]').check();
      
      // Submit assessment
      cy.get('[data-cy=submit-assessment-button]').click();
      cy.get('[data-cy=confirm-submit-button]').click();
      
      // View results
      cy.get('[data-cy=assessment-score]').should('be.visible');
      cy.get('[data-cy=assessment-feedback]').should('contain', 'feedback');
      cy.get('[data-cy=improvement-suggestions]').should('be.visible');
    });
  });

  describe('Analytics and Progress Tracking', () => {
    beforeEach(() => {
      cy.login('ahmad.ridwan@student.test', 'SecurePassword123!');
    });

    it('should display comprehensive analytics dashboard', () => {
      cy.visit('/student/analytics');
      cy.wait('@getAnalytics');
      
      // Check key metrics are displayed
      cy.get('[data-cy=engagement-score]').should('contain', '%');
      cy.get('[data-cy=average-score]').should('contain', '%');
      cy.get('[data-cy=study-hours]').should('contain', 'h');
      cy.get('[data-cy=improvement-trend]').should('be.visible');
      
      // Check learning style profile is displayed
      cy.get('[data-cy=learning-style-profile]').should('be.visible');
      cy.get('[data-cy=dominant-style-badge]').should('contain', 'Visual');
      
      // Check recent activity summary
      cy.get('[data-cy=recent-activity]').should('be.visible');
      cy.get('[data-cy=content-views-count]').should('be.visible');
      cy.get('[data-cy=completions-count]').should('be.visible');
      
      // Check recent feedback
      cy.get('[data-cy=recent-feedback]').should('be.visible');
      
      // Test period selector
      cy.get('[data-cy=period-selector] button').contains('Month').click();
      cy.url().should('include', 'period=month');
    });

    it('should handle different time periods correctly', () => {
      cy.visit('/student/analytics');
      
      const periods = ['day', 'week', 'month', 'quarter'];
      
      periods.forEach(period => {
        cy.get(`[data-cy=period-selector] button`).contains(period.charAt(0).toUpperCase() + period.slice(1)).click();
        cy.url().should('include', `period=${period}`);
        cy.get('[data-cy=analytics-content]').should('be.visible');
      });
    });
  });

  describe('Responsive Design Testing', () => {
    beforeEach(() => {
      cy.login('ahmad.ridwan@student.test', 'SecurePassword123!');
    });

    it('should work correctly on mobile devices', () => {
      // Test iPhone X viewport
      cy.viewport('iphone-x');
      cy.visit('/student/dashboard');
      
      // Check mobile navigation
      cy.get('[data-cy=mobile-menu-button]').should('be.visible').click();
      cy.get('[data-cy=mobile-navigation]').should('be.visible');
      
      // Check content is properly responsive
      cy.get('[data-cy=dashboard-content]').should('be.visible');
      cy.get('[data-cy=learning-style-card]').should('be.visible');
      
      // Test touch interactions
      cy.get('[data-cy=recommendation-card]').first().click();
    });

    it('should work correctly on tablets', () => {
      // Test iPad viewport
      cy.viewport('ipad-2');
      cy.visit('/student/analytics');
      
      // Check tablet layout
      cy.get('[data-cy=analytics-grid]').should('have.class', 'md:grid-cols-2');
      cy.get('[data-cy=metric-cards]').should('be.visible');
      
      // Test swipe gestures (if implemented)
      cy.get('[data-cy=analytics-content]').trigger('touchstart', { which: 1 });
      cy.get('[data-cy=analytics-content]').trigger('touchmove', { which: 1 });
      cy.get('[data-cy=analytics-content]').trigger('touchend');
    });
  });

  describe('Performance Testing', () => {
    beforeEach(() => {
      cy.login('ahmad.ridwan@student.test', 'SecurePassword123!');
    });

    it('should load pages within acceptable time limits', () => {
      const performanceTest = (url, maxTime = 3000) => {
        const startTime = Date.now();
        cy.visit(url);
        cy.get('[data-cy=page-content]').should('be.visible').then(() => {
          const loadTime = Date.now() - startTime;
          expect(loadTime).to.be.lessThan(maxTime);
        });
      };

      // Test critical pages
      performanceTest('/student/dashboard', 3000);
      performanceTest('/student/content', 3000);
      performanceTest('/student/analytics', 5000); // Analytics might take longer
      performanceTest('/student/recommendations', 3000);
    });

    it('should handle large datasets efficiently', () => {
      // Navigate to analytics with large dataset
      cy.intercept('GET', '/student/analytics/data', { fixture: 'large-analytics-dataset.json' });
      cy.visit('/student/analytics');
      
      // Check page still loads within reasonable time
      cy.get('[data-cy=analytics-content]').should('be.visible');
      cy.get('[data-cy=metric-cards]').should('be.visible');
    });
  });

  describe('Accessibility Testing', () => {
    beforeEach(() => {
      cy.login('ahmad.ridwan@student.test', 'SecurePassword123!');
      cy.injectAxe(); // Inject axe-core for accessibility testing
    });

    it('should meet WCAG 2.1 AA standards', () => {
      cy.visit('/student/dashboard');
      
      // Run accessibility audit
      cy.checkA11y();
      
      // Test keyboard navigation
      cy.get('body').tab(); // Should focus on skip link or first interactive element
      cy.focused().should('be.visible');
      
      // Test with high contrast mode
      cy.get('html').invoke('addClass', 'high-contrast');
      cy.checkA11y();
    });

    it('should support screen readers', () => {
      cy.visit('/student/analytics');
      
      // Check for proper heading structure
      cy.get('h1').should('have.length', 1);
      cy.get('h2').should('have.length.at.least', 1);
      
      // Check for alt text on images
      cy.get('img').each(($img) => {
        cy.wrap($img).should('have.attr', 'alt');
      });
      
      // Check for form labels
      cy.get('input, select, textarea').each(($input) => {
        cy.wrap($input).should('have.attr', 'aria-label').or('have.attr', 'id');
      });
    });

    it('should support keyboard navigation', () => {
      cy.visit('/student/content');
      
      // Test tab navigation through interactive elements
      cy.get('body').tab();
      cy.focused().should('be.visible');
      
      cy.get('body').tab();
      cy.focused().should('be.visible');
      
      // Test Enter key activation
      cy.get('[data-cy=content-card]').first().focus().type('{enter}');
      cy.url().should('include', '/student/content/');
    });
  });

  describe('Error Handling and Edge Cases', () => {
    beforeEach(() => {
      cy.login('ahmad.ridwan@student.test', 'SecurePassword123!');
    });

    it('should handle API failures gracefully', () => {
      // Mock API failure
      cy.intercept('GET', '/student/analytics', { statusCode: 500 }).as('analyticsError');
      
      cy.visit('/student/analytics');
      cy.wait('@analyticsError');
      
      // Should show error message
      cy.get('[data-cy=error-message]').should('be.visible');
      cy.get('[data-cy=retry-button]').should('be.visible');
    });

    it('should handle network connectivity issues', () => {
      // Simulate offline mode
      cy.window().then((win) => {
        win.navigator.onLine = false;
        win.dispatchEvent(new Event('offline'));
      });
      
      cy.visit('/student/dashboard');
      
      // Should show offline indicator
      cy.get('[data-cy=offline-indicator]').should('be.visible');
      
      // Restore online mode
      cy.window().then((win) => {
        win.navigator.onLine = true;
        win.dispatchEvent(new Event('online'));
      });
      
      cy.get('[data-cy=offline-indicator]').should('not.exist');
    });

    it('should validate form inputs properly', () => {
      cy.visit('/profile/setup');
      
      // Try to submit empty form
      cy.get('[data-cy=biodata-next-button]').click();
      
      // Should show validation errors
      cy.get('[data-cy=validation-error]').should('be.visible');
      cy.get('[data-cy=student-number-input]').should('have.attr', 'aria-invalid', 'true');
    });
  });

  describe('Cross-Browser Compatibility', () => {
    beforeEach(() => {
      cy.login('ahmad.ridwan@student.test', 'SecurePassword123!');
    });

    it('should work consistently across browsers', () => {
      // This test would typically run in CI across multiple browsers
      cy.visit('/student/dashboard');
      
      // Test core functionality
      cy.get('[data-cy=dashboard-content]').should('be.visible');
      cy.get('[data-cy=navigation-menu]').should('be.visible');
      
      // Test CSS rendering
      cy.get('[data-cy=learning-style-card]').should('have.css', 'border-radius');
      cy.get('[data-cy=metric-card]').should('have.css', 'box-shadow');
    });
  });
});

// Custom Cypress commands for this test suite
Cypress.Commands.add('login', (email, password) => {
  cy.visit('/login');
  cy.get('[data-cy=email-input]').type(email);
  cy.get('[data-cy=password-input]').type(password);
  cy.get('[data-cy=login-button]').click();
  cy.url().should('include', '/student/dashboard');
});

Cypress.Commands.add('seedTestUser', () => {
  cy.request('POST', '/api/test/seed-user', {
    name: 'Test Student',
    email: 'test.student@example.com',
    password: 'password',
    role: 'student'
  });
});