/**
 * Performance Optimization Composable for Learning Platform
 * Phase 5: UI/UX and Testing Implementation
 */

import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import type { Ref } from 'vue'

interface PerformanceMetrics {
  navigationTiming: PerformanceNavigationTiming | null
  paintTiming: PerformanceEntry[]
  resourceTiming: PerformanceResourceTiming[]
  userTiming: PerformanceEntry[]
  firstContentfulPaint: number | null
  largestContentfulPaint: number | null
  cumulativeLayoutShift: number | null
  firstInputDelay: number | null
}

interface LazyLoadOptions {
  rootMargin?: string
  threshold?: number | number[]
  loadingClass?: string
  loadedClass?: string
  errorClass?: string
}

interface ImageOptimization {
  webpSupported: boolean
  avifSupported: boolean
  preferredFormat: 'webp' | 'avif' | 'jpeg'
}

export function usePerformanceOptimization() {
  // Performance metrics state
  const performanceMetrics: Ref<PerformanceMetrics> = ref({
    navigationTiming: null,
    paintTiming: [],
    resourceTiming: [],
    userTiming: [],
    firstContentfulPaint: null,
    largestContentfulPaint: null,
    cumulativeLayoutShift: null,
    firstInputDelay: null,
  })

  // Image optimization detection
  const imageOptimization: Ref<ImageOptimization> = ref({
    webpSupported: false,
    avifSupported: false,
    preferredFormat: 'jpeg',
  })

  // Performance observer instances
  const performanceObservers: PerformanceObserver[] = []

  // Lazy loading implementation
  const useLazyLoading = (options: LazyLoadOptions = {}) => {
    const defaultOptions = {
      rootMargin: '50px',
      threshold: 0.1,
      loadingClass: 'loading',
      loadedClass: 'loaded',
      errorClass: 'error',
      ...options,
    }

    const lazyImages = ref<HTMLImageElement[]>([])
    const lazyComponents = ref<HTMLElement[]>([])

    const imageObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const img = entry.target as HTMLImageElement
          loadImage(img, defaultOptions)
          imageObserver.unobserve(img)
        }
      })
    }, {
      rootMargin: defaultOptions.rootMargin,
      threshold: defaultOptions.threshold,
    })

    const loadImage = (img: HTMLImageElement, opts: LazyLoadOptions) => {
      img.classList.add(opts.loadingClass!)
      
      // Get optimized image source
      const src = getOptimizedImageSrc(img.dataset.src || img.src)
      
      const imageLoader = new Image()
      imageLoader.onload = () => {
        img.src = src
        img.classList.remove(opts.loadingClass!)
        img.classList.add(opts.loadedClass!)
        
        // Announce to screen reader if important
        if (img.dataset.important === 'true') {
          announceImageLoad(img.alt || 'Image loaded')
        }
      }
      
      imageLoader.onerror = () => {
        img.classList.remove(opts.loadingClass!)
        img.classList.add(opts.errorClass!)
        console.warn('Failed to load image:', src)
      }
      
      imageLoader.src = src
    }

    const observeImages = (container: HTMLElement = document.body) => {
      const images = container.querySelectorAll('img[data-src]') as NodeListOf<HTMLImageElement>
      images.forEach((img) => {
        lazyImages.value.push(img)
        imageObserver.observe(img)
      })
    }

    return {
      observeImages,
      lazyImages,
      lazyComponents,
    }
  }

  // Image format detection and optimization
  const detectImageSupport = async () => {
    const supportsWebP = await checkImageSupport('webp')
    const supportsAVIF = await checkImageSupport('avif')

    imageOptimization.value = {
      webpSupported: supportsWebP,
      avifSupported: supportsAVIF,
      preferredFormat: supportsAVIF ? 'avif' : supportsWebP ? 'webp' : 'jpeg',
    }
  }

  const checkImageSupport = (format: string): Promise<boolean> => {
    return new Promise((resolve) => {
      const image = new Image()
      image.onload = () => resolve(image.width === 1 && image.height === 1)
      image.onerror = () => resolve(false)
      
      const testImages = {
        webp: 'data:image/webp;base64,UklGRiIAAABXRUJQVlA4IBYAAAAwAQCdASoBAAEADsD+JaQAA3AAAAAA',
        avif: 'data:image/avif;base64,AAAAIGZ0eXBhdmlmAAAAAGF2aWZtaWYxbWlhZk1BMUIAAADybWV0YQAAAAAAAAAoaGRscgAAAAAAAAAAcGljdAAAAAAAAAAAAAAAAGxpYmF2aWYAAAAADnBpdG0AAAAAAAEAAAAeaWxvYwAAAABEAAABAAEAAAABAAABGgAAAB0AAAAoaWluZgAAAAAAAQAAABppbmZlAgAAAAABAABhdjAxQ29sb3IAAAAAamlwcnAAAABLaXBjbwAAABRpc3BlAAAAAAAAAAQAAAAEAAAADHBpeGkAAAAAAwgICAAAAAxhdjFDgQ0MAAAAABNjb2xybmNseAACAAIAAYAAAAAXaXBtYQAAAAAAAAABAAEEAQKDBAAAACVtZGF0EgAKCBgABogQEAwgMg8f8D///8WfhwB8+ErK42A=',
      }
      
      image.src = testImages[format as keyof typeof testImages] || ''
    })
  }

  const getOptimizedImageSrc = (originalSrc: string): string => {
    if (!originalSrc) return ''

    // If the image is already optimized or external, return as-is
    if (originalSrc.includes('.webp') || originalSrc.includes('.avif') || originalSrc.startsWith('http')) {
      return originalSrc
    }

    // Generate optimized image URL based on supported formats
    const { preferredFormat } = imageOptimization.value
    const basePath = originalSrc.replace(/\.[^.]+$/, '')
    
    return `${basePath}.${preferredFormat}`
  }

  // Web Vitals measurement
  const measureWebVitals = () => {
    // First Contentful Paint (FCP)
    const observer1 = new PerformanceObserver((list) => {
      for (const entry of list.getEntries()) {
        if (entry.name === 'first-contentful-paint') {
          performanceMetrics.value.firstContentfulPaint = entry.startTime
        }
      }
    })
    observer1.observe({ entryTypes: ['paint'] })
    performanceObservers.push(observer1)

    // Largest Contentful Paint (LCP)
    const observer2 = new PerformanceObserver((list) => {
      const entries = list.getEntries()
      const lastEntry = entries[entries.length - 1] as PerformanceEntry & { startTime: number }
      performanceMetrics.value.largestContentfulPaint = lastEntry.startTime
    })
    observer2.observe({ entryTypes: ['largest-contentful-paint'] })
    performanceObservers.push(observer2)

    // Cumulative Layout Shift (CLS)
    const observer3 = new PerformanceObserver((list) => {
      let clsValue = 0
      for (const entry of list.getEntries()) {
        if (!(entry as any).hadRecentInput) {
          clsValue += (entry as any).value
        }
      }
      performanceMetrics.value.cumulativeLayoutShift = clsValue
    })
    observer3.observe({ entryTypes: ['layout-shift'] })
    performanceObservers.push(observer3)

    // First Input Delay (FID)
    const observer4 = new PerformanceObserver((list) => {
      for (const entry of list.getEntries()) {
        performanceMetrics.value.firstInputDelay = (entry as any).processingStart - entry.startTime
      }
    })
    observer4.observe({ entryTypes: ['first-input'] })
    performanceObservers.push(observer4)
  }

  // Resource loading optimization
  const preloadCriticalResources = (resources: Array<{ href: string; as: string; type?: string }>) => {
    resources.forEach(({ href, as, type }) => {
      const link = document.createElement('link')
      link.rel = 'preload'
      link.href = href
      link.as = as
      if (type) link.type = type
      document.head.appendChild(link)
    })
  }

  const prefetchResources = (urls: string[]) => {
    urls.forEach((url) => {
      const link = document.createElement('link')
      link.rel = 'prefetch'
      link.href = url
      document.head.appendChild(link)
    })
  }

  // Code splitting and dynamic imports helper
  const dynamicImport = async <T>(
    importFn: () => Promise<T>,
    fallbackComponent?: T,
    options: { timeout?: number; retries?: number } = {}
  ): Promise<T> => {
    const { timeout = 10000, retries = 2 } = options
    
    const attemptImport = async (attempt: number): Promise<T> => {
      try {
        const timeoutPromise = new Promise<never>((_, reject) => {
          setTimeout(() => reject(new Error('Import timeout')), timeout)
        })
        
        const result = await Promise.race([importFn(), timeoutPromise])
        return result
      } catch (error) {
        if (attempt < retries) {
          console.warn(`Import failed, retrying (${attempt + 1}/${retries})...`, error)
          await new Promise(resolve => setTimeout(resolve, 1000 * Math.pow(2, attempt)))
          return attemptImport(attempt + 1)
        }
        
        if (fallbackComponent) {
          console.error('Import failed, using fallback:', error)
          return fallbackComponent
        }
        
        throw error
      }
    }
    
    return attemptImport(0)
  }

  // Memory usage monitoring
  const monitorMemoryUsage = () => {
    if ('memory' in performance) {
      const memory = (performance as any).memory
      return {
        used: memory.usedJSHeapSize,
        total: memory.totalJSHeapSize,
        limit: memory.jsHeapSizeLimit,
        percentage: Math.round((memory.usedJSHeapSize / memory.totalJSHeapSize) * 100),
      }
    }
    return null
  }

  // Network information detection
  const getNetworkInfo = () => {
    const connection = (navigator as any).connection || (navigator as any).mozConnection || (navigator as any).webkitConnection
    
    if (connection) {
      return {
        effectiveType: connection.effectiveType,
        downlink: connection.downlink,
        rtt: connection.rtt,
        saveData: connection.saveData,
      }
    }
    
    return null
  }

  // Adaptive loading based on network conditions
  const shouldLoadHighQualityAssets = computed(() => {
    const networkInfo = getNetworkInfo()
    
    if (!networkInfo) return true
    
    // Don't load high quality assets on slow connections or when data saver is on
    if (networkInfo.saveData || networkInfo.effectiveType === 'slow-2g' || networkInfo.effectiveType === '2g') {
      return false
    }
    
    return true
  })

  // Performance budget monitoring
  const checkPerformanceBudget = () => {
    const budget = {
      maxFCP: 1800, // First Contentful Paint
      maxLCP: 2500, // Largest Contentful Paint
      maxCLS: 0.1,  // Cumulative Layout Shift
      maxFID: 100,  // First Input Delay
    }
    
    const metrics = performanceMetrics.value
    const violations: string[] = []
    
    if (metrics.firstContentfulPaint && metrics.firstContentfulPaint > budget.maxFCP) {
      violations.push(`FCP: ${metrics.firstContentfulPaint}ms exceeds budget of ${budget.maxFCP}ms`)
    }
    
    if (metrics.largestContentfulPaint && metrics.largestContentfulPaint > budget.maxLCP) {
      violations.push(`LCP: ${metrics.largestContentfulPaint}ms exceeds budget of ${budget.maxLCP}ms`)
    }
    
    if (metrics.cumulativeLayoutShift && metrics.cumulativeLayoutShift > budget.maxCLS) {
      violations.push(`CLS: ${metrics.cumulativeLayoutShift} exceeds budget of ${budget.maxCLS}`)
    }
    
    if (metrics.firstInputDelay && metrics.firstInputDelay > budget.maxFID) {
      violations.push(`FID: ${metrics.firstInputDelay}ms exceeds budget of ${budget.maxFID}ms`)
    }
    
    return {
      passed: violations.length === 0,
      violations,
    }
  }

  // Critical CSS inlining helper
  const inlineCriticalCSS = (css: string) => {
    const style = document.createElement('style')
    style.textContent = css
    document.head.appendChild(style)
  }

  // Service Worker registration for caching
  const registerServiceWorker = async (swPath: string = '/sw.js') => {
    if ('serviceWorker' in navigator) {
      try {
        const registration = await navigator.serviceWorker.register(swPath)
        console.log('Service Worker registered:', registration)
        return registration
      } catch (error) {
        console.error('Service Worker registration failed:', error)
        return null
      }
    }
    return null
  }

  // Utility function to announce image loading for accessibility
  const announceImageLoad = (message: string) => {
    const announcement = document.createElement('div')
    announcement.setAttribute('aria-live', 'polite')
    announcement.className = 'sr-only'
    announcement.textContent = message
    document.body.appendChild(announcement)
    
    setTimeout(() => {
      document.body.removeChild(announcement)
    }, 1000)
  }

  // Performance reporting
  const reportPerformanceMetrics = () => {
    const metrics = {
      ...performanceMetrics.value,
      memoryUsage: monitorMemoryUsage(),
      networkInfo: getNetworkInfo(),
      timestamp: Date.now(),
      url: window.location.href,
    }
    
    // In a real application, this would send metrics to an analytics service
    console.log('Performance Metrics:', metrics)
    
    return metrics
  }

  // Initialize performance monitoring
  onMounted(async () => {
    await detectImageSupport()
    measureWebVitals()
    
    // Collect navigation timing
    performanceMetrics.value.navigationTiming = performance.getEntriesByType('navigation')[0] as PerformanceNavigationTiming
    
    // Report metrics after page load
    window.addEventListener('load', () => {
      setTimeout(reportPerformanceMetrics, 0)
    })
  })

  // Cleanup
  onUnmounted(() => {
    performanceObservers.forEach(observer => observer.disconnect())
  })

  return {
    // State
    performanceMetrics,
    imageOptimization,
    
    // Lazy loading
    useLazyLoading,
    
    // Image optimization
    getOptimizedImageSrc,
    detectImageSupport,
    
    // Resource optimization
    preloadCriticalResources,
    prefetchResources,
    dynamicImport,
    
    // Monitoring
    monitorMemoryUsage,
    getNetworkInfo,
    checkPerformanceBudget,
    reportPerformanceMetrics,
    
    // Computed
    shouldLoadHighQualityAssets,
    
    // Utilities
    inlineCriticalCSS,
    registerServiceWorker,
  }
}