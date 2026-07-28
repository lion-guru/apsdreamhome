var AIPropertySearch = {
    init: function(inputSelector, resultsSelector) {
        var input = document.querySelector(inputSelector);
        var results = document.querySelector(resultsSelector);
        if (!input || !results) return;
        var timer;
        input.addEventListener('input', function() {
            clearTimeout(timer);
            timer = setTimeout(function() {
                AIPropertySearch.search(input.value, results);
            }, 300);
        });
    },
    search: function(query, container) {
        if (query.length < 2) { container.innerHTML = ''; return; }
        fetch('/api/properties/search?q=' + encodeURIComponent(query))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            container.innerHTML = '';
            data.forEach(function(p) {
                var div = document.createElement('div');
                div.className = 'p-2 border-bottom';
                div.textContent = p.title + ' - ' + p.price;
                container.appendChild(div);
            });
        });
    }
};
