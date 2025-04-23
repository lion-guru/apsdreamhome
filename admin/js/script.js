$(document).ready(function() {
    // Form validation
    $('form').on('submit', function(e) {
        var username = $('input[name="user"]').val().trim();
        var password = $('input[name="pass"]').val().trim();
        
        if (!username || !password) {
            e.preventDefault();
            alert('कृपया सभी फील्ड भरें');
            return false;
        }
    });

    // Show/hide password functionality
    $('.form-group').append('<span class="password-toggle"><i class="fas fa-eye"></i></span>');
    
    $('.password-toggle').on('click', function() {
        var passwordField = $(this).siblings('input[type="password"]');
        var icon = $(this).find('i');
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Add custom styles for password toggle
    $('<style>\n\
        .password-toggle {\n\
            position: absolute;\n\
            right: 10px;\n\
            top: 50%;\n\
            transform: translateY(-50%);\n\
            cursor: pointer;\n\
            color: #666;\n\
        }\n\
        .form-group {\n\
            position: relative;\n\
        }\n\
    </style>').appendTo('head');

    // Add loading indicator on form submit
    $('form').on('submit', function() {
        $('.btn-primary').prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> लॉगिन हो रहा है...'
        );
    });
});