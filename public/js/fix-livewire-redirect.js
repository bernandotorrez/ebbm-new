// Fix for Livewire 404 after redirect and file upload
// Compatible with Livewire v3
(function() {
    let hasRedirected = false;
    let redirectTimeout = null;
    let isProcessingUpload = false;
    
    // Wait for Livewire to be ready
    document.addEventListener('livewire:init', function() {
        console.log('Livewire 404 fix initialized');
        
        // Detect when redirect happens
        Livewire.hook('commit', ({ succeed }) => {
            succeed(({ effects }) => {
                if (effects && effects.redirect) {
                    hasRedirected = true;
                    console.log('Redirect detected, will suppress 404 errors');
                    
                    // Clear flag after redirect completes
                    if (redirectTimeout) clearTimeout(redirectTimeout);
                    redirectTimeout = setTimeout(() => {
                        hasRedirected = false;
                        console.log('Redirect flag cleared');
                    }, 3000);
                }
            });
        });
        
        // Intercept failed requests
        Livewire.hook('request', ({ fail }) => {
            fail(({ status, preventDefault }) => {
                // Suppress 404 errors after redirect or during file upload
                if ((hasRedirected || isProcessingUpload) && status === 404) {
                    console.log('Suppressing 404 error (redirect or upload in progress)');
                    preventDefault();
                }
            });
        });
        
        // Track file upload state
        document.addEventListener('livewire:upload-start', () => {
            isProcessingUpload = true;
            console.log('File upload started');
        });
        
        document.addEventListener('livewire:upload-finish', () => {
            setTimeout(() => {
                isProcessingUpload = false;
                console.log('File upload finished');
            }, 1000);
        });
        
        document.addEventListener('livewire:upload-error', () => {
            isProcessingUpload = false;
            console.log('File upload error');
        });
    });
    
    // Fallback for older Livewire versions or if livewire:init doesn't fire
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            if (window.Livewire && !hasRedirected) {
                console.log('Livewire 404 fix loaded (fallback)');
            }
        }, 1000);
    });
    
    // Reset on page navigation
    document.addEventListener('livewire:navigated', () => {
        hasRedirected = false;
        isProcessingUpload = false;
        if (redirectTimeout) clearTimeout(redirectTimeout);
        console.log('Navigation detected, flags reset');
    });
})();
