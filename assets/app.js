/*
 * Welcome to your app's main JavaScript file!
 *
 * This is the entry point for your application's JavaScript
 * and includes Bootstrap, Alpine.js, and custom functionality.
 */

// Import SCSS
import './styles/app.scss';

// Import Bootstrap
import 'bootstrap';

// Import Alpine.js
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// Import Bootstrap JS
import { Tooltip, Toast, Popover, Dropdown } from 'bootstrap';

// Initialize Bootstrap components
document.addEventListener('DOMContentLoaded', () => {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new Popover(popoverTriggerEl);
    });

    // Dark mode toggle
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    const body = document.body;
    
    // Check for saved user preference and system preference
    const savedDarkMode = localStorage.getItem('darkMode');
    const prefersDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Apply dark mode if saved or system preference is dark
    if (savedDarkMode === 'true' || (savedDarkMode === null && prefersDarkMode)) {
        body.classList.add('dark-mode');
        updateDarkModeIcon(true);
    }
    
    // Toggle dark mode
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            const isDarkMode = body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDarkMode);
            updateDarkModeIcon(isDarkMode);
            
            // Add transition effect
            body.style.transition = 'background-color 0.5s ease, color 0.5s ease';
            setTimeout(() => {
                body.style.transition = '';
            }, 500);
        });
    }
    
    function updateDarkModeIcon(isDarkMode) {
        if (darkModeToggle) {
            const icon = darkModeToggle.querySelector('i') || darkModeToggle;
            if (isDarkMode) {
                icon.className = 'fas fa-sun';
            } else {
                icon.className = 'fas fa-moon';
            }
        }
    }
    
    // Button ripple effect
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const rect = button.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const ripple = document.createElement('span');
            ripple.className = 'ripple';
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            
            button.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80, // Adjust for navbar height
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Add animation to cards when they enter viewport
    const animateOnScroll = () => {
        const cards = document.querySelectorAll('.card');
        const triggerBottom = window.innerHeight * 0.9;
        
        cards.forEach(card => {
            const cardTop = card.getBoundingClientRect().top;
            
            if (cardTop < triggerBottom) {
                card.classList.add('animate__animated', 'animate__fadeInUp');
                card.style.opacity = '1';
            }
        });
    };
    
    // Initialize cards with zero opacity
    document.querySelectorAll('.card').forEach(card => {
        card.style.opacity = '0';
    });
    
    // Run animation on page load and scroll
    animateOnScroll();
    window.addEventListener('scroll', animateOnScroll);
    
    // Add active class to current nav link
    const currentLocation = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const linkPath = link.getAttribute('href');
        if (linkPath && currentLocation.includes(linkPath) && linkPath !== '/') {
            link.classList.add('active');
        } else if (linkPath === '/' && currentLocation === '/') {
            link.classList.add('active');
        }
    });
    
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// Add loader animation
window.addEventListener('load', () => {
    const loader = document.querySelector('.page-loader');
    if (loader) {
        loader.classList.add('fade-out');
        setTimeout(() => {
            loader.style.display = 'none';
        }, 500);
    }
});

// Interactive functionality for session booking
document.addEventListener('alpine:init', () => {
    Alpine.data('sessionForm', () => ({
        skillOptions: [],
        selectedSkill: '',
        availableDates: [],
        selectedDate: '',
        
        init() {
            // This would normally fetch from your backend API
            this.skillOptions = ['Programming', 'Language Learning', 'Music', 'Cooking'];
            
            // Generate some example dates (next 7 days)
            const today = new Date();
            for (let i = 1; i <= 7; i++) {
                const date = new Date();
                date.setDate(today.getDate() + i);
                this.availableDates.push({
                    value: date.toISOString().split('T')[0],
                    label: date.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' })
                });
            }
        }
    }));
});

// Page loader - consolidated into a single implementation to avoid conflicts
document.addEventListener('DOMContentLoaded', function() {
    const loader = document.querySelector('.page-loader');
    if (loader) {
        // First make sure it's visible
        loader.style.display = 'flex';
        
        // Hide the loader after content loads
        window.addEventListener('load', function() {
            loader.classList.add('fade-out');
            // Enable page animations after load
            document.body.classList.add('content-loaded');
            
            setTimeout(() => {
                loader.style.display = 'none';
            }, 800);
        });
    }
    
    // Initialize all UI enhancement features
    initDarkMode();
    initRippleEffect();
    initAnimations();
    initFormEnhancements();
    initSmoothScrolling();
});

// Dark mode toggle
function initDarkMode() {
    const darkModeToggle = document.querySelector('.dark-mode-toggle');
    const storedTheme = localStorage.getItem('theme');
    
    // Check for saved theme preference
    if (storedTheme === 'dark') {
        document.body.classList.add('dark-mode');
        updateDarkModeIcon(true);
    }
    
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            const isDarkMode = document.body.classList.toggle('dark-mode');
            localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
            updateDarkModeIcon(isDarkMode);
            
            // Add transition effect
            document.body.classList.add('theme-transition');
            setTimeout(() => {
                document.body.classList.remove('theme-transition');
            }, 1000);
        });
    }
}

// Update dark mode icon
function updateDarkModeIcon(isDarkMode) {
    const darkModeToggle = document.querySelector('.dark-mode-toggle i');
    if (darkModeToggle) {
        if (isDarkMode) {
            darkModeToggle.className = 'fas fa-sun';
        } else {
            darkModeToggle.className = 'fas fa-moon';
        }
    }
}

// Button ripple effect
function initRippleEffect() {
    const buttons = document.querySelectorAll('.btn');
    
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const ripple = document.createElement('span');
            ripple.classList.add('ripple');
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
}

// Enhanced animations
function initAnimations() {
    // Card hover effects
    const cards = document.querySelectorAll('.card');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
            this.style.boxShadow = 'var(--box-shadow-xl)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });
    
    // Animate elements when they enter viewport
    const observedElements = document.querySelectorAll('.animate-on-scroll');
    
    if (observedElements.length > 0) {
        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        
        observedElements.forEach(element => {
            observer.observe(element);
        });
    }
}

// Form enhancements
function initFormEnhancements() {
    // Password strength indicator
    const passwordFields = document.querySelectorAll('input[type="password"]');
    
    passwordFields.forEach(field => {
        // Find the closest .form-group parent
        const formGroup = field.closest('.form-group');
        
        // Skip if there's no matching indicator
        if (!formGroup || !formGroup.querySelector('.password-strength')) return;
        
        field.addEventListener('input', function() {
            const password = this.value;
            const strengthIndicator = formGroup.querySelector('.password-strength');
            if (!strengthIndicator) return;
            
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 1;
            
            // Contains uppercase
            if (/[A-Z]/.test(password)) strength += 1;
            
            // Contains lowercase
            if (/[a-z]/.test(password)) strength += 1;
            
            // Contains number
            if (/[0-9]/.test(password)) strength += 1;
            
            // Contains special character
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            // Reset all classes
            strengthIndicator.className = 'password-strength';
            
            // Add appropriate class
            if (password.length === 0) {
                // No class if empty
            } else if (strength < 2) {
                strengthIndicator.classList.add('weak');
            } else if (strength < 4) {
                strengthIndicator.classList.add('medium');
            } else if (strength < 5) {
                strengthIndicator.classList.add('strong');
            } else {
                strengthIndicator.classList.add('very-strong');
            }
        });
    });
    
    // Form input animations
    const formControls = document.querySelectorAll('.form-control');
    
    formControls.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('input-focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('input-focused');
        });
    });
}

// Smooth scrolling for anchor links
function initSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                e.preventDefault();
                
                window.scrollTo({
                    top: targetElement.offsetTop - 80, // Accounting for fixed header
                    behavior: 'smooth'
                });
            }
        });
    });
}

// Add content-loaded class after initial load for entrance animations
window.addEventListener('load', function() {
    document.body.classList.add('content-loaded');
});
