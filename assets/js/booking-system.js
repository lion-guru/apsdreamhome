var BookingSystem = {
    currentStep: 1,
    totalSteps: 3,
    init: function() { this.showStep(1); },
    showStep: function(n) {
        this.currentStep = n;
        for (var i = 1; i <= this.totalSteps; i++) {
            var s = document.getElementById('step-' + i);
            if (s) s.style.display = i === n ? 'block' : 'none';
        }
        var prev = document.getElementById('prevBtn');
        var next = document.getElementById('nextBtn');
        if (prev) prev.style.display = n === 1 ? 'none' : 'inline-block';
        if (next) next.textContent = n === this.totalSteps ? 'Submit' : 'Next';
    },
    next: function() { if (this.currentStep < this.totalSteps) this.showStep(this.currentStep + 1); },
    prev: function() { if (this.currentStep > 1) this.showStep(this.currentStep - 1); }
};
