document.addEventListener('DOMContentLoaded', function() {
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
    
    // Portfolio filtering
    const filterButtons = document.querySelectorAll('.filter-btn');
    const portfolioItems = document.querySelectorAll('.portfolio-item');
    
    if (filterButtons.length && portfolioItems.length) {
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Update active button
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                const filterValue = this.getAttribute('data-filter');
                
                // Filter items
                portfolioItems.forEach(item => {
                    if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
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
                    menuToggle.querySelector('i').classList.toggle('fa-bars');
                    menuToggle.querySelector('i').classList.toggle('fa-times');
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
});