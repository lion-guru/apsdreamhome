/**
 * 3D Interactive Holographic ID Card
 * Follows mouse or device gyroscope to create a stunning 3D glassmorphism and foil effect.
 */

document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.digital-id-card');
    
    cards.forEach(card => {
        const wrapper = card.closest('.id-card-wrapper') || card;
        
        // Mouse Move Effect (Desktop)
        wrapper.addEventListener('mousemove', (e) => {
            const rect = wrapper.getBoundingClientRect();
            
            // Calculate mouse position relative to card center (-1 to 1)
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const percentX = (x - centerX) / centerX;
            const percentY = -((y - centerY) / centerY); // Invert Y for natural tilt
            
            // Max tilt angle (degrees)
            const maxTilt = 15;
            
            const rotateX = percentY * maxTilt;
            const rotateY = percentX * maxTilt;
            
            // Apply CSS variables for glare and foil calculations
            card.style.setProperty('--mouse-x', `${(x / rect.width) * 100}%`);
            card.style.setProperty('--mouse-y', `${(y / rect.height) * 100}%`);
            card.style.setProperty('--rotate-x', `${rotateX}deg`);
            card.style.setProperty('--rotate-y', `${rotateY}deg`);
            
            card.classList.add('interacting');
        });
        
        // Mouse Leave (Reset)
        wrapper.addEventListener('mouseleave', () => {
            card.style.setProperty('--rotate-x', '0deg');
            card.style.setProperty('--rotate-y', '0deg');
            card.style.setProperty('--mouse-x', '50%');
            card.style.setProperty('--mouse-y', '50%');
            card.classList.remove('interacting');
            
            // Reset transition for smooth return
            card.style.transition = 'transform 0.6s cubic-bezier(0.23, 1, 0.32, 1)';
            setTimeout(() => {
                if(!card.classList.contains('interacting')) {
                    card.style.transition = 'none'; // Remove transition for instant mouse follow
                }
            }, 600);
        });
        
        wrapper.addEventListener('mouseenter', () => {
            card.style.transition = 'none';
        });

        // Device Orientation Effect (Mobile)
        if (window.DeviceOrientationEvent) {
            window.addEventListener('deviceorientation', (e) => {
                // Constrain values
                let gamma = e.gamma; // Left/Right (-90 to 90)
                let beta = e.beta;   // Front/Back (-180 to 180)
                
                // Keep beta within reasonable bounds for viewing
                if (beta > 90) beta = 90;
                if (beta < -90) beta = -90;
                
                // Normalize to -1 to 1 range (roughly)
                const percentX = gamma / 45;
                const percentY = (beta - 45) / 45; // Assume holding at 45deg angle
                
                const maxTilt = 20;
                
                let rotateX = -(percentY * maxTilt);
                let rotateY = percentX * maxTilt;
                
                // Clamp
                rotateX = Math.max(-maxTilt, Math.min(maxTilt, rotateX));
                rotateY = Math.max(-maxTilt, Math.min(maxTilt, rotateY));
                
                if(!card.classList.contains('interacting')) { // Mouse takes priority if both fire
                    card.style.setProperty('--rotate-x', `${rotateX}deg`);
                    card.style.setProperty('--rotate-y', `${rotateY}deg`);
                    card.style.setProperty('--mouse-x', `${(percentX + 1) * 50}%`);
                    card.style.setProperty('--mouse-y', `${(-percentY + 1) * 50}%`);
                }
            });
        }
        
        // Initial intro animation
        setTimeout(() => {
            card.classList.add('revealed');
        }, 300);
    });
});
