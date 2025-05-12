document.addEventListener('DOMContentLoaded', function() {
    // Initialize all editor toolbars
    function initEditorToolbars() {
        document.querySelectorAll('.editor-toolbar').forEach(toolbar => {
            const textarea = toolbar.nextElementSibling;
            if (textarea && textarea.tagName === 'TEXTAREA') {
                setupEditorToolbar(toolbar, textarea);
            }
        });
    }

    // Setup individual editor toolbar
    function setupEditorToolbar(toolbar, textarea) {
        toolbar.addEventListener('click', function(e) {
            const button = e.target.closest('button');
            if (button) {
                const command = button.getAttribute('data-command');
                handleEditorCommand(textarea, command);
            }
        });
    }

    // Handle editor commands
    function handleEditorCommand(textarea, command) {
        const startPos = textarea.selectionStart;
        const endPos = textarea.selectionEnd;
        const selectedText = textarea.value.substring(startPos, endPos);
        let newText = '';
        let cursorOffset = 0;

        switch(command) {
            case 'bold':
                newText = `**${selectedText}**`;
                cursorOffset = 2;
                break;
            case 'italic':
                newText = `_${selectedText}_`;
                cursorOffset = 1;
                break;
            case 'insertLink':
                const linkText = selectedText || 'link text';
                const url = prompt('Enter the URL:', 'https://');
                if (url) {
                    newText = `[${linkText}](${url})`;
                    cursorOffset = url ? -1 : 0;
                }
                break;
            case 'insertImage':
                const altText = selectedText || 'image description';
                const imgUrl = prompt('Enter the image URL:', 'https://');
                if (imgUrl) {
                    newText = `![${altText}](${imgUrl})`;
                    cursorOffset = imgUrl ? -1 : 0;
                }
                break;
        }

        if (newText) {
            textarea.value = textarea.value.substring(0, startPos) + 
                          newText + 
                          textarea.value.substring(endPos);
            
            // Set cursor position
            const newCursorPos = startPos + newText.length + cursorOffset;
            textarea.setSelectionRange(newCursorPos, newCursorPos);
            textarea.focus();
        }
    }

    // Initialize all editors
    initEditorToolbars();

    // Mobile menu toggle
    const mobileMenuToggle = document.createElement('button');
    mobileMenuToggle.className = 'mobile-menu-toggle';
    mobileMenuToggle.innerHTML = '<i class="fas fa-bars"></i>';
    mobileMenuToggle.style.display = 'none';
    
    const nav = document.querySelector('nav .container');
    if (nav) {
        nav.parentNode.insertBefore(mobileMenuToggle, nav);
        
        mobileMenuToggle.addEventListener('click', function() {
            nav.style.display = nav.style.display === 'none' ? 'block' : 'none';
        });
        
        function checkScreenSize() {
            if (window.innerWidth <= 768) {
                mobileMenuToggle.style.display = 'block';
                nav.style.display = 'none';
            } else {
                mobileMenuToggle.style.display = 'none';
                nav.style.display = 'block';
            }
        }
        
        checkScreenSize();
        window.addEventListener('resize', checkScreenSize);
    }

    // Handle comment form submission with AJAX
    const commentForm = document.getElementById('subscribe-form');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            
            // Simple validation
            if (!email || !email.includes('@')) {
                alert('Please enter a valid email address.');
                return;
            }
            
            // Here you would typically send the data to your server
            // For this example, we'll just show a message
            alert('Thank you for subscribing!');
            this.reset();
        });
    }
    
    // Show success/error messages for comments
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('comment_success')) {
        alert('Comment submitted successfully!');
        window.history.replaceState({}, document.title, window.location.pathname);
    } else if (urlParams.has('comment_error')) {
        alert(urlParams.get('comment_error'));
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});