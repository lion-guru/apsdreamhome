document.addEventListener("DOMContentLoaded", function() {
    // Initialize AOS
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            once: true
        });
    }

    // Filter functionality
    document.querySelectorAll(".filter-btn").forEach(btn => {
        btn.addEventListener("click", function() {
            const filter = this.getAttribute("data-filter");

            // Update active button
            document.querySelectorAll(".filter-btn").forEach(b => b.classList.remove("active"));
            this.classList.add("active");

            // Filter colonies
            const colonies = document.querySelectorAll(".colony-item");
            colonies.forEach(colony => {
                if (filter === "all") {
                    colony.style.display = "block";
                } else {
                    const location = colony.getAttribute("data-location");
                    if (location === filter || colony.classList.contains(filter)) {
                        colony.style.display = "block";
                    } else {
                        colony.style.display = "none";
                    }
                }
            });
        });
    });

    // Counter animation
    function animateCounters() {
        const counters = document.querySelectorAll(".stat-number");

        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute("data-target")) || parseInt(counter.textContent);
            const duration = 2000; // 2 seconds
            const increment = target / (duration / 16); // 60fps
            let current = 0;

            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    counter.textContent = target;
                    clearInterval(timer);
                } else {
                    counter.textContent = Math.floor(current);
                }
            }, 16);
        });
    }
    
    animateCounters();

    // Interest modal
    window.showInterest = function(colonyId) {
        const input = document.getElementById("colony_id");
        if (input) input.value = colonyId;
        
        const modalEl = document.getElementById("interestModal");
        if (modalEl && window.bootstrap) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    };

    // Form submission
    const interestForm = document.getElementById("interestForm");
    if (interestForm) {
        interestForm.addEventListener("submit", function(e) {
            e.preventDefault();
            alert("Thank you for your interest! We will contact you soon.");
            
            const modalEl = document.getElementById("interestModal");
            if (modalEl && window.bootstrap) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
            
            this.reset();
        });
    }
});
