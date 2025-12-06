/**
 * SmartNote Landing Page
 * JavaScript Interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initNavbarScroll();
    initSmoothScroll();
    initBackToTop();
    initScrollAnimations();
    initContactForm();
    initTestimonialForm();
    initActiveNavLinks();
});

/**
 * Navbar Scroll Effect
 * Adds 'scrolled' class when page is scrolled
 */
function initNavbarScroll() {
    const navbar = document.querySelector('.navbar');
    
    function handleScroll() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }
    
    window.addEventListener('scroll', handleScroll);
    handleScroll(); // Initial check
}

/**
 * Smooth Scroll for Anchor Links
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            if (href === '#') return;
            
            e.preventDefault();
            
            const target = document.querySelector(href);
            if (target) {
                const navbarHeight = document.querySelector('.navbar').offsetHeight;
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navbarHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                const navbarCollapse = document.querySelector('.navbar-collapse');
                if (navbarCollapse.classList.contains('show')) {
                    const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    }
                }
            }
        });
    });
}

/**
 * Back to Top Button
 */
function initBackToTop() {
    const backToTop = document.getElementById('backToTop');
    
    function handleScroll() {
        if (window.scrollY > 300) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
        }
    }
    
    window.addEventListener('scroll', handleScroll);
    
    backToTop.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

/**
 * Scroll Animations
 * Animates elements when they enter viewport
 */
function initScrollAnimations() {
    const animatedElements = document.querySelectorAll('.feature-card, .step-card, .testimonial-card, .why-item');
    
    animatedElements.forEach(el => {
        el.classList.add('scroll-animate');
    });
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    animatedElements.forEach(el => {
        observer.observe(el);
    });
}

/**
 * Contact Form Handler
 * Sends form data to WhatsApp
 */
function initContactForm() {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const subject = document.getElementById('subject').value;
            const message = document.getElementById('message').value.trim();
            
            // Validate
            if (!name || !email || !subject || !message) {
                showToast('Mohon lengkapi semua field!', 'error');
                return;
            }
            
            if (!isValidEmail(email)) {
                showToast('Format email tidak valid!', 'error');
                return;
            }
            
            // Format message for WhatsApp
            const subjectText = getSubjectText(subject);
            const whatsappMessage = `*Pesan dari Website SmartNote*%0A%0A` +
                `*Nama:* ${encodeURIComponent(name)}%0A` +
                `*Email:* ${encodeURIComponent(email)}%0A` +
                `*Subjek:* ${encodeURIComponent(subjectText)}%0A%0A` +
                `*Pesan:*%0A${encodeURIComponent(message)}`;
            
            // Open WhatsApp
            const whatsappUrl = `https://wa.me/6281371254173?text=${whatsappMessage}`;
            window.open(whatsappUrl, '_blank');
            
            // Reset form
            contactForm.reset();
            showToast('Redirecting ke WhatsApp...', 'success');
        });
    }
}

/**
 * Testimonial Form Handler
 * Saves testimonials to localStorage and displays them
 */
function initTestimonialForm() {
    const testimonialForm = document.getElementById('testimonialForm');
    
    if (testimonialForm) {
        // Load existing testimonials
        loadTestimonials();
        
        testimonialForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('testiName').value.trim();
            const profession = document.getElementById('testiProfession').value.trim();
            const message = document.getElementById('testiMessage').value.trim();
            
            if (!name || !profession || !message) {
                showToast('Mohon lengkapi semua field!', 'error');
                return;
            }
            
            // Save testimonial
            saveTestimonial({
                name: name,
                profession: profession,
                message: message,
                date: new Date().toISOString()
            });
            
            // Reset form
            testimonialForm.reset();
            showToast('Terima kasih! Testimoni Anda berhasil dikirim.', 'success');
        });
    }
}

/**
 * Save testimonial to localStorage
 */
function saveTestimonial(testimonial) {
    let testimonials = JSON.parse(localStorage.getItem('smartnote_testimonials') || '[]');
    testimonials.push(testimonial);
    localStorage.setItem('smartnote_testimonials', JSON.stringify(testimonials));
    
    // Reload testimonials display
    loadTestimonials();
}

/**
 * Load testimonials from localStorage
 */
function loadTestimonials() {
    const testimonials = JSON.parse(localStorage.getItem('smartnote_testimonials') || '[]');
    const container = document.querySelector('.testimonials-section .row.g-4');
    
    if (!container || testimonials.length === 0) return;
    
    // Add user testimonials after existing ones
    testimonials.slice(-3).forEach(testi => {
        // Check if already displayed
        const existingCards = container.querySelectorAll('.testimonial-card.user-testi');
        if (existingCards.length >= 3) {
            existingCards[0].closest('.col-lg-4').remove();
        }
        
        const card = createTestimonialCard(testi);
        container.appendChild(card);
    });
}

/**
 * Create testimonial card element
 */
function createTestimonialCard(testimonial) {
    const col = document.createElement('div');
    col.className = 'col-lg-4 col-md-6';
    
    col.innerHTML = `
        <div class="testimonial-card user-testi scroll-animate animated">
            <div class="testimonial-rating">
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
            </div>
            <p class="testimonial-text">"${escapeHtml(testimonial.message)}"</p>
            <div class="testimonial-author">
                <div class="author-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="author-info">
                    <h4>${escapeHtml(testimonial.name)}</h4>
                    <span>${escapeHtml(testimonial.profession)}</span>
                </div>
            </div>
        </div>
    `;
    
    return col;
}

/**
 * Active Navigation Links
 * Highlights nav link based on scroll position
 */
function initActiveNavLinks() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');
    
    function updateActiveLink() {
        const scrollPosition = window.scrollY + 100;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            const sectionId = section.getAttribute('id');
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === `#${sectionId}`) {
                        link.classList.add('active');
                    }
                });
            }
        });
    }
    
    window.addEventListener('scroll', updateActiveLink);
    updateActiveLink(); // Initial check
}

/**
 * Show Toast Notification
 */
function showToast(message, type = 'success') {
    const toastEl = document.getElementById('successToast');
    const toastBody = toastEl.querySelector('.toast-body');
    const toastIcon = toastEl.querySelector('.toast-header i');
    
    toastBody.textContent = message;
    
    if (type === 'error') {
        toastIcon.className = 'bi bi-exclamation-circle-fill text-danger me-2';
    } else {
        toastIcon.className = 'bi bi-check-circle-fill text-success me-2';
    }
    
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}

/**
 * Helper: Validate Email
 */
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Helper: Get Subject Text
 */
function getSubjectText(value) {
    const subjects = {
        'general': 'Pertanyaan Umum',
        'pricing': 'Informasi Harga',
        'demo': 'Request Demo',
        'support': 'Bantuan Teknis',
        'partnership': 'Kerjasama'
    };
    return subjects[value] || value;
}

/**
 * Helper: Escape HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Counter Animation (optional - for hero stats)
 */
function animateCounter(element, target, duration = 2000) {
    let start = 0;
    const increment = target / (duration / 16);
    
    function updateCounter() {
        start += increment;
        if (start < target) {
            element.textContent = Math.floor(start).toLocaleString();
            requestAnimationFrame(updateCounter);
        } else {
            element.textContent = target.toLocaleString();
        }
    }
    
    updateCounter();
}

// Initialize counter animation when hero section is visible
const heroStats = document.querySelectorAll('.stat-number');
if (heroStats.length > 0) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const statNumbers = [
                    { el: heroStats[0], target: 10000 },
                    { el: heroStats[1], target: 50000 },
                    { el: heroStats[2], target: 99 }
                ];
                
                statNumbers.forEach((stat, index) => {
                    if (stat.el) {
                        setTimeout(() => {
                            animateCounter(stat.el, stat.target);
                            if (index === 2) {
                                stat.el.textContent = '99%';
                            } else {
                                stat.el.textContent = stat.target.toLocaleString() + '+';
                            }
                        }, index * 200);
                    }
                });
                
                observer.disconnect();
            }
        });
    }, { threshold: 0.5 });
    
    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
        observer.observe(heroSection);
    }
}
