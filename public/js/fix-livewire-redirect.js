/**
 * Fix Livewire 404 error after redirect
 * This script prevents Livewire from making additional update requests after a redirect
 */

document.addEventListener('DOMContentLoaded', function() {
    let isRedirecting = false;
    let isProcessing = false;
    
    // Listen for custom event to stop Livewire
    window.addEventListener('stop-livewire-updates', function() {
        isRedirecting = true;
        isProcessing = false;
    });
    
    // Intercept Livewire requests
    if (window.Livewire) {
        // Hook into Livewire's request lifecycle
        Livewire.hook('message.sent', (message, component) => {
            // If we're already redirecting or processing, cancel the request
            if (isRedirecting) {
                console.log('Livewire: Blocking request - already redirecting');
                return false;
            }
            
            // Mark as processing for create/update operations
            if (message.updateQueue && message.updateQueue.length > 0) {
                const hasFormSubmit = message.updateQueue.some(update => 
                    update.method === 'create' || 
                    update.method === 'save' ||
                    update.method === 'callMountedAction'
                );
                
                if (hasFormSubmit && isProcessing) {
                    console.log('Livewire: Blocking duplicate form submission');
                    return false;
                }
                
                if (hasFormSubmit) {
                    isProcessing = true;
                }
            }
        });
        
        Livewire.hook('message.processed', (message, component) => {
            // Check if response contains redirect
            if (message.response && message.response.effects) {
                if (message.response.effects.redirect || message.response.effects.redirectRoute) {
                    console.log('Livewire: Redirect detected, stopping further updates');
                    isRedirecting = true;
                    isProcessing = false;
                    
                    // Stop all pending Livewire updates immediately
                    if (window.Livewire && window.Livewire.components) {
                        window.Livewire.components.forEach(c => {
                            if (c.skipWatcher) return;
                            c.skipWatcher = true;
                        });
                    }
                }
            }
            
            // Reset processing flag after successful response (if not redirecting)
            if (!isRedirecting) {
                setTimeout(() => {
                    isProcessing = false;
                }, 1000);
            }
        });
        
        // Handle errors
        Livewire.hook('message.failed', (message, component) => {
            isProcessing = false;
        });
    }
    
    // Also handle page unload
    window.addEventListener('beforeunload', function() {
        isRedirecting = true;
        isProcessing = false;
    });
});
