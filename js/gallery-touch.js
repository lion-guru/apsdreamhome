// Gallery Touch Gestures and Animations with Enhanced Transitions

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('lightbox-modal');
    const modalImg = modal.querySelector('.modal-body img');
    let currentScale = 1;
    let initialDistance = 0;
    let startX = 0;
    let startY = 0;
    let lastX = 0;
    let lastY = 0;
    let isDragging = false;
    let currentRotation = 0;

    // Enhanced animation configurations
    const SWIPE_THRESHOLD = 50;
    const ANIMATION_DURATION = 300;
    const MAX_SCALE = 3;
    const MIN_SCALE = 0.5;
    const ROTATION_THRESHOLD = 45; // degrees

    // Touch start event
    // Enhanced touch start handler with spring effect
    modalImg.addEventListener('touchstart', (e) => {
        if (e.touches.length === 1) {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            lastX = startX;
            lastY = startY;
            isDragging = true;
            modalImg.style.transition = 'transform 0.1s cubic-bezier(0.4, 0, 0.2, 1)';
            modalImg.style.transform = `scale(${currentScale * 0.95})`; // Slight scale down for feedback
        } else if (e.touches.length === 2) {
            initialDistance = getDistance(e.touches[0], e.touches[1]);
            modalImg.style.transition = 'none';
        }
    });

    // Touch move event
    // Enhanced touch move handler with smooth animations and rotation
    modalImg.addEventListener('touchmove', function(e) {
        if (e.touches.length === 1) {
            const deltaX = e.touches[0].clientX - lastX;
            const deltaY = e.touches[0].clientY - lastY;
    
            // Add slight rotation based on swipe speed
            const swipeSpeed = Math.abs(deltaX) + Math.abs(deltaY);
            const rotationDelta = (swipeSpeed > 10) ? (deltaX > 0 ? 2 : -2) : 0;
            currentRotation = (currentRotation + rotationDelta) % 360;
    
            modalImg.style.transform = `translate(${currentX + deltaX}px, ${currentY + deltaY}px) scale(${currentScale}) rotate(${currentRotation}deg)`;
            
            lastX = e.touches[0].clientX;
            lastY = e.touches[0].clientY;
        }
    });

    // Touch end event
    modalImg.addEventListener('touchend', function(e) {
        if (e.touches.length < 2) {
            // Update current scale after pinch
            const transform = window.getComputedStyle(modalImg).transform;
            const matrix = new DOMMatrix(transform);
            currentScale = matrix.m11;
        }
        
        // Reset position if scaled back to normal
        if (currentScale === 1) {
            currentX = 0;
            currentY = 0;
            modalImg.style.transform = 'none';
        }
        
        isMoving = false;
    });

    // Double tap to reset zoom
    let lastTap = 0;
    modalImg.addEventListener('touchend', function(e) {
        const currentTime = new Date().getTime();
        const tapLength = currentTime - lastTap;
        
        if (tapLength < 300 && tapLength > 0) {
            // Double tap detected
            currentScale = 1;
            currentX = 0;
            currentY = 0;
            modalImg.style.transform = 'none';
            e.preventDefault();
        }
        lastTap = currentTime;
    });

    // Calculate distance between two touch points
    function getDistance(touch1, touch2) {
        const dx = touch1.clientX - touch2.clientX;
        const dy = touch1.clientY - touch2.clientY;
        return Math.sqrt(dx * dx + dy * dy);
    }

    // Add smooth transitions for touch interactions
    modalImg.style.transition = 'transform 0.3s ease-out';

    // Remove transition during active touch
    modalImg.addEventListener('touchstart', () => {
        modalImg.style.transition = 'none';
    });

    // Restore transition after touch
    modalImg.addEventListener('touchend', () => {
        modalImg.style.transition = 'transform 0.3s ease-out';
    });
});