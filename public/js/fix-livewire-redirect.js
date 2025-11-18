// Simple fix for Livewire 404 after redirect
// Just suppress the error notification, don't block requests
document.addEventListener('DOMContentLoaded', function() {
    // Override Livewire error handler to suppress 404 after successful redirect
    if (window.Livewire) {
        let hasRedirected = false;
        
        Livewire.hook('message.processed', (message) => {
            // Check if redirect happened
            if (message.response?.effects?.redirect) {
                hasRedirected = true;
            }
        });
        
        // Suppress error notifications after redirect
        Livewire.hook('message.failed', (message) => {
            if (hasRedirected) {
                // Suppress error - it's expected after redirect
                return false;
            }
        });
    }
});
