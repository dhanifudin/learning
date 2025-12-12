/**
 * Accessibility Composable for Learning Platform
 * Phase 5: UI/UX and Testing Implementation - WCAG 2.1 AA Compliance
 */

import { ref, computed, onMounted, onUnmounted } from 'vue'
import type { Ref } from 'vue'

interface AccessibilityPreferences {
  prefersReducedMotion: boolean
  prefersHighContrast: boolean
  prefersDarkMode: boolean
  prefersLargeText: boolean
  focusVisible: boolean
}

interface AccessibilityState {
  announcements: string[]
  currentFocus: string | null
  skipLinks: Array<{ target: string; label: string }>
  keyboardNavigation: boolean
}

export function useAccessibility() {
  // Reactive state for accessibility preferences
  const preferences: Ref<AccessibilityPreferences> = ref({
    prefersReducedMotion: false,
    prefersHighContrast: false,
    prefersDarkMode: false,
    prefersLargeText: false,
    focusVisible: false,
  })

  // Accessibility state
  const accessibilityState: Ref<AccessibilityState> = ref({
    announcements: [],
    currentFocus: null,
    skipLinks: [
      { target: '#main-content', label: 'Skip to main content' },
      { target: '#navigation', label: 'Skip to navigation' },
      { target: '#search', label: 'Skip to search' },
    ],
    keyboardNavigation: false,
  })

  // Screen reader announcement function
  const announceToScreenReader = (message: string, priority: 'polite' | 'assertive' = 'polite') => {
    const announcement = document.createElement('div')
    announcement.setAttribute('aria-live', priority)
    announcement.setAttribute('aria-atomic', 'true')
    announcement.className = 'sr-only'
    announcement.textContent = message
    
    document.body.appendChild(announcement)
    
    // Add to announcements state
    accessibilityState.value.announcements.unshift(message)
    if (accessibilityState.value.announcements.length > 5) {
      accessibilityState.value.announcements.pop()
    }

    // Clean up after announcement
    setTimeout(() => {
      if (document.body.contains(announcement)) {
        document.body.removeChild(announcement)
      }
    }, 1000)
  }

  // Focus management
  const manageFocus = (elementId: string, options: { restore?: boolean } = {}) => {
    const element = document.getElementById(elementId)
    if (!element) return false

    // Store previous focus for restoration
    if (options.restore && document.activeElement) {
      const previousFocus = document.activeElement as HTMLElement
      element.addEventListener('blur', () => {
        previousFocus?.focus()
      }, { once: true })
    }

    element.focus()
    accessibilityState.value.currentFocus = elementId
    return true
  }

  // Trap focus within an element (for modals, dropdowns)
  const trapFocus = (containerElement: HTMLElement, options: { initialFocus?: HTMLElement } = {}) => {
    const focusableElements = containerElement.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    ) as NodeListOf<HTMLElement>

    const firstElement = focusableElements[0]
    const lastElement = focusableElements[focusableElements.length - 1]

    // Focus initial element or first focusable element
    const initialElement = options.initialFocus || firstElement
    initialElement?.focus()

    const handleTabKey = (event: KeyboardEvent) => {
      if (event.key !== 'Tab') return

      if (event.shiftKey) {
        // Shift + Tab - go to previous element
        if (document.activeElement === firstElement) {
          event.preventDefault()
          lastElement?.focus()
        }
      } else {
        // Tab - go to next element
        if (document.activeElement === lastElement) {
          event.preventDefault()
          firstElement?.focus()
        }
      }
    }

    const handleEscapeKey = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        event.preventDefault()
        cleanup()
      }
    }

    const cleanup = () => {
      containerElement.removeEventListener('keydown', handleTabKey)
      containerElement.removeEventListener('keydown', handleEscapeKey)
    }

    containerElement.addEventListener('keydown', handleTabKey)
    containerElement.addEventListener('keydown', handleEscapeKey)

    return cleanup
  }

  // Keyboard navigation support
  const handleKeyboardNavigation = (event: KeyboardEvent) => {
    accessibilityState.value.keyboardNavigation = true
    
    // Show focus indicators when using keyboard
    if (event.key === 'Tab') {
      preferences.value.focusVisible = true
    }

    // Arrow key navigation for grid layouts
    if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(event.key)) {
      handleGridNavigation(event)
    }

    // Enter/Space activation for custom interactive elements
    if (event.key === 'Enter' || event.key === ' ') {
      handleCustomActivation(event)
    }
  }

  // Grid navigation for content grids and analytics dashboards
  const handleGridNavigation = (event: KeyboardEvent) => {
    const activeElement = document.activeElement as HTMLElement
    if (!activeElement?.dataset.gridItem) return

    const gridContainer = activeElement.closest('[data-grid-container]')
    if (!gridContainer) return

    const gridItems = Array.from(gridContainer.querySelectorAll('[data-grid-item]')) as HTMLElement[]
    const currentIndex = gridItems.indexOf(activeElement)
    const columnsCount = parseInt(gridContainer.getAttribute('data-grid-columns') || '1', 10)

    let targetIndex = currentIndex

    switch (event.key) {
      case 'ArrowRight':
        targetIndex = currentIndex + 1
        break
      case 'ArrowLeft':
        targetIndex = currentIndex - 1
        break
      case 'ArrowDown':
        targetIndex = currentIndex + columnsCount
        break
      case 'ArrowUp':
        targetIndex = currentIndex - columnsCount
        break
    }

    if (targetIndex >= 0 && targetIndex < gridItems.length) {
      event.preventDefault()
      gridItems[targetIndex]?.focus()
    }
  }

  // Handle activation for custom interactive elements
  const handleCustomActivation = (event: KeyboardEvent) => {
    const activeElement = document.activeElement as HTMLElement
    
    // Handle custom button-like elements
    if (activeElement?.dataset.customButton && !activeElement.disabled) {
      event.preventDefault()
      activeElement.click()
    }

    // Handle card activations
    if (activeElement?.dataset.cardClickable) {
      event.preventDefault()
      activeElement.click()
    }
  }

  // High contrast mode detection and management
  const detectHighContrastMode = () => {
    const mediaQuery = window.matchMedia('(prefers-contrast: high)')
    preferences.value.prefersHighContrast = mediaQuery.matches

    mediaQuery.addEventListener('change', (e) => {
      preferences.value.prefersHighContrast = e.matches
      updateAccessibilityClasses()
    })
  }

  // Reduced motion detection
  const detectReducedMotionPreference = () => {
    const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)')
    preferences.value.prefersReducedMotion = mediaQuery.matches

    mediaQuery.addEventListener('change', (e) => {
      preferences.value.prefersReducedMotion = e.matches
      updateAccessibilityClasses()
    })
  }

  // Dark mode detection
  const detectDarkModePreference = () => {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
    preferences.value.prefersDarkMode = mediaQuery.matches

    mediaQuery.addEventListener('change', (e) => {
      preferences.value.prefersDarkMode = e.matches
      updateAccessibilityClasses()
    })
  }

  // Large text preference detection
  const detectLargeTextPreference = () => {
    // Check for browser zoom or large text settings
    const testElement = document.createElement('div')
    testElement.style.fontSize = '16px'
    testElement.style.position = 'absolute'
    testElement.style.visibility = 'hidden'
    document.body.appendChild(testElement)

    const computedSize = parseFloat(window.getComputedStyle(testElement).fontSize)
    preferences.value.prefersLargeText = computedSize > 20

    document.body.removeChild(testElement)
  }

  // Update CSS classes based on accessibility preferences
  const updateAccessibilityClasses = () => {
    const html = document.documentElement

    // High contrast mode
    if (preferences.value.prefersHighContrast) {
      html.classList.add('high-contrast')
    } else {
      html.classList.remove('high-contrast')
    }

    // Reduced motion
    if (preferences.value.prefersReducedMotion) {
      html.classList.add('reduced-motion')
    } else {
      html.classList.remove('reduced-motion')
    }

    // Focus visible
    if (preferences.value.focusVisible) {
      html.classList.add('focus-visible')
    } else {
      html.classList.remove('focus-visible')
    }

    // Large text
    if (preferences.value.prefersLargeText) {
      html.classList.add('large-text')
    } else {
      html.classList.remove('large-text')
    }
  }

  // Skip link functionality
  const addSkipLinks = () => {
    const skipLinksContainer = document.createElement('nav')
    skipLinksContainer.className = 'skip-links sr-only-focusable'
    skipLinksContainer.setAttribute('aria-label', 'Skip navigation links')

    accessibilityState.value.skipLinks.forEach(link => {
      const skipLink = document.createElement('a')
      skipLink.href = link.target
      skipLink.textContent = link.label
      skipLink.className = 'skip-link'
      skipLinksContainer.appendChild(skipLink)
    })

    document.body.insertBefore(skipLinksContainer, document.body.firstChild)
  }

  // Color contrast ratio calculation
  const getContrastRatio = (foreground: string, background: string): number => {
    const getLuminance = (color: string): number => {
      const rgb = color.match(/\d+/g)?.map(num => parseInt(num) / 255) || [0, 0, 0]
      
      const [r, g, b] = rgb.map(channel => {
        return channel <= 0.03928 
          ? channel / 12.92 
          : Math.pow((channel + 0.055) / 1.055, 2.4)
      })

      return 0.2126 * r + 0.7152 * g + 0.0722 * b
    }

    const fgLuminance = getLuminance(foreground)
    const bgLuminance = getLuminance(background)

    const lighter = Math.max(fgLuminance, bgLuminance)
    const darker = Math.min(fgLuminance, bgLuminance)

    return (lighter + 0.05) / (darker + 0.05)
  }

  // ARIA attributes management
  const setAriaAttribute = (element: HTMLElement, attribute: string, value: string | boolean) => {
    if (typeof value === 'boolean') {
      element.setAttribute(`aria-${attribute}`, value.toString())
    } else {
      element.setAttribute(`aria-${attribute}`, value)
    }
  }

  // Live region management for dynamic content
  const createLiveRegion = (type: 'polite' | 'assertive' = 'polite'): HTMLElement => {
    const region = document.createElement('div')
    region.setAttribute('aria-live', type)
    region.setAttribute('aria-atomic', 'true')
    region.className = 'sr-only'
    document.body.appendChild(region)
    return region
  }

  // Indonesian language accessibility considerations
  const setupIndonesianA11y = () => {
    document.documentElement.setAttribute('lang', 'id')
    
    // Set text direction (Indonesian is LTR)
    document.documentElement.setAttribute('dir', 'ltr')
    
    // Indonesian-specific screen reader announcements
    return {
      loadingMessage: 'Sedang memuat...',
      errorMessage: 'Terjadi kesalahan',
      successMessage: 'Berhasil',
      navigationLabel: 'Navigasi utama',
      searchLabel: 'Pencarian',
      menuLabel: 'Menu',
    }
  }

  // Computed properties for accessibility status
  const isAccessible = computed(() => {
    return {
      hasHighContrast: preferences.value.prefersHighContrast,
      hasReducedMotion: preferences.value.prefersReducedMotion,
      hasKeyboardNavigation: accessibilityState.value.keyboardNavigation,
      hasFocusVisible: preferences.value.focusVisible,
    }
  })

  // Initialize accessibility features
  onMounted(() => {
    detectHighContrastMode()
    detectReducedMotionPreference()
    detectDarkModePreference()
    detectLargeTextPreference()
    addSkipLinks()
    updateAccessibilityClasses()

    document.addEventListener('keydown', handleKeyboardNavigation)
    document.addEventListener('mousedown', () => {
      preferences.value.focusVisible = false
      accessibilityState.value.keyboardNavigation = false
      updateAccessibilityClasses()
    })
  })

  // Cleanup
  onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyboardNavigation)
  })

  return {
    // State
    preferences,
    accessibilityState,
    
    // Functions
    announceToScreenReader,
    manageFocus,
    trapFocus,
    getContrastRatio,
    setAriaAttribute,
    createLiveRegion,
    setupIndonesianA11y,
    
    // Computed
    isAccessible,
  }
}