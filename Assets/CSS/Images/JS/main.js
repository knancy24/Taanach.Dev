
document.addEventListener('DOMContentLoaded', function() {
    console.log('Portfolio website script loaded');
    
    // Mobile menu toggle
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    
    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function() {
            mainNav.classList.toggle('active');
            this.querySelector('i').classList.toggle('fa-bars');
            this.querySelector('i').classList.toggle('fa-times');
        });
    }
    
    // ENHANCED PORTFOLIO FILTERING
    const filterButtons = document.querySelectorAll('.portfolio-filter .filter-btn');
    const portfolioItems = document.querySelectorAll('.portfolio-item');
    const noResultsMessage = document.getElementById('no-results');
    
    if (filterButtons.length && portfolioItems.length) {
        console.log(`Found ${filterButtons.length} filter buttons and ${portfolioItems.length} portfolio items`);
        
        // Function to update button states
        function updateButtonStates(activeButton) {
            filterButtons.forEach(button => {
                button.classList.remove('active');
                button.style.transform = 'translateY(0)';
                button.style.backgroundColor = '#f0f8ff';
                button.style.color = '#2c3e50';
                button.style.borderColor = '#e0f0ff';
            });
            
            if (activeButton) {
                activeButton.classList.add('active');
                activeButton.style.transform = 'translateY(-3px)';
            }
        }
        
        // Function to filter projects
        function filterProjects(filterValue) {
            let visibleCount = 0;
            
            portfolioItems.forEach(item => {
                const itemCategory = item.getAttribute('data-category');
                
                if (filterValue === 'all' || itemCategory === filterValue) {
                    // Show item
                    item.classList.remove('hidden');
                    item.style.display = 'block';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                    item.style.height = 'auto';
                    item.style.margin = '';
                    item.style.padding = '';
                    item.style.overflow = 'visible';
                    item.style.pointerEvents = 'auto';
                    visibleCount++;
                    
                    // Add animation
                    item.style.animation = 'fadeIn 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards';
                } else {
                    // Hide item
                    item.classList.add('hidden');
                    item.style.display = 'none';
                    item.style.opacity = '0';
                    item.style.transform = 'translateY(20px)';
                    item.style.height = '0';
                    item.style.margin = '0';
                    item.style.padding = '0';
                    item.style.overflow = 'hidden';
                    item.style.pointerEvents = 'none';
                }
            });
            
            // Show/hide no results message
            if (noResultsMessage) {
                if (visibleCount === 0) {
                    noResultsMessage.style.display = 'block';
                    noResultsMessage.style.animation = 'fadeIn 0.6s ease';
                } else {
                    noResultsMessage.style.display = 'none';
                }
            }
            
            console.log(`Filtered to ${filterValue}, showing ${visibleCount} projects`);
            return visibleCount;
        }
        
        // Add click events to filter buttons
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filterValue = this.getAttribute('data-filter');
                
                // Update UI
                updateButtonStates(this);
                
                // Filter projects
                const visibleCount = filterProjects(filterValue);
                
                // Add click animation
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    if (this.classList.contains('active')) {
                        this.style.transform = 'translateY(-3px)';
                    } else {
                        this.style.transform = 'translateY(0)';
                    }
                }, 150);
            });
        });
        
        // Add hover effect to buttons
        filterButtons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                if (!this.classList.contains('active')) {
                    this.style.transform = 'translateY(-3px)';
                    this.style.backgroundColor = '#e0f7ff';
                }
            });
            
            button.addEventListener('mouseleave', function() {
                if (!this.classList.contains('active')) {
                    this.style.transform = 'translateY(0)';
                    this.style.backgroundColor = '#f0f8ff';
                }
            });
        });
        
        // Initialize based on URL parameter
        function initializeFromURL() {
            const urlParams = new URLSearchParams(window.location.search);
            const categoryParam = urlParams.get('filter');
            
            if (categoryParam && ['all', 'web', 'app', 'design'].includes(categoryParam)) {
                const button = document.querySelector(`[data-filter="${categoryParam}"]`);
                if (button) {
                    updateButtonStates(button);
                    filterProjects(categoryParam);
                    return;
                }
            }
            
            // Default to "All"
            const allButton = document.querySelector('[data-filter="all"]');
            if (allButton) {
                updateButtonStates(allButton);
                filterProjects('all');
            }
        }
        
        // Initialize
        initializeFromURL();
        console.log('Portfolio filter initialized successfully');
    } else {
        console.warn('Portfolio filter elements not found');
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                if (mainNav && mainNav.classList.contains('active')) {
                    mainNav.classList.remove('active');
                    if (menuToggle) {
                        menuToggle.querySelector('i').classList.toggle('fa-bars');
                        menuToggle.querySelector('i').classList.toggle('fa-times');
                    }
                }
            }
        });
    });
    
    // Form validation
    const contactForm = document.querySelector('.contact-form form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            let isValid = true;
            const nameInput = this.querySelector('#name');
            const emailInput = this.querySelector('#email');
            const messageInput = this.querySelector('#message');
            
            // Reset errors
            this.querySelectorAll('.has-error').forEach(el => {
                el.classList.remove('has-error');
            });
            this.querySelectorAll('.error-message').forEach(el => {
                el.remove();
            });
            
            // Validate name
            if (!nameInput.value.trim()) {
                isValid = false;
                displayError(nameInput, 'Name is required');
            }
            
            // Validate email
            if (!emailInput.value.trim()) {
                isValid = false;
                displayError(emailInput, 'Email is required');
            } else if (!isValidEmail(emailInput.value.trim())) {
                isValid = false;
                displayError(emailInput, 'Please enter a valid email');
            }
            
            // Validate message
            if (!messageInput.value.trim()) {
                isValid = false;
                displayError(messageInput, 'Message is required');
            } else if (messageInput.value.trim().length < 10) {
                isValid = false;
                displayError(messageInput, 'Message should be at least 10 characters');
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    function displayError(input, message) {
        const formGroup = input.closest('.form-group');
        formGroup.classList.add('has-error');
        
        const errorElement = document.createElement('span');
        errorElement.className = 'error-message';
        errorElement.textContent = message;
        formGroup.appendChild(errorElement);
    }
    
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    // Animate skill bars on scroll
    const skillBars = document.querySelectorAll('.skill-progress');
    if (skillBars.length) {
        const animateSkillBars = () => {
            skillBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                
                const observer = new IntersectionObserver((entries) => {
                    if (entries[0].isIntersecting) {
                        bar.style.transition = 'width 1.5s ease';
                        bar.style.width = width;
                        observer.unobserve(bar);
                    }
                });
                
                observer.observe(bar);
            });
        };
        
        animateSkillBars();
    }
    
    // Old portfolio toggle function (keeping for backward compatibility)
    function togglePortfolio(id) {
        const sections = document.querySelectorAll('.portfolio-content');
        
        sections.forEach(section => {
            if (section.id === id) {
                section.style.display =
                    section.style.display === 'block' ? 'none' : 'block';
            } else {
                section.style.display = 'none';
            }
        });
    }
    
    // Make togglePortfolio function available globally if needed
    window.togglePortfolio = togglePortfolio;
    
    console.log('All scripts initialized successfully');


    // Animate skill bars on scroll
function animateSkillBars() {
    const skillBars = document.querySelectorAll('.skill-progress');
    const learningBars = document.querySelectorAll('.learning-progress .progress-bar');
    
    if (skillBars.length || learningBars.length) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const bar = entry.target;
                    const width = bar.getAttribute('style')?.match(/width:\s*(\d+)%/)?.[1] || '0';
                    
                    // Reset to 0 and animate
                    bar.style.width = '0';
                    setTimeout(() => {
                        bar.style.transition = 'width 1.5s ease';
                        bar.style.width = width + '%';
                    }, 300);
                    
                    observer.unobserve(bar);
                }
            });
        }, {
            threshold: 0.3,
            rootMargin: '0px 0px -50px 0px'
        });
        
        skillBars.forEach(bar => observer.observe(bar));
        learningBars.forEach(bar => observer.observe(bar));
    }
}

// Call this function in your DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    // ... your existing code ...
    
    animateSkillBars();
});


document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS
    AOS.init({
        duration: 1000,
        once: true,
        offset: 100
    });
    
    // Typing Animation
    const typingText = document.querySelector('.typing-text');
    const professions = ['Full Stack Developer', 'Web Designer', 'Problem Solver', 'Tech Enthusiast'];
    let professionIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    
    function type() {
        const currentProfession = professions[professionIndex];
        
        if (isDeleting) {
            typingText.textContent = currentProfession.substring(0, charIndex - 1);
            charIndex--;
        } else {
            typingText.textContent = currentProfession.substring(0, charIndex + 1);
            charIndex++;
        }
        
        if (!isDeleting && charIndex === currentProfession.length) {
            isDeleting = true;
            setTimeout(type, 1500);
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            professionIndex = (professionIndex + 1) % professions.length;
            setTimeout(type, 500);
        } else {
            setTimeout(type, isDeleting ? 50 : 100);
        }
    }
    
    // Start typing animation
    if (typingText) {
        setTimeout(type, 1000);
    }
    
    // Stats Counter Animation
    const statNumbers = document.querySelectorAll('.stat-number');
    
    function animateStats() {
        statNumbers.forEach(stat => {
            const target = parseInt(stat.getAttribute('data-count'));
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;
            
            const counter = setInterval(() => {
                current += step;
                if (current >= target) {
                    stat.textContent = target + '+';
                    clearInterval(counter);
                } else {
                    stat.textContent = Math.floor(current) + '+';
                }
            }, 16);
        });
    }
    
    // Animate stats when they come into view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateStats();
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    const heroStats = document.querySelector('.hero-stats');
    if (heroStats) observer.observe(heroStats);
    
    // Animate skill bars on scroll
    const skillBars = document.querySelectorAll('.skill-progress');
    
    const animateSkillBars = () => {
        skillBars.forEach(bar => {
            const rect = bar.getBoundingClientRect();
            const isVisible = rect.top <= window.innerHeight && rect.bottom >= 0;
            
            if (isVisible && bar.style.width === '0%') {
                const percentage = bar.getAttribute('data-percentage');
                bar.style.width = percentage + '%';
            }
        });
    };
    
    animateSkillBars();
    window.addEventListener('scroll', animateSkillBars);
});

});