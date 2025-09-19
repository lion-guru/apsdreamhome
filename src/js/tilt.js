import VanillaTilt from 'vanilla-tilt';

document.addEventListener('DOMContentLoaded', function() {
  VanillaTilt.init(document.querySelectorAll(".property-card"), {
    max: 8,
    perspective: 1000,
    speed: 300,
    glare: true,
    "max-glare": 0.1,
    scale: 1.02
  });
});
